<?php

/**
 * @file
 * Service page (page + field_display_on_services): related documents block.
 */

declare(strict_types=1);

use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Loads documents linked to a service via field_services.
 *
 * @return array{
 *   rows: array<int, array<string, mixed>>,
 *   more_url: string,
 *   more_label: string,
 *   has_more: bool,
 *   cache_tags: string[],
 * }
 */
function motaded_theme_service_documents_build(NodeInterface $service, int $limit = 3): array {
  $empty = [
    'rows' => [],
    'more_url' => Url::fromUserInput('/library')->toString(),
    'more_label' => (string) t('See more'),
    'has_more' => FALSE,
    'cache_tags' => ['node_list', 'node:' . $service->id()],
  ];

  if ($service->bundle() !== 'page' || $limit < 1) {
    return $empty;
  }
  if (!$service->hasField('field_display_on_services')
    || $service->get('field_display_on_services')->isEmpty()
    || !(int) $service->get('field_display_on_services')->value) {
    return $empty;
  }

  $service_nid = (int) $service->id();
  $storage = \Drupal::entityTypeManager()->getStorage('node');

  $count = (int) $storage->getQuery()
    ->condition('type', 'document')
    ->condition('status', 1)
    ->condition('field_services.target_id', $service_nid)
    ->accessCheck(TRUE)
    ->count()
    ->execute();

  if ($count === 0) {
    return $empty;
  }

  $doc_nids = $storage->getQuery()
    ->condition('type', 'document')
    ->condition('status', 1)
    ->condition('field_services.target_id', $service_nid)
    ->sort('created', 'DESC')
    ->range(0, $limit)
    ->accessCheck(TRUE)
    ->execute();

  $langcode = $service->language()->getId();
  $rows = [];
  $cache_tags = $empty['cache_tags'];
  if ($doc_nids) {
    foreach ($storage->loadMultiple($doc_nids) as $doc) {
      if (!$doc instanceof NodeInterface || $doc->bundle() !== 'document' || !$doc->access('view')) {
        continue;
      }
      $cache_tags = Cache::mergeTags($cache_tags, $doc->getCacheTags());
      $rows[] = _motaded_theme_platform_row_document($doc, $langcode);
    }
  }

  return [
    'rows' => $rows,
    'more_url' => Url::fromUserInput('/library')->toString(),
    'more_label' => (string) t('See more'),
    'has_more' => $count > $limit,
    'cache_tags' => array_values(array_unique($cache_tags)),
  ];
}
