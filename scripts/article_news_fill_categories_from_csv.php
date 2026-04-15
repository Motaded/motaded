<?php

/**
 * @file
 * Fill article/news category field from manual CSV mapping.
 *
 * Usage:
 *   ddev drush scr scripts/article_news_fill_categories_from_csv.php
 *   ddev drush scr scripts/article_news_fill_categories_from_csv.php -- --dry-run
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$csvPath = DRUPAL_ROOT . '/data/service-import/article_news_categories_manual.csv';
$reportPath = DRUPAL_ROOT . '/data/service-import/article_news_categories_import_report.csv';

if (!is_readable($csvPath)) {
  throw new RuntimeException("CSV not found or unreadable: {$csvPath}");
}

$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

$taxonomyTerms = [];
foreach ($termStorage->loadByProperties(['vid' => 'taxonomy']) as $term) {
  $taxonomyTerms[$term->label()] = (int) $term->id();
}

$fh = fopen($csvPath, 'rb');
if ($fh === FALSE) {
  throw new RuntimeException("Cannot open CSV: {$csvPath}");
}

$header = fgetcsv($fh);
if ($header === FALSE) {
  fclose($fh);
  throw new RuntimeException("CSV is empty: {$csvPath}");
}

$header = array_map(static fn($h) => trim((string) $h), $header);
$idx = array_flip($header);
foreach (['nid', 'type', 'category'] as $required) {
  if (!isset($idx[$required])) {
    fclose($fh);
    throw new RuntimeException("CSV missing required column: {$required}");
  }
}

$report = [];
$updated = 0;
$processed = 0;

while (($row = fgetcsv($fh)) !== FALSE) {
  $processed++;
  $nid = (int) trim((string) ($row[$idx['nid']] ?? '0'));
  $type = trim((string) ($row[$idx['type']] ?? ''));
  $category = trim((string) ($row[$idx['category']] ?? ''));

  if ($nid < 1 || ($type !== 'article' && $type !== 'news')) {
    $report[] = [$nid, 'skip', 'Invalid nid/type'];
    continue;
  }
  if ($category === '' || !isset($taxonomyTerms[$category])) {
    $report[] = [$nid, 'skip', "Unknown category: {$category}"];
    continue;
  }

  $node = $nodeStorage->load($nid);
  if (!$node instanceof NodeInterface) {
    $report[] = [$nid, 'skip', 'Node not found'];
    continue;
  }
  if ($node->bundle() !== $type) {
    $report[] = [$nid, 'skip', "Type mismatch ({$node->bundle()} != {$type})"];
    continue;
  }
  if (!$node->isPublished()) {
    $report[] = [$nid, 'skip', 'Node unpublished'];
    continue;
  }

  $targetTid = $taxonomyTerms[$category];
  $current = [];
  foreach ($node->get('field_taxonomy') as $item) {
    $current[] = (int) $item->target_id;
  }
  $already = in_array($targetTid, $current, TRUE) && count($current) === 1;
  if ($already) {
    $report[] = [$nid, 'unchanged', $category];
    continue;
  }

  $node->set('field_taxonomy', [['target_id' => $targetTid]]);
  if (!$dryRun) {
    $node->setNewRevision(FALSE);
    $node->save();
    $updated++;
  }
  $report[] = [$nid, $dryRun ? 'dry_run' : 'updated', $category];
}
fclose($fh);

$rf = fopen($reportPath, 'wb');
if ($rf !== FALSE) {
  fputcsv($rf, ['nid', 'status', 'detail']);
  foreach ($report as $line) {
    fputcsv($rf, $line);
  }
  fclose($rf);
}

print $dryRun ? "DRY RUN\n" : "Updated: {$updated}\n";
print "Processed: {$processed}\n";
print "Report: {$reportPath}\n";
