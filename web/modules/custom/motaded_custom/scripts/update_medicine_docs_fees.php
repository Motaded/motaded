<?php

/**
 * @file
 * Rewrites medicine documents and fees, and moves the start note into the form.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_medicine_docs_fees.php
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

$docs = NULL;
$fees = NULL;
$form = NULL;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'lists') {
    $docs = $paragraph;
  }
  if ($type === 'fees') {
    $fees = $paragraph;
  }
  if ($paragraph->bundle() === 'webform') {
    $form = $paragraph;
  }
}
if (!$docs instanceof ParagraphInterface || !$fees instanceof ParagraphInterface) {
  throw new \RuntimeException('Medicine documents or fees block not found.');
}

_motaded_medicine_docs_fees_replace($docs, $copy['documents']);
_motaded_medicine_docs_fees_replace($fees, $copy['fees']);

if ($form instanceof ParagraphInterface) {
  $form->set('field_body', [
    'value' => $copy['form']['body'],
    'format' => 'full_html',
  ]);
  $form->save();
}

$updated = [
  (int) $docs->id() => $docs,
  (int) $fees->id() => $fees,
];
if ($form instanceof ParagraphInterface) {
  $updated[(int) $form->id()] = $form;
}

$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $id = (int) $paragraph->id();
  if (isset($updated[$id])) {
    $refs[] = [
      'target_id' => $updated[$id]->id(),
      'target_revision_id' => $updated[$id]->getRevisionId(),
    ];
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
echo "Updated medicine documents, fees and form on /node/{$nid}.\n";

/**
 * @param array{title: string, lede?: string, items: list<array<string, mixed>>} $data
 */
function _motaded_medicine_docs_fees_replace(ParagraphInterface $parent, array $data): void {
  $old = $parent->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($data['items'] as $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'] ?? '',
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
