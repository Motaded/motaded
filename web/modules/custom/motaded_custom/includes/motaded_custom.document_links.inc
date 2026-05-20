<?php

/**
 * @file
 * Document internal links: services (page) and sector_page hubs.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Link rules and limits.
 *
 * @return array{
 *   category_sector_pages: array<string, list<string>>,
 *   sector_term_sector_pages: array<string, list<string>>,
 *   max_services: int,
 *   max_sector_pages: int,
 * }
 */
function motaded_custom_document_links_defaults(): array {
  static $defaults;
  if ($defaults === NULL) {
    $path = dirname(__DIR__) . '/data/document_links.defaults.php';
    $defaults = is_readable($path) ? require $path : [
      'category_sector_pages' => [],
      'sector_term_sector_pages' => [],
      'max_services' => 3,
      'max_sector_pages' => 3,
    ];
  }
  return $defaults;
}

/**
 * Title-keyed overrides.
 *
 * @return array{services: array<string, list<string>>, sector_pages: array<string, list<string>>}
 */
function motaded_custom_document_links_overrides(): array {
  static $overrides;
  if ($overrides === NULL) {
    $path = dirname(__DIR__) . '/data/document_links.overrides.php';
    $loaded = is_readable($path) ? require $path : [];
    $overrides = [
      'services' => $loaded['services'] ?? [],
      'sector_pages' => $loaded['sector_pages'] ?? [],
    ];
  }
  return $overrides;
}

/**
 * Published service pages indexed by service-type term ID and by title.
 *
 * @return array{
 *   by_tid: array<int, list<int>>,
 *   by_title: array<string, int>,
 * }
 */
function motaded_custom_document_service_page_index(): array {
  static $index;
  if ($index !== NULL) {
    return $index;
  }
  $index = ['by_tid' => [], 'by_title' => []];
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'page')
    ->condition('status', 1)
    ->condition('field_display_on_services', 1)
    ->execute();
  foreach ($storage->loadMultiple($nids) as $page) {
    if (!$page instanceof NodeInterface) {
      continue;
    }
    $title = trim($page->label());
    if ($title === '') {
      continue;
    }
    $index['by_title'][$title] = (int) $page->id();
    if ($page->hasField('field_taxonomy') && !$page->get('field_taxonomy')->isEmpty()) {
      $tid = (int) $page->get('field_taxonomy')->target_id;
      $index['by_tid'][$tid][] = (int) $page->id();
    }
  }
  return $index;
}

/**
 * Published sector_page nodes indexed by hub title and sector term ID.
 *
 * @return array{
 *   by_title: array<string, int>,
 *   by_tid: array<int, int>,
 * }
 */
function motaded_custom_document_sector_page_index(): array {
  static $index;
  if ($index !== NULL) {
    return $index;
  }
  $index = ['by_title' => [], 'by_tid' => []];
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'sector_page')
    ->condition('status', 1)
    ->execute();
  foreach ($storage->loadMultiple($nids) as $page) {
    if (!$page instanceof NodeInterface) {
      continue;
    }
    $title = trim($page->label());
    if ($title !== '') {
      $index['by_title'][$title] = (int) $page->id();
    }
    if ($page->hasField('field_sector') && !$page->get('field_sector')->isEmpty()) {
      $tid = (int) $page->get('field_sector')->target_id;
      $index['by_tid'][$tid] = (int) $page->id();
    }
  }
  return $index;
}

/**
 * Resolves node IDs from a list of titles against an index.
 *
 * @param list<string> $titles
 * @param array<string, int> $by_title
 *
 * @return int[]
 */
function motaded_custom_document_resolve_nids_by_titles(array $titles, array $by_title): array {
  $nids = [];
  foreach ($titles as $title) {
    $title = trim($title);
    if ($title === '') {
      continue;
    }
    if (isset($by_title[$title])) {
      $nids[] = $by_title[$title];
      continue;
    }
    foreach ($by_title as $indexed_title => $nid) {
      if (strcasecmp($indexed_title, $title) === 0) {
        $nids[] = $nid;
        break;
      }
    }
  }
  return array_values(array_unique($nids));
}

/**
 * Scores service pages against a document title (higher = more relevant).
 *
 * @param int[] $candidate_nids
 *
 * @return array<int, int>
 */
