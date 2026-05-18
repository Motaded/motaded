<?php

/**
 * @file
 * Apply EN SEO meta (keywords) to sector_page nodes from sector_page.seo.php.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/apply_sector_page_seo.php
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

/** @var array<string, array<string, string>> $seo */
$seo = require dirname(__DIR__) . '/data/sector_page.seo.php';

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'sector_page')
  ->condition('langcode', 'en')
  ->execute();

$done = 0;
$missing = [];

foreach ($nids as $nid) {
  $node = $storage->load((int) $nid);
  if (!$node instanceof NodeInterface || !$node->hasTranslation('en')) {
    continue;
  }
  $en_title = $node->getTranslation('en')->label();
  if (!isset($seo[$en_title])) {
    $missing[] = $en_title;
    continue;
  }
  motaded_custom_apply_node_metatags($node->getTranslation('en'), $seo[$en_title]);
  $node->save();
  $done++;
  \Drupal::logger('motaded')->notice('sector_page SEO EN: @t', ['@t' => $en_title]);
}

print "Applied SEO meta on {$done} sector_page node(s) (EN).\n";
if ($missing !== []) {
  print 'Missing SEO dataset (' . count($missing) . "):\n";
  foreach ($missing as $t) {
    print "  - {$t}\n";
  }
}
