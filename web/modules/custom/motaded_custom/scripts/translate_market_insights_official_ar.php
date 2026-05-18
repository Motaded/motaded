<?php

/**
 * @file
 * Apply Arabic translations for official market_insight nodes (catalog-driven).
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_market_insights_official_ar.php
 *   ddev drush minsight-ar
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

$links = (require dirname(__DIR__) . '/data/market_insight_import_official.i18n.php')();
/** @var array<string, string> $body_ar */
$body_ar = (require dirname(__DIR__) . '/data/market_insight_import_official.body.ar.php')(
  $links['library'],
  $links['insights_dir'],
  $links['sector_tourism'],
  $links['sector_finance'],
  $links['sector_energy'],
  $links['sector_tech'],
  $links['sector_health'],
  $links['sector_construction'],
  $links['sector_logistics'],
  $links['sector_manufacturing'],
  $links['inv_law'],
);
/** @var array<string, array<string, mixed>> $overrides */
$overrides = require dirname(__DIR__) . '/data/market_insight_import_official.ar.overrides.php';
/** @var array<string, array<string, string>> $seo_ar */
$seo_ar = require dirname(__DIR__) . '/data/market_insight_import_official.seo.ar.php';
/** @var array<string, array<string, string>> $tax_maps */
$tax_maps = require dirname(__DIR__) . '/data/market_insight_import_official.ar.taxonomy.php';

$ensure_term_ar = static function (string $vid, string $en_name, string $ar_name): void {
  if ($en_name === '' || $ar_name === '') {
    return;
  }
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $terms = $storage->loadByProperties(['vid' => $vid, 'name' => $en_name]);
  if ($terms === []) {
    return;
  }
  $term = reset($terms);
  if (!$term instanceof TermInterface) {
    return;
  }
  if (!$term->hasTranslation('ar')) {
    $term->addTranslation('ar', ['name' => $ar_name, 'status' => 1]);
  }
  else {
    $term->getTranslation('ar')->setName($ar_name);
  }
  $term->save();
};

foreach ($tax_maps as $vid => $map) {
  if (!is_array($map)) {
    continue;
  }
  foreach ($map as $en => $ar) {
    $ensure_term_ar((string) $vid, (string) $en, (string) $ar);
  }
}

$normalize_body = static function (string $raw): string {
  $raw = trim($raw);
  if ($raw === '') {
    return '';
  }
  if (str_contains($raw, '<') && preg_match('/<[a-z][\s\S]*>/i', $raw)) {
    return $raw;
  }
  return '<p>' . htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
};

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'market_insight')
  ->condition('langcode', 'en')
  ->execute();

$done = 0;
$aliases = 0;
$missing = [];

foreach ($nids as $nid) {
  $node = $storage->load((int) $nid);
  if (!$node instanceof NodeInterface || !$node->hasTranslation('en')) {
    continue;
  }
  $en_title = $node->getTranslation('en')->label();
  if (!isset($overrides[$en_title])) {
    $missing[] = $en_title;
    continue;
  }
  $row = array_merge($overrides[$en_title], $seo_ar[$en_title] ?? []);
  $body_key = (string) ($row['body_key'] ?? '');
  $body = $body_key !== '' && isset($body_ar[$body_key]) ? (string) $body_ar[$body_key] : '';
  if ($body === '') {
    $missing[] = $en_title . ' (empty body)';
    continue;
  }

  $en = $node->getTranslation('en');
  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', [
      'title' => (string) ($row['title'] ?? $en_title),
      'status' => $en->isPublished(),
    ]);
  }
  $ar = $node->getTranslation('ar');

  $ar->setTitle((string) ($row['title'] ?? $en_title));
  $ar->set('body', [
    'value' => $normalize_body($body),
    'summary' => trim((string) ($row['body_summary'] ?? '')),
    'format' => 'basic_html',
  ]);

  if (!empty($row['field_stat_label'])) {
    $ar->set('field_stat_label', (string) $row['field_stat_label']);
  }
  if (!empty($row['field_stat_prefix']) || array_key_exists('field_stat_prefix', $row)) {
    $ar->set('field_stat_prefix', (string) ($row['field_stat_prefix'] ?? $en->get('field_stat_prefix')->value ?? ''));
  }
  if (!empty($row['field_stat_value']) || $en->hasField('field_stat_value')) {
    $ar->set('field_stat_value', (string) ($row['field_stat_value'] ?? $en->get('field_stat_value')->value ?? ''));
  }
  if (!empty($row['field_stat_suffix']) || $en->hasField('field_stat_suffix')) {
    $ar->set('field_stat_suffix', (string) ($row['field_stat_suffix'] ?? $en->get('field_stat_suffix')->value ?? ''));
  }
  if (!empty($row['field_source_text'])) {
    $ar->set('field_source_text', (string) $row['field_source_text']);
  }

  if ($ar->hasField('field_source_link') && !$en->get('field_source_link')->isEmpty()) {
    $link = $en->get('field_source_link')->first();
    if ($link !== NULL) {
      $uri = $link->get('uri')->getString();
      $ar->set('field_source_link', [[
        'uri' => $uri,
        'title' => trim((string) ($row['field_source_link_title'] ?? $link->get('title')->getString() ?: 'المصدر الرسمي')),
      ]]);
    }
  }

  if (!empty($row['field_key_takeaways'])) {
    $ar->set('field_key_takeaways', [
      'value' => $normalize_body((string) $row['field_key_takeaways']),
      'format' => 'basic_html',
    ]);
  }

  if ($ar->hasField('field_meta_tags')) {
    $meta = array_filter([
      'title' => trim((string) ($row['meta_title'] ?? '')),
      'description' => trim((string) ($row['meta_description'] ?? '')),
      'keywords' => trim((string) ($row['meta_keywords'] ?? '')),
    ], static fn (string $v): bool => $v !== '');
    if ($meta !== []) {
      $ar->set('field_meta_tags', json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
  }

  motaded_custom_node_ar_copy_shared_field_values(
    $ar,
    $en,
    motaded_custom_market_insight_ar_shared_field_names(),
  );

  $ar->setPublished($en->isPublished());
  $node->save();

  if (motaded_custom_market_insight_ar_sync_path_alias($node)) {
    $aliases++;
  }
  $done++;
  \Drupal::logger('motaded')->notice('market insight official AR: @t', ['@t' => $en_title]);
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'taxonomy_term_list']);
print "Translated {$done} official market insight node(s).\n";
print "Synced {$aliases} Arabic path alias(es).\n";
if ($missing !== []) {
  print 'Missing or skipped (' . count($missing) . "):\n";
  foreach ($missing as $t) {
    print "  - {$t}\n";
  }
}
