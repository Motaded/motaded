<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic taxonomy term translations from dataset.
 */
final class TaxonomyTermsArCommands extends DrushCommands {

  /**
   * Import Arabic labels for all taxonomy terms.
   */
  #[CLI\Command(name: 'motaded:taxonomy-terms-ar', aliases: ['mtar'])]
  #[CLI\Usage(name: 'drush mtar', description: 'Apply AR translations to all taxonomy terms.')]
  public function apply(): void {
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_taxonomy_terms_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

}
