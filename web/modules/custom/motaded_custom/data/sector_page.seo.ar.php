<?php

/**
 * @file
 * Arabic SEO meta for sector_page nodes (key = EN title).
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var array<string, array<string, mixed>> $nodes */
$nodes = (require __DIR__ . '/sectors_ar.dataset.php')['nodes'] ?? [];

$keyword_map = [
  'Technology' => ['تقنية', 'اقتصاد رقمي', 'ذكاء اصطناعي'],
  'Tourism' => ['سياحة', 'ضيافة', 'ترفيه'],
  'Logistics & Transport' => ['لوجستيات', 'موانئ', 'نقل'],
  'Finance & Fintech' => ['تمويل', 'تقنية مالية', 'تداول'],
  'Manufacturing' => ['تصنيع', 'توطين', 'مدن صناعية'],
  'Healthcare' => ['رعاية صحية', 'مستشفيات', 'تحول صحي'],
  'Energy' => ['طاقة', 'نفط', 'طاقة متجددة'],
  'Construction & Infrastructure' => ['إنشاءات', 'بنية تحتية', 'مشاريع كبرى'],
];

$out = [];
foreach ($nodes as $en_title => $row) {
  $ar_title = trim((string) ($row['title'] ?? $en_title));
  $summary = trim((string) ($row['field_short_description'] ?? ''));
  $desc = $summary !== '' ? $summary : 'قطاع ' . $ar_title . ' في السعودية — فرص استثمار وتشغيل.';
  $kw = $keyword_map[$en_title] ?? [];
  $out[$en_title] = motaded_custom_seo_row(
    $ar_title,
    $desc,
    ' | قطاعات الاستثمار في السعودية | متعدد',
    array_merge([$ar_title, 'قطاع ' . $ar_title . ' السعودية', 'رؤية 2030'], $kw),
  );
}

return $out;
