<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic sector taxonomy and sector_page translations.
 */
final class SectorsArCommands extends DrushCommands {

  /**
   * Import Arabic labels and copy for all sector pages.
   */
  #[CLI\Command(name: 'motaded:sectors-ar', aliases: ['msar'])]
  #[CLI\Usage(name: 'drush msar', description: 'Apply AR translations to sector taxonomy and sector_page nodes.')]
  public function apply(): void {
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_sectors_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

}
