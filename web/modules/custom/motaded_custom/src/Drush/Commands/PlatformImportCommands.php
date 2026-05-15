<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import / upsert platform nodes from CSV (local + prod).
 */
final class PlatformImportCommands extends DrushCommands {

  /** @var list<string> */
  private const PARAGRAPH_REFERENCE_FIELDS = [
    'field_why_matters',
    'field_how_we_help',
    'field_process_steps',
    'field_requirements',
    'field_resources',
  ];

  /** Paragraph fields cleared and rebuilt per translation row. */
  private const TRANSLATION_PARAGRAPH_FIELDS = [
    'field_why_matters',
    'field_how_we_help',
    'field_process_steps',
    'field_requirements',
    'field_resources',
  ];

  /** @var list<string> */
  private const TRANSLATION_VALUE_KEYS = [
    'title',
    'status',
    'body',
    'field_short_description',
    'field_subtitle',
    'field_cta_text',
    'field_source_text',
    'field_category',
    'field_region',
    'field_sector',
    'field_cta_link',
    'field_external_link',
    'field_source_link',
    'field_logo',
    'field_hero_image',
    'field_partner_logos',
    'field_meta_tags',
    'field_why_matters',
    'field_how_we_help',
    'field_process_steps',
    'field_requirements',
    'field_resources',
    'field_related_platforms',
  ];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Import platforms from CSV into node type "platform".
   *
   * Default: upsert by exact title (overwrites body, paragraphs, links, etc.).
   * Taxonomy columns must match existing term names (no auto-create).
   * Non-default langcode: set translation_source_title to the English platform title.
   */
  #[CLI\Command(name: 'motaded:platform-import-csv', aliases: ['mpic'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../platform_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'upsert', description: 'When true (default), update existing platform with same title+langcode; otherwise only create new nodes.')]
  #[CLI\Option(name: 'skip-existing', description: 'When upsert is false: skip row if a published platform with the same title exists.')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Usage(name: 'drush mpic', description: 'Upsert from project root platform_import_ready.csv')]
  #[CLI\Usage(name: 'drush mpic ../platform_import_ar_ready.csv', description: 'Upsert Arabic translations (requires EN platforms; translation_source_title column).')]
  #[CLI\Usage(name: 'drush mpic /path/to.csv --upsert=1', description: 'Upsert from a specific CSV file.')]
  public function import(?string $path = NULL, array $options = [
    'upsert' => TRUE,
    'skip-existing' => TRUE,
    'dry-run' => FALSE,
  ]): void {
    $drupal_root = \Drupal::root();
    $default = $drupal_root . '/../platform_import_ready.csv';
    $path = $path ?? $default;

    if ($path !== '' && $path[0] !== '/' && !preg_match('#^[a-zA-Z]:\\\\#', $path)) {
      $candidate = $drupal_root . '/../' . $path;
      if (is_readable($candidate)) {
        $path = $candidate;
      }
    }

    $resolved = $this->fileSystem->realpath($path);
    $path = $resolved ?: $path;
    if (!is_readable($path)) {
      $this->logger()->error(sprintf('Cannot read CSV: %s', $path));
      return;
    }

    $upsert = filter_var($options['upsert'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $skip_existing = filter_var($options['skip-existing'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error('Could not open CSV.');
      return;
    }

    $default_lang = $this->languageManager->getDefaultLanguage()->getId();
    // Match scripts/rebuild_platform_import_csv.php: enclosure ", escape \.
    $header = fgetcsv($handle, 0, ',', '"', '\\');
    if ($header === FALSE || $header === []) {
      fclose($handle);
      $this->logger()->error('Empty CSV.');
      return;
    }
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }

    $indexes = array_flip($header);
    foreach (['title', 'status', 'langcode', 'body', 'body_summary'] as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error(sprintf('Missing column in CSV: %s', $col));
        return;
      }
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $saved = 0;
    $skipped = 0;
    $row_num = 1;

    while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== FALSE) {
      $row_num++;
      $row = $this->padRow($row, count($header));
      $get = static function (array $idx, array $r, string $key): string {
        $i = $idx[$key] ?? NULL;
        if ($i === NULL || !isset($r[$i])) {
          return '';
        }
        return trim((string) $r[$i]);
      };

      $title = $get($indexes, $row, 'title');
      if ($title === '') {
        continue;
      }

      $row_lang = $get($indexes, $row, 'langcode') ?: $default_lang;
      $translation_source = isset($indexes['translation_source_title'])
        ? $get($indexes, $row, 'translation_source_title')
        : '';
      if ($row_lang !== $default_lang && $translation_source === '') {
        $msg = sprintf('Row %d: translation_source_title is required when langcode is %s.', $row_num, $row_lang);
        $this->logger()->error($msg);
        $this->io()->writeln('[error] ' . $msg);
        continue;
      }
      $is_translation = ($row_lang !== $default_lang);
      $existing = $is_translation ? NULL : $this->findPlatformByTitle($title, $row_lang);

      if (!$is_translation && !$upsert && $skip_existing && !$dry_run) {
        $published = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'platform')
          ->condition('title', $title)
          ->condition('status', NodeInterface::PUBLISHED)
          ->range(0, 1)
          ->execute();
        if ($published) {
          $this->io()->writeln(sprintf('Skip row %d (exists): %s', $row_num, $title));
          $skipped++;
          continue;
        }
      }

      try {
        $status = (int) ($get($indexes, $row, 'status') ?: '1');
        $summary = $get($indexes, $row, 'body_summary');
        $body_raw = $get($indexes, $row, 'body');
        if ($body_raw === '') {
          throw new \InvalidArgumentException('Empty body.');
        }
        if ($summary === '') {
          throw new \InvalidArgumentException('Empty body_summary.');
        }

        if ($dry_run) {
          $this->decodeJsonList($get($indexes, $row, 'field_why_matters_json'), 'field_why_matters_json');
          $this->decodeJsonList($get($indexes, $row, 'field_how_we_help_json'), 'field_how_we_help_json');
          $this->decodeJsonList($get($indexes, $row, 'field_process_steps_json'), 'field_process_steps_json');
          $this->decodeJsonList($get($indexes, $row, 'field_requirements_json'), 'field_requirements_json');
          $this->decodeResourcesJson($get($indexes, $row, 'field_resources_json'));
          $meta_raw = $get($indexes, $row, 'field_meta_tags_json');
          if ($meta_raw !== '') {
            $decoded = json_decode($meta_raw, TRUE);
            if (!is_array($decoded)) {
              throw new \InvalidArgumentException('Invalid JSON in field_meta_tags_json');
            }
          }
          if ($is_translation) {
            $base_nid_check = $this->findPlatformByTitle($translation_source, $default_lang);
            if ($base_nid_check === NULL) {
              throw new \InvalidArgumentException('Base platform not found for translation_source_title: ' . $translation_source);
            }
          }
          $this->io()->writeln(sprintf('[dry-run] Row %d OK: %s', $row_num, $title));
          $saved++;
          continue;
        }

        $tax_lang = $is_translation ? $default_lang : $row_lang;
        $related_lang = $is_translation ? $default_lang : $row_lang;

        $base = NULL;
        $tr = NULL;
        $node = NULL;

        if ($is_translation) {
          $base_nid = $this->findPlatformByTitle($translation_source, $default_lang);
          if ($base_nid === NULL) {
            throw new \InvalidArgumentException('Base platform not found for translation_source_title: ' . $translation_source);
          }
          $base = $node_storage->load($base_nid);
          if (!$base instanceof NodeInterface) {
            throw new \InvalidArgumentException('Could not load base platform node.');
          }
          if (!$base->isTranslatable()) {
            throw new \InvalidArgumentException('Platform nodes are not translatable. Enable content translation for the platform bundle (language.content_settings.node.platform) and import configuration.');
          }
          if (!$base->hasTranslation($row_lang)) {
            $base->addTranslation($row_lang, [
              'title' => $title,
              'status' => $status,
            ]);
          }
          $tr = $base->getTranslation($row_lang);
          foreach (self::TRANSLATION_PARAGRAPH_FIELDS as $fieldName) {
            $this->deleteParagraphFieldItems($tr, $fieldName);
          }
        }
        else {
          $node = $existing ? $node_storage->load($existing) : NULL;
          if ($node instanceof NodeInterface) {
            foreach (self::PARAGRAPH_REFERENCE_FIELDS as $fieldName) {
              $this->deleteParagraphFieldItems($node, $fieldName);
            }
          }
        }

        $values = [
          'type' => 'platform',
          'title' => $title,
          'langcode' => $row_lang,
          'status' => $status,
          'uid' => 1,
          'promote' => 0,
          'body' => [
            'value' => $this->normalizeBodyHtml($body_raw),
            'summary' => $this->normalizeBodyHtml($summary),
            'format' => 'basic_html',
          ],
          'field_short_description' => $get($indexes, $row, 'field_short_description'),
          'field_subtitle' => $get($indexes, $row, 'field_subtitle'),
          'field_cta_text' => $get($indexes, $row, 'field_cta_text'),
          'field_featured' => (bool) (int) ($get($indexes, $row, 'field_featured') ?: '0'),
          'field_source_text' => $get($indexes, $row, 'field_source_text'),
        ];

        $category_name = $get($indexes, $row, 'field_category_name');
        if ($category_name !== '') {
          $tid = $this->getExistingTermId('platform_category', $category_name, $tax_lang);
          if ($tid !== NULL) {
            $values['field_category'] = ['target_id' => $tid];
          }
          else {
            $this->logger()->warning(sprintf('Row %d: category term not found (skipped): %s', $row_num, $category_name));
          }
        }

        $region_name = $get($indexes, $row, 'field_region_name');
        if ($region_name !== '') {
          $tid = $this->getExistingTermId('region', $region_name, $tax_lang);
          if ($tid !== NULL) {
            $values['field_region'] = ['target_id' => $tid];
          }
          else {
            $this->logger()->warning(sprintf('Row %d: region term not found (skipped): %s', $row_num, $region_name));
          }
        }

        $sector_name = $get($indexes, $row, 'field_sector_name');
        if ($sector_name !== '') {
          $tid = $this->getExistingTermId('sector', $sector_name, $tax_lang);
          if ($tid !== NULL) {
            $values['field_sector'] = ['target_id' => $tid];
          }
          else {
            $this->logger()->warning(sprintf('Row %d: sector term not found (skipped): %s', $row_num, $sector_name));
          }
        }

        $cta_link = $this->buildLinkValue($get($indexes, $row, 'field_cta_link_uri'), $get($indexes, $row, 'field_cta_link_title'));
        if ($cta_link !== NULL) {
          $values['field_cta_link'] = [$cta_link];
        }

        $external_link = $this->buildLinkValue($get($indexes, $row, 'field_external_link_uri'), $get($indexes, $row, 'field_external_link_title'));
        if ($external_link !== NULL) {
          $values['field_external_link'] = [$external_link];
        }

        $source_link = $this->buildLinkValue($get($indexes, $row, 'field_source_link_uri'), $get($indexes, $row, 'field_source_link_title'));
        if ($source_link !== NULL) {
          $values['field_source_link'] = [$source_link];
        }

        $logo_mid = $get($indexes, $row, 'field_logo_mid');
        if ($logo_mid !== '' && ctype_digit($logo_mid)) {
          $values['field_logo'] = ['target_id' => (int) $logo_mid];
        }

        $hero_mid = $get($indexes, $row, 'field_hero_image_mid');
        if ($hero_mid !== '' && ctype_digit($hero_mid)) {
          $values['field_hero_image'] = ['target_id' => (int) $hero_mid];
        }

        $partner_mids = $this->parseIntList($get($indexes, $row, 'field_partner_logos_mids'));
        if ($partner_mids !== []) {
          $values['field_partner_logos'] = array_map(static fn (int $mid): array => ['target_id' => $mid], $partner_mids);
        }

        $why_items = $this->decodeJsonList($get($indexes, $row, 'field_why_matters_json'), 'field_why_matters_json');
        if ($why_items !== []) {
          $values['field_why_matters'] = $this->createTitleBodyParagraphs('benefit_item', $why_items, $row_lang);
        }

        $help_items = $this->decodeJsonList($get($indexes, $row, 'field_how_we_help_json'), 'field_how_we_help_json');
        if ($help_items !== []) {
          $values['field_how_we_help'] = $this->createTitleBodyParagraphs('help_item', $help_items, $row_lang);
        }

        $steps = $this->decodeJsonList($get($indexes, $row, 'field_process_steps_json'), 'field_process_steps_json');
        if ($steps !== []) {
          $values['field_process_steps'] = $this->createTitleBodyParagraphs('platform_process_step', $steps, $row_lang);
        }

        $reqs = $this->decodeJsonList($get($indexes, $row, 'field_requirements_json'), 'field_requirements_json');
        if ($reqs !== []) {
          $values['field_requirements'] = $this->createRequirementsParagraphs($reqs, $row_lang);
        }

        $resources = $this->decodeResourcesJson($get($indexes, $row, 'field_resources_json'));
        if ($resources !== []) {
          $values['field_resources'] = $this->createPlatformResourceParagraphs($resources, $row_lang);
        }

        $related_raw = $get($indexes, $row, 'field_related_platforms_titles');
        if ($related_raw !== '') {
          $refs = $this->resolveRelatedPlatformTargets($related_raw, $related_lang);
          if ($refs !== []) {
            $values['field_related_platforms'] = $refs;
          }
        }

        $meta_json = $get($indexes, $row, 'field_meta_tags_json');
        if ($meta_json !== '') {
          $decoded = json_decode($meta_json, TRUE);
          if (is_array($decoded) && $decoded !== []) {
            $encoded = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $values['field_meta_tags'] = [['value' => $encoded]];
          }
        }

        $alias = $get($indexes, $row, 'path_alias');

        if ($is_translation) {
          if ($tr === NULL || $base === NULL) {
            throw new \InvalidArgumentException('Translation state is inconsistent.');
          }
          unset($values['field_featured']);
          foreach (self::TRANSLATION_VALUE_KEYS as $key) {
            if (!array_key_exists($key, $values)) {
              continue;
            }
            if (!$tr->hasField($key)) {
              continue;
            }
            $tr->set($key, $values[$key]);
          }
          $base->save();
          $nid = (int) $base->id();
          $this->io()->writeln(sprintf('Saved translation %s for nid=%d: %s', $row_lang, $nid, $title));
        }
        elseif ($node instanceof NodeInterface) {
          foreach ($values as $key => $value) {
            $node->set($key, $value);
          }
          $node->save();
          $nid = (int) $node->id();
          $this->io()->writeln(sprintf('Updated nid=%d: %s', $nid, $title));
        }
        else {
          $node = $node_storage->create($values);
          $node->save();
          $nid = (int) $node->id();
          $this->io()->writeln(sprintf('Created nid=%d: %s', $nid, $title));
        }

        if ($alias !== '') {
          $this->replaceNodePathAlias($nid, $row_lang, '/' . ltrim($alias, '/'));
        }

        $saved++;
      }
      catch (\Throwable $e) {
        $msg = sprintf('Row %d: %s', $row_num, $e->getMessage());
        $this->logger()->error($msg);
        $this->io()->writeln(sprintf('[error] %s', $msg));
        \Drupal::logger('motaded_custom')->error($msg);
      }
    }

    fclose($handle);
    if ($dry_run) {
      $this->logger()->success(sprintf('Dry-run: %d row(s) OK.', $saved));
    }
    else {
      $this->logger()->success(sprintf('Processed %d platform row(s); skipped %d.', $saved, $skipped));
    }
  }

