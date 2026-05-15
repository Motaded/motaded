<?php

/**
 * @file
 * Будує platform_import_ready.csv з Platforms.md у форматі motaded:platform-import-csv.
 *
 * Запуск (з кореня репозиторію, без Drupal):
 *   php scripts/platforms_md_to_platform_import_csv.php
 *
 * Вихід: platform_import_ready.csv (перезапис).
 */

declare(strict_types=1);

$repo = dirname(__DIR__);
$mdPath = $repo . '/Platforms.md';
$csvPath = $repo . '/platform_import_ready.csv';

if (!is_readable($mdPath)) {
  fwrite(STDERR, "Не знайдено: {$mdPath}\n");
  exit(1);
}

$platforms = motaded_parse_platforms_md(file_get_contents($mdPath) ?: '');
if ($platforms === []) {
  fwrite(STDERR, "Не вдалося розпарсити жодної платформи.\n");
  exit(1);
}

$rows = [];
foreach ($platforms as $p) {
  $rows[] = motaded_platform_row_to_import($p);
}

$header = array_keys($rows[0]);
$fh = fopen($csvPath, 'wb');
if ($fh === FALSE) {
  fwrite(STDERR, "Не вдалося записати: {$csvPath}\n");
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

echo "OK: {$csvPath} (" . count($rows) . " рядків)\n";

/**
 * @return list<array<string, string>>
 */
function motaded_parse_platforms_md(string $text): array {
  $lines = preg_split("/\r\n|\n|\r/", $text);
  $n = count($lines);
  $out = [];
  $current = NULL;
  $i = 0;
  while ($i < $n) {
    $trim = trim($lines[$i]);
    if ($trim === '') {
      $i++;
      continue;
    }
    if (preg_match('/^#\s+\*\*(.+?)\*\*\s*$/u', $trim, $mh)) {
      $rawTitle = $mh[1];
      if (preg_match('/ДОДАЙ|ДАЛІ|НАСТУПНИЙ\s+КРОК/iu', $rawTitle)) {
        if ($current !== NULL) {
          $out[] = $current;
          $current = NULL;
        }
        $i++;
        continue;
      }
      if ($current !== NULL) {
        $out[] = $current;
      }
      $current = ['_title' => motaded_clean_platform_title($rawTitle)];
      $i++;
      continue;
    }
    if ($current === NULL || !preg_match('/^###\s+\*\*(.+?)\*\*\s*$/u', $trim, $fh)) {
      $i++;
      continue;
    }
    $fieldKey = str_replace('\\', '', $fh[1]);
    $i++;
    $chunk = [];
    while ($i < $n) {
      $L = $lines[$i];
      $T = trim($L);
      if ($T !== '' && preg_match('/^###\s+\*\*/', $T)) {
        break;
      }
      if ($T !== '' && preg_match('/^#\s+\*\*/', $T)) {
        break;
      }
      $i++;
      if ($T === '---') {
        continue;
      }
      $chunk[] = rtrim($L);
    }
    $current[$fieldKey] = motaded_normalize_chunk($fieldKey, $chunk);
  }
  if ($current !== NULL) {
    $out[] = $current;
  }
  return $out;
}

function motaded_clean_platform_title(string $raw): string {
  $t = trim($raw);
  $t = preg_replace('/\s*\(FINAL VERSION\)\s*$/iu', '', $t);
  $t = preg_replace('/^[\p{So}\s]+/u', '', $t);
  return trim($t);
}

/**
 * @param list<string> $chunk
 */
function motaded_normalize_chunk(string $fieldKey, array $chunk): string {
  $lines = [];
  foreach ($chunk as $ln) {
    $ln = rtrim($ln);
    if ($ln !== '') {
      $lines[] = $ln;
    }
  }
  if ($lines === []) {
    return '';
  }
  $join = in_array($fieldKey, ['related_platforms', 'related_sectors', 'related_services'], TRUE)
    ? '|'
    : "\n\n";
  $parts = [];
  foreach ($lines as $ln) {
    $ln = preg_replace('/^\s*✔\s*/u', '', $ln);
    $ln = trim($ln);
    if ($ln !== '') {
      $parts[] = $ln;
    }
  }
  return implode($join, $parts);
}

/**
 * @param array<string, string> $p
 *
 * @return array<string, string>
 */
function motaded_platform_row_to_import(array $p): array {
  $title = $p['_title'] ?? '';
  $short = $p['short_description'] ?? '';
  $overview = $p['overview'] ?? '';

  $help = [];
  for ($k = 1; $k <= 3; $k++) {
    $t = trim($p["help_{$k}_title"] ?? '');
    $b = trim($p["help_{$k}_body"] ?? '');
    $icon = trim($p["help_{$k}_icon"] ?? '');
    if ($t !== '' || $b !== '') {
      $row = ['title' => $t, 'body' => $b];
      if ($icon !== '') {
        $row['icon'] = $icon;
      }
      $help[] = $row;
    }
  }
  $steps = [];
  for ($k = 1; $k <= 3; $k++) {
    $t = trim($p["step_{$k}_title"] ?? '');
    $b = trim($p["step_{$k}_body"] ?? '');
    if ($t !== '' || $b !== '') {
      $steps[] = ['title' => $t, 'body' => $b];
    }
  }
  $reqs = [];
  for ($k = 1; $k <= 5; $k++) {
    $c = trim($p["req_{$k}_content"] ?? '');
    if ($c !== '') {
      $reqs[] = ['content' => $c];
    }
  }

  $bodyExtra = [];
  foreach (['when_you_need', 'benefits', 'related_services'] as $ek) {
    $v = trim($p[$ek] ?? '');
    if ($v === '') {
      continue;
    }
    $label = match ($ek) {
      'when_you_need' => 'When you need this',
      'benefits' => 'Benefits',
      'related_services' => 'Related services',
      default => $ek,
    };
    $items = array_values(array_filter(array_map('trim', explode('|', $v)), static fn ($x) => $x !== ''));
    $lis = implode('', array_map(static fn ($x) => '<li>' . motaded_plain_to_html($x) . '</li>', $items));
    $bodyExtra[] = '<h2>' . htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2><ul>' . $lis . '</ul>';
  }
  $relPlat = trim($p['related_platforms'] ?? '');
  if ($relPlat !== '') {
    $names = array_values(array_filter(array_map('trim', explode('|', $relPlat)), static fn ($x) => $x !== ''));
    $bodyExtra[] = '<h2>Related platforms</h2><p>' . motaded_plain_to_html(implode(', ', $names)) . '</p>';
  }
  $relSec = trim($p['related_sectors'] ?? '');
  if ($relSec !== '') {
    $bodyExtra[] = '<h2>Sectors</h2><p>' . motaded_plain_to_html(str_replace('|', ', ', $relSec)) . '</p>';
  }

  $body = motaded_overview_to_basic_html($overview);
  if ($bodyExtra !== []) {
    $body .= "\n\n" . implode("\n\n", $bodyExtra);
  }

  $summary = $short !== '' ? $short : motaded_teaser_plain($overview, 400);

  $sectors = trim($p['related_sectors'] ?? '');
  $sectorFirst = $sectors !== '' ? trim(explode('|', $sectors)[0]) : '';

  return [
    'title' => $title,
    'status' => '1',
    'langcode' => 'en',
    'body' => $body,
    'body_summary' => $summary,
    'field_short_description' => $short,
    'field_subtitle' => '',
    'field_cta_text' => '',
    'field_featured' => '0',
    'field_source_text' => '',
    'field_category_name' => 'Government Platforms',
    'field_region_name' => 'Saudi Arabia',
    'field_sector_name' => $sectorFirst,
    'field_cta_link_uri' => '',
    'field_cta_link_title' => '',
    'field_external_link_uri' => '',
    'field_external_link_title' => '',
    'field_source_link_uri' => '',
    'field_source_link_title' => '',
    'field_logo_mid' => '',
    'field_hero_image_mid' => '',
    'field_partner_logos_mids' => '',
    'field_why_matters_json' => '[]',
    'field_how_we_help_json' => json_encode($help, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'field_process_steps_json' => json_encode($steps, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'field_requirements_json' => json_encode($reqs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'path_alias' => motaded_slug_alias($title),
  ];
}

function motaded_plain_to_html(string $text): string {
  return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function motaded_overview_to_basic_html(string $overview): string {
  $overview = trim(str_replace('\.', '.', $overview));
  if ($overview === '') {
    return '<p></p>';
  }
  $blocks = preg_split("/\n\s*\n/", $overview, -1, PREG_SPLIT_NO_EMPTY);
  $out = [];
  foreach ($blocks as $b) {
    $b = trim($b);
    if ($b === '') {
      continue;
    }
    $out[] = '<p>' . nl2br(motaded_plain_to_html($b)) . '</p>';
  }
  return $out !== [] ? implode("\n", $out) : '<p></p>';
}

function motaded_teaser_plain(string $text, int $max): string {
  $text = trim(preg_replace('/\s+/', ' ', $text));
  if (mb_strlen($text) <= $max) {
    return $text;
  }
  return rtrim(mb_substr($text, 0, $max - 1)) . '…';
}

function motaded_slug_alias(string $title): string {
  $s = mb_strtolower($title);
  $s = preg_replace('/[^a-z0-9\x{0600}-\x{06FF}]+/u', '-', $s);
  $s = trim((string) $s, '-');
  return '/platforms/' . ($s !== '' ? $s : 'platform');
}
