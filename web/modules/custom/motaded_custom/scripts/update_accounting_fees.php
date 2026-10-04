<?php

/**
 * @file
 * Shortens the accounting fees and quotation block.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_fees.php
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

$fees = NULL;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'fees') {
    $fees = $paragraph;
    break;
  }
}
if (!$fees instanceof ParagraphInterface) {
  throw new \RuntimeException('Accounting fees block not found.');
}

$items = [
  [
    'title' => 'The quotation takes into account',
    'body' => '<ul><li>The volume and period of work.</li><li>The condition of existing records.</li><li>The services required.</li></ul>',
  ],
  [
    'title' => 'Your quotation includes',
    'body' => '<ul><li>The agreed work.</li><li>The expected results.</li><li>The timeline.</li><li>The fee.</li><li>Any separate costs.</li></ul><p>Taxes, zakat and any third-party payments are separate from Motaded’s fee.</p>',
    'uri' => 'internal:#assessment',
    'link_title' => 'Request a Quotation',
  ],
];

$old = $fees->get('field_paragraphs')->referencedEntities();
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

$fees->set('field_title', 'Fees and quotation');
$fees->set('field_body', [
  'value' => '<p>You can request a quotation for a one-off assignment or for ongoing bookkeeping support.</p><p>The quotation shows what you are ordering and the fee, so you can agree before work starts.</p>',
  'format' => 'basic_html',
]);
$fees->set('field_paragraphs', $children);
$fees->save();
foreach ($old as $child) {
  $child->delete();
}

$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if ((int) $paragraph->id() === (int) $fees->id()) {
    $refs[] = [
      'target_id' => $fees->id(),
      'target_revision_id' => $fees->getRevisionId(),
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
echo "Updated Fees and quotation on /node/{$nid}.\n";
