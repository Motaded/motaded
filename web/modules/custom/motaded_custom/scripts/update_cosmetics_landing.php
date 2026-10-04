<?php

/**
 * @file
 * Applies the cosmetics listing rewrite to the live landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_cosmetics_landing.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$landings = require dirname(__DIR__) . '/data/sfda_service_landings.dataset.php';
$copy = $landings['cosmetics'];

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/cosmetics-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Cosmetics landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Cosmetics landing node not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

$blocks = [
  'hero_split_banner' => NULL,
  'briefing' => NULL,
  'composition' => NULL,
  'support' => NULL,
  'deliverables' => NULL,
  'listing' => NULL,
  'needs' => NULL,
  'catalogue' => NULL,
  'situations' => NULL,
  'directory' => NULL,
  'features' => NULL,
  'stack' => NULL,
  'lists' => NULL,
  'fees' => NULL,
  'related' => NULL,
  'webform' => NULL,
];
$rest = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->bundle();
  if ($paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()) {
    $type = (string) $paragraph->get('field_card_type')->value;
  }
  $title = $paragraph->hasField('field_title') ? trim((string) $paragraph->get('field_title')->value) : '';
  if ($type === 'related' || $title === 'Official guidance and related support') {
    $key = 'related';
  }
  elseif (in_array($title, [
    'Support for Brands, Manufacturers and Importers',
    'Support for Cosmetic Brands, Manufacturers and Importers',
    'Who this support is for',
  ], TRUE) || $type === 'needs') {
    $key = 'support';
  }
  elseif (in_array($title, [
    'Product Information and Documentation',
    'Documents we may need',
  ], TRUE)) {
    $key = 'lists';
  }
  elseif ($title === 'Cosmetics and Personal-Care Products in Saudi Arabia') {
    $key = 'briefing';
  }
  elseif ($title === 'Product Composition, Claims and Labelling') {
    $key = 'composition';
  }
  elseif ($title === 'Service Deliverables') {
    $key = 'deliverables';
  }
  elseif (in_array($title, [
    'Cosmetic Product Listing Support',
    'How Motaded can help',
  ], TRUE) || $type === 'tabs' || $type === 'catalogue') {
    $key = 'listing';
  }
  elseif (in_array($title, [
    'From Product Review to Listing Submission',
    'How we work',
  ], TRUE) || $type === 'stages' || $type === 'stack') {
    $key = 'stack';
  }
  elseif ($title === 'When to contact us' || $type === 'situations') {
    $key = 'situations';
  }
  elseif ($title === 'Working with Motaded' || $title === 'Request a Consultation' || $type === 'features') {
    $key = 'features';
  }
  elseif ($type === 'matrix' && !$blocks['support'] instanceof ParagraphInterface) {
    $key = 'support';
  }
  elseif (array_key_exists($type, $blocks)) {
    $key = $type;
  }
  else {
    $rest[] = $paragraph;
    continue;
  }
  $blocks[$key] = $paragraph;
}

foreach (['hero_split_banner', 'stack', 'lists', 'fees', 'related'] as $key) {
  if (!$blocks[$key] instanceof ParagraphInterface) {
    throw new \RuntimeException("Cosmetics {$key} block not found.");
  }
}

$source->setTitle($copy['title']);
$source->set('field_meta', json_encode([
  'title' => $copy['meta_title'],
  'description' => $copy['meta_description'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
$source->save();

$hero = $blocks['hero_split_banner'];
$hero_src = $hero->hasTranslation('en') ? $hero->getTranslation('en') : $hero;
$hero_src->set('field_hero_split_eyebrow', $copy['hero']['eyebrow']);
$hero_src->set('field_hero_split_headline_prefix', $copy['hero']['prefix']);
$hero_src->set('field_hero_split_headline_accent', $copy['hero']['accent']);
$hero_src->set('field_body', [
  'value' => $copy['hero']['body'],
  'format' => 'basic_html',
]);
$hero_src->set('field_link', [
  'uri' => 'internal:#assessment',
  'title' => $copy['hero']['primary'],
]);
$hero_src->set('field_link_secondary', [
  'uri' => 'internal:#catalogue',
  'title' => $copy['hero']['secondary'],
]);
$hero_src->save();

if (!$blocks['briefing'] instanceof ParagraphInterface) {
  $blocks['briefing'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'prose',
  ]);
  $blocks['briefing']->save();
}
_motaded_cosmetics_replace_cards($blocks['briefing'], [
  'title' => $copy['briefing']['title'],
  'lede' => $copy['briefing']['lede'],
  'items' => $copy['briefing']['items'] ?? [],
]);
$blocks['briefing']->set('field_card_type', 'prose');
$blocks['briefing']->save();

if (!$blocks['composition'] instanceof ParagraphInterface) {
  $blocks['composition'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'prose',
  ]);
  $blocks['composition']->save();
}
_motaded_cosmetics_replace_cards($blocks['composition'], [
  'title' => $copy['composition']['title'],
  'lede' => $copy['composition']['lede'],
  'items' => $copy['composition']['items'],
]);
$blocks['composition']->set('field_card_type', 'prose');
$blocks['composition']->save();

if (!$blocks['support'] instanceof ParagraphInterface) {
  $blocks['support'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'formats',
  ]);
  $blocks['support']->save();
}
_motaded_cosmetics_replace_cards($blocks['support'], $copy['support']);
$blocks['support']->set('field_card_type', 'formats');
$blocks['support']->save();

if (!$blocks['listing'] instanceof ParagraphInterface) {
  $blocks['listing'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'formats',
  ]);
  $blocks['listing']->save();
}
$listing = $copy['listing'];
_motaded_cosmetics_replace_cards($blocks['listing'], [
  'title' => $listing['title'],
  'lede' => $listing['lede'],
  'items' => $listing['items'],
]);
$blocks['listing']->set('field_card_type', 'formats');
$blocks['listing']->save();

if ($blocks['situations'] instanceof ParagraphInterface) {
  foreach ($blocks['situations']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['situations']->delete();
  $blocks['situations'] = NULL;
}

if ($blocks['needs'] instanceof ParagraphInterface && (int) $blocks['needs']->id() !== (int) $blocks['support']->id()) {
  foreach ($blocks['needs']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['needs']->delete();
  $blocks['needs'] = NULL;
}

if ($blocks['catalogue'] instanceof ParagraphInterface && (int) $blocks['catalogue']->id() !== (int) $blocks['listing']->id()) {
  foreach ($blocks['catalogue']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['catalogue']->delete();
  $blocks['catalogue'] = NULL;
}

if (!$blocks['features'] instanceof ParagraphInterface) {
  $blocks['features'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'features',
  ]);
  $blocks['features']->save();
}
_motaded_cosmetics_replace_cards($blocks['features'], $copy['features']);
$blocks['features']->set('field_card_type', 'features');
$blocks['features']->save();

if (!$blocks['deliverables'] instanceof ParagraphInterface) {
  $blocks['deliverables'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'formats',
  ]);
  $blocks['deliverables']->save();
}
_motaded_cosmetics_replace_cards($blocks['deliverables'], $copy['deliverables']);
$blocks['deliverables']->set('field_card_type', 'formats');
$blocks['deliverables']->save();

_motaded_cosmetics_replace_cards($blocks['stack'], $copy['process']);
$blocks['stack']->set('field_card_type', 'stages');
$blocks['stack']->save();

_motaded_cosmetics_replace_cards($blocks['lists'], $copy['documents']);
$blocks['lists']->set('field_card_type', 'matrix');
$blocks['lists']->save();
_motaded_cosmetics_replace_cards($blocks['fees'], $copy['fees']);
_motaded_cosmetics_replace_cards($blocks['related'], [
  'title' => 'Official guidance and related support',
  'lede' => '',
  'items' => $copy['related_extra'],
]);

if ($blocks['webform'] instanceof ParagraphInterface) {
  $blocks['webform']->set('field_body', [
    'value' => $copy['form']['body'],
    'format' => 'full_html',
  ]);
  $blocks['webform']->save();
}

if ($blocks['directory'] instanceof ParagraphInterface) {
  foreach ($blocks['directory']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['directory']->delete();
}

$ordered = [
  $hero_src,
  $blocks['listing'],
  $blocks['support'],
  $blocks['deliverables'],
  $blocks['stack'],
  $blocks['briefing'],
  $blocks['features'],
  $blocks['composition'],
  $blocks['lists'],
  $blocks['fees'],
];
$ordered = array_merge($ordered, $rest);
if ($blocks['webform'] instanceof ParagraphInterface) {
  $ordered[] = $blocks['webform'];
}
$ordered[] = $blocks['related'];

$refs = [];
foreach ($ordered as $paragraph) {
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}
$source->set('field_paragraphs', $refs);
$source->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Updated cosmetics landing on /node/{$nid}.\n";

/**
 * @param array{lede?: string, items?: list<array<string, mixed>>} $section
 */
function _motaded_cosmetics_prose_body(array $section): string {
  $html = (string) ($section['lede'] ?? '');
  foreach ($section['items'] ?? [] as $item) {
    $heading = trim((string) ($item['title'] ?? ''));
    if ($heading !== '') {
      $html .= '<h3>' . $heading . '</h3>';
    }
    $html .= (string) ($item['body'] ?? '');
  }
  return $html;
}

/**
 * @param array{title: string, lede?: string, items: list<array<string, mixed>>, headers?: list<string>} $data
 */
function _motaded_cosmetics_replace_cards(ParagraphInterface $parent, array $data): void {
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
    if (!empty($item['media'])) {
      $values['field_media'] = ['target_id' => (int) $item['media']];
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
  if (!empty($data['headers']) && $parent->hasField('field_sub_title')) {
    $parent->set('field_sub_title', implode('|', $data['headers']));
  }
  $parent->set('field_paragraphs', $children);
  $parent->save();
  foreach ($old as $child) {
    $child->delete();
  }
}
