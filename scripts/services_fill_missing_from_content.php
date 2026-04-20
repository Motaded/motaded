<?php

/**
 * @file
 * Fill empty service sidebar / summary fields from existing node body text only.
 *
 * Usage: drush scr scripts/services_fill_missing_from_content.php
 *
 * Rules:
 * - Only EN nodes with field_display_on_services.
 * - Never overwrites non-empty values.
 * - body.summary: generated short service explainer for users + SEO meta (~150–160 chars
 *   when possible): best sentence(s) from body scored with title keywords, not a blind cut;
 *   never identical to full plain body.
 * - Optional: SERVICES_FILL_FORCE_SUMMARY=1 to regenerate summaries even if filled.
 * - Sidebar strings: only when a labelled line is found in plain text (regex), no guessing.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

$forceSummary = getenv('SERVICES_FILL_FORCE_SUMMARY') === '1';

$reportPath = DRUPAL_ROOT . '/reports/services_fill_missing_report.csv';
$fp = fopen($reportPath, 'w');
fputcsv($fp, ['nid', 'field', 'action', 'preview']);

$isEmpty = static function (?string $value): bool {
  return trim((string) $value) === '';
};

$plainFromHtml = static function (string $html): string {
  // Preserve word boundaries when block tags are removed.
  $html = preg_replace('/<\/(p|div|h[1-6]|li|tr|section|article)\b[^>]*>/iu', ' ', $html);
  $html = preg_replace('/<br\s*\/?>/iu', ' ', $html);
  $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  return preg_replace('/\s+/u', ' ', trim($plain)) ?? '';
};

/**
 * Build a short summary from HTML body; must not equal full plain text.
 */
$buildSummary = static function (string $bodyHtml, int $maxLen = 300) use ($plainFromHtml): string {
  $plain = $plainFromHtml($bodyHtml);
  if ($plain === '') {
    return '';
  }
  $len = mb_strlen($plain);
  if ($len <= 40) {
    return '';
  }
  // Long text: word-boundary excerpt.
  if ($len > $maxLen + 40) {
    $trunc = mb_substr($plain, 0, $maxLen);
    $trunc = preg_replace('/\s+\S*$/u', '', $trunc) ?? $trunc;
    $trunc = rtrim($trunc, " \t\n\r\0\x0B,;:.—-") . '…';
    if ($trunc !== $plain) {
      return $trunc;
    }
  }
  // Medium/short: first sentence if it is clearly shorter than full body.
  if (preg_match('/^(.{25,}?[.!?])(\s+|$)/u', $plain, $m)) {
    $sentence = trim($m[1]);
    if (mb_strlen($sentence) < $len - 25) {
      return $sentence;
    }
  }
  // Still too similar to full body: take ~55% at word boundary.
  $target = (int) max(80, min($maxLen, floor($len * 0.55)));
  $trunc = mb_substr($plain, 0, $target);
  $trunc = preg_replace('/\s+\S*$/u', '', $trunc) ?? $trunc;
  $trunc = rtrim($trunc, " \t\n\r\0\x0B,;:.—-") . '…';
  return ($trunc !== $plain && mb_strlen($trunc) < $len - 15) ? $trunc : '';
};

/**
 * First capture group from multiline plain text; trimmed, max length.
 */
$matchLabeled = static function (string $plain, string $pattern, int $max = 200): string {
  if ($plain === '') {
    return '';
  }
  if (preg_match($pattern, $plain, $m)) {
    $out = trim($m[1]);
    $out = preg_replace('/\s+/u', ' ', $out) ?? $out;
    if (mb_strlen($out) > $max) {
      $out = mb_substr($out, 0, $max);
      $out = preg_replace('/\s+\S*$/u', '', $out) ?? $out;
      $out = rtrim($out, ' ,;:.') . '…';
    }
    return $out;
  }
  return '';
};

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', 'page')
  ->condition('status', 1)
  ->condition('field_display_on_services.value', 1)
  ->condition('langcode', 'en')
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes = $storage->loadMultiple($nids);

