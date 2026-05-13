<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\SectorIndicators\SectorIndicatorsImporter;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for sector indicators (World Bank) import.
 */
final class SectorIndicatorsImportCommands extends DrushCommands {

  public function __construct(
    private readonly SectorIndicatorsImporter $importer,
  ) {}

  /**
   * Fetch World Bank data and refresh sector_indicator_points table.
   */
  #[CLI\Command(name: 'motaded:sector-indicators-import', aliases: ['msii'])]
  #[CLI\Usage(name: 'drush msii', description: 'Run sector indicators import immediately (same as queue worker).')]
  public function import(): void {
    $ok = $this->importer->importAll();
    if ($ok) {
      $this->logger()->success('Sector indicators import completed (all World Bank series returned data).');
    }
    else {
      $this->logger()->warning('Sector indicators import finished with gaps; see dblog. Previous snapshot rows are kept where fetch failed.');
    }
  }

}
