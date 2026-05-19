<?php

/**
 * @file
 * Import taxonomy terms (EN + AR) from CSV into the current database.
 *
 * Default dataset (11 vocabularies for prod migration):
 *   modules/custom/motaded_custom/data/taxonomy_migration_terms.csv
 *
 * Run:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/import_taxonomy_terms.php
 *   ddev drush php:script modules/custom/motaded_custom/scripts/import_taxonomy_terms.php -- --dry-run
 *   ddev drush moti
 *   ddev drush moti --dry-run
 */

declare(strict_types=1);

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/** Vocabularies included in taxonomy_migration_terms.csv. */
const MOTADED_TAXONOMY_IMPORT_VIDS = [
  'city',
  'country',
  'document_category',
  'document_type',
  'event_type',
  'library_section',
  'market_insight_category',
  'event_organizer',
  'platform_category',
  'region',
  'sector',
];

/**
 * @var string|null $motaded_taxonomy_import_file
 * @var bool|null $motaded_taxonomy_import_dry_run
 */

$file = $motaded_taxonomy_import_file ?? (dirname(__DIR__) . '/data/taxonomy_migration_terms.csv');
$dryRun = (bool) ($motaded_taxonomy_import_dry_run ?? FALSE);

if (isset($extra) && is_array($extra)) {
  foreach ($extra as $arg) {
    if ($arg === '--dry-run') {
      $dryRun = TRUE;
    }
    elseif (is_string($arg) && $arg !== '' && !str_starts_with($arg, '--')) {
      $file = $arg;
    }
  }
}

if (!is_readable($file)) {
  throw new \RuntimeException('CSV not found or not readable: ' . $file);
}

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

/**
 * Finds a term by vocabulary + exact English label.
 */
$find_term = static function (string $vid, string $name) use ($storage): ?TermInterface {
  $name = trim($name);
  if ($name === '') {
    return NULL;
  }
  $tids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', $vid)
    ->condition('name', $name)
    ->range(0, 1)
    ->execute();
  if (!$tids) {
    return NULL;
  }
  $term = $storage->load((int) reset($tids));
  return $term instanceof TermInterface ? $term : NULL;
};

/**
 * Ensures Arabic translation on a term.
 *
 * @return 'created'|'updated'|'skipped'
 */
$ensure_ar = static function (TermInterface $term, string $ar_name, bool $force) use ($dryRun): string {
  $ar_name = trim($ar_name);
  if ($ar_name === '') {
    return 'skipped';
  }
  if ($term->hasTranslation('ar')) {
    $existing = trim((string) $term->getTranslation('ar')->label());
    if (!$force && $existing === $ar_name) {
      return 'skipped';
    }
    if ($dryRun) {
      return 'updated';
    }
    $term->getTranslation('ar')->setName($ar_name);
    if ($term->getTranslation('ar')->hasField('status')) {
      $term->getTranslation('ar')->set('status', TRUE);
    }
    $term->save();
    return 'updated';
  }
  if ($dryRun) {
    return 'created';
  }
  $term->addTranslation('ar', ['name' => $ar_name, 'status' => 1]);
  $term->save();
  return 'created';
};

// tid => ['vid' => ..., 'en' => ..., 'ar' => ..., 'status' => ...]
$groups = [];
$handle = fopen($file, 'rb');
if ($handle === FALSE) {
  throw new \RuntimeException('Cannot open CSV: ' . $file);
}

$header = fgetcsv($handle);
if ($header === FALSE) {
  fclose($handle);
  throw new \RuntimeException('Empty CSV: ' . $file);
}

while (($row = fgetcsv($handle)) !== FALSE) {
  if (count($row) < 6) {
    continue;
  }
  [$vid, , $tid, $name, $langcode, $status] = $row;
  $vid = trim($vid);
  if (!in_array($vid, MOTADED_TAXONOMY_IMPORT_VIDS, TRUE)) {
    continue;
  }
  $tid = (int) $tid;
  if (!isset($groups[$tid])) {
    $groups[$tid] = [
      'vid' => $vid,
      'en' => '',
      'ar' => '',
      'status' => 1,
    ];
  }
  $name = trim($name);
  $langcode = trim($langcode);
  if ($langcode === 'en') {
    $groups[$tid]['en'] = $name;
    $groups[$tid]['status'] = (int) $status;
  }
  elseif ($langcode === 'ar') {
    $groups[$tid]['ar'] = $name;
  }
}
fclose($handle);

$stats = [
  'terms' => 0,
  'created' => 0,
  'exists' => 0,
  'ar_created' => 0,
  'ar_updated' => 0,
  'skipped' => 0,
  'errors' => 0,
];

foreach ($groups as $group) {
  $vid = $group['vid'];
  $en = $group['en'];
  $ar = $group['ar'];
  if ($en === '') {
    $stats['errors']++;
    print sprintf("Skip: missing EN name for vid=%s\n", $vid);
    continue;
  }

  $stats['terms']++;
  $term = $find_term($vid, $en);

  if ($term === NULL) {
    if ($dryRun) {
      print sprintf("[dry-run] create %s / %s\n", $vid, $en);
      $stats['created']++;
    }
    else {
      $term = Term::create([
        'vid' => $vid,
        'name' => $en,
        'langcode' => 'en',
        'status' => (bool) $group['status'],
      ]);
      $term->save();
      print sprintf("Created %s / %s (tid=%d)\n", $vid, $en, (int) $term->id());
      $stats['created']++;
    }
  }
  else {
    $stats['exists']++;
  }

  if ($ar === '' || $term === NULL) {
    if ($ar !== '' && $term === NULL && $dryRun) {
      print sprintf("[dry-run] ar translation for new %s / %s → %s\n", $vid, $en, $ar);
      $stats['ar_created']++;
    }
    continue;
  }

  $ar_result = $ensure_ar($term, $ar, FALSE);
  if ($ar_result === 'created') {
    print sprintf("AR created %s / %s\n", $vid, $en);
    $stats['ar_created']++;
  }
  elseif ($ar_result === 'updated') {
    print sprintf("AR updated %s / %s\n", $vid, $en);
    $stats['ar_updated']++;
  }
  else {
    $stats['skipped']++;
  }
}

if (!$dryRun) {
  \Drupal::service('cache_tags.invalidator')->invalidateTags(['taxonomy_term_list']);
}

$mode = $dryRun ? 'dry-run' : 'import';
print sprintf(
  "Taxonomy %s from %s: terms=%d, created=%d, exists=%d, ar_created=%d, ar_updated=%d, ar_unchanged=%d, errors=%d\n",
  $mode,
  $file,
  $stats['terms'],
  $stats['created'],
  $stats['exists'],
  $stats['ar_created'],
  $stats['ar_updated'],
  $stats['skipped'],
  $stats['errors'],
);
