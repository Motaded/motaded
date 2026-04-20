<?php

declare(strict_types=1);

/**
 * Import English service fields from reports/services_content_audit_en_manual.csv.
 *
 * Usage:
 *   ddev drush scr scripts/services_en_import_from_manual_csv.php
 *   ddev drush scr scripts/services_en_import_from_manual_csv.php -- --dry-run
 */

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$csvPath = DRUPAL_ROOT . '/reports/services_content_audit_en_manual.csv';
$reportPath = DRUPAL_ROOT . '/reports/services_en_import_report.csv';

if (!is_readable($csvPath)) {
  throw new RuntimeException("CSV not found/readable: {$csvPath}");
}

$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

$normalize = static function (string $value): string {
  $value = trim($value);
  $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
  return mb_strtolower($value);
};

/**
 * @return array<string,int>
 */
$buildTermIndex = static function (string $vid) use ($termStorage, $normalize): array {
  $index = [];
  $terms = $termStorage->loadByProperties(['vid' => $vid]);
  foreach ($terms as $term) {
    if (!$term instanceof TermInterface) {
      continue;
    }
    $tid = (int) $term->id();
    $label = $normalize((string) $term->label());
    if ($label !== '') {
      $index[$label] = $tid;
    }
  }
  return $index;
};

$termIndexes = [
  'taxonomy' => $buildTermIndex('taxonomy'),
  'beneficiaries' => $buildTermIndex('beneficiaries'),
  'service_tags' => $buildTermIndex('service_tags'),
];

