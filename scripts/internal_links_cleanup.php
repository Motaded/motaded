<?php

/**
 * @file
 * Bulk replace internal links: www.motaded.com.sa → motaded.com.sa
 *
 * Updates database fields that may contain hardcoded www URLs so internal
 * links point to the canonical (non-www) domain.
 *
 * Run: drush php:script scripts/internal_links_cleanup.php
 * Or:  drush scr scripts/internal_links_cleanup.php
 */

$replacements = [
  // Standard www → non-www
  'https://www.motaded.com.sa' => 'https://motaded.com.sa',
  'https://www.motaded .com.sa' => 'https://motaded.com.sa',
  'http://www.motaded.com.sa' => 'https://motaded.com.sa',
  'http://motaded.com.sa' => 'https://motaded.com.sa',
  // Malformed (extra spaces in protocol)
  'https ://www.motaded.com.sa' => 'https://motaded.com.sa',
  'https: //www.motaded.com.sa' => 'https://motaded.com.sa',
  'https:/ /www.motaded.com.sa' => 'https://motaded.com.sa',
  'https : //www.motaded.com.sa' => 'https://motaded.com.sa',
  // Wrong domain .com → .com.sa (typo fix)
  'https://www.motaded.com' => 'https://motaded.com.sa',
];

$tables = [
  // Node body & text
  ['node__body', 'body_value'],
  ['node_revision__body', 'body_value'],
  ['node__field_free_consultation', 'field_free_consultation_uri'],
  ['node_revision__field_free_consultation', 'field_free_consultation_uri'],
  // Block content
  ['block_content__body', 'body_value'],
  ['block_content_revision__body', 'body_value'],
  ['block_content__field_description', 'field_description_value'],
  ['block_content_revision__field_description', 'field_description_value'],
  ['block_content__field_text', 'field_text_value'],
  ['block_content_revision__field_text', 'field_text_value'],
  ['block_content__field_textarea', 'field_textarea_value'],
  ['block_content_revision__field_textarea', 'field_textarea_value'],
  ['block_content__field_subtitle', 'field_subtitle_value'],
  ['block_content_revision__field_subtitle', 'field_subtitle_value'],
  ['block_content__field_sub_title', 'field_sub_title_value'],
  ['block_content_revision__field_sub_title', 'field_sub_title_value'],
  ['block_content__field_head_office_second', 'field_head_office_second_value'],
  ['block_content_revision__field_head_office_second', 'field_head_office_second_value'],
  ['block_content__field_link', 'field_link_uri'],
  ['block_content_revision__field_link', 'field_link_uri'],
  ['block_content__field_email_link', 'field_email_link_uri'],
  ['block_content_revision__field_email_link', 'field_email_link_uri'],
  // Paragraphs
  ['paragraph__field_link', 'field_link_uri'],
  ['paragraph_revision__field_link', 'field_link_uri'],
  ['paragraph__field_links', 'field_links_uri'],
  ['paragraph_revision__field_links', 'field_links_uri'],
  ['paragraph__field_body', 'field_body_value'],
  ['paragraph_revision__field_body', 'field_body_value'],
  ['paragraph__field_content', 'field_content_value'],
  ['paragraph_revision__field_content', 'field_content_value'],
  ['paragraph__field_info', 'field_info_value'],
  ['paragraph_revision__field_info', 'field_info_value'],
  ['paragraph__field_sub_title', 'field_sub_title_value'],
  ['paragraph_revision__field_sub_title', 'field_sub_title_value'],
  ['paragraph__field_plan_details', 'field_plan_details_value'],
  ['paragraph_revision__field_plan_details', 'field_plan_details_value'],
  // Taxonomy term description (may contain HTML links)
  ['taxonomy_term_field_data', 'description__value'],
  ['taxonomy_term_field_revision', 'description__value'],
  // Comments
  ['comment__comment_body', 'comment_body_value'],
  ['comment_revision__comment_body', 'comment_body_value'],
  // Redirect module (only redirect target URI)
  ['redirect', 'redirect_redirect__uri'],
  // Menu & shortcut (may have absolute URLs in edge cases)
  ['menu_link_content_data', 'link__uri'],
  ['shortcut_field_data', 'link__uri'],
];

$connection = \Drupal::database();
$total_updated = 0;

foreach ($tables as [$table, $column]) {
  try {
    $schema = $connection->schema();
    if (!$schema->tableExists($table) || !$schema->fieldExists($table, $column)) {
      continue;
    }

    foreach ($replacements as $from => $to) {
      $like_pattern = '%' . $from . '%';
      $sql = "UPDATE {{$table}} SET {$column} = REPLACE({$column}, :from, :to) WHERE {$column} LIKE :like";
      $connection->query($sql, [
        ':from' => $from,
        ':to' => $to,
        ':like' => $like_pattern,
      ]);
      $updated = $connection->query('SELECT ROW_COUNT()')->fetchField();
      $total_updated += (int) $updated;
      if ($updated > 0) {
        echo "{$table}.{$column}: replaced '{$from}' → '{$to}' in {$updated} row(s)\n";
      }
    }
  } catch (\Exception $e) {
    echo "Skip {$table}.{$column}: " . $e->getMessage() . "\n";
  }
}

echo "\nTotal rows updated: {$total_updated}\n";
echo "Run 'drush cr' to clear caches.\n";
