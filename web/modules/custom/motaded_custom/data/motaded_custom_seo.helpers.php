<?php

/**
 * @file
 * Helpers to build meta_title, meta_description, meta_keywords for imports.
 */

declare(strict_types=1);

/**
 * @param list<string> $extra_keywords
 *
 * @return array{meta_title: string, meta_description: string, meta_keywords: string}
 */
function motaded_custom_seo_row(
  string $title,
  string $description,
  string $title_suffix,
  array $extra_keywords = [],
): array {
  $title = trim($title);
  $keywords = array_values(array_unique(array_filter(array_merge(
    [$title, $title . ' Saudi Arabia', $title . ' KSA'],
    $extra_keywords,
    ['Saudi Arabia', 'Motaded'],
  ))));
  return [
    'meta_title' => $title . $title_suffix,
    'meta_description' => $description,
    'meta_keywords' => implode(', ', array_slice($keywords, 0, 12)),
  ];
}

/**
 * @param list<array<string, mixed>> $catalog_rows
 *
 * @return array<string, array{meta_title: string, meta_description: string, meta_keywords: string}>
 */
function motaded_custom_document_seo_from_catalog(array $catalog_rows): array {
  require_once __DIR__ . '/motaded_custom_seo.helpers.php';
  $seo = [];
  foreach ($catalog_rows as $row) {
    if (!is_array($row)) {
      continue;
    }
    if (($row['action'] ?? '') === 'delete') {
      continue;
    }
    $title = trim((string) ($row['title'] ?? ''));
    if ($title === '') {
      continue;
    }
    $type = trim((string) ($row['field_document_type'] ?? 'document'));
    $category = trim((string) ($row['field_category'] ?? ''));
    $sector = trim((string) ($row['field_sector'] ?? ''));
    $summary = trim((string) ($row['field_short_description'] ?? ''));
    $desc = $summary !== ''
      ? $summary
      : 'Official ' . strtolower($type) . ' for Saudi Arabia: ' . $title . '.';
    $extra = array_filter([
      $type . ' Saudi Arabia',
      $category !== '' ? $category . ' compliance KSA' : '',
      $sector !== '' ? $sector . ' sector Saudi' : '',
      'Saudi regulations',
      'Motaded library',
      'government PDF KSA',
    ]);
    $seo[$title] = motaded_custom_seo_row(
      $title,
      $desc,
      ' | Saudi Arabia documents & regulations | Motaded',
      $extra,
    );
  }
  return $seo;
}
