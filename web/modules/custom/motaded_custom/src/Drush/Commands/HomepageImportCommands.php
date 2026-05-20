<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\Homepage\HomepageMigrator;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Export / import homepage (node 374) paragraph tree.
 */
final class HomepageImportCommands extends DrushCommands {

  public function __construct(
    protected readonly HomepageMigrator $migrator,
  ) {
    parent::__construct();
  }

  /**
   * Export homepage from local db into data/homepage_export.php.
   */
  #[CLI\Command(name: 'motaded:homepage-export', aliases: ['mhome-export'])]
  #[CLI\Usage(name: 'drush mhome-export', description: 'Export node 374 from local db (uses env DRUPAL_DB=).')]
  public function export(): void {
    try {
      $path = $this->migrator->runExport();
      $this->io()->success('Homepage exported to ' . $path);
    }
    catch (\Throwable $e) {
      $this->logger()->error($e->getMessage());
    }
  }

  /**
   * Import homepage on current database (replaces paragraph tree on node 374).
   */
  #[CLI\Command(name: 'motaded:homepage-import', aliases: ['mhome-import'])]
  #[CLI\Option(name: 'dry-run', description: 'Count paragraphs only; do not modify.')]
  #[CLI\Usage(name: 'drush mhome-import --dry-run', description: 'Preview paragraph count.')]
  #[CLI\Usage(name: 'drush mhome-import', description: 'Replace homepage paragraphs from dataset.')]
  public function import(array $options = ['dry-run' => FALSE]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    if (!$dry_run && !$this->io()->confirm('Replace ALL paragraphs on homepage node 374?')) {
      $this->logger()->warning('Aborted.');
      return;
    }

    try {
      $stats = $this->migrator->import($dry_run);
    }
    catch (\Throwable $e) {
      $this->logger()->error($e->getMessage());
      return;
    }

    if ($dry_run) {
      $this->io()->success(sprintf('[dry-run] Would import %d paragraph(s).', $stats['paragraphs']));
    }
    else {
      $this->io()->success(sprintf('Imported %d paragraph(s).', $stats['paragraphs']));
    }

    if ($stats['errors'] !== []) {
      $this->io()->warning('Issues (' . count($stats['errors']) . '):');
      foreach (array_slice($stats['errors'], 0, 25) as $err) {
        $this->io()->writeln('  - ' . $err);
      }
    }

    if (!$dry_run) {
      $this->io()->writeln('Run: drush cr');
    }
  }

}
