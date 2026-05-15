<?php

/**
 * @file
 * Арабські переклади для platform_import_ar_ready.csv.
 *
 * Базові EN-рядки беруться з platform_import.dataset.php; тут лише перекладені поля
 * та langcode / translation_source_title. path_alias як у EN (/platforms/…): префікс /ar/ додає language negotiation.
 * Назви термінів taxonomy у CSV лишаються англійськими (пошук tid як у mpic).
 *
 * Генерація: php scripts/rebuild_platform_import_ar_csv.php
 *
 * @return list<array<string, string>>
 */

declare(strict_types=1);

/** @var list<array<string, string>> $en */
$en = require __DIR__ . '/platform_import.dataset.php';
/** @var array<string, array<string, string>> $over */
$over = require __DIR__ . '/platform_import_ar.overrides.php';

$out = [];
foreach ($en as $row) {
  $src = $row['title'] ?? '';
  if ($src === '' || !isset($over[$src])) {
    throw new \RuntimeException('Missing Arabic overrides for platform title: ' . $src);
  }
  $path = $row['path_alias'] ?? '';
  // Negotiation uses /ar/ prefix — aliases must not include it or URLs become /ar/ar/...
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
