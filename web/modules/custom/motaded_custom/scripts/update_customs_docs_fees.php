<?php

/**
 * @file
 * Tightens documents, fees and form copy on the live customs landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_docs_fees.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$docs_en = [
  'title' => 'Documents required for customs clearance',
  'lede' => '<p>The documents listed below are typically required for customs clearance in Saudi Arabia. Available documents are reviewed and any outstanding items are confirmed in writing.</p>',
  'items' => [
    [
      'title' => 'Documents for customs clearance',
      'body' => '<ul><li>Commercial invoice</li><li>Packing list</li><li>Transport document (bill of lading or air waybill)</li><li>Company registration details</li><li>Product certificates and permits, where required — <a href="/platforms/saber">SABER</a> and <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a>. These depend on the cargo.</li></ul>',
    ],
  ],
];

$docs_ar = [
  'title' => 'المستندات المطلوبة للتخليص الجمركي',
  'lede' => '<p>تُطلب عادةً المستندات المدرجة أدناه للتخليص الجمركي في المملكة العربية السعودية. تُراجع المستندات المتوفرة ويُؤكد كتابةً أي بنود ناقصة.</p>',
  'items' => [
    [
      'title' => 'مستندات التخليص الجمركي',
      'body' => '<ul><li>فاتورة تجارية</li><li>قائمة تعبئة</li><li>مستند النقل (بوليصة شحن أو بوليصة شحن جوي)</li><li>بيانات تسجيل المنشأة</li><li>شهادات المنتج والتصاريح عند الحاجة — <a href="/platforms/saber">سابر</a> و<a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a>. تعتمد على البضاعة.</li></ul>',
    ],
  ],
];

$fees_en = [
  'title' => 'Fees and quotation',
  'lede' => '<p>A written quotation is issued for each consignment. The service fee is agreed before work begins and reflects the cargo, the shipment route and the clearance work required.</p>',
  'items' => [
    [
      'title' => 'Motaded service fee',
      'body' => '<ul><li>Agreed before work begins</li><li>The quotation states what is included and what is paid separately</li></ul>',
    ],
    [
      'title' => 'Additional charges',
      'body' => '<ul><li>Duty and taxes, if they apply — <a href="/platforms/zatca">ZATCA</a></li><li>Inspections and permits, if required</li><li>Terminal and warehouse charges, if they arise</li></ul>',
    ],
  ],
];

$fees_ar = [
  'title' => 'الرسوم وعرض السعر',
  'lede' => '<p>يُصدر عرض سعر مكتوب لكل شحنة. يُتفق على أتعاب الخدمة قبل بدء العمل، وتراعي البضاعة ومسار الشحنة وعمل التخليص المطلوب.</p>',
  'items' => [
    [
      'title' => 'أتعاب خدمة متعدد',
      'body' => '<ul><li>يُتفق عليها قبل بدء العمل</li><li>يبيّن عرض السعر ما هو مشمول وما يُدفع بشكل منفصل</li></ul>',
    ],
    [
      'title' => 'رسوم إضافية',
      'body' => '<ul><li>الرسوم الجمركية والضرائب، إذا انطبقت — <a href="/platforms/zatca">ZATCA</a></li><li>المعاينات والتصاريح، إذا لزم الأمر</li><li>رسوم الميناء والمستودع، إذا نشأت</li></ul>',
    ],
  ],
];

$form_en = '<h2>Request a Consultation</h2>';
$form_ar = '<h2>طلب استشارة</h2>';

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/customs-clearance-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Customs landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$found = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }

  if ($paragraph->bundle() === 'webform') {
    _motaded_customs_docs_fees_set_body($paragraph, 'en', $form_en, 'full_html');
    _motaded_customs_docs_fees_set_body($paragraph, 'ar', $form_ar, 'full_html');
    $found[] = 'form';
    continue;
  }

  if ($paragraph->bundle() !== 'cards') {
    continue;
  }
  $en_p = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = trim((string) $en_p->get('field_title')->value);

  if (in_array($title, [
    'Requirements and Documents',
    'What documents do you need?',
    'Documents required for customs clearance',
  ], TRUE)) {
    _motaded_customs_docs_fees_apply($paragraph, 'en', $docs_en, 'lists', TRUE);
    _motaded_customs_docs_fees_apply($paragraph, 'ar', $docs_ar, 'lists', FALSE);
    $found[] = 'documents';
  }

  if (in_array($title, [
    'Fees and Quotation',
    'Fees and quotation',
    'How much does customs clearance cost?',
  ], TRUE)) {
    _motaded_customs_docs_fees_apply($paragraph, 'en', $fees_en, 'fees', FALSE);
    _motaded_customs_docs_fees_apply($paragraph, 'ar', $fees_ar, 'fees', FALSE);
    $found[] = 'fees';
  }
}

if ($found === []) {
  throw new \RuntimeException('Documents, fees or form paragraph not found.');
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo 'Updated ' . implode(', ', $found) . ' on /node/' . $nid . "\n";

/**
 * @param array<string, mixed> $copy
 */
function _motaded_customs_docs_fees_apply(ParagraphInterface $paragraph, string $langcode, array $copy, string $type, bool $trim_children): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $copy['title']);
  $tr->set('field_body', [
    'value' => $copy['lede'],
    'format' => 'basic_html',
  ]);
  $tr->set('field_card_type', $type);

  $children = [];
  foreach ($paragraph->get('field_paragraphs') as $item) {
    if ($item->entity instanceof ParagraphInterface) {
      $children[] = $item->entity;
    }
  }

  $keep = [];
  foreach ($copy['items'] as $i => $row) {
    if (!isset($children[$i])) {
      continue;
    }
    $card = $children[$i];
    $card_tr = $card->hasTranslation($langcode)
      ? $card->getTranslation($langcode)
      : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
    $card_tr->set('field_title', $row['title']);
    $card_tr->set('field_body', [
      'value' => $row['body'],
      'format' => 'basic_html',
    ]);
    $card_tr->save();
    $keep[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  if ($trim_children) {
    $tr->set('field_paragraphs', $keep);
  }
  $tr->save();
}

function _motaded_customs_docs_fees_set_body(ParagraphInterface $paragraph, string $langcode, string $html, string $format): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_body', [
    'value' => $html,
    'format' => $format,
  ]);
  $tr->save();
}
