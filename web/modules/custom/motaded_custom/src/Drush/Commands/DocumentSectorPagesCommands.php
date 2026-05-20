<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Sync document field_sector_pages from field_sector taxonomy.
 */
final class DocumentSectorPagesCommands extends DrushCommands {

  /**
   * Fills field_sector_pages on documents from field_sector → sector_page mapping.
   */
  #[CLI\Command(name: 'motaded:document-sector-pages', aliases: ['mdoc-sectors'])]
  #[CLI\Option(name: 'replace', description: 'Replace existing field_sector_pages values.')]
  #[CLI\Usage(name: 'drush mdoc-sectors', description: 'Fill empty field_sector_pages where a sector hub exists for field_sector.')]
  #[CLI\Usage(name: 'drush mdoc-sectors --replace', description: 'Overwrite all field_sector_pages from field_sector.')]
  public function sync(array $options = ['replace' => FALSE]): void {
    require_once \Drupal::root() . '/modules/custom/motaded_custom/includes/motaded_custom.document_sector_pages.inc';

    $only_if_empty = !filter_var($options['replace'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $this->io()->writeln($only_if_empty
      ? 'Syncing empty field_sector_pages from field_sector (sector_page.field_sector).'
      : 'Replacing field_sector_pages from field_sector for all published documents.');

    $stats = motaded_custom_document_sync_all_sector_pages($only_if_empty);
    $this->io()->table(
      ['Result', 'Count'],
      [
        ['Updated', (string) $stats['updated']],
        ['Skipped (already has sector pages)', (string) $stats['skipped_has_pages']],
        ['Skipped (no field_sector)', (string) $stats['skipped_no_sector']],
        ['Skipped (no hub for term)', (string) $stats['skipped_no_hub']],
      ]
    );
    if ($stats['unmapped_terms'] !== []) {
      $this->io()->warning('No sector_page hub for these sector terms (left unchanged):');
      foreach ($stats['unmapped_terms'] as $term => $count) {
        $this->io()->writeln(sprintf('  - %s (%d document(s))', $term, $count));
      }
    }
    $this->io()->success(sprintf('Done. Updated %d document(s).', $stats['updated']));
  }

}
