<?php

declare(strict_types=1);

/**
 * Generate Arabic SEO meta content directly in services CSV table.
 *
 * Updates columns in reports/services_content_audit_ar.csv:
 * - meta_title
 * - meta_description
 * - meta_abstract
 * - meta_keywords
 *
 * Usage:
 *   php scripts/services_ar_generate_meta_table.php
 */

$path = __DIR__ . '/../reports/services_content_audit_ar.csv';
if (!is_file($path)) {
  fwrite(STDERR, "CSV not found: {$path}\n");
  exit(1);
}

$in = fopen($path, 'rb');
if (!$in) {
  fwrite(STDERR, "Cannot open CSV for reading.\n");
  exit(1);
}

$header = fgetcsv($in, 0, ',', '"', '\\');
if (!$header) {
  fwrite(STDERR, "CSV header missing.\n");
  exit(1);
}

$required = ['nid', 'title'];
foreach ($required as $col) {
  if (!in_array($col, $header, TRUE)) {
    fwrite(STDERR, "Missing required column: {$col}\n");
    exit(1);
  }
}

$metaColumns = ['meta_title', 'meta_description', 'meta_abstract', 'meta_keywords'];
foreach ($metaColumns as $metaColumn) {
  if (!in_array($metaColumn, $header, TRUE)) {
    $header[] = $metaColumn;
  }
}
if (!in_array('status', $header, TRUE)) {
  $header[] = 'status';
}

$idx = array_flip($header);

$openers = [
  'نقدم خدمة متخصصة تضمن الامتثال الكامل للأنظمة وتسريع الإجراءات.',
  'نوفّر تنفيذًا دقيقًا بخطوات واضحة لتقليل الوقت التشغيلي وتجنب التعثر.',
  'حل عملي للشركات التي تبحث عن إنجاز سريع مع متابعة تنظيمية متكاملة.',
  'خدمة احترافية تركّز على تقليل المخاطر الإجرائية ورفع جاهزية المنشأة.',
  'تنفيذ منظم من فريق متخصص لضمان دقة البيانات وسلاسة اعتماد الطلبات.',
];

$benefits = [
  'مع متابعة حالة الطلب حتى الإقفال النهائي.',
  'بما يدعم انطلاقة آمنة ونموًا تشغيليًا مستدامًا.',
  'وفق متطلبات الجهات الحكومية ومعايير الحوكمة المؤسسية.',
  'مع توثيق كامل وخطة تنفيذ واضحة لكل مرحلة.',
  'مع تقارير متابعة دورية وتسليمات دقيقة في كل خطوة.',
];

$titleSpecific = static function (string $title): string {
  $t = mb_strtolower($title);

  if (str_contains($t, 'ضريبة') || str_contains($t, 'الزكاة') || str_contains($t, 'المحاس')) {
    return 'نساعدك على ضبط الالتزامات المالية والضريبية ورفع جودة التقارير المحاسبية.';
  }
  if (str_contains($t, 'قوى') || str_contains($t, 'مدد') || str_contains($t, 'مقيم') || str_contains($t, 'توطين') || str_contains($t, 'رخصة العمل')) {
    return 'ننفّذ متطلبات الموارد البشرية والمنصات الحكومية لضمان استمرارية الامتثال دون غرامات.';
  }
  if (str_contains($t, 'تأشيرة') || str_contains($t, 'الإقامة') || str_contains($t, 'نقل الخدمات') || str_contains($t, 'الكفالة')) {
    return 'ندير إجراءات العمالة والإقامات باحتراف لتسريع دورة التوظيف واستقرار الكوادر.';
  }
  if (str_contains($t, 'مكتب') || str_contains($t, 'الافتراضي') || str_contains($t, 'الاجتماعات') || str_contains($t, 'قاعة') || str_contains($t, 'الاستقبال')) {
    return 'نوفر بيئة أعمال جاهزة تعكس حضورًا مهنيًا وتدعم متطلبات التشغيل اليومية.';
  }
  if (str_contains($t, 'الاستثمار') || str_contains($t, 'التأسيس') || str_contains($t, 'الاسم التجاري') || str_contains($t, 'الغرفة التجارية')) {
    return 'نقودك خلال خطوات التأسيس الرسمية من التسجيل إلى الجاهزية القانونية للتشغيل.';
  }

  return 'نقدم دعمًا تشغيليًا وقانونيًا متكاملاً لمواءمة الخدمة مع أهداف منشأتك.';
};

