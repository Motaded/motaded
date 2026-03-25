<?php

/**
 * @file
 * Remove Google-wrapped internal links from content.
 *
 * For HTML fields: remove <a> wrapper and keep inner text/content.
 * For URI fields: clear URI if it is a Google search wrapping motaded.com.sa.
 *
 * Run: DRY_RUN=1 drush php:script scripts/fix_google_wrapped_internal_links.php
 *      drush php:script scripts/fix_google_wrapped_internal_links.php
 *      drush cr
 */

declare(strict_types=1);

$connection = \Drupal::database();
$schema = $connection->schema();

$dry_run = (bool) getenv('DRY_RUN');
$host_pattern = '#^https?://(www\.)?motaded\.com\.sa(/|$)#i';

/**
 * True if href is a Google search whose q= points to motaded.com.sa.
 */
$is_google_wrapped_motaded_href = static function (string $href) use ($host_pattern): bool {
  if (stripos($href, 'google.') === FALSE || stripos($href, 'search') === FALSE) {
    return FALSE;
  }
  $parts = parse_url($href);
  if (empty($parts['query'])) {
    return FALSE;
  }
  parse_str($parts['query'], $query);
  if (empty($query['q']) || !is_string($query['q'])) {
    return FALSE;
  }
  $q = trim($query['q']);
  return (bool) preg_match($host_pattern, $q);
};

$fix_html = static function (string $html) use ($is_google_wrapped_motaded_href): string {
  if ($html === '' || stripos($html, 'google.') === FALSE) {
    return $html;
  }
  return (string) preg_replace_callback(
    '/<a\s+([^>]*?)href=(["\'])([^"\']*)\2([^>]*)>([\s\S]*?)<\/a>/iu',
    static function (array $m) use ($is_google_wrapped_motaded_href): string {
      return $is_google_wrapped_motaded_href($m[3]) ? $m[5] : $m[0];
    },
    $html
  );
};

$fix_uri = static function (?string $uri) use ($is_google_wrapped_motaded_href): ?string {
  if ($uri === NULL || $uri === '' || stripos($uri, 'google.') === FALSE) {
    return $uri;
  }
  return $is_google_wrapped_motaded_href($uri) ? NULL : $uri;
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
print 'Dry run: ' . ($dry_run ? 'yes' : 'no') . "\n";

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
    ->condition($column, '%google%', 'LIKE')
    ->execute();

  foreach ($result as $row) {
    $orig = $row->{$column};
    $new = $is_html ? $fix_html((string) $orig) : $fix_uri($orig === NULL ? NULL : (string) $orig);

    if ($new !== $orig) {
      if (!$dry_run) {
        $q = $connection->update($table)->fields([$column => $new]);
        foreach ($keys as $k) {
          if (isset($row->{$k})) {
            $q->condition($k, $row->{$k});
          }
        }
        $q->execute();
      }
      $total++;
      print "{$table} ({$column})\n";
    }
  }
}

echo "Rows " . ($dry_run ? 'that would be ' : '') . "updated: {$total}\n";
echo "Run 'drush cr' after a real run.\n";
