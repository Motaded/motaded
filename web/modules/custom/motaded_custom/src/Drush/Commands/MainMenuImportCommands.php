<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\Menu\MainMenuMigrator;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Export / import the "main" navigation menu.
 */
final class MainMenuImportCommands extends DrushCommands {

  public function __construct(
    protected readonly MainMenuMigrator $migrator,
  ) {
    parent::__construct();
  }

  /**
   * Export main menu from local database `db` into data/main_menu_export.php.
   */
  #[CLI\Command(name: 'motaded:main-menu-export', aliases: ['mmain-export'])]
  #[CLI\Option(name: 'source-db', description: 'Source database name (default: db).')]
  #[CLI\Usage(name: 'drush mmain-export', description: 'Export main menu from local db to dataset file.')]
  public function export(array $options = ['source-db' => 'db']): void {
    $sourceDb = (string) ($options['source-db'] ?? 'db');
    $dataset = $this->migrator->exportFromConnection('menu_export_source', $sourceDb);
    $path = $this->migrator->writeDataset($dataset);
    $count = count($dataset['links']);
    $this->io()->success(sprintf('Exported %d main menu link(s) to %s', $count, $path));
  }

  /**
   * Import main menu from data/main_menu_export.php (replaces existing custom links).
   */
  #[CLI\Command(name: 'motaded:main-menu-import', aliases: ['mmain-import'])]
  #[CLI\Option(name: 'dry-run', description: 'Validate only; do not write entities.')]
  #[CLI\Option(name: 'no-replace', description: 'Do not delete existing main menu links before import.')]
  #[CLI\Usage(name: 'drush mmain-import --dry-run', description: 'Preview import on current database.')]
  #[CLI\Usage(name: 'drush mmain-import', description: 'Replace main menu custom links from dataset.')]
  public function import(array $options = [
    'dry-run' => FALSE,
    'no-replace' => FALSE,
  ]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $replace = !filter_var($options['no-replace'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    if (!$dry_run && $replace && !$this->io()->confirm('Delete all custom links in the "main" menu and import from dataset?')) {
      $this->logger()->warning('Aborted.');
      return;
    }

    try {
      $stats = $this->migrator->import($replace, $dry_run);
    }
    catch (\Throwable $e) {
      $this->logger()->error($e->getMessage());
      return;
    }

    $label = $dry_run ? '[dry-run] Would create' : 'Created';
    $this->io()->success(sprintf('%s %d link(s), skipped %d.', $label, $stats['created'], $stats['skipped']));

    if ($stats['errors'] !== []) {
      $this->io()->warning('Issues (' . count($stats['errors']) . '):');
      foreach (array_slice($stats['errors'], 0, 30) as $err) {
        $this->io()->writeln('  - ' . $err);
      }
    }

    if (!$dry_run) {
      $this->io()->writeln('Run: drush cr');
    }
  }

}
