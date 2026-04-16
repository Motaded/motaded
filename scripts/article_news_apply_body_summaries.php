<?php

/**
 * @file
 * Full overwrite of body.summary for article/news from export CSV (column new_summary filled).
 *
 * Input (default):
 *   data/service-import/article_news_body_export.csv
 *   Required columns: nid, type, new_summary
 *
 * Usage:
 *   ddev drush scr scripts/article_news_apply_body_summaries.php
 *   ddev drush scr scripts/article_news_apply_body_summaries.php -- --dry-run
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$csvPath = DRUPAL_ROOT . '/data/service-import/article_news_body_export.csv';
$reportPath = DRUPAL_ROOT . '/data/service-import/article_news_body_summaries_apply_report.csv';

if (!is_readable($csvPath)) {
  throw new RuntimeException("CSV not found or unreadable: {$csvPath}");
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
foreach (['nid', 'type', 'new_summary'] as $required) {
  if (!isset($idx[$required])) {
    fclose($fh);
    throw new RuntimeException("CSV missing required column: {$required}");
  }
}

$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

$report = [];
$processed = 0;
$updated = 0;

while (($row = fgetcsv($fh)) !== FALSE) {
  $processed++;
  $nid = (int) trim((string) ($row[$idx['nid']] ?? '0'));
  $type = trim((string) ($row[$idx['type']] ?? ''));
  $newSummary = trim((string) ($row[$idx['new_summary']] ?? ''));

  if ($nid < 1 || !in_array($type, ['article', 'news'], TRUE)) {
    $report[] = [$nid, 'skip', 'Invalid nid/type'];
    continue;
  }
  if ($newSummary === '') {
    $report[] = [$nid, 'skip', 'Empty new_summary'];
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
  if (!$node->hasField('body') || $node->get('body')->isEmpty()) {
    $report[] = [$nid, 'skip', 'No body field/value'];
    continue;
  }

  $item = $node->get('body')->first();
  $currentValue = (string) ($item->value ?? '');
  $currentFormat = (string) ($item->format ?? 'basic_html');

  $node->set('body', [[
    'value' => $currentValue,
    'summary' => $newSummary,
    'format' => $currentFormat,
  ]]);

  if (!$dryRun) {
    $node->setNewRevision(FALSE);
    $node->save();
    $updated++;
  }
  $report[] = [$nid, $dryRun ? 'dry_run' : 'updated', $newSummary];
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