$updatedNodes = 0;
$changes = 0;

foreach ($nodes as $node) {
  if (!$node instanceof NodeInterface) {
    continue;
  }
  $entity = $node->get('langcode')->value === 'en' ? $node : ($node->hasTranslation('en') ? $node->getTranslation('en') : $node);
  if ($entity->get('langcode')->value !== 'en') {
    continue;
  }

  $bodyField = $entity->get('body');
  $bodyHtml = (string) ($bodyField->value ?? '');
  $bodyFormat = $bodyField->format ?: 'full_html';
  $plain = $plainFromHtml($bodyHtml);

  if ($plain === '') {
    continue;
  }

  $dirty = FALSE;

  // body summary (only when body exists and summary is empty).
  if (!$bodyField->isEmpty()) {
    $currentSummary = (string) ($bodyField->summary ?? '');
    if ($isEmpty($currentSummary)) {
      $newSummary = $buildSummary($bodyHtml);
      if ($newSummary !== '') {
        $plainSummary = $plainFromHtml($newSummary);
        if ($plainSummary !== $plain) {
          $bodyField->setValue([
            'value' => $bodyHtml,
            'summary' => $newSummary,
            'format' => $bodyFormat,
          ]);
          $dirty = TRUE;
          $changes++;
          fputcsv($fp, [$entity->id(), 'body.summary', 'set', mb_substr($newSummary, 0, 120)]);
        }
      }
    }
  }

  // Target audience: only if an explicit label exists in the text.
  if ($entity->get('field_target_audience')->isEmpty() || $isEmpty((string) $entity->get('field_target_audience')->value)) {
    $ta = $matchLabeled(
      $plain,
      '/(?:^|\n|\s)(?:target\s+audience|intended\s+audience|who\s+is\s+this\s+for)\s*[:]\s*([^\n]+)/imu'
    );
    if ($ta !== '') {
      $entity->set('field_target_audience', $ta);
      $dirty = TRUE;
      $changes++;
      fputcsv($fp, [$entity->id(), 'field_target_audience', 'set', mb_substr($ta, 0, 120)]);
    }
  }

  $labeled = [
    'field_service_duration' => [
      '/(?:^|\n|\s)(?:service\s+duration|duration|timeframe|timeline)\s*[:]\s*([^\n]+)/imu',
    ],
    'field_provided_languages' => [
      '/(?:^|\n|\s)(?:provided\s+languages?|languages?)\s*[:]\s*([^\n]+)/imu',
    ],
    'field_service_channels' => [
      '/(?:^|\n|\s)(?:service\s+channels?|channels?)\s*[:]\s*([^\n]+)/imu',
    ],
    'field_service_cost' => [
      '/(?:^|\n|\s)(?:service\s+cost|cost|fee|pricing)\s*[:]\s*([^\n]+)/imu',
    ],
    'field_payment_options' => [
      '/(?:^|\n|\s)(?:payment\s+options?|payments?)\s*[:]\s*([^\n]+)/imu',
    ],
  ];

  foreach ($labeled as $fieldName => $patterns) {
    if (!isset($entity->{$fieldName})) {
      continue;
    }
    $f = $entity->get($fieldName);
    if (!$f->isEmpty() && !$isEmpty((string) $f->value)) {
      continue;
    }
    foreach ($patterns as $pattern) {
      $val = $matchLabeled($plain, $pattern);
      if ($val !== '') {
        $entity->set($fieldName, $val);
        $dirty = TRUE;
        $changes++;
        fputcsv($fp, [$entity->id(), $fieldName, 'set', mb_substr($val, 0, 120)]);
        break;
      }
    }
  }

  if ($dirty) {
    $entity->setNewRevision(FALSE);
    $entity->save();
    $updatedNodes++;
  }
}

fclose($fp);

print "Updated nodes: {$updatedNodes}\n";
print "Total field writes: {$changes}\n";
print "Report: {$reportPath}\n";
