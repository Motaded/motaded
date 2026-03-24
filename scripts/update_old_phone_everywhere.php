<?php

declare(strict_types=1);

use Drupal\block_content\Entity\BlockContent;
use Drupal\node\Entity\Node;

// Usage:
//   drush --uri=https://motaded.com.sa php:script scripts/update_old_phone_everywhere.php
//
// Optional env overrides:
//   OLD_PHONE=+966112909900
//   NEW_PHONE=+966539797197
//   BLOCK_IDS=23,45
//   DRY_RUN=1

$old_phone = getenv('OLD_PHONE') ?: '+966112909900';
$new_phone = getenv('NEW_PHONE') ?: '+966539797197';
$block_ids_raw = getenv('BLOCK_IDS') ?: '23';
$dry_run = (bool) getenv('DRY_RUN');

$old_no_plus = ltrim($old_phone, '+');
$old_formatted = '+966 11 290 9900';
$wa_old = 'wa.me/' . $old_no_plus;
$wa_new = 'wa.me/' . ltrim($new_phone, '+');

$block_ids = array_values(array_filter(array_map(
  static fn(string $id): int => (int) trim($id),
  explode(',', $block_ids_raw)
)));

if ($old_phone === $new_phone) {
  throw new RuntimeException('OLD_PHONE and NEW_PHONE are the same.');
}

print "Old phone: {$old_phone}\n";
print "New phone: {$new_phone}\n";
print "Dry run: " . ($dry_run ? 'yes' : 'no') . "\n";

// 1) Update node field_phone_number values.
$connection = \Drupal::database();
$node_ids = $connection->select('node__field_phone_number', 'p')
  ->fields('p', ['entity_id'])
  ->condition('field_phone_number_value', $old_phone)
  ->distinct()
  ->execute()
  ->fetchCol();

$updated_nodes = 0;
foreach ($node_ids as $nid) {
  $node = Node::load((int) $nid);
  if (!$node || !$node->hasField('field_phone_number')) {
    continue;
  }
  if (!$dry_run) {
    $node->set('field_phone_number', $new_phone);
    $node->save();
  }
  $updated_nodes++;
}

// 2) Update old phone references in selected custom block bodies.
$updated_blocks = 0;
$updated_block_translations = 0;
foreach ($block_ids as $block_id) {
  if (!$block_id) {
    continue;
  }

  $block = BlockContent::load($block_id);
  if (!$block || !$block->hasField('body')) {
    continue;
  }

  $block_changed = FALSE;
  foreach (array_keys($block->getTranslationLanguages()) as $langcode) {
    $translation = $block->getTranslation($langcode);
    $current_body = (string) $translation->get('body')->value;
    if ($current_body === '') {
      continue;
    }

    $updated_body = str_replace(
      [$old_phone, $old_no_plus, $old_formatted, $wa_old],
      [$new_phone, ltrim($new_phone, '+'), $new_phone, $wa_new],
      $current_body
    );

    if ($updated_body !== $current_body) {
      if (!$dry_run) {
        $body_item = $translation->get('body')->first();
        $body_item->value = $updated_body;
        $translation->save();
      }
      $updated_block_translations++;
      $block_changed = TRUE;
      print "Updated block {$block_id} ({$langcode})\n";
    }
  }

  if ($block_changed) {
    $updated_blocks++;
  }
}

print "Updated nodes: {$updated_nodes}\n";
print "Updated blocks: {$updated_blocks}\n";
print "Updated block translations: {$updated_block_translations}\n";
print "Done.\n";