function motaded_custom_document_score_service_candidates(NodeInterface $document, array $candidate_nids): array {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $haystack = mb_strtolower($document->label());
  $tokens = array_filter(preg_split('/[^a-z0-9]+/i', $haystack) ?: [], static fn (string $t): bool => strlen($t) >= 4);
  $scores = [];
  foreach ($candidate_nids as $nid) {
    $page = $storage->load($nid);
    if (!$page instanceof NodeInterface) {
      continue;
    }
    $needle = mb_strtolower($page->label());
    $score = 0;
    foreach ($tokens as $token) {
      if (str_contains($needle, $token)) {
        $score += 2;
      }
    }
    if (str_contains($haystack, 'vat') && str_contains($needle, 'vat')) {
      $score += 5;
    }
    if (str_contains($haystack, 'zakat') && str_contains($needle, 'zakat')) {
      $score += 5;
    }
    if (str_contains($haystack, 'investment') && str_contains($needle, 'investment')) {
      $score += 4;
    }
    if (str_contains($haystack, 'misa') && str_contains($needle, 'misa')) {
      $score += 4;
    }
    if (str_contains($haystack, 'employment') && (str_contains($needle, 'contract') || str_contains($needle, 'hr'))) {
      $score += 4;
    }
    if (str_contains($haystack, 'company') && (str_contains($needle, 'articles') || str_contains($needle, 'entity'))) {
      $score += 3;
    }
    if (str_contains($haystack, 'commercial') && str_contains($needle, 'commercial')) {
      $score += 3;
    }
    if (str_contains($haystack, 'gosi') && str_contains($needle, 'gosi')) {
      $score += 4;
    }
    if (str_contains($haystack, 'payroll') && str_contains($needle, 'payroll')) {
      $score += 4;
    }
    if (str_contains($haystack, 'audit') && str_contains($needle, 'audit')) {
      $score += 4;
    }
    if ($score > 0) {
      $scores[$nid] = $score;
    }
  }
  arsort($scores);
  return $scores;
}

/**
 * Resolves service page nids for one document translation.
 *
 * @return int[]
 */
function motaded_custom_document_resolve_service_nids(NodeInterface $document): array {
  if ($document->bundle() !== 'document') {
    return [];
  }
  $defaults = motaded_custom_document_links_defaults();
  $max = max(1, (int) ($defaults['max_services'] ?? 3));
  $index = motaded_custom_document_service_page_index();
  $en = $document->hasTranslation('en') ? $document->getTranslation('en') : $document;
  $title = trim($en->label());
  $overrides = motaded_custom_document_links_overrides()['services'][$title] ?? [];
  if ($overrides !== []) {
    return array_slice(motaded_custom_document_resolve_nids_by_titles($overrides, $index['by_title']), 0, $max);
  }

  $candidates = [];
  if ($document->hasField('field_taxonomy') && !$document->get('field_taxonomy')->isEmpty()) {
    $term = $document->get('field_taxonomy')->entity;
    $term_name = $term ? trim($term->label()) : '';
    $tid = (int) $document->get('field_taxonomy')->target_id;
    $candidates = $index['by_tid'][$tid] ?? [];
    if ($candidates === [] && $term_name !== '') {
      $fallback_name = $defaults['service_type_fallback'][$term_name] ?? '';
      if ($fallback_name !== '') {
        $fallback_tid = motaded_custom_service_type_tid_by_name($fallback_name);
        if ($fallback_tid !== NULL) {
          $candidates = $index['by_tid'][$fallback_tid] ?? [];
        }
      }
    }
  }
  if ($candidates === []) {
    return [];
  }

  $scores = motaded_custom_document_score_service_candidates($document, $candidates);
  if ($scores === []) {
    return array_slice($candidates, 0, min($max, count($candidates)));
  }
  return array_slice(array_keys($scores), 0, $max);
}

/**
 * Resolves sector_page nids for one document translation.
 *
 * @return int[]
 */
function motaded_custom_document_resolve_sector_page_nids(NodeInterface $document): array {
  if ($document->bundle() !== 'document') {
    return [];
  }
  require_once __DIR__ . '/motaded_custom.document_sector_pages.inc';

  $defaults = motaded_custom_document_links_defaults();
  $max = max(1, (int) ($defaults['max_sector_pages'] ?? 3));
  $index = motaded_custom_document_sector_page_index();
  $en = $document->hasTranslation('en') ? $document->getTranslation('en') : $document;
  $title = trim($en->label());
  $override_titles = motaded_custom_document_links_overrides()['sector_pages'][$title] ?? [];
  if ($override_titles !== []) {
    return array_slice(motaded_custom_document_resolve_nids_by_titles($override_titles, $index['by_title']), 0, $max);
  }

  $nids = [];
  if ($document->hasField('field_sector') && !$document->get('field_sector')->isEmpty()) {
    $term = $document->get('field_sector')->entity;
    $term_name = $term ? trim($term->label()) : '';
    if ($term_name !== '' && isset($defaults['sector_term_sector_pages'][$term_name])) {
      $nids = motaded_custom_document_resolve_nids_by_titles($defaults['sector_term_sector_pages'][$term_name], $index['by_title']);
    }
    elseif ($term_name === 'General' && $document->hasField('field_category') && !$document->get('field_category')->isEmpty()) {
      $cat = $document->get('field_category')->entity;
      $cat_name = $cat ? trim($cat->label()) : '';
      if ($cat_name !== '' && isset($defaults['category_sector_pages'][$cat_name])) {
        $nids = motaded_custom_document_resolve_nids_by_titles($defaults['category_sector_pages'][$cat_name], $index['by_title']);
      }
    }
    else {
      $hub = motaded_custom_document_sector_page_nids_from_sector($document);
      $nids = $hub;
    }
  }

  return array_slice(array_values(array_unique($nids)), 0, $max);
}

