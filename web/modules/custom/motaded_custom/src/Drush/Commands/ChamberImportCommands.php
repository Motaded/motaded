<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import / upsert chamber nodes from CSV (local + prod).
 */
final class ChamberImportCommands extends DrushCommands {

  private const RELATIONSHIP_FOCUS_MAX = 255;

  /** @var list<string> */
  private const TYPE_KEYS = [
    'chamber_of_commerce',
    'business_council',
    'trade_association',
  ];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Import chambers from CSV into node type "chamber".
   *
   * Upsert by exact title + langcode for default language, or attach/update
   * translations when langcode differs: then translation_source_title must match
   * the English (site default) node title.
   */
  #[CLI\Command(name: 'motaded:chamber-import-csv', aliases: ['mcic'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../chamber_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'upsert', description: 'When true (default), update existing chamber with same title+langcode.')]
  #[CLI\Option(name: 'skip-existing', description: 'When upsert is false: skip row if a published chamber with the same title exists.')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Option(name: 'create-country-terms', description: 'When true (default), create missing country taxonomy terms (vocabulary: country). When false, require an existing term name match.')]
  #[CLI\Usage(name: 'drush mcic', description: 'Upsert from project root chamber_import_ready.csv')]
  #[CLI\Usage(name: 'drush mcic ../chamber_import_ar_ready.csv', description: 'Upsert Arabic translations (requires EN chambers; translation_source_title column).')]
  public function import(?string $path = NULL, array $options = [
    'upsert' => TRUE,
    'skip-existing' => TRUE,
    'dry-run' => FALSE,
    'create-country-terms' => TRUE,
  ]): void {
    $drupal_root = \Drupal::root();
    $default = $drupal_root . '/../chamber_import_ready.csv';
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
    $create_country_terms = filter_var($options['create-country-terms'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error('Could not open CSV.');
      return;
    }

    $default_lang = $this->languageManager->getDefaultLanguage()->getId();
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
    foreach (['title', 'status', 'langcode', 'body', 'body_summary', 'field_short_description', 'field_country_name', 'field_type', 'field_relationship_focus', 'field_external_link_uri', 'field_priority', 'field_featured'] as $col) {
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
      $existing = $is_translation ? NULL : $this->findChamberByTitle($title, $row_lang);

      if (!$is_translation && !$upsert && $skip_existing && !$dry_run) {
        $published = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'chamber')
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
        $teaser = $get($indexes, $row, 'field_short_description');
        $focus = $get($indexes, $row, 'field_relationship_focus');
        if ($teaser === '') {
          throw new \InvalidArgumentException('Empty field_short_description.');
        }
        if ($focus === '') {
          throw new \InvalidArgumentException('Empty field_relationship_focus.');
        }

        $body_raw = $get($indexes, $row, 'body');
        $summary_raw = $get($indexes, $row, 'body_summary');
        if ($body_raw === '') {
          $body_raw = $this->defaultChamberOverviewHtml($title, $teaser, $focus);
        }
        if ($summary_raw === '') {
          $summary_raw = $this->truncateUtf8(trim($teaser . ' ' . $this->truncateAtWord($focus, 200)), 480);
        }

        $type_key = $get($indexes, $row, 'field_type');
        if (!in_array($type_key, self::TYPE_KEYS, TRUE)) {
          throw new \InvalidArgumentException('Invalid field_type: ' . $type_key);
        }

        $country_name = $get($indexes, $row, 'field_country_name');
        if ($country_name === '') {
          throw new \InvalidArgumentException('Empty field_country_name.');
        }

        $uri_raw = $get($indexes, $row, 'field_external_link_uri');
        if ($uri_raw === '') {
          throw new \InvalidArgumentException('Empty field_external_link_uri.');
        }

        $stats_raw = $get($indexes, $row, 'field_chamber_featured_stats_json');
        $stats_lines = $this->decodeStatsJson($stats_raw);

        $meta_raw = $get($indexes, $row, 'field_meta_tags_json');
        if ($dry_run) {
          if ($meta_raw !== '') {
            $decoded = json_decode($meta_raw, TRUE);
            if (!is_array($decoded)) {
              throw new \InvalidArgumentException('Invalid JSON in field_meta_tags_json');
            }
          }
          if ($is_translation) {
            $base_nid_check = $this->findChamberByTitle($translation_source, $default_lang);
            if ($base_nid_check === NULL) {
              throw new \InvalidArgumentException('Base chamber not found for translation_source_title: ' . $translation_source);
            }
          }
          $this->io()->writeln(sprintf('[dry-run] Row %d OK: %s', $row_num, $title));
          $saved++;
          continue;
        }

        if ($create_country_terms) {
          $country_tid = $this->getOrCreateTerm('country', $country_name, $row_lang);
        }
        else {
          $country_tid = $this->getExistingTermId('country', $country_name, $row_lang);
          if ($country_tid === NULL) {
            throw new \InvalidArgumentException('Country term not found: ' . $country_name);
          }
        }

        $priority = (int) ($get($indexes, $row, 'field_priority') ?: '0');
        $featured = (bool) (int) ($get($indexes, $row, 'field_featured') ?: '0');

        $link = $this->buildLinkValue(
          $uri_raw,
          $get($indexes, $row, 'field_external_link_title')
        );
        if ($link === NULL) {
          throw new \InvalidArgumentException('Could not build external link.');
        }
        $link['options'] = [
          'attributes' => [
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
          ],
        ];

        $values = [
          'type' => 'chamber',
          'title' => $title,
          'langcode' => $row_lang,
          'status' => $status,
          'uid' => 1,
          'promote' => 0,
          'body' => [
            'value' => $this->normalizeBodyHtml($body_raw),
            'summary' => $this->normalizeBodyHtml($summary_raw),
            'format' => 'basic_html',
          ],
          'field_short_description' => $teaser,
          'field_relationship_focus' => $this->truncateUtf8($this->truncateAtWord($focus, 200), self::RELATIONSHIP_FOCUS_MAX),
          'field_type' => $type_key,
          'field_country' => ['target_id' => $country_tid],
          'field_priority' => $priority,
          'field_featured' => $featured,
          'field_external_link' => [$link],
        ];

        $logo_mid = $get($indexes, $row, 'field_logo_mid');
        if ($logo_mid !== '' && ctype_digit($logo_mid)) {
          $values['field_logo'] = ['target_id' => (int) $logo_mid];
        }

        if ($stats_lines !== []) {
          $stats_field = [];
          foreach ($stats_lines as $line) {
            $stats_field[] = ['value' => $line];
          }
          $values['field_chamber_featured_stats'] = $stats_field;
        }
        else {
          $values['field_chamber_featured_stats'] = [];
        }

        if ($meta_raw !== '') {
          $decoded = json_decode($meta_raw, TRUE);
          if (is_array($decoded) && $decoded !== []) {
            $encoded = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $values['field_meta_tags'] = [['value' => $encoded]];
          }
        }

        $alias = $get($indexes, $row, 'path_alias');

        if ($is_translation) {
          $base_nid = $this->findChamberByTitle($translation_source, $default_lang);
          if ($base_nid === NULL) {
            throw new \InvalidArgumentException('Base chamber not found for translation_source_title: ' . $translation_source);
          }
          $node = $node_storage->load($base_nid);
          if (!$node instanceof NodeInterface) {
            throw new \InvalidArgumentException('Could not load base chamber node.');
          }
          if (!$node->isTranslatable()) {
            throw new \InvalidArgumentException('Chamber nodes are not translatable. Enable content translation for the chamber bundle (language.content_settings.node.chamber) and re-import config.');
          }
          if (!$node->hasTranslation($row_lang)) {
            $node->addTranslation($row_lang, [
              'title' => $title,
              'status' => $status,
            ]);
          }
          $tr = $node->getTranslation($row_lang);
          foreach ([
            'title',
            'status',
            'body',
            'field_short_description',
            'field_relationship_focus',
            'field_type',
            'field_country',
            'field_external_link',
            'field_meta_tags',
            'field_chamber_featured_stats',
            'field_logo',
          ] as $field_name) {
            if (!array_key_exists($field_name, $values)) {
              continue;
            }
            if (!$tr->hasField($field_name)) {
              continue;
            }
            $tr->set($field_name, $values[$field_name]);
          }
          $node->save();
          $nid = (int) $node->id();
          $this->io()->writeln(sprintf('Saved translation %s for nid=%d: %s', $row_lang, $nid, $title));
        }
        else {
          $node = $existing ? $node_storage->load($existing) : NULL;
          if ($node instanceof NodeInterface) {
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
      $this->logger()->success(sprintf('Processed %d chamber row(s); skipped %d.', $saved, $skipped));
    }
  }

  private function truncateAtWord(string $text, int $max): string {
    $text = trim($text);
    if ($text === '') {
      return '';
    }
    if (!function_exists('mb_strlen') || !function_exists('mb_substr') || !function_exists('mb_strrpos')) {
      return $this->truncateUtf8($text, $max);
    }
    if (mb_strlen($text, 'UTF-8') <= $max) {
      return $text;
    }
    $chunk = mb_substr($text, 0, $max, 'UTF-8');
    $lastSpace = mb_strrpos($chunk, ' ', 0, 'UTF-8');
    if ($lastSpace !== FALSE && $lastSpace > 24) {
      return rtrim(mb_substr($chunk, 0, $lastSpace, 'UTF-8'), '.,;:! ');
    }
    return rtrim($chunk, '.,;:! ');
  }

  private function defaultChamberOverviewHtml(string $title, string $teaser, string $focusFull): string {
    $title = trim($title);
    $teaser = trim($teaser);
    $focusFull = trim($focusFull);
    $t = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $s = htmlspecialchars($teaser, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $f = htmlspecialchars($focusFull, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $html = '<p><strong>' . $t . '</strong>. ' . $s . '</p>';
    if ($f !== '') {
      $html .= '<p>' . $f . '</p>';
    }
    return $html;
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

  private function truncateUtf8(string $text, int $max): string {
    if ($max < 1) {
      return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($text, 'UTF-8') <= $max) {
        return $text;
      }
      return mb_substr($text, 0, $max, 'UTF-8');
    }
    return strlen($text) > $max ? substr($text, 0, $max) : $text;
  }

  /**
   * @param string[] $row
   *
   * @return string[]
   */
  private function padRow(array $row, int $count): array {
    while (count($row) < $count) {
      $row[] = '';
    }
    return $row;
  }

  private function findChamberByTitle(string $title, string $langcode): ?int {
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'chamber')
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

  /**
   * @return list<string>
   */
  private function decodeStatsJson(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $decoded = json_decode($raw, TRUE);
    if (!is_array($decoded)) {
      throw new \InvalidArgumentException('Invalid JSON in field_chamber_featured_stats_json');
    }
    $out = [];
    foreach ($decoded as $item) {
      if (!is_string($item)) {
        continue;
      }
      $t = trim($item);
      if ($t !== '') {
        $out[] = $t;
      }
      if (count($out) >= 3) {
        break;
      }
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

  /**
   * @return array{uri: string, title: string, options?: array<string, mixed>}|null
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
   * Resolves a taxonomy term by exact name; creates it if missing (same as events import).
   */
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
    $this->io()->writeln(sprintf('Created taxonomy term %s / %s (tid=%d)', $vid, $name, (int) $term->id()));
    return (int) $term->id();
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
