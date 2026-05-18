<?php

/**
 * @file
 * One-off: rebuild menu_links_ar.php from motaded-service-ui.ar.po.
 *
 * Usage: php scripts/build_menu_links_ar_dataset.php
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$po_path = $root . '/translations/motaded-service-ui.ar.po';
$out_path = $root . '/web/modules/custom/motaded_custom/data/menu_links_ar.php';

if (!is_readable($po_path)) {
  fwrite(STDERR, "PO not found: {$po_path}\n");
  exit(1);
}

$po = file_get_contents($po_path);
preg_match_all(
  '/^msgid "((?:\\\\.|[^"\\\\])*)"\nmsgstr "((?:\\\\.|[^"\\\\])*)"/m',
  $po,
  $matches,
  PREG_SET_ORDER,
);

$unpo = static function (string $s): string {
  return stripcslashes($s);
};

$map = [];
foreach ($matches as $match) {
  $id = $unpo($match[1]);
  $str = $unpo($match[2]);
  if ($id === '' || $str === '') {
    continue;
  }
  if (mb_strlen($id) > 80) {
    continue;
  }
  $map[$id] = $str;
}

ksort($map, SORT_STRING);

$export = static function (string $s): string {
  return str_replace(['\\', "'"], ['\\\\', "\\'"], $s);
};

$lines = [
  '<?php',
  '',
  'declare(strict_types=1);',
  '',
  '/**',
  ' * @file',
  ' * Arabic menu link titles (English label => Arabic).',
  ' *',
  ' * Regenerate: php scripts/build_menu_links_ar_dataset.php',
  ' * Applied by: drush motaded:menu-links-ar (mmnar)',
  ' */',
  '',
  'return [',
];

foreach ($map as $en => $ar) {
  $lines[] = "  '" . $export($en) . "' => '" . $export($ar) . "',";
}

$lines[] = '];';
$lines[] = '';

file_put_contents($out_path, implode("\n", $lines));
print "Wrote " . count($map) . " entries to {$out_path}\n";
