<?php

/**
 * @file
 * Import Document nodes from documents_40_v2.csv (repository root).
 *
 * Maps CSV columns to fields: category, country, document_type, featured,
 * source_link, platforms (titles, semicolon-separated), year, sector (taxonomy),
 * sector_page (node title), service type (taxonomy vid "taxonomy"), short description.
 *
 * field_document: shared demo PDF (CSV filenames are not resolved to files).
 * Updates nodes matched by title; creates missing ones.
 * Missing sector_page "General" leaves field empty (no hub with that title).
 * Unknown service type labels are created as new terms in vocabulary "taxonomy".
 *
 * Usage: ddev drush php:script scripts/motaded_import_documents.php
 */

declare(strict_types=1);

use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

if (!defined('DRUPAL_ROOT')) {
  throw new \RuntimeException('Run via Drush: ddev drush php:script scripts/motaded_import_documents.php');
}

$csv_path = dirname(__DIR__) . '/documents_40_v2.csv';
if (!is_readable($csv_path)) {
  throw new \RuntimeException('CSV not found: ' . $csv_path);
}

$fh = fopen($csv_path, 'rb');
if ($fh === FALSE) {
  throw new \RuntimeException('Could not open CSV.');
}
$header = fgetcsv($fh);
if ($header === FALSE) {
  fclose($fh);
  throw new \RuntimeException('Empty CSV.');
}
// Normalize header keys (trim BOM / spaces).
$header = array_map(static function ($h) {
  return trim((string) $h, "\xEF\xBB\xBF \t\n\r");
}, $header);

$term_tid_cache = [];

$termId = static function (string $vid, string $name) use (&$term_tid_cache): ?int {
  $name = trim($name);
  if ($name === '') {
    return NULL;
  }
  $key = $vid . '|' . $name;
  if (array_key_exists($key, $term_tid_cache)) {
    return $term_tid_cache[$key];
  }
  $ids = \Drupal::entityQuery('taxonomy_term')
    ->accessCheck(FALSE)
    ->condition('vid', $vid)
    ->condition('name', $name)
    ->condition('langcode', 'en')
    ->range(0, 1)
    ->execute();
  if (!$ids) {
    $ids = \Drupal::entityQuery('taxonomy_term')
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
  }
  $tid = $ids ? (int) reset($ids) : NULL;
  $term_tid_cache[$key] = $tid;
  return $tid;
};

$nodeId = static function (string $bundle, string $title): ?int {
  $title = trim($title);
  if ($title === '') {
    return NULL;
  }
  $ids = \Drupal::entityQuery('node')
    ->accessCheck(FALSE)
    ->condition('type', $bundle)
    ->condition('title', $title)
    ->range(0, 1)
    ->execute();
  return $ids ? (int) reset($ids) : NULL;
};

$document_type_map = [];
foreach (Term::loadMultiple(\Drupal::entityQuery('taxonomy_term')->accessCheck(FALSE)->condition('vid', 'document_type')->execute()) as $term) {
  $document_type_map[$term->label()] = (int) $term->id();
}

// Shared media: one PDF for all rows (CSV file column is not used for binaries).
$pdf_rel = 'themes/custom/motaded_theme/Professional Conduct Charter.pdf';
$pdf_path = DRUPAL_ROOT . '/' . $pdf_rel;
if (!is_readable($pdf_path)) {
  throw new \RuntimeException('Missing PDF at ' . $pdf_path);
}
$binary = file_get_contents($pdf_path);
if ($binary === FALSE) {
  throw new \RuntimeException('Could not read PDF.');
}
/** @var \Drupal\file\FileRepositoryInterface $repo */
$repo = \Drupal::service('file.repository');
$file = $repo->writeData($binary, 'public://motaded-demo-import-document.pdf', FileSystemInterface::EXISTS_REPLACE);

$media_storage = \Drupal::entityTypeManager()->getStorage('media');
$existing_media = $media_storage->loadByProperties([
  'bundle' => 'document',
  'name' => 'Motaded demo import (shared)',
]);
if ($existing_media) {
  $media = reset($existing_media);
  $media->set('field_media_document', $file->id());
  $media->set('status', 1);
  $media->save();
}
else {
  $media = Media::create([
    'bundle' => 'document',
    'uid' => 1,
    'name' => 'Motaded demo import (shared)',
    'field_media_document' => ['target_id' => $file->id()],
    'status' => 1,
  ]);
  $media->save();
}

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$created = 0;
$updated = 0;
$errors = [];

