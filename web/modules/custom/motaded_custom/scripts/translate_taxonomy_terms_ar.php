<?php

/**
 * @file
 * Apply Arabic (ar) translations to all taxonomy terms from dataset.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_taxonomy_terms_ar.php
 */

declare(strict_types=1);

use Drupal\taxonomy\TermInterface;

/**
 * Normalizes term labels for lookup (trim, collapse whitespace).
 */
function motaded_taxonomy_normalize_label(string $label): string {
  return trim(preg_replace('/\s+/u', ' ', $label) ?? $label);
}

/**
 * Returns TRUE when the string is predominantly Arabic script.
 */
function motaded_taxonomy_is_mostly_arabic(string $text): bool {
  if ($text === '') {
    return FALSE;
  }
  $letters = preg_replace('/[^\p{Arabic}]/u', '', $text) ?? '';
  $latin = preg_replace('/[^a-zA-Z]/', '', $text) ?? '';
  return mb_strlen($letters) > mb_strlen($latin);
}

/** @var array<string, array<string, string>> $MAP */
$MAP = require dirname(__DIR__) . '/data/taxonomy_terms_ar.php';

// Build per-vocabulary maps with normalized keys.
/** @var array<string, array<string, string>> $normalized */
$normalized = [];
foreach ($MAP as $vid => $terms) {
  if (!is_array($terms)) {
    continue;
  }
  $normalized[$vid] = [];
  foreach ($terms as $en => $ar) {
    $key = motaded_taxonomy_normalize_label((string) $en);
    if ($key !== '') {
      $normalized[$vid][$key] = (string) $ar;
    }
  }
}

$storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$vocab_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
$applied = 0;
$skipped = 0;
$fallback = 0;
$unresolved = [];

$ensure_term_ar = static function (TermInterface $term, string $ar_name) use (&$applied): void {
  $ar_name = motaded_taxonomy_normalize_label($ar_name);
  if ($ar_name === '') {
    return;
  }
  if (!$term->hasTranslation('ar')) {
    $term->addTranslation('ar', ['name' => $ar_name, 'status' => 1]);
  }
  else {
    $term->getTranslation('ar')->setName($ar_name);
  }
  if ($term->getTranslation('ar')->hasField('status')) {
    $term->getTranslation('ar')->set('status', TRUE);
  }
  $term->save();
  $applied++;
};

foreach ($vocab_storage->loadMultiple() as $vocabulary) {
  $vid = $vocabulary->id();
  $tids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', $vid)
    ->sort('tid')
    ->execute();

  $vid_map = $normalized[$vid] ?? [];

  foreach ($tids as $tid) {
    $term = $storage->load((int) $tid);
    if (!$term instanceof TermInterface) {
      continue;
    }

    $source = $term->getUntranslated();
    $source_lang = $source->language()->getId();
    $en = $term->hasTranslation('en')
      ? $term->getTranslation('en')
      : ($source_lang === 'en' ? $source : $term);
    $label = motaded_taxonomy_normalize_label((string) $en->label());

    if ($term->hasTranslation('ar')) {
      $existing_ar = motaded_taxonomy_normalize_label((string) $term->getTranslation('ar')->label());
      if (isset($vid_map[$label]) && $existing_ar === $vid_map[$label]) {
        $skipped++;
        continue;
      }
    }

    if (isset($vid_map[$label])) {
      $ensure_term_ar($term, $vid_map[$label]);
      continue;
    }

    if (motaded_taxonomy_is_mostly_arabic($label)) {
      $ensure_term_ar($term, $label);
      $fallback++;
      continue;
    }

    $unresolved[] = $vid . '|' . $tid . '|' . $label;
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['taxonomy_term_list']);

print sprintf(
  "Taxonomy AR: applied=%d, unchanged=%d, arabic_fallback=%d, unresolved=%d\n",
  $applied,
  $skipped,
  $fallback,
  count($unresolved),
);

if ($unresolved !== []) {
  print "Unresolved terms (add to dataset):\n";
  foreach ($unresolved as $line) {
    print '  ' . $line . "\n";
  }
}
