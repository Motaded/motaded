<?php

/**
 * @file
 * Replace hardcoded /node/XXX URLs with canonical aliases.
 * Dynamically discovers node references in nodes, blocks, paragraphs.
 * If node has alias → replace with alias; if broken (deleted) → remove the link.
 *
 * Run: drush scr scripts/replace_node_urls_with_aliases.php
 */

$connection = \Drupal::database();
$schema = $connection->schema();
$base_url = 'https://motaded.com.sa';

// Build alias map: nid => [langcode => alias path]
$alias_map = [];
foreach ($connection->query("SELECT path, alias, langcode FROM {path_alias} WHERE path LIKE '/node/%' AND status = 1") as $row) {
  if (preg_match('#^/node/(\d+)$#', $row->path, $m)) {
    $nid = (int) $m[1];
    $alias_map[$nid][$row->langcode] = $row->alias;
  }
}

$existing_nodes = array_flip(array_map('intval', $connection->query("SELECT nid FROM {node_field_data}")->fetchCol()));
$default_lang = 'en';

$get_alias = function ($nid, $lang = NULL) use ($alias_map, $default_lang) {
  if (!isset($alias_map[$nid])) {
    return NULL;
  }
  $lang = $lang ?: $default_lang;
  return $alias_map[$nid][$lang] ?? $alias_map[$nid][$default_lang] ?? reset($alias_map[$nid]);
};

// Build replacement map (from => to) - only common patterns to keep it fast
$replacements = [];
foreach (array_keys($alias_map) as $nid) {
  $alias = $get_alias($nid);
  if (!$alias) {
    continue;
  }
  foreach (['', '/ar', '/ar/', '/es', '/es/', '/fr', '/fr/', '/zh-hans', '/zh-hans/'] as $lp) {
    $from = $lp ? $lp . '/node/' . $nid : '/node/' . $nid;
    $to = $lp ? ($get_alias($nid, trim($lp, '/')) ?? $alias) : $alias;
    $replacements[$from] = $to;
    if ($lp) {
      $replacements[trim($lp, '/') . '//node/' . $nid] = $to;
    }
  }
  $canonical = 'https://motaded.com.sa';
  $to_en = $canonical . ($get_alias($nid) ?? $alias);
  $to_ar = $canonical . ($get_alias($nid, 'ar') ?? $alias);
  $to_es = $canonical . ($get_alias($nid, 'es') ?? $alias);
  $to_fr = $canonical . ($get_alias($nid, 'fr') ?? $alias);
  $to_zh = $canonical . ($get_alias($nid, 'zh-hans') ?? $alias);

  $http_variants = ['https://', 'http://', 'https://www.', 'http://www.'];
  $host = 'motaded.com.sa';
  $lang_paths = [
    '/' => $to_en,
    '/ar/' => $to_ar, '/ar//' => $to_ar,
    '/es/' => $to_es, '/es//' => $to_es,
    '/fr/' => $to_fr, '/fr//' => $to_fr,
    '/zh-hans/' => $to_zh, '/zh-hans//' => $to_zh,
  ];
  foreach ($http_variants as $prefix) {
    foreach ($lang_paths as $lp => $to) {
      $replacements[$prefix . $host . $lp . 'node/' . $nid] = $to;
    }
  }
  $replacements['internal:/node/' . $nid] = 'internal:' . $alias;
}
uksort($replacements, fn ($a, $b) => strlen($b) - strlen($a));

/**
 * Process text: apply replacements, then strip <a> for broken nodes.
 * For URI fields: broken node → #.
 */
$process = function ($text, $is_html = TRUE) use ($replacements, $existing_nodes) {
  if (empty($text) || strpos($text, 'node/') === FALSE) {
    return $text;
  }
  foreach ($replacements as $from => $to) {
    $text = str_replace($from, $to, $text);
  }
  if (!$is_html) {
    if (preg_match('#node/(\d+)#', $text, $m) && !isset($existing_nodes[(int) $m[1]])) {
      $text = (strpos($text, 'internal:') === 0) ? 'internal:/' : '#';
    }
    return $text;
  }
  return preg_replace_callback(
    '/<a\s+([^>]*)\s*href=(["\'])([^"\']*?node\/(\d+)[^"\']*)\2([^>]*)>([\s\S]*?)<\/a>/isu',
    function ($m) use ($existing_nodes) {
      $nid = (int) $m[4];
      if (isset($existing_nodes[$nid])) {
        return $m[0];
      }
      return $m[6];
    },
    $text
  );
};

