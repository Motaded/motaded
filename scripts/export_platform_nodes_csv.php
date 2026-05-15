<?php

/**
 * @file
 * Export all `platform` nodes to a single UTF-8 CSV (Excel-friendly BOM).
 *
 * Includes: core text, links, taxonomy names, related platform titles (|),
 * metatags (JSON in one cell), paragraphs up to N slots (no media columns).
 *
 * Run (from project root, with DDEV started):
 *   ddev drush php:script scripts/export_platform_nodes_csv.php
 *
 * Довідник допустимих значень (list / taxonomy / related titles):
 *   ddev drush php:script scripts/export_platform_field_options.php
 *   → exports/platform_field_options.csv
 *
 * Файл з’явиться на хості в каталозі проєкту:
 *   exports/platforms_export.csv
 */

declare(strict_types=1);

use Drupal\Component\Serialization\Json;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

const MOTADED_PLATFORM_CSV_LIST_SEP = '|';
const MOTADED_PLATFORM_CSV_PARAGRAPH_SLOTS = 7;
const MOTADED_PLATFORM_CSV_RELATIVE_PATH = 'exports/platforms_export.csv';

/**
 * Normalizes text for a single CSV cell (newlines kept, \r stripped).
 */
function _motaded_export_platform_csv_cell(?string $text): string {
  if ($text === NULL || $text === '') {
    return '';
  }
  return str_replace(["\r\n", "\r"], "\n", $text);
}

/**
 * First link field item as [uri, title].
 *
 * @return array{0: string, 1: string}
 */
function _motaded_export_platform_link_pair(NodeInterface $node, string $field): array {
  if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
    return ['', ''];
  }
  $item = $node->get($field)->first();
  if (!$item) {
    return ['', ''];
  }
  $uri = (string) ($item->uri ?? '');
  $title = (string) ($item->title ?? '');
  return [_motaded_export_platform_csv_cell($uri), _motaded_export_platform_csv_cell($title)];
}

/**
 * Taxonomy term names, same vocabulary order as stored, joined by LIST_SEP.
 */
function _motaded_export_platform_term_names(NodeInterface $node, string $field): string {
  if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
    return '';
  }
  $names = [];
  foreach ($node->get($field) as $item) {
    $tid = (int) ($item->target_id ?? 0);
    if ($tid < 1) {
      continue;
    }
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    if ($term instanceof TermInterface) {
      $names[] = _motaded_export_platform_csv_cell($term->getName());
    }
  }
  return implode(MOTADED_PLATFORM_CSV_LIST_SEP, $names);
}

/**
 * Related platform node titles in default language of each target.
 */
function _motaded_export_platform_related_titles(NodeInterface $node): string {
  if (!$node->hasField('field_related_platforms') || $node->get('field_related_platforms')->isEmpty()) {
    return '';
  }
  $titles = [];
  foreach ($node->get('field_related_platforms') as $item) {
    $nid = (int) ($item->target_id ?? 0);
    if ($nid < 1) {
      continue;
    }
    $related = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    if ($related instanceof NodeInterface && $related->bundle() === 'platform') {
      $titles[] = _motaded_export_platform_csv_cell($related->getTitle());
    }
  }
  return implode(MOTADED_PLATFORM_CSV_LIST_SEP, $titles);
}

/**
 * Metatag field as compact JSON (empty object if none).
 */
function _motaded_export_platform_meta_json(NodeInterface $node): string {
  if (!$node->hasField('field_meta_tags') || $node->get('field_meta_tags')->isEmpty()) {
    return '{}';
  }
  $values = $node->get('field_meta_tags')->getValue();
  return Json::encode($values);
}

/**
 * @return list<ParagraphInterface>
 */
function _motaded_export_platform_paragraph_entities(FieldItemListInterface $list): array {
  $out = [];
  foreach ($list as $item) {
    $p = $item->entity ?? NULL;
    if ($p instanceof ParagraphInterface) {
      $out[] = $p;
    }
  }
  return $out;
}

/**
 * @return array{0: string, 1: string}
 */
function _motaded_export_paragraph_link(ParagraphInterface $p, string $field): array {
  if (!$p->hasField($field) || $p->get($field)->isEmpty()) {
    return ['', ''];
  }
  $item = $p->get($field)->first();
  if (!$item) {
    return ['', ''];
  }
  $uri = (string) ($item->uri ?? '');
  $title = (string) ($item->title ?? '');
  return [_motaded_export_platform_csv_cell($uri), _motaded_export_platform_csv_cell($title)];
}

