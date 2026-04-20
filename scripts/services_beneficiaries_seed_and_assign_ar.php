<?php

declare(strict_types=1);

/**
 * Seed beneficiaries dictionary (EN + AR) and assign AR services.
 *
 * Usage:
 *   ddev drush scr scripts/services_beneficiaries_seed_and_assign_ar.php
 *   ddev drush scr scripts/services_beneficiaries_seed_and_assign_ar.php -- --dry-run
 */

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$termsMap = [
  'Company Owners' => 'أصحاب الشركات',
  'Compliance and Legal Teams' => 'فرق الامتثال والشؤون القانونية',
  'Finance and Payroll Teams' => 'فرق المالية والرواتب',
  'HR and Workforce Teams' => 'فرق الموارد البشرية والقوى العاملة',
  'Importers and Exporters' => 'المستوردون والمصدرون',
  'Local and International Businesses' => 'الشركات المحلية والدولية',
  'PRO and Government Relations Teams' => 'فرق العلاقات الحكومية والتعقيب',
];

$termStorage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

/**
 * @return int
 */
$ensureTerm = static function (string $enName, string $arName) use ($termStorage, $dryRun): int {
  $existing = $termStorage->loadByProperties([
    'vid' => 'beneficiaries',
    'name' => $enName,
  ]);
  $term = $existing ? reset($existing) : FALSE;
  if (!$term instanceof TermInterface) {
    if ($dryRun) {
      return -1;
    }
    $term = $termStorage->create([
      'vid' => 'beneficiaries',
      'name' => $enName,
      'langcode' => 'en',
    ]);
    $term->save();
  }

  if (!$dryRun) {
    if ($term->hasTranslation('ar')) {
      $ar = $term->getTranslation('ar');
      if ((string) $ar->label() !== $arName) {
        $ar->setName($arName);
        $ar->save();
      }
    }
    else {
      $term->addTranslation('ar', ['name' => $arName])->save();
    }
  }

  return (int) $term->id();
};

$tidsByEn = [];
foreach ($termsMap as $en => $ar) {
  $tid = $ensureTerm($en, $ar);
  if ($tid > 0) {
    $tidsByEn[$en] = $tid;
  }
}

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', 'page')
  ->condition('status', 1)
  ->condition('langcode', 'ar')
  ->condition('field_display_on_services.value', 1)
  ->sort('nid')
  ->execute();

$nodes = $nodeStorage->loadMultiple($nids);

$contains = static function (string $text, array $needles): bool {
  foreach ($needles as $needle) {
    if (mb_stripos($text, $needle) !== FALSE) {
      return TRUE;
    }
  }
  return FALSE;
};

$assigned = 0;
$report = [];

foreach ($nodes as $node) {
  if (!$node instanceof NodeInterface || !$node->hasTranslation('ar')) {
    continue;
  }
  $entity = $node->getTranslation('ar');
  $title = trim((string) $entity->label());
  $haystack = mb_strtolower($title);

  $labels = [
    'Local and International Businesses',
  ];

  if ($contains($haystack, ['مستثمر', 'استثمار', 'تأسيس', 'الشركات', 'شركة'])) {
    $labels[] = 'Company Owners';
  }
  if ($contains($haystack, ['عقد', 'الاسم التجاري', 'ترخيص', 'الغرفة التجارية', 'امتثال'])) {
    $labels[] = 'Compliance and Legal Teams';
  }
  if ($contains($haystack, ['محاس', 'ضريبة', 'الزكاة', 'تدقيق', 'الرواتب', 'التأمينات'])) {
    $labels[] = 'Finance and Payroll Teams';
  }
  if ($contains($haystack, ['قوى', 'رخصة العمل', 'تأشيرة', 'الإقامة', 'مقيم', 'توطين', 'موظف'])) {
    $labels[] = 'HR and Workforce Teams';
  }
  if ($contains($haystack, ['المستورد', 'المصدر', 'التصدير', 'الاستيراد', 'منفذ'])) {
    $labels[] = 'Importers and Exporters';
  }
  if ($contains($haystack, ['منصة', 'الغرفة التجارية', 'العنوان الوطني', 'مقيم', 'قوى', 'مدد'])) {
    $labels[] = 'PRO and Government Relations Teams';
  }

  $labels = array_values(array_unique($labels));
  $items = [];
  foreach ($labels as $label) {
    if (!empty($tidsByEn[$label])) {
      $items[] = ['target_id' => $tidsByEn[$label]];
    }
  }

  if (!$dryRun) {
    $entity->set('field_beneficiaries', $items);
    $node->setNewRevision(FALSE);
    $node->save();
  }

  $assigned++;
  $report[] = [(string) $entity->id(), $title, implode(' | ', $labels)];
}

$reportPath = DRUPAL_ROOT . '/reports/services_ar_beneficiaries_assignment.csv';
$fh = fopen($reportPath, 'wb');
fputcsv($fh, ['nid', 'title_ar', 'assigned_beneficiaries_en'], ',', '"', '\\');
foreach ($report as $line) {
  fputcsv($fh, $line, ',', '"', '\\');
}
fclose($fh);

print "Terms ensured: " . count($termsMap) . "\n";
print "AR services processed: {$assigned}\n";
print $dryRun ? "DRY RUN (no saves)\n" : "Saved\n";
print "Report: {$reportPath}\n";

