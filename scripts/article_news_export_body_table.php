<?php

/**
 * @file
 * Export article + news body as plain text for manual / AI summary work.
 *
 * Usage:
 *   ddev drush scr scripts/article_news_export_body_table.php
 *
 * Output:
 *   data/service-import/article_news_body_export.csv
 *   Columns: nid, type, title, url, body_plain, new_summary (empty — заповнити вручну / далі імпорт)
 */

declare(strict_types=1);

use Drupal\Core\Url;
use Drupal\node\NodeInterface;

$outPath = DRUPAL_ROOT . '/data/service-import/article_news_body_export.csv';
$outDir = dirname($outPath);
if (!is_dir($outDir) && !mkdir($outDir, 0775, TRUE) && !is_dir($outDir)) {
  throw new RuntimeException("Cannot create output directory: {$outDir}");
}

$plainFromHtml = static function (string $html): string {
  $html = preg_replace('/<\/(p|div|h[1-6]|li|tr|section|article)\b[^>]*>/iu', ' ', $html);
  $html = preg_replace('/<br\s*\/?>/iu', ' ', $html);
  $plain = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  return preg_replace('/\s+/u', ' ', trim($plain)) ?? '';
};

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', ['article', 'news'], 'IN')
  ->condition('status', 1)
  ->condition('langcode', 'en')
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes = $storage->loadMultiple($nids);

$fh = fopen($outPath, 'wb');
if ($fh === FALSE) {
  throw new RuntimeException("Cannot open output CSV for writing: {$outPath}");
}

fputcsv($fh, ['nid', 'type', 'title', 'url', 'body_plain', 'new_summary']);

$rows = 0;
foreach ($nodes as $node) {
  if (!$node instanceof NodeInterface) {
    continue;
  }
  $entity = $node->get('langcode')->value === 'en'
    ? $node
    : ($node->hasTranslation('en') ? $node->getTranslation('en') : $node);
  if (!$entity instanceof NodeInterface || $entity->get('langcode')->value !== 'en') {
    continue;
  }
  if (!$entity->hasField('body') || $entity->get('body')->isEmpty()) {
    continue;
  }
  $item = $entity->get('body')->first();
  if (!$item) {
    continue;
  }
  $bodyHtml = (string) ($item->processed ?: $item->value);
  $plain = $plainFromHtml($bodyHtml);
  if ($plain === '') {
    continue;
  }

  $url = Url::fromRoute('entity.node.canonical', ['node' => $entity->id()], ['absolute' => TRUE])->toString();

  fputcsv($fh, [
    (string) $entity->id(),
    $entity->bundle(),
    trim((string) $entity->label()),
    $url,
    $plain,
    '',
  ]);
  $rows++;
}

fclose($fh);

print "Rows: {$rows}\n";
print "Output: {$outPath}\n";
