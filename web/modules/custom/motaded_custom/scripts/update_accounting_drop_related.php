<?php

/**
 * @file
 * Removes Official guidance and related support from the accounting landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_drop_related.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/accounting-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Accounting landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Accounting landing node not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

$refs = [];
$removed = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  $title = $paragraph->hasField('field_title') && !$paragraph->get('field_title')->isEmpty()
    ? (string) $paragraph->get('field_title')->value
    : '';
  if ($type === 'related' || $title === 'Official guidance and related support') {
    $removed[] = $paragraph;
    continue;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

if ($removed === []) {
  echo "No Official guidance block found on /node/{$nid}.\n";
  return;
}

$source->set('field_paragraphs', $refs);
$source->save();
foreach ($removed as $paragraph) {
  foreach ($paragraph->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $paragraph->delete();
}
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Removed Official guidance and related support from /node/{$nid}.\n";