while (($data = fgetcsv($fh)) !== FALSE) {
  if (count($data) < 2 || trim((string) ($data[0] ?? '')) === '') {
    continue;
  }
  $row = [];
  foreach ($header as $i => $col) {
    $row[$col] = $data[$i] ?? '';
  }

  $title = trim((string) ($row['title'] ?? ''));
  if ($title === '') {
    continue;
  }

  $type_label = trim((string) ($row['field_document_type'] ?? ''));
  if ($type_label === '' || !isset($document_type_map[$type_label])) {
    $errors[] = "Unknown document_type \"{$type_label}\" for: {$title}";
    continue;
  }

  $dup = $node_storage->loadByProperties(['type' => 'document', 'title' => $title]);
  $node = $dup ? reset($dup) : Node::create(['type' => 'document', 'title' => $title, 'uid' => 1]);

  $node->set('field_short_description', trim((string) ($row['field_short_description'] ?? '')));
  $node->set('field_document_type', ['target_id' => $document_type_map[$type_label]]);
  $node->set('field_document', ['target_id' => $media->id()]);
  $node->set('status', 1);

  $feat = trim((string) ($row['field_featured'] ?? '0'));
  $node->set('field_featured', (bool) (int) $feat);

  $year = trim((string) ($row['field_year'] ?? ''));
  if ($year !== '' && ctype_digit($year)) {
    $node->set('field_year', (int) $year);
  }

  $link = trim((string) ($row['field_source_link'] ?? ''));
  if ($link !== '') {
    $node->set('field_source_link', [['uri' => $link, 'title' => '']]);
  }
  else {
    $node->set('field_source_link', NULL);
  }

  $cat = trim((string) ($row['field_category'] ?? ''));
  if ($cat !== '') {
    $tid = $termId('document_category', $cat);
    if ($tid) {
      $node->set('field_category', ['target_id' => $tid]);
    }
  }

  $country = trim((string) ($row['field_country'] ?? ''));
  if ($country !== '') {
    $tid = $termId('country', $country);
    if ($tid) {
      $node->set('field_country', ['target_id' => $tid]);
    }
  }

  $sector = trim((string) ($row['field_sector'] ?? ''));
  if ($sector !== '') {
    $tid = $termId('sector', $sector);
    if ($tid) {
      $node->set('field_sector', ['target_id' => $tid]);
    }
  }

  $st = trim((string) ($row['field_taxonomy'] ?? ''));
  if ($st === '') {
    $node->set('field_taxonomy', NULL);
  }
  else {
    $tid = $termId('taxonomy', $st);
    if (!$tid) {
      $term = Term::create([
        'vid' => 'taxonomy',
        'name' => $st,
        'langcode' => 'en',
      ]);
      $term->save();
      $tid = (int) $term->id();
      $term_tid_cache['taxonomy|' . $st] = $tid;
    }
    $node->set('field_taxonomy', ['target_id' => $tid]);
  }

  $sp = trim((string) ($row['field_sector_pages'] ?? ''));
  if ($sp === '') {
    $node->set('field_sector_pages', NULL);
  }
  else {
    $nid = $nodeId('sector_page', $sp);
    if ($nid) {
      $node->set('field_sector_pages', [['target_id' => $nid]]);
    }
    else {
      $node->set('field_sector_pages', NULL);
      $errors[] = "No sector_page titled \"{$sp}\" (cleared field) for: {$title}";
    }
  }

  $plat = trim((string) ($row['field_platforms'] ?? ''));
  if ($plat === '') {
    $node->set('field_platforms', NULL);
  }
  else {
    $targets = [];
    foreach (preg_split('/\s*;\s*/', $plat, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ptitle) {
      $pnid = $nodeId('platform', $ptitle);
      if ($pnid) {
        $targets[] = ['target_id' => $pnid];
      }
      else {
        $errors[] = "No platform titled \"{$ptitle}\" for: {$title}";
      }
    }
    $node->set('field_platforms', $targets ?: NULL);
  }

  $is_new = $node->isNew();
  $node->save();
  if ($is_new) {
    $created++;
  }
  else {
    $updated++;
  }
}
fclose($fh);

\Drupal::logger('motaded_import')->notice('CSV documents: @c created, @u updated, media @m.', [
  '@c' => $created,
  '@u' => $updated,
  '@m' => $media->id(),
]);

print "Created {$created}, updated {$updated}. Shared media ID: {$media->id()}\n";
if ($errors) {
  print "Warnings (" . count($errors) . "):\n" . implode("\n", array_slice($errors, 0, 20)) . (count($errors) > 20 ? "\n..." : '') . "\n";
}
