<?php

/**
 * @file
 * Service type taxonomy helpers and content migrations.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Canonical service type dataset.
 *
 * @return array{terms: list<string>, aliases: array<string, string>, document_remap: array<string, string>}
 */
function motaded_custom_service_type_canonical(): array {
  static $data;
  if ($data === NULL) {
    $path = dirname(__DIR__) . '/data/service_type.canonical.php';
    $data = is_readable($path) ? require $path : ['terms' => [], 'aliases' => [], 'document_remap' => []];
  }
  return $data;
}

/**
 * Resolves a service type label to a taxonomy term ID (canonical name or alias).
 */
function motaded_custom_service_type_tid_by_name(string $name): ?int {
  $name = trim($name);
  if ($name === '') {
    return NULL;
  }
  $canonical = motaded_custom_service_type_canonical();
  $resolved = $canonical['aliases'][$name] ?? $name;
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', 'taxonomy')
    ->condition('name', $resolved)
    ->range(0, 1)
    ->execute();
  return $ids ? (int) reset($ids) : NULL;
}

/**
 * Ensures canonical Service type terms exist.
 */
function motaded_custom_ensure_service_type_terms(): void {
  if (!\Drupal::moduleHandler()->moduleExists('taxonomy')) {
    return;
  }
  $terms = motaded_custom_service_type_canonical()['terms'] ?? [];
  if ($terms !== []) {
    _motaded_custom_install_ensure_taxonomy_terms('taxonomy', $terms);
  }
}

/**
 * Remaps document field_taxonomy from legacy labels to canonical service types.
 *
 * @return array<string, int>
 *   Counts keyed by "from → to".
 */
function motaded_custom_remap_document_service_types(): array {
  $remap = motaded_custom_service_type_canonical()['document_remap'] ?? [];
  if ($remap === []) {
    return [];
  }
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $counts = [];

  foreach ($remap as $from => $to) {
    $from_tid = motaded_custom_service_type_tid_by_name($from);
    $to_tid = motaded_custom_service_type_tid_by_name($to);
    if ($from_tid === NULL || $to_tid === NULL || $from_tid === $to_tid) {
      continue;
    }
    $nids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'document')
      ->condition('field_taxonomy.target_id', $from_tid)
      ->execute();
    $updated = 0;
    foreach ($node_storage->loadMultiple($nids) as $node) {
      if (!$node instanceof NodeInterface || !$node->hasField('field_taxonomy')) {
        continue;
      }
      foreach (array_keys($node->getTranslationLanguages(TRUE)) as $langcode) {
        $translation = $node->getTranslation($langcode);
        if (!$translation->hasField('field_taxonomy') || $translation->get('field_taxonomy')->isEmpty()) {
          continue;
        }
        if ((int) $translation->get('field_taxonomy')->target_id !== $from_tid) {
          continue;
        }
        $translation->set('field_taxonomy', ['target_id' => $to_tid]);
        $translation->save();
        $updated++;
      }
    }
    if ($updated > 0) {
      $counts["{$from} → {$to}"] = $updated;
    }
  }

  return $counts;
}

/**
 * Clears deprecated field_category on all event nodes.
 */
function motaded_custom_clear_event_field_category(): int {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'event')
    ->exists('field_category')
    ->execute();
  if ($nids === []) {
    return 0;
  }
  $cleared = 0;
  foreach ($node_storage->loadMultiple($nids) as $node) {
    if (!$node instanceof NodeInterface || !$node->hasField('field_category')) {
      continue;
    }
    foreach (array_keys($node->getTranslationLanguages(TRUE)) as $langcode) {
      $translation = $node->getTranslation($langcode);
      if (!$translation->hasField('field_category') || $translation->get('field_category')->isEmpty()) {
        continue;
      }
      $translation->set('field_category', NULL);
      $translation->save();
      $cleared++;
    }
  }
  return $cleared;
}

/**
 * Switches Events view filters from field_category to field_sector_pages.
 */
function motaded_custom_patch_events_view_sector_filter(): void {
  $config = \Drupal::configFactory()->getEditable('views.view.events');
  $displays = $config->get('display');
  if (!is_array($displays)) {
    return;
  }

  $rename_bef_key = static function (array &$bef_filters): void {
    if (!isset($bef_filters['field_category_target_id'])) {
      return;
    }
    $bef_filters['field_sector_pages_target_id'] = $bef_filters['field_category_target_id'];
    unset($bef_filters['field_category_target_id']);
  };

  foreach ($displays as $display_id => &$display) {
    if (!is_array($display)) {
      continue;
    }
    $options = &$display['display_options'];
    if (!is_array($options)) {
      continue;
    }

    if (!empty($options['exposed_form']['options']['bef']['filter']) && is_array($options['exposed_form']['options']['bef']['filter'])) {
      $rename_bef_key($options['exposed_form']['options']['bef']['filter']);
    }

    if (empty($options['filters']['field_category_target_id'])) {
      continue;
    }

    $filter = $options['filters']['field_category_target_id'];
    unset($options['filters']['field_category_target_id']);

    $filter['id'] = 'field_sector_pages_target_id';
    $filter['table'] = 'node__field_sector_pages';
    $filter['field'] = 'field_sector_pages_target_id';
    $filter['plugin_id'] = 'entity_reference';
    unset($filter['vid'], $filter['type'], $filter['hierarchy'], $filter['limit'], $filter['error_message']);
    $filter['handler'] = 'default:node';
    $filter['handler_settings'] = [
      'target_bundles' => ['sector_page' => 'sector_page'],
      'sort' => ['field' => 'title', 'direction' => 'ASC'],
      'auto_create' => FALSE,
      'auto_create_bundle' => '',
    ];

    if (!empty($filter['expose']) && is_array($filter['expose'])) {
      $filter['expose']['identifier'] = 'field_sector_pages_target_id';
      $filter['expose']['operator_id'] = 'field_sector_pages_target_id_op';
      $filter['expose']['operator'] = 'field_sector_pages_target_id_op';
      if (($filter['expose']['label'] ?? '') === 'Industry') {
        $filter['expose']['label'] = 'Sector';
      }
    }

    $options['filters']['field_sector_pages_target_id'] = $filter;
  }
  unset($display);

  $config->set('display', $displays);
  $config->save(TRUE);
}