function _motaded_export_paragraph_text_plain(ParagraphInterface $p, string $field): string {
  if (!$p->hasField($field) || $p->get($field)->isEmpty()) {
    return '';
  }
  $def = $p->get($field)->getFieldDefinition();
  $type = $def->getType();
  if ($type === 'string' || $type === 'string_long') {
    return _motaded_export_platform_csv_cell((string) $p->get($field)->value);
  }
  if ($type === 'text' || $type === 'text_long' || $type === 'text_with_summary') {
    return _motaded_export_platform_csv_cell((string) $p->get($field)->value);
  }
  return '';
}

function _motaded_export_paragraph_icon_value(ParagraphInterface $p, string $field): string {
  if (!$p->hasField($field) || $p->get($field)->isEmpty()) {
    return '';
  }
  return _motaded_export_platform_csv_cell((string) $p->get($field)->value);
}

// --- Header -----------------------------------------------------------------
$slots = MOTADED_PLATFORM_CSV_PARAGRAPH_SLOTS;
$header = [
  'nid',
  'uuid',
  'langcode',
  'status',
  'title',
  'field_subtitle',
  'field_short_description',
  'body',
  'field_featured',
  'field_external_link_uri',
  'field_external_link_title',
  'field_cta_link_uri',
  'field_cta_link_title',
  'field_cta_text',
  'field_source_link_uri',
  'field_source_link_title',
  'field_source_text',
  'field_sector_name',
  'field_category_name',
  'field_region_name',
  'field_related_platforms_titles',
  'field_meta_tags_json',
];
for ($i = 1; $i <= $slots; $i++) {
  $header[] = "why_{$i}_title";
  $header[] = "why_{$i}_body";
  $header[] = "why_{$i}_icon";
}
for ($i = 1; $i <= $slots; $i++) {
  $header[] = "help_{$i}_title";
  $header[] = "help_{$i}_body";
  $header[] = "help_{$i}_icon";
}
for ($i = 1; $i <= $slots; $i++) {
  $header[] = "step_{$i}_title";
  $header[] = "step_{$i}_body";
}
for ($i = 1; $i <= $slots; $i++) {
  $header[] = "req_{$i}_content";
}
for ($i = 1; $i <= $slots; $i++) {
  $header[] = "res_{$i}_title";
  $header[] = "res_{$i}_link_uri";
  $header[] = "res_{$i}_link_title";
  $header[] = "res_{$i}_icon";
}

$repo_root = dirname(__DIR__);
$out_path = $repo_root . '/' . MOTADED_PLATFORM_CSV_RELATIVE_PATH;
$dir = dirname($out_path);
if (!is_dir($dir) && !@mkdir($dir, 0775, TRUE) && !is_dir($dir)) {
  throw new \RuntimeException('Cannot create directory: ' . $dir);
}

$fh = fopen($out_path, 'wb');
if ($fh === FALSE) {
  throw new \RuntimeException('Cannot open for write: ' . $out_path);
}

// UTF-8 BOM for Excel.
fwrite($fh, "\xEF\xBB\xBF");
fputcsv($fh, $header);

