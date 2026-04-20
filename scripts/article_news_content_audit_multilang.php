<?php

declare(strict_types=1);

/**
 * Export article + news audit table for EN/AR with content/meta fields.
 *
 * Usage:
 *   ddev drush scr scripts/article_news_content_audit_multilang.php
 *
 * Output:
 *   reports/article_news_content_audit_multilang.csv
 */

use Drupal\node\NodeInterface;

$outPath = DRUPAL_ROOT . '/reports/article_news_content_audit_multilang.csv';
$outDir = dirname($outPath);
if (!is_dir($outDir) && !mkdir($outDir, 0775, TRUE) && !is_dir($outDir)) {
  throw new RuntimeException("Cannot create output directory: {$outDir}");
}

$clean = static function (?string $value): string {
  return trim((string) $value);
};

$fieldValue = static function ($entity, string $fieldName) use ($clean): string {
  if (!$entity || !$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty()) {
    return '';
  }
  return $clean((string) $entity->get($fieldName)->value);
};

$bodySummary = static function ($entity) use ($clean): string {
  if (!$entity || !$entity->hasField('body') || $entity->get('body')->isEmpty()) {
    return '';
  }
  return $clean((string) $entity->get('body')->summary);
};

$termLabels = static function ($entity, string $fieldName): string {
  if (!$entity || !$entity->hasField($fieldName) || $entity->get($fieldName)->isEmpty()) {
    return '';
  }
  $labels = [];
  foreach ($entity->get($fieldName) as $item) {
    if ($item->entity) {
      $labels[] = trim((string) $item->entity->label());
    }
  }
  $labels = array_values(array_filter(array_unique($labels)));
  return implode(' | ', $labels);
};

$metaValue = static function ($entity, string $key): string {
  if (!$entity || !$entity->hasField('field_meta_tags') || $entity->get('field_meta_tags')->isEmpty()) {
    return '';
  }
  $raw = (string) $entity->get('field_meta_tags')->first()->value;
  if ($raw === '') {
    return '';
  }
  $decoded = json_decode($raw, TRUE);
  if (!is_array($decoded)) {
    return '';
  }
  return trim((string) ($decoded[$key] ?? ''));
};

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', ['article', 'news'], 'IN')
  ->condition('status', 1)
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes = $storage->loadMultiple($nids);

$fh = fopen($outPath, 'wb');
if ($fh === FALSE) {
  throw new RuntimeException("Cannot open output CSV: {$outPath}");
}

fputcsv($fh, [
  'nid',
  'type',
  'langcode',
  'title',
  'body_summary',
  'categories',
  'tags',
  'beneficiaries',
  'meta_title',
  'meta_description',
  'meta_abstract',
  'meta_keywords',
  'missing_body_summary',
  'missing_categories',
  'missing_tags',
  'missing_beneficiaries',
  'missing_meta_title',
  'missing_meta_description',
  'missing_meta_abstract',
  'missing_meta_keywords',
  'status',
]);

$rows = 0;
foreach ($nodes as $node) {
  if (!$node instanceof NodeInterface) {
    continue;
  }
  foreach (['en', 'ar'] as $langcode) {
    if (!$node->hasTranslation($langcode)) {
      continue;
    }
    $entity = $node->getTranslation($langcode);

    $summary = $bodySummary($entity);
    $categories = $termLabels($entity, 'field_taxonomy');
    $tags = $termLabels($entity, 'field_tags');
    $beneficiaries = $termLabels($entity, 'field_beneficiaries');

    $metaTitle = $metaValue($entity, 'title');
    $metaDescription = $metaValue($entity, 'description');
    $metaAbstract = $metaValue($entity, 'abstract');
    $metaKeywords = $metaValue($entity, 'keywords');

    fputcsv($fh, [
      (string) $entity->id(),
      $entity->bundle(),
      $langcode,
      trim((string) $entity->label()),
      $summary,
      $categories,
      $tags,
      $beneficiaries,
      $metaTitle,
      $metaDescription,
      $metaAbstract,
      $metaKeywords,
      $summary === '' ? 'Yes' : 'No',
      $categories === '' ? 'Yes' : 'No',
      $tags === '' ? 'Yes' : 'No',
      $beneficiaries === '' ? 'Yes' : 'No',
      $metaTitle === '' ? 'Yes' : 'No',
      $metaDescription === '' ? 'Yes' : 'No',
      $metaAbstract === '' ? 'Yes' : 'No',
      $metaKeywords === '' ? 'Yes' : 'No',
      '',
    ]);
    $rows++;
  }
}

fclose($fh);

print "Rows: {$rows}\n";
print "Output: {$outPath}\n";