$keywordBucket = static function (string $title): array {
  $t = mb_strtolower($title);
  $base = ['خدمات معتد', 'السوق السعودي'];

  if (str_contains($t, 'ضريبة') || str_contains($t, 'الزكاة') || str_contains($t, 'المحاس')) {
    return array_merge([$title, 'خدمات محاسبية', 'امتثال ضريبي'], $base);
  }
  if (str_contains($t, 'قوى') || str_contains($t, 'مدد') || str_contains($t, 'مقيم') || str_contains($t, 'توطين')) {
    return array_merge([$title, 'خدمات الموارد البشرية', 'المنصات الحكومية'], $base);
  }
  if (str_contains($t, 'تأشيرة') || str_contains($t, 'الإقامة') || str_contains($t, 'نقل الخدمات') || str_contains($t, 'الكفالة')) {
    return array_merge([$title, 'خدمات الإقامات والتأشيرات', 'إدارة شؤون الموظفين'], $base);
  }
  if (str_contains($t, 'مكتب') || str_contains($t, 'الافتراضي') || str_contains($t, 'الاجتماعات') || str_contains($t, 'قاعة') || str_contains($t, 'الاستقبال')) {
    return array_merge([$title, 'حلول المكاتب', 'بيئة أعمال مرنة'], $base);
  }

  return array_merge([$title, 'تأسيس الأعمال في السعودية', 'خدمات الشركات'], $base);
};

$clip = static function (string $text, int $max): string {
  $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
  if (mb_strlen($text) <= $max) {
    return $text;
  }
  $cut = mb_substr($text, 0, $max);
  $cut = preg_replace('/\s+\S*$/u', '', $cut) ?? $cut;
  return rtrim($cut, " \t\n\r\0\x0B,;:.،") . '...';
};

$rows = [];
$seenDescriptions = [];
$updated = 0;

while (($row = fgetcsv($in, 0, ',', '"', '\\')) !== false) {
  // Backfill row length in case new columns were appended.
  while (count($row) < count($header)) {
    $row[] = '';
  }
  $title = trim($row[$idx['title']] ?? '');
  $nid = (int) ($row[$idx['nid']] ?? 0);
  if ($title === '' || $nid === 0) {
    $rows[] = $row;
    continue;
  }

  $opener = $openers[$nid % count($openers)];
  $benefit = $benefits[$nid % count($benefits)];
  $specific = $titleSpecific($title);

  $metaTitle = $clip($title . ' | معتد', 65);
  $descriptionCore = "{$title}: {$specific} {$opener} {$benefit}";
  $metaDescription = $clip($descriptionCore, 158);
  if (isset($seenDescriptions[$metaDescription])) {
    $metaDescription = $clip($descriptionCore . " (خدمة رقم {$nid})", 158);
  }
  $seenDescriptions[$metaDescription] = true;

  $metaAbstract = $clip("{$title}: {$specific}", 110);

  $kws = [];
  foreach ($keywordBucket($title) as $kw) {
    $kw = trim($kw);
    if ($kw !== '' && !in_array($kw, $kws, true)) {
      $kws[] = $kw;
    }
  }
  $metaKeywords = implode('، ', array_slice($kws, 0, 6));

  $row[$idx['meta_title']] = $metaTitle;
  $row[$idx['meta_description']] = $metaDescription;
  $row[$idx['meta_abstract']] = $metaAbstract;
  $row[$idx['meta_keywords']] = $metaKeywords;
  if (isset($idx['status'])) {
    $row[$idx['status']] = 'handcrafted';
  }

  $rows[] = $row;
  $updated++;
}
fclose($in);

$tmp = $path . '.tmp';
$out = fopen($tmp, 'wb');
fputcsv($out, $header, ',', '"', '\\');
foreach ($rows as $row) {
  fputcsv($out, $row, ',', '"', '\\');
}
fclose($out);

rename($tmp, $path);

print "Updated rows: {$updated}\n";
print "File: {$path}\n";

