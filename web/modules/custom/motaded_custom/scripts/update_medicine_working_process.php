<?php

/**
 * @file
 * Removes medicine authorities block and rewrites Working / How we work.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_medicine_working_process.php
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

$blocks = [
  'catalogue' => NULL,
  'directory' => NULL,
  'features' => NULL,
  'stack' => NULL,
  'fees' => NULL,
  'related' => NULL,
];
$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  $title = $paragraph->hasField('field_title') ? trim((string) $paragraph->get('field_title')->value) : '';
  if ($type === 'related' || $title === 'Official guidance and related support') {
    $type = 'related';
  }
  if (array_key_exists($type, $blocks)) {
    $blocks[$type] = $paragraph;
  }
  if ($type === 'directory') {
    continue;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

foreach (['catalogue', 'features', 'stack', 'fees', 'related'] as $key) {
  if (!$blocks[$key] instanceof ParagraphInterface) {
    throw new \RuntimeException("Medicine {$key} block not found.");
  }
}

_motaded_medicine_replace_cards($blocks['catalogue'], $copy['catalogue']);
_motaded_medicine_replace_cards($blocks['features'], $copy['features']);
_motaded_medicine_replace_cards($blocks['stack'], $copy['process']);
_motaded_medicine_replace_cards($blocks['fees'], $copy['fees']);
_motaded_medicine_replace_cards($blocks['related'], [
  'title' => 'Official guidance and related support',
  'lede' => '',
  'items' => $copy['related_extra'],
]);

if ($blocks['directory'] instanceof ParagraphInterface) {
  foreach ($blocks['directory']->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $blocks['directory']->delete();
}

$updated = [
  (int) $blocks['catalogue']->id() => $blocks['catalogue'],
  (int) $blocks['features']->id() => $blocks['features'],
  (int) $blocks['stack']->id() => $blocks['stack'],
  (int) $blocks['fees']->id() => $blocks['fees'],
  (int) $blocks['related']->id() => $blocks['related'],
];
foreach ($refs as $i => $ref) {
  $id = (int) $ref['target_id'];
  if (isset($updated[$id])) {
    $refs[$i]['target_revision_id'] = $updated[$id]->getRevisionId();
  }
}

$source->set('field_paragraphs', $refs);
$source->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Updated medicine working and process blocks on /node/{$nid}.\n";

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
