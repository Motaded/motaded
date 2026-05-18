<?php

/**
 * @file
 * Apply Arabic translations for official event nodes (catalog-driven).
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_events_official_ar.php
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\taxonomy\TermInterface;

$links = (require dirname(__DIR__) . '/data/event_import_official.i18n.php')();
/** @var array<string, string> $about_ar */
$about_ar = (require dirname(__DIR__) . '/data/event_import_official.about.ar.php')(
  $links['library'],
  $links['events_dir'],
  $links['inc_guide'],
  $links['inv_law'],
  $links['sector_tech'],
  $links['sector_energy'],
  $links['sector_health'],
  $links['sector_construction'],
  $links['sector_tourism'],
  $links['sector_logistics'],
  $links['sector_finance'],
  $links['platform_misa'],
  $links['platform_zatca'],
);
/** @var array<string, array<string, mixed>> $overrides */
$overrides = require dirname(__DIR__) . '/data/event_import_official.ar.overrides.php';
/** @var array<string, array<string, string>> $seo_ar */
$seo_ar = require dirname(__DIR__) . '/data/event_import_official.seo.ar.php';
/** @var array<string, array<string, string>> $tax_maps */
$tax_maps = require dirname(__DIR__) . '/data/event_import_official.ar.taxonomy.php';

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

$paragraph_fields = [
  'why_attend' => 'field_why_attend',
  'agenda' => 'field_agenda',
  'what_we_offer' => 'field_what_we_offer',
  'who_should_attend' => 'field_who_should_attend',
];

$clear_paragraph_field = static function (NodeInterface $node, string $field_name): void {
  if (!$node->hasField($field_name)) {
    return;
  }
  foreach ($node->get($field_name)->referencedEntities() as $entity) {
    $entity->delete();
  }
  $node->set($field_name, []);
};

$attach_paragraphs = static function (NodeInterface $node, string $field_name, array $items, string $langcode): void {
  if ($items === []) {
    return;
  }
  foreach ($items as $item) {
    if (!is_array($item)) {
      continue;
    }
    $p_title = trim((string) ($item['title'] ?? ''));
    $p_body = trim((string) ($item['body'] ?? ''));
    if ($p_title === '' && $p_body === '') {
      continue;
    }
    $p = Paragraph::create([
      'type' => 'event_content_item',
      'langcode' => $langcode,
      'field_title' => $p_title,
      'field_body' => [
        'value' => $p_body,
        'format' => 'basic_html',
      ],
    ]);
    $p->save();
    $node->get($field_name)->appendItem([
      'target_id' => (int) $p->id(),
      'target_revision_id' => (int) $p->getRevisionId(),
    ]);
  }
};

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
  ->condition('type', 'event')
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
  $body = $body_key !== '' && isset($about_ar[$body_key]) ? (string) $about_ar[$body_key] : '';
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

  foreach ($paragraph_fields as $key => $field_name) {
    $clear_paragraph_field($ar, $field_name);
  }

  $ar->setTitle((string) ($row['title'] ?? $en_title));
  $ar->set('body', [
    'value' => $normalize_body($body),
    'summary' => trim((string) ($row['body_summary'] ?? '')),
    'format' => 'basic_html',
  ]);

  if (!empty($row['field_location'])) {
    $ar->set('field_location', (string) $row['field_location']);
  }

  if ($ar->hasField('field_cta_link') && !$en->get('field_cta_link')->isEmpty()) {
    $cta = $en->get('field_cta_link')->first();
    if ($cta !== NULL) {
      $uri = $cta->get('uri')->getString();
      $ar->set('field_cta_link', [[
        'uri' => $uri,
        'title' => trim((string) ($row['field_cta_link_title'] ?? $cta->get('title')->getString())),
      ]]);
    }
  }

  if ($ar->hasField('field_official_website') && !$en->get('field_official_website')->isEmpty()) {
    $off = $en->get('field_official_website')->first();
    if ($off !== NULL) {
      $uri = $off->get('uri')->getString();
      $ar->set('field_official_website', [[
        'uri' => $uri,
        'title' => trim((string) ($row['field_official_website_title'] ?? 'الموقع الرسمي')),
      ]]);
    }
  }

  foreach ($paragraph_fields as $key => $field_name) {
    $items = $row[$key] ?? [];
    if (is_array($items)) {
      $attach_paragraphs($ar, $field_name, $items, 'ar');
    }
  }

  if ($ar->hasField('field_meta')) {
    motaded_custom_apply_node_metatags($ar, $row, 'field_meta');
  }

  $ar->setPublished($en->isPublished());
  $node->save();

  if (motaded_custom_event_ar_sync_path_alias($node)) {
    $aliases++;
  }
  $done++;
  \Drupal::logger('motaded')->notice('event official AR: @t', ['@t' => $en_title]);
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'taxonomy_term_list']);
print "Translated {$done} official event node(s).\n";
print "Synced {$aliases} Arabic path alias(es).\n";
if ($missing !== []) {
  print 'Missing or skipped (' . count($missing) . "):\n";
  foreach ($missing as $t) {
    print "  - {$t}\n";
  }
}
