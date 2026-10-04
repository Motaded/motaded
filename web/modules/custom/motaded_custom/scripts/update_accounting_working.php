<?php

/**
 * @file
 * Rewrites the accounting Working with Motaded block.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_working.php
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

$working = NULL;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'features') {
    $working = $paragraph;
    break;
  }
}
if (!$working instanceof ParagraphInterface) {
  throw new \RuntimeException('Accounting working block not found.');
}

$items = [
  [
    'title' => 'One point of contact',
    'body' => '<p>You know who to contact for documents, questions and clarifications.</p>',
  ],
  [
    'title' => 'Scope and fees agreed before work starts',
    'body' => '<p>You know the agreed work, the expected results and the fee before anything begins.</p>',
  ],
  [
    'title' => 'Clear reports and next steps',
    'body' => '<p>You receive the agreed materials and an explanation of anything that still needs your attention.</p>',
  ],
  [
    'title' => 'Request a Consultation',
    'body' => '',
    'uri' => 'internal:#assessment',
    'link_title' => 'Request a Consultation',
  ],
  [
    'title' => 'WhatsApp',
    'body' => '',
    'uri' => 'https://wa.me/966539797197',
    'link_title' => 'WhatsApp',
  ],
];

$old = $working->get('field_paragraphs')->referencedEntities();
$children = [];
foreach ($items as $item) {
  $values = [
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $item['title'],
    'field_body' => [
      'value' => $item['body'],
      'format' => 'basic_html',
    ],
  ];
  if (($item['uri'] ?? '') !== '') {
    $values['field_link'] = [
      'uri' => $item['uri'],
      'title' => $item['link_title'],
    ];
  }
  $card = Paragraph::create($values);
  $card->save();
  $children[] = [
    'target_id' => $card->id(),
    'target_revision_id' => $card->getRevisionId(),
  ];
}

$working->set('field_title', 'Working with Motaded');
$working->set('field_body', [
  'value' => '',
  'format' => 'basic_html',
]);
$working->set('field_paragraphs', $children);
$working->save();
foreach ($old as $child) {
  $child->delete();
}

$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if ((int) $paragraph->id() === (int) $working->id()) {
    $refs[] = [
      'target_id' => $working->id(),
      'target_revision_id' => $working->getRevisionId(),
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
echo "Updated Working with Motaded on /node/{$nid}.\n";
