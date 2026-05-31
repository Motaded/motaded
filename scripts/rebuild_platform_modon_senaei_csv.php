<?php

/**
 * @file
 * Generates platform_import_modon_senaei_ready.csv and AR CSV from dataset files.
 *
 *   php scripts/rebuild_platform_modon_senaei_csv.php
 */

declare(strict_types=1);

$repo = dirname(__DIR__);
$dataFile = $repo . '/web/modules/custom/motaded_custom/data/platform_import_modon_senaei.dataset.php';
$arDataFile = $repo . '/web/modules/custom/motaded_custom/data/platform_import_modon_senaei_ar.dataset.php';
$outEn = $repo . '/platform_import_modon_senaei_ready.csv';
$outAr = $repo . '/platform_import_modon_senaei_ar_ready.csv';

if (!is_readable($dataFile)) {
  fwrite(STDERR, "Missing: {$dataFile}\nRun: python3 scripts/build_platform_modon_senaei_dataset.py\n");
  exit(1);
}

/** @var list<array<string, string>> $rows */
$rows = require $dataFile;
write_csv($outEn, $rows);
echo "Wrote {$outEn} (" . count($rows) . " rows)\n";

if (is_readable($arDataFile)) {
  /** @var list<array<string, string>> $arRows */
  $arRows = require $arDataFile;
  write_csv($outAr, $arRows);
  echo "Wrote {$outAr} (" . count($arRows) . " rows)\n";
}

/**
 * @param list<array<string, string>> $rows
 */
function write_csv(string $path, array $rows): void {
  if ($rows === []) {
    throw new RuntimeException("Empty dataset for {$path}");
  }
  $header = array_keys($rows[0]);
  $fh = fopen($path, 'wb');
  if ($fh === FALSE) {
    throw new RuntimeException("Cannot write {$path}");
  }
  fwrite($fh, "\xEF\xBB\xBF");
  fputcsv($fh, $header, ',', '"', '\\');
  foreach ($rows as $r) {
    $line = [];
    foreach ($header as $col) {
      $line[] = $r[$col] ?? '';
    }
    fputcsv($fh, $line, ',', '"', '\\');
  }
  fclose($fh);
}
