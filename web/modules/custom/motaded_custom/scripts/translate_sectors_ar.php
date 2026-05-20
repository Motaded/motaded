<?php

/**
 * @file
 * Apply Arabic translations for sector taxonomy + sector_page nodes.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_sectors_ar.php
 *   drush msar
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/** @var array{taxonomy?: array<string, string>, nodes?: array<string, array<string, mixed>>} $DATA */
$DATA = require dirname(__DIR__) . '/data/sectors_ar.dataset.php';
/** @var array<string, array<string, string>> $seo_ar */
$seo_ar = require dirname(__DIR__) . '/data/sector_page.seo.ar.php';

$ensure_term_ar = static function (string $en_name, string $ar_name): bool {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $terms = $storage->loadByProperties(['vid' => 'sector', 'name' => $en_name]);
  if ($terms === []) {
    return FALSE;
  }
  $term = reset($terms);
  if (!$term instanceof TermInterface) {
    return FALSE;
  }
  if (!$term->hasTranslation('ar')) {
    $term->addTranslation('ar', ['name' => $ar_name, 'status' => 1]);
  }
  else {
    $term->getTranslation('ar')->setName($ar_name);
  }
  $term->save();
  return TRUE;
};

$terms_done = 0;
foreach (($DATA['taxonomy'] ?? []) as $en => $ar) {
  if ($ensure_term_ar((string) $en, (string) $ar)) {
    $terms_done++;
  }
}

$apply_sector_metatags = static function (NodeInterface $ar, array $row): void {
  if (!$ar->hasField('field_meta_tags')) {
    return;
  }
  $ar_title = trim((string) ($row['title'] ?? $ar->label()));
  $description = trim((string) ($row['field_short_description'] ?? ''));
  $middle = (string) t('Investment sectors in Saudi Arabia', [], ['langcode' => 'ar']);
  $site_name = (string) \Drupal::config('system.site')->get('name');
  $page_title = $ar_title . ' | ' . $middle . ' | ' . $site_name;

  $data = [];
  $source = $ar->getUntranslated();
  if ($source->hasField('field_meta_tags') && !$source->get('field_meta_tags')->isEmpty()) {
    $raw = (string) $source->get('field_meta_tags')->value;
    if ($raw !== '') {
      $decoded = json_decode($raw, TRUE);
      if (is_array($decoded)) {
        $data = $decoded;
      }
    }
  }

  $meta_title = trim((string) ($row['meta_title'] ?? ''));
  if ($meta_title !== '') {
    $data['title'] = $meta_title;
  }
  else {
    $data['title'] = $page_title;
  }
  if ($description !== '') {
    $data['description'] = $description;
    $data['og_description'] = $description;
    $data['twitter_cards_description'] = $description;
    $data['schema_web_page_description'] = $description;
  }
  $keywords = trim((string) ($row['meta_keywords'] ?? ''));
  if ($keywords !== '') {
    $data['keywords'] = $keywords;
  }
  $data['og_title'] = $ar_title;
  $data['twitter_cards_title'] = $ar_title;
  $data['schema_web_page_name'] = $ar_title;

  $ar->set('field_meta_tags', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
};

$set_formatted = static function (NodeInterface $node, string $field, mixed $value): void {
  if ($value === NULL || $value === '') {
    $node->set($field, []);
    return;
  }
  if (is_array($value)) {
    $node->set($field, [$value]);
    return;
  }
  $node->set($field, [
    'value' => (string) $value,
    'format' => 'basic_html',
  ]);
};

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes_done = 0;
$aliases = 0;
$missing = [];

foreach (($DATA['nodes'] ?? []) as $en_title => $row) {
  if (!is_array($row)) {
    continue;
  }
  $row = array_merge($row, $seo_ar[$en_title] ?? []);
  $nids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'sector_page')
    ->condition('title', (string) $en_title)
    ->range(0, 1)
    ->execute();
  if ($nids === []) {
    $missing[] = (string) $en_title;
    continue;
  }
  $node = $storage->load((int) reset($nids));
  if (!$node instanceof NodeInterface || !$node->hasTranslation('en')) {
    continue;
  }
  $en = $node->getTranslation('en');
  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', $en->toArray());
  }
  $ar = $node->getTranslation('ar');
  $ar->setTitle((string) ($row['title'] ?? $en_title));
  if (isset($row['field_sector_eyebrow'])) {
    $ar->set('field_sector_eyebrow', (string) $row['field_sector_eyebrow']);
  }
  if (isset($row['field_short_description'])) {
    $ar->set('field_short_description', (string) $row['field_short_description']);
  }
  if (array_key_exists('body', $row)) {
    $set_formatted($ar, 'body', $row['body']);
  }
  if (array_key_exists('field_sector_highlights', $row)) {
    $set_formatted($ar, 'field_sector_highlights', $row['field_sector_highlights']);
  }
  if (array_key_exists('field_sector_stats', $row)) {
    $set_formatted($ar, 'field_sector_stats', $row['field_sector_stats']);
  }
  $apply_sector_metatags($ar, $row);
  $ar->setPublished($en->isPublished());
  $node->save();
  if (motaded_custom_sector_page_ar_sync_path_alias($node)) {
    $aliases++;
  }
  $nodes_done++;
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'taxonomy_term_list']);

print sprintf(
  "Sectors AR: taxonomy=%d, sector_page=%d, aliases=%d, missing_nodes=%d\n",
  $terms_done,
  $nodes_done,
  $aliases,
  count($missing),
);

if ($missing !== []) {
  print "Missing sector_page nodes:\n";
  foreach ($missing as $title) {
    print '  - ' . $title . "\n";
  }
}
