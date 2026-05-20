<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Menu;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Export / import custom links for the "main" menu between databases.
 */
final class MainMenuMigrator {

  public const MENU_NAME = 'main';

  public const DATASET_REL = '/data/main_menu_export.php';

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Path to the export dataset inside motaded_custom module.
   */
  public function datasetPath(): string {
    return \Drupal::service('extension.list.module')->getPath('motaded_custom') . self::DATASET_REL;
  }

  /**
   * Exports the main menu from a secondary DB (typically local `db`).
   *
   * @return array{version: int, menu_name: string, exported_at: string, links: list<array<string, mixed>>}
   */
  public function exportFromConnection(string $connectionKey = 'menu_export_source', string $database = 'db'): array {
    $this->registerSourceConnection($connectionKey, $database);
    $db = Database::getConnection('default', $connectionKey);

    $rows = $db->select('menu_link_content_data', 'd')
      ->fields('d', ['id', 'title', 'link__uri', 'parent', 'weight', 'enabled', 'expanded', 'description'])
      ->condition('menu_name', self::MENU_NAME)
      ->condition('langcode', 'en')
      ->condition('default_langcode', 1)
      ->orderBy('parent')
      ->orderBy('weight')
      ->orderBy('title')
      ->execute()
      ->fetchAllAssoc('id', \PDO::FETCH_ASSOC);

    if ($rows === []) {
      return $this->emptyDataset();
    }

    $uuidMap = $db->select('menu_link_content', 'mlc')
      ->fields('mlc', ['id', 'uuid'])
      ->condition('id', array_keys($rows), 'IN')
      ->execute()
      ->fetchAllKeyed();

    $arTitles = [];
    $arResult = $db->select('menu_link_content_data', 'd')
      ->fields('d', ['id', 'title'])
      ->condition('menu_name', self::MENU_NAME)
      ->condition('langcode', 'ar')
      ->execute();
    foreach ($arResult as $arRow) {
      $arTitles[(int) $arRow->id] = (string) $arRow->title;
    }

    $links = [];
    foreach ($rows as $id => $row) {
      $id = (int) $id;
      $sourceUuid = (string) ($uuidMap[$id] ?? '');
      if ($sourceUuid === '') {
        continue;
      }

      $parentSourceUuid = $this->parentSourceUuid((string) ($row['parent'] ?? ''), $uuidMap);

      $uri = (string) ($row['link__uri'] ?? '');
      $nodeResolve = $this->nodeResolveFromUri($uri, $db);

      $links[] = [
        'source_uuid' => $sourceUuid,
        'parent_source_uuid' => $parentSourceUuid,
        'title_en' => (string) ($row['title'] ?? ''),
        'title_ar' => $arTitles[$id] ?? NULL,
        'uri' => $uri,
        'node_resolve' => $nodeResolve,
        'weight' => (int) ($row['weight'] ?? 0),
        'enabled' => (bool) ($row['enabled'] ?? TRUE),
        'expanded' => (bool) ($row['expanded'] ?? FALSE),
        'description' => (string) ($row['description'] ?? ''),
      ];
    }

    return [
      'version' => 1,
      'menu_name' => self::MENU_NAME,
      'exported_at' => gmdate('c'),
      'links' => $links,
    ];
  }

  /**
   * Writes export dataset to the module data directory.
   */
  public function writeDataset(array $dataset): string {
    $path = $this->datasetPath();
    $dir = dirname($path);
    if (!is_dir($dir)) {
      mkdir($dir, 0755, TRUE);
    }

    $export = var_export($dataset, TRUE);
    $php = <<<PHP
<?php

declare(strict_types=1);

/**
 * @file
 * Exported "main" menu (auto-generated). Import: drush mmain-import
 *
 * Generated: {$dataset['exported_at']}
 */

return {$export};

PHP;

    file_put_contents($path, $php);
    return $path;
  }

  /**
   * Imports dataset into the active database.
   *
   * @return array{created: int, skipped: int, errors: list<string>}
   */
  public function import(bool $replace = TRUE, bool $dry_run = FALSE): array {
    $path = $this->datasetPath();
    if (!is_readable($path)) {
      throw new \RuntimeException('Dataset not found. Run: drush mmain-export');
    }

    /** @var array{version?: int, menu_name?: string, links?: list<array<string, mixed>>} $dataset */
    $dataset = require $path;
    $links = $dataset['links'] ?? [];
    if ($links === []) {
      throw new \RuntimeException('Dataset has no links.');
    }

    $stats = ['created' => 0, 'skipped' => 0, 'errors' => []];

    if ($replace && !$dry_run) {
      $this->deleteCustomMainMenuLinks();
    }

    $byParent = [];
    foreach ($links as $row) {
      $parentKey = (string) ($row['parent_source_uuid'] ?? '');
      $byParent[$parentKey][] = $row;
    }

    $newUuidBySource = [];
    $this->importLevel($byParent, '', $newUuidBySource, $dry_run, $stats);

    if (!$dry_run) {
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['config:system.menu']);
    }

