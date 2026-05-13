<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\MarketIndicators\MarketIndicatorsImporter;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for market indicators (World Bank) import.
 */
final class MarketIndicatorsImportCommands extends DrushCommands {

  public function __construct(
    private readonly MarketIndicatorsImporter $importer,
  ) {}

  /**
   * Fetch World Bank data and refresh market_indicators tables.
   */
  #[CLI\Command(name: 'motaded:market-indicators-import', aliases: ['mmii'])]
  #[CLI\Usage(name: 'drush mmii', description: 'Run import immediately (same as queue worker).')]
  public function import(): void {
    $ok = $this->importer->importAll();
    if ($ok) {
      $this->logger()->success('Market indicators import completed (all World Bank series returned data).');
    }
    else {
      $this->logger()->warning('Market indicators import finished with gaps; see dblog. Previous snapshot rows are kept where fetch failed.');
    }
  }

}
