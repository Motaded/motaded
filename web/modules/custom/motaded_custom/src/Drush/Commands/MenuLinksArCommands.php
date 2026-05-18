<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic menu link translations from dataset.
 */
final class MenuLinksArCommands extends DrushCommands {

  /**
   * Import Arabic titles for menu_link_content entities (main, footer, etc.).
   */
  #[CLI\Command(name: 'motaded:menu-links-ar', aliases: ['mmnar'])]
  #[CLI\Usage(name: 'drush mmnar', description: 'Apply AR translations to all custom menu links.')]
  public function apply(): void {
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_menu_links_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

}
