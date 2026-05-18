<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\node\NodeInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic translations for official event catalog nodes.
 */
final class EventsArOfficialCommands extends DrushCommands {

  /**
   * Translate official events to Arabic from PHP datasets.
   */
  #[CLI\Command(name: 'motaded:events-official-ar', aliases: ['mevent-ar'])]
  #[CLI\Option(name: 'aliases-only', description: 'Only sync AR path aliases from EN (skip content).')]
  #[CLI\Usage(name: 'drush mevent-ar', description: 'Apply AR translations for all official events')]
  public function apply(array $options = ['aliases-only' => FALSE]): void {
    if (filter_var($options['aliases-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN)) {
      $this->syncAliasesOnly();
      return;
    }
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_events_official_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

  private function syncAliasesOnly(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'event')
      ->execute();
    $synced = 0;
    foreach ($nids as $nid) {
      $node = $storage->load((int) $nid);
      if ($node instanceof NodeInterface && motaded_custom_event_ar_sync_path_alias($node)) {
        $synced++;
      }
    }
    $this->io()->success(sprintf('Synced %d Arabic event path alias(es).', $synced));
  }

}
