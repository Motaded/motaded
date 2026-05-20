<?php

/**
 * @file
 * Export all taxonomy terms to CSV (one row per translation).
 *
 * Run:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/export_taxonomy_terms.php
 *
 * Output (project root):
 *   exports/taxonomy_terms.csv
 */

declare(strict_types=1);

use Drupal\taxonomy\TermInterface;

$projectRoot = dirname(DRUPAL_ROOT);
$outDir = $projectRoot . '/exports';
$outPath = $outDir . '/taxonomy_terms.csv';

if (!is_dir($outDir) && !@mkdir($outDir, 0775, TRUE)) {
  throw new \RuntimeException('Cannot create directory: ' . $outDir);
}

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$tids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->sort('vid')
  ->sort('tid')
  ->execute();

$fp = fopen($outPath, 'wb');
if ($fp === FALSE) {
  throw new \RuntimeException('Cannot write: ' . $outPath);
}

// UTF-8 BOM for Excel.
fwrite($fp, "\xEF\xBB\xBF");
fputcsv($fp, ['vid', 'vocabulary', 'tid', 'name', 'langcode', 'status']);

$rows = 0;
foreach ($tids as $tid) {
  $term = $storage->load($tid);
  if (!$term instanceof TermInterface) {
    continue;
  }
  $vid = $term->bundle();
  $vocabulary = \Drupal::entityTypeManager()
    ->getStorage('taxonomy_vocabulary')
    ->load($vid);
  $vocabLabel = $vocabulary ? (string) $vocabulary->label() : $vid;

  foreach (array_keys($term->getTranslationLanguages()) as $langcode) {
    $translation = $term->hasTranslation($langcode)
      ? $term->getTranslation($langcode)
      : $term;
    fputcsv($fp, [
      $vid,
      $vocabLabel,
      (int) $tid,
      $translation->label(),
      $langcode,
      $translation->isPublished() ? '1' : '0',
    ]);
    $rows++;
  }
}

fclose($fp);

print sprintf("Wrote %s (%d terms, %d rows)\n", $outPath, count($tids), $rows);
