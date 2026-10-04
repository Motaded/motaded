<?php

/**
 * @file
 * Places both trade charts after Working with Motaded and updates copy.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_trade_block.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/customs-clearance-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Customs landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$before = [];
$market = NULL;
$after = [];
$seen_working = FALSE;

foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $en_p = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $en_p->hasField('field_title') ? trim((string) $en_p->get('field_title')->value) : '';
  $ref = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];

  if ($paragraph->bundle() === 'trade_market_overview') {
    _motaded_customs_trade_set($paragraph, 'en', 'Saudi Arabia’s trade in figures', '<p>Merchandise imports and exports for the latest month, compared with the same month last year, and the recent monthly trend.</p>');
    _motaded_customs_trade_set($paragraph, 'ar', 'تجارة المملكة العربية السعودية بالأرقام', '<p>واردات وصادرات السلع لآخر شهر، مقارنة بالشهر نفسه من العام الماضي، مع الاتجاه الشهري الأخير.</p>');
    $ref['target_revision_id'] = $paragraph->getRevisionId();
    $market = $ref;
    continue;
  }
  if ($paragraph->bundle() === 'trade_by_product') {
    continue;
  }

  if (!$seen_working) {
    $before[] = $ref;
    if ($paragraph->bundle() === 'cards' && in_array($title, [
      'Working with Motaded',
      'Why work with Motaded',
    ], TRUE)) {
      $seen_working = TRUE;
    }
    continue;
  }
  $after[] = $ref;
}

if ($market === NULL) {
  throw new \RuntimeException('Trade paragraph not found.');
}
if (!$seen_working) {
  throw new \RuntimeException('Working with Motaded paragraph not found.');
}

$refs = array_values(array_filter([...$before, $market, ...$after]));
$en = Node::load($nid);
$en = $en->hasTranslation('en') ? $en->getTranslation('en') : $en;
$en->set('field_paragraphs', $refs);
$en->save();
if ($node->hasTranslation('ar')) {
  $ar = Node::load($nid)->getTranslation('ar');
  $ar->set('field_paragraphs', $refs);
  $ar->save();
}

\Drupal::service('cache_tags.invalidator')->invalidateTags([
  'node:' . $nid,
  'datasaudi_trade',
]);
echo "Moved trade block after Working with Motaded on /node/{$nid}.\n";

function _motaded_customs_trade_set(ParagraphInterface $paragraph, string $langcode, string $title, string $lede): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $title);
  $tr->set('field_body', [
    'value' => $lede,
    'format' => 'basic_html',
  ]);
  $tr->save();
}
