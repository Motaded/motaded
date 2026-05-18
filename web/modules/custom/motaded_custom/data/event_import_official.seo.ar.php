<?php

/**
 * @file
 * Arabic SEO meta for official events (key = EN title).
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var array<string, array<string, mixed>> $overrides */
$overrides = require __DIR__ . '/event_import_official.ar.overrides.php';

$keyword_map = [
  'LEAP 2027' => ['ليب السعودية', 'مؤتمر تقنية', 'التحول الرقمي'],
  'Future Investment Initiative 2026' => ['منتدى الاستثمار', 'مبادرة المستقبل', 'استثمار سيادي'],
  'Cityscape Global 2026' => ['عقارات السعودية', 'سيتي سكيب', 'تطوير عمراني'],
  'Big 5 Construct Saudi 2026' => ['معرض إنشاءات', 'مواد بناء', 'بيج فايف السعودية'],
  'Global Health Exhibition 2026' => ['معرض صحي', 'أجهزة طبية', 'مشتريات مستشفيات'],
  'Saudi Food Expo 2026' => ['صناعة غذائية', 'حلال', 'معرض أغذية'],
  'Future Minerals Forum 2027' => ['تعدين السعودية', 'معادن', 'منتدى معادن'],
  'BIBAN 2026' => ['ريادة أعمال', 'منشآت', 'بيبان'],
  'Saudi Travel Market 2026' => ['سياحة', 'ضيافة', 'سوق السفر'],
  'International Petroleum Technology Conference 2027' => ['نفط وغاز', 'أرامكو', 'تقنية بترول'],
  'Black Hat MEA 2026' => ['أمن سيبراني', 'بلاك هات', 'CISO'],
  'World Defense Show 2026' => ['معرض دفاع', 'تقنيات عسكرية', 'مشتريات دفاع'],
  'Global AI Summit 2026' => ['ذكاء اصطناعي', 'سدايا', 'قمة الذكاء الاصطناعي'],
  'INDEX Saudi Arabia 2026' => ['تصميم داخلي', 'أثاث', 'ضيافة'],
  'DeepFest 2026' => ['ذكاء اصطناعي', 'ديب فيست', 'ليب'],
  'Saudi Warehousing & Logistics Expo 2026' => ['لوجستيات', 'مستودعات', 'سلاسل إمداد'],
  'SEA Expo 2026' => ['ترفيه', 'مدن ملاهي', 'معالم ترفيهية'],
  'Saudi International Pharma Expo 2026' => ['أدوية', 'صيدلة', 'هيئة الغذاء والدواء'],
  'Saudi Build 2026' => ['إنشاءات', 'مواد بناء', 'مشاريع سعودية'],
  'JTTX 2026' => ['سفر', 'جدة', 'سياحة صادرة'],
];

$out = [];
foreach ($overrides as $en_title => $row) {
  $ar_title = trim((string) ($row['title'] ?? $en_title));
  $summary = trim((string) ($row['body_summary'] ?? ''));
  $desc = $summary !== '' ? $summary : 'دليل فعالية ' . $ar_title . ' في السعودية.';
  $kw = $keyword_map[$en_title] ?? [];
  $out[$en_title] = motaded_custom_seo_row(
    $ar_title,
    $desc,
    ' | فعاليات الأعمال في السعودية | موتاد',
    array_merge([$ar_title . ' السعودية', 'فعاليات أعمال'], $kw),
  );
}

return $out;
