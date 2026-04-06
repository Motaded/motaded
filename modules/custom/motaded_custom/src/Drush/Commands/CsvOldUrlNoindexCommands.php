<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasRepositoryInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * CSV old-URL helpers: sitemap/metatag noindex, unpublish nodes.
 */
final class CsvOldUrlNoindexCommands extends DrushCommands {

  /**
   * Language-prefixed paths that must not receive noindex (home per locale).
   */
  private const LANGUAGE_ROOT_DENYLIST = [
    '/zh-hans',
    '/ar',
    '/en',
    '/es',
    '/fr',
  ];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly AliasRepositoryInterface $pathAliasRepository,
    protected readonly ModuleHandlerInterface $moduleHandler,
    protected readonly EntityFieldManagerInterface $entityFieldManager,
    protected readonly LanguageManagerInterface $languageManager,
  ) {
    parent::__construct();
  }

  /**
   * Exclude nodes from Simple XML Sitemap and/or set metatag robots (CSV column 1 = old URL).
   */
  #[CLI\Command(name: 'motaded:csv-noindex-by-old-url')]
  #[CLI\Option(name: 'file', description: 'CSV path: column A = old absolute URL (same style as Alina Task - Sheet1.csv).')]
  #[CLI\Option(name: 'dry-run', description: 'List targets only; do not save (default: true).')]
  #[CLI\Option(name: 'sitemap', description: 'Set per-entity Simple XML Sitemap override: do not index (default: true).')]
  #[CLI\Option(name: 'metatag', description: 'Also set metatag robots on matching translations (default: false).')]
  #[CLI\Option(name: 'robots', description: 'Metatag robots value when --metatag (default: noindex, nofollow).')]
  #[CLI\Usage(name: 'drush motaded:csv-noindex-by-old-url --file=Alina\\ Task\\ -\\ Sheet1.csv --dry-run', description: 'Preview sitemap exclusions (default).')]
  #[CLI\Usage(name: 'drush motaded:csv-noindex-by-old-url --file=sheet.csv --no-dry-run -y', description: 'Apply sitemap exclusions like the node edit form.')]
  #[CLI\Usage(name: 'drush motaded:csv-noindex-by-old-url --file=sheet.csv --metatag --no-dry-run -y', description: 'Sitemap + metatag robots.')]
  public function csvNoindexByOldUrl(array $options = [
    'file' => NULL,
    'dry-run' => TRUE,
    'sitemap' => TRUE,
    'metatag' => FALSE,
    'robots' => 'noindex, nofollow',
  ]): void {
    $do_sitemap = filter_var($options['sitemap'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $do_metatag = filter_var($options['metatag'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    if (!$do_sitemap && !$do_metatag) {
      throw new \InvalidArgumentException('Enable at least one of --sitemap or --metatag.');
    }
    if ($do_sitemap && !$this->moduleHandler->moduleExists('simple_sitemap')) {
      throw new \RuntimeException('The simple_sitemap module must be enabled for --sitemap.');
    }
    if ($do_metatag && !$this->moduleHandler->moduleExists('metatag')) {
      throw new \RuntimeException('The metatag module must be enabled for --metatag.');
    }

    $file = $this->resolveReadablePathInProject((string) ($options['file'] ?? ''), 'file');
    $dry = filter_var($options['dry-run'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $robots = trim((string) ($options['robots'] ?? 'noindex, nofollow'));
    if ($do_metatag && $robots === '') {
      throw new \InvalidArgumentException('Empty --robots value.');
    }

    $paths = $this->loadOldUrlPathsFromCsv($file);
    if ($paths === []) {
      throw new \RuntimeException('No old URLs with http(s) found in CSV.');
    }

    $targets = [];
    foreach ($paths as $path) {
      if ($this->isLanguageRootPath($path)) {
        $this->logger()->notice(dt('Skip language home path: @p', ['@p' => $path]));
        continue;
      }
      $resolved = $this->resolveNodeFromPublicPath($path);
      if ($resolved === NULL) {
        $this->logger()->warning(dt('No path_alias → node for: @p', ['@p' => $path]));
        continue;
      }
      $key = $resolved['nid'] . ':' . $resolved['langcode'];
      $targets[$key] = $resolved;
    }

    if ($targets === []) {
      $this->logger()->warning(dt('No resolvable nodes; nothing to do.'));
      return;
    }

    $this->io()->writeln(dt('Resolved @n unique node translation row(s) from CSV.', ['@n' => count($targets)]));

    $storage = $this->entityTypeManager->getStorage('node');
    $saved_metatag = 0;
    $saved_sitemap = 0;

    if ($do_sitemap) {
      $nids = [];
      foreach ($targets as $row) {
        $nids[$row['nid']] = TRUE;
      }
      $nids = array_keys($nids);
      foreach ($nids as $nid) {
        $node = $storage->load($nid);
        if (!$node instanceof NodeInterface) {
          $this->logger()->warning(dt('Node @nid missing (sitemap).', ['@nid' => $nid]));
          continue;
        }
        $indexed = $this->nodeIsIndexedInDefaultSitemap((int) $nid);
        if ($indexed === FALSE) {
          $this->io()->writeln(dt('Sitemap: already excluded node @nid — @t', [
            '@nid' => $nid,
            '@t' => $node->label(),
          ]));
          continue;
        }
        if ($dry) {
          $this->io()->writeln(dt('Sitemap: would exclude node @nid — @t', [
            '@nid' => $nid,
            '@t' => $node->label(),
          ]));
          continue;
        }
        $this->getSimpleSitemapEntityManager()
          ->setSitemaps()
          ->setEntityInstanceSettings('node', (string) $nid, ['index' => 0]);
        $saved_sitemap++;
        $this->logger()->notice(dt('Sitemap: excluded node @nid — @t', ['@nid' => $nid, '@t' => $node->label()]));
      }
    }

    if ($do_metatag) {
      foreach ($targets as $row) {
        $nid = $row['nid'];
        $langcode = $row['langcode'];
        /** @var \Drupal\node\NodeInterface|null $node */
        $node = $storage->load($nid);
        if (!$node instanceof NodeInterface) {
          $this->logger()->warning(dt('Node @nid missing.', ['@nid' => $nid]));
          continue;
        }
        if ($langcode === LanguageInterface::LANGCODE_NOT_SPECIFIED) {
          $langcode = $node->language()->getId();
        }
        if (!$node->hasTranslation($langcode)) {
          $this->logger()->warning(dt('Node @nid has no @lang translation.', ['@nid' => $nid, '@lang' => $langcode]));
          continue;
        }
        $translation = $node->getTranslation($langcode);
        $field_name = $this->getMetatagFieldName($translation);
        if ($field_name === NULL) {
          $this->logger()->warning(dt('Node @nid (@bundle) has no metatag field.', ['@nid' => $nid, '@bundle' => $translation->bundle()]));
          continue;
        }

        $raw = $translation->get($field_name)->isEmpty() ? '{}' : (string) $translation->get($field_name)->value;
        $tags = metatag_data_decode($raw);
        $previous = $tags['robots'] ?? '';
        $tags['robots'] = $robots;

        $label = $translation->label();
        if ($previous === $robots) {
          $this->io()->writeln(dt('Metatag: already set @nid @lang — @t', ['@nid' => $nid, '@lang' => $langcode, '@t' => $label]));
          continue;
        }

        if ($dry) {
          $this->io()->writeln(dt('Metatag: would set robots=@r: @nid @lang — @t (was: @w)', [
            '@r' => $robots,
            '@nid' => $nid,
            '@lang' => $langcode,
            '@t' => $label,
            '@w' => $previous !== '' ? $previous : '(default)',
          ]));
          continue;
        }

        $node->getTranslation($langcode)->set($field_name, [['value' => metatag_data_encode($tags)]]);
        $node->setNewRevision(FALSE);
        $node->save();
        $saved_metatag++;
        $this->logger()->notice(dt('Metatag: updated @nid @lang — @t', ['@nid' => $nid, '@lang' => $langcode, '@t' => $label]));
      }
    }

    if ($dry) {
      $this->logger()->notice(dt('Dry run only. Re-run with --no-dry-run to apply.'));
      return;
    }

    $parts = [];
    if ($do_sitemap) {
      $parts[] = dt('@n sitemap override(s)', ['@n' => $saved_sitemap]);
    }
    if ($do_metatag) {
      $parts[] = dt('@n metatag save(s)', ['@n' => $saved_metatag]);
    }
    $this->logger()->success(dt('Done: !parts. Regenerate sitemaps: drush simple-sitemap:rebuild-queue && drush simple-sitemap:generate. Then drush cr.', [
      '!parts' => implode(', ', $parts),
    ]));
  }

  /**
   * Unpublish nodes matching old URLs in CSV column A (unique nid; same resolver as csv-noindex).
   */
  #[CLI\Command(name: 'motaded:csv-unpublish-by-old-url')]
  #[CLI\Option(name: 'file', description: 'CSV path: column A = old absolute URL (e.g. Alina Task - Sheet1.csv).')]
  #[CLI\Option(name: 'dry-run', description: 'List nodes only; do not save (default: true).')]
  #[CLI\Usage(name: 'drush motaded:csv-unpublish-by-old-url --file=Alina\\ Task\\ -\\ Sheet1.csv --dry-run', description: 'Preview which nodes would be unpublished.')]
  #[CLI\Usage(name: 'drush motaded:csv-unpublish-by-old-url --file=sheet.csv --no-dry-run -y', description: 'Unpublish resolved nodes.')]
  public function csvUnpublishByOldUrl(array $options = [
    'file' => NULL,
    'dry-run' => TRUE,
  ]): void {
    $file = $this->resolveReadablePathInProject((string) ($options['file'] ?? ''), 'file');
    $dry = filter_var($options['dry-run'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);

    $paths = $this->loadOldUrlPathsFromCsv($file);
    if ($paths === []) {
      throw new \RuntimeException('No old URLs with http(s) found in CSV.');
    }

    $nids = [];
    foreach ($paths as $path) {
      if ($this->isLanguageRootPath($path)) {
        $this->logger()->notice(dt('Skip language home path: @p', ['@p' => $path]));
        continue;
      }
      $resolved = $this->resolveNodeFromPublicPath($path);
      if ($resolved === NULL) {
        $this->logger()->warning(dt('No path_alias → node for: @p', ['@p' => $path]));
        continue;
      }
      $nids[$resolved['nid']] = TRUE;
    }

    $nid_list = array_keys($nids);
    sort($nid_list);
    if ($nid_list === []) {
      $this->logger()->warning(dt('No resolvable nodes; nothing to do.'));
      return;
    }

    $this->io()->writeln(dt('Unique nodes to process: @n', ['@n' => count($nid_list)]));

    $storage = $this->entityTypeManager->getStorage('node');
    $saved = 0;
    foreach ($nid_list as $nid) {
      $node = $storage->load($nid);
      if (!$node instanceof NodeInterface) {
        $this->logger()->warning(dt('Node @nid missing.', ['@nid' => $nid]));
        continue;
      }
      if (!$node->isPublished()) {
        $this->io()->writeln(dt('Already unpublished: @nid — @t', ['@nid' => $nid, '@t' => $node->label()]));
        continue;
      }
      if ($dry) {
        $this->io()->writeln(dt('Would unpublish: @nid — @t', ['@nid' => $nid, '@t' => $node->label()]));
        continue;
      }
      $node->setUnpublished();
      $node->setNewRevision(FALSE);
      $node->save();
      $saved++;
      $this->logger()->notice(dt('Unpublished @nid — @t', ['@nid' => $nid, '@t' => $node->label()]));
    }

    if ($dry) {
      $this->logger()->notice(dt('Dry run only. Re-run with --no-dry-run to unpublish.'));
      return;
    }

    $this->logger()->success(dt('Unpublished @n node(s). Run drush cr if needed.', ['@n' => $saved]));
  }

  /**
   * TRUE if indexed in default sitemap, FALSE if excluded, NULL if undetermined.
   */
  private function nodeIsIndexedInDefaultSitemap(int $nid): ?bool {
    $em = $this->getSimpleSitemapEntityManager();
    $em->setSitemaps();
    $instance = $em->getEntityInstanceSettings('node', (string) $nid);
    if ($instance === FALSE) {
      return NULL;
    }
    $settings = reset($instance);
    if (!is_array($settings)) {
      return NULL;
    }
    return !empty($settings['index']);
  }

  private function getSimpleSitemapEntityManager(): object {
    if (!\Drupal::hasService('simple_sitemap.entity_manager')) {
      throw new \RuntimeException('Service simple_sitemap.entity_manager is not available.');
    }
    return \Drupal::service('simple_sitemap.entity_manager');
  }

  /**
   * @return list<string>
   *   Normalized paths (leading slash, no trailing slash except /).
   */
  private function loadOldUrlPathsFromCsv(string $file): array {
    $fh = fopen($file, 'rb');
    if ($fh === FALSE) {
      throw new \RuntimeException(sprintf('Cannot open CSV: %s', $file));
    }
    $paths = [];
    try {
      while (($row = fgetcsv($fh)) !== FALSE) {
        $old = trim((string) ($row[0] ?? ''));
        if ($old === '' || stripos($old, 'http') !== 0) {
          continue;
        }
        $path = parse_url($old, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
          continue;
        }
        $paths[] = $this->normalizePublicPath($path);
      }
    }
    finally {
      fclose($fh);
    }
    return array_values(array_unique($paths));
  }

  private function normalizePublicPath(string $path): string {
    $path = '/' . ltrim($path, '/');
    if ($path !== '/') {
      $path = rtrim($path, '/');
    }
    return $path;
  }

  private function isLanguageRootPath(string $path): bool {
    return in_array($path, self::LANGUAGE_ROOT_DENYLIST, TRUE);
  }

  /**
   * @return array{nid: int, langcode: string}|null
   */
  private function resolveNodeFromPublicPath(string $path): ?array {
    if (preg_match('#^/(ar|en|zh-hans|es|fr)/node/(\d+)$#', $path, $m)) {
      return ['nid' => (int) $m[2], 'langcode' => $m[1]];
    }
    if (preg_match('#^/node/(\d+)$#', $path, $m)) {
      return ['nid' => (int) $m[1], 'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED];
    }
    foreach ($this->langcodesToTryForPath($path) as $langcode) {
      foreach ($this->aliasCandidates($path) as $alias) {
        $record = $this->pathAliasRepository->lookupByAlias($alias, $langcode);
        if ($record === NULL || empty($record['path'])) {
          continue;
        }
        $internal = $record['path'];
        if (preg_match('#^/node/(\d+)$#', $internal, $m)) {
          return [
            'nid' => (int) $m[1],
            'langcode' => $record['langcode'] ?: $langcode,
          ];
        }
      }
    }
    return NULL;
  }

  /**
   * @return list<string>
   */
  private function langcodesToTryForPath(string $path): array {
    $list = [];
    if (preg_match('#^/(ar|en|zh-hans|es|fr)(/|$)#', $path, $m)) {
      $list[] = $m[1];
    }
    $list[] = $this->languageManager->getDefaultLanguage()->getId();
    $list[] = LanguageInterface::LANGCODE_NOT_SPECIFIED;
    return array_values(array_unique($list));
  }

  /**
   * @return list<string>
   */
  private function aliasCandidates(string $path): array {
    $candidates = [$path];
    if ($path !== '/' && str_ends_with($path, '/')) {
      $candidates[] = rtrim($path, '/');
    }
    elseif ($path !== '/') {
      $candidates[] = $path . '/';
    }
    return array_values(array_unique($candidates));
  }

  private function getMetatagFieldName(NodeInterface $node): ?string {
    $bundle = $node->bundle();
    $definitions = $this->entityFieldManager->getFieldDefinitions('node', $bundle);
    foreach ($definitions as $name => $definition) {
      if ($definition->getType() === 'metatag') {
        return (string) $name;
      }
    }
    return NULL;
  }

  private function getProjectRootDirectory(): string {
    $drupal_root = \Drupal::root();
    $dir = $drupal_root;
    for ($i = 0; $i < 6; $i++) {
      if (is_file($dir . '/composer.json')) {
        return $dir;
      }
      $parent = dirname($dir);
      if ($parent === $dir) {
        break;
      }
      $dir = $parent;
    }
    return $drupal_root;
  }

  /**
   * Resolves CSV path for Drush in DDEV vs host.
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
        ' With DDEV, use /var/www/html/%s or only the filename if the file is in the project root.',
        $basename,
      );
    }
    throw new \InvalidArgumentException(sprintf('Unreadable --%s: %s.%s', $option_name, $path, $hint));
  }

}
