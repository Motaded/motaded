<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
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

        $category_tid = 0;
        $category_name = $get($indexes, $row, 'field_category_name');
        if ($category_name !== '') {
          $category_tid = $this->getOrCreateTerm('taxonomy', $category_name, $row_lang);
        }

        $region_tid = 0;
        $region_name = $get($indexes, $row, 'field_region_name');
        if ($region_name !== '') {
          $region_tid = $this->getOrCreateTerm('region', $region_name, $row_lang);
        }

        $city_tid = 0;
        $city_name = $get($indexes, $row, 'field_city_name');
        if ($city_name !== '') {
          $city_tid = $this->getOrCreateTerm('city', $city_name, $row_lang);
        }

        $country_tid = 0;
        $country_name = $get($indexes, $row, 'field_country_name');
        if ($country_name !== '') {
          $country_tid = $this->getOrCreateTerm('country', $country_name, $row_lang);
        }
        $organizer_ids = $this->resolveOrganizers($get($indexes, $row, 'field_organizer_names'), $row_lang);

        $location = $get($indexes, $row, 'field_location');
        $featured = (bool) (int) ($get($indexes, $row, 'field_featured') ?: '0');
        $meeting = (bool) (int) ($get($indexes, $row, 'field_meeting_enabled') ?: '0');
        $cta_type = $this->normalizeCtaType($get($indexes, $row, 'field_cta_type') ?: 'register');
        if (!in_array($cta_type, ['register', 'meeting', 'external'], TRUE)) {
          $cta_type = 'register';
        }
        $event_format = $this->normalizeEventFormat($get($indexes, $row, 'field_event_format'));
        $price_range = $this->normalizePriceRange($get($indexes, $row, 'field_price_range'));
        $attendance_type = $this->normalizeAttendanceType($get($indexes, $row, 'field_attendance_type'));

        $cta_link = $this->buildLinkValue($get($indexes, $row, 'field_cta_link_uri'), $get($indexes, $row, 'field_cta_link_title'));
        $secondary_link = $this->buildLinkValue($get($indexes, $row, 'field_secondary_cta_uri'), $get($indexes, $row, 'field_secondary_cta_title'));
        $official_website = $this->buildLinkValue($get($indexes, $row, 'field_official_website'), $get($indexes, $row, 'field_official_website_title') ?: 'Official website');

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
          'field_location' => $location,
          'field_featured' => $featured,
          'field_meeting_enabled' => $meeting,
          'field_cta_type' => $cta_type,
        ];
        if ($category_tid > 0) {
          $values['field_category'] = ['target_id' => $category_tid];
        }
        if ($region_tid > 0) {
          $values['field_region'] = ['target_id' => $region_tid];
        }
        if ($city_tid > 0) {
          $values['field_city'] = ['target_id' => $city_tid];
        }
        if ($country_tid > 0) {
          $values['field_country'] = ['target_id' => $country_tid];
        }
        if ($event_format !== '') {
          $values['field_event_format'] = $event_format;
        }
        if ($price_range !== '') {
          $values['field_price_range'] = $price_range;
        }
        if ($attendance_type !== '') {
          $values['field_attendance_type'] = $attendance_type;
        }
        if ($organizer_ids !== []) {
          $values['field_organizer'] = array_map(static fn (int $tid): array => ['target_id' => $tid], $organizer_ids);
        }
        if ($official_website !== NULL) {
          $values['field_official_website'] = [$official_website];
        }

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
        // Drush logger uses PSR-3 placeholders: {placeholder}.
        $this->logger()->error('Row {n}: {msg}', ['n' => $row_num, 'msg' => $e->getMessage()]);
      }
    }

    fclose($handle);
    if ($dry_run) {
      $this->logger()->success('Dry-run: @n row(s) OK (nothing saved).', ['@n' => (string) $created]);
    }
    else {
      $this->logger()->success('Imported {n} event(s); skipped {s}.', [
        'n' => (string) $created,
        's' => (string) $skipped,
      ]);
    }
  }

  /**
   * Import Event content paragraphs (agenda/why attend/etc) from CSV.
   */
  #[CLI\Command(name: 'motaded:events-import-paragraphs-csv', aliases: ['meipc'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../event_paragraphs_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Option(name: 'replace', description: 'Replace existing items in the target field (per event + paragraph_type).')]
  #[CLI\Usage(name: 'drush meipc', description: 'Import from project root event_paragraphs_import_ready.csv')]
  public function importParagraphs(?string $path = NULL, array $options = [
    'dry-run' => FALSE,
    'replace' => TRUE,
  ]): void {
    $drupal_root = $this->drupalRoot();
    $default = $drupal_root . '/../event_paragraphs_import_ready.csv';
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
      $this->logger()->error('Cannot read CSV: @path', ['@path' => $path]);
      return;
    }

    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $replace = filter_var($options['replace'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

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
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }
    $indexes = array_flip($header);
    foreach (['event_title', 'paragraph_type', 'field_title', 'field_body'] as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error('Missing column in CSV: @c', ['@c' => $col]);
        return;
      }
    }

    $get = static function (array $idx, array $r, string $key): string {
      $i = $idx[$key] ?? NULL;
      if ($i === NULL || !isset($r[$i])) {
        return '';
      }
      return trim((string) $r[$i]);
    };

    $type_to_field = [
      'agenda' => 'field_agenda',
      'why_attend' => 'field_why_attend',
      'what_we_offer' => 'field_what_we_offer',
      'who_should_attend' => 'field_who_should_attend',
    ];

    $rows_by_event = [];
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== FALSE) {
      $row_num++;
      $row = $this->padRow($row, count($header));

      $event_title = $get($indexes, $row, 'event_title');
      $ptype = $get($indexes, $row, 'paragraph_type');
      $p_title = $get($indexes, $row, 'field_title');
      $p_body = $get($indexes, $row, 'field_body');

      if ($event_title === '') {
        continue;
      }
      if (!isset($type_to_field[$ptype])) {
        $this->logger()->error('Row {n}: Unknown paragraph_type "{t}"', ['n' => $row_num, 't' => $ptype]);
        continue;
      }
      $rows_by_event[$event_title][$ptype][] = [
        'title' => $p_title,
        'body' => $p_body,
      ];
    }
    fclose($handle);

    if ($rows_by_event === []) {
      $this->logger()->warning('No rows to import.');
      return;
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $paragraph_storage = $this->entityTypeManager->getStorage('paragraph');
    $updated = 0;

    foreach ($rows_by_event as $event_title => $by_type) {
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'event')
        ->condition('title', $event_title)
        ->range(0, 1)
        ->execute();
      if (!$nids) {
        $this->logger()->error('Event not found by title: {t}', ['t' => $event_title]);
        continue;
      }
      $nid = (int) reset($nids);
      /** @var \Drupal\node\NodeInterface $node */
      $node = $node_storage->load($nid);
      if (!$node) {
        $this->logger()->error('Could not load event nid={nid}', ['nid' => $nid]);
        continue;
      }

      foreach ($by_type as $ptype => $items) {
        $field_name = $type_to_field[$ptype];
        if ($replace) {
          $node->set($field_name, []);
        }

        foreach ($items as $item) {
          $p = Paragraph::create([
            'type' => 'event_content_item',
            'langcode' => $langcode,
            'field_title' => $item['title'],
            'field_body' => [
              'value' => $this->normalizeBodyHtml($item['body']),
              'format' => 'basic_html',
            ],
          ]);

          if ($dry_run) {
            continue;
          }

          $p->save();
          $node->get($field_name)->appendItem([
            'target_id' => (int) $p->id(),
            'target_revision_id' => (int) $p->getRevisionId(),
          ]);
        }
      }

      if ($dry_run) {
        $this->io()->writeln(sprintf('[dry-run] Would update nid=%d (%s)', $nid, $event_title));
        $updated++;
        continue;
      }

      $node->save();
      $this->io()->writeln(sprintf('Updated nid=%d (%s)', $nid, $event_title));
      $updated++;
    }

    if ($dry_run) {
      $this->logger()->success('Dry-run: {n} event(s) validated (nothing saved).', ['n' => (string) $updated]);
    }
    else {
      $this->logger()->success('Updated {n} event(s).', ['n' => (string) $updated]);
    }
  }

  /**
   * Import Event documents (event_document_item paragraphs) from CSV.
   *
   * CSV columns: event_title, field_title, field_documents_media
   * where field_documents_media is a document filename (e.g. "FII_2026_Brochure.pdf").
   */
  #[CLI\Command(name: 'motaded:events-import-documents-csv', aliases: ['meidc'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../event_documents_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Option(name: 'replace', description: 'Replace existing documents on the event (field_documents).')]
  #[CLI\Option(name: 'create-test-media', description: 'If the referenced filename has no Media, create a test Document media with a generated file.')]
  #[CLI\Usage(name: 'drush meidc', description: 'Import from project root event_documents_import_ready.csv')]
  public function importDocuments(?string $path = NULL, array $options = [
    'dry-run' => FALSE,
    'replace' => TRUE,
    'create-test-media' => TRUE,
  ]): void {
    $drupal_root = $this->drupalRoot();
    $default = $drupal_root . '/../event_documents_import_ready.csv';
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
      $this->logger()->error('Cannot read CSV: @path', ['@path' => $path]);
      return;
    }

    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $replace = filter_var($options['replace'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $create_test_media = filter_var($options['create-test-media'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

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
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }
    $indexes = array_flip($header);
    foreach (['event_title', 'field_title', 'field_documents_media'] as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error('Missing column in CSV: @c', ['@c' => $col]);
        return;
      }
    }

    $get = static function (array $idx, array $r, string $key): string {
      $i = $idx[$key] ?? NULL;
      if ($i === NULL || !isset($r[$i])) {
        return '';
      }
      return trim((string) $r[$i]);
    };

    $rows_by_event = [];
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== FALSE) {
      $row_num++;
      $row = $this->padRow($row, count($header));

      $event_title = $get($indexes, $row, 'event_title');
      $doc_title = $get($indexes, $row, 'field_title');
      $media_ref = $get($indexes, $row, 'field_documents_media');

      if ($event_title === '') {
        continue;
      }
      if ($media_ref === '') {
        $this->logger()->error('Row {n}: Empty field_documents_media for "{t}"', ['n' => $row_num, 't' => $event_title]);
        continue;
      }

      $rows_by_event[$event_title][] = [
        'title' => $doc_title,
        'media_ref' => $media_ref,
      ];
    }
    fclose($handle);

    if ($rows_by_event === []) {
      $this->logger()->warning('No rows to import.');
      return;
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $updated = 0;
    $created_media = 0;

    foreach ($rows_by_event as $event_title => $items) {
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'event')
        ->condition('title', $event_title)
        ->range(0, 1)
        ->execute();
      if (!$nids) {
        $this->logger()->error('Event not found by title: {t}', ['t' => $event_title]);
        continue;
      }
      $nid = (int) reset($nids);
      /** @var \Drupal\node\NodeInterface $node */
      $node = $node_storage->load($nid);
      if (!$node) {
        $this->logger()->error('Could not load event nid={nid}', ['nid' => $nid]);
        continue;
      }

      if ($replace) {
        $node->set('field_documents', []);
      }

      foreach ($items as $item) {
        $filename = $this->normalizeFilename($item['media_ref']);
        if ($filename === '') {
          $this->logger()->error('Event "{t}": invalid filename "{f}"', ['t' => $event_title, 'f' => $item['media_ref']]);
          continue;
        }

        $media = $this->loadDocumentMediaByFilename($filename);
        if (!$media && $create_test_media && !$dry_run) {
          $media = $this->createTestDocumentMedia($filename, $langcode);
          $created_media++;
        }

        if (!$media) {
          $this->logger()->error('Event "{t}": Media not found for filename "{f}"', ['t' => $event_title, 'f' => $filename]);
          continue;
        }

        if ($dry_run) {
          continue;
        }

        $p = Paragraph::create([
          'type' => 'event_document_item',
          'langcode' => $langcode,
          'field_title' => $item['title'],
          'field_documents_media' => [
            ['target_id' => (int) $media->id()],
          ],
        ]);
        $p->save();

        $node->get('field_documents')->appendItem([
          'target_id' => (int) $p->id(),
          'target_revision_id' => (int) $p->getRevisionId(),
        ]);
      }

      if ($dry_run) {
        $this->io()->writeln(sprintf('[dry-run] Would update nid=%d (%s)', $nid, $event_title));
        $updated++;
        continue;
      }

      $node->save();
      $this->io()->writeln(sprintf('Updated nid=%d (%s) documents=%d', $nid, $event_title, $node->get('field_documents')->count()));
      $updated++;
    }

    if ($dry_run) {
      $this->logger()->success('Dry-run: {n} event(s) validated (nothing saved).', ['n' => (string) $updated]);
      return;
    }
    $this->logger()->success('Updated {n} event(s). Created {m} test media.', [
      'n' => (string) $updated,
      'm' => (string) $created_media,
    ]);
  }

  /**
   * Import Events ↔ Sectors (sector_page node references) from CSV.
   *
   * CSV columns: event_title, sector_title
   */
  #[CLI\Command(name: 'motaded:events-import-sectors-csv', aliases: ['meisc'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../event_sectors_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Option(name: 'replace', description: 'Replace existing sector links on the event (field_sector_pages).')]
  #[CLI\Usage(name: 'drush meisc', description: 'Import from project root event_sectors_import_ready.csv')]
  public function importSectors(?string $path = NULL, array $options = [
    'dry-run' => FALSE,
    'replace' => TRUE,
  ]): void {
    $drupal_root = $this->drupalRoot();
    $default = $drupal_root . '/../event_sectors_import_ready.csv';
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
      $this->logger()->error('Cannot read CSV: @path', ['@path' => $path]);
      return;
    }

    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $replace = filter_var($options['replace'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error('Could not open CSV.');
      return;
    }

    $header = fgetcsv($handle);
    if ($header === FALSE || $header === []) {
      fclose($handle);
      $this->logger()->error('Empty CSV.');
      return;
    }
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }
    $indexes = array_flip($header);
    foreach (['event_title', 'sector_title'] as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error('Missing column in CSV: @c', ['@c' => $col]);
        return;
      }
    }

    $get = static function (array $idx, array $r, string $key): string {
      $i = $idx[$key] ?? NULL;
      if ($i === NULL || !isset($r[$i])) {
        return '';
      }
      return trim((string) $r[$i]);
    };

    $rows_by_event = [];
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== FALSE) {
      $row_num++;
      $row = $this->padRow($row, count($header));
      $event_title = $get($indexes, $row, 'event_title');
      $sector_title = $get($indexes, $row, 'sector_title');
      if ($event_title === '' || $sector_title === '') {
        continue;
      }
      $rows_by_event[$event_title][] = $sector_title;
    }
    fclose($handle);

    if ($rows_by_event === []) {
      $this->logger()->warning('No rows to import.');
      return;
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $updated = 0;
    $missing_sectors = 0;

    foreach ($rows_by_event as $event_title => $sector_titles) {
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'event')
        ->condition('title', $event_title)
        ->range(0, 1)
        ->execute();
      if (!$nids) {
        $this->logger()->error('Event not found by title: {t}', ['t' => $event_title]);
        continue;
      }
      $nid = (int) reset($nids);
      /** @var \Drupal\node\NodeInterface $node */
      $node = $node_storage->load($nid);
      if (!$node) {
        $this->logger()->error('Could not load event nid={nid}', ['nid' => $nid]);
        continue;
      }

      if ($replace) {
        $node->set('field_sector_pages', []);
      }

      $sector_titles = array_values(array_unique(array_map('trim', $sector_titles)));
      $sector_nids = [];
      foreach ($sector_titles as $sector_title) {
        if ($sector_title === '') {
          continue;
        }
        $snids = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'sector_page')
          ->condition('title', $sector_title)
          ->range(0, 1)
          ->execute();
        if (!$snids) {
          $this->logger()->error('Missing sector_page node by title "{s}" (event "{e}")', [
            's' => $sector_title,
            'e' => $event_title,
          ]);
          $missing_sectors++;
          continue;
        }
        $sector_nids[] = (int) reset($snids);
      }

      if ($dry_run) {
        $this->io()->writeln(sprintf('[dry-run] Would update nid=%d (%s) sectors=%d', $nid, $event_title, count($sector_nids)));
        $updated++;
        continue;
      }

      foreach ($sector_nids as $sid) {
        $node->get('field_sector_pages')->appendItem(['target_id' => $sid]);
      }
      $node->save();
      $this->io()->writeln(sprintf('Updated nid=%d (%s) sectors=%d', $nid, $event_title, $node->get('field_sector_pages')->count()));
      $updated++;
    }

    if ($dry_run) {
      $this->logger()->success('Dry-run: {n} event(s) validated (nothing saved). Missing sectors: {m}.', [
        'n' => (string) $updated,
        'm' => (string) $missing_sectors,
      ]);
    }
    else {
      $this->logger()->success('Updated {n} event(s). Missing sectors: {m}.', [
        'n' => (string) $updated,
        'm' => (string) $missing_sectors,
      ]);
    }
  }

  /**
   * Auto-fill richer event content for existing event nodes.
   *
   * - Expands body ("About") if it's too short.
   * - Ensures each of: why/agenda/offer/who has at least N items (appends).
   * - Attempts to set organizer based on known patterns.
   */
  #[CLI\Command(name: 'motaded:events-autofill-content', aliases: ['meac'])]
  #[CLI\Option(name: 'dry-run', description: 'Show actions without saving.')]
  #[CLI\Option(name: 'min-items', description: 'Minimum items per section field before appending.')]
  public function autofillContent(array $options = [
    'dry-run' => FALSE,
    'min-items' => 4,
  ]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $min_items = (int) ($options['min-items'] ?? 4);
    if ($min_items < 1) {
      $min_items = 1;
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $event_nids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'event')
      ->sort('nid', 'ASC')
      ->execute();
    if (!$event_nids) {
      $this->logger()->warning('No event nodes found.');
      return;
    }

    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $updated = 0;

    foreach ($event_nids as $nid) {
      /** @var \Drupal\node\NodeInterface|null $node */
      $node = $node_storage->load((int) $nid);
      if (!$node) {
        continue;
      }

      $title = $node->label();
      $changed = FALSE;

      // Expand About/body if very short.
      $body_value = (string) ($node->get('body')->value ?? '');
      $plain_len = strlen(trim(strip_tags($body_value)));
      if ($plain_len < 220) {
        $start = $node->get('field_event_start')->value;
        $end = $node->get('field_event_end')->value;
        $city = $node->hasField('field_city') && !$node->get('field_city')->isEmpty() ? $node->get('field_city')->entity?->label() : '';
        $country = $node->hasField('field_country') && !$node->get('field_country')->isEmpty() ? $node->get('field_country')->entity?->label() : '';
        $event_type = $node->hasField('field_event_type') && !$node->get('field_event_type')->isEmpty()
          ? $node->get('field_event_type')->entity?->label()
          : 'Event';
        $where = trim(implode(', ', array_filter([$city, $country])));

        $sector_labels = [];
        if ($node->hasField('field_sector_pages') && !$node->get('field_sector_pages')->isEmpty()) {
          foreach ($node->get('field_sector_pages') as $it) {
            $sector_labels[] = $it->entity?->label();
          }
        }
        $sector_labels = array_values(array_filter($sector_labels));
        $sectors_text = $sector_labels ? ('This event is especially relevant for ' . implode(', ', $sector_labels) . ' stakeholders.') : '';

        $official = '';
        if ($node->hasField('field_official_website') && !$node->get('field_official_website')->isEmpty()) {
          $official = (string) ($node->get('field_official_website')->first()->uri ?? '');
        }

        $about = '<p><strong>' . htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong> is a ' . htmlspecialchars((string) $event_type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ' designed for decision-makers, operators, and practitioners who are driving projects and partnerships.</p>';
        if ($where !== '' || $start !== '' || $end !== '') {
          $meta = [];
          if ($where !== '') {
            $meta[] = $where;
          }
          if ($start !== '' && $end !== '') {
            $meta[] = $start . ' → ' . $end;
          }
          $about .= '<p><strong>When & where:</strong> ' . htmlspecialchars(implode(' • ', $meta), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        if ($sectors_text !== '') {
          $about .= '<p>' . htmlspecialchars($sectors_text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        if ($official !== '') {
          $about .= '<p><strong>Official website:</strong> ' . htmlspecialchars($official, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        }
        $node->set('body', ['value' => $about, 'format' => 'basic_html']);
        $changed = TRUE;
      }

      // Append paragraphs to reach min-items for each section.
      $section_map = [
        'field_why_attend' => [
          'Why attend',
          [
            ['title' => 'Practical insights', 'body' => 'Learn what’s driving the market and what teams are doing now to execute better and faster.'],
            ['title' => 'Meet the right people', 'body' => 'Connect with operators, decision-makers, and partners relevant to your projects.'],
            ['title' => 'New solutions', 'body' => 'Explore tools, suppliers, and services that can improve delivery and outcomes.'],
          ],
        ],
        'field_agenda' => [
          'Agenda overview',
          [
            ['title' => 'Keynotes & panels', 'body' => 'High-level sessions covering strategy, policy, and market direction.'],
            ['title' => 'Workshops & deep-dives', 'body' => 'Focused sessions on execution, best practices, and implementation details.'],
            ['title' => 'Networking & meetings', 'body' => 'Dedicated time to connect with stakeholders and suppliers.'],
          ],
        ],
        'field_what_we_offer' => [
          'What we offer',
          [
            ['title' => 'Curated content', 'body' => 'A structured program that balances high-level context with practical takeaways.'],
            ['title' => 'Exhibition floor', 'body' => 'Access to exhibitors and solution providers across the value chain.'],
            ['title' => 'Partnership opportunities', 'body' => 'Ways to connect, collaborate, and move from interest to action.'],
          ],
        ],
        'field_who_should_attend' => [
          'Who should attend',
          [
            ['title' => 'Operators & project teams', 'body' => 'Teams responsible for planning, delivery, compliance, and performance.'],
            ['title' => 'Suppliers & service providers', 'body' => 'Companies offering products, services, and technology for the sector.'],
            ['title' => 'Investors & partners', 'body' => 'Stakeholders evaluating opportunities and partnerships.'],
          ],
        ],
      ];

      foreach ($section_map as $field_name => [$label, $defaults]) {
        if (!$node->hasField($field_name)) {
          continue;
        }
        $count = $node->get($field_name)->count();
        if ($count >= $min_items) {
          continue;
        }

        $need = $min_items - $count;
        $to_add = array_slice($defaults, 0, max(0, min($need, count($defaults))));
        if ($to_add === []) {
          continue;
        }

        if ($dry_run) {
          $this->io()->writeln(sprintf('[dry-run] Would append %d %s items to nid=%d (%s)', count($to_add), $label, (int) $nid, $title));
          $changed = TRUE;
          continue;
        }

        foreach ($to_add as $item) {
          $p = Paragraph::create([
            'type' => 'event_content_item',
            'langcode' => $langcode,
            'field_title' => $item['title'],
            'field_body' => [
              'value' => $this->normalizeBodyHtml($item['body']),
              'format' => 'basic_html',
            ],
          ]);
          $p->save();
          $node->get($field_name)->appendItem([
            'target_id' => (int) $p->id(),
            'target_revision_id' => (int) $p->getRevisionId(),
          ]);
        }
        $changed = TRUE;
      }

      // Organizer: set from heuristics if empty.
      if ($node->hasField('field_organizer') && $node->get('field_organizer')->isEmpty()) {
        $org_name = $this->guessOrganizerName($node);
        if ($org_name !== '') {
          if ($dry_run) {
            $this->io()->writeln(sprintf('[dry-run] Would set organizer "%s" on nid=%d (%s)', $org_name, (int) $nid, $title));
            $changed = TRUE;
          }
          else {
            $tid = $this->getOrCreateTerm('event_organizer', $org_name, $langcode);
            $node->set('field_organizer', [['target_id' => $tid]]);
            $changed = TRUE;
          }
        }
      }

      if (!$changed) {
        continue;
      }
      if (!$dry_run) {
        $node->save();
      }
      $updated++;
    }

    $this->logger()->success(($dry_run ? 'Dry-run: ' : '') . 'Processed {n} event(s).', ['n' => (string) $updated]);
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

  private function normalizeFilename(string $raw): string {
    $raw = trim($raw);
    if ($raw === '') {
      return '';
    }
    // Accept "filename" or "some/path/filename".
    $raw = str_replace('\\', '/', $raw);
    $raw = basename($raw);
    // Basic hardening: keep safe chars only.
    $raw = preg_replace('/[^A-Za-z0-9._-]+/', '_', $raw) ?? '';
    return trim($raw, '._-');
  }

  private function guessOrganizerName(NodeInterface $node): string {
    $title = strtolower($node->label());
    $official = '';
    if ($node->hasField('field_official_website') && !$node->get('field_official_website')->isEmpty()) {
      $official = strtolower((string) ($node->get('field_official_website')->first()->uri ?? ''));
    }
    $body = strtolower((string) ($node->get('body')->value ?? ''));

    $hay = $title . ' ' . $official . ' ' . $body;

    if (str_contains($hay, 'futureinvestmentinitiative') || str_contains($hay, 'pif')) {
      return 'Public Investment Fund (PIF)';
    }
    if (str_contains($hay, 'onegiantleap') || str_contains($hay, 'leap')) {
      return 'Tahaluf';
    }
    if (str_contains($hay, 'misa.gov.sa') || str_contains($hay, 'ministry of investment') || str_contains($hay, 'misa')) {
      return 'Ministry of Investment';
    }
    if (str_contains($hay, 'gitex')) {
      return 'Informa';
    }
    if (str_contains($hay, 'dmg')) {
      return 'dmg events';
    }
    return '';
  }

  private function loadDocumentMediaByFilename(string $filename): ?MediaInterface {
    $file_storage = $this->entityTypeManager->getStorage('file');
    $fids = $file_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('filename', $filename)
      ->range(0, 1)
      ->execute();
    if (!$fids) {
      return NULL;
    }
    $fid = (int) reset($fids);

    $media_storage = $this->entityTypeManager->getStorage('media');
    $mids = $media_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('bundle', 'document')
      ->condition('field_media_document.target_id', $fid)
      ->range(0, 1)
      ->execute();
    if (!$mids) {
      return NULL;
    }
    $mid = (int) reset($mids);
    $media = $media_storage->load($mid);
    return $media instanceof MediaInterface ? $media : NULL;
  }

  private function createTestDocumentMedia(string $filename, string $langcode): MediaInterface {
    $data = "%PDF-1.4\n% Test document generated by importer.\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n";
    $directory = 'public://event-documents';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $uri = $directory . '/' . $filename;

    /** @var \Drupal\file\FileRepositoryInterface $repo */
    $repo = \Drupal::service('file.repository');
    $file = $repo->writeData($data, $uri, FileSystemInterface::EXISTS_REPLACE);
    if (!$file instanceof FileInterface) {
      throw new \RuntimeException('Failed to create test file: ' . $uri);
    }

    $media_storage = $this->entityTypeManager->getStorage('media');
    /** @var \Drupal\media\MediaInterface $media */
    $media = $media_storage->create([
      'bundle' => 'document',
      'langcode' => $langcode,
      'name' => pathinfo($filename, PATHINFO_FILENAME),
      'field_media_document' => [
        'target_id' => (int) $file->id(),
      ],
      'status' => 1,
    ]);
    $media->save();
    $this->io()->writeln(sprintf('Created test media document (mid=%d) for %s', (int) $media->id(), $filename));
    return $media;
  }

  private function normalizeEventFormat(string $raw): string {
    $raw = strtolower(trim($raw));
    if ($raw === '' || $raw === 'all') {
      return '';
    }
    if (in_array($raw, ['in-person', 'in_person', 'inperson', 'in person'], TRUE)) {
      return 'in_person';
    }
    if ($raw === 'online') {
      return 'online';
    }
    if ($raw === 'hybrid') {
      return 'hybrid';
    }
    return '';
  }

  private function normalizeCtaType(string $raw): string {
    $raw = strtolower(trim($raw));
    if (in_array($raw, ['register', 'registration'], TRUE)) {
      return 'register';
    }
    if (in_array($raw, ['meeting', 'book meeting', 'book_a_meeting'], TRUE)) {
      return 'meeting';
    }
    if (in_array($raw, ['external', 'website', 'visit'], TRUE)) {
      return 'external';
    }
    return $raw;
  }

  private function normalizePriceRange(string $raw): string {
    $raw = strtolower(trim($raw));
    if ($raw === '' || $raw === 'all') {
      return '';
    }
    if ($raw === 'free') {
      return 'free';
    }
    if ($raw === 'paid') {
      return 'paid';
    }
    if (in_array($raw, ['invite only', 'invite-only', 'invite_only'], TRUE)) {
      return 'invite_only';
    }
    return '';
  }

  private function normalizeAttendanceType(string $raw): string {
    $raw = strtolower(trim($raw));
    if ($raw === '' || $raw === 'all') {
      return '';
    }
    if ($raw === 'public') {
      return 'public';
    }
    if ($raw === 'private') {
      return 'private';
    }
    if (in_array($raw, ['invite only', 'invite-only', 'invite_only'], TRUE)) {
      return 'invite_only';
    }
    return '';
  }

  /**
   * @return int[]
   */
  private function resolveOrganizers(string $raw, string $langcode): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $parts = array_filter(array_map('trim', preg_split('/[|,]/', $raw) ?: []));
    $ids = [];
    foreach ($parts as $name) {
      $ids[] = $this->getOrCreateTerm('event_organizer', $name, $langcode);
    }
    $ids = array_values(array_unique(array_filter($ids, static fn ($v) => (int) $v > 0)));
    return array_map('intval', $ids);
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
