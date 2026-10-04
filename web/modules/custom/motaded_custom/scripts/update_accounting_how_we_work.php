<?php

/**
 * @file
 * Replaces the accounting process block with How we work.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_how_we_work.php
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

$process = NULL;
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'stack') {
    $process = $paragraph;
    break;
  }
}
if (!$process instanceof ParagraphInterface) {
  throw new \RuntimeException('Accounting process block not found.');
}

$items = [
  [
    'title' => 'Submission of requirements',
    'body' => '<p>The business, the matter, the period concerned and any deadline are stated. A specific accounting, tax or audit service name is not required. The applicable support is identified during consultation.</p>',
  ],
  [
    'title' => 'Review and confirmation of scope',
    'body' => '<p>Available records and information are reviewed. The work, the expected results, the timeline and the fee are agreed in writing before work begins.</p>',
  ],
  [
    'title' => 'Preparation of records and completion of the work',
    'body' => '<p>Requested documents and clarifications are supplied by the client. Motaded completes the agreed work.</p>',
  ],
  [
    'title' => 'Reports and next steps',
    'body' => '<p>The result of the ordered service is issued — for example updated accounting records, financial reports, prepared returns, or a document pack for the appointed auditor.</p><p>Where filing is included, confirmation of submission is issued, together with any further actions still required.</p>',
  ],
];

$old = $process->get('field_paragraphs')->referencedEntities();
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

$process->set('field_title', 'How we work');
$process->set('field_body', [
  'value' => '',
  'format' => 'basic_html',
]);
$process->set('field_paragraphs', $children);
$process->save();
foreach ($old as $child) {
  $child->delete();
}

$refs = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if ((int) $paragraph->id() === (int) $process->id()) {
    $refs[] = [
      'target_id' => $process->id(),
      'target_revision_id' => $process->getRevisionId(),
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
echo "Updated How we work on /node/{$nid}.\n";