/**
 * Applies field_services on a document translation.
 */
function motaded_custom_document_apply_services(NodeInterface $document, bool $only_if_empty = TRUE): string {
  if ($document->bundle() !== 'document' || !$document->hasField('field_services')) {
    return 'skipped_no_field';
  }
  if ($only_if_empty && !$document->get('field_services')->isEmpty()) {
    return 'skipped_has_services';
  }
  $nids = motaded_custom_document_resolve_service_nids($document);
  if ($nids === []) {
    return 'skipped_no_match';
  }
  $document->set('field_services', array_map(static fn (int $nid): array => ['target_id' => $nid], $nids));
  return 'updated';
}

/**
 * Applies field_sector_pages using hub + category/override rules.
 */
function motaded_custom_document_apply_sector_pages(NodeInterface $document, bool $only_if_empty = TRUE): string {
  if ($document->bundle() !== 'document' || !$document->hasField('field_sector_pages')) {
    return 'skipped_no_field';
  }
  if ($only_if_empty && !$document->get('field_sector_pages')->isEmpty()) {
    return 'skipped_has_pages';
  }
  $nids = motaded_custom_document_resolve_sector_page_nids($document);
  if ($nids === []) {
    if ($document->hasField('field_sector') && $document->get('field_sector')->isEmpty()) {
      return 'skipped_no_sector';
    }
    return 'skipped_no_hub';
  }
  $document->set('field_sector_pages', array_map(static fn (int $nid): array => ['target_id' => $nid], $nids));
  return 'updated';
}

/**
 * Saves services + sector_pages on all translations when changed.
 *
 * @return array{services: string, sector_pages: string}
 */
function motaded_custom_document_save_links_all_translations(NodeInterface $document, bool $only_if_empty = TRUE): array {
  $result = ['services' => 'skipped_no_field', 'sector_pages' => 'skipped_no_field'];
  $services_changed = FALSE;
  $sectors_changed = FALSE;
  foreach (array_keys($document->getTranslationLanguages()) as $langcode) {
    $translation = $document->getTranslation($langcode);
    $svc = motaded_custom_document_apply_services($translation, $only_if_empty);
    if ($svc === 'updated') {
      $services_changed = TRUE;
      $result['services'] = 'updated';
    }
    elseif ($result['services'] !== 'updated') {
      $result['services'] = $svc;
    }
    $sec = motaded_custom_document_apply_sector_pages($translation, $only_if_empty);
    if ($sec === 'updated') {
      $sectors_changed = TRUE;
      $result['sector_pages'] = 'updated';
    }
    elseif ($result['sector_pages'] !== 'updated') {
      $result['sector_pages'] = $sec;
    }
  }
  if ($services_changed || $sectors_changed) {
    $document->save();
  }
  return $result;
}

/**
 * Batch-syncs services and sector_pages for all published documents.
 *
 * @return array<string, int|array<string, int>>
 */
function motaded_custom_document_sync_all_links(bool $only_if_empty = TRUE, bool $replace = FALSE, ?callable $progress = NULL): array {
  if ($replace) {
    $only_if_empty = FALSE;
  }
  $stats = [
    'services_updated' => 0,
    'sector_pages_updated' => 0,
    'skipped_no_service_match' => 0,
    'skipped_no_sector_hub' => 0,
    'unmapped_sectors' => [],
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
    if ($replace) {
      if ($node->hasField('field_services')) {
        $node->set('field_services', NULL);
      }
      if ($node->hasField('field_sector_pages')) {
        $node->set('field_sector_pages', NULL);
      }
    }
    $step = motaded_custom_document_save_links_all_translations($node, $only_if_empty);
    if ($step['services'] === 'updated') {
      $stats['services_updated']++;
    }
    elseif ($step['services'] === 'skipped_no_match') {
      $stats['skipped_no_service_match']++;
    }
    if ($step['sector_pages'] === 'updated') {
      $stats['sector_pages_updated']++;
    }
    elseif ($step['sector_pages'] === 'skipped_no_hub' && $node->hasField('field_sector') && !$node->get('field_sector')->isEmpty()) {
      $stats['skipped_no_sector_hub']++;
      $term = $node->get('field_sector')->entity;
      $label = $term ? $term->label() : (string) $node->get('field_sector')->target_id;
      $stats['unmapped_sectors'][$label] = ($stats['unmapped_sectors'][$label] ?? 0) + 1;
    }
    if ($progress) {
      $progress($node, $step);
    }
  }
  arsort($stats['unmapped_sectors']);
  return $stats;
}