    return $stats;
  }

  /**
   * Recursively creates menu links depth-first.
   *
   * @param array<string, list<array<string, mixed>>> $byParent
   * @param array<string, string> $newUuidBySource
   * @param array{created: int, skipped: int, errors: list<string>} $stats
   */
  private function importLevel(
    array $byParent,
    string $parentSourceUuid,
    array &$newUuidBySource,
    bool $dry_run,
    array &$stats,
  ): void {
    foreach ($byParent[$parentSourceUuid] ?? [] as $row) {
      $sourceUuid = (string) ($row['source_uuid'] ?? '');
      $titleEn = trim((string) ($row['title_en'] ?? ''));
      if ($sourceUuid === '' || $titleEn === '') {
        $stats['skipped']++;
        continue;
      }

      $uri = $this->resolveUri($row, $stats);
      if ($uri === NULL) {
        $stats['skipped']++;
        continue;
      }

      if ($dry_run) {
        $stats['created']++;
        $newUuidBySource[$sourceUuid] = 'dry-run-' . $sourceUuid;
        $this->importLevel($byParent, $sourceUuid, $newUuidBySource, $dry_run, $stats);
        continue;
      }

      try {
        $parentPluginId = '';
        if ($parentSourceUuid !== '' && isset($newUuidBySource[$parentSourceUuid])) {
          $parentPluginId = 'menu_link_content:' . $newUuidBySource[$parentSourceUuid];
        }

        $values = [
          'title' => $titleEn,
          'link' => ['uri' => $uri],
          'menu_name' => self::MENU_NAME,
          'weight' => (int) ($row['weight'] ?? 0),
          'enabled' => (bool) ($row['enabled'] ?? TRUE),
          'expanded' => (bool) ($row['expanded'] ?? FALSE),
          'langcode' => 'en',
        ];
        $description = trim((string) ($row['description'] ?? ''));
        if ($description !== '') {
          $values['description'] = $description;
        }

        $link = MenuLinkContent::create($values);
        if ($parentPluginId !== '') {
          $link->set('parent', $parentPluginId);
        }
        $link->save();

        $newUuid = $link->uuid();
        $newUuidBySource[$sourceUuid] = $newUuid;

        $titleAr = trim((string) ($row['title_ar'] ?? ''));
        if ($titleAr !== '' && $titleAr !== $titleEn) {
          if (!$link->hasTranslation('ar')) {
            $arValues = $values;
            $arValues['title'] = $titleAr;
            $link->addTranslation('ar', $arValues);
          }
          else {
            $link->getTranslation('ar')->setTitle($titleAr);
          }
          $link->save();
        }

        $stats['created']++;
      }
      catch (\Throwable $e) {
        $stats['errors'][] = $titleEn . ': ' . $e->getMessage();
      }

      $this->importLevel($byParent, $sourceUuid, $newUuidBySource, $dry_run, $stats);
    }
  }

  /**
   * Deletes custom menu_link_content entities in the main menu.
   */
  public function deleteCustomMainMenuLinks(): void {
    $storage = $this->entityTypeManager->getStorage('menu_link_content');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('menu_name', self::MENU_NAME)
      ->execute();

    if ($ids === []) {
      return;
    }

    $entities = $storage->loadMultiple($ids);
    $storage->delete($entities);
  }

  /**
   * @param array<string, mixed> $row
   * @param array{created: int, skipped: int, errors: list<string>} $stats
   */
  private function resolveUri(array $row, array &$stats): ?string {
    $uri = (string) ($row['uri'] ?? '');
    $nodeResolve = $row['node_resolve'] ?? NULL;
    if (!is_array($nodeResolve) || $nodeResolve === []) {
      return $uri !== '' ? $uri : NULL;
    }

    $bundle = (string) ($nodeResolve['bundle'] ?? '');
    $title = (string) ($nodeResolve['title'] ?? '');
    $titleTrimmed = trim($title);
    if ($bundle === '' || $titleTrimmed === '') {
      return $uri !== '' ? $uri : NULL;
    }

    $candidates = array_values(array_unique(array_filter([$title, $titleTrimmed])));
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->condition('title', $candidates, 'IN')
      ->range(0, 1)
      ->execute();

    if ($nids === []) {
      $stats['errors'][] = sprintf('Node not found for menu link "%s": %s (%s)', (string) ($row['title_en'] ?? ''), $titleTrimmed, $bundle);
      return $uri !== '' ? $uri : NULL;
    }

    return 'entity:node/' . (int) reset($nids);
  }

  private function parentSourceUuid(string $parent, array $uuidMap): ?string {
    if ($parent === '' || !str_starts_with($parent, 'menu_link_content:')) {
      return NULL;
    }
    $parentUuid = substr($parent, strlen('menu_link_content:'));
    foreach ($uuidMap as $id => $uuid) {
      if ($uuid === $parentUuid) {
        return $parentUuid;
      }
    }
    return $parentUuid !== '' ? $parentUuid : NULL;
  }

  /**
   * @return array{bundle: string, title: string}|null
   */
  private function nodeResolveFromUri(string $uri, Connection $db): ?array {
    if (!preg_match('#^entity:node/(\d+)$#', $uri, $m)) {
      return NULL;
    }
    $nid = (int) $m[1];
    $node = $db->select('node_field_data', 'n')
      ->fields('n', ['title', 'type'])
      ->condition('nid', $nid)
      ->condition('langcode', 'en')
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();
    if (!$node) {
      return NULL;
    }
    return [
      'bundle' => (string) $node['type'],
      'title' => (string) $node['title'],
    ];
  }

  private function registerSourceConnection(string $connectionKey, string $database): void {
    if (Database::getConnectionInfo($connectionKey)) {
      return;
    }

    $default = Database::getConnectionInfo('default')['default'] ?? [];
    if ($default === []) {
      throw new \RuntimeException('No default database connection.');
    }

    $info = $default;
    $info['database'] = $database;
    Database::addConnectionInfo($connectionKey, 'default', $info);
  }

  /**
   * @return array{version: int, menu_name: string, exported_at: string, links: list<array<string, mixed>>}
   */
  private function emptyDataset(): array {
    return [
      'version' => 1,
      'menu_name' => self::MENU_NAME,
      'exported_at' => gmdate('c'),
      'links' => [],
    ];
  }

}
