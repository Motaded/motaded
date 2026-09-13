<?php

/**
 * @file
 * Updates Scope / Working / Authorities copy on the live customs landing.
 *
 * Does not rebuild the page. Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_scope_working_authorities.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_LAYOUT_ALIAS = '/customs-clearance-saudi-arabia';

$updates = [
  'Working with Motaded' => [
    'en' => [
      'lede' => '<p>Riyadh-based consultancy, established 2017. More on <a href="/about-us">About Motaded</a>.</p>',
      'items' => [
        [
          'title' => 'One point of contact',
          'body' => '<p>A dedicated coordinator for your documents, customs declaration and shipment updates.</p>',
        ],
        [
          'title' => 'Clear scope and fees',
          'body' => '<p>The work and service fee are agreed before your clearance engagement begins.</p>',
        ],
        [
          'title' => 'Updates at key stages',
          'body' => '<p>Know when documents, inspections or payments need your attention, and when release is confirmed.</p>',
        ],
      ],
    ],
    'ar' => [
      'lede' => '<p>استشارات مقرها الرياض، تأسست عام 2017. المزيد في <a href="/about-us">عن متعدد</a>.</p>',
      'items' => [
        [
          'title' => 'جهة اتصال واحدة',
          'body' => '<p>منسّق مخصص للمستندات والبيان الجمركي وتحديثات الشحنة.</p>',
        ],
        [
          'title' => 'نطاق ورسوم واضحة',
          'body' => '<p>يُتفق على العمل وأتعاب الخدمة قبل بدء ارتباط التخليص.</p>',
        ],
        [
          'title' => 'تحديثات في المراحل الرئيسية',
          'body' => '<p>تعرفون متى تحتاج المستندات أو المعاينات أو المدفوعات إلى انتباهكم، ومتى يُؤكد الإفراج.</p>',
        ],
      ],
    ],
  ],
  'Relevant Authorities and Platforms' => [
    'en' => [
      'lede' => '<p>Motaded coordinates the shipment through the official channels that apply to the goods.</p>',
      'items' => [
        ['title' => 'ZATCA', 'body' => '<p>Customs procedures, duty and import VAT.</p>'],
        ['title' => 'FASAH', 'body' => '<p>National platform for the customs declaration. Importer data must match the Commercial Register.</p>'],
        ['title' => 'SABER / SASO', 'body' => '<p>Product conformity certificates where a technical regulation applies — before arrival, not instead of the declaration.</p>'],
        ['title' => 'SFDA', 'body' => '<p>Additional controls for food, medicines, medical devices and cosmetics.</p>'],
      ],
    ],
    'ar' => [
      'lede' => '<p>ينسّق متعدد الشحنة عبر القنوات الرسمية المنطبقة على البضاعة.</p>',
      'items' => [
        ['title' => 'ZATCA', 'body' => '<p>الإجراءات الجمركية والرسوم وضريبة القيمة المضافة على الاستيراد.</p>'],
        ['title' => 'فسح', 'body' => '<p>المنصة الوطنية للبيان الجمركي. يجب أن تطابق بيانات المستورد السجل التجاري.</p>'],
        ['title' => 'سابر / ساسو', 'body' => '<p>شهادات مطابقة المنتج عند انطباق لائحة فنية — قبل الوصول، وليست بديلاً عن البيان.</p>'],
        ['title' => 'SFDA', 'body' => '<p>رقابة إضافية على الأغذية والأدوية والأجهزة الطبية ومستحضرات التجميل.</p>'],
      ],
    ],
  ],
];

$node = _motaded_customs_layout_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$found = [];
foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
    continue;
  }
  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = trim((string) $source->get('field_title')->value);
  if (!isset($updates[$title])) {
    continue;
  }
  _motaded_customs_layout_apply($paragraph, $updates[$title]);
  $found[] = $title;
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $node->id()]);
echo 'Updated cards on /node/' . $node->id() . ': ' . implode(', ', $found) . "\n";

function _motaded_customs_layout_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_LAYOUT_ALIAS,
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
 * @param array<string, array<string, mixed>> $langs
 */
function _motaded_customs_layout_apply(ParagraphInterface $paragraph, array $langs): void {
  foreach (['en', 'ar'] as $langcode) {
    if (!isset($langs[$langcode])) {
      continue;
    }
    $data = $langs[$langcode];
    $tr = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
    $tr->set('field_body', [
      'value' => $data['lede'],
      'format' => 'basic_html',
    ]);
    $tr->save();
  }

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface) {
      continue;
    }
    foreach (['en', 'ar'] as $langcode) {
      if (!isset($langs[$langcode]['items'][$i])) {
        continue;
      }
      $row = $langs[$langcode]['items'][$i];
      $tr = $card->hasTranslation($langcode)
        ? $card->getTranslation($langcode)
        : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
      $tr->set('field_title', $row['title']);
      $tr->set('field_body', [
        'value' => $row['body'],
        'format' => 'basic_html',
      ]);
      $tr->save();
    }
    $i++;
  }
}
