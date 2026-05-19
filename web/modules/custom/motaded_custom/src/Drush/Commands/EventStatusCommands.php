<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Recompute event field_event_status from start/end dates.
 */
final class EventStatusCommands extends DrushCommands {

  /**
   * Syncs upcoming / ongoing / past on all event nodes.
   */
  #[CLI\Command(name: 'motaded:event-status-sync', aliases: ['mevent-status'])]
  #[CLI\Usage(name: 'drush mevent-status', description: 'Recompute field_event_status for all events.')]
  public function sync(): void {
    $updated = motaded_custom_sync_all_event_statuses();
    $this->io()->success(sprintf('Updated event status on %d node(s).', $updated));
  }

}
