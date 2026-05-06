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
 * Import event nodes from CSV (e.g. events_import_ready.csv).
 */
final class EventsImportCommands extends DrushCommands {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Import events from CSV into node type "event".
   */
  #[CLI\Command(name: 'motaded:events-import-csv', aliases: ['meic'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../events_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'skip-existing', description: 'Skip row when a published event with the same title already exists.')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Usage(name: 'drush meic', description: 'Import from project root events_import_ready.csv')]
  #[CLI\Usage(name: 'drush meic /path/to.csv', description: 'Import a specific file.')]
  public function import(?string $path = NULL, array $options = [
    'skip-existing' => TRUE,
    'dry-run' => FALSE,
  ]): void {
    $drupal_root = $this->drupalRoot();
    $default = $drupal_root . '/../events_import_ready.csv';
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

    $required_cols = ['title', 'status', 'langcode', 'body', 'field_event_start', 'field_event_end', 'field_event_type_name'];
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
          ->condition('type', 'event')
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
        $body_html = $this->normalizeBodyHtml($body_raw);

        $start_raw = $get($indexes, $row, 'field_event_start');
        $end_raw = $get($indexes, $row, 'field_event_end');
        $start_ts = $this->normalizeDatetime($start_raw, FALSE);
        $end_ts = $this->normalizeDatetime($end_raw, TRUE);

        if ($dry_run) {
          $this->io()->writeln(sprintf('[dry-run] Row %d OK: %s (%s – %s)', $row_num, $title, $start_ts, $end_ts));
          $created++;
          continue;
        }

        $event_type_tid = $this->getOrCreateTerm('event_type', $get($indexes, $row, 'field_event_type_name'), $row_lang);
        $category_tid = $this->getOrCreateTerm('taxonomy', $get($indexes, $row, 'field_category_name'), $row_lang);
        $region_tid = $this->getOrCreateTerm('region', $get($indexes, $row, 'field_region_name'), $row_lang);

        $location = $get($indexes, $row, 'field_location');
        $featured = (bool) (int) ($get($indexes, $row, 'field_featured') ?: '0');
        $meeting = (bool) (int) ($get($indexes, $row, 'field_meeting_enabled') ?: '0');
        $cta_type = $get($indexes, $row, 'field_cta_type') ?: 'register';
        if (!in_array($cta_type, ['register', 'meeting', 'external'], TRUE)) {
          $cta_type = 'register';
        }

        $cta_link = $this->buildLinkValue($get($indexes, $row, 'field_cta_link_uri'), $get($indexes, $row, 'field_cta_link_title'));
        $secondary_link = $this->buildLinkValue($get($indexes, $row, 'field_secondary_cta_uri'), $get($indexes, $row, 'field_secondary_cta_title'));

        $tag_ids = $this->resolveTags($get($indexes, $row, 'field_tags_names'), $row_lang);

        $values = [
          'type' => 'event',
          'title' => $title,
          'langcode' => $row_lang,
          'status' => $status,
          'uid' => 1,
          'body' => [
            'value' => $body_html,
            'summary' => $summary,
            'format' => 'basic_html',
          ],
          'field_event_start' => $start_ts,
          'field_event_end' => $end_ts,
          'field_event_type' => ['target_id' => $event_type_tid],
          'field_category' => ['target_id' => $category_tid],
          'field_region' => ['target_id' => $region_tid],
          'field_location' => $location,
          'field_featured' => $featured,
          'field_meeting_enabled' => $meeting,
          'field_cta_type' => $cta_type,
        ];

        if ($cta_link !== NULL) {
          $values['field_cta_link'] = [$cta_link];
        }
        if ($secondary_link !== NULL) {
          $values['field_secondary_cta'] = [$secondary_link];
        }
        if ($tag_ids !== []) {
          $values['field_tags'] = array_map(static fn (int $tid): array => ['target_id' => $tid], $tag_ids);
        }

        // Optional media MIDs (single).
        $hero_mid = $get($indexes, $row, 'field_hero_image_mid');
        if ($hero_mid !== '' && ctype_digit($hero_mid)) {
          $values['field_hero_image'] = ['target_id' => (int) $hero_mid];
        }
        $feat_mid = $get($indexes, $row, 'field_featured_image_mid');
        if ($feat_mid !== '' && ctype_digit($feat_mid)) {
          $values['field_featured_image'] = ['target_id' => (int) $feat_mid];
        }

        $partner_mids = $this->parseIntList($get($indexes, $row, 'field_partner_logos_mids'));
        if ($partner_mids !== []) {
          $values['field_partner_logos'] = array_map(static fn (int $mid): array => ['target_id' => $mid], $partner_mids);
        }
        $gallery_mids = $this->parseIntList($get($indexes, $row, 'field_gallery_mids'));
        if ($gallery_mids !== []) {
          $values['field_gallery'] = array_map(static fn (int $mid): array => ['target_id' => $mid], $gallery_mids);
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
      $this->logger()->success('Imported @n event(s); skipped @s.', [
        '@n' => (string) $created,
        '@s' => (string) $skipped,
      ]);
    }
  }

  /**
   * Drupal root path (…/web).
   */
  private function drupalRoot(): string {
    return \Drupal::root();
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
   * Date-only YYYY-MM-DD gets time; full ISO strings preserved.
   */
  private function normalizeDatetime(string $raw, bool $endOfDay): string {
    $raw = trim($raw);
    if ($raw === '') {
      throw new \InvalidArgumentException('Empty datetime.');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
      return $raw . ($endOfDay ? 'T17:00:00' : 'T09:00:00');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?/', $raw)) {
      return str_replace(' ', 'T', trim($raw));
    }
    throw new \InvalidArgumentException('Invalid datetime: ' . $raw);
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
  private function resolveTags(string $csv_tags, string $langcode): array {
    if ($csv_tags === '') {
      return [];
    }
    $parts = preg_split('/\s*,\s*/', $csv_tags, -1, PREG_SPLIT_NO_EMPTY);
    $tids = [];
    foreach ($parts as $name) {
      $tids[] = $this->getOrCreateTerm('tags', $name, $langcode);
    }
    return $tids;
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
