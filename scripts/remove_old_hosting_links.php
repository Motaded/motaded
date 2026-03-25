<?php

/**
 * @file
 * Remove external links pointing to old hosting domains from content.
 *
 * - For HTML fields: unwrap <a> tags whose href points to those domains (keep anchor text).
 * - For URI fields: clear the URI if it points to those domains.
 *
 * Run:
 *   DOMAINS=dimofinf.sa,dimofinf.net DRY_RUN=1 ddev exec "cd /var/www/html && ./vendor/bin/drush scr scripts/remove_old_hosting_links.php"
 *   DOMAINS=dimofinf.sa,dimofinf.net ddev exec "cd /var/www/html && ./vendor/bin/drush scr scripts/remove_old_hosting_links.php"
 *   ddev drush cr
 */

declare(strict_types=1);

$connection = \Drupal::database();
$schema = $connection->schema();

$dry_run = (bool) getenv('DRY_RUN');
$domains_raw = (string) (getenv('DOMAINS') ?: 'dimofinf.sa,dimofinf.net');
$domains = array_values(array_filter(array_map(
  static fn(string $d): string => strtolower(trim($d)),
  explode(',', $domains_raw)
)));

if (empty($domains)) {
  throw new RuntimeException('DOMAINS is empty.');
}

$domain_pattern = '#^(https?:)?//(www\.)?(' . implode('|', array_map(static fn($d) => preg_quote($d, '#'), $domains)) . ')(/|$)#i';

$should_remove_href = static function (string $href) use ($domain_pattern): bool {
  $href = trim($href);
  if ($href === '' || $href[0] === '#') {
    return FALSE;
  }
  return (bool) preg_match($domain_pattern, $href);
};

$remove_links_from_html = static function (string $html) use ($should_remove_href, $domains): string {
  if ($html === '') {
    return $html;
  }
  $hit = FALSE;
  foreach ($domains as $d) {
    if (stripos($html, $d) !== FALSE) {
      $hit = TRUE;
      break;
    }
  }
  if (!$hit) {
    return $html;
  }

  // 1) Remove/unwrap anchors that point to old host domains (keep inner HTML/text).
  $html = (string) preg_replace_callback(
    '/<a\s+([^>]*?)href=(["\'])([^"\']*)\2([^>]*)>([\s\S]*?)<\/a>/iu',
    static function (array $m) use ($should_remove_href): string {
      return $should_remove_href($m[3]) ? $m[5] : $m[0];
    },
    $html
  );

  // 2) Remove <img> tags that load from old host domains (incl. srcset).
  // We remove the whole tag since there is no meaningful fallback text.
  $html = (string) preg_replace_callback(
    '/<img\s+[^>]*?>/iu',
    static function (array $m) use ($should_remove_href): string {
      $tag = $m[0];
      if (preg_match('/\bsrc=(["\'])([^"\']*)\1/iu', $tag, $msrc) && $should_remove_href($msrc[2])) {
        return '';
      }
      if (preg_match('/\bsrcset=(["\'])([^"\']*)\1/iu', $tag, $mset)) {
        // srcset is "url [descriptor], url [descriptor], ..."
        foreach (preg_split('/\s*,\s*/', $mset[2]) as $candidate) {
          $url = trim(preg_split('/\s+/', trim($candidate))[0] ?? '');
          if ($url !== '' && $should_remove_href($url)) {
            return '';
          }
        }
      }
      return $tag;
    },
    $html
  );

  return $html;
};

$clear_uri_if_matches = static function (?string $uri) use ($should_remove_href): ?string {
  if ($uri === NULL || $uri === '') {
    return $uri;
  }
  return $should_remove_href($uri) ? NULL : $uri;
};

// Keep in sync with other content-maintenance scripts.
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

print 'Dry run: ' . ($dry_run ? 'yes' : 'no') . "\n";
print 'Domains: ' . implode(', ', $domains) . "\n";

$total_rows = 0;
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
  $query = $connection->select($table, 't')->fields('t', $fields);
  $or = $query->orConditionGroup();
  foreach ($domains as $d) {
    $or->condition($column, '%' . $connection->escapeLike($d) . '%', 'LIKE');
  }
  $query->condition($or);
  $result = $query->execute();

  foreach ($result as $row) {
    $orig = $row->{$column};
    $new = $is_html
      ? $remove_links_from_html((string) $orig)
      : $clear_uri_if_matches($orig === NULL ? NULL : (string) $orig);

    if ($new !== $orig) {
      if (!$dry_run) {
        $u = $connection->update($table)->fields([$column => $new]);
        foreach ($keys as $k) {
          if (isset($row->{$k})) {
            $u->condition($k, $row->{$k});
          }
        }
        $u->execute();
      }
      $total_rows++;
      print "{$table} ({$column})\n";
    }
  }
}

echo 'Rows ' . ($dry_run ? 'that would be ' : '') . "updated: {$total_rows}\n";
echo "Run 'drush cr' after a real run.\n";

