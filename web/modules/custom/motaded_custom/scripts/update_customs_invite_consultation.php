<?php

/**
 * @file
 * Retargets the mid-page invite from assessment copy to consultation.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_invite_consultation.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$en = [
  'title' => 'Request a written consultation',
  'lede' => '<p>Company and consignment details are reviewed in consultation. Applicable customs clearance requirements and the service fee are confirmed in writing.</p>',
  'cta' => 'Request a Consultation',
];
$ar = [
  'title' => 'طلب استشارة مكتوبة',
  'lede' => '<p>تُراجع بيانات المنشأة والشحنة أثناء الاستشارة. تُؤكد متطلبات التخليص الجمركي المنطبقة وأتعاب الخدمة كتابةً.</p>',
  'cta' => 'طلب استشارة',
];

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
$updated = FALSE;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
    continue;
  }
  $en_p = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = trim((string) $en_p->get('field_title')->value);
  if (!in_array($title, [
    'Start with a written assessment',
    'Start with a consultation',
    'Talk to the clearance team',
    'Request a written consultation',
  ], TRUE)) {
    continue;
  }

  foreach (['en' => $en, 'ar' => $ar] as $langcode => $copy) {
    $tr = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
    $tr->set('field_title', $copy['title']);
    $tr->set('field_body', [
      'value' => $copy['lede'],
      'format' => 'basic_html',
    ]);
    $tr->set('field_card_type', 'invite');
    $tr->save();
  }

  foreach ($paragraph->get('field_paragraphs') as $child_item) {
    $card = $child_item->entity;
    if (!$card instanceof ParagraphInterface) {
      continue;
    }
    foreach (['en' => $en, 'ar' => $ar] as $langcode => $copy) {
      $card_tr = $card->hasTranslation($langcode)
        ? $card->getTranslation($langcode)
        : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
      $card_tr->set('field_title', $copy['cta']);
      $link = $card->get('field_link')->getValue();
      if ($link) {
        $link[0]['uri'] = 'internal:#assessment';
        $link[0]['title'] = $copy['cta'];
        $card_tr->set('field_link', $link);
      }
      $card_tr->save();
    }
  }

  $updated = TRUE;
  echo "Updated invite paragraph {$paragraph->id()}.\n";
}

if (!$updated) {
  throw new \RuntimeException('Invite paragraph not found.');
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Invite retargeted to consultation on /node/{$nid}.\n";
