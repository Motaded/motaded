<?php

/**
 * @file
 * Fill SEO metatags for Arabic service pages (field_meta_tags).
 *
 * Scope:
 * - Node type: page
 * - Published
 * - field_display_on_services = 1
 * - Arabic translation exists
 *
 * Metatags written:
 * - title
 * - description
 * - abstract
 * - keywords
 *
 * Usage:
 *   drush scr scripts/services_ar_fill_metatags.php
 *   drush scr scripts/services_ar_fill_metatags.php -- --dry-run
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

$argv = $_SERVER['argv'] ?? $GLOBALS['argv'] ?? [];
$dryRun = in_array('--dry-run', $argv, TRUE);

$reportPath = DRUPAL_ROOT . '/reports/services_ar_metatags_report.csv';
$reportHandle = fopen($reportPath, 'wb');
fputcsv($reportHandle, ['nid', 'title', 'meta_title', 'meta_description', 'meta_abstract', 'meta_keywords', 'status']);

$plainFromHtml = static function (string $html): string {
  $html = preg_replace('/<\/(p|div|h[1-6]|li|tr|section|article)\b[^>]*>/iu', ' ', $html);
  $html = preg_replace('/<br\s*\/?>/iu', ' ', $html);
  $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  return preg_replace('/\s+/u', ' ', trim($plain)) ?? '';
};

$clipAtWord = static function (string $text, int $max): string {
  $text = trim($text);
  if (mb_strlen($text) <= $max) {
    return $text;
  }
  $cut = mb_substr($text, 0, $max);
  $cut = preg_replace('/\s+\S*$/u', '', $cut) ?? $cut;
  return rtrim($cut, " \t\n\r\0\x0B,;:.،") . '...';
};

$extractSummary = static function (string $plain, int $max = 155) use ($clipAtWord): string {
  if ($plain === '') {
    return '';
  }
  $sentences = preg_split('/(?<=[\.\!\؟])\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];
  $summary = '';
  foreach ($sentences as $sentence) {
    $sentence = trim($sentence);
    if ($sentence === '') {
      continue;
    }
    $candidate = $summary === '' ? $sentence : $summary . ' ' . $sentence;
    if (mb_strlen($candidate) > $max) {
      break;
    }
    $summary = $candidate;
    if (mb_strlen($summary) >= 120) {
      break;
    }
  }
  if ($summary === '') {
    $summary = $clipAtWord($plain, $max);
  }
  return $summary;
};

$tokenizeKeywords = static function (string $title, array $categories): string {
  $base = [
    $title,
    'خدمات معتد',
    'تأسيس الشركات في السعودية',
  ];
  foreach ($categories as $cat) {
    $base[] = $cat;
  }
  $keywords = [];
  foreach ($base as $item) {
    $item = trim((string) $item);
    if ($item !== '' && !in_array($item, $keywords, TRUE)) {
      $keywords[] = $item;
    }
  }
  return implode('، ', array_slice($keywords, 0, 8));
};

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', 'page')
  ->condition('status', 1)
  ->condition('field_display_on_services.value', 1)
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes = $storage->loadMultiple($nids);

$seenDescriptions = [];
$updated = 0;

foreach ($nodes as $node) {
  if (!$node instanceof NodeInterface || !$node->hasTranslation('ar')) {
    continue;
  }
  $entity = $node->getTranslation('ar');
  $nid = (int) $entity->id();
  $title = trim((string) $entity->label());

  $bodyHtml = '';
  if ($entity->hasField('body') && !$entity->get('body')->isEmpty()) {
    $item = $entity->get('body')->first();
    $bodyHtml = (string) ($item->processed ?: $item->value);
  }
  $plain = $plainFromHtml($bodyHtml);

  $categories = [];
  if ($entity->hasField('field_taxonomy') && !$entity->get('field_taxonomy')->isEmpty()) {
    foreach ($entity->get('field_taxonomy') as $termRef) {
      if ($termRef->entity) {
        $categories[] = trim((string) $termRef->entity->label());
      }
    }
  }
  $categories = array_values(array_filter(array_unique($categories)));

  $metaTitle = $title . ' | معتد';
  $coreDescription = $extractSummary($plain, 150);
  $metaDescription = $title . ': ' . $coreDescription;
  $metaDescription = $clipAtWord($metaDescription, 160);

  // Ensure description uniqueness across services.
  if (isset($seenDescriptions[$metaDescription])) {
    $suffix = !empty($categories) ? ' - ' . $categories[0] : ' - خدمة ' . $nid;
    $metaDescription = $clipAtWord($metaDescription . $suffix, 160);
  }
  $seenDescriptions[$metaDescription] = TRUE;

  $metaAbstract = $clipAtWord($title . ': ' . ($coreDescription !== '' ? $coreDescription : $title), 120);
  $metaKeywords = $tokenizeKeywords($title, $categories);

  $meta = [
    'title' => $metaTitle,
    'description' => $metaDescription,
    'abstract' => $metaAbstract,
    'keywords' => $metaKeywords,
  ];

  if (!$dryRun) {
    $entity->set('field_meta_tags', [
      'value' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    $entity->setNewRevision(FALSE);
    $entity->save();
    $updated++;
  }

  fputcsv($reportHandle, [
    (string) $nid,
    $title,
    $metaTitle,
    $metaDescription,
    $metaAbstract,
    $metaKeywords,
    $dryRun ? 'dry_run' : 'updated',
  ]);
}

fclose($reportHandle);

print $dryRun ? "DRY RUN complete.\n" : "Updated nodes: {$updated}\n";
print "Report: {$reportPath}\n";

