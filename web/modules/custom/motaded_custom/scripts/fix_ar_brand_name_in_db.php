<?php

/**
 * @file
 * Find / fix wrong Arabic brand spellings stored in the database.
 *
 * Scans: nodes (AR), paragraphs (AR), block content (AR), menu links, taxonomy terms,
 * language.ar config. Includes link fields (button labels: field_link, field_cta_link, …).
 *
 * Correct brand: متعدد (not معتد, موتاد).
 *
 * Run:
 *   drush php:script modules/custom/motaded_custom/scripts/fix_ar_brand_name_in_db.php -- --dry-run
 *   drush php:script modules/custom/motaded_custom/scripts/fix_ar_brand_name_in_db.php -- --fix
 */

declare(strict_types=1);

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\taxonomy\TermInterface;

$dry_run = TRUE;
$fix = FALSE;
if (isset($extra) && is_array($extra)) {
  foreach ($extra as $arg) {
    if ($arg === '--fix') {
      $fix = TRUE;
      $dry_run = FALSE;
    }
    elseif ($arg === '--dry-run') {
      $dry_run = TRUE;
      $fix = FALSE;
    }
  }
}

/**
 * Replaces wrong Arabic brand tokens; leaves «معتدل» (moderate) unchanged.
 */
function motaded_ar_brand_fix_text(string $text): array {
  if ($text === '' || !preg_match('/موتاد|معتد/u', $text)) {
    return [$text, FALSE];
  }
  $fixed = str_replace('موتاد', 'متعدد', $text);
  $fixed = preg_replace('/معتد(?!ل)/u', 'متعدد', $fixed) ?? $fixed;
  return [$fixed, $fixed !== $text];
}

/**
 * @return list<string>
 */
function motaded_ar_brand_find_in_value(mixed $value): array {
  $hits = [];
  if (is_string($value)) {
    if (preg_match('/موتاد|معتد(?!ل)/u', $value)) {
      $hits[] = mb_substr($value, 0, 120);
    }
    return $hits;
  }
  if (!is_array($value)) {
    return $hits;
  }
  foreach ($value as $item) {
    if (is_string($item)) {
      if (preg_match('/موتاد|معتد(?!ل)/u', $item)) {
        $hits[] = mb_substr($item, 0, 120);
      }
    }
    elseif (is_array($item)) {
      foreach ($item as $nested) {
        if (is_string($nested) && preg_match('/موتاد|معتد(?!ل)/u', $nested)) {
          $hits[] = mb_substr($nested, 0, 120);
        }
      }
    }
  }
  return $hits;
}

$entity_type_manager = \Drupal::entityTypeManager();
$entity_field_manager = \Drupal::service('entity_field.manager');
$found = 0;
$fixed = 0;

$process_entity = static function (EntityInterface $entity, string $label) use (
  $entity_field_manager,
  $dry_run,
  $fix,
  &$found,
  &$fixed,
): void {
  if (!$entity instanceof ContentEntityInterface) {
    return;
  }
  $langcodes = array_keys($entity->getTranslationLanguages());
  if (!in_array('ar', $langcodes, TRUE)) {
    return;
  }
  $ar = $entity->getTranslation('ar');
  $bundle_fields = $entity_field_manager->getFieldDefinitions($entity->getEntityTypeId(), $entity->bundle());
  $changed = FALSE;

  foreach ($bundle_fields as $field_name => $definition) {
    if (!$ar->hasField($field_name) || $ar->get($field_name)->isEmpty()) {
      continue;
    }
    $type = $definition->getType();
    if (!in_array($type, [
      'string',
      'string_long',
      'text',
      'text_long',
      'text_with_summary',
      'link',
    ], TRUE)) {
      continue;
    }
    $field = $ar->get($field_name);
    if (!$field instanceof FieldItemListInterface) {
      continue;
    }
    foreach ($field as $delta => $item) {
      $values = $item->getValue();
      $item_changed = FALSE;
      foreach ($values as $key => $value) {
        if (!is_string($value)) {
          continue;
        }
        // Link fields: fix button label (title); uri rarely contains brand name.
        if ($type === 'link' && !in_array($key, ['title', 'uri'], TRUE)) {
          continue;
        }
        [$new, $did] = motaded_ar_brand_fix_text($value);
        if ($did) {
          $found++;
          print sprintf(
            "[%s] %s ar %s[%d].%s\n  was: %s\n",
            $entity->getEntityTypeId(),
            $label,
            $field_name,
            (int) $delta,
            $key,
            mb_substr($value, 0, 100),
          );
          if ($fix) {
            $values[$key] = $new;
            $item_changed = TRUE;
          }
        }
      }
      if ($item_changed) {
        $field->set($delta, $values);
        $changed = TRUE;
      }
    }
  }

  if ($changed && $fix) {
    $ar->save();
    $fixed++;
    print "  -> saved\n";
  }
};