$nids = \Drupal::entityQuery('node')
  ->condition('type', 'platform')
  ->accessCheck(FALSE)
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
foreach ($nids as $nid) {
  $node = $storage->load($nid);
  if (!$node instanceof NodeInterface) {
    continue;
  }

  $langcodes = array_keys($node->getTranslationLanguages());
  foreach ($langcodes as $langcode) {
    $n = $node->getTranslation($langcode);

    [$ext_uri, $ext_title] = _motaded_export_platform_link_pair($n, 'field_external_link');
    [$cta_uri, $cta_title] = _motaded_export_platform_link_pair($n, 'field_cta_link');
    [$src_uri, $src_title] = _motaded_export_platform_link_pair($n, 'field_source_link');

    $featured = '0';
    if ($n->hasField('field_featured') && !$n->get('field_featured')->isEmpty()) {
      $featured = ((bool) $n->get('field_featured')->value) ? '1' : '0';
    }

    $body = '';
    if ($n->hasField('body') && !$n->get('body')->isEmpty()) {
      $body = _motaded_export_platform_csv_cell((string) $n->get('body')->value);
    }

    $row = [
      (string) $n->id(),
      $n->uuid(),
      $langcode,
      $n->isPublished() ? '1' : '0',
      _motaded_export_platform_csv_cell($n->getTitle()),
      $n->hasField('field_subtitle') ? _motaded_export_platform_csv_cell((string) $n->get('field_subtitle')->value) : '',
      $n->hasField('field_short_description') ? _motaded_export_platform_csv_cell((string) $n->get('field_short_description')->value) : '',
      $body,
      $featured,
      $ext_uri,
      $ext_title,
      $cta_uri,
      $cta_title,
      $n->hasField('field_cta_text') ? _motaded_export_platform_csv_cell((string) $n->get('field_cta_text')->value) : '',
      $src_uri,
      $src_title,
      $n->hasField('field_source_text') ? _motaded_export_platform_csv_cell((string) $n->get('field_source_text')->value) : '',
      _motaded_export_platform_term_names($n, 'field_sector'),
      _motaded_export_platform_term_names($n, 'field_category'),
      _motaded_export_platform_term_names($n, 'field_region'),
      _motaded_export_platform_related_titles($n),
      _motaded_export_platform_meta_json($n),
    ];

    $why = $n->hasField('field_why_matters') ? _motaded_export_platform_paragraph_entities($n->get('field_why_matters')) : [];
    for ($i = 1; $i <= $slots; $i++) {
      $p = $why[$i - 1] ?? NULL;
      if ($p instanceof ParagraphInterface && $p->bundle() === 'benefit_item') {
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_title');
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_body');
        $row[] = _motaded_export_paragraph_icon_value($p, 'field_icon');
      }
      else {
        $row[] = '';
        $row[] = '';
        $row[] = '';
      }
    }

    $help = $n->hasField('field_how_we_help') ? _motaded_export_platform_paragraph_entities($n->get('field_how_we_help')) : [];
    for ($i = 1; $i <= $slots; $i++) {
      $p = $help[$i - 1] ?? NULL;
      if ($p instanceof ParagraphInterface && $p->bundle() === 'help_item') {
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_title');
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_body');
        $row[] = _motaded_export_paragraph_icon_value($p, 'field_icon');
      }
      else {
        $row[] = '';
        $row[] = '';
        $row[] = '';
      }
    }

    $steps = $n->hasField('field_process_steps') ? _motaded_export_platform_paragraph_entities($n->get('field_process_steps')) : [];
    for ($i = 1; $i <= $slots; $i++) {
      $p = $steps[$i - 1] ?? NULL;
      if ($p instanceof ParagraphInterface && $p->bundle() === 'platform_process_step') {
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_title');
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_body');
      }
      else {
        $row[] = '';
        $row[] = '';
      }
    }

    $reqs = $n->hasField('field_requirements') ? _motaded_export_platform_paragraph_entities($n->get('field_requirements')) : [];
    for ($i = 1; $i <= $slots; $i++) {
      $p = $reqs[$i - 1] ?? NULL;
      if ($p instanceof ParagraphInterface && $p->bundle() === 'requirements') {
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_content');
      }
      else {
        $row[] = '';
      }
    }

    $res = $n->hasField('field_resources') ? _motaded_export_platform_paragraph_entities($n->get('field_resources')) : [];
    for ($i = 1; $i <= $slots; $i++) {
      $p = $res[$i - 1] ?? NULL;
      if ($p instanceof ParagraphInterface && $p->bundle() === 'platform_resource') {
        $row[] = _motaded_export_paragraph_text_plain($p, 'field_title');
        [$u, $t] = _motaded_export_paragraph_link($p, 'field_link');
        $row[] = $u;
        $row[] = $t;
        $row[] = _motaded_export_paragraph_icon_value($p, 'field_icon');
      }
      else {
        $row[] = '';
        $row[] = '';
        $row[] = '';
        $row[] = '';
      }
    }

    fputcsv($fh, $row);
  }
}

fclose($fh);

$rel = MOTADED_PLATFORM_CSV_RELATIVE_PATH;
$abs = $out_path;
$count = is_file($out_path) ? count(file($out_path)) - 1 : 0;
echo "Wrote {$rel} (data rows ≈ {$count}, incl. translations)." . PHP_EOL;
echo "Absolute path: {$abs}" . PHP_EOL;
