<?php

/**
 * @file
 * Drops the authorities block and retargets the process block on the live node.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_process_drop_authorities.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_PROCESS_ALIAS = '/services/customs-clearance-saudi-arabia';

$process_en = [
  'title' => 'Customs clearance procedure',
  'lede' => '<p>Customs clearance in Saudi Arabia proceeds in the stages below. Duration depends on document completeness, cargo category and any inspection appointments. A written indication of timing is provided with the quotation after the consignment details have been reviewed.</p>',
  'items' => [
    [
      'title' => 'Submission of consignment details',
      'body' => _motaded_customs_process_stage(
        'Company details, trade direction, cargo category and available commercial documents. A complete file is not required at this stage.',
        'The submitted information is reviewed. Applicable documents, approvals and actions are confirmed in writing, and a scope of work is proposed.',
        'Written service quotation for acceptance.'
      ),
    ],
    [
      'title' => 'Confirmation of scope and preparation of documents',
      'body' => _motaded_customs_process_stage(
        'Acceptance of the written quotation, together with the commercial documents already held (invoice, packing list, bill of lading or air waybill, Commercial Register data, and any SABER or SFDA evidence already available).',
        'The agreed scope is confirmed. Remaining documents or certificates required before filing are listed in writing.',
        'Documents ready for the declaration, or a written list of outstanding items.'
      ),
    ],
    [
      'title' => 'Declaration and required checks',
      'body' => _motaded_customs_process_stage(
        'Confirmation to file, and any clarifications requested by the authority.',
        'Submits the declaration on <a href="/platforms/fasah">FASAH</a>, follows it with <a href="/platforms/zatca">ZATCA</a>, and coordinates <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or <a href="/platforms/saber">SABER</a> inspection where required.',
        'Any official charges issued, and the release decision.'
      ),
    ],
    [
      'title' => 'Payment, release and handover',
      'body' => _motaded_customs_process_stage(
        'Payment of official duty, tax and any inspection fees through the authority’s channels; nomination of the transporter.',
        'Confirms that official charges are paid; follows the authority’s release decision; then hands the shipment to the nominated transporter.',
        'Completion confirmed to the named contact.'
      ),
    ],
  ],
];

$process_ar = [
  'title' => 'كيف يعمل التخليص الجمركي لديكم',
  'lede' => '<p>يوضح التسلسل أدناه كيف يبدأ ارتباط التخليص وما يحتاجه متعدد منكم في كل مرحلة. تعتمد المدة على اكتمال المستندات وفئة الشحنة وأي مواعيد معاينة. يعطي متعدد توجيهاً مكتوباً بالمدة مع عرض السعر بعد مراجعة بيانات الشحنة.</p>',
  'items' => [
    [
      'title' => 'أرسلوا بيانات الشحنة',
      'body' => _motaded_customs_process_stage_ar(
        'بيانات المنشأة، اتجاه التجارة، فئة الشحنة، والمستندات التجارية المتوفرة لديكم. يمكن البدء بالمعلومات المتاحة الآن — لا يُشترط ملف مكتمل.',
        'يراجع ما ترسلونه ويؤكد كتابةً المستندات والموافقات والإجراءات اللازمة لشحنتكم، ثم يقترح نطاق العمل.',
        'عرض سعر مكتوب للقبول.'
      ),
    ],
    [
      'title' => 'تأكيد النطاق وإعداد المستندات',
      'body' => _motaded_customs_process_stage_ar(
        'قبول عرض السعر المكتوب، والمستندات التجارية المتوفرة (الفاتورة، قائمة التعبئة، بوليصة الشحن أو بوليصة الشحن الجوي، بيانات السجل التجاري، وأي إثباتات سابر أو الهيئة العامة للغذاء والدواء المتوفرة).',
        'يؤكد النطاق المتفق عليه ويبيّن كتابةً المستندات أو الشهادات المتبقية التي يجب إعدادها قبل التقديم.',
        'مستندات جاهزة للبيان، أو قائمة مكتوبة بالمعلقات.'
      ),
    ],
    [
      'title' => 'البيان والفحوصات المطلوبة',
      'body' => _motaded_customs_process_stage_ar(
        'تأكيد التقديم، وأي إيضاحات تطلبها الجهة.',
        'يقدّم البيان عبر <a href="/platforms/fasah">فسح</a>، ويتابعه مع <a href="/platforms/zatca">ZATCA</a>، وينسّق معاينة <a href="/platforms/saudi-food-and-drug-authority-sfda">الهيئة العامة للغذاء والدواء</a> أو <a href="/platforms/saber">سابر</a> عند الحاجة.',
        'أي رسوم رسمية إن صدرت، وقرار الإفراج.'
      ),
    ],
    [
      'title' => 'السداد والإفراج والتسليم',
      'body' => _motaded_customs_process_stage_ar(
        'سداد الرسوم الجمركية والضريبة وأي رسوم معاينة عبر قنوات الجهة؛ وترشيح الناقل.',
        'يؤكد سداد الرسوم الرسمية؛ ويتابع قرار الإفراج من الجهة؛ ثم يسلّم الشحنة إلى الناقل المعيّن.',
        'تأكيد الإنجاز لجهة الاتصال المحددة.'
      ),
    ],
  ],
];

$node = _motaded_customs_process_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$refs = [];
$removed = [];
$process_updated = FALSE;

foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = $source->hasField('field_title') ? trim((string) $source->get('field_title')->value) : '';

  if ($paragraph->bundle() === 'cards' && in_array($title, [
    'Relevant Authorities and Platforms',
    'الجهات والمنصات ذات الصلة',
  ], TRUE)) {
    $removed[] = $title;
    continue;
  }

  if ($paragraph->bundle() === 'cards' && in_array($title, [
    'Clearance Process',
    'How your customs clearance works',
    'Customs clearance procedure',
    'إجراءات التخليص',
    'كيف يعمل التخليص الجمركي لديكم',
    'إجراءات التخليص الجمركي',
  ], TRUE)) {
    _motaded_customs_process_apply($paragraph, 'en', $process_en);
    _motaded_customs_process_apply($paragraph, 'ar', $process_ar);
    $process_updated = TRUE;
    $refs[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
    continue;
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
echo 'Updated process on /node/' . $node->id()
  . '; authorities removed: ' . ($removed ? implode(', ', $removed) : 'none')
  . '; process updated: ' . ($process_updated ? 'yes' : 'no') . "\n";

function _motaded_customs_process_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_PROCESS_ALIAS,
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
function _motaded_customs_process_apply(ParagraphInterface $paragraph, string $langcode, array $copy): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  $tr->set('field_title', $copy['title']);
  $tr->set('field_body', [
    'value' => $copy['lede'],
    'format' => 'basic_html',
  ]);
  $tr->set('field_card_type', 'stack');
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
      'format' => 'full_html',
    ]);
    $card_tr->save();
    $i++;
  }
}

function _motaded_customs_process_stage(string $client, string $motaded, string $next): string {
  return '<div class="customs-stage__grid">'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Client provides</span><p>' . $client . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Motaded does</span><p>' . $motaded . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Next step</span><p>' . $next . '</p></div>'
    . '</div>';
}

function _motaded_customs_process_stage_ar(string $client, string $motaded, string $next): string {
  return '<div class="customs-stage__grid">'
    . '<div class="customs-stage__col"><span class="customs-stage__label">يقدّم العميل</span><p>' . $client . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">ينفّذ متعدد</span><p>' . $motaded . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">الخطوة التالية</span><p>' . $next . '</p></div>'
    . '</div>';
}
