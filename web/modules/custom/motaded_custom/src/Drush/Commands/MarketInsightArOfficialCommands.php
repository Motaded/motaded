<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\node\NodeInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Apply Arabic translations for official market insight catalog nodes.
 */
final class MarketInsightArOfficialCommands extends DrushCommands {

  /**
   * Translate official market insights to Arabic from PHP datasets.
   */
  #[CLI\Command(name: 'motaded:market-insight-official-ar', aliases: ['minsight-ar'])]
  #[CLI\Option(name: 'aliases-only', description: 'Only sync AR path aliases from EN (skip content).')]
  #[CLI\Option(name: 'media-only', description: 'Copy EN hero/thumbnail and shared refs to AR (skip text).')]
  #[CLI\Usage(name: 'drush minsight-ar', description: 'Apply AR translations for all official market insights')]
  public function apply(array $options = ['aliases-only' => FALSE, 'media-only' => FALSE]): void {
    if (filter_var($options['aliases-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN)) {
      $this->syncAliasesOnly();
      return;
    }
    if (filter_var($options['media-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN)) {
      $this->syncMediaOnly();
      return;
    }
    $script = \Drupal::root() . '/modules/custom/motaded_custom/scripts/translate_market_insights_official_ar.php';
    if (!is_readable($script)) {
      throw new \RuntimeException('Script not found: ' . $script);
    }
    require $script;
  }

  private function syncMediaOnly(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'market_insight')
      ->execute();
    $updated = 0;
    foreach ($nids as $nid) {
      $node = $storage->load((int) $nid);
      if (!$node instanceof NodeInterface
        || !$node->hasTranslation('en')
        || !$node->hasTranslation('ar')) {
        continue;
      }
      $en = $node->getTranslation('en');
      $ar = $node->getTranslation('ar');
      motaded_custom_node_ar_copy_shared_field_values(
        $ar,
        $en,
        motaded_custom_market_insight_ar_shared_field_names(),
      );
      $node->save();
      $updated++;
    }
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list']);
    $this->io()->success(sprintf('Copied shared EN fields onto %d Arabic market insight translation(s).', $updated));
  }

  private function syncAliasesOnly(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'market_insight')
      ->execute();
    $synced = 0;
    foreach ($nids as $nid) {
      $node = $storage->load((int) $nid);
      if ($node instanceof NodeInterface && motaded_custom_market_insight_ar_sync_path_alias($node)) {
        $synced++;
      }
    }
    $this->io()->success(sprintf('Synced %d Arabic market insight path alias(es).', $synced));
  }

}
