<?php

/**
 * @file
 * Add missing alt attributes to inline images in HTML content.
 *
 * Processes nodes, paragraphs, block_content, comments, and taxonomy terms.
 * Uses filename as fallback for alt (e.g. "image_2.jpeg" → "Image 2").
 *
 * Run: drush scr scripts/add_missing_image_alt.php
 * Dry run: drush scr scripts/add_missing_image_alt.php -- --dry-run
 */

use Drupal\Core\Entity\EntityInterface;

$dry_run = in_array('--dry-run', $GLOBALS['argv'] ?? [], true);

/** @var array Entity type => field names that may contain HTML with images */
$entity_fields = [
  'node' => ['body'],
  'paragraph' => ['field_body', 'field_content', 'field_contents', 'field_info', 'field_plan_details'],
  'block_content' => ['body', 'field_description', 'field_text', 'field_textarea', 'field_subtitle', 'field_sub_title', 'field_head_office_second', 'field_email'],
  'comment' => ['comment_body'],
  'taxonomy_term' => ['description'],
];

/**
 * Generate human-readable alt from filename.
 */
function alt_from_filename(string $src): string {
  $path = parse_url($src, PHP_URL_PATH);
  $filename = $path ? basename($path) : 'image';
  $name = pathinfo($filename, PATHINFO_FILENAME);
  // Replace underscores, hyphens with spaces; collapse multiple spaces.
  $name = preg_replace('/[-_]+/', ' ', $name);
  $name = preg_replace('/\s+/', ' ', trim($name));
  $name = ucwords(strtolower($name));
  return $name ?: 'Image';
}

/**
 * Add alt to img tags that lack it.
 */
function fix_missing_alt(string $html): string {
  if (strpos($html, '<img') === false) {
    return $html;
  }

  $dom = new \DOMDocument();
  $dom->encoding = 'UTF-8';
  libxml_use_internal_errors(TRUE);
  $options = 0;
  if (defined('LIBXML_HTML_NOIMPLICIT') && defined('LIBXML_HTML_NODEFDTD')) {
    $options = LIBXML_HTML_NOIMPLICIT | LIBXML_HTML_NODEFDTD;
  }
  @$dom->loadHTML(
    '<?xml encoding="UTF-8"><html><body><div>' . $html . '</div></body></html>',
    $options
  );
  libxml_clear_errors();

  $changed = false;
  foreach ($dom->getElementsByTagName('img') as $img) {
    $alt = $img->getAttribute('alt');
    if (trim($alt) !== '') {
      continue;
    }
    $src = $img->getAttribute('src');
    $img->setAttribute('alt', alt_from_filename($src));
    $changed = true;
  }

  if (!$changed) {
    return $html;
  }

  $body = $dom->getElementsByTagName('body')->item(0);
  $div = $body->firstChild;
  $result = '';
  foreach ($div->childNodes as $child) {
    $result .= $dom->saveHTML($child);
  }
  return $result;
}

/**
 * Process an entity's text fields.
 */
function process_entity(EntityInterface $entity, array $field_names): int {
  $fixed = 0;
  foreach ($field_names as $field_name) {
    if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      continue;
    }

    $field = $entity->get($field_name);
    $values = $field->getValue();
    $changed = false;

    foreach ($values as $delta => $item_value) {
      $value = $item_value['value'] ?? '';
      if (!is_string($value) || strpos($value, '<img') === false) {
        continue;
      }

      $new_value = fix_missing_alt($value);
      if ($new_value !== $value) {
        $values[$delta]['value'] = $new_value;
        $fixed++;
        $changed = true;
      }
    }

    if ($changed) {
      $entity->set($field_name, $values);
    }
  }
  return $fixed;
}

$total_fixed = 0;
$total_entities = 0;

// Nodes
$node_storage = \Drupal::entityTypeManager()->getStorage('node');
foreach (['article', 'news', 'page', 'entities', 'partner', 'pricing', 'landing_page'] as $type) {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $type)
    ->condition('body', '%<img%', 'LIKE')
    ->execute();
  foreach ($node_storage->loadMultiple($ids) as $node) {
    $n = process_entity($node, $entity_fields['node']);
    if ($n > 0) {
      if (!$dry_run) {
        $node->save();
      }
      $total_fixed += $n;
      $total_entities++;
      $title = $node->getTitle();
      echo "Node {$node->id()} ({$type}): {$title}\n";
    }
  }
}

// Paragraphs
$para_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
$para_ids = $para_storage->getQuery()->accessCheck(FALSE)->execute();
$fields = $entity_fields['paragraph'];
foreach ($para_storage->loadMultiple($para_ids) as $para) {
  $n = process_entity($para, $fields);
  if ($n > 0) {
    if (!$dry_run) {
      $para->save();
    }
    $total_fixed += $n;
    $total_entities++;
    echo "Paragraph {$para->id()} ({$para->bundle()})\n";
  }
}

// Block content
$block_storage = \Drupal::entityTypeManager()->getStorage('block_content');
$block_ids = [];
foreach ($entity_fields['block_content'] as $fn) {
  $ids = $block_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition($fn, '%<img%', 'LIKE')
    ->execute();
  $block_ids = array_merge($block_ids, $ids);
}
$block_ids = array_unique($block_ids);
foreach ($block_storage->loadMultiple($block_ids) as $block) {
  $n = process_entity($block, $entity_fields['block_content']);
  if ($n > 0) {
    if (!$dry_run) {
      $block->save();
    }
    $total_fixed += $n;
    $total_entities++;
    echo "Block {$block->id()} ({$block->bundle()})\n";
  }
}

// Comments
$comment_storage = \Drupal::entityTypeManager()->getStorage('comment');
$comment_ids = $comment_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('comment_body', '%<img%', 'LIKE')
  ->execute();
foreach ($comment_storage->loadMultiple($comment_ids) as $comment) {
  $n = process_entity($comment, $entity_fields['comment']);
  if ($n > 0) {
    if (!$dry_run) {
      $comment->save();
    }
    $total_fixed += $n;
    $total_entities++;
    echo "Comment {$comment->id()}\n";
  }
}

// Taxonomy terms (description is base field)
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$term_ids = $term_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('description.value', '%<img%', 'LIKE')
  ->execute();
foreach ($term_storage->loadMultiple($term_ids) as $term) {
  $n = process_entity($term, $entity_fields['taxonomy_term']);
  if ($n > 0) {
    if (!$dry_run) {
      $term->save();
    }
    $total_fixed += $n;
    $total_entities++;
    echo "Taxonomy term {$term->id()} ({$term->bundle()})\n";
  }
}

echo "\nTotal images fixed: {$total_fixed} in {$total_entities} entities.\n";
if ($dry_run) {
  echo "(Dry run - no changes saved. Run without --dry-run to apply.)\n";
} else {
  echo "Run 'drush cr' to clear caches.\n";
}
