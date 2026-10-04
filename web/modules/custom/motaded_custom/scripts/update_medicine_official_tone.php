<?php

/**
 * @file
 * Applies official institutional copy to the live medicine landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_medicine_official_tone.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\webform\Entity\Webform;

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
$source->setTitle($copy['title']);
$source->set('field_meta', json_encode([
  'title' => $copy['meta_title'],
  'description' => $copy['meta_description'],
], JSON_UNESCAPED_SLASHES));
$source->save();

$map = [
  'context' => $copy['context'],
  'eligibility' => $copy['eligibility'],
  'tabs' => $copy['catalogue'],
  'features' => $copy['features'],
  'stages' => $copy['process'],
  'stack' => $copy['process'],
  'lists' => $copy['documents'],
  'fees' => $copy['fees'],
  'related' => [
    'title' => 'Official guidance and related support',
    'lede' => '',
    'items' => $copy['related_extra'],
  ],
];

$updated = ['meta'];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $bundle = $paragraph->bundle();
  if ($bundle === 'hero_split_banner') {
    $hero = $copy['hero'];
    $paragraph->set('field_hero_split_eyebrow', $hero['eyebrow']);
    $paragraph->set('field_hero_split_headline_prefix', $hero['prefix']);
    $paragraph->set('field_hero_split_headline_accent', $hero['accent']);
    $paragraph->set('field_body', [
      'value' => $hero['body'],
      'format' => 'basic_html',
    ]);
    $link = $paragraph->get('field_link')->getValue();
    if ($link) {
      $link[0]['title'] = $hero['primary'];
      $paragraph->set('field_link', $link);
    }
    $paragraph->save();
    $updated[] = 'hero';
    continue;
  }
  if ($bundle === 'webform') {
    $paragraph->set('field_body', [
      'value' => $copy['form']['body'],
      'format' => 'full_html',
    ]);
    $paragraph->save();
    $updated[] = 'form';
    continue;
  }
  if ($bundle === 'accordions') {
    $i = 0;
    foreach ($paragraph->get('field_paragraphs') as $child_item) {
      $card = $child_item->entity;
      if (!$card instanceof ParagraphInterface || !isset($copy['faq'][$i])) {
        continue;
      }
      $row = $copy['faq'][$i];
      $card->set('field_title', $row['question']);
      $card->set('field_body', [
        'value' => $row['answer'],
        'format' => 'basic_html',
      ]);
      $card->save();
      $i++;
    }
    $updated[] = 'faq';
    continue;
  }
  if ($bundle !== 'cards' || !$paragraph->hasField('field_card_type') || $paragraph->get('field_card_type')->isEmpty()) {
    continue;
  }
  $type = (string) $paragraph->get('field_card_type')->value;
  if ($type === 'needs' || $type === 'eligibility') {
    _motaded_medicine_tone_replace_cards($paragraph, $copy['eligibility']);
    $paragraph->set('field_card_type', 'eligibility');
    $paragraph->save();
    $updated[] = 'eligibility';
    continue;
  }
  if ($type === 'features') {
    _motaded_medicine_tone_replace_cards($paragraph, $copy['features']);
    $paragraph->set('field_card_type', 'features');
    $paragraph->save();
    $updated[] = 'features';
    continue;
  }
  if ($type === 'stack' || $type === 'stages') {
    _motaded_medicine_tone_apply_cards($paragraph, $copy['process']);
    $paragraph->set('field_card_type', 'stages');
    $paragraph->save();
    $updated[] = 'stages';
    continue;
  }
  if ($type === 'situations') {
    continue;
  }
  if (!isset($map[$type])) {
    continue;
  }
  _motaded_medicine_tone_apply_cards($paragraph, $map[$type]);
  $updated[] = $type;
}

$context_p = NULL;
$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'situations') {
    foreach ($paragraph->get('field_paragraphs')->referencedEntities() as $child) {
      $child->delete();
    }
    $paragraph->delete();
    $updated[] = 'dropped-situations';
    continue;
  }
  if ($type === 'context') {
    $context_p = $paragraph;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

if (!$context_p instanceof ParagraphInterface) {
  $context_p = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'context',
  ]);
  $context_p->save();
  _motaded_medicine_tone_replace_cards($context_p, $copy['context']);
  $context_p->set('field_card_type', 'context');
  $context_p->save();
  $updated[] = 'context-created';
}

$ordered = [];
$context_ref = [
  'target_id' => $context_p->id(),
  'target_revision_id' => $context_p->getRevisionId(),
];
$inserted_context = FALSE;
foreach ($refs as $ref) {
  $paragraph = Paragraph::load((int) $ref['target_id']);
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if ($paragraph->bundle() === 'hero_split_banner') {
    $ordered[] = $ref;
    $ordered[] = $context_ref;
    $inserted_context = TRUE;
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'context') {
    if (!$inserted_context) {
      $ordered[] = $context_ref;
      $inserted_context = TRUE;
    }
    continue;
  }
  $ordered[] = $ref;
}
if (!$inserted_context) {
  array_unshift($ordered, $context_ref);
}
$source->set('field_paragraphs', $ordered);
$source->save();

$webform = Webform::load('sfda_assessment');
if ($webform instanceof Webform) {
  $elements = $webform->getElementsDecoded();
  if (isset($elements['actions'])) {
    $elements['actions']['#submit__label'] = 'Submit';
    $webform->setElements($elements);
    $webform->save();
    $updated[] = 'submit';
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo 'Updated medicine official tone on /node/' . $nid . ': ' . implode(', ', $updated) . "\n";

/**
 * @param array{title: string, lede?: string, items?: list<array<string, mixed>>} $data
 */
function _motaded_medicine_tone_apply_cards(ParagraphInterface $paragraph, array $data): void {
  $paragraph->set('field_title', $data['title']);
  if (($data['lede'] ?? '') !== '') {
    $paragraph->set('field_body', [
      'value' => $data['lede'],
      'format' => 'basic_html',
    ]);
  }
  else {
    $paragraph->set('field_body', []);
  }
  $paragraph->save();

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($data['items'][$i])) {
      continue;
    }
    $row = $data['items'][$i];
    $card->set('field_title', $row['title']);
    $card->set('field_body', [
      'value' => $row['body'] ?? '',
      'format' => 'basic_html',
    ]);
    if ($card->hasField('field_link')) {
      if (($row['uri'] ?? '') !== '') {
        $card->set('field_link', [
          'uri' => $row['uri'],
          'title' => ($row['link_title'] ?? '') !== '' ? $row['link_title'] : $row['title'],
        ]);
      }
      else {
        $card->set('field_link', []);
      }
    }
    $card->save();
    $i++;
  }
}

/**
 * @param array{title: string, lede?: string, items: list<array<string, mixed>>} $data
 */
function _motaded_medicine_tone_replace_cards(ParagraphInterface $parent, array $data): void {
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
