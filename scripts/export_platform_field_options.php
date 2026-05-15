<?php

/**
 * @file
 * Довідник можливих значень для полів platform (і пов’язаних параграфів) — CSV.
 *
 * Включає:
 *   - list_string: спільне сховище paragraph.field_icon (іконки benefit/help/resource)
 *   - boolean: node.platform.field_featured
 *   - taxonomy: усі терміни з vocabulary sector, platform_category, region (усі мови)
 *   - node reference: усі ноди типу platform (nid + заголовок, default language)
 *
 * Запуск (з кореня проєкту, DDEV):
 *   ddev drush php:script scripts/export_platform_field_options.php
 *
 * Вихід: exports/platform_field_options.csv
 */

declare(strict_types=1);

use Drupal\Component\Utility\Html;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

const MOTADED_PLATFORM_OPTIONS_CSV = 'exports/platform_field_options.csv';

/**
 * @param resource $fh
 */
function _motaded_platform_options_write_header($fh): void {
  fputcsv($fh, [
    'option_type',
    'context',
    'vocabulary_or_bundle',
    'machine_value_or_id',
    'label',
    'langcode',
    'notes',
  ]);
}

/**
 * @param resource $fh
 */
function _motaded_platform_options_row(
  $fh,
  string $option_type,
  string $context,
  string $vocab_or_bundle,
  string $machine_or_id,
  string $label,
  string $langcode,
  string $notes,
): void {
  fputcsv($fh, [
    $option_type,
    $context,
    $vocab_or_bundle,
    $machine_or_id,
    $label,
    $langcode,
    $notes,
  ]);
}

$repo_root = dirname(__DIR__);
$out_path = $repo_root . '/' . MOTADED_PLATFORM_OPTIONS_CSV;
$dir = dirname($out_path);
if (!is_dir($dir) && !@mkdir($dir, 0775, TRUE) && !is_dir($dir)) {
  throw new \RuntimeException('Cannot create directory: ' . $dir);
}

$fh = fopen($out_path, 'wb');
if ($fh === FALSE) {
  throw new \RuntimeException('Cannot open for write: ' . $out_path);
}

fwrite($fh, "\xEF\xBB\xBF");
_motaded_platform_options_write_header($fh);

// --- list_string: paragraph.field_icon (одне сховище на benefit_item, help_item, platform_resource)
$icon_storage = FieldStorageConfig::load('paragraph.field_icon');
if ($icon_storage && $icon_storage->getType() === 'list_string') {
  $allowed = $icon_storage->getSetting('allowed_values');
  if (is_array($allowed)) {
    foreach ($allowed as $value => $label) {
      $label_plain = is_string($label) ? Html::decodeEntities($label) : (string) $label;
      _motaded_platform_options_row(
        $fh,
        'list_string',
        'paragraph:benefit_item|help_item|platform_resource:field_icon',
        'paragraph',
        (string) $value,
        $label_plain,
        '',
        'Одне сховище field_icon для трьох типів параграфів; у CSV експорту — колонки *_icon.'
      );
    }
  }
}

// --- boolean: node.field_featured
$feat_storage = FieldStorageConfig::load('node.field_featured');
if ($feat_storage && $feat_storage->getType() === 'boolean') {
  $on = $feat_storage->getSetting('on_label') ?: 'On';
  $off = $feat_storage->getSetting('off_label') ?: 'Off';
  _motaded_platform_options_row($fh, 'boolean', 'node:platform:field_featured', 'node', '0', (string) $off, '', '');
  _motaded_platform_options_row($fh, 'boolean', 'node:platform:field_featured', 'node', '1', (string) $on, '', '');
}

// --- taxonomy: словники з полів platform
$taxonomy_fields = [
  'field_sector' => 'sector',
  'field_category' => 'platform_category',
  'field_region' => 'region',
];
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
foreach ($taxonomy_fields as $field_name => $vid) {
  $terms = $term_storage->loadByProperties(['vid' => $vid]);
  foreach ($terms as $term) {
    if (!$term instanceof TermInterface) {
      continue;
    }
    foreach ($term->getTranslationLanguages() as $langcode => $_lang) {
      $t = $term->getTranslation($langcode);
      $parent_ids = $t->get('parent')->getValue();
      $parent_note = '';
      if ($parent_ids !== []) {
        $pid = (int) ($parent_ids[0]['target_id'] ?? 0);
        if ($pid > 0) {
          $parent = $term_storage->load($pid);
          if ($parent instanceof TermInterface) {
            $parent_note = 'parent_tid=' . $pid . '; parent=' . $parent->label();
          }
        }
      }
      _motaded_platform_options_row(
        $fh,
        'taxonomy_term',
        'node:platform:' . $field_name,
        $vid,
        (string) $t->id(),
        $t->getName(),
        $langcode,
        $parent_note
      );
    }
  }
}

// --- node reference: інші platform (для field_related_platforms)
$nids = \Drupal::entityQuery('node')
  ->condition('type', 'platform')
  ->accessCheck(FALSE)
  ->sort('title')
  ->execute();
$node_storage = \Drupal::entityTypeManager()->getStorage('node');
foreach ($nids as $nid) {
  $node = $node_storage->load($nid);
  if (!$node instanceof NodeInterface) {
    continue;
  }
  $lang = $node->language()->getId();
  _motaded_platform_options_row(
    $fh,
    'node_reference',
    'node:platform:field_related_platforms',
    'platform',
    (string) $node->id(),
    $node->getTitle(),
    $lang,
    'uuid=' . $node->uuid()
  );
}

fclose($fh);

echo 'Wrote ' . MOTADED_PLATFORM_OPTIONS_CSV . PHP_EOL;
echo 'Absolute (host): ' . $out_path . PHP_EOL;
