<?php

/**
 * @file
 * Import service node fields from versioned CSV under /data/service-import.
 *
 * Sources (DRUPAL_ROOT/data/service-import/):
 * - body_summaries_by_nid.csv              (nid, body_summary)
 * - service_target_audience_beneficiaries.csv (nid, target_audience, beneficiaries)
 * - service_duration_languages_channels.csv   (nid, service_duration, provided_languages, service_channels)
 * - service_cost_payment_options.csv       (nid, service_cost, payment_options)
 * - service_categories_tags.csv            (nid, categories, tags)
 *
 * Usage:
 *   ddev drush scr scripts/services_import_from_csv.php
 *   ddev drush scr scripts/services_import_from_csv.php -- --dry-run
 *   ddev exec bash -lc 'export SERVICES_IMPORT_DRY_RUN=1 && drush scr scripts/services_import_from_csv.php'
 *
 * Rules:
 * - Only published EN page nodes with field_display_on_services = 1.
 * - Overwrites field values with CSV content (intended full replace for these columns).
 * - Taxonomy: categories (vid taxonomy) and beneficiaries (vid beneficiaries) must
 *   already exist (exact name match, default language). Tags (vid service_tags) are
 *   matched by name and created in English if missing.
 * - Cost/payment: a cell of "-" or empty means clear the field (no placeholder).
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = getenv('SERVICES_IMPORT_DRY_RUN') === '1'
  || in_array('--dry-run', $argv, TRUE);
$root = DRUPAL_ROOT;
$dataDir = $root . '/data/service-import';

// Populated during import; referenced by tag auto-create closure.
$report = [];

$files = [
  'summary' => $dataDir . '/body_summaries_by_nid.csv',
  'audience' => $dataDir . '/service_target_audience_beneficiaries.csv',
  'dlc' => $dataDir . '/service_duration_languages_channels.csv',
  'cost' => $dataDir . '/service_cost_payment_options.csv',
  'taxo' => $dataDir . '/service_categories_tags.csv',
];

foreach ($files as $key => $path) {
  if (!is_readable($path)) {
    throw new \RuntimeException("Missing or unreadable CSV: {$path}");
  }
}

/**
 * Read CSV into [ nid => assoc row ].
 *
 * @return array<int, array<string, string>>
 */
$readCsv = static function (string $path, string $nidKey = 'nid'): array {
  $fh = fopen($path, 'rb');
  if ($fh === FALSE) {
    throw new \RuntimeException("Cannot open {$path}");
  }
  $bom = fread($fh, 3);
  if ($bom !== "\xEF\xBB\xBF") {
    rewind($fh);
  }
  $header = fgetcsv($fh);
  if ($header === FALSE) {
    fclose($fh);
    return [];
  }
  $header = array_map(static fn ($h) => trim((string) $h), $header);
  $out = [];
  while (($row = fgetcsv($fh)) !== FALSE) {
    $assoc = [];
    foreach ($header as $i => $col) {
      $assoc[$col] = isset($row[$i]) ? trim((string) $row[$i]) : '';
    }
    if (!isset($assoc[$nidKey]) || $assoc[$nidKey] === '') {
      continue;
    }
    $nid = (int) $assoc[$nidKey];
    if ($nid < 1) {
      continue;
    }
    $out[$nid] = $assoc;
  }
  fclose($fh);
  return $out;
};

$splitPipe = static function (string $value): array {
  if ($value === '' || $value === '-') {
    return [];
  }
  $parts = preg_split('/\s*\|\s*/', $value, -1, PREG_SPLIT_NO_EMPTY);
  return $parts ? array_map('trim', $parts) : [];
};

$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

$resolveTerm = static function (string $vid, string $name) use ($termStorage): ?int {
  $name = trim($name);
  if ($name === '') {
    return NULL;
  }
  $terms = $termStorage->loadByProperties([
    'vid' => $vid,
    'name' => $name,
  ]);
  $term = $terms ? reset($terms) : FALSE;
  return $term instanceof TermInterface ? (int) $term->id() : NULL;
};

$resolveOrCreateTag = static function (string $name) use ($termStorage, $dryRun, &$report): int {
  $name = trim($name);
  if ($name === '') {
    throw new \InvalidArgumentException('Empty tag name');
  }
  $tid = NULL;
  $terms = $termStorage->loadByProperties([
    'vid' => 'service_tags',
    'name' => $name,
  ]);
  $term = $terms ? reset($terms) : FALSE;
  if ($term instanceof TermInterface) {
    return (int) $term->id();
  }
  if ($dryRun) {
    $report[] = ['', 'tag_would_create', "service_tags: {$name}"];
    return -1;
  }
  $new = $termStorage->create([
    'vid' => 'service_tags',
    'name' => $name,
    'langcode' => 'en',
  ]);
  $new->save();
  $report[] = ['', 'tag_created', $name . ' (tid ' . $new->id() . ')'];
  return (int) $new->id();
};

$dataSummary = $readCsv($files['summary']);
$dataAudience = $readCsv($files['audience']);
$dataDlc = $readCsv($files['dlc']);
$dataCost = $readCsv($files['cost']);
$dataTaxo = $readCsv($files['taxo']);

$nids = array_unique(array_merge(
  array_keys($dataSummary),
  array_keys($dataAudience),
  array_keys($dataDlc),
  array_keys($dataCost),
  array_keys($dataTaxo)
));
sort($nids, SORT_NUMERIC);

