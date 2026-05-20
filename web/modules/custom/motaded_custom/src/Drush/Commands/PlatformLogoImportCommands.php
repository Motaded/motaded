<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\Logo\LogoImportConfig;
use Drupal\motaded_custom\Logo\NodeLogoImporter;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Download platform logos from the web and attach field_logo media.
 */
final class PlatformLogoImportCommands extends DrushCommands {

  public function __construct(
    protected readonly NodeLogoImporter $logoImporter,
  ) {
    parent::__construct();
  }

  #[CLI\Command(name: 'motaded:platform-import-logos', aliases: ['mpil'])]
  #[CLI\Option(name: 'force', description: 'Replace existing field_logo.')]
  #[CLI\Option(name: 'dry-run', description: 'Discover URLs only; do not save files or nodes.')]
  #[CLI\Option(name: 'title', description: 'Process a single platform by exact title (default language).')]
  #[CLI\Option(name: 'limit', description: 'Max platforms to process (0 = all).')]
  #[CLI\Option(name: 'repair', description: 'Convert existing platform logo files to PNG (fixes empty <img> for .ico).')]
  #[CLI\Option(name: 'only-overrides', description: 'Only platforms listed in platform_import.logos.php (implies re-import those rows).')]
  #[CLI\Usage(name: 'drush mpil', description: 'Download logos for platforms without field_logo.')]
  #[CLI\Usage(name: 'drush mpil --force', description: 'Re-download and replace all platform logos.')]
  #[CLI\Usage(name: 'drush mpil --only-overrides --force', description: 'Re-import curated logos from platform_import.logos.php.')]
  #[CLI\Usage(name: 'drush mpil --repair', description: 'Convert existing logos to PNG without re-downloading.')]
  public function importLogos(array $options = [
    'force' => FALSE,
    'dry-run' => FALSE,
    'title' => '',
    'limit' => 0,
    'repair' => FALSE,
    'only-overrides' => FALSE,
  ]): void {
    $this->logoImporter->run(LogoImportConfig::platforms(), $options, $this->io(), $this->logger());
    $this->logger()->success('Platform logo import finished.');
  }

}