  /**
   * @param string[] $row
   * @return string[]
   */
  private function padRow(array $row, int $count): array {
    while (count($row) < $count) {
      $row[] = '';
    }
    return $row;
  }

  private function findPlatformByTitle(string $title, string $langcode): ?int {
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'platform')
      ->condition('title', $title)
      ->condition('langcode', $langcode)
      ->sort('nid', 'DESC')
      ->range(0, 1)
      ->execute();
    if (!$nids) {
      return NULL;
    }
    return (int) reset($nids);
  }

  private function deleteParagraphFieldItems(NodeInterface $node, string $fieldName): void {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return;
    }
    $ids = [];
    foreach ($node->get($fieldName) as $item) {
      $p = $item->entity;
      if ($p instanceof ParagraphInterface) {
        $ids[] = (int) $p->id();
      }
    }
    $node->set($fieldName, NULL);
    foreach ($ids as $pid) {
      $p = Paragraph::load($pid);
      if ($p instanceof ParagraphInterface) {
        $p->delete();
      }
    }
  }

  /**
   * @return list<array{target_id:int}>
   */
  private function resolveRelatedPlatformTargets(string $rawTitles, string $langcode): array {
    $out = [];
    foreach (preg_split('/\s*\|\s*/', $rawTitles, -1, PREG_SPLIT_NO_EMPTY) as $t) {
      $t = trim($t);
      if ($t === '') {
        continue;
      }
      $nid = $this->findPlatformByTitle($t, $langcode);
      if ($nid === NULL) {
        $this->logger()->warning(sprintf('Related platform title not found: %s', $t));
        continue;
      }
      $out[] = ['target_id' => $nid];
    }
    return $out;
  }

  private function replaceNodePathAlias(int $nid, string $langcode, string $alias): void {
    $alias = '/' . ltrim($alias, '/');
    $system_path = '/node/' . $nid;
    $storage = $this->entityTypeManager->getStorage('path_alias');
    $existing = $storage->loadByProperties([
      'path' => $system_path,
      'langcode' => $langcode,
    ]);
    foreach ($existing as $entity) {
      $entity->delete();
    }
    PathAlias::create([
      'path' => $system_path,
      'alias' => $alias,
      'langcode' => $langcode,
    ])->save();
  }

  private function normalizeBodyHtml(string $raw): string {
    $raw = trim($raw);
    if ($raw === '') {
      return '';
    }
    if (str_contains($raw, '<') && preg_match('/<[a-z][\s\S]*>/i', $raw)) {
      return $raw;
    }
    return '<p>' . htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  }

  /**
   * @return array<int, array<string, mixed>>
   */
  private function decodeJsonList(string $raw, string $colName): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $decoded = json_decode($raw, TRUE);
    if (!is_array($decoded)) {
      throw new \InvalidArgumentException('Invalid JSON in ' . $colName);
    }
    return array_values(array_filter($decoded, static fn ($v): bool => is_array($v)));
  }

  /**
   * @return list<array{title: string, uri: string, link_title?: string, icon?: string}>
   */
  private function decodeResourcesJson(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $decoded = json_decode($raw, TRUE);
    if (!is_array($decoded)) {
      throw new \InvalidArgumentException('Invalid JSON in field_resources_json');
    }
    $out = [];
    foreach ($decoded as $item) {
      if (!is_array($item)) {
        continue;
      }
      $title = trim((string) ($item['title'] ?? ''));
      $uri = trim((string) ($item['uri'] ?? ''));
      if ($title === '' || $uri === '') {
        continue;
      }
      $out[] = [
        'title' => $title,
        'uri' => $uri,
        'link_title' => trim((string) ($item['link_title'] ?? $title)),
        'icon' => trim((string) ($item['icon'] ?? '')),
      ];
    }
    return $out;
  }

  /**
   * @param list<array{title: string, uri: string, link_title?: string, icon?: string}> $items
   *
   * @return array<int, array{target_id:int, target_revision_id:int}>
   */
  private function createPlatformResourceParagraphs(array $items, string $langcode): array {
    $out = [];
    foreach ($items as $item) {
      $link = $this->buildLinkValue($item['uri'], $item['link_title'] ?? $item['title']);
      if ($link === NULL) {
        continue;
      }
      $values = [
        'type' => 'platform_resource',
        'langcode' => $langcode,
        'field_title' => $item['title'],
        'field_link' => [$link],
      ];
      if (($item['icon'] ?? '') !== '') {
        $values['field_icon'] = $item['icon'];
      }
      $p = Paragraph::create($values);
      $p->save();
      $out[] = [
        'target_id' => (int) $p->id(),
        'target_revision_id' => (int) $p->getRevisionId(),
      ];
    }
    return $out;
  }

  /**
   * @return array{uri: string, title: string}|null
   */
  private function buildLinkValue(string $uri, string $title): ?array {
    $uri = trim($uri);
    if ($uri === '') {
      return NULL;
    }
    if (!str_starts_with($uri, 'internal:')
      && !str_starts_with($uri, 'entity:')
      && !str_starts_with($uri, 'mailto:')
      && !str_starts_with($uri, 'tel:')
      && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $uri)) {
      $uri = 'https://' . ltrim($uri, '/');
    }

    return ['uri' => $uri, 'title' => trim($title)];
  }

  /**
   * @return int[]
   */
  private function parseIntList(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $out = [];
    foreach (preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $p) {
      if (ctype_digit($p)) {
        $out[] = (int) $p;
      }
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $items
   *
   * @return array<int, array{target_id:int, target_revision_id:int}>
   */
  private function createTitleBodyParagraphs(string $paragraphType, array $items, string $langcode): array {
    $out = [];
    foreach ($items as $item) {
      $title = trim((string) ($item['title'] ?? ''));
      $body = trim((string) ($item['body'] ?? ''));
      if ($title === '' && $body === '') {
        continue;
      }

      $p = Paragraph::create([
        'type' => $paragraphType,
        'langcode' => $langcode,
        'field_title' => $title,
        'field_body' => [
          'value' => $this->normalizeBodyHtml($body),
          'format' => 'basic_html',
        ],
      ]);
      $icon = trim((string) ($item['icon'] ?? ''));
      if ($icon !== '' && $p->hasField('field_icon')) {
        $p->set('field_icon', $icon);
      }
      $p->save();

      $out[] = [
        'target_id' => (int) $p->id(),
        'target_revision_id' => (int) $p->getRevisionId(),
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $items
   *
   * @return array<int, array{target_id:int, target_revision_id:int}>
   */
  private function createRequirementsParagraphs(array $items, string $langcode): array {
    $out = [];
    foreach ($items as $item) {
      $content = trim((string) ($item['content'] ?? ''));
      $media_mid = $item['media_mid'] ?? NULL;

      if ($content === '' && $media_mid === NULL) {
        continue;
      }

      $values = [
        'type' => 'requirements',
        'langcode' => $langcode,
        'field_content' => $content,
      ];
      if (is_int($media_mid) && $media_mid > 0) {
        $values['field_media'] = ['target_id' => $media_mid];
      }
      elseif (is_string($media_mid) && ctype_digit($media_mid)) {
        $values['field_media'] = ['target_id' => (int) $media_mid];
      }

      $p = Paragraph::create($values);
      $p->save();

      $out[] = [
        'target_id' => (int) $p->id(),
        'target_revision_id' => (int) $p->getRevisionId(),
      ];
    }
    return $out;
  }

  private function getExistingTermId(string $vid, string $name, string $langcode): ?int {
    $name = trim($name);
    if ($name === '') {
      return NULL;
    }
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->condition('langcode', $langcode)
      ->range(0, 1)
      ->execute();
    if ($tids) {
      return (int) reset($tids);
    }
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    return $tids ? (int) reset($tids) : NULL;
  }

}
