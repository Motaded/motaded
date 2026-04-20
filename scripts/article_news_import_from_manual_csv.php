<?php

declare(strict_types=1);

/**
 * Import article/news fields from manual EN/AR CSV tables.
 *
 * Sources:
 * - reports/article_news_content_audit_en_manual.csv
 * - reports/article_news_content_audit_ar_manual.csv
 *
 * Usage:
 *   ddev drush scr scripts/article_news_import_from_manual_csv.php
 *   ddev drush scr scripts/article_news_import_from_manual_csv.php -- --dry-run
 */

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$sources = [
  'en' => DRUPAL_ROOT . '/reports/article_news_content_audit_en_manual.csv',
  'ar' => DRUPAL_ROOT . '/reports/article_news_content_audit_ar_manual.csv',
];
$reportPath = DRUPAL_ROOT . '/reports/article_news_import_report.csv';

foreach ($sources as $lang => $path) {
  if (!is_readable($path)) {
    throw new RuntimeException("CSV not found/readable ({$lang}): {$path}");
  }
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
$buildTermIndex = static function (string $vid, string $langcode) use ($termStorage, $normalize): array {
  $index = [];
  $terms = $termStorage->loadByProperties(['vid' => $vid]);
  foreach ($terms as $term) {
    if (!$term instanceof TermInterface) {
      continue;
    }
    if ($term->hasTranslation($langcode)) {
      $label = $normalize((string) $term->getTranslation($langcode)->label());
      if ($label !== '') {
        $index[$label] = (int) $term->id();
      }
    }
  }
  return $index;
};

$termIndexes = [
  'en' => [
    'taxonomy' => $buildTermIndex('taxonomy', 'en'),
    'beneficiaries' => $buildTermIndex('beneficiaries', 'en'),
    'service_tags' => $buildTermIndex('service_tags', 'en'),
  ],
  'ar' => [
    'taxonomy' => $buildTermIndex('taxonomy', 'ar'),
    'beneficiaries' => $buildTermIndex('beneficiaries', 'ar'),
    'service_tags' => $buildTermIndex('service_tags', 'ar'),
  ],
];

$splitPipe = static function (string $value): array {
  $value = trim($value);
  if ($value === '' || $value === '-') {
    return [];
  }
  $parts = preg_split('/\s*\|\s*/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  return array_values(array_unique(array_map('trim', $parts)));
};

$termRefsFromCsv = static function (string $vid, string $rawValue, array $index, array &$report, int $nid, string $langcode) use ($splitPipe, $normalize): array {
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
      $report[] = [(string) $nid, $langcode, 'missing_term', "{$vid}: {$label}"];
    }
  }
  return $items;
};

$requiredColumns = [
  'nid',
  'type',
  'langcode',
  'body_summary',
  'categories',
  'tags',
  'beneficiaries',
  'meta_title',
  'meta_description',
  'meta_abstract',
  'meta_keywords',
];

$report = [];
$updated = 0;
$processed = 0;

foreach ($sources as $langcode => $csvPath) {
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
    continue;
  }
  $header = array_map(static fn($h) => trim((string) $h), $header);
  $idx = array_flip($header);
  foreach ($requiredColumns as $col) {
    if (!isset($idx[$col])) {
      fclose($fh);
      throw new RuntimeException("Missing required column {$col} in {$csvPath}");
    }
  }

  while (($row = fgetcsv($fh, 0, ',', '"', '\\')) !== FALSE) {
    $processed++;
    $nid = (int) ($row[$idx['nid']] ?? 0);
    $type = trim((string) ($row[$idx['type']] ?? ''));
    $rowLang = trim((string) ($row[$idx['langcode']] ?? ''));
    if ($nid < 1 || !in_array($type, ['article', 'news'], TRUE) || $rowLang !== $langcode) {
      continue;
    }

    $node = $nodeStorage->load($nid);
    if (!$node instanceof NodeInterface) {
      $report[] = [(string) $nid, $langcode, 'skip', 'node_not_found'];
      continue;
    }
    if ($node->bundle() !== $type || !$node->isPublished()) {
      $report[] = [(string) $nid, $langcode, 'skip', 'type_or_status_mismatch'];
      continue;
    }
    if (!$node->hasTranslation($langcode)) {
      $report[] = [(string) $nid, $langcode, 'skip', 'translation_not_found'];
      continue;
    }

    $entity = $node->getTranslation($langcode);
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

    // taxonomy / tags / beneficiaries
    $taxItems = $termRefsFromCsv(
      'taxonomy',
      (string) ($row[$idx['categories']] ?? ''),
      $termIndexes[$langcode]['taxonomy'],
      $report,
      $nid,
      $langcode
    );
    $tagItems = $termRefsFromCsv(
      'service_tags',
      (string) ($row[$idx['tags']] ?? ''),
      $termIndexes[$langcode]['service_tags'],
      $report,
      $nid,
      $langcode
    );
    $beneficiaryItems = $termRefsFromCsv(
      'beneficiaries',
      (string) ($row[$idx['beneficiaries']] ?? ''),
      $termIndexes[$langcode]['beneficiaries'],
      $report,
      $nid,
      $langcode
    );

    if ($entity->hasField('field_taxonomy')) {
      $entity->set('field_taxonomy', $taxItems);
      $dirty = TRUE;
    }
    if ($entity->hasField('field_tags')) {
      $entity->set('field_tags', $tagItems);
      $dirty = TRUE;
    }
    if ($entity->hasField('field_beneficiaries')) {
      $entity->set('field_beneficiaries', $beneficiaryItems);
      $dirty = TRUE;
    }

    // Metatags field differs by bundle: article/news use field_meta, pages use field_meta_tags.
    $metaFieldName = '';
    if ($entity->hasField('field_meta')) {
      $metaFieldName = 'field_meta';
    }
    elseif ($entity->hasField('field_meta_tags')) {
      $metaFieldName = 'field_meta_tags';
    }

    if ($metaFieldName !== '') {
      $meta = [
        'title' => trim((string) ($row[$idx['meta_title']] ?? '')),
        'description' => trim((string) ($row[$idx['meta_description']] ?? '')),
        'abstract' => trim((string) ($row[$idx['meta_abstract']] ?? '')),
        'keywords' => trim((string) ($row[$idx['meta_keywords']] ?? '')),
      ];
      $json = json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
      $currentJson = $entity->get($metaFieldName)->isEmpty()
        ? ''
        : (string) $entity->get($metaFieldName)->first()->value;
      if ($json !== $currentJson) {
        $entity->set($metaFieldName, ['value' => $json]);
        $dirty = TRUE;
      }
    }

    if ($dirty) {
      if ($dryRun) {
        $report[] = [(string) $nid, $langcode, 'dry_run', 'would_save'];
      }
      else {
        $node->setNewRevision(FALSE);
        $node->save();
        $updated++;
        $report[] = [(string) $nid, $langcode, 'saved', 'ok'];
      }
    }
    else {
      $report[] = [(string) $nid, $langcode, 'skip', 'no_changes'];
    }
  }

  fclose($fh);
}

$rfh = fopen($reportPath, 'wb');
if ($rfh === FALSE) {
  throw new RuntimeException("Cannot write report: {$reportPath}");
}
fputcsv($rfh, ['nid', 'langcode', 'action', 'detail'], ',', '"', '\\');
foreach ($report as $line) {
  fputcsv($rfh, $line, ',', '"', '\\');
}
fclose($rfh);

print "Processed rows: {$processed}\n";
print $dryRun ? "DRY RUN (no saves)\n" : "Updated nodes: {$updated}\n";
print "Report: {$reportPath}\n";

