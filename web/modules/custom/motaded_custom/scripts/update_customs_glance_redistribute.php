<?php

/**
 * @file
 * Removes leftover Glance and redistributes its facts. Renames Scope.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_glance_redistribute.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_GLANCE_ALIAS = '/services/customs-clearance-saudi-arabia';

$node = _motaded_customs_glance_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$refs = [];

foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  if ($paragraph->bundle() === 'glance_block') {
    echo "Removed leftover Service at a Glance.\n";
    continue;
  }

  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $source->hasField('field_title') ? trim((string) $source->get('field_title')->value) : '';

  if ($paragraph->bundle() === 'cards' && $title === 'Customs clearance support for your shipment') {
    _motaded_customs_glance_set_lede($paragraph, 'en', '<p>For companies importing into or exporting from Saudi Arabia, and for the logistics or PRO teams acting for them. Both directions are supported; the documents and FASAH messages differ by direction.</p><p>Send the shipment details. Motaded confirms the applicable requirements, the scope of work and the service fee.</p>');
    _motaded_customs_glance_set_lede($paragraph, 'ar', '<p>للمنشآت التي تستورد إلى المملكة العربية السعودية أو تصدّر منها، ولفرق الخدمات اللوجستية أو العلاقات الحكومية التي تعمل نيابة عنها. يُدعم الاتجاهان؛ تختلف المستندات ورسائل فسح بحسب الاتجاه.</p><p>أرسلوا بيانات الشحنة. يحدّد متعدد المتطلبات المنطبقة ونطاق العمل وأتعاب الخدمة.</p>');
  }

  if ($paragraph->bundle() === 'cards' && in_array($title, ['Scope of Services', 'What Motaded handles'], TRUE) && $title !== 'Customs Clearance Scope of Services') {
    _motaded_customs_glance_apply_cards($paragraph, 'en', [
      'title' => 'What Motaded handles',
      'lede' => '<p>A typical clearance follows this sequence. The exact scope is confirmed in the quotation.</p>',
      'items' => [
        ['title' => 'Review documents', 'body' => '<p>Invoice, packing list, transport document and importer data checked before filing. Gaps are listed in writing.</p>'],
        ['title' => 'Prepare and submit the declaration', 'body' => '<p>Customs declaration prepared and submitted on <a href="/platforms/fasah">FASAH</a>, aligned with the Commercial Register.</p>'],
        ['title' => 'Coordinate additional requirements', 'body' => '<p><a href="/platforms/saber">SABER</a> or <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> requirements identified where they apply. Inspection appointments coordinated when required.</p>'],
        ['title' => 'Follow through to the release decision', 'body' => '<p>The shipment is followed to the authority’s release decision, then handed to the client’s transporter.</p>'],
      ],
    ]);
    _motaded_customs_glance_apply_cards($paragraph, 'ar', [
      'title' => 'ما يتولاه متعدد',
      'lede' => '<p>يتبع التخليص المعتاد هذا التسلسل. يُؤكد النطاق الدقيق في عرض السعر.</p>',
      'items' => [
        ['title' => 'مراجعة المستندات', 'body' => '<p>مراجعة الفاتورة وقائمة التعبئة ومستند النقل وبيانات المستورد قبل التقديم. تُذكر الفجوات كتابةً.</p>'],
        ['title' => 'إعداد البيان وتقديمه', 'body' => '<p>إعداد البيان الجمركي وتقديمه عبر <a href="/platforms/fasah">فسح</a>، بما يتوافق مع السجل التجاري.</p>'],
        ['title' => 'تنسيق المتطلبات الإضافية', 'body' => '<p>تحديد متطلبات <a href="/platforms/saber">سابر</a> أو <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> عند انطباقها. تنسيق مواعيد المعاينة عند الحاجة.</p>'],
        ['title' => 'المتابعة حتى قرار الإفراج', 'body' => '<p>تُتابع الشحنة حتى قرار الإفراج من الجهة، ثم تُسلَّم إلى ناقل العميل.</p>'],
      ],
    ]);
    echo "Updated What Motaded handles.\n";
  }

  if ($paragraph->bundle() === 'cards' && $title === 'Working with Motaded') {
    _motaded_customs_glance_ensure_language_card($paragraph);
    echo "Added Arabic and English to Working with Motaded.\n";
  }

  if ($paragraph->bundle() === 'cards' && $title === 'Fees and Quotation') {
    _motaded_customs_glance_set_lede($paragraph, 'en', '<p>Motaded’s service fee is quoted per shipment and agreed in writing before work begins. Duty, import VAT and official inspection fees are paid to the competent authority. Storage, terminal or warehouse charges are billed by those providers.</p>');
    _motaded_customs_glance_set_lede($paragraph, 'ar', '<p>تُسعَّر أتعاب خدمة متعدد لكل شحنة ويُتفق عليها كتابةً قبل بدء العمل. تُسدَّد الرسوم الجمركية وضريبة القيمة المضافة ورسوم المعاينة الرسمية للجهة المختصة. رسوم التخزين أو الميناء أو المستودع يصدرها مقدّمو تلك الخدمات.</p>');
    echo "Updated Fees and Quotation lede.\n";
  }

  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
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
echo 'Glance redistribute done on /node/' . $node->id() . "\n";

function _motaded_customs_glance_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_GLANCE_ALIAS,
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

function _motaded_customs_glance_set_lede(ParagraphInterface $paragraph, string $langcode, string $lede): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_body', [
    'value' => $lede,
    'format' => 'basic_html',
  ]);
  $tr->save();
}

/**
 * @param array<string, mixed> $copy
 */
