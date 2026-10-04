<?php

/**
 * @file
 * Updates the first three medicine landing blocks after the banner.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_medicine_first_blocks.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$landings = require dirname(__DIR__) . '/data/sfda_service_landings.dataset.php';
$copy = $landings['medicine'];

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/medicine-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Medicine landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Medicine landing node not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

$context_p = NULL;
$eligibility_p = NULL;
$catalogue_p = NULL;
$related_p = NULL;
$ordered = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  $title = $paragraph->hasField('field_title') ? trim((string) $paragraph->get('field_title')->value) : '';
  if ($type === 'context') {
    $context_p = $paragraph;
    continue;
  }
  if ($type === 'eligibility' || $type === 'needs') {
    $eligibility_p = $paragraph;
    continue;
  }
  if ($type === 'situations') {
    foreach ($paragraph->get('field_paragraphs')->referencedEntities() as $child) {
      $child->delete();
    }
    $paragraph->delete();
    continue;
  }
  if ($type === 'catalogue' || $type === 'tabs') {
    $catalogue_p = $paragraph;
    continue;
  }
  if ($type === 'related' || $title === 'Official guidance and related support') {
    $related_p = $paragraph;
  }
  $ordered[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

if (!$eligibility_p || !$catalogue_p) {
  throw new \RuntimeException('Medicine first-block paragraphs not found.');
}

if ($context_p instanceof ParagraphInterface && !empty($copy['context'])) {
  _motaded_medicine_replace_cards($context_p, $copy['context']);
  $context_p->set('field_card_type', 'context');
  $context_p->save();
}
_motaded_medicine_replace_cards($eligibility_p, $copy['eligibility']);
$eligibility_p->set('field_card_type', 'eligibility');
$eligibility_p->save();
_motaded_medicine_replace_cards($catalogue_p, $copy['catalogue']);
$catalogue_p->set('field_sub_title', $copy['catalogue']['note'] ?? '');
$catalogue_p->save();

if ($related_p instanceof ParagraphInterface) {
  _motaded_medicine_replace_cards($related_p, [
    'title' => 'Official guidance and related support',
    'lede' => '',
    'items' => $copy['related_extra'],
  ]);
  foreach ($ordered as $i => $ref) {
    if ((int) $ref['target_id'] === (int) $related_p->id()) {
      $ordered[$i]['target_revision_id'] = $related_p->getRevisionId();
    }
  }
}

$hero = array_shift($ordered);
$refs = [];
if (is_array($hero)) {
  $refs[] = $hero;
}
foreach (array_filter([$context_p, $eligibility_p, $catalogue_p]) as $paragraph) {
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}
$refs = array_merge($refs, $ordered);

$source->set('field_paragraphs', $refs);
$source->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Updated medicine first blocks on /node/{$nid}.\n";

/**
 * @param array{title: string, lede?: string, items: list<array<string, mixed>>} $data
 */
function _motaded_medicine_replace_cards(ParagraphInterface $parent, array $data): void {
  $old = $parent->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($data['items'] as $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'],
      'field_body' => [
        'value' => $item['body'] ?? '',
        'format' => 'basic_html',
      ],
    ];
    if (($item['subtitle'] ?? '') !== '') {
      $values['field_sub_title'] = $item['subtitle'];
    }
    if (($item['uri'] ?? '') !== '') {
      $values['field_link'] = [
        'uri' => $item['uri'],
        'title' => ($item['link_title'] ?? '') !== '' ? $item['link_title'] : $item['title'],
      ];
    }
    $card = Paragraph::create($values);
    $card->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }
  $parent->set('field_title', $data['title']);
  if (($data['lede'] ?? '') !== '') {
    $parent->set('field_body', [
      'value' => $data['lede'],
      'format' => 'basic_html',
    ]);
  }
  else {
    $parent->set('field_body', []);
  }
  $parent->set('field_paragraphs', $children);
  $parent->save();
  foreach ($old as $child) {
    $child->delete();
  }
}
