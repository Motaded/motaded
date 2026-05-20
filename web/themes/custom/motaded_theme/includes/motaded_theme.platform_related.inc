<?php

/**
 * @file
 * Platform full page: related documents block.
 */

declare(strict_types=1);

use Drupal\Core\Cache\Cache;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Loads up to $limit documents linked to a platform via field_platforms.
 *
 * @return array{
 *   rows: array<int, array<string, mixed>>,
 *   more_url: string,
 *   more_label: string,
 *   has_more: bool,
 *   cache_tags: string[],
 * }
 */
function motaded_theme_platform_documents_build(NodeInterface $platform, int $limit = 3): array {
  $empty = [
    'rows' => [],
    'more_url' => Url::fromUserInput('/library')->toString(),
    'more_label' => (string) t('See more'),
    'has_more' => FALSE,
    'cache_tags' => ['node_list', 'node:' . $platform->id()],
  ];

  if ($platform->bundle() !== 'platform' || $limit < 1) {
    return $empty;
  }

  $platform_nid = (int) $platform->id();
  $storage = \Drupal::entityTypeManager()->getStorage('node');

  $count = (int) $storage->getQuery()
    ->condition('type', 'document')
    ->condition('status', 1)
    ->condition('field_platforms.target_id', $platform_nid)
    ->accessCheck(TRUE)
    ->count()
    ->execute();

  if ($count === 0) {
    return $empty;
  }

  $doc_nids = $storage->getQuery()
    ->condition('type', 'document')
    ->condition('status', 1)
    ->condition('field_platforms.target_id', $platform_nid)
    ->sort('created', 'DESC')
    ->range(0, $limit)
    ->accessCheck(TRUE)
    ->execute();

  $langcode = $platform->language()->getId();
  $rows = [];
  $cache_tags = $empty['cache_tags'];
  if ($doc_nids) {
    $docs = $storage->loadMultiple($doc_nids);
    foreach ($docs as $doc) {
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

/**
 * @return array{
 *   type: string,
 *   url: string,
 *   title: string,
 *   meta: string,
 *   excerpt: string,
 *   badge: string,
 * }
 */
function _motaded_theme_platform_row_document(NodeInterface $node, ?string $langcode = NULL): array {
  $entity_repository = \Drupal::service('entity.repository');
  $langcode = $langcode ?? $node->language()->getId();
  $base = _motaded_theme_sector_row_document($node, $langcode, $entity_repository);
  $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);

  $excerpt = '';
  if ($node->hasField('field_short_description') && !$node->get('field_short_description')->isEmpty()) {
    $raw = trim((string) $node->get('field_short_description')->value);
    if ($raw !== '') {
      $excerpt = motaded_theme_plain_text_truncate($raw, 130);
    }
  }

  $badge = '';
  if ($node->hasField('field_document_type') && !$node->get('field_document_type')->isEmpty()) {
    $term = $node->get('field_document_type')->entity;
    if ($term) {
      $term = $entity_repository->getTranslationFromContext($term, $langcode);
      $badge = $term->label();
    }
  }
  elseif ($node->hasField('field_category') && !$node->get('field_category')->isEmpty()) {
    $term = $node->get('field_category')->entity;
    if ($term) {
      $term = $entity_repository->getTranslationFromContext($term, $langcode);
      $badge = $term->label();
    }
  }

  return array_merge($base, [
    'excerpt' => $excerpt,
    'badge' => $badge,
  ]);
}
