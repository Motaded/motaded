<?php

/**
 * @file
 * Arabic rows for Modon + Senaei platform import.
 *
 * @return list<array<string, string>>
 */

declare(strict_types=1);

/** @var list<array<string, string>> $en */
$en = require __DIR__ . '/platform_import_modon_senaei.dataset.php';
/** @var array<string, array<string, string>> $over */
$over = require __DIR__ . '/platform_import_modon_senaei_ar.overrides.php';

$out = [];
foreach ($en as $row) {
  $src = $row['title'] ?? '';
  if ($src === '' || !isset($over[$src])) {
    throw new RuntimeException('Missing Arabic overrides for platform title: ' . $src);
  }
  $path = $row['path_alias'] ?? '';
  $arPath = $path;
  if ($arPath !== '' && strncmp($arPath, '/ar/', 4) === 0) {
    $arPath = substr($arPath, 3);
  }
  $out[] = array_merge($row, $over[$src], [
    'langcode' => 'ar',
    'translation_source_title' => $src,
    'path_alias' => $arPath,
  ]);
}

return $out;
