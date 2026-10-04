<?php

/**
 * @file
 * Replaces Service Overview + Glance with a situations block on the live node.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_overview_situations.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_OVERVIEW_ALIAS = '/services/customs-clearance-saudi-arabia';

$en_copy = [
  'title' => 'Customs clearance support for your shipment',
  'lede' => '<p>For companies importing into or exporting from Saudi Arabia, and for the logistics or PRO teams acting for them.</p>'
    . '<p>Send the shipment details. Motaded confirms the applicable requirements, the scope of work and the service fee.</p>',
  'items' => [
    ['title' => 'You are planning an import into Saudi Arabia or an export from the Kingdom.', 'body' => ''],
    ['title' => 'The goods are already in transit, and the documents for clearance still need to be prepared.', 'body' => ''],
    ['title' => 'You are not sure which certificates or permits apply to your product.', 'body' => ''],
    ['title' => 'Your team needs one contact to coordinate the customs clearance.', 'body' => ''],
    [
      'title' => 'Request a Consultation',
      'body' => '',
      'uri' => 'internal:#assessment',
      'link_title' => 'Request a Consultation',
    ],
  ],
];

$ar_copy = [
  'title' => 'دعم التخليص الجمركي لشحنتم',
  'lede' => '<p>للمنشآت التي تستورد إلى المملكة العربية السعودية أو تصدّر منها، ولفرق الخدمات اللوجستية أو العلاقات الحكومية التي تعمل نيابة عنها.</p>'
    . '<p>أرسلوا بيانات الشحنة. يحدّد متعدد المتطلبات المنطبقة ونطاق العمل وأتعاب الخدمة.</p>',
  'items' => [
    ['title' => 'تخطّطون لاستيراد بضائع إلى المملكة العربية السعودية أو لتصديرها منها.', 'body' => ''],
    ['title' => 'البضاعة في الطريق، ولا تزال مستندات التخليص بحاجة إلى إعداد.', 'body' => ''],
    ['title' => 'لستم متأكدين أي شهادات أو تصاريح تنطبق على منتجكم.', 'body' => ''],
    ['title' => 'يحتاج فريقكم جهة اتصال واحدة لتنسيق التخليص الجمركي.', 'body' => ''],
    [
      'title' => 'طلب استشارة',
      'body' => '',
      'link_title' => 'طلب استشارة',
    ],
  ],
];

$node = _motaded_customs_overview_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$refs = [];
$replaced = FALSE;
$existing = NULL;

foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $source->hasField('field_title') ? trim((string) $source->get('field_title')->value) : '';
  $bundle = $paragraph->bundle();

  if ($bundle === 'glance_block') {
    echo "Removed Service at a Glance.\n";
    continue;
  }

  if (
    ($bundle === 'media_lead' && $title === 'Service Overview')
    || ($bundle === 'cards' && in_array($title, ['Service Overview', 'Customs clearance support for your shipment'], TRUE))
  ) {
    if ($bundle === 'cards' && $existing === NULL) {
      $existing = $paragraph;
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
      $replaced = TRUE;
      continue;
    }
    if ($replaced) {
      continue;
    }
    $overview = _motaded_customs_overview_create_cards($en_copy);
    _motaded_customs_overview_apply($overview, 'ar', $ar_copy);
    $refs[] = [
      'target_id' => $overview->id(),
      'target_revision_id' => $overview->getRevisionId(),
    ];
    $replaced = TRUE;
    echo "Replaced {$bundle} «{$title}» with situations block.\n";
    continue;
  }

  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

if ($existing instanceof ParagraphInterface) {
  _motaded_customs_overview_apply($existing, 'en', $en_copy);
  _motaded_customs_overview_apply($existing, 'ar', $ar_copy);
  echo "Updated existing situations block.\n";
}

if (!$replaced) {
  $overview = _motaded_customs_overview_create_cards($en_copy);
  _motaded_customs_overview_apply($overview, 'ar', $ar_copy);
  array_splice($refs, 1, 0, [[
    'target_id' => $overview->id(),
    'target_revision_id' => $overview->getRevisionId(),
  ]]);
  echo "Inserted situations block after the hero.\n";
}

$en = Node::load((int) $node->id());
$en = $en->hasTranslation('en') ? $en->getTranslation('en') : $en;
$en->set('field_paragraphs', $refs);
$en->save();

if ($node->hasTranslation('ar')) {
  $ar = Node::load((int) $node->id())->getTranslation('ar');
  $ar->set('field_paragraphs', $refs);
  $ar->save();
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $node->id()]);
echo 'Overview updated on /node/' . $node->id() . "\n";

function _motaded_customs_overview_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_OVERVIEW_ALIAS,
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if (!$alias instanceof PathAlias) {
    return NULL;
  }
  $nid = (int) str_replace('/node/', '', $alias->getPath());
  $node = Node::load($nid);
  return $node instanceof Node ? $node : NULL;
}

/**
 * @param array<string, mixed> $copy
 */
function _motaded_customs_overview_create_cards(array $copy): Paragraph {
  $children = [];
  foreach ($copy['items'] as $item) {
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
        'title' => $item['link_title'] ?? $item['title'],
      ];
    }
    $card = Paragraph::create($values);
    $card->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  $cards = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'situations',
    'field_title' => $copy['title'],
    'field_body' => [
      'value' => $copy['lede'],
      'format' => 'basic_html',
    ],
    'field_paragraphs' => $children,
  ]);
  $cards->save();
  return $cards;
}

/**
 * @param array<string, mixed> $copy
 */
function _motaded_customs_overview_apply(ParagraphInterface $paragraph, string $langcode, array $copy): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $copy['title']);
  $tr->set('field_body', [
    'value' => $copy['lede'],
    'format' => 'basic_html',
  ]);
  $tr->set('field_card_type', 'situations');
  $tr->save();

  $existing = [];
  foreach ($paragraph->get('field_paragraphs') as $item) {
    if ($item->entity instanceof ParagraphInterface) {
      $existing[] = $item->entity;
    }
  }

  $refs = [];
  foreach ($copy['items'] as $i => $row) {
    $card = $existing[$i] ?? Paragraph::create([
      'type' => 'card',
      'langcode' => 'en',
    ]);
    if ($card->isNew()) {
      $card->save();
    }
    $card_tr = $card->hasTranslation($langcode)
      ? $card->getTranslation($langcode)
      : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
    $card_tr->set('field_title', $row['title']);
    $card_tr->set('field_body', [
      'value' => $row['body'],
      'format' => 'basic_html',
    ]);
    if (($row['uri'] ?? '') !== '' || ($row['link_title'] ?? '') !== '') {
      $link = $card->hasField('field_link') ? $card->get('field_link')->getValue() : [];
      if ($link === []) {
        $link = [[
          'uri' => $row['uri'] ?? 'internal:#assessment',
          'title' => $row['link_title'] ?? $row['title'],
        ]];
      }
      else {
        $link[0]['title'] = $row['link_title'] ?? $row['title'];
        if (($row['uri'] ?? '') !== '') {
          $link[0]['uri'] = $row['uri'];
        }
      }
      $card_tr->set('field_link', $link);
    }
    $card_tr->save();
    $refs[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $en->set('field_paragraphs', $refs);
  $en->save();
}
