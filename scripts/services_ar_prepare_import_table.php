<?php

/**
 * @file
 * Prepare AR services import table with prefilled values.
 *
 * Strategy:
 * - Scope: published service pages (field_display_on_services = 1).
 * - For each field, take AR value first.
 * - If AR value is empty, fallback to EN value.
 *
 * Output:
 *   data/service-import/services_ar_import_table.csv
 *
 * Usage:
 *   drush scr scripts/services_ar_prepare_import_table.php
 */

declare(strict_types=1);

$outPath = DRUPAL_ROOT . '/data/service-import/services_ar_import_table.csv';
$outDir = dirname($outPath);
if (!is_dir($outDir) && !mkdir($outDir, 0775, TRUE) && !is_dir($outDir)) {
  throw new RuntimeException("Cannot create output directory: {$outDir}");
}

$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', 'page')
  ->condition('status', 1)
  ->condition('field_display_on_services.value', 1)
  ->sort('nid')
  ->execute();

$nodes = $nodeStorage->loadMultiple($nids);

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

$fh = fopen($outPath, 'wb');
if ($fh === FALSE) {
  throw new RuntimeException("Cannot open output CSV: {$outPath}");
}

fputcsv($fh, [
  'nid',
  'title_ar',
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
  'source_note',
]);

$rows = 0;
foreach ($nodes as $node) {
  $ar = $node->hasTranslation('ar') ? $node->getTranslation('ar') : NULL;
  $en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

  $titleAr = $ar ? trim((string) $ar->label()) : '';

  $getPrefilled = static function (string $arValue, string $enValue): array {
    if ($arValue !== '') {
      return [$arValue, 'ar'];
    }
    if ($enValue !== '') {
      return [$enValue, 'en_fallback'];
    }
    return ['', 'empty'];
  };

  [$summary, $summarySrc] = $getPrefilled(
    $bodySummary($ar),
    $bodySummary($en)
  );
  [$audience, $audienceSrc] = $getPrefilled(
    $fieldValue($ar, 'field_target_audience'),
    $fieldValue($en, 'field_target_audience')
  );
  [$duration, $durationSrc] = $getPrefilled(
    $fieldValue($ar, 'field_service_duration'),
    $fieldValue($en, 'field_service_duration')
  );
  [$langs, $langsSrc] = $getPrefilled(
    $fieldValue($ar, 'field_provided_languages'),
    $fieldValue($en, 'field_provided_languages')
  );
  [$channels, $channelsSrc] = $getPrefilled(
    $fieldValue($ar, 'field_service_channels'),
    $fieldValue($en, 'field_service_channels')
  );
  [$cost, $costSrc] = $getPrefilled(
    $fieldValue($ar, 'field_service_cost'),
    $fieldValue($en, 'field_service_cost')
  );
  [$payment, $paymentSrc] = $getPrefilled(
    $fieldValue($ar, 'field_payment_options'),
    $fieldValue($en, 'field_payment_options')
  );
  [$categories, $catSrc] = $getPrefilled(
    $termLabels($ar, 'field_taxonomy'),
    $termLabels($en, 'field_taxonomy')
  );
  [$beneficiaries, $benSrc] = $getPrefilled(
    $termLabels($ar, 'field_beneficiaries'),
    $termLabels($en, 'field_beneficiaries')
  );
  [$tags, $tagsSrc] = $getPrefilled(
    $termLabels($ar, 'field_tags'),
    $termLabels($en, 'field_tags')
  );

  $sourceNote = implode('; ', [
    "summary={$summarySrc}",
    "audience={$audienceSrc}",
    "duration={$durationSrc}",
    "languages={$langsSrc}",
    "channels={$channelsSrc}",
    "cost={$costSrc}",
    "payment={$paymentSrc}",
    "categories={$catSrc}",
    "beneficiaries={$benSrc}",
    "tags={$tagsSrc}",
  ]);

  fputcsv($fh, [
    (string) $node->id(),
    $titleAr,
    $summary,
    $audience,
    $duration,
    $langs,
    $channels,
    $cost,
    $payment,
    $categories,
    $beneficiaries,
    $tags,
    $sourceNote,
  ]);
  $rows++;
}

fclose($fh);

print "Rows: {$rows}\n";
print "Output: {$outPath}\n";

