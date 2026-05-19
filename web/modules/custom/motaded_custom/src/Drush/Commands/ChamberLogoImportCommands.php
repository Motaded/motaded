<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\Logo\LogoImportConfig;
use Drupal\motaded_custom\Logo\NodeLogoImporter;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Download chamber logos from the web and attach field_logo media.
 */
final class ChamberLogoImportCommands extends DrushCommands {

  public function __construct(
    protected readonly NodeLogoImporter $logoImporter,
  ) {
    parent::__construct();
  }

  #[CLI\Command(name: 'motaded:chamber-import-logos', aliases: ['mcil'])]
  #[CLI\Option(name: 'force', description: 'Replace existing field_logo.')]
  #[CLI\Option(name: 'dry-run', description: 'Discover URLs only; do not save files or nodes.')]
  #[CLI\Option(name: 'title', description: 'Process a single chamber by exact title (default language).')]
  #[CLI\Option(name: 'limit', description: 'Max chambers to process (0 = all).')]
  #[CLI\Option(name: 'repair', description: 'Convert existing chamber logo files to PNG (fixes empty <img> for .ico).')]
  #[CLI\Option(name: 'only-overrides', description: 'Only chambers listed in chamber_import.logos.php.')]
  #[CLI\Usage(name: 'drush mcil', description: 'Download logos for chambers without field_logo.')]
  #[CLI\Usage(name: 'drush mcil --force', description: 'Re-download and replace all chamber logos.')]
  #[CLI\Usage(name: 'drush mcil --repair', description: 'Convert existing logos to PNG without re-downloading.')]
  public function importLogos(array $options = [
    'force' => FALSE,
    'dry-run' => FALSE,
    'title' => '',
    'limit' => 0,
    'repair' => FALSE,
    'only-overrides' => FALSE,
  ]): void {
    $this->logoImporter->run(LogoImportConfig::chambers(), $options, $this->io(), $this->logger());
    $this->logger()->success('Chamber logo import finished.');
  }

}
