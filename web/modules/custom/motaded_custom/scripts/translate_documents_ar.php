<?php

/**
 * @file
 * Apply Arabic translations for document nodes (title + summary + path alias).
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_documents_ar.php
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

/** @var array<string, array{title: string, field_short_description: string}> $DATA */
$DATA = require dirname(__DIR__) . '/data/documents_ar.dataset.php';
/** @var array<string, array<string, string>> $seo_ar */
$seo_ar = require dirname(__DIR__) . '/data/document_import_official.seo.ar.php';

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'document')
  ->condition('langcode', 'en')
  ->execute();

$done = 0;
$missing = [];

foreach ($nids as $nid) {
  $node = $storage->load((int) $nid);
  if (!$node instanceof NodeInterface) {
    continue;
  }
  if (!$node->hasTranslation('en')) {
    continue;
  }
  $en = $node->getTranslation('en');
  $en_title = $en->label();
  if (!isset($DATA[$en_title])) {
    $missing[] = $en_title;
    continue;
  }
  $row = $DATA[$en_title];
  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', $en->toArray());
  }
  $ar = $node->getTranslation('ar');
  $ar->setTitle((string) $row['title']);
  $ar->set('field_short_description', (string) $row['field_short_description']);
  if (isset($seo_ar[$en_title])) {
    motaded_custom_apply_node_metatags($ar, $seo_ar[$en_title]);
  }
  $ar->setPublished($en->isPublished());
  $node->save();
  motaded_custom_document_ar_sync_path_alias($node);
  $done++;
  \Drupal::logger('motaded')->notice('document AR: @t', ['@t' => $en_title]);
}

$alias_synced = 0;
$all_nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'document')
  ->execute();
foreach ($all_nids as $all_nid) {
  $doc = $storage->load((int) $all_nid);
  if ($doc instanceof NodeInterface && motaded_custom_document_ar_sync_path_alias($doc)) {
    $alias_synced++;
  }
}

print "Translated {$done} document node(s).\n";
print "Synced {$alias_synced} Arabic path alias(es) from English.\n";
if ($missing !== []) {
  print 'Missing dataset keys (' . count($missing) . "):\n";
  foreach ($missing as $t) {
    print "  - {$t}\n";
  }
}
