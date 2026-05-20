<?php

/**
 * @file
 * Sync document field_sector_pages from field_sector taxonomy + sector_page.field_sector.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Maps sector taxonomy term ID → published sector_page node ID.
 *
 * @return array<int, int>
 */
function motaded_custom_sector_term_to_page_map(): array {
  static $map = NULL;
  if ($map !== NULL) {
    return $map;
  }
  $map = [];
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'sector_page')
    ->condition('status', 1)
    ->execute();
  if ($nids) {
    foreach ($storage->loadMultiple($nids) as $page) {
      if (!$page instanceof NodeInterface || $page->bundle() !== 'sector_page') {
        continue;
      }
      if (!$page->hasField('field_sector') || $page->get('field_sector')->isEmpty()) {
        continue;
      }
      $tid = (int) $page->get('field_sector')->target_id;
      if ($tid > 0) {
        $map[$tid] = (int) $page->id();
      }
    }
  }
  return $map;
}

/**
 * Resolves sector_page nid(s) from a document's field_sector term.
 *
 * @return int[]
 */
function motaded_custom_document_sector_page_nids_from_sector(NodeInterface $document): array {
  if ($document->bundle() !== 'document' || !$document->hasField('field_sector') || $document->get('field_sector')->isEmpty()) {
    return [];
  }
  $tid = (int) $document->get('field_sector')->target_id;
  if ($tid <= 0) {
    return [];
  }
  $map = motaded_custom_sector_term_to_page_map();
  return isset($map[$tid]) ? [$map[$tid]] : [];
}

/**
 * Sets field_sector_pages from field_sector when a hub exists for that term.
 *
 * @return string
 *   One of: updated, skipped_has_pages, skipped_no_sector, skipped_no_hub, skipped_no_field.
 */
function motaded_custom_document_apply_sector_pages_from_sector(NodeInterface $document, bool $only_if_empty = TRUE): string {
  require_once __DIR__ . '/motaded_custom.document_links.inc';
  return motaded_custom_document_apply_sector_pages($document, $only_if_empty);
}

/**
 * Syncs field_sector_pages on all document translations for one node.
 */
function motaded_custom_document_save_sector_pages_all_translations(NodeInterface $document, bool $only_if_empty = TRUE): string {
  $result = 'skipped_no_field';
  foreach (array_keys($document->getTranslationLanguages()) as $langcode) {
    $translation = $document->getTranslation($langcode);
    $step = motaded_custom_document_apply_sector_pages_from_sector($translation, $only_if_empty);
    if ($step === 'updated') {
      $result = 'updated';
    }
    elseif ($result !== 'updated') {
      $result = $step;
    }
  }
  if ($result === 'updated') {
    $document->save();
  }
  return $result;
}

/**
 * Batch-syncs documents (default: only when field_sector_pages is empty).
 *
 * @return array{
 *   updated: int,
 *   skipped_has_pages: int,
 *   skipped_no_sector: int,
 *   skipped_no_hub: int,
 *   unmapped_terms: array<string, int>,
 * }
 */
function motaded_custom_document_sync_all_sector_pages(bool $only_if_empty = TRUE, ?callable $progress = NULL): array {
  $stats = [
    'updated' => 0,
    'skipped_has_pages' => 0,
    'skipped_no_sector' => 0,
    'skipped_no_hub' => 0,
    'unmapped_terms' => [],
  ];
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'document')
    ->condition('status', 1)
    ->execute();
  foreach ($nids as $nid) {
    $node = $storage->load((int) $nid);
    if (!$node instanceof NodeInterface) {
      continue;
    }
    $before = $result = motaded_custom_document_save_sector_pages_all_translations($node, $only_if_empty);
    if ($result === 'skipped_no_hub' && !$node->get('field_sector')->isEmpty()) {
      $term = $node->get('field_sector')->entity;
      $label = $term ? $term->label() : (string) $node->get('field_sector')->target_id;
      $stats['unmapped_terms'][$label] = ($stats['unmapped_terms'][$label] ?? 0) + 1;
    }
    if (isset($stats[$before])) {
      $stats[$before]++;
    }
    if ($progress) {
      $progress($node, $before);
    }
  }
  arsort($stats['unmapped_terms']);
  return $stats;
}
