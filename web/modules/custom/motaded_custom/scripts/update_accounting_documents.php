<?php

/**
 * @file
 * Shortens the accounting documents block and moves the start note into the form.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_documents.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
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

$docs = NULL;
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
  if ($paragraph->bundle() === 'webform') {
    $form = $paragraph;
  }
}
if (!$docs instanceof ParagraphInterface) {
  throw new \RuntimeException('Accounting documents block not found.');
}

$items = [
  [
    'title' => '',
    'body' => '<ul><li>Company and tax registration details.</li><li>Existing accounting records and financial statements.</li><li>Invoices, bank statements and expense records.</li><li>Depending on the service: payroll records, inventory lists or previous tax returns.</li></ul>',
  ],
  [
    'title' => '',
    'body' => '<p>Incomplete records do not prevent you from contacting us. Motaded will tell you what still needs to be added.</p>',
  ],
];

$old = $docs->get('field_paragraphs')->referencedEntities();
$children = [];
foreach ($items as $item) {
  $card = Paragraph::create([
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $item['title'],
    'field_body' => [
      'value' => $item['body'],
      'format' => 'basic_html',
    ],
  ]);
  $card->save();
  $children[] = [
    'target_id' => $card->id(),
    'target_revision_id' => $card->getRevisionId(),
  ];
}
$docs->set('field_title', 'Documents we may need');
$docs->set('field_body', [
  'value' => '',
  'format' => 'basic_html',
]);
$docs->set('field_paragraphs', $children);
$docs->save();
foreach ($old as $child) {
  $child->delete();
}

if ($form instanceof ParagraphInterface) {
  $form->set('field_body', [
    'value' => '<h2>Request a Consultation</h2>',
    'format' => 'full_html',
  ]);
  $form->save();
}

$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $id = (int) $paragraph->id();
  if ($id === (int) $docs->id()) {
    $refs[] = [
      'target_id' => $docs->id(),
      'target_revision_id' => $docs->getRevisionId(),
    ];
    continue;
  }
  if ($form instanceof ParagraphInterface && $id === (int) $form->id()) {
    $refs[] = [
      'target_id' => $form->id(),
      'target_revision_id' => $form->getRevisionId(),
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
echo "Updated documents and form copy on /node/{$nid}.\n";
