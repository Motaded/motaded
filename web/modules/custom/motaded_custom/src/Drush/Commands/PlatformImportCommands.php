<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import platform nodes from CSV.
 */
final class PlatformImportCommands extends DrushCommands {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Import platforms from CSV into node type "platform".
   */
  #[CLI\Command(name: 'motaded:platform-import-csv', aliases: ['mpic'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../platform_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'skip-existing', description: 'Skip row when a published platform with the same title already exists.')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Usage(name: 'drush mpic', description: 'Import from project root platform_import_ready.csv')]
  #[CLI\Usage(name: 'drush mpic /path/to.csv', description: 'Import a specific file.')]
  public function import(?string $path = NULL, array $options = [
    'skip-existing' => TRUE,
    'dry-run' => FALSE,
  ]): void {
    $drupal_root = \Drupal::root();
    $default = $drupal_root . '/../platform_import_ready.csv';
    $path = $path ?? $default;

    // Relative filenames resolve against repo root (parent of web/).
    if ($path !== '' && $path[0] !== '/' && !preg_match('#^[a-zA-Z]:\\\\#', $path)) {
      $candidate = $drupal_root . '/../' . $path;
      if (is_readable($candidate)) {
        $path = $candidate;
      }
    }

    $resolved = $this->fileSystem->realpath($path);
    $path = $resolved ?: $path;
    if (!is_readable($path)) {
      $this->logger()->error('Cannot read CSV: @path (tip: put file in project root or pass absolute path)', ['@path' => $path]);
      return;
    }

    $skip_existing = filter_var($options['skip-existing'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error('Could not open CSV.');
      return;
    }

    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $header = fgetcsv($handle);
    if ($header === FALSE || $header === []) {
      fclose($handle);
      $this->logger()->error('Empty CSV.');
      return;
    }

    // Strip BOM from first header cell if present.
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }

    $indexes = array_flip($header);

    $required_cols = ['title', 'status', 'langcode', 'body', 'body_summary'];
    foreach ($required_cols as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error('Missing column in CSV: @c', ['@c' => $col]);
        return;
      }
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $created = 0;
    $skipped = 0;
    $row_num = 1;

    while (($row = fgetcsv($handle)) !== FALSE) {
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

      if ($skip_existing && !$dry_run) {
        $existing = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'platform')
          ->condition('title', $title)
          ->condition('status', NodeInterface::PUBLISHED)
          ->range(0, 1)
          ->execute();
        if ($existing) {
          $this->io()->writeln(sprintf('Skip row %d (exists): %s', $row_num, $title));
          $skipped++;
          continue;
        }
      }

      try {
        $status = (int) ($get($indexes, $row, 'status') ?: '1');
        $row_lang = $get($indexes, $row, 'langcode') ?: $langcode;

        $summary = $get($indexes, $row, 'body_summary');
        $body_raw = $get($indexes, $row, 'body');
        if ($body_raw === '') {
          throw new \InvalidArgumentException('Empty body.');
        }
        if ($summary === '') {
          throw new \InvalidArgumentException('Empty body_summary.');
        }

        if ($dry_run) {
          // Validate JSON columns early.
          $this->decodeJsonList($get($indexes, $row, 'field_why_matters_json'), 'field_why_matters_json');
          $this->decodeJsonList($get($indexes, $row, 'field_how_we_help_json'), 'field_how_we_help_json');
          $this->decodeJsonList($get($indexes, $row, 'field_process_steps_json'), 'field_process_steps_json');
          $this->decodeJsonList($get($indexes, $row, 'field_requirements_json'), 'field_requirements_json');

          $this->io()->writeln(sprintf('[dry-run] Row %d OK: %s', $row_num, $title));
          $created++;
          continue;
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
          $values['field_category'] = ['target_id' => $this->getOrCreateTerm('platform_category', $category_name, $row_lang)];
        }

        $region_name = $get($indexes, $row, 'field_region_name');
        if ($region_name !== '') {
          $values['field_region'] = ['target_id' => $this->getOrCreateTerm('region', $region_name, $row_lang)];
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

        // Paragraphs.
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

        $alias = $get($indexes, $row, 'path_alias');

        $node = $node_storage->create($values);
        $node->save();
        $nid = (int) $node->id();

        if ($alias !== '') {
          $alias = '/' . ltrim($alias, '/');
          PathAlias::create([
            'path' => '/node/' . $nid,
            'alias' => $alias,
            'langcode' => $row_lang,
          ])->save();
        }

        $this->io()->writeln(sprintf('Created nid=%d: %s', $nid, $title));
        $created++;
      }
      catch (\Throwable $e) {
        $this->logger()->error('Row @n: @msg', ['@n' => $row_num, '@msg' => $e->getMessage()]);
      }
    }

    fclose($handle);
    if ($dry_run) {
      $this->logger()->success('Dry-run: @n row(s) OK (nothing saved).', ['@n' => (string) $created]);
    }
    else {
      $this->logger()->success('Imported @n platform(s); skipped @s.', [
        '@n' => (string) $created,
        '@s' => (string) $skipped,
      ]);
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

  private function getOrCreateTerm(string $vid, string $name, string $langcode): int {
    $name = trim($name);
    if ($name === '') {
      throw new \InvalidArgumentException('Empty taxonomy term name for vocabulary ' . $vid);
    }
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    if ($tids) {
      return (int) reset($tids);
    }
    $term = Term::create([
      'vid' => $vid,
      'name' => $name,
      'langcode' => $langcode,
    ]);
    $term->save();
    $this->io()->writeln(sprintf('Created term %s / %s (tid=%d)', $vid, $name, (int) $term->id()));
    return (int) $term->id();
  }

}
