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

  private function normalizeInternalPath(string $path): string {
    return '/' . trim($path, '/');
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
