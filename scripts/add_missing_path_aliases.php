<?php

/**
 * @file
 * Add missing path aliases for linked nodes.
 * For nodes in menu/paragraphs/blocks: adds fr, es, zh-hans aliases when missing.
 * Uses existing en (or ar) alias as fallback when no translation exists.
 *
 * Run: drush scr scripts/add_missing_path_aliases.php
 */

use Drupal\path_alias\Entity\PathAlias;

$connection = \Drupal::database();
$site_langs = ['en', 'ar', 'es', 'fr', 'zh-hans'];
$default_lang = 'en';

// Linked nodes: menu + paragraph link + block link
$linked = $connection->query("
  SELECT DISTINCT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(link__uri, 'node/', -1), '/', 1) AS UNSIGNED) as nid
  FROM menu_link_content_data WHERE link__uri LIKE 'entity:node/%'
  UNION
  SELECT DISTINCT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(field_link_uri, 'node/', -1), '/', 1) AS UNSIGNED)
  FROM paragraph__field_link WHERE field_link_uri LIKE 'entity:node/%'
  UNION
  SELECT DISTINCT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(field_link_uri, 'node/', -1), '/', 1) AS UNSIGNED)
  FROM block_content__field_link WHERE field_link_uri LIKE 'entity:node/%'
")->fetchCol();
$linked = array_filter(array_map('intval', $linked));

// Existing aliases: path => [lang => alias]
$alias_map = [];
foreach ($connection->query("SELECT path, alias, langcode FROM {path_alias} WHERE path LIKE '/node/%' AND status = 1") as $row) {
  if (preg_match('#^/node/(\d+)$#', $row->path, $m)) {
    $nid = (int) $m[1];
    $alias_map[$nid][$row->langcode] = $row->alias;
  }
}

$storage = \Drupal::entityTypeManager()->getStorage('path_alias');
$created = 0;

foreach ($linked as $nid) {
  $path = '/node/' . $nid;
  $existing = $alias_map[$nid] ?? [];

  foreach ($site_langs as $lang) {
    if (isset($existing[$lang])) {
      continue;
    }

    // Pick alias to use: prefer existing for same lang, else en, else ar, else first
    $alias_to_use = $existing[$lang]
      ?? $existing[$default_lang]
      ?? $existing['ar']
      ?? reset($existing);

    if (!$alias_to_use) {
      continue;
    }

    $alias_to_use = '/' . trim($alias_to_use, '/');

    $entity = $storage->create([
      'path' => $path,
      'alias' => $alias_to_use,
      'langcode' => $lang,
      'status' => 1,
    ]);
    $entity->save();
    $created++;
    echo "Created: {$path} ({$lang}) → {$alias_to_use}\n";
  }
}

echo "\nTotal aliases created: {$created}\n";
echo "Run 'drush cr' to clear caches.\n";
