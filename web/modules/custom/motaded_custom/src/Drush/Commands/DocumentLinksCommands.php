<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Sync document internal links (services + sector pages).
 */
final class DocumentLinksCommands extends DrushCommands {

  /**
   * Fills field_services and field_sector_pages on Library documents.
   */
  #[CLI\Command(name: 'motaded:document-links', aliases: ['mdoc-links'])]
  #[CLI\Option(name: 'replace', description: 'Replace existing service and sector_page references.')]
  #[CLI\Usage(name: 'drush mdoc-links', description: 'Fill empty field_services and field_sector_pages.')]
  #[CLI\Usage(name: 'drush mdoc-links --replace', description: 'Rebuild all document internal links.')]
  public function sync(array $options = ['replace' => FALSE]): void {
    require_once \Drupal::root() . '/modules/custom/motaded_custom/includes/motaded_custom.document_links.inc';

    $replace = filter_var($options['replace'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $this->io()->writeln($replace
      ? 'Replacing field_services and field_sector_pages on all published documents.'
      : 'Filling empty field_services and field_sector_pages.');

    $stats = motaded_custom_document_sync_all_links(!$replace, $replace);
    $this->io()->table(
      ['Result', 'Count'],
      [
        ['Services updated', (string) $stats['services_updated']],
        ['Sector pages updated', (string) $stats['sector_pages_updated']],
        ['No service match', (string) $stats['skipped_no_service_match']],
        ['No sector hub', (string) $stats['skipped_no_sector_hub']],
      ]
    );
    if (!empty($stats['unmapped_sectors'])) {
      $this->io()->warning('Documents still without sector_page after sync:');
      foreach ($stats['unmapped_sectors'] as $term => $count) {
        $this->io()->writeln(sprintf('  - %s (%d)', $term, $count));
      }
    }
    $this->io()->success(sprintf(
      'Done. Services: %d, sector pages: %d.',
      $stats['services_updated'],
      $stats['sector_pages_updated']
    ));
  }

}
