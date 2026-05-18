<?php

/**
 * @file
 * Arabic SEO meta for documents (key = EN title).
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var array<string, array<string, string>> $en */
$en = require __DIR__ . '/document_import_official.seo.php';
/** @var array<string, array{title: string, field_short_description: string}> $ar_docs */
$ar_docs = require __DIR__ . '/documents_ar.dataset.php';

$out = [];
foreach ($en as $en_title => $meta) {
  if (!isset($ar_docs[$en_title])) {
    continue;
  }
  $ar_title = trim((string) $ar_docs[$en_title]['title']);
  $summary = trim((string) $ar_docs[$en_title]['field_short_description']);
  $desc = $summary !== '' ? $summary : (string) ($meta['meta_description'] ?? '');
  $out[$en_title] = motaded_custom_seo_row(
    $ar_title,
    $desc,
    ' | وثائق وأنظمة السعودية | موتاد',
    [
      $ar_title . ' السعودية',
      'امتثال السعودية',
      'مكتبة موتاد',
      'وثائق حكومية',
      'أنظمة المملكة',
    ],
  );
}

return $out;