// Nodes (AR translations).
$bundles = ['platform', 'chamber', 'event', 'document', 'market_insight', 'sector_page', 'article', 'news', 'page', 'landing_page'];
foreach ($bundles as $bundle) {
  if (!$entity_type_manager->getStorage('node')->getEntityType()->hasKey('bundle')) {
    continue;
  }
  $nids = $entity_type_manager->getStorage('node')->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $bundle)
    ->condition('langcode', 'ar')
    ->execute();
  foreach ($nids as $nid) {
    $node = $entity_type_manager->getStorage('node')->load((int) $nid);
    if ($node === NULL) {
      continue;
    }
    $process_entity($node, 'node/' . $bundle . ':' . $nid);
  }
}

// Paragraphs (AR) — body/title on help_item, benefit_item, platform_resource, cards, etc.
if ($entity_type_manager->hasDefinition('paragraph')) {
  $pids = $entity_type_manager->getStorage('paragraph')->getQuery()
    ->accessCheck(FALSE)
    ->execute();
  foreach ($pids as $pid) {
    $paragraph = $entity_type_manager->getStorage('paragraph')->load((int) $pid);
    if ($paragraph === NULL || !$paragraph->hasTranslation('ar')) {
      continue;
    }
    $process_entity($paragraph, 'paragraph/' . $paragraph->bundle() . ':' . $pid);
  }
}

// Custom blocks (AR) — global CTA, banners with link buttons.
if ($entity_type_manager->hasDefinition('block_content')) {
  $bids = $entity_type_manager->getStorage('block_content')->getQuery()
    ->accessCheck(FALSE)
    ->execute();
  foreach ($bids as $bid) {
    $block = $entity_type_manager->getStorage('block_content')->load((int) $bid);
    if ($block === NULL || !$block->hasTranslation('ar')) {
      continue;
    }
    $process_entity($block, 'block_content/' . $block->bundle() . ':' . $bid);
  }
}

// Menu links (AR).
if ($entity_type_manager->hasDefinition('menu_link_content')) {
  $ids = $entity_type_manager->getStorage('menu_link_content')->getQuery()
    ->accessCheck(FALSE)
    ->condition('langcode', 'ar')
    ->execute();
  foreach ($ids as $id) {
    $link = $entity_type_manager->getStorage('menu_link_content')->load((int) $id);
    if ($link instanceof MenuLinkContentInterface) {
      $process_entity($link, 'menu_link_content:' . $id);
    }
  }
}

// Taxonomy terms (AR).
$tids = $entity_type_manager->getStorage('taxonomy_term')->getQuery()
  ->accessCheck(FALSE)
  ->condition('langcode', 'ar')
  ->execute();
foreach ($tids as $tid) {
  $term = $entity_type_manager->getStorage('taxonomy_term')->load((int) $tid);
  if ($term instanceof TermInterface) {
    $process_entity($term, 'taxonomy_term:' . $tid);
  }
}

// Config overrides in DB (language.ar.*) — optional scan.
$config_factory = \Drupal::configFactory();
$storage = \Drupal::database();
if ($storage->schema()->tableExists('config')) {
  $rows = $storage->select('config', 'c')
    ->fields('c', ['name', 'data'])
    ->condition('name', 'language.ar.%', 'LIKE')
    ->execute();
  foreach ($rows as $row) {
    $raw = (string) ($row->data ?? '');
    if ($raw === '' || !preg_match('/موتاد|معتد(?!ل)/u', $raw)) {
      continue;
    }
    $decoded = @unserialize($raw, ['allowed_classes' => FALSE]);
    if (!is_array($decoded)) {
      continue;
    }
    $json = json_encode($decoded, JSON_UNESCAPED_UNICODE);
    [$new_json, $did] = motaded_ar_brand_fix_text($json);
    if (!$did) {
      continue;
    }
    $found++;
    print "[config] {$row->name}\n";
    if ($fix) {
      $new_data = json_decode($new_json, TRUE);
      if (is_array($new_data)) {
        $config = $config_factory->getEditable($row->name);
        $config->setData($new_data);
        $config->save();
        $fixed++;
        print "  -> saved\n";
      }
    }
  }
}

$mode = $fix ? 'fix' : 'dry-run';
print sprintf("\nDone (%s): occurrences=%d, entities_saved=%d\n", $mode, $found, $fixed);
if ($dry_run && $found > 0) {
  print "Re-run with --fix to update the database.\n";
}
