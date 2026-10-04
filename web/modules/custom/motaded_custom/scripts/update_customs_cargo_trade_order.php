<?php

/**
 * @file
 * Lifts cargo after overview, moves trade data to the end, updates copy.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_cargo_trade_order.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_CARGO_ALIAS = '/services/customs-clearance-saudi-arabia';

$cargo_en = [
  'title' => 'Does this cover your cargo?',
  'lede' => '<p>If you import or export goods in the categories below, Motaded can review the shipment and confirm the clearance work that applies. Product-specific requirements are identified during consultation — they are not assumed from the category name alone.</p>',
  'items' => [
    [
      'title' => 'General cargo',
      'body' => '<p>Motaded reviews the commercial documents and prepares the customs declaration. A <a href="/platforms/saber">SABER</a> certificate may also be required where a technical regulation applies.</p>',
    ],
    [
      'title' => 'Food',
      'body' => '<p>Motaded identifies the extra food-control steps for the shipment. <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> rules often apply in addition to the customs declaration, including inspection when SFDA requires it.</p>',
    ],
    [
      'title' => 'Pharma and medical devices',
      'body' => '<p>Motaded confirms the clearance path for the shipment. <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> requirements often apply in addition to the customs declaration.</p>',
    ],
    [
      'title' => 'Cosmetics',
      'body' => '<p>Motaded reviews the shipment and the extra product steps that may apply. <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> requirements often sit alongside the customs declaration.</p>',
    ],
    [
      'title' => 'Electronics and consumer goods',
      'body' => '<p>Motaded flags whether a conformity certificate is needed before arrival. <a href="/platforms/saber">SABER</a> PCoC/SCoC is often required for this category.</p>',
    ],
    [
      'title' => 'Chemicals and restricted goods',
      'body' => '<p>Additional permits may apply. Motaded confirms during consultation whether it can take the shipment and which extra steps are required.</p>',
    ],
  ],
];

$cargo_ar = [
  'title' => 'هل تشمل هذه الفئات شحنتكم؟',
  'lede' => '<p>إذا كنتم تستوردون أو تصدّرون بضائع من الفئات أدناه، يمكن لمتعدد مراجعة الشحنة وتأكيد عمل التخليص المنطبق. تُحدَّد متطلبات المنتج أثناء الاستشارة — ولا تُفترض من اسم الفئة وحدها.</p>',
  'items' => [
    [
      'title' => 'بضائع عامة',
      'body' => '<p>يراجع متعدد المستندات التجارية ويعدّ البيان الجمركي. قد تُطلب أيضاً شهادة <a href="/platforms/saber">سابر</a> عند انطباق لائحة فنية.</p>',
    ],
    [
      'title' => 'أغذية',
      'body' => '<p>يحدد متعدد خطوات الرقابة الغذائية الإضافية للشحنة. كثيراً ما تنطبق قواعد <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> إلى جانب البيان الجمركي، بما في ذلك المعاينة عندما تطلبها الهيئة.</p>',
    ],
    [
      'title' => 'أدوية وأجهزة طبية',
      'body' => '<p>يؤكد متعدد مسار التخليص للشحنة. كثيراً ما تنطبق متطلبات <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> إضافةً إلى البيان الجمركي.</p>',
    ],
    [
      'title' => 'مستحضرات تجميل',
      'body' => '<p>يراجع متعدد الشحنة والخطوات الإضافية التي قد تنطبق على المنتج. كثيراً ما ترافق متطلبات <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> البيان الجمركي.</p>',
    ],
    [
      'title' => 'إلكترونيات وسلع استهلاكية',
      'body' => '<p>يشير متعدد إلى ما إذا كانت شهادة مطابقة لازمة قبل الوصول. غالباً تُطلب شهادة <a href="/platforms/saber">سابر</a> (PCoC/SCoC) لهذه الفئة.</p>',
    ],
    [
      'title' => 'كيماويات وبضائع مقيدة',
      'body' => '<p>قد تنطبق تصاريح إضافية. يؤكد متعدد أثناء الاستشارة ما إذا كان يمكنه تولي الشحنة وأي خطوات إضافية مطلوبة.</p>',
    ],
  ],
];

$node = _motaded_customs_cargo_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$hero = NULL;
$overview = NULL;
$cargo = NULL;
$rest = [];

foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $source->hasField('field_title') ? trim((string) $source->get('field_title')->value) : '';
  $bundle = $paragraph->bundle();
  $ref = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];

  if ($bundle === 'hero_split_banner') {
    $hero = $ref;
    continue;
  }
  if ($bundle === 'cards' && in_array($title, [
    'Customs clearance support for your shipment',
    'Customs clearance for import and export consignments',
    'Service Overview',
  ], TRUE)) {
    $overview = $ref;
    continue;
  }
  if ($bundle === 'cards' && in_array($title, [
    'Cargo Categories and Additional Requirements',
    'Does this cover your cargo?',
    'Cargo categories for customs clearance',
    'Cargo Type and Product Requirements',
  ], TRUE)) {
    $cargo = $ref;
    continue;
  }
  if ($bundle === 'trade_market_overview') {
    _motaded_customs_cargo_set_text($paragraph, 'en', 'Saudi Arabia’s trade in figures', '<p>Merchandise imports and exports for the latest month, compared with the same month last year, and the recent monthly trend.</p>');
    _motaded_customs_cargo_set_text($paragraph, 'ar', 'تجارة المملكة العربية السعودية بالأرقام', '<p>واردات وصادرات السلع لآخر شهر، مقارنة بالشهر نفسه من العام الماضي، مع الاتجاه الشهري الأخير.</p>');
  }
  if ($bundle === 'trade_by_product') {
    continue;
  }
  $rest[] = $ref;
}

$refs = array_values(array_filter([$hero, $overview, $cargo, ...$rest]));
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
echo 'Reordered cargo + trade on /node/' . $node->id() . "\n";

function _motaded_customs_cargo_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_CARGO_ALIAS,
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
function _motaded_customs_cargo_apply(ParagraphInterface $paragraph, string $langcode, array $copy): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $copy['title']);
  $tr->set('field_body', [
    'value' => $copy['lede'],
    'format' => 'basic_html',
  ]);
  $tr->set('field_card_type', 'tabs');
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

function _motaded_customs_cargo_set_text(ParagraphInterface $paragraph, string $langcode, string $title, string $lede): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $title);
  $tr->set('field_body', [
    'value' => $lede,
    'format' => 'basic_html',
  ]);
  $tr->save();
}