$tables = [
  ['node__body', 'body_value', TRUE],
  ['node_revision__body', 'body_value', TRUE],
  ['block_content__body', 'body_value', TRUE],
  ['block_content_revision__body', 'body_value', TRUE],
  ['block_content__field_description', 'field_description_value', TRUE],
  ['block_content_revision__field_description', 'field_description_value', TRUE],
  ['block_content__field_text', 'field_text_value', TRUE],
  ['block_content_revision__field_text', 'field_text_value', TRUE],
  ['block_content__field_textarea', 'field_textarea_value', TRUE],
  ['block_content_revision__field_textarea', 'field_textarea_value', TRUE],
  ['block_content__field_subtitle', 'field_subtitle_value', TRUE],
  ['block_content_revision__field_subtitle', 'field_subtitle_value', TRUE],
  ['block_content__field_sub_title', 'field_sub_title_value', TRUE],
  ['block_content_revision__field_sub_title', 'field_sub_title_value', TRUE],
  ['block_content__field_head_office_second', 'field_head_office_second_value', TRUE],
  ['block_content_revision__field_head_office_second', 'field_head_office_second_value', TRUE],
  ['paragraph__field_body', 'field_body_value', TRUE],
  ['paragraph_revision__field_body', 'field_body_value', TRUE],
  ['paragraph__field_content', 'field_content_value', TRUE],
  ['paragraph_revision__field_content', 'field_content_value', TRUE],
  ['paragraph__field_info', 'field_info_value', TRUE],
  ['paragraph_revision__field_info', 'field_info_value', TRUE],
  ['paragraph__field_sub_title', 'field_sub_title_value', TRUE],
  ['paragraph_revision__field_sub_title', 'field_sub_title_value', TRUE],
  ['paragraph__field_plan_details', 'field_plan_details_value', TRUE],
  ['paragraph_revision__field_plan_details', 'field_plan_details_value', TRUE],
  ['taxonomy_term_field_data', 'description__value', TRUE],
  ['taxonomy_term_field_revision', 'description__value', TRUE],
  ['comment__comment_body', 'comment_body_value', TRUE],
  ['comment_revision__comment_body', 'comment_body_value', TRUE],
  ['node__field_free_consultation', 'field_free_consultation_uri', FALSE],
  ['node_revision__field_free_consultation', 'field_free_consultation_uri', FALSE],
  ['block_content__field_link', 'field_link_uri', FALSE],
  ['block_content_revision__field_link', 'field_link_uri', FALSE],
  ['block_content__field_email_link', 'field_email_link_uri', FALSE],
  ['block_content_revision__field_email_link', 'field_email_link_uri', FALSE],
  ['paragraph__field_link', 'field_link_uri', FALSE],
  ['paragraph_revision__field_link', 'field_link_uri', FALSE],
  ['paragraph__field_links', 'field_links_uri', FALSE],
  ['paragraph_revision__field_links', 'field_links_uri', FALSE],
];

$total = 0;
foreach ($tables as [$table, $column, $is_html]) {
  if (!$schema->tableExists($table) || !$schema->fieldExists($table, $column)) {
    continue;
  }

  if (strpos($table, 'taxonomy_term_field_revision') !== FALSE) {
    $keys = ['revision_id', 'langcode'];
  }
  elseif (strpos($table, 'taxonomy_term') !== FALSE) {
    $keys = ['tid', 'langcode'];
  }
  elseif (strpos($table, 'revision') !== FALSE) {
    $keys = ['entity_id', 'revision_id', 'deleted', 'delta', 'langcode'];
  }
  else {
    $keys = ['entity_id', 'deleted', 'delta', 'langcode'];
  }

  $fields = array_merge($keys, [$column]);
  $result = $connection->select($table, 't')
    ->fields('t', $fields)
    ->condition($column, '%node/%', 'LIKE')
    ->execute();

  foreach ($result as $row) {
    $orig = $row->{$column};
    $new = $process($orig, $is_html);

    if ($new !== $orig) {
      $q = $connection->update($table)->fields([$column => $new]);
      foreach ($keys as $k) {
        if (isset($row->{$k})) {
          $q->condition($k, $row->{$k});
        }
      }
      $q->execute();
      $total++;
    }
  }
}

echo "Total rows updated: {$total}\n";
echo "Run 'drush cr' to clear caches.\n";
