<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\path_alias\PathAliasInterface;
use Drupal\redirect\Entity\Redirect;
use Drupal\taxonomy\TermInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * FR/ES removal: 301 redirects to English (or home) and optional translation cleanup.
 */
final class FrEsRedirectCommands extends DrushCommands {

  /**
   * URL path prefixes for fr/es (must match former language.negotiation.url.prefixes).
   */
  private const LANG_PREFIX = [
    'fr' => 'fr',
    'es' => 'es',
  ];

  /**
   * Allowed first segment for FR/ES public URLs (path prefix negotiation).
   */
  private const SOURCE_LANG_PREFIX = ['fr', 'es'];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly AliasManagerInterface $aliasManager,
    protected readonly Connection $database,
    protected readonly LanguageManagerInterface $languageManager,
  ) {
    parent::__construct();
  }

  /**
   * Creates 301 Redirect entities for all FR/ES path aliases → English (or /).
   */
  #[CLI\Command(name: 'motaded:fr-es-redirects')]
  #[CLI\Option(name: 'dry-run', description: 'List actions without saving.')]
  public function createRedirects(array $options = ['dry-run' => FALSE]): void {
    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $storage = $this->entityTypeManager->getStorage('path_alias');
    $last_id = 0;
    $created = 0;
    $skip_excluded = 0;
    $skip_same_target = 0;
    $skip_existing_redirect = 0;
    $report = [];

    // Views/static paths are not path_alias records; add them explicitly.
    $this->createStaticLanguageRedirects($dry, $created, $skip_existing_redirect, $report);

    while (TRUE) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('id', $last_id, '>')
        ->condition('langcode', ['fr', 'es'], 'IN')
        ->condition('status', TRUE)
        ->sort('id', 'ASC')
        ->range(0, 150)
        ->execute();

      if ($ids === NULL || $ids === []) {
        break;
      }

      $last_id = (int) max($ids);
      /** @var \Drupal\path_alias\Entity\PathAlias[] $aliases */
      $aliases = $storage->loadMultiple($ids);

      foreach ($aliases as $alias_entity) {
        if (!$alias_entity instanceof PathAliasInterface) {
          continue;
        }

        $source_display = $this->publicSourcePathForAlias($alias_entity);
        $source_key = $this->normalizeRedirectSourceKey($source_display);

        if ($this->shouldSkipSourcePath($source_key)) {
          $skip_excluded++;
          continue;
        }

        $internal = $this->normalizeInternalPath($alias_entity->getPath());
        $target_path = $this->normalizeInternalPath(
          $this->normalizePublicEnglishPath($this->resolveEnglishPath($internal)),
        );

        if ($this->pathsEquivalentForRedirect($source_display, $target_path)) {
          $skip_same_target++;
          continue;
        }

        if ($this->redirectSourcePathExists($source_key)) {
          $skip_existing_redirect++;
          continue;
        }

        $report[] = sprintf('%s → %s', $source_display, $target_path);

        if (!$dry) {
          $redirect = Redirect::create();
          $redirect->setSource($source_key);
          $redirect->setRedirect($this->redirectDestinationUri($target_path));
          $redirect->setStatusCode(301);
          $redirect->save();
          $created++;
        }
        else {
          $created++;
        }
      }
    }

    $skipped_total = $skip_excluded + $skip_same_target + $skip_existing_redirect;
    $this->logger()->notice($dry
      ? sprintf(
        'Dry-run: would create %d redirects. Skipped %d total (excluded path: %d, source=target: %d, redirect already exists: %d).',
        $created,
        $skipped_total,
        $skip_excluded,
        $skip_same_target,
        $skip_existing_redirect,
      )
      : sprintf(
        'Created %d redirects. Skipped %d total (excluded path: %d, source=target: %d, redirect already exists: %d).',
        $created,
        $skipped_total,
        $skip_excluded,
        $skip_same_target,
        $skip_existing_redirect,
      ));

    foreach (array_slice($report, 0, 40) as $line) {
      $this->output()->writeln($line);
    }
    if (count($report) > 40) {
      $this->output()->writeln('… +' . (count($report) - 40) . ' more');
    }
  }

  /**
   * Adds redirects for known public routes that are not stored as path aliases.
   *
   * After removing language prefixes, URLs like /fr and /fr/blog would 404
   * unless a redirect entity exists.
   */
  private function createStaticLanguageRedirects(
    bool $dry,
    int &$created,
    int &$skip_existing_redirect,
    array &$report,
  ): void {
    $map = [
      // Language prefix roots.
      'fr' => '/',
      'es' => '/',
      // Key listing pages (Views/menu routes).
      'fr/blog' => '/blog',
      'es/blog' => '/blog',
      'fr/news' => '/news',
      'es/news' => '/news',
      'fr/services' => '/services',
      'es/services' => '/services',
      'fr/partners' => '/partners',
      'es/partners' => '/partners',
      // Contact page path used in metatags defaults.
      'fr/contact-us' => '/contact-us',
      'es/contact-us' => '/contact-us',
    ];

    foreach ($map as $source_key => $target_path) {
      $source_key = trim((string) $source_key, '/');
      $target_path = $this->normalizeInternalPath((string) $target_path);
      $source_display = '/' . $source_key;

      if ($this->redirectSourcePathExists($source_key)) {
        $skip_existing_redirect++;
        continue;
      }

      $report[] = sprintf('%s → %s', $source_display, $target_path);
      if (!$dry) {
        $redirect = Redirect::create();
        $redirect->setSource($source_key);
        $redirect->setRedirect($this->redirectDestinationUri($target_path));
        $redirect->setStatusCode(301);
        $redirect->save();
      }
      $created++;
    }
  }

  /**
   * Removes FR and ES translations from nodes and taxonomy terms (not default lang).
   */
  #[CLI\Command(name: 'motaded:fr-es-remove-translations')]
  #[CLI\Option(name: 'dry-run', description: 'Report only; no saves.')]
  public function removeTranslations(array $options = ['dry-run' => FALSE]): void {
    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $removed = 0;
    $warned = 0;
    $removed += $this->removeTranslationsFromEntityType('node', 'node_field_data', $dry, $warned);
    $removed += $this->removeTranslationsFromEntityType('taxonomy_term', 'taxonomy_term_field_data', $dry, $warned);

    $this->logger()->notice($dry
      ? sprintf('Dry-run: would remove %d translation record(s); %d default-lang warning(s).', $removed, $warned)
      : sprintf('Removed %d translation(s); %d default-lang skip(s).', $removed, $warned));
  }

  /**
   * Exports a CSV table: FR/ES URL → EN path (same rules as motaded:fr-es-redirects).
   *
   * Run on prod **before** deleting FR/ES translations or path aliases, otherwise
   * aliases may disappear and the map will be incomplete.
   */
  #[CLI\Command(name: 'motaded:fr-es-export-map')]
  #[CLI\Option(name: 'output', description: 'CSV file path (default: fr-es-redirect-map.csv in project root next to composer.json).')]
  #[CLI\Option(name: 'stdout', description: 'Print CSV to STDOUT instead of writing a file.')]
  #[CLI\Option(name: 'base-url', description: 'Site base URL without trailing slash; adds source_url and target_url columns.')]
  #[CLI\Usage(name: 'drush motaded:fr-es-export-map --base-url=https://example.com', description: 'Writes fr-es-redirect-map.csv in project root (visible on host with DDEV).')]
  public function exportMap(array $options = ['output' => NULL, 'stdout' => FALSE, 'base-url' => NULL]): void {
    $base = rtrim((string) ($options['base-url'] ?? ''), '/');
    $has_base = $base !== '';

    $use_stdout = filter_var($options['stdout'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $path = $options['output'] ?? NULL;
    $path = $path !== NULL && $path !== '' ? (string) $path : NULL;

    if ($use_stdout) {
      $path = NULL;
      $target_label = 'STDOUT';
    }
    elseif ($path === NULL) {
      $path = $this->getProjectRootDirectory() . '/fr-es-redirect-map.csv';
      $target_label = $path;
    }
    else {
      $target_label = $path;
    }

    $fh = fopen($path ?? 'php://output', 'wb');
    if ($fh === FALSE) {
      throw new \RuntimeException($path !== NULL
        ? sprintf('Cannot open file for writing: %s', $path)
        : 'Cannot open STDOUT for writing.');
    }

    fwrite($fh, "\xEF\xBB\xBF");

    $header = ['langcode', 'source_path', 'target_path'];
    if ($has_base) {
      $header[] = 'source_url';
      $header[] = 'target_url';
    }
    fputcsv($fh, $header);

    $rows = 0;
    $storage = $this->entityTypeManager->getStorage('path_alias');
    $last_id = 0;
    while (TRUE) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('id', $last_id, '>')
        ->condition('langcode', ['fr', 'es'], 'IN')
        ->condition('status', TRUE)
        ->sort('id', 'ASC')
        ->range(0, 150)
        ->execute();

      if ($ids === NULL || $ids === []) {
        break;
      }

      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $alias_entity) {
        if (!$alias_entity instanceof PathAliasInterface) {
          continue;
        }

        $langcode = $alias_entity->language()->getId();
        $source_display = $this->publicSourcePathForAlias($alias_entity);
        $source_key = $this->normalizeRedirectSourceKey($source_display);

        if ($this->shouldSkipSourcePath($source_key)) {
          continue;
        }

        $internal = $this->normalizeInternalPath($alias_entity->getPath());
        $target_path = $this->normalizeInternalPath(
          $this->normalizePublicEnglishPath($this->resolveEnglishPath($internal)),
        );

        if ($this->pathsEquivalentForRedirect($source_display, $target_path)) {
          continue;
        }

        $row = [$langcode, $source_display, $target_path];
        if ($has_base) {
          $row[] = $base . $source_display;
          $row[] = $base . $target_path;
        }
        fputcsv($fh, $row);
        $rows++;
      }
    }

    fclose($fh);

    $this->logger()->notice(sprintf('Exported %d row(s) to %s.', $rows, $target_label));
  }

  /**
   * Imports Redirect entities from a CSV mapping (source_path → New target_path).
   *
   * Expected headers (case-sensitive, as in the provided sheet):
   * - source_path
   * - New target_path
   * Optional fallback header:
   * - target_path
   *
   * Creates 301 redirects. By default, skips sources that already exist; use
   * --force to update existing redirects to the new target.
   */
  #[CLI\Command(name: 'motaded:fr-es-import-redirects-csv')]
  #[CLI\Option(name: 'file', description: 'Path to CSV file to import.')]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving redirects.')]
  #[CLI\Option(name: 'force', description: 'Update existing redirects for the same source path.')]
  #[CLI\Usage(name: 'drush motaded:fr-es-import-redirects-csv --file=/tmp/redirects.csv --dry-run', description: 'Dry-run import from spreadsheet CSV.')]
  public function importRedirectsFromCsv(array $options = ['file' => NULL, 'dry-run' => FALSE, 'force' => FALSE]): void {
    $file = (string) ($options['file'] ?? '');
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Provide an absolute path to the CSV.');
    }

    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $force = filter_var($options['force'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV file: %s', $file));
    }

    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('CSV header row is missing/invalid.');
    }

    // Normalize possible UTF-8 BOM in the first header cell.
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];

    $col = array_flip($header);
    $source_idx = $col['source_path'] ?? NULL;
    $new_target_idx = $col['New target_path'] ?? NULL;
    $fallback_target_idx = $col['target_path'] ?? NULL;

    if (!is_int($source_idx) || (!is_int($new_target_idx) && !is_int($fallback_target_idx))) {
      fclose($fh);
      throw new \InvalidArgumentException('CSV must include source_path and either New target_path or target_path columns.');
    }

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $row_num = 1;

    while (($row = fgetcsv($fh)) !== FALSE) {
      $row_num++;
      if (!is_array($row) || $row === []) {
        continue;
      }

      $source_path = (string) ($row[$source_idx] ?? '');
      $target_path = '';
      if (is_int($new_target_idx)) {
        $target_path = (string) ($row[$new_target_idx] ?? '');
      }
      if ($target_path === '' && is_int($fallback_target_idx)) {
        $target_path = (string) ($row[$fallback_target_idx] ?? '');
      }

      $source_path = $this->normalizeInternalPath($source_path);
      $source_key = $this->normalizeRedirectSourceKey($source_path);
      if ($source_key === '' || $this->shouldSkipSourcePath($source_key)) {
        $skipped++;
        continue;
      }

      $target_path = trim($target_path);
      if ($target_path === '') {
        $this->logger()->warning(sprintf('Row %d: empty target for source %s; skipped.', $row_num, $source_path));
        $skipped++;
        continue;
      }

      $destination_uri = $this->destinationUriFromCsvTarget($target_path);

      $existing_ids = $this->entityTypeManager->getStorage('redirect')->getQuery()
        ->accessCheck(FALSE)
        ->condition('redirect_source.path', $source_key)
        ->range(0, 1)
        ->execute();

      if ($existing_ids !== NULL && $existing_ids !== []) {
        if (!$force) {
          $skipped++;
          continue;
        }
        $rid = (int) array_key_first($existing_ids);
        /** @var \Drupal\redirect\Entity\Redirect|null $redirect */
        $redirect = $this->entityTypeManager->getStorage('redirect')->load($rid);
        if (!$redirect instanceof Redirect) {
          $skipped++;
          continue;
        }
        if (!$dry) {
          $redirect->setRedirect($destination_uri);
          $redirect->setStatusCode(301);
          $redirect->save();
        }
        $updated++;
        continue;
      }

      if (!$dry) {
        $redirect = Redirect::create();
        $redirect->setSource($source_key);
        $redirect->setRedirect($destination_uri);
        $redirect->setStatusCode(301);
        $redirect->save();
      }
      $created++;
    }

    fclose($fh);

    $this->logger()->notice(sprintf(
      '%s: created %d, updated %d, skipped %d. File: %s',
      $dry ? 'Dry-run' : 'Import',
      $created,
      $updated,
      $skipped,
      $file,
    ));
  }

  /**
   * Imports redirects from the "Rebuilding url - Url.csv" file (from_path → to_path).
   *
   * Intended for bulk URL restructuring like:
   * - /foo → /blog/foo
   * - /bar → /services/bar
   *
   * CSV headers:
   * - from_path
   * - to_path
   * Optional filters:
   * - section (e.g. blog/services)
   */
  #[CLI\Command(name: 'motaded:rebuild-url-import')]
  #[CLI\Option(name: 'file', description: 'Path to CSV file to import.')]
  #[CLI\Option(name: 'section', description: 'Comma-separated section filter (e.g. blog,services). If omitted, imports all rows.')]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving redirects.')]
  #[CLI\Option(name: 'force', description: 'Update existing redirects for the same source path.')]
  #[CLI\Usage(name: 'drush motaded:rebuild-url-import --file=/tmp/rebuild.csv --section=blog,services --dry-run', description: 'Dry-run import blog/services URL redirects.')]
  public function importRebuildingUrls(array $options = ['file' => NULL, 'section' => NULL, 'dry-run' => FALSE, 'force' => FALSE]): void {
    $file = (string) ($options['file'] ?? '');
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Provide an absolute path to the CSV.');
    }

    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $force = filter_var($options['force'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $section_filter = [];
    $section_opt = (string) ($options['section'] ?? '');
    if (trim($section_opt) !== '') {
      foreach (explode(',', $section_opt) as $s) {
        $s = strtolower(trim($s));
        if ($s !== '') {
          $section_filter[$s] = TRUE;
        }
      }
    }

    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV file: %s', $file));
    }

    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('CSV header row is missing/invalid.');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
    $col = array_flip($header);

    $from_idx = $col['from_path'] ?? NULL;
    $to_idx = $col['to_path'] ?? NULL;
    $section_idx = $col['section'] ?? NULL;

    if (!is_int($from_idx) || !is_int($to_idx)) {
      fclose($fh);
      throw new \InvalidArgumentException('CSV must include from_path and to_path columns.');
    }

    $created = 0;
    $updated = 0;
    $skipped = 0;
    $row_num = 1;

    while (($row = fgetcsv($fh)) !== FALSE) {
      $row_num++;
      if (!is_array($row) || $row === []) {
        continue;
      }

      if (is_int($section_idx) && $section_filter !== []) {
        $sec = strtolower(trim((string) ($row[$section_idx] ?? '')));
        if ($sec === '' || empty($section_filter[$sec])) {
          continue;
        }
      }

      $from_path = (string) ($row[$from_idx] ?? '');
      $to_path = (string) ($row[$to_idx] ?? '');

      $from_path = $this->normalizeInternalPath($from_path);
      $source_key = $this->normalizeRedirectSourceKey($from_path);
      if ($source_key === '' || $this->shouldSkipAnySourcePath($source_key)) {
        $skipped++;
        continue;
      }

      $to_path = trim($to_path);
      if ($to_path === '') {
        $this->logger()->warning(sprintf('Row %d: empty to_path for source %s; skipped.', $row_num, $from_path));
        $skipped++;
        continue;
      }

      $destination_uri = $this->destinationUriFromCsvTarget($to_path);

      $existing_ids = $this->entityTypeManager->getStorage('redirect')->getQuery()
        ->accessCheck(FALSE)
        ->condition('redirect_source.path', $source_key)
        ->range(0, 1)
        ->execute();

      if ($existing_ids !== NULL && $existing_ids !== []) {
        if (!$force) {
          $skipped++;
          continue;
        }
        $rid = (int) array_key_first($existing_ids);
        /** @var \Drupal\redirect\Entity\Redirect|null $redirect */
        $redirect = $this->entityTypeManager->getStorage('redirect')->load($rid);
        if (!$redirect instanceof Redirect) {
          $skipped++;
          continue;
        }
        if (!$dry) {
          $redirect->setRedirect($destination_uri);
          $redirect->setStatusCode(301);
          $redirect->save();
        }
        $updated++;
        continue;
      }

      if (!$dry) {
        $redirect = Redirect::create();
        $redirect->setSource($source_key);
        $redirect->setRedirect($destination_uri);
        $redirect->setStatusCode(301);
        $redirect->save();
      }
      $created++;
    }

    fclose($fh);
    $this->logger()->notice(sprintf(
      '%s: created %d, updated %d, skipped %d. File: %s',
      $dry ? 'Dry-run' : 'Import',
      $created,
      $updated,
      $skipped,
      $file,
    ));
  }

  /**
   * Updates node path aliases from the "Rebuilding url - Url.csv" file.
   *
   * This changes the actual URLs (path aliases), not redirects.
   *
   * CSV headers:
   * - nid
   * - langcode
   * - to_path
   *
   * Notes:
   * - If to_path starts with "/{langcode}/", the language prefix is stripped
   *   before saving the alias (Drupal adds it via language negotiation).
   * - Existing redirects can be created automatically by Redirect module if
   *   configured (auto_redirect).
   */
  #[CLI\Command(name: 'motaded:rebuild-url-update-aliases')]
  #[CLI\Option(name: 'file', description: 'Path to CSV file to import.')]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving aliases.')]
  #[CLI\Option(name: 'only-langcodes', description: 'Comma-separated langcodes to process (e.g. ar,en). If omitted, all rows are processed.')]
  #[CLI\Usage(name: 'drush motaded:rebuild-url-update-aliases --file=/tmp/rebuild.csv --dry-run', description: 'Dry-run update path aliases from CSV to_path.')]
  public function updateAliasesFromRebuildCsv(array $options = ['file' => NULL, 'dry-run' => FALSE, 'only-langcodes' => NULL]): void {
    $file = (string) ($options['file'] ?? '');
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Provide an absolute path to the CSV.');
    }

    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $only = [];
    $only_opt = (string) ($options['only-langcodes'] ?? '');
    if (trim($only_opt) !== '') {
      foreach (explode(',', $only_opt) as $lc) {
        $lc = strtolower(trim($lc));
        if ($lc !== '') {
          $only[$lc] = TRUE;
        }
      }
    }

    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV file: %s', $file));
    }

    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('CSV header row is missing/invalid.');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
    $col = array_flip($header);

    $nid_idx = $col['nid'] ?? NULL;
    $lang_idx = $col['langcode'] ?? NULL;
    $to_idx = $col['to_path'] ?? NULL;

    if (!is_int($nid_idx) || !is_int($lang_idx) || !is_int($to_idx)) {
      fclose($fh);
      throw new \InvalidArgumentException('CSV must include nid, langcode, and to_path columns.');
    }

    $storage = $this->entityTypeManager->getStorage('path_alias');
    $updated = 0;
    $created = 0;
    $skipped = 0;
    $conflicts = 0;
    $row_num = 1;

    while (($row = fgetcsv($fh)) !== FALSE) {
      $row_num++;
      if (!is_array($row) || $row === []) {
        continue;
      }

      $nid = (int) ($row[$nid_idx] ?? 0);
      $langcode = strtolower(trim((string) ($row[$lang_idx] ?? '')));
      $to_path = (string) ($row[$to_idx] ?? '');

      if ($nid <= 0 || $langcode === '') {
        $skipped++;
        continue;
      }
      if ($only !== [] && empty($only[$langcode])) {
        continue;
      }

      $internal = '/node/' . $nid;
      $alias_to_save = $this->aliasValueFromPublicPath($to_path, $langcode);
      if ($alias_to_save === '') {
        $this->logger()->warning(sprintf('Row %d: empty/invalid to_path for nid %d (%s); skipped.', $row_num, $nid, $langcode));
        $skipped++;
        continue;
      }

      // Prevent alias collisions: another path already uses this alias in this language.
      $existing_for_alias = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('alias', $alias_to_save)
        ->condition('langcode', $langcode)
        ->range(0, 1)
        ->execute();

      if ($existing_for_alias !== NULL && $existing_for_alias !== []) {
        $existing_id = (int) array_key_first($existing_for_alias);
        /** @var \Drupal\path_alias\PathAliasInterface|null $collision */
        $collision = $storage->load($existing_id);
        if ($collision instanceof PathAliasInterface && $this->normalizeInternalPath($collision->getPath()) !== $internal) {
          $this->logger()->warning(sprintf(
            'Row %d: alias collision %s (%s) already points to %s; wanted %s. Skipped.',
            $row_num,
            $alias_to_save,
            $langcode,
            $collision->getPath(),
            $internal,
          ));
          $conflicts++;
          continue;
        }
      }

      // Update existing alias for this internal path + language, else create one.
      $existing_ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('path', $internal)
        ->condition('langcode', $langcode)
        ->condition('status', TRUE)
        ->range(0, 1)
        ->execute();

      if ($existing_ids !== NULL && $existing_ids !== []) {
        $id = (int) array_key_first($existing_ids);
        /** @var \Drupal\path_alias\PathAliasInterface|null $alias */
        $alias = $storage->load($id);
        if (!$alias instanceof PathAliasInterface) {
          $skipped++;
          continue;
        }
        if ($alias->getAlias() === $alias_to_save) {
          $skipped++;
          continue;
        }
        if (!$dry) {
          // PathAliasInterface doesn't guarantee a setter; the concrete entity does.
          $alias->set('alias', $alias_to_save);
          $alias->save();
        }
        $updated++;
        continue;
      }

      if (!$dry) {
        $new_alias = $storage->create([
          'path' => $internal,
          'alias' => $alias_to_save,
          'langcode' => $langcode,
          'status' => 1,
        ]);
        $new_alias->save();
      }
      $created++;
    }

    fclose($fh);

    $this->logger()->notice(sprintf(
      '%s: created %d, updated %d, skipped %d, conflicts %d. File: %s',
      $dry ? 'Dry-run' : 'Update aliases',
      $created,
      $updated,
      $skipped,
      $conflicts,
      $file,
    ));
  }

  /**
   * Project root (Composer root): parent of the Drupal root when composer.json lives there.
   *
   * With DDEV, this path is the mounted repo directory (same as on the host).
   */
  private function getProjectRootDirectory(): string {
    $drupal_root = \Drupal::root();
    $parent = dirname($drupal_root);
    if (is_file($parent . '/composer.json')) {
      return $parent;
    }
    return $drupal_root;
  }

  /**
   * Prints a newline-separated list of FR/ES public paths (for QA after deploy).
   */
  #[CLI\Command(name: 'motaded:fr-es-list-sources')]
  public function listSources(): void {
    $storage = $this->entityTypeManager->getStorage('path_alias');
    $last_id = 0;
    while (TRUE) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('id', $last_id, '>')
        ->condition('langcode', ['fr', 'es'], 'IN')
        ->condition('status', TRUE)
        ->sort('id', 'ASC')
        ->range(0, 200)
        ->execute();

      if ($ids === NULL || $ids === []) {
        break;
      }

      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $alias_entity) {
        if ($alias_entity instanceof PathAliasInterface) {
          $path = $this->publicSourcePathForAlias($alias_entity);
          if (!$this->shouldSkipSourcePath($this->normalizeRedirectSourceKey($path))) {
            $this->output()->writeln($path);
          }
        }
      }
    }
  }

  private function publicSourcePathForAlias(PathAliasInterface $alias): string {
    $langcode = $alias->language()->getId();
    $prefix = self::LANG_PREFIX[$langcode] ?? $langcode;
    $raw = trim($alias->getAlias(), '/');

    if ($raw === '') {
      return '/' . $prefix;
    }

    if ($raw === $prefix || str_starts_with($raw, $prefix . '/')) {
      return '/' . $raw;
    }

    return '/' . $prefix . '/' . $raw;
  }

  /**
   * Strips a leading "en/" segment when English is default and has no URL prefix.
   *
   * Path aliases sometimes store "en/business-setup" while the public EN URL is
   * "/business-setup".
   */
  private function normalizePublicEnglishPath(string $path): string {
    $path = $this->normalizeInternalPath($path);
    if ($path === '/') {
      return '/';
    }
    if ($this->languageManager->getDefaultLanguage()->getId() !== 'en') {
      return $path;
    }
    $trimmed = ltrim($path, '/');
    if ($trimmed === 'en') {
      return '/';
    }
    if (str_starts_with($trimmed, 'en/')) {
      $rest = substr($trimmed, strlen('en/'));
      return $this->normalizeInternalPath($rest);
    }
    return $path;
  }

  /**
   * Builds a valid redirect module destination URI (never "internal://").
   */
  private function redirectDestinationUri(string $target_path): string {
    $path = $this->normalizeInternalPath($target_path);
    return 'internal:' . $path;
  }

  /**
   * Converts a CSV target value into a redirect destination URI.
   *
   * Accepts:
   * - internal paths like /career, career
   * - absolute URLs (https://example.com/path)
   */
  private function destinationUriFromCsvTarget(string $target): string {
    $target = trim($target);
    if ($target === '') {
      return 'internal:/';
    }
    // Normalize common malformed internal URIs that can appear in DB/inputs.
    $target = ltrim($target, '/');
    if (str_starts_with($target, 'internal:/internal:')) {
      $target = str_replace('internal:/internal:', 'internal:', $target);
    }
    if (str_starts_with($target, 'internal://')) {
      // internal://foo → internal:/foo
      $target = 'internal:/' . ltrim(substr($target, strlen('internal://')), '/');
    }
    if (preg_match('#^internal:#i', $target)) {
      // Already a destination URI.
      return $target;
    }
    if (preg_match('#^https?://#i', $target)) {
      return $target;
    }
    return $this->redirectDestinationUri($target);
  }

  /**
   * Generic scope guard for bulk imports (do not create redirects for admin paths).
   */
  private function shouldSkipAnySourcePath(string $source_key): bool {
    if ($source_key === '') {
      return TRUE;
    }
    if (preg_match('#(^|/)admin(/|$)#', $source_key)) {
      return TRUE;
    }
    if (preg_match('#(^|/)user(/|$)#', $source_key)) {
      return TRUE;
    }
    if (preg_match('#(^|/)(batch|cron|devel|filter|system)(/|$)#', $source_key)) {
      return TRUE;
    }
    return FALSE;
  }

  private function normalizeInternalPath(string $path): string {
    return '/' . trim($path, '/');
  }

  /**
   * Converts a public CSV path into a path_alias alias value for a language.
   */
  private function aliasValueFromPublicPath(string $public_path, string $langcode): string {
    $public_path = $this->normalizeInternalPath($public_path);
    if ($public_path === '/') {
      // Don't try to map a node alias to the site front page.
      return '';
    }

    // Strip language prefix if present (e.g. /ar/blog/foo → /blog/foo).
    $trimmed = ltrim($public_path, '/');
    if ($langcode !== '' && ($trimmed === $langcode || str_starts_with($trimmed, $langcode . '/'))) {
      $rest = $trimmed === $langcode ? '' : substr($trimmed, strlen($langcode . '/'));
      $public_path = $this->normalizeInternalPath($rest);
      if ($public_path === '/') {
        return '';
      }
    }

    return $public_path;
  }

  private function normalizeRedirectSourceKey(string $path_with_slash): string {
    return trim($path_with_slash, '/');
  }

  /**
   * Whether a redirect already exists for this source path (no RedirectRepository).
   *
   * Avoids findMatchingRedirect(), which may load all redirects and hit malformed
   * destinations (e.g. "//") in the database.
   */
  private function redirectSourcePathExists(string $source_key): bool {
    $ids = $this->entityTypeManager->getStorage('redirect')->getQuery()
      ->accessCheck(FALSE)
      ->condition('redirect_source.path', $source_key)
      ->range(0, 1)
      ->execute();
    return $ids !== NULL && $ids !== [];
  }

  private function shouldSkipSourcePath(string $source_key): bool {
    if ($source_key === '') {
      return TRUE;
    }
    if (preg_match('#(^|/)admin(/|$)#', $source_key)) {
      return TRUE;
    }
    if (preg_match('#(^|/)user(/|$)#', $source_key)) {
      return TRUE;
    }
    if (preg_match('#(^|/)(batch|cron|devel|filter|system)(/|$)#', $source_key)) {
      return TRUE;
    }
    $first = strtolower(explode('/', $source_key, 2)[0]);
    return !in_array($first, self::SOURCE_LANG_PREFIX, TRUE);
  }

  private function pathsEquivalentForRedirect(string $source_display, string $target_path): bool {
    $a = trim($source_display, '/');
    $b = trim($target_path, '/');
    return $a === $b;
  }

  private function resolveEnglishPath(string $internalPath): string {
    if (preg_match('#^/node/(\d+)$#', $internalPath, $m)) {
      return $this->nodeEnglishPath((int) $m[1]);
    }
    if (preg_match('#^/taxonomy/term/(\d+)$#', $internalPath, $m)) {
      return $this->termEnglishPath((int) $m[1]);
    }

    $en_alias = $this->aliasManager->getAliasByPath($internalPath, 'en');
    if ($en_alias !== $internalPath && $en_alias !== '') {
      return $this->normalizeInternalPath($en_alias);
    }

    return $internalPath;
  }

  private function nodeEnglishPath(int $nid): string {
    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$node instanceof NodeInterface) {
      return '/';
    }
    if (!$node->hasTranslation('en')) {
      return '/';
    }
    $en = $node->getTranslation('en');
    if (!$en->isPublished()) {
      return '/';
    }
    $internal = '/node/' . $nid;
    $alias = $this->aliasManager->getAliasByPath($internal, 'en');
    return $alias !== $internal && $alias !== ''
      ? $this->normalizeInternalPath($alias)
      : $internal;
  }

  private function termEnglishPath(int $tid): string {
    $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
    if (!$term instanceof TermInterface) {
      return '/';
    }
    if (!$term->hasTranslation('en')) {
      return '/';
    }
    $en = $term->getTranslation('en');
    if (!$en->isPublished()) {
      return '/';
    }
    $internal = '/taxonomy/term/' . $tid;
    $alias = $this->aliasManager->getAliasByPath($internal, 'en');
    return $alias !== $internal && $alias !== ''
      ? $this->normalizeInternalPath($alias)
      : $internal;
  }

  private function removeTranslationsFromEntityType(string $entity_type, string $table, bool $dry, int &$warned): int {
    $removed = 0;
    $q = $this->database->select($table, 't')
      ->fields('t', [$entity_type === 'node' ? 'nid' : 'tid', 'langcode'])
      ->condition('langcode', ['fr', 'es'], 'IN');
    $id_key = $entity_type === 'node' ? 'nid' : 'tid';
    $ids = [];
    foreach ($q->execute()->fetchAll() as $row) {
      $ids[(int) $row->{$id_key}][] = $row->langcode;
    }

    $storage = $this->entityTypeManager->getStorage($entity_type);
    foreach ($ids as $entity_id => $langcodes) {
      $entity = $storage->load($entity_id);
      if (!$entity instanceof TranslatableInterface) {
        continue;
      }
      $default = $entity->language()->getId();
      $changed = FALSE;
      foreach (array_unique($langcodes) as $lang) {
        if (!in_array($lang, ['fr', 'es'], TRUE)) {
          continue;
        }
        if ($lang === $default) {
          $this->logger()->warning(sprintf(
            '%s %s default language is %s; not removed (handle manually).',
            $entity_type,
            (string) $entity_id,
            $lang,
          ));
          $warned++;
          continue;
        }
        if ($entity->hasTranslation($lang)) {
          if (!$dry) {
            $entity->removeTranslation($lang);
          }
          $changed = TRUE;
          $removed++;
        }
      }
      if ($changed && !$dry) {
        $entity->save();
      }
    }

    return $removed;
  }

}