$reportPath = $dataDir . '/last_import_report.csv';
$reportFh = fopen($reportPath, 'wb');
if ($reportFh === FALSE) {
  throw new \RuntimeException('Cannot write report');
}
fputcsv($reportFh, ['nid', 'action', 'detail']);

$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
$updated = 0;

foreach ($nids as $nid) {
  $node = $nodeStorage->load($nid);
  if (!$node instanceof NodeInterface) {
    $report[] = [(string) $nid, 'skip', 'Node not found'];
    continue;
  }
  if ($node->bundle() !== 'page' || !$node->isPublished()) {
    $report[] = [(string) $nid, 'skip', 'Not a published page'];
    continue;
  }
  if (!(bool) $node->get('field_display_on_services')->value) {
    $report[] = [(string) $nid, 'skip', 'Not a service page'];
    continue;
  }
  if (!$node->hasTranslation('en')) {
    $report[] = [(string) $nid, 'skip', 'No EN translation'];
    continue;
  }

  $entity = $node->getTranslation('en');
  $dirty = FALSE;

  // body.summary
  if (isset($dataSummary[$nid]['body_summary']) && $dataSummary[$nid]['body_summary'] !== '') {
    $summary = $dataSummary[$nid]['body_summary'];
    $bodyField = $entity->get('body');
    if (!$bodyField->isEmpty()) {
      $entity->set('body', [
        'value' => $bodyField->value,
        'summary' => $summary,
        'format' => $bodyField->format ?: 'full_html',
      ]);
      $dirty = TRUE;
      $report[] = [(string) $nid, 'body.summary', 'set'];
    }
    else {
      $report[] = [(string) $nid, 'body.summary', 'skipped (empty body)'];
    }
  }

  // target_audience
  if (isset($dataAudience[$nid]['target_audience'])) {
    $entity->set('field_target_audience', $dataAudience[$nid]['target_audience']);
    $dirty = TRUE;
    $report[] = [(string) $nid, 'field_target_audience', 'set'];
  }

  // beneficiaries
  if (isset($dataAudience[$nid]['beneficiaries'])) {
    $tids = [];
    foreach ($splitPipe($dataAudience[$nid]['beneficiaries']) as $label) {
      $tid = $resolveTerm('beneficiaries', $label);
      if ($tid !== NULL) {
        $tids[] = ['target_id' => $tid];
      }
      else {
        $report[] = [(string) $nid, 'beneficiary_missing', $label];
      }
    }
    $entity->set('field_beneficiaries', $tids);
    $dirty = TRUE;
    $report[] = [(string) $nid, 'field_beneficiaries', 'set (' . count($tids) . ' terms)'];
  }

  // duration / languages / channels
  if (isset($dataDlc[$nid])) {
    $row = $dataDlc[$nid];
    foreach (['service_duration' => 'field_service_duration', 'provided_languages' => 'field_provided_languages', 'service_channels' => 'field_service_channels'] as $col => $field) {
      if (array_key_exists($col, $row)) {
        $entity->set($field, $row[$col]);
        $dirty = TRUE;
      }
    }
    $report[] = [(string) $nid, 'sidebar_strings', 'duration/languages/channels'];
  }

  // cost / payment ( "-" or blank = leave field empty, do not store a dash )
  if (isset($dataCost[$nid])) {
    $row = $dataCost[$nid];
    foreach (['service_cost' => 'field_service_cost', 'payment_options' => 'field_payment_options'] as $col => $fieldName) {
      if (!array_key_exists($col, $row)) {
        continue;
      }
      $val = trim((string) $row[$col]);
      if ($val === '' || $val === '-') {
        $entity->get($fieldName)->setValue([]);
      }
      else {
        $entity->set($fieldName, $val);
      }
      $dirty = TRUE;
    }
    $report[] = [(string) $nid, 'cost_payment', 'set'];
  }

  // categories + tags
  if (isset($dataTaxo[$nid])) {
    $row = $dataTaxo[$nid];
    $catItems = [];
    foreach ($splitPipe($row['categories'] ?? '') as $label) {
      $tid = $resolveTerm('taxonomy', $label);
      if ($tid !== NULL) {
        $catItems[] = ['target_id' => $tid];
      }
      else {
        $report[] = [(string) $nid, 'category_missing', $label];
      }
    }
    $entity->set('field_taxonomy', $catItems);
    $dirty = TRUE;

    $tagItems = [];
    foreach ($splitPipe($row['tags'] ?? '') as $label) {
      $tid = $resolveTerm('service_tags', $label);
      if ($tid === NULL) {
        if ($dryRun) {
          $report[] = [(string) $nid, 'tag_would_create', $label];
          continue;
        }
        $tid = $resolveOrCreateTag($label);
      }
      if ($tid !== NULL && $tid > 0) {
        $tagItems[] = ['target_id' => $tid];
      }
    }
    $entity->set('field_tags', $tagItems);
    $report[] = [(string) $nid, 'field_taxonomy', 'set (' . count($catItems) . ')'];
    $report[] = [(string) $nid, 'field_tags', 'set (' . count($tagItems) . ')'];
  }

  if ($dirty && !$dryRun) {
    $node->setNewRevision(FALSE);
    $node->save();
    $updated++;
    $report[] = [(string) $nid, 'saved', 'ok'];
  }
  elseif ($dryRun && $dirty) {
    $report[] = [(string) $nid, 'dry_run', 'would save'];
  }
}

foreach ($report as $line) {
  fputcsv($reportFh, $line);
}
fclose($reportFh);

print $dryRun ? "DRY RUN — no saves.\n" : "Updated nodes: {$updated}\n";
print "Report: {$reportPath}\n";