$splitPipe = static function (string $value): array {
  $value = trim($value);
  if ($value === '' || $value === '-') {
    return [];
  }
  $parts = preg_split('/\s*\|\s*/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  return array_values(array_unique(array_map('trim', $parts)));
};

$termRefsFromCsv = static function (string $vid, string $rawValue, array $index, array &$report, int $nid) use ($splitPipe, $normalize): array {
  $items = [];
  foreach ($splitPipe($rawValue) as $label) {
    $key = $normalize($label);
    if ($key === '') {
      continue;
    }
    if (isset($index[$key])) {
      $items[] = ['target_id' => $index[$key]];
    }
    else {
      $report[] = [(string) $nid, 'missing_term', "{$vid}: {$label}"];
    }
  }
  return $items;
};

$fh = fopen($csvPath, 'rb');
if ($fh === FALSE) {
  throw new RuntimeException("Cannot open CSV: {$csvPath}");
}
$bom = fread($fh, 3);
if ($bom !== "\xEF\xBB\xBF") {
  rewind($fh);
}

$header = fgetcsv($fh, 0, ',', '"', '\\');
if ($header === FALSE) {
  fclose($fh);
  throw new RuntimeException('CSV is empty.');
}
$header = array_map(static fn($h) => trim((string) $h), $header);
$idx = array_flip($header);

$requiredColumns = [
  'nid',
  'langcode',
  'body_summary',
  'target_audience',
  'service_duration',
  'provided_languages',
  'service_channels',
  'service_cost',
  'payment_options',
  'categories',
  'beneficiaries',
  'tags',
  'meta_title',
  'meta_description',
  'meta_abstract',
  'meta_keywords',
];
foreach ($requiredColumns as $col) {
  if (!isset($idx[$col])) {
    fclose($fh);
    throw new RuntimeException("Missing required column: {$col}");
  }
}

$report = [];
$updated = 0;
$processed = 0;

while (($row = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
  $processed++;
  $nid = (int) ($row[$idx['nid']] ?? 0);
  $langcode = trim((string) ($row[$idx['langcode']] ?? ''));
  if ($nid < 1 || $langcode !== 'en') {
    continue;
  }

  $node = $nodeStorage->load($nid);
  if (!$node instanceof NodeInterface) {
    $report[] = [(string) $nid, 'skip', 'node_not_found'];
    continue;
  }
  if ($node->bundle() !== 'page' || !$node->isPublished()) {
    $report[] = [(string) $nid, 'skip', 'not_published_service_page'];
    continue;
  }
  if (!(bool) $node->get('field_display_on_services')->value) {
    $report[] = [(string) $nid, 'skip', 'field_display_on_services=0'];
    continue;
  }
  if (!$node->hasTranslation('en')) {
    $report[] = [(string) $nid, 'skip', 'no_en_translation'];
    continue;
  }

  $entity = $node->getTranslation('en');
  $dirty = FALSE;

  // body.summary
  $summary = trim((string) ($row[$idx['body_summary']] ?? ''));
  if ($entity->hasField('body') && !$entity->get('body')->isEmpty()) {
    $bodyField = $entity->get('body')->first();
    $currentSummary = trim((string) $bodyField->summary);
    if ($summary !== $currentSummary) {
      $entity->set('body', [
        'value' => $bodyField->value,
        'format' => $bodyField->format ?: 'full_html',
        'summary' => $summary,
      ]);
      $dirty = TRUE;
    }
  }

  $setSimpleField = static function ($entityObj, string $fieldName, string $value) use (&$dirty): void {
    if (!$entityObj->hasField($fieldName)) {
      return;
    }
    $current = trim((string) $entityObj->get($fieldName)->value);
    if ($current !== $value) {
      if ($value === '' || $value === '-') {
        $entityObj->get($fieldName)->setValue([]);
      }
      else {
        $entityObj->set($fieldName, $value);
      }
      $dirty = TRUE;
    }
  };

  $setSimpleField($entity, 'field_target_audience', trim((string) ($row[$idx['target_audience']] ?? '')));
  $setSimpleField($entity, 'field_service_duration', trim((string) ($row[$idx['service_duration']] ?? '')));
  $setSimpleField($entity, 'field_provided_languages', trim((string) ($row[$idx['provided_languages']] ?? '')));
  $setSimpleField($entity, 'field_service_channels', trim((string) ($row[$idx['service_channels']] ?? '')));
  $setSimpleField($entity, 'field_service_cost', trim((string) ($row[$idx['service_cost']] ?? '')));
  $setSimpleField($entity, 'field_payment_options', trim((string) ($row[$idx['payment_options']] ?? '')));

  if ($entity->hasField('field_taxonomy')) {
    $taxItems = $termRefsFromCsv(
      'taxonomy',
      (string) ($row[$idx['categories']] ?? ''),
      $termIndexes['taxonomy'],
      $report,
      $nid
    );
    $entity->set('field_taxonomy', $taxItems);
    $dirty = TRUE;
  }

  if ($entity->hasField('field_beneficiaries')) {
    $beneficiaryItems = $termRefsFromCsv(
      'beneficiaries',
      (string) ($row[$idx['beneficiaries']] ?? ''),
      $termIndexes['beneficiaries'],
      $report,
      $nid
    );
    $entity->set('field_beneficiaries', $beneficiaryItems);
    $dirty = TRUE;
  }

  if ($entity->hasField('field_tags')) {
    $tagItems = $termRefsFromCsv(
      'service_tags',
      (string) ($row[$idx['tags']] ?? ''),
      $termIndexes['service_tags'],
      $report,
      $nid
    );
    $entity->set('field_tags', $tagItems);
    $dirty = TRUE;
  }

  if ($entity->hasField('field_meta_tags')) {
    $meta = [
      'title' => trim((string) ($row[$idx['meta_title']] ?? '')),
      'description' => trim((string) ($row[$idx['meta_description']] ?? '')),
      'abstract' => trim((string) ($row[$idx['meta_abstract']] ?? '')),
      'keywords' => trim((string) ($row[$idx['meta_keywords']] ?? '')),
    ];
    $json = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $currentJson = $entity->get('field_meta_tags')->isEmpty()
      ? ''
      : (string) $entity->get('field_meta_tags')->first()->value;
    if ($json !== $currentJson) {
      $entity->set('field_meta_tags', ['value' => $json]);
      $dirty = TRUE;
    }
  }

  if ($dirty) {
    if ($dryRun) {
      $report[] = [(string) $nid, 'dry_run', 'would_save'];
    }
    else {
      $node->setNewRevision(FALSE);
      $node->save();
      $updated++;
      $report[] = [(string) $nid, 'saved', 'ok'];
    }
  }
  else {
    $report[] = [(string) $nid, 'skip', 'no_changes'];
  }
}
fclose($fh);

$rfh = fopen($reportPath, 'wb');
if ($rfh === FALSE) {
  throw new RuntimeException("Cannot write report: {$reportPath}");
}
fputcsv($rfh, ['nid', 'action', 'detail'], ',', '"', '\\');
foreach ($report as $line) {
  fputcsv($rfh, $line, ',', '"', '\\');
}
fclose($rfh);

print "Processed rows: {$processed}\n";
print $dryRun ? "DRY RUN (no saves)\n" : "Updated nodes: {$updated}\n";
print "Report: {$reportPath}\n";

