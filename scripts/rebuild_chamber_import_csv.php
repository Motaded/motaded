<?php

/**
 * @file
 * Generates chamber_import_ready.csv from chamber_import.dataset.php.
 *
 *   php scripts/rebuild_chamber_import_csv.php
 */

declare(strict_types=1);

$repo = dirname(__DIR__);
$dataFile = $repo . '/web/modules/custom/motaded_custom/data/chamber_import.dataset.php';
$outFile = $repo . '/chamber_import_ready.csv';

if (!is_readable($dataFile)) {
  fwrite(STDERR, "Missing: {$dataFile}\n");
  exit(1);
}

/** @var list<array<string, string>> $rows */
$rows = require $dataFile;
if ($rows === []) {
  fwrite(STDERR, "Empty dataset.\n");
  exit(1);
}

$header = array_keys($rows[0]);
$fh = fopen($outFile, 'wb');
if ($fh === FALSE) {
  fwrite(STDERR, "Cannot write {$outFile}\n");
  exit(1);
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

echo "Wrote {$outFile} (" . count($rows) . " rows)\n";
