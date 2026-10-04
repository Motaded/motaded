<?php

/**
 * @file
 * Adds Service:* card types and sets them on the customs landing paragraphs.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/update_service_card_layouts.php
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$layouts = [
  'workstreams' => 'Service: icon columns',
  'features' => 'Service: dark split panel',
  'directory' => 'Service: logo directory',
  'stack' => 'Service: numbered stages',
  'stages' => 'Service: vertical stage list',
  'table' => 'Service: table',
  'lists' => 'Service: document lists',
  'fees' => 'Service: fees columns',
  'invite' => 'Service: mid-page CTA',
  'contact' => 'Service: contact line',
  'related' => 'Service: related links',
  'eligibility' => 'Service: overview and eligibility',
  'context' => 'Service: sector context',
  'formats' => 'Service: engagement formats',
  'prose' => 'Service: full-width prose',
  'matrix' => 'Service: support table',
];

$storage = FieldStorageConfig::loadByName('paragraph', 'field_card_type');
if (!$storage) {
  throw new \RuntimeException('paragraph.field_card_type storage is missing.');
}
$allowed = $storage->getSetting('allowed_values') ?? [];
if ($allowed !== [] && array_is_list($allowed)) {
  $keyed = [];
  foreach ($allowed as $item) {
    if (is_array($item) && isset($item['value'])) {
      $keyed[(string) $item['value']] = (string) ($item['label'] ?? $item['value']);
    }
  }
  $allowed = $keyed;
}
$changed = FALSE;
foreach ($layouts as $value => $label) {
  if (!isset($allowed[$value])) {
    $allowed[$value] = $label;
    $changed = TRUE;
  }
}
if ($changed) {
  $storage->setSetting('allowed_values', $allowed);
  $storage->save();
  echo "Added Service:* values to paragraph.field_card_type.\n";
}

$field = FieldConfig::loadByName('paragraph', 'cards', 'field_card_type');
if ($field && $field->getDescription() === '') {
  $field->setDescription('On a Service landing, pick a Service:* option to style this section. Other types keep the default cards layout.');
  $field->save();
}

$title_map = [
  'Clearance Process' => 'stack',
  'إجراءات التخليص' => 'stack',
  'How your customs clearance works' => 'stack',
  'Customs clearance procedure' => 'stack',
  'كيف يعمل التخليص الجمركي لديكم' => 'stack',
  'إجراءات التخليص الجمركي' => 'stack',
  'Scope of Services' => 'workstreams',
  'نطاق الخدمات' => 'workstreams',
  'What Motaded handles' => 'tabs',
  'Customs Clearance Scope of Services' => 'tabs',
  'ما يتولاه متعدد' => 'tabs',
  'نطاق خدمات التخليص الجمركي' => 'tabs',
  'Relevant Authorities and Platforms' => 'directory',
  'الجهات والمنصات ذات الصلة' => 'directory',
  'Cargo Categories and Additional Requirements' => 'needs',
  'فئات الشحنات والمتطلبات الإضافية' => 'needs',
  'Does this cover your cargo?' => 'tabs',
  'Cargo categories for customs clearance' => 'matrix',
  'Cargo Type and Product Requirements' => 'matrix',
  'هل تشمل هذه الفئات شحنتكم؟' => 'tabs',
  'فئات البضائع للتخليص الجمركي' => 'matrix',
  'نوع الشحنة ومتطلبات المنتج' => 'matrix',
  'Customs clearance for import and export consignments' => 'overview',
  'Customs clearance support for your shipment' => 'overview',
  'Service Overview' => 'overview',
  'التخليص الجمركي لشحنات الاستيراد والتصدير' => 'overview',
  'نظرة عامة على الخدمة' => 'overview',
  'Requirements and Documents' => 'lists',
  'المتطلبات والمستندات' => 'lists',
  'What documents do you need?' => 'lists',
  'Documents required for customs clearance' => 'lists',
  'ما المستندات التي تحتاجونها؟' => 'lists',
  'المستندات المطلوبة للتخليص الجمركي' => 'lists',
  'Fees and Quotation' => 'fees',
  'الرسوم وعرض السعر' => 'fees',
  'How much does customs clearance cost?' => 'fees',
  'كم تكلفة التخليص الجمركي؟' => 'fees',
  'Fees and quotation' => 'fees',
  'الرسوم وعرض السعر' => 'fees',
  'Working with Motaded' => 'features',
  'Accounting Coordination and Progress Updates' => 'features',
  'التنسيق المحاسبي وتحديثات التقدم' => 'features',
  'Service Scope and Client Coordination' => 'features',
  'How we work' => 'stack',
  'Accounting Engagement Process' => 'stages',
  'عملية الارتباط المحاسبي' => 'stages',
  'Application Preparation and Follow-up' => 'stages',
  'Documents we may need' => 'lists',
  'Pharmaceutical Product and Company Documentation' => 'lists',
  'Fees and quotation' => 'fees',
  'Service Fees and Regulatory Charges' => 'fees',
  'How Motaded can help' => 'tabs',
  'Drug Registration, Establishment Licensing and Import Support' => 'tabs',
  'Who this support is for' => 'needs',
  'Who the service is intended for' => 'needs',
  'Pharmaceutical Services for Manufacturers, Importers and Distributors' => 'needs',
  'Service Overview and Eligibility' => 'eligibility',
  'نظرة عامة على الخدمة وأهلية الاستفادة' => 'eligibility',
  'Medicines in Saudi Arabia' => 'context',
  'الأدوية في المملكة العربية السعودية' => 'context',
  'Accounting and Tax Compliance in Saudi Arabia' => 'context',
  'المحاسبة والامتثال الضريبي في المملكة العربية السعودية' => 'context',
  'What Determines the Regulatory Requirements?' => 'prose',
  'Which Support Does Your Company Need?' => 'matrix',
  'MDMA Application Support' => 'tabs',
  'Authorised Representation and Import Coordination' => 'formats',
  'Technical Documentation Readiness' => 'matrix',
  'From Document Review to Application Outcome' => 'stages',
  'ما الذي يحدد المتطلبات التنظيمية؟' => 'prose',
  'أي دعم تحتاجه منشأتك؟' => 'matrix',
  'دعم طلب MDMA' => 'tabs',
  'التمثيل النظامي وتنسيق الاستيراد' => 'formats',
  'جاهزية المستندات الفنية' => 'matrix',
  'من مراجعة المستندات إلى نتيجة الطلب' => 'stages',
  'الرسوم وعرض السعر' => 'fees',
  'Support for Cosmetic Brands, Manufacturers and Importers' => 'matrix',
  'Support for Brands, Manufacturers and Importers' => 'formats',
  'الدعم للعلامات والمصنّعين والمستوردين' => 'formats',
  'Service Deliverables' => 'formats',
  'مخرجات الخدمة' => 'formats',
  'Cosmetic Product Listing Support' => 'formats',
  'دعم إدراج منتجات التجميل' => 'formats',
  'Cosmetics and Personal-Care Products in Saudi Arabia' => 'prose',
  'مستحضرات التجميل ومنتجات العناية الشخصية في المملكة العربية السعودية' => 'prose',
  'Product Composition, Claims and Labelling' => 'prose',
  'تركيب المنتج والادعاءات والبطاقات' => 'prose',
  'Request a Consultation' => 'features',
  'طلب استشارة' => 'features',
  'From Product Review to Listing Submission' => 'stages',
  'من مراجعة المنتج إلى تقديم الإدراج' => 'stages',
  'Product Information and Documentation' => 'matrix',
  'معلومات المنتج والمستندات' => 'matrix',
  'Ongoing Accounting and One-Off Assignments' => 'formats',
  'المحاسبة المستمرة والمهام لمرة واحدة' => 'formats',
  'دعم تسجيل الأدوية وترخيص المنشآت وتنسيق الاستيراد' => 'tabs',
  'نطاق الخدمة والتنسيق مع العميل' => 'features',
  'إعداد الطلب والمتابعة' => 'stages',
  'مستندات المنتج والمنشأة الصيدلانية' => 'lists',
  'أتعاب الخدمة والرسوم التنظيمية' => 'fees',
  'الإرشادات الرسمية والدعم ذو الصلة' => 'related',
  'When to contact us' => 'situations',
  'When the service applies' => 'situations',
  'Accounting Challenges We Address' => 'situations',
  'التحديات المحاسبية التي نعالجها' => 'situations',
  'Support for New and Existing Pharmaceutical Applications' => 'situations',
  'العمل مع متعدد' => 'features',
  'Accounting Support at Every Stage of Your Business' => 'needs',
  'الدعم المحاسبي في كل مرحلة من مراحل أعمالكم' => 'needs',
  'Related Motaded pages' => 'related',
  'صفحات متعدد ذات الصلة' => 'related',
  'Contact Our Customs Clearance Team' => 'contact',
  'تواصل مع فريق التخليص الجمركي' => 'contact',
  'Talk to the clearance team' => 'invite',
  'تحدث مع فريق التخليص' => 'invite',
  'Start with a written assessment' => 'invite',
  'ابدأ بتقييم مكتوب' => 'invite',
  'Start with a consultation' => 'invite',
  'Request a written consultation' => 'invite',
  'طلب استشارة مكتوبة' => 'invite',
  'ابدأ باستشارة' => 'invite',
];

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

$updated = 0;
foreach ($node->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
    continue;
  }
  foreach (['en', 'ar'] as $langcode) {
    $translation = $paragraph->hasTranslation($langcode)
      ? $paragraph->getTranslation($langcode)
      : $paragraph;
    $title = $translation->hasField('field_title')
      ? trim((string) $translation->get('field_title')->value)
      : '';
    $layout = $title_map[$title] ?? '';
    if ($layout === '') {
      continue;
    }
    $translation->set('field_card_type', $layout);
    $translation->save();
    $updated++;
    echo "Set {$layout} on paragraph {$translation->id()} ({$langcode}: {$title}).\n";
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Updated {$updated} cards translations on nid={$nid}.\n";
