<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\node\NodeInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic translations for document nodes.
 */
final class DocumentsArCommands extends DrushCommands {

  /**
   * Translate all document nodes to Arabic (title + short description).
   */
  #[CLI\Command(name: 'motaded:documents-ar', aliases: ['mdar'])]
  #[CLI\Option(name: 'aliases-only', description: 'Only sync AR path aliases from EN (skip translation updates).')]
  #[CLI\Usage(name: 'drush mdar', description: 'Apply AR translations from documents_ar.dataset.php')]
  #[CLI\Usage(name: 'drush mdar --aliases-only', description: 'Fix AR /node/N URLs by copying EN pathauto aliases')]
  public function apply(array $options = ['aliases-only' => FALSE]): void {
    if (filter_var($options['aliases-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN)) {
      $this->syncAliasesOnly();
      return;
    }
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_documents_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

  /**
   * Copy EN document aliases to AR (Pathauto cannot slug Arabic titles).
   */
  private function syncAliasesOnly(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'document')
      ->execute();
    $synced = 0;
    foreach ($nids as $nid) {
      $node = $storage->load((int) $nid);
      if ($node instanceof NodeInterface && motaded_custom_document_ar_sync_path_alias($node)) {
        $synced++;
      }
    }
    $this->io()->success(sprintf('Synced %d Arabic document path alias(es).', $synced));
  }

}
