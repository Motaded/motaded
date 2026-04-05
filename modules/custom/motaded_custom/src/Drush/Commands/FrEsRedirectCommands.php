<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\FieldItemInterface;
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

  /**
   * Field types scanned for duplicate /services/services and /blog/blog segments.
   */
  private const DUPLICATE_PREFIX_FIELD_TYPES = [
    'text',
    'text_long',
    'text_with_summary',
    'string_long',
    'string',
    'link',
  ];

  /**
   * Entity types skipped by default for motaded:rewrite-urls-from-full-url-csv (safety).
   */
  private const URL_REWRITE_DEFAULT_EXCLUDED_ENTITY_TYPES = [
    'user',
    'file',
    'path_alias',
    'redirect',
    // Search API queue rows: SQL table mapping can throw "'' not found"; not public content.
    'search_api_task',
  ];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly AliasManagerInterface $aliasManager,
    protected readonly Connection $database,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly EntityFieldManagerInterface $entityFieldManager,
    protected readonly EntityTypeBundleInfoInterface $entityTypeBundleInfo,
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
   * Exports CSV: "short" public path → canonical path with /blog, /services, or /news.
   *
   * For each active path_alias whose alias looks like /{lang}/blog/... or /blog/..., builds:
   * - new_path = alias as stored (canonical, with prefix)
   * - old_path = same path with the first segment (blog|services|news) removed, keeping optional
   *   language prefix (e.g. /ar/blog/foo → old /ar/foo, /blog/foo → old /foo).
   *
   * Use for bulk replacing legacy links in HTML (href without prefix → href with prefix).
   */
  #[CLI\Command(name: 'motaded:export-url-prefix-map')]
  #[CLI\Option(name: 'output', description: 'CSV file path (default: url-prefix-map.csv in Composer project root).')]
  #[CLI\Option(name: 'stdout', description: 'Print CSV to STDOUT instead of writing a file.')]
  #[CLI\Option(name: 'base-url', description: 'Site base URL without trailing slash; adds old_url and new_url columns.')]
  #[CLI\Option(name: 'langcodes', description: 'Comma-separated path_alias.langcode filter (empty = all).')]
  #[CLI\Usage(name: 'drush motaded:export-url-prefix-map --base-url=https://motaded.com.sa', description: 'Writes url-prefix-map.csv with old_path/new_path and full URLs.')]
  public function exportUrlPrefixMap(array $options = ['output' => NULL, 'stdout' => FALSE, 'base-url' => NULL, 'langcodes' => NULL]): void {
    $base = rtrim((string) ($options['base-url'] ?? ''), '/');
    $has_base = $base !== '';

    $use_stdout = filter_var($options['stdout'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $path = $options['output'] ?? NULL;
    $path = $path !== NULL && $path !== '' ? (string) $path : NULL;

    $langcodes_filter = [];
    $langcodes_raw = trim((string) ($options['langcodes'] ?? ''));
    if ($langcodes_raw !== '') {
      $langcodes_filter = array_values(array_filter(array_map('trim', explode(',', $langcodes_raw)), static fn($v) => $v !== ''));
    }

    if ($use_stdout) {
      $path = NULL;
      $target_label = 'STDOUT';
    }
    elseif ($path === NULL) {
      $path = $this->getProjectRootDirectory() . '/url-prefix-map.csv';
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

    $header = ['old_path', 'new_path'];
    if ($has_base) {
      $header[] = 'old_url';
      $header[] = 'new_url';
    }
    $header[] = 'langcode';
    $header[] = 'drupal_internal_path';
    fputcsv($fh, $header);

    $q = $this->database->select('path_alias', 'pa')
      ->fields('pa', ['alias', 'path', 'langcode'])
      ->condition('status', 1)
      ->orderBy('alias');

    if ($langcodes_filter !== []) {
      $q->condition('langcode', $langcodes_filter, 'IN');
    }

    /** @var list<array{alias: string, path: string, langcode: string}> $rows */
    $rows = $q->execute()->fetchAll(\PDO::FETCH_ASSOC);

    $seen_old = [];
    $written = 0;
    $skipped_dup = 0;

    foreach ($rows as $row) {
      $alias_raw = (string) ($row['alias'] ?? '');
      $internal = (string) ($row['path'] ?? '');
      $langcode = (string) ($row['langcode'] ?? '');
      if ($alias_raw === '') {
        continue;
      }

      $alias_norm = $this->normalizeInternalPath('/' . trim($alias_raw, '/'));
      if ($alias_norm === '/') {
        continue;
      }

      if (!preg_match('#^/(?:(?P<lang>[a-z]{2}(?:-[a-zA-Z0-9]+)?)/)?(?P<cat>blog|services|news)/(?P<rest>.+)$#iu', $alias_norm, $m)) {
        continue;
      }

      $rest = (string) $m['rest'];
      if ($rest === '') {
        continue;
      }

      $new_path = $alias_norm;
      if (!empty($m['lang'])) {
        $old_path = $this->normalizeInternalPath('/' . $m['lang'] . '/' . $rest);
      }
      else {
        $old_path = $this->normalizeInternalPath('/' . $rest);
      }

      if ($old_path === $new_path) {
        continue;
      }

      if (isset($seen_old[$old_path])) {
        if ($seen_old[$old_path] === $new_path) {
          continue;
        }
        $this->logger()->warning(sprintf(
          'Skip conflicting old_path %s (already mapped to %s, also saw %s).',
          $old_path,
          $seen_old[$old_path],
          $new_path,
        ));
        $skipped_dup++;
        continue;
      }
      $seen_old[$old_path] = $new_path;

      $out = [$old_path, $new_path];
      if ($has_base) {
        $out[] = $base . $old_path;
        $out[] = $base . $new_path;
      }
      $out[] = $langcode;
      $out[] = $this->normalizeInternalPath($internal);
      fputcsv($fh, $out);
      $written++;
    }

    fclose($fh);

    $this->logger()->notice(sprintf(
      'Exported %d mapping row(s) to %s.%s',
      $written,
      $target_label,
      $skipped_dup > 0 ? sprintf(' Skipped %d conflicting duplicate(s).', $skipped_dup) : '',
    ));
  }

  /**
   * Scans content fields for legacy URLs from url-prefix-map.csv (read-only audit).
   *
   * Expects CSV from motaded:export-url-prefix-map with columns old_path, new_path.
   * Writes a report of matches (hits) per entity translation, field, and map row.
   */
  #[CLI\Command(name: 'motaded:audit-url-prefix-map-in-content')]
  #[CLI\Option(name: 'file', description: 'Path to url-prefix-map.csv (absolute path in container for DDEV).')]
  #[CLI\Option(name: 'output', description: 'Report CSV (default: url-prefix-map-audit.csv in Composer project root).')]
  #[CLI\Option(name: 'types', description: 'Comma-separated entity type IDs (default: node,paragraph,block_content,taxonomy_term).')]
  #[CLI\Usage(name: 'drush motaded:audit-url-prefix-map-in-content --file=/var/www/html/url-prefix-map.csv', description: 'Audit content for legacy short paths.')]
  public function auditUrlPrefixMapInContent(array $options = ['file' => NULL, 'output' => NULL, 'types' => 'node,paragraph,block_content,taxonomy_term']): void {
    $file = trim((string) ($options['file'] ?? ''));
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Use an absolute path (e.g. /var/www/html/url-prefix-map.csv in DDEV).');
    }

    $map = $this->loadUrlPrefixMapFromCsv($file);
    if ($map === []) {
      throw new \RuntimeException('No rows loaded from CSV (need old_path and new_path columns).');
    }

    $output = trim((string) ($options['output'] ?? ''));
    if ($output === '') {
      $output = $this->getProjectRootDirectory() . '/url-prefix-map-audit.csv';
    }

    $fh = fopen($output, 'wb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open report: %s', $output));
    }
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, [
      'entity_type',
      'entity_id',
      'langcode',
      'field_name',
      'old_path',
      'new_path',
      'hits_total',
      'hits_full_url',
      'hits_internal',
      'hits_relative',
    ]);

    $types_raw = (string) ($options['types'] ?? 'node,paragraph,block_content,taxonomy_term');
    $entity_type_ids = array_values(array_filter(array_map('trim', explode(',', $types_raw)), static fn($v) => $v !== ''));

    $audit_rows = 0;
    foreach ($entity_type_ids as $entity_type_id) {
      if (!$this->entityTypeManager->hasDefinition($entity_type_id)) {
        $this->logger()->warning(sprintf('Unknown entity type, skipping: %s', $entity_type_id));
        continue;
      }
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle_key = $entity_type->getKey('bundle');
      $id_key = $entity_type->getKey('id');
      if (!$id_key || $bundle_key === NULL) {
        continue;
      }
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      foreach (array_keys($bundles) as $bundle) {
        $field_map = $this->duplicatePrefixProcessableFields($entity_type_id, (string) $bundle);
        if ($field_map === []) {
          continue;
        }
        $this->processUrlMapAuditEntityBatch(
          $entity_type_id,
          $storage,
          $id_key,
          $bundle_key,
          (string) $bundle,
          $field_map,
          $map,
          $fh,
          $audit_rows,
        );
      }
    }

    fclose($fh);
    $this->logger()->notice(sprintf('Wrote %d audit row(s) to %s.', $audit_rows, $output));
  }

  /**
   * Rewrites legacy short paths in content fields using url-prefix-map.csv.
   *
   * Applies the same replacements as the audit counts (relative, internal:, motaded host).
   * After each field item, collapses /blog/blog and /services/services to avoid doubling.
   */
  #[CLI\Command(name: 'motaded:fix-url-prefix-map-in-content')]
  #[CLI\Option(name: 'file', description: 'Path to url-prefix-map.csv (old_path, new_path).')]
  #[CLI\Option(name: 'audit', description: 'Optional url-prefix-map-audit.csv: only entity/field rows listed there are processed.')]
  #[CLI\Option(name: 'dry-run', description: 'Preview replacements without saving (default: true).')]
  #[CLI\Option(name: 'types', description: 'Comma-separated entity type IDs when not using --audit (default: node,paragraph,block_content,taxonomy_term).')]
  #[CLI\Option(name: 'report', description: 'Optional CSV path listing each changed field and replacement count.')]
  #[CLI\Usage(name: 'drush motaded:fix-url-prefix-map-in-content --file=/var/www/html/url-prefix-map.csv --dry-run', description: 'Preview URL rewrites.')]
  #[CLI\Usage(name: 'drush motaded:fix-url-prefix-map-in-content --file=/var/www/html/url-prefix-map.csv --audit=/var/www/html/url-prefix-map-audit.csv --dry-run=0', description: 'Fix only audited rows.')]
  public function fixUrlPrefixMapInContent(array $options = [
    'file' => NULL,
    'audit' => NULL,
    'dry-run' => TRUE,
    'types' => 'node,paragraph,block_content,taxonomy_term',
    'report' => NULL,
  ]): void {
    $file = trim((string) ($options['file'] ?? ''));
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Use an absolute path (e.g. /var/www/html/url-prefix-map.csv in DDEV).');
    }

    $map = $this->loadUrlPrefixMapFromCsv($file);
    if ($map === []) {
      throw new \RuntimeException('No rows loaded from CSV (need old_path and new_path columns).');
    }

    $dry = filter_var($options['dry-run'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $audit_path = trim((string) ($options['audit'] ?? ''));
    $report_path = trim((string) ($options['report'] ?? ''));
    $report_fh = NULL;
    if ($report_path !== '') {
      $report_fh = fopen($report_path, 'wb');
      if ($report_fh === FALSE) {
        throw new \RuntimeException(sprintf('Cannot open report: %s', $report_path));
      }
      fwrite($report_fh, "\xEF\xBB\xBF");
      fputcsv($report_fh, ['entity_type', 'entity_id', 'langcode', 'field_name', 'replacements']);
    }

    $changed_entities = 0;
    $changed_fields = 0;
    $total_replacements = 0;

    if ($audit_path !== '') {
      if (!is_file($audit_path)) {
        throw new \InvalidArgumentException(sprintf('Unreadable --audit file: %s', $audit_path));
      }
      $targets = $this->loadUrlPrefixMapAuditTargets($audit_path);
      if ($targets === []) {
        $this->logger()->warning('No rows loaded from audit CSV; nothing to do.');
        if ($report_fh !== NULL) {
          fclose($report_fh);
        }
        return;
      }
      foreach ($targets as $target) {
        $entity_type_id = $target['entity_type'];
        $entity_id = $target['entity_id'];
        $langcode = $target['langcode'];
        $field_filter = $target['fields'];
        if (!$this->entityTypeManager->hasDefinition($entity_type_id)) {
          continue;
        }
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $entity = $storage->load($entity_id);
        if (!$entity instanceof FieldableEntityInterface || !$entity instanceof TranslatableInterface) {
          continue;
        }
        if (!$entity->hasTranslation($langcode)) {
          continue;
        }
        $t = $entity->getTranslation($langcode);
        if (!$t instanceof FieldableEntityInterface) {
          continue;
        }
        $bundle = $entity->bundle();
        $field_map = $this->duplicatePrefixProcessableFields($entity_type_id, $bundle);
        $allowed = array_intersect_key($field_map, $field_filter);
        if ($allowed === []) {
          continue;
        }
        $n = $this->applyUrlPrefixMapFixToFieldable(
          $t,
          $allowed,
          $map,
          $entity_type_id,
          $entity_id,
          $langcode,
          $total_replacements,
          $report_fh,
        );
        if ($n > 0) {
          $changed_fields += $n;
          if (!$dry) {
            $t->save();
          }
          $changed_entities++;
        }
      }
    }
    else {
      $types_raw = (string) ($options['types'] ?? 'node,paragraph,block_content,taxonomy_term');
      $entity_type_ids = array_values(array_filter(array_map('trim', explode(',', $types_raw)), static fn($v) => $v !== ''));
      foreach ($entity_type_ids as $entity_type_id) {
        if (!$this->entityTypeManager->hasDefinition($entity_type_id)) {
          $this->logger()->warning(sprintf('Unknown entity type, skipping: %s', $entity_type_id));
          continue;
        }
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
        $bundle_key = $entity_type->getKey('bundle');
        $id_key = $entity_type->getKey('id');
        if (!$id_key || $bundle_key === NULL) {
          continue;
        }
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
        $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
        foreach (array_keys($bundles) as $bundle) {
          $field_map = $this->duplicatePrefixProcessableFields($entity_type_id, (string) $bundle);
          if ($field_map === []) {
            continue;
          }
          $this->processUrlPrefixMapFixEntityBatch(
            $entity_type_id,
            $storage,
            $id_key,
            $bundle_key,
            (string) $bundle,
            $field_map,
            $map,
            $dry,
            $changed_entities,
            $changed_fields,
            $total_replacements,
            $report_fh,
          );
        }
      }
    }

    if ($report_fh !== NULL) {
      fclose($report_fh);
      $this->logger()->notice(sprintf('Wrote report: %s', $report_path));
    }

    $this->logger()->notice(sprintf(
      $dry
        ? 'Dry-run: would save %d entity translation(s), touch %d field(s), apply %d replacement(s).'
        : 'Updated: saved %d entity translation(s), touched %d field(s), applied %d replacement(s).',
      $changed_entities,
      $changed_fields,
      $total_replacements,
    ));
  }

  /**
   * @return list<array{entity_type: string, entity_id: int, langcode: string, fields: array<string, true>}>
   */
  private function loadUrlPrefixMapAuditTargets(string $file): array {
    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open audit CSV: %s', $file));
    }
    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('Audit CSV header row is missing/invalid.');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
    $col = array_flip($header);
    foreach (['entity_type', 'entity_id', 'langcode', 'field_name'] as $required) {
      if (!isset($col[$required])) {
        fclose($fh);
        throw new \InvalidArgumentException(sprintf('Audit CSV must include column: %s', $required));
      }
    }

    $merged = [];
    while (($row = fgetcsv($fh)) !== FALSE) {
      if (!is_array($row) || $row === []) {
        continue;
      }
      $type = trim((string) ($row[$col['entity_type']] ?? ''));
      $id = (int) ($row[$col['entity_id']] ?? 0);
      $lang = trim((string) ($row[$col['langcode']] ?? ''));
      $fname = trim((string) ($row[$col['field_name']] ?? ''));
      if ($type === '' || $id < 1 || $lang === '' || $fname === '') {
        continue;
      }
      $key = $type . ':' . $id . ':' . $lang;
      if (!isset($merged[$key])) {
        $merged[$key] = [
          'entity_type' => $type,
          'entity_id' => $id,
          'langcode' => $lang,
          'fields' => [],
        ];
      }
      $merged[$key]['fields'][$fname] = TRUE;
    }
    fclose($fh);
    return array_values($merged);
  }

  /**
   * @param array<string, string> $field_map field name => field type id (subset allowed)
   * @param array<string, string> $map old_path => new_path
   * @param resource|null $report_fh
   *
   * @return int number of fields that had at least one replacement (including collapse)
   */
  private function applyUrlPrefixMapFixToFieldable(
    FieldableEntityInterface $entity,
    array $field_map,
    array $map,
    string $entity_type_id,
    int $entity_id,
    string $langcode,
    int &$total_replacements,
    $report_fh,
  ): int {
    $fields_touched = 0;
    foreach ($field_map as $field_name => $field_type) {
      if (!$entity->hasField($field_name)) {
        continue;
      }
      $list = $entity->get($field_name);
      if ($list->isEmpty()) {
        continue;
      }
      $field_repls = 0;
      foreach ($list as $item) {
        if (!$item instanceof FieldItemInterface) {
          continue;
        }
        $field_repls += $this->applyUrlPrefixMapReplacementsToFieldItem($item, $field_type, $map);
      }
      if ($field_repls > 0) {
        $fields_touched++;
        $total_replacements += $field_repls;
        if ($report_fh !== NULL && $report_fh !== FALSE) {
          fputcsv($report_fh, [$entity_type_id, (string) $entity_id, $langcode, $field_name, (string) $field_repls]);
        }
      }
    }
    return $fields_touched;
  }

  /**
   * Applies map replacements then collapses duplicate /blog/blog segments on the item.
   *
   * @param array<string, string> $map
   */
  private function applyUrlPrefixMapReplacementsToFieldItem(
    FieldItemInterface $item,
    string $field_type,
    array $map,
  ): int {
    $repl = 0;
    if ($field_type === 'link') {
      $uri = (string) $item->get('uri')->getString();
      if ($uri === '') {
        return 0;
      }
      foreach ($map as $old_path => $new_path) {
        if (!$this->textMightContainLegacyPath($uri, $old_path)) {
          continue;
        }
        if ($this->countLegacyPathOccurrencesInText($uri, $old_path)['total'] === 0) {
          continue;
        }
        $uri = $this->replacePathOccurrencesForLinkUri($uri, $old_path, $new_path, $repl);
      }
      $item->set('uri', $uri);
      $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, 'link');
      return $repl;
    }
    if ($field_type === 'text_with_summary') {
      foreach (['value', 'summary'] as $prop) {
        $chunk = (string) $item->get($prop)->getString();
        if ($chunk === '') {
          continue;
        }
        foreach ($map as $old_path => $new_path) {
          if (!$this->textMightContainLegacyPath($chunk, $old_path)) {
            continue;
          }
          if ($this->countLegacyPathOccurrencesInText($chunk, $old_path)['total'] === 0) {
            continue;
          }
          $chunk = $this->replacePathOccurrencesWithMotadedHost($chunk, $old_path, $new_path, $repl);
        }
        $item->set($prop, $chunk);
      }
      $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, 'text_with_summary');
      return $repl;
    }
    $chunk = (string) $item->get('value')->getString();
    if ($chunk === '') {
      return 0;
    }
    foreach ($map as $old_path => $new_path) {
      if (!$this->textMightContainLegacyPath($chunk, $old_path)) {
        continue;
      }
      if ($this->countLegacyPathOccurrencesInText($chunk, $old_path)['total'] === 0) {
        continue;
      }
      $chunk = $this->replacePathOccurrencesWithMotadedHost($chunk, $old_path, $new_path, $repl);
    }
    $item->set('value', $chunk);
    $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, $field_type);
    return $repl;
  }

  /**
   * @param array<string, string> $field_map
   * @param array<string, string> $map
   * @param resource|null $report_fh
   */
  private function processUrlPrefixMapFixEntityBatch(
    string $entity_type_id,
    $storage,
    string $id_key,
    string $bundle_property,
    string $bundle_id,
    array $field_map,
    array $map,
    bool $dry,
    int &$changed_entities,
    int &$changed_fields,
    int &$total_replacements,
    $report_fh,
  ): void {
    $last_id = 0;
    $batch = 80;
    while (TRUE) {
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition($bundle_property, $bundle_id)
        ->condition($id_key, $last_id, '>')
        ->sort($id_key, 'ASC')
        ->range(0, $batch);
      $ids = $query->execute();
      if ($ids === NULL || $ids === []) {
        break;
      }
      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $entity) {
        if (!$entity instanceof FieldableEntityInterface) {
          continue;
        }
        if ($entity->bundle() !== $bundle_id) {
          continue;
        }
        $to_save_langs = [];
        if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
          foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
            $t = $entity->getTranslation($langcode);
            if (!$t instanceof FieldableEntityInterface) {
              continue;
            }
            $n = $this->applyUrlPrefixMapFixToFieldable(
              $t,
              $field_map,
              $map,
              $entity_type_id,
              (int) $entity->id(),
              (string) $langcode,
              $total_replacements,
              $report_fh,
            );
            if ($n > 0) {
              $to_save_langs[] = $langcode;
              $changed_fields += $n;
            }
          }
          foreach ($to_save_langs as $langcode) {
            $tr = $entity->getTranslation($langcode);
            if (!$dry) {
              $tr->save();
            }
            $changed_entities++;
          }
        }
        else {
          $n = $this->applyUrlPrefixMapFixToFieldable(
            $entity,
            $field_map,
            $map,
            $entity_type_id,
            (int) $entity->id(),
            $entity->language()->getId(),
            $total_replacements,
            $report_fh,
          );
          if ($n > 0) {
            $changed_fields += $n;
            if (!$dry) {
              $entity->save();
            }
            $changed_entities++;
          }
        }
      }
    }
  }

  /**
   * @param array<string, string> $map old_path => new_path
   * @param resource $report_fh
   */
  private function processUrlMapAuditEntityBatch(
    string $entity_type_id,
    $storage,
    string $id_key,
    string $bundle_property,
    string $bundle_id,
    array $field_map,
    array $map,
    $report_fh,
    int &$audit_rows,
  ): void {
    $last_id = 0;
    $batch = 80;
    while (TRUE) {
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition($bundle_property, $bundle_id)
        ->condition($id_key, $last_id, '>')
        ->sort($id_key, 'ASC')
        ->range(0, $batch);
      $ids = $query->execute();
      if ($ids === NULL || $ids === []) {
        break;
      }
      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $entity) {
        if (!$entity instanceof FieldableEntityInterface) {
          continue;
        }
        if ($entity->bundle() !== $bundle_id) {
          continue;
        }
        if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
          foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
            $t = $entity->getTranslation($langcode);
            if (!$t instanceof FieldableEntityInterface) {
              continue;
            }
            $audit_rows += $this->applyAuditUrlMapToFieldable(
              $t,
              $field_map,
              $map,
              $report_fh,
              $entity_type_id,
              (int) $entity->id(),
              (string) $langcode,
            );
          }
        }
        else {
          $audit_rows += $this->applyAuditUrlMapToFieldable(
            $entity,
            $field_map,
            $map,
            $report_fh,
            $entity_type_id,
            (int) $entity->id(),
            $entity->language()->getId(),
          );
        }
      }
    }
  }

  /**
   * @param array<string, string> $map old_path => new_path
   * @param resource $report_fh
   */
  private function applyAuditUrlMapToFieldable(
    FieldableEntityInterface $entity,
    array $field_map,
    array $map,
    $report_fh,
    string $entity_type_id,
    int $entity_id,
    string $langcode,
  ): int {
    $rows = 0;
    foreach ($field_map as $field_name => $field_type) {
      if (!$entity->hasField($field_name)) {
        continue;
      }
      $list = $entity->get($field_name);
      if ($list->isEmpty()) {
        continue;
      }
      $chunks = [];
      foreach ($list as $item) {
        if (!$item instanceof FieldItemInterface) {
          continue;
        }
        if ($field_type === 'link') {
          $chunks[] = (string) $item->get('uri')->getString();
        }
        elseif ($field_type === 'text_with_summary') {
          $chunks[] = (string) $item->get('value')->getString();
          $chunks[] = (string) $item->get('summary')->getString();
        }
        else {
          $chunks[] = (string) $item->get('value')->getString();
        }
      }
      $combined = implode("\n", array_filter($chunks, static fn($s) => $s !== ''));
      if ($combined === '') {
        continue;
      }
      foreach ($map as $old_path => $new_path) {
        if (!$this->textMightContainLegacyPath($combined, $old_path)) {
          continue;
        }
        $c = $this->countLegacyPathOccurrencesInText($combined, $old_path);
        if ($c['total'] > 0) {
          fputcsv($report_fh, [
            $entity_type_id,
            (string) $entity_id,
            $langcode,
            $field_name,
            $old_path,
            $new_path,
            (string) $c['total'],
            (string) $c['full'],
            (string) $c['internal'],
            (string) $c['relative'],
          ]);
          $rows++;
        }
      }
    }
    return $rows;
  }

  /**
   * @return array<string, string>
   */
  private function loadUrlPrefixMapFromCsv(string $file): array {
    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV: %s', $file));
    }
    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('CSV header row is missing/invalid.');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];
    $col = array_flip($header);
    if (!isset($col['old_path'], $col['new_path'])) {
      fclose($fh);
      throw new \InvalidArgumentException('CSV must include old_path and new_path columns.');
    }

    $map = [];
    while (($row = fgetcsv($fh)) !== FALSE) {
      if (!is_array($row) || $row === []) {
        continue;
      }
      $old = $this->normalizeInternalPath(trim((string) ($row[$col['old_path']] ?? '')));
      $new = $this->normalizeInternalPath(trim((string) ($row[$col['new_path']] ?? '')));
      if ($old === '' || $old === '/') {
        continue;
      }
      if (!isset($map[$old])) {
        $map[$old] = $new;
      }
    }
    fclose($fh);
    return $map;
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
   * Rewrites old internal links inside content fields using a mapping CSV.
   *
   * Input file format: "Rebuilding url - Old links in content.csv"
   * Columns:
   * - entity_type, entity_id, langcode, table, column, old_paths, new_paths
   *
   * Currently supported targets:
   * - node__body.body_value (field: body, property: value)
   * - node__field_keywords.field_keywords_value (field: field_keywords, property: value)
   * - block_content__body.body_value (field: body, property: value)
   * - paragraph__field_link.field_link_uri (field: field_link, property: uri)
   */
  #[CLI\Command(name: 'motaded:rebuild-url-fix-content-links')]
  #[CLI\Option(name: 'file', description: 'Path to CSV file to import.')]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving entities.')]
  #[CLI\Option(name: 'limit', description: 'Max rows to process (0 = no limit).')]
  #[CLI\Option(name: 'report', description: 'Write details CSV report to this file (skipped/missing/unsupported).')]
  #[CLI\Usage(name: 'drush motaded:rebuild-url-fix-content-links --file=/tmp/old-links.csv --dry-run', description: 'Dry-run rewrite internal links in content fields.')]
  public function fixContentLinksFromCsv(array $options = ['file' => NULL, 'dry-run' => FALSE, 'limit' => 0, 'report' => NULL]): void {
    $file = (string) ($options['file'] ?? '');
    if ($file === '' || !is_file($file)) {
      throw new \InvalidArgumentException('Missing or unreadable --file. Provide an absolute path to the CSV.');
    }

    $dry = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $limit = max(0, (int) ($options['limit'] ?? 0));
    $report_path = (string) ($options['report'] ?? '');
    $report_path = trim($report_path) !== '' ? $report_path : '';
    $report_fh = NULL;
    if ($report_path !== '') {
      $report_fh = fopen($report_path, 'wb');
      if ($report_fh === FALSE) {
        throw new \RuntimeException(sprintf('Cannot open report file for writing: %s', $report_path));
      }
      fwrite($report_fh, "\xEF\xBB\xBF");
      fputcsv($report_fh, [
        'category',
        'entity_type',
        'entity_id',
        'langcode',
        'table',
        'column',
        'old_paths',
        'new_paths',
        'note',
      ]);
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

    $required = ['entity_type', 'entity_id', 'langcode', 'table', 'column', 'old_paths', 'new_paths'];
    foreach ($required as $name) {
      if (!array_key_exists($name, $col)) {
        fclose($fh);
        throw new \InvalidArgumentException(sprintf('CSV missing required column: %s', $name));
      }
    }

    $processed = 0;
    $changed_entities = 0;
    $changed_links = 0;
    $skipped = 0;
    $unsupported = 0;
    $missing_entity = 0;

    while (($row = fgetcsv($fh)) !== FALSE) {
      if (!is_array($row) || $row === []) {
        continue;
      }
      $processed++;
      if ($limit > 0 && $processed > $limit) {
        break;
      }

      $entity_type = (string) ($row[$col['entity_type']] ?? '');
      $entity_id = (int) ($row[$col['entity_id']] ?? 0);
      $langcode = (string) ($row[$col['langcode']] ?? '');
      $table = (string) ($row[$col['table']] ?? '');
      $column = (string) ($row[$col['column']] ?? '');
      $old_paths_raw = (string) ($row[$col['old_paths']] ?? '');
      $new_paths_raw = (string) ($row[$col['new_paths']] ?? '');

      if ($entity_type === '' || $entity_id <= 0 || $langcode === '') {
        $skipped++;
        if ($report_fh) {
          fputcsv($report_fh, ['skipped', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'missing required identifiers']);
        }
        continue;
      }

      $target = $this->fieldTargetFromTableColumn($entity_type, $table, $column);
      if ($target === NULL) {
        $unsupported++;
        if ($report_fh) {
          fputcsv($report_fh, ['unsupported', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'unsupported table/column']);
        }
        continue;
      }
      [$field_name, $property] = $target;

      $old_paths = array_values(array_filter(array_map('trim', explode(' | ', $old_paths_raw)), static fn($v) => $v !== ''));
      $new_paths = array_values(array_filter(array_map('trim', explode(' | ', $new_paths_raw)), static fn($v) => $v !== ''));
      if ($old_paths === [] || $new_paths === [] || count($old_paths) !== count($new_paths)) {
        $this->logger()->warning(sprintf(
          'Skip %s %d (%s): old/new paths mismatch (%d vs %d).',
          $entity_type,
          $entity_id,
          $langcode,
          count($old_paths),
          count($new_paths),
        ));
        $skipped++;
        if ($report_fh) {
          fputcsv($report_fh, ['skipped', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'old/new paths mismatch']);
        }
        continue;
      }

      $storage = $this->entityTypeManager->getStorage($entity_type);
      $entity = $storage->load($entity_id);
      if (!$entity instanceof TranslatableInterface || !$entity instanceof FieldableEntityInterface) {
        $missing_entity++;
        if ($report_fh) {
          fputcsv($report_fh, ['missing', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'entity missing or not translatable/fieldable']);
        }
        continue;
      }
      if (!$entity->hasTranslation($langcode)) {
        $missing_entity++;
        if ($report_fh) {
          fputcsv($report_fh, ['missing', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'translation missing']);
        }
        continue;
      }
      /** @var \Drupal\Core\Entity\FieldableEntityInterface&\Drupal\Core\Entity\TranslatableInterface $t */
      $t = $entity->getTranslation($langcode);
      if (!$t->hasField($field_name)) {
        $unsupported++;
        if ($report_fh) {
          fputcsv($report_fh, ['unsupported', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'field missing on entity translation']);
        }
        continue;
      }

      $item = $t->get($field_name)->first();
      if ($item === NULL) {
        $skipped++;
        if ($report_fh) {
          fputcsv($report_fh, ['skipped', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'empty field item']);
        }
        continue;
      }
      $value = (string) ($item->{$property} ?? '');
      if ($value === '') {
        $skipped++;
        if ($report_fh) {
          fputcsv($report_fh, ['skipped', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'empty field value']);
        }
        continue;
      }

      $before = $value;
      $local_changed = 0;
      foreach ($old_paths as $i => $old) {
        $new = $new_paths[$i];
        // Only rewrite internal paths (start with /). Keep absolute URLs untouched.
        if ($old === '' || $new === '' || $old[0] !== '/' || $new[0] !== '/') {
          continue;
        }
        $count = 0;
        if ($property === 'uri') {
          $value = $this->replacePathOccurrencesForLinkUri($value, $old, $new, $count);
        }
        else {
          $value = $this->replacePathOccurrencesWithMotadedHost($value, $old, $new, $count);
        }
        $local_changed += $count;
      }

      if ($value === $before) {
        $skipped++;
        if ($report_fh) {
          fputcsv($report_fh, ['skipped', $entity_type, $entity_id, $langcode, $table, $column, $old_paths_raw, $new_paths_raw, 'no changes (already updated)']);
        }
        continue;
      }

      if (!$dry) {
        // Do not overwrite text format/other properties (prevents HTML being escaped).
        $t->get($field_name)->first()?->set($property, $value);
        $t->save();
      }

      $changed_entities++;
      $changed_links += $local_changed;
    }

    fclose($fh);
    if ($report_fh) {
      fclose($report_fh);
      $this->logger()->notice(sprintf('Wrote report: %s', $report_path));
    }

    $this->logger()->notice(sprintf(
      '%s: processed %d row(s). Changed entities: %d. Rewrites: %d. Skipped: %d. Missing: %d. Unsupported: %d. File: %s',
      $dry ? 'Dry-run' : 'Rewrite links',
      $processed,
      $changed_entities,
      $changed_links,
      $skipped,
      $missing_entity,
      $unsupported,
      $file,
    ));
  }

  /**
   * Rewrites full motaded.com.sa URLs across fieldable entities (spreadsheet-style CSV).
   *
   * CSV: two columns — old absolute URL, new absolute URL (e.g. "Alina Task - Sheet1.csv").
   * Rows without http(s):// in column A are skipped (header lines). Longer old URLs run first.
   * Replaces: full URL variants (http/https, www), internal:/path, and path occurrences in text.
   */
  #[CLI\Command(name: 'motaded:rewrite-urls-from-full-url-csv')]
  #[CLI\Option(name: 'file', description: 'CSV path (DDEV: use /var/www/html/... or filename in project root; host /Users/... is not visible in the container).')]
  #[CLI\Option(name: 'dry-run', description: 'Preview without saving (default: true).')]
  #[CLI\Option(name: 'types', description: 'Comma-separated entity type IDs, or * for all fieldable types (default: *).')]
  #[CLI\Option(name: 'exclude-types', description: 'Extra entity types to skip (comma list). Defaults include user,file,path_alias,redirect.')]
  #[CLI\Option(name: 'collapse-duplicates', description: 'After rewrites, collapse /blog/blog and /services/services (default: true).')]
  #[CLI\Option(name: 'report', description: 'Optional CSV: entity_type, entity_id, bundle, entity_label (title for nodes), langcode, field_name, replacements.')]
  #[CLI\Usage(name: 'drush motaded:rewrite-urls-from-full-url-csv --file=/var/www/html/Alina\\ Task\\ -\\ Sheet1.csv --dry-run', description: 'Dry-run (DDEV: path must be inside the container, e.g. /var/www/html/...).')]
  public function rewriteUrlsFromFullUrlCsv(array $options = [
    'file' => NULL,
    'dry-run' => TRUE,
    'types' => '*',
    'exclude-types' => NULL,
    'collapse-duplicates' => TRUE,
    'report' => NULL,
  ]): void {
    $file = $this->resolveReadablePathInProject((string) ($options['file'] ?? ''), 'file');

    $pairs = $this->loadFullUrlRewritePairsFromCsv($file);
    if ($pairs === []) {
      throw new \RuntimeException('No valid URL pairs loaded from CSV (need two columns with http(s) URLs).');
    }

    $dry = filter_var($options['dry-run'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $collapse = filter_var($options['collapse-duplicates'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

    $exclude = self::URL_REWRITE_DEFAULT_EXCLUDED_ENTITY_TYPES;
    $extra_ex = trim((string) ($options['exclude-types'] ?? ''));
    if ($extra_ex !== '') {
      foreach (array_filter(array_map('trim', explode(',', $extra_ex)), static fn($v) => $v !== '') as $ex) {
        $exclude[] = $ex;
      }
    }
    $exclude = array_values(array_unique($exclude));

    $types_raw = trim((string) ($options['types'] ?? '*'));
    if ($types_raw === '' || $types_raw === '*') {
      $entity_type_ids = $this->listFieldableEntityTypeIdsForUrlRewrite($exclude);
    }
    else {
      $entity_type_ids = array_values(array_filter(array_map('trim', explode(',', $types_raw)), static fn($v) => $v !== ''));
    }

    $report_path = trim((string) ($options['report'] ?? ''));
    $report_fh = NULL;
    if ($report_path !== '') {
      $report_fh = fopen($report_path, 'wb');
      if ($report_fh === FALSE) {
        throw new \RuntimeException(sprintf('Cannot open report: %s', $report_path));
      }
      fwrite($report_fh, "\xEF\xBB\xBF");
      fputcsv($report_fh, ['entity_type', 'entity_id', 'bundle', 'entity_label', 'langcode', 'field_name', 'replacements']);
    }

    $changed_entities = 0;
    $changed_fields = 0;
    $total_replacements = 0;

    foreach ($entity_type_ids as $entity_type_id) {
      if (in_array($entity_type_id, $exclude, TRUE)) {
        continue;
      }
      if (!$this->entityTypeManager->hasDefinition($entity_type_id)) {
        $this->logger()->warning(sprintf('Unknown entity type, skipping: %s', $entity_type_id));
        continue;
      }
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      if (!is_a($entity_type->getClass(), ContentEntityInterface::class, TRUE)) {
        continue;
      }
      $base_table = $entity_type->getBaseTable();
      if (!is_string($base_table) || $base_table === '') {
        continue;
      }
      $bundle_key = $entity_type->getKey('bundle');
      $id_key = $entity_type->getKey('id');
      if (!$id_key || $bundle_key === NULL) {
        continue;
      }
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      if (!$storage instanceof SqlContentEntityStorage) {
        continue;
      }
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      foreach (array_keys($bundles) as $bundle) {
        if ($bundle === '') {
          continue;
        }
        $field_map = $this->urlRewriteProcessableFields($entity_type_id, (string) $bundle);
        if ($field_map === []) {
          continue;
        }
        try {
          $this->processUrlRewriteEntityBatch(
            $entity_type_id,
            $storage,
            $id_key,
            $bundle_key,
            (string) $bundle,
            $field_map,
            $pairs,
            $collapse,
            $dry,
            $changed_entities,
            $changed_fields,
            $total_replacements,
            $report_fh,
          );
        }
        catch (\Throwable $e) {
          $this->logger()->warning(sprintf(
            'URL rewrite skipped for %s bundle %s: %s',
            $entity_type_id,
            (string) $bundle,
            $e->getMessage(),
          ));
        }
      }
    }

    if ($report_fh !== NULL) {
      fclose($report_fh);
      $this->logger()->notice(sprintf('Wrote report: %s', $report_path));
    }

    $this->logger()->notice(sprintf(
      $dry
        ? 'Dry-run: would save %d entity translation(s), touch %d field(s), apply %d replacement(s). Loaded %d URL pair(s).'
        : 'Updated: saved %d entity translation(s), touched %d field(s), applied %d replacement(s). Loaded %d URL pair(s).',
      $changed_entities,
      $changed_fields,
      $total_replacements,
      count($pairs),
    ));
  }

  /**
   * @return list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}>
   */
  private function loadFullUrlRewritePairsFromCsv(string $file): array {
    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV: %s', $file));
    }
    $header = fgetcsv($fh);
    if (!is_array($header) || $header === []) {
      fclose($fh);
      throw new \RuntimeException('CSV is empty.');
    }
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]) ?? (string) $header[0];

    $pairs = [];
    $seen = [];
    while (($row = fgetcsv($fh)) !== FALSE) {
      if (!is_array($row) || count($row) < 2) {
        continue;
      }
      $old = trim((string) ($row[0] ?? ''));
      $new = trim((string) ($row[1] ?? ''));
      if ($old === '' || $new === '') {
        continue;
      }
      if (!preg_match('#^https?://#i', $old) || !preg_match('#^https?://#i', $new)) {
        continue;
      }
      if ($old === $new) {
        continue;
      }
      if (isset($seen[$old])) {
        $this->logger()->warning(sprintf('Duplicate old URL in CSV (using first row): %s', $old));
        continue;
      }
      $seen[$old] = TRUE;

      $old_path_raw = parse_url($old, PHP_URL_PATH);
      $new_path_raw = parse_url($new, PHP_URL_PATH);
      $old_path = $this->normalizeInternalPath((string) ($old_path_raw !== NULL && $old_path_raw !== '' ? $old_path_raw : '/'));
      $new_path = $this->normalizeInternalPath((string) ($new_path_raw !== NULL && $new_path_raw !== '' ? $new_path_raw : '/'));

      $pairs[] = [
        'old_full' => $old,
        'new_full' => $new,
        'old_path' => $old_path,
        'new_path' => $new_path,
        'old_variants' => $this->expandMotadedUrlVariantsForRewrite($old),
      ];
    }
    fclose($fh);

    usort($pairs, static function (array $a, array $b): int {
      return strlen($b['old_full']) <=> strlen($a['old_full']);
    });

    return $pairs;
  }

  /**
   * @return list<string>
   */
  private function expandMotadedUrlVariantsForRewrite(string $url): array {
    $parts = parse_url($url);
    if ($parts === FALSE || empty($parts['host'])) {
      return [$url];
    }
    if (!preg_match('/(^|\\.)motaded\\.com\\.sa$/i', (string) $parts['host'])) {
      return [$url];
    }
    $path = (string) ($parts['path'] ?? '');
    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
    $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';
    $tail = $path . $query . $fragment;
    $out = [];
    foreach (['https', 'http'] as $scheme) {
      foreach (['motaded.com.sa', 'www.motaded.com.sa'] as $host) {
        $out[] = $scheme . '://' . $host . $tail;
      }
    }
    return array_values(array_unique($out));
  }

  /**
   * @param list<string> $exclude entity type ids
   *
   * @return list<string>
   */
  private function listFieldableEntityTypeIdsForUrlRewrite(array $exclude): array {
    $exclude_set = array_fill_keys($exclude, TRUE);
    $out = [];
    foreach ($this->entityTypeManager->getDefinitions() as $id => $def) {
      if (isset($exclude_set[$id])) {
        continue;
      }
      $class = $def->getClass();
      if (!is_a($class, FieldableEntityInterface::class, TRUE) || !is_a($class, ContentEntityInterface::class, TRUE)) {
        continue;
      }
      $base = $def->getBaseTable();
      if (!is_string($base) || $base === '') {
        continue;
      }
      $storage = $this->entityTypeManager->getStorage((string) $id);
      if (!$storage instanceof SqlContentEntityStorage) {
        continue;
      }
      $out[] = (string) $id;
    }
    sort($out);
    return $out;
  }

  /**
   * @return array<string, string> field name => field type id
   */
  private function urlRewriteProcessableFields(string $entity_type_id, string $bundle): array {
    $defs = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $out = [];
    foreach ($defs as $name => $def) {
      if (in_array($def->getType(), self::DUPLICATE_PREFIX_FIELD_TYPES, TRUE)) {
        $out[$name] = $def->getType();
      }
    }
    return $out;
  }

  /**
   * @param list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}> $pairs
   * @param resource|null $report_fh
   */
  private function processUrlRewriteEntityBatch(
    string $entity_type_id,
    $storage,
    string $id_key,
    string $bundle_property,
    string $bundle_id,
    array $field_map,
    array $pairs,
    bool $collapse,
    bool $dry,
    int &$changed_entities,
    int &$changed_fields,
    int &$total_replacements,
    $report_fh,
  ): void {
    $last_id = 0;
    $batch = 80;
    while (TRUE) {
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition($bundle_property, $bundle_id)
        ->condition($id_key, $last_id, '>')
        ->sort($id_key, 'ASC')
        ->range(0, $batch);
      $ids = $query->execute();
      if ($ids === NULL || $ids === []) {
        break;
      }
      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $entity) {
        if (!$entity instanceof FieldableEntityInterface) {
          continue;
        }
        if ($entity->bundle() !== $bundle_id) {
          continue;
        }
        $to_save_langs = [];
        if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
          foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
            $t = $entity->getTranslation($langcode);
            if (!$t instanceof FieldableEntityInterface) {
              continue;
            }
            $n = $this->applyUrlRewriteFixesToFieldable(
              $t,
              $field_map,
              $pairs,
              $collapse,
              $entity_type_id,
              (int) $entity->id(),
              (string) $langcode,
              $report_fh,
              $total_replacements,
            );
            if ($n > 0) {
              $to_save_langs[] = $langcode;
              $changed_fields += $n;
            }
          }
          foreach ($to_save_langs as $langcode) {
            $tr = $entity->getTranslation($langcode);
            if (!$dry) {
              $tr->save();
            }
            $changed_entities++;
          }
        }
        else {
          $n = $this->applyUrlRewriteFixesToFieldable(
            $entity,
            $field_map,
            $pairs,
            $collapse,
            $entity_type_id,
            (int) $entity->id(),
            $entity->language()->getId(),
            $report_fh,
            $total_replacements,
          );
          if ($n > 0) {
            $changed_fields += $n;
            if (!$dry) {
              $entity->save();
            }
            $changed_entities++;
          }
        }
      }
    }
  }

  /**
   * @param array<string, string> $field_map
   * @param list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}> $pairs
   * @param resource|null $report_fh
   */
  private function applyUrlRewriteFixesToFieldable(
    FieldableEntityInterface $entity,
    array $field_map,
    array $pairs,
    bool $collapse,
    string $entity_type_id,
    int $entity_id,
    string $langcode,
    $report_fh,
    int &$total_replacements,
  ): int {
    [$bundle, $entity_label] = $this->entityBundleAndLabelForReport($entity);
    $fields_touched = 0;
    foreach ($field_map as $field_name => $field_type) {
      if (!$entity->hasField($field_name)) {
        continue;
      }
      $list = $entity->get($field_name);
      if ($list->isEmpty()) {
        continue;
      }
      $field_repls = 0;
      foreach ($list as $item) {
        if (!$item instanceof FieldItemInterface) {
          continue;
        }
        $field_repls += $this->applyUrlRewriteToFieldItem($item, $field_type, $pairs, $collapse);
      }
      if ($field_repls > 0) {
        $fields_touched++;
        $total_replacements += $field_repls;
        if ($report_fh !== NULL && $report_fh !== FALSE) {
          fputcsv($report_fh, [
            $entity_type_id,
            (string) $entity_id,
            $bundle,
            $entity_label,
            $langcode,
            $field_name,
            (string) $field_repls,
          ]);
        }
      }
    }
    return $fields_touched;
  }

  /**
   * @return array{0: string, 1: string} bundle id, human label (e.g. node title for this translation)
   */
  private function entityBundleAndLabelForReport(FieldableEntityInterface $entity): array {
    $bundle = '';
    $label = '';
    try {
      $bundle = $entity->bundle();
    }
    catch (\Throwable) {
    }
    try {
      $label = (string) $entity->label();
    }
    catch (\Throwable) {
    }
    return [$bundle, $label];
  }

  /**
   * @param list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}> $pairs
   */
  private function applyUrlRewriteToFieldItem(
    FieldItemInterface $item,
    string $field_type,
    array $pairs,
    bool $collapse,
  ): int {
    $repl = 0;
    if ($field_type === 'link') {
      $uri = (string) $item->get('uri')->getString();
      if ($uri === '' || !$this->textMightContainAnyUrlRewritePair($uri, $pairs)) {
        return 0;
      }
      $uri = $this->applyFullUrlRewritePairsToText($uri, $pairs, $repl, TRUE);
      $item->set('uri', $uri);
      if ($collapse) {
        $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, 'link');
      }
      return $repl;
    }
    if ($field_type === 'text_with_summary') {
      foreach (['value', 'summary'] as $prop) {
        $chunk = (string) $item->get($prop)->getString();
        if ($chunk === '' || !$this->textMightContainAnyUrlRewritePair($chunk, $pairs)) {
          continue;
        }
        $chunk = $this->applyFullUrlRewritePairsToText($chunk, $pairs, $repl, FALSE);
        $item->set($prop, $chunk);
      }
      if ($collapse) {
        $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, 'text_with_summary');
      }
      return $repl;
    }
    $chunk = (string) $item->get('value')->getString();
    if ($chunk === '' || !$this->textMightContainAnyUrlRewritePair($chunk, $pairs)) {
      return 0;
    }
    $chunk = $this->applyFullUrlRewritePairsToText($chunk, $pairs, $repl, FALSE);
    $item->set('value', $chunk);
    if ($collapse) {
      $repl += $this->collapseDuplicatePrefixesOnFieldItem($item, $field_type);
    }
    return $repl;
  }

  /**
   * @param list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}> $pairs
   */
  private function textMightContainAnyUrlRewritePair(string $text, array $pairs): bool {
    foreach ($pairs as $p) {
      if (str_contains($text, $p['old_full'])) {
        return TRUE;
      }
      foreach ($p['old_variants'] as $v) {
        if (str_contains($text, $v)) {
          return TRUE;
        }
      }
      if (str_contains($text, $p['old_path'])) {
        return TRUE;
      }
      if (str_contains($text, 'internal:' . $p['old_path'])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * @param list<array{old_full: string, new_full: string, old_path: string, new_path: string, old_variants: list<string>}> $pairs
   */
  private function applyFullUrlRewritePairsToText(string $text, array $pairs, int &$repl, bool $is_link_uri): string {
    foreach ($pairs as $p) {
      foreach ($p['old_variants'] as $from) {
        $c = 0;
        $text = str_replace($from, $p['new_full'], $text, $c);
        $repl += (int) $c;
      }
      $c2 = 0;
      $text = str_replace('internal:' . $p['old_path'], 'internal:' . $p['new_path'], $text, $c2);
      $repl += (int) $c2;
      if ($is_link_uri) {
        $text = $this->replacePathOccurrencesForLinkUri($text, $p['old_path'], $p['new_path'], $repl);
      }
      else {
        $text = $this->replacePathOccurrencesWithMotadedHost($text, $p['old_path'], $p['new_path'], $repl);
      }
    }
    return $text;
  }

  /**
   * Collapses duplicate URL path segments in entity text/link fields (content fix, not redirects).
   *
   * Fixes repeated /services/services/... and /blog/blog/... (any depth), including after
   * language prefixes (e.g. /ar/services/services/...) and inside https://motaded.com.sa/... .
   */
  #[CLI\Command(name: 'motaded:fix-content-duplicate-path-prefixes')]
  #[CLI\Option(name: 'dry-run', description: 'List counts without saving entities.')]
  #[CLI\Option(name: 'types', description: 'Comma-separated entity type IDs (default: node,paragraph,block_content,taxonomy_term).')]
  #[CLI\Option(name: 'report', description: 'Optional CSV path listing each changed field.')]
  #[CLI\Usage(name: 'drush motaded:fix-content-duplicate-path-prefixes --dry-run', description: 'Preview duplicate-prefix fixes.')]
  public function fixContentDuplicatePathPrefixes(array $options = ['dry-run' => TRUE, 'types' => 'node,paragraph,block_content,taxonomy_term', 'report' => NULL]): void {
    $dry = filter_var($options['dry-run'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $types_raw = (string) ($options['types'] ?? 'node,paragraph,block_content,taxonomy_term');
    $entity_type_ids = array_values(array_filter(array_map('trim', explode(',', $types_raw)), static fn($v) => $v !== ''));
    $report_path = trim((string) ($options['report'] ?? ''));
    $report_fh = NULL;
    if ($report_path !== '') {
      $report_fh = fopen($report_path, 'wb');
      if ($report_fh === FALSE) {
        throw new \RuntimeException(sprintf('Cannot open report: %s', $report_path));
      }
      fwrite($report_fh, "\xEF\xBB\xBF");
      fputcsv($report_fh, ['entity_type', 'entity_id', 'langcode', 'field_name', 'replacements']);
    }

    $changed_entities = 0;
    $changed_fields = 0;
    $total_replacements = 0;

    foreach ($entity_type_ids as $entity_type_id) {
      if (!$this->entityTypeManager->hasDefinition($entity_type_id)) {
        $this->logger()->warning(sprintf('Unknown entity type, skipping: %s', $entity_type_id));
        continue;
      }
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle_key = $entity_type->getKey('bundle');
      $id_key = $entity_type->getKey('id');
      if (!$id_key) {
        continue;
      }
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
      if ($bundle_key === NULL) {
        $this->logger()->warning(sprintf('Entity type has no bundle key, skipping: %s', $entity_type_id));
        continue;
      }
      foreach (array_keys($bundles) as $bundle) {
        $field_map = $this->duplicatePrefixProcessableFields($entity_type_id, (string) $bundle);
        if ($field_map === []) {
          continue;
        }
        $this->processDuplicatePrefixEntityBatch(
          $entity_type_id,
          $storage,
          $id_key,
          $bundle_key,
          (string) $bundle,
          $field_map,
          $dry,
          $changed_entities,
          $changed_fields,
          $total_replacements,
          $report_fh,
        );
      }
    }

    if ($report_fh !== NULL) {
      fclose($report_fh);
      $this->logger()->notice(sprintf('Wrote report: %s', $report_path));
    }

    $this->logger()->notice(sprintf(
      $dry
        ? 'Dry-run: would save %d entity translation(s), touch %d field(s), apply %d substring replacement(s) (no DB writes).'
        : 'Updated: saved %d entity translation(s), touched %d field(s), applied %d substring replacement(s).',
      $changed_entities,
      $changed_fields,
      $total_replacements,
    ));
  }

  /**
   * @param array<string, string> $field_map field name => field type plugin id
   * @param resource|null $report_fh
   */
  private function processDuplicatePrefixEntityBatch(
    string $entity_type_id,
    $storage,
    string $id_key,
    string $bundle_property,
    string $bundle_id,
    array $field_map,
    bool $dry,
    int &$changed_entities,
    int &$changed_fields,
    int &$total_replacements,
    $report_fh,
  ): void {
    $last_id = 0;
    $batch = 80;
    while (TRUE) {
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition($bundle_property, $bundle_id)
        ->condition($id_key, $last_id, '>')
        ->sort($id_key, 'ASC')
        ->range(0, $batch);
      $ids = $query->execute();
      if ($ids === NULL || $ids === []) {
        break;
      }
      $last_id = (int) max($ids);
      foreach ($storage->loadMultiple($ids) as $entity) {
        if (!$entity instanceof FieldableEntityInterface) {
          continue;
        }
        if ($entity->bundle() !== $bundle_id) {
          continue;
        }
        $to_save_langs = [];
        if ($entity instanceof TranslatableInterface && $entity->isTranslatable()) {
          foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
            $t = $entity->getTranslation($langcode);
            if (!$t instanceof FieldableEntityInterface) {
              continue;
            }
            $n = $this->applyDuplicatePrefixFixesToFieldable(
              $t,
              $field_map,
              $entity_type_id,
              (int) $entity->id(),
              (string) $langcode,
              $report_fh,
              $total_replacements,
            );
            if ($n > 0) {
              $to_save_langs[] = $langcode;
              $changed_fields += $n;
            }
          }
          foreach ($to_save_langs as $langcode) {
            $tr = $entity->getTranslation($langcode);
            if (!$dry) {
              $tr->save();
            }
            $changed_entities++;
          }
        }
        else {
          $n = $this->applyDuplicatePrefixFixesToFieldable(
            $entity,
            $field_map,
            $entity_type_id,
            (int) $entity->id(),
            $entity->language()->getId(),
            $report_fh,
            $total_replacements,
          );
          if ($n > 0) {
            $changed_fields += $n;
            if (!$dry) {
              $entity->save();
            }
            $changed_entities++;
          }
        }
      }
    }
  }

  /**
   * @return array<string, string> field name => field type id
   */
  private function duplicatePrefixProcessableFields(string $entity_type_id, string $bundle): array {
    $defs = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    $out = [];
    foreach ($defs as $name => $def) {
      if (in_array($def->getType(), self::DUPLICATE_PREFIX_FIELD_TYPES, TRUE)) {
        $out[$name] = $def->getType();
      }
    }
    return $out;
  }

  /**
   * @param array<string, string> $field_map
   * @param resource|null $report_fh
   *
   * @return int number of fields that had at least one replacement
   */
  private function applyDuplicatePrefixFixesToFieldable(
    FieldableEntityInterface $entity,
    array $field_map,
    string $entity_type_id,
    int $entity_id,
    string $langcode,
    $report_fh,
    int &$total_replacements,
  ): int {
    $fields_touched = 0;
    foreach ($field_map as $field_name => $field_type) {
      if (!$entity->hasField($field_name)) {
        continue;
      }
      $list = $entity->get($field_name);
      if ($list->isEmpty()) {
        continue;
      }
      $field_repls = 0;
      foreach ($list as $item) {
        if (!$item instanceof FieldItemInterface) {
          continue;
        }
        $field_repls += $this->collapseDuplicatePrefixesOnFieldItem($item, $field_type);
      }
      if ($field_repls > 0) {
        $fields_touched++;
        $total_replacements += $field_repls;
        if ($report_fh !== NULL && $report_fh !== FALSE) {
          fputcsv($report_fh, [$entity_type_id, (string) $entity_id, $langcode, $field_name, (string) $field_repls]);
        }
      }
    }
    return $fields_touched;
  }

  /**
   * Collapses /services/services/... and /blog/blog/... repeatedly (handles triple+ segments).
   *
   * @return array{0: string, 1: int} New text and number of preg replacements applied.
   */
  private function collapseDuplicatePathSegmentsInText(string $text): array {
    if ($text === '') {
      return [$text, 0];
    }
    $total = 0;
    do {
      $before = $text;
      // Use ~ delimiter so # can appear in the character class (URL fragments).
      $out = preg_replace('~/services/services(?=/|["\'\s?#]|$)~u', '/services', $text, -1, $c1);
      $text = is_string($out) ? $out : $before;
      $total += (int) $c1;
      $out = preg_replace('~/blog/blog(?=/|["\'\s?#]|$)~u', '/blog', $text, -1, $c2);
      $text = is_string($out) ? $out : $text;
      $total += (int) $c2;
    } while ($text !== $before);
    return [$text, $total];
  }

  /**
   * Mutates the field item in place; returns replacement count for this item.
   */
  private function collapseDuplicatePrefixesOnFieldItem(FieldItemInterface $item, string $field_type): int {
    $repl = 0;
    if ($field_type === 'link') {
      $uri = (string) $item->get('uri')->getString();
      if ($uri === '') {
        return 0;
      }
      [$new, $c] = $this->collapseDuplicatePathSegmentsInText($uri);
      if ($c > 0) {
        $item->set('uri', $new);
      }
      return $c;
    }
    if ($field_type === 'text_with_summary') {
      foreach (['value', 'summary'] as $prop) {
        $chunk = (string) $item->get($prop)->getString();
        if ($chunk === '') {
          continue;
        }
        [$new, $c] = $this->collapseDuplicatePathSegmentsInText($chunk);
        if ($c > 0) {
          $item->set($prop, $new);
        }
        $repl += $c;
      }
      return $repl;
    }
    $chunk = (string) $item->get('value')->getString();
    if ($chunk === '') {
      return 0;
    }
    [$new, $c] = $this->collapseDuplicatePathSegmentsInText($chunk);
    if ($c > 0) {
      $item->set('value', $new);
    }
    return $c;
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
   * Resolves a host-side path (e.g. /Users/...) to a file inside the project when using DDEV.
   *
   * Tries: exact path, then project_root/basename, then project_root/relative_path.
   */
  private function resolveReadablePathInProject(string $path, string $option_name): string {
    $path = trim($path);
    if ($path === '') {
      throw new \InvalidArgumentException(sprintf('Missing --%s.', $option_name));
    }
    $normalized = str_replace('\\', '/', $path);
    if (is_file($normalized) && is_readable($normalized)) {
      return $normalized;
    }
    $root = $this->getProjectRootDirectory();
    $basename = basename($normalized);
    if ($basename !== '' && $basename !== '.' && $basename !== '..') {
      $try = $root . '/' . $basename;
      if (is_file($try) && is_readable($try)) {
        return $try;
      }
    }
    if (!str_starts_with($normalized, '/')) {
      $try_rel = $root . '/' . ltrim($normalized, '/');
      if (is_file($try_rel) && is_readable($try_rel)) {
        return $try_rel;
      }
    }
    $hint = '';
    if (str_starts_with($normalized, '/Users/') || str_starts_with($normalized, '/home/')) {
      $hint = sprintf(
        ' With DDEV, Drush runs in the container: use /var/www/html/%s or place the CSV in the project root and pass only the filename.',
        $basename,
      );
    }
    throw new \InvalidArgumentException(sprintf('Unreadable --%s: %s.%s', $option_name, $path, $hint));
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

  /**
   * Maps report table/column to an entity field + property.
   *
   * @return array{0:string,1:string}|null
   */
  private function fieldTargetFromTableColumn(string $entity_type, string $table, string $column): ?array {
    $key = $entity_type . '|' . $table . '|' . $column;
    return match ($key) {
      'node|node__body|body_value' => ['body', 'value'],
      'node|node__field_keywords|field_keywords_value' => ['field_keywords', 'value'],
      'block_content|block_content__body|body_value' => ['body', 'value'],
      'paragraph|paragraph__field_link|field_link_uri' => ['field_link', 'uri'],
      default => NULL,
    };
  }

  /**
   * Replaces occurrences of an internal path within HTML/text safely-ish.
   *
   * Only replaces when the old path is followed by a URL boundary.
   */
  /**
   * Host variants used when matching absolute motaded.com.sa URLs in content.
   *
   * @return list<string>
   */
  private function motadedHostPrefixes(): array {
    return [
      'https://motaded.com.sa',
      'http://motaded.com.sa',
      'https://www.motaded.com.sa',
      'http://www.motaded.com.sa',
    ];
  }

  /**
   * Regex fragment for matching a legacy internal path without false positives.
   *
   * Paths like /blog/foo or /services/foo already end with /foo; the short legacy
   * form in the map is /foo. A naive match would count /foo inside /blog/foo.
   * Require that the path is not immediately preceded by /blog/, /services/, or
   * /news/ (including after a language prefix, e.g. /ar/blog/foo).
   */
  private function legacyRelativeOldPathRegexFragment(string $old_path): string {
    // $old_path in the map includes a leading "/" (e.g. "/what-neom").
    // In the canonical path "/blog/what-neom", the substring "/what-neom"
    // starts right after "blog" (i.e. preceded by ".../blog", not ".../blog/").
    return '(?<!/blog)(?<!/services)(?<!/news)' . preg_quote($old_path, '~');
  }

  /**
   * Quick filter before running regex counts over a large URL map.
   */
  private function textMightContainLegacyPath(string $text, string $old_path): bool {
    $old_path = $this->normalizeInternalPath($old_path);
    if ($old_path === '/' || $old_path === '') {
      return FALSE;
    }
    if (strpos($text, 'motaded.com.sa') !== FALSE) {
      return TRUE;
    }
    if (strpos($text, 'internal:' . $old_path) !== FALSE) {
      return TRUE;
    }
    if (strpos($text, $old_path) !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Counts legacy URL forms: absolute on motaded.com.sa, internal: URIs, then relative path.
   *
   * Relative matches are counted on text with absolute and internal occurrences removed
   * to reduce double-counting the same href.
   *
   * @return array{total: int, full: int, internal: int, relative: int}
   */
  private function countLegacyPathOccurrencesInText(string $text, string $old_path): array {
    $old_path = $this->normalizeInternalPath($old_path);
    if ($old_path === '/' || $old_path === '') {
      return ['total' => 0, 'full' => 0, 'internal' => 0, 'relative' => 0];
    }

    $full = 0;
    foreach ($this->motadedHostPrefixes() as $host) {
      $abs = $host . $old_path;
      preg_match_all('~' . preg_quote($abs, '~') . '(?=($|[\"\'\s?#/]))~iu', $text, $m);
      $full += isset($m[0]) ? count($m[0]) : 0;
    }

    $internal_uri = 'internal:' . $old_path;
    preg_match_all('~' . preg_quote($internal_uri, '~') . '(?=($|[\"\'\s?#/]))~iu', $text, $m2);
    $internal = isset($m2[0]) ? count($m2[0]) : 0;

    $t = $text;
    foreach ($this->motadedHostPrefixes() as $host) {
      $abs = $host . $old_path;
      $out = preg_replace('~' . preg_quote($abs, '~') . '(?=($|[\"\'\s?#/]))~iu', '', $t);
      $t = is_string($out) ? $out : $t;
    }
    $out = preg_replace('~' . preg_quote($internal_uri, '~') . '(?=($|[\"\'\s?#/]))~iu', '', $t);
    $t = is_string($out) ? $out : $t;

    preg_match_all(
      '~' . $this->legacyRelativeOldPathRegexFragment($old_path) . '(?=($|[\"\'\s?#/]))~u',
      $t,
      $m3,
    );
    $relative = isset($m3[0]) ? count($m3[0]) : 0;

    return [
      'total' => $full + $internal + $relative,
      'full' => $full,
      'internal' => $internal,
      'relative' => $relative,
    ];
  }

  private function replacePathOccurrences(string $text, string $old, string $new, int &$count): string {
    $old = $this->normalizeInternalPath($old);
    $new = $this->normalizeInternalPath($new);
    $pattern = '~' . $this->legacyRelativeOldPathRegexFragment($old) . '(?=($|[\"\'\s?#/]))~u';
    $result = preg_replace($pattern, $new, $text, -1, $count_local);
    $count += (int) $count_local;
    return is_string($result) ? $result : $text;
  }

  /**
   * Replaces an internal path both as relative and as full motaded.com.sa URL.
   *
   * Example: replaces "/ZATCA" and "https://motaded.com.sa/ZATCA".
   *
   * Keeps the matched scheme/host intact and only swaps the path segment.
   */
  private function replacePathOccurrencesWithMotadedHost(string $text, string $old, string $new, int &$count): string {
    $old_path = $this->normalizeInternalPath($old);
    $new_path = $this->normalizeInternalPath($new);

    // Prevent double-prefix effects like "/blog/blog/..." when:
    // - old="/foo"
    // - new="/blog/foo"
    // and the content already contains "/blog/foo" (which contains "/foo").
    //
    // We "protect" already-updated occurrences of $new_path so they won't be
    // touched by the subsequent old->new replacement.
    $token = '__MOTADED_REPL_' . substr(md5($old_path . '|' . $new_path), 0, 10) . '__';
    if ($new_path !== '' && $new_path !== '/' && strpos($text, $new_path) !== FALSE) {
      $text = str_replace($new_path, $token, $text);
    }
    // Also protect absolute URLs on our host with the new path.
    foreach (['https://motaded.com.sa', 'http://motaded.com.sa', 'https://www.motaded.com.sa', 'http://www.motaded.com.sa'] as $host) {
      $abs_new = $host . $new_path;
      if ($new_path !== '' && $new_path !== '/' && strpos($text, $abs_new) !== FALSE) {
        $text = str_replace($abs_new, $host . $token, $text);
      }
    }

    // Relative path occurrences.
    $text = $this->replacePathOccurrences($text, $old_path, $new_path, $count);

    // Absolute URLs on our own host (keep host, replace path).
    $host_pattern = '~(https?://(?:www\.)?motaded\.com\.sa)' . preg_quote($old_path, '~') . '(?=($|[\"\'\s?#/]))~iu';
    $result = preg_replace($host_pattern, '$1' . $new_path, $text, -1, $count_local);
    $count += (int) $count_local;
    $text = is_string($result) ? $result : $text;

    // Unprotect tokens back to the new path.
    if ($new_path !== '' && $new_path !== '/' && strpos($text, $token) !== FALSE) {
      $text = str_replace($token, $new_path, $text);
    }
    foreach (['https://motaded.com.sa', 'http://motaded.com.sa', 'https://www.motaded.com.sa', 'http://www.motaded.com.sa'] as $host) {
      $abs_token = $host . $token;
      if ($new_path !== '' && $new_path !== '/' && strpos($text, $abs_token) !== FALSE) {
        $text = str_replace($abs_token, $host . $new_path, $text);
      }
    }
    return $text;
  }

  /**
   * Like replacePathOccurrences(), but also supports link field URIs.
   *
   * For link fields, the stored value is often "internal:/path". We rewrite both
   * the plain path and the internal URI form.
   */
  private function replacePathOccurrencesForLinkUri(string $text, string $old, string $new, int &$count): string {
    $old_path = $this->normalizeInternalPath($old);
    $new_path = $this->normalizeInternalPath($new);

    $text = $this->replacePathOccurrencesWithMotadedHost($text, $old_path, $new_path, $count);

    $old_uri = 'internal:' . $old_path;
    $new_uri = 'internal:' . $new_path;
    $pattern = '~' . preg_quote($old_uri, '~') . '(?=($|[\"\'\s?#/]))~u';
    $result = preg_replace($pattern, $new_uri, $text, -1, $count_local);
    $count += (int) $count_local;
    return is_string($result) ? $result : $text;
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
