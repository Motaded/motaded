<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import taxonomy terms for prod migration (EN + AR from CSV).
 */
final class TaxonomyImportCommands extends DrushCommands {

  /**
   * Import migration taxonomy terms from dataset CSV.
   */
  #[CLI\Command(name: 'motaded:taxonomy-import', aliases: ['mti'])]
  #[CLI\Option(name: 'file', description: 'CSV path (default: data/taxonomy_migration_terms.csv).')]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving.')]
  #[CLI\Usage(name: 'drush mti', description: 'Import city, country, sector, … from migration CSV.')]
  #[CLI\Usage(name: 'drush mti --dry-run', description: 'Preview import only.')]
  public function import(array $options = ['file' => NULL, 'dry-run' => FALSE]): void {
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/import_taxonomy_terms.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }

    $default = \Drupal::root() . '/modules/custom/motaded_custom/data/taxonomy_migration_terms.csv';
    $motaded_taxonomy_import_file = (string) ($options['file'] ?? '') !== ''
      ? (string) $options['file']
      : $default;
    $motaded_taxonomy_import_dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    require $script;
  }

}