function _motaded_customs_glance_apply_cards(ParagraphInterface $paragraph, string $langcode, array $copy): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $copy['title']);
  $tr->set('field_body', [
    'value' => $copy['lede'],
    'format' => 'basic_html',
  ]);
  $tr->set('field_card_type', 'workstreams');
  $tr->save();

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($copy['items'][$i])) {
      continue;
    }
    $row = $copy['items'][$i];
    $card_tr = $card->hasTranslation($langcode)
      ? $card->getTranslation($langcode)
      : $card->addTranslation($langcode, ['status' => $card->isPublished()]);
    $card_tr->set('field_title', $row['title']);
    $card_tr->set('field_body', [
      'value' => $row['body'],
      'format' => 'basic_html',
    ]);
    $card_tr->save();
    $i++;
  }
}

function _motaded_customs_glance_ensure_language_card(ParagraphInterface $paragraph): void {
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface) {
      continue;
    }
    $source = $card->hasTranslation('en') ? $card->getTranslation('en') : $card;
    $title = trim((string) $source->get('field_title')->value);
    if (in_array($title, ['Arabic and English', 'العربية والإنجليزية'], TRUE)) {
      return;
    }
  }

  $card = Paragraph::create([
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => 'Arabic and English',
    'field_body' => [
      'value' => '<p>Documents and status updates in Arabic and English.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $card->save();
  $ar = $card->addTranslation('ar', ['status' => TRUE]);
  $ar->set('field_title', 'العربية والإنجليزية');
  $ar->set('field_body', [
    'value' => '<p>المستندات وتحديثات الحالة بالعربية والإنجليزية.</p>',
    'format' => 'basic_html',
  ]);
  $ar->save();

  $refs = [];
  $inserted = FALSE;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $existing = $item->entity;
    if ($existing instanceof ParagraphInterface && !$inserted && $existing->hasField('field_link') && !$existing->get('field_link')->isEmpty()) {
      $refs[] = [
        'target_id' => $card->id(),
        'target_revision_id' => $card->getRevisionId(),
      ];
      $inserted = TRUE;
    }
    $refs[] = [
      'target_id' => $item->target_id,
      'target_revision_id' => $item->target_revision_id,
    ];
  }
  if (!$inserted) {
    $refs[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $en->set('field_paragraphs', $refs);
  $en->save();
}
