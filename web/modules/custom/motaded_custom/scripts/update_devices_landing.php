<?php

/**
 * @file
 * Applies the medicine landing structure to the medical devices page.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_devices_landing.php
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$landings = require dirname(__DIR__) . '/data/sfda_service_landings.dataset.php';
$copy = $landings['devices'];

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card_type');
if ($storage) {
  $allowed = $storage->getSetting('allowed_values') ?: [];
  if ($allowed !== [] && array_is_list($allowed)) {
    $keyed = [];
    foreach ($allowed as $item) {
      if (is_array($item) && isset($item['value'])) {
        $keyed[(string) $item['value']] = (string) ($item['label'] ?? $item['value']);
      }
    }
    $allowed = $keyed;
  }
  $needed = [
    'prose' => 'Service: full-width prose',
    'matrix' => 'Service: support table',
    'formats' => 'Service: engagement formats',
  ];
  $changed = FALSE;
  foreach ($needed as $value => $label) {
    if (!isset($allowed[$value])) {
      $allowed[$value] = $label;
      $changed = TRUE;
    }
  }
  if ($changed) {
    $storage->setSetting('allowed_values', $allowed);
    $storage->save();
  }
}

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/medical-device-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Medical devices landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Medical devices landing node not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

$blocks = [
  'hero_split_banner' => NULL,
  'requirements' => NULL,
  'support' => NULL,
  'mdma' => NULL,
  'representation' => NULL,
  'documents' => NULL,
  'features' => NULL,
  'process' => NULL,
  'stack' => NULL,
  'fees' => NULL,
  'related' => NULL,
  'webform' => NULL,
  'directory' => NULL,
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
  elseif (in_array($title, ['What Determines the Regulatory Requirements?', 'Who this support is for'], TRUE)) {
    $key = 'requirements';
  }
  elseif (in_array($title, ['Which Support Does Your Company Need?', 'When to contact us'], TRUE)) {
    $key = 'support';
  }
  elseif (in_array($title, ['MDMA Application Support', 'How Motaded can help'], TRUE) || $type === 'tabs') {
    $key = 'mdma';
  }
  elseif (in_array($title, ['Authorised Representation and Import Coordination'], TRUE) || $type === 'formats') {
    $key = 'representation';
  }
  elseif (in_array($title, ['Technical Documentation Readiness', 'Documents we may need'], TRUE) || $type === 'lists') {
    $key = 'documents';
  }
  elseif ($title === 'Working with Motaded' || $title === 'Request a Consultation' || $type === 'features') {
    $key = 'features';
  }
  elseif (in_array($title, ['From Document Review to Application Outcome', 'How we work'], TRUE) || $type === 'stages') {
    $key = 'process';
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

foreach (['hero_split_banner', 'requirements', 'support', 'fees', 'related'] as $key) {
  if (!$blocks[$key] instanceof ParagraphInterface) {
    throw new \RuntimeException("Devices {$key} block not found.");
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

_motaded_devices_replace_cards($blocks['requirements'], $copy['requirements']);
$blocks['requirements']->set('field_card_type', 'prose');
$blocks['requirements']->save();
_motaded_devices_replace_cards($blocks['support'], $copy['support']);
$blocks['support']->set('field_card_type', 'matrix');
$blocks['support']->save();

if (!$blocks['mdma'] instanceof ParagraphInterface) {
  $blocks['mdma'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'tabs',
  ]);
  $blocks['mdma']->save();
}
_motaded_devices_replace_cards($blocks['mdma'], $copy['mdma']);
$blocks['mdma']->set('field_card_type', 'tabs');
$blocks['mdma']->save();

if (!$blocks['representation'] instanceof ParagraphInterface) {
  $blocks['representation'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'formats',
  ]);
  $blocks['representation']->save();
}
_motaded_devices_replace_cards($blocks['representation'], $copy['representation']);
$blocks['representation']->set('field_card_type', 'formats');
$blocks['representation']->save();

if (!$blocks['documents'] instanceof ParagraphInterface) {
  $blocks['documents'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'matrix',
  ]);
  $blocks['documents']->save();
}
_motaded_devices_replace_cards($blocks['documents'], $copy['documents']);
$blocks['documents']->set('field_card_type', 'matrix');
$blocks['documents']->save();

if ($blocks['stack'] instanceof ParagraphInterface) {
  foreach ($blocks['stack']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['stack']->delete();
  $blocks['stack'] = NULL;
}

if (!$blocks['features'] instanceof ParagraphInterface) {
  $blocks['features'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'features',
  ]);
  $blocks['features']->save();
}
_motaded_devices_replace_cards($blocks['features'], $copy['features']);
$blocks['features']->set('field_card_type', 'features');
$blocks['features']->save();

if (!$blocks['process'] instanceof ParagraphInterface) {
  $blocks['process'] = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'stages',
  ]);
  $blocks['process']->save();
}
_motaded_devices_replace_cards($blocks['process'], $copy['process']);
$blocks['process']->set('field_card_type', 'stages');
$blocks['process']->save();

_motaded_devices_replace_cards($blocks['fees'], $copy['fees']);
_motaded_devices_replace_cards($blocks['related'], [
  'title' => 'Official guidance and related support',
  'lede' => '',
  'items' => $copy['related_extra'],
]);

foreach ($rest as $paragraph) {
  if ($paragraph->bundle() !== 'accordions') {
    continue;
  }
  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $child_item) {
    $card = $child_item->entity;
    if (!$card instanceof ParagraphInterface || !isset($copy['faq'][$i])) {
      continue;
    }
    $card->set('field_title', $copy['faq'][$i]['question']);
    $card->set('field_body', [
      'value' => $copy['faq'][$i]['answer'],
      'format' => 'basic_html',
    ]);
    $card->save();
    $i++;
  }
}

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
  $blocks['requirements'],
  $blocks['support'],
  $blocks['mdma'],
  $blocks['representation'],
  $blocks['features'],
  $blocks['documents'],
  $blocks['process'],
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
echo "Updated medical devices landing on /node/{$nid}.\n";

/**
 * @param array{title: string, lede?: string, items: list<array<string, mixed>>} $data
 */
function _motaded_devices_replace_cards(ParagraphInterface $parent, array $data): void {
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
    $subtitle = (string) ($item['subtitle'] ?? '');
    if (($item['badge'] ?? '') !== '') {
      $subtitle = ($subtitle !== '' ? $subtitle . '|' : '') . $item['badge'];
    }
    if ($subtitle !== '') {
      $values['field_sub_title'] = $subtitle;
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
  if (!empty($data['headers']) && $parent->hasField('field_sub_title')) {
    $parent->set('field_sub_title', implode('|', $data['headers']));
  }
  $parent->set('field_paragraphs', $children);
  $parent->save();
  foreach ($old as $child) {
    $child->delete();
  }
}
