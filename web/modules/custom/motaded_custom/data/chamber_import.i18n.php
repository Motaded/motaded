<?php

/**
 * @file
 * Спільні допоміжні функції для chamber_import.dataset.php та chamber_import_ar.dataset.php.
 */

declare(strict_types=1);

if (!function_exists('_motaded_ci_truncate')) {

  function _motaded_ci_truncate(string $text, int $max): string {
    if ($max < 1) {
      return '';
    }
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
      if (mb_strlen($text, 'UTF-8') <= $max) {
        return $text;
      }
      return mb_substr($text, 0, $max, 'UTF-8');
    }
    return strlen($text) > $max ? substr($text, 0, $max) : $text;
  }

  /**
   * Truncates at a word boundary for short labels (relationship focus panel).
   */
  function _motaded_ci_truncate_at_word(string $text, int $max): string {
    $text = trim($text);
    if ($text === '') {
      return '';
    }
    if (!function_exists('mb_strlen') || !function_exists('mb_substr') || !function_exists('mb_strrpos')) {
      return _motaded_ci_truncate($text, $max);
    }
    if (mb_strlen($text, 'UTF-8') <= $max) {
      return $text;
    }
    $chunk = mb_substr($text, 0, $max, 'UTF-8');
    $lastSpace = mb_strrpos($chunk, ' ', 0, 'UTF-8');
    if ($lastSpace !== FALSE && $lastSpace > 24) {
      return rtrim(mb_substr($chunk, 0, $lastSpace, 'UTF-8'), '.,;:! ');
    }
    return rtrim($chunk, '.,;:! ');
  }

  /**
   * Builds default overview HTML from title, teaser, and full relationship-focus copy.
   */
  function _motaded_ci_default_overview_html(string $title, string $teaser, string $focusFull): string {
    $title = trim($title);
    $teaser = trim($teaser);
    $focusFull = trim($focusFull);
    $t = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $s = htmlspecialchars($teaser, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $f = htmlspecialchars($focusFull, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $html = '<p><strong>' . $t . '</strong>. ' . $s . '</p>';
    if ($f !== '') {
      $html .= '<p>' . $f . '</p>';
    }
    return $html;
  }

  /**
   * @param array<string, mixed> $r
   *
   * @return array<string, string>
   */
  function _motaded_ci_row(array $r): array {
    $stats = $r['stats'] ?? [];
    if (!is_array($stats)) {
      $stats = [];
    }
    $stats = array_values(array_filter(array_map(static function ($v): string {
      return trim((string) $v);
    }, $stats), static fn (string $s): bool => $s !== ''));
    if (count($stats) > 3) {
      $stats = array_slice($stats, 0, 3);
    }
    $meta = array_filter([
      'title' => $r['meta_title'] ?? '',
      'description' => $r['meta_description'] ?? '',
      'keywords' => $r['meta_keywords'] ?? '',
    ], static fn ($v) => $v !== '');
    $title = (string) ($r['title'] ?? '');
    $teaser = (string) ($r['field_short_description'] ?? '');
    $focusRaw = (string) ($r['field_relationship_focus'] ?? '');
    $focusTagline = _motaded_ci_truncate_at_word($focusRaw, 200);
    $focusOut = _motaded_ci_truncate($focusTagline, 255);
    $body = (string) ($r['body'] ?? '');
    if ($body === '') {
      $body = _motaded_ci_default_overview_html($title, $teaser, $focusRaw);
    }
    $bodySummary = (string) ($r['body_summary'] ?? '');
    if ($bodySummary === '') {
      $bodySummary = _motaded_ci_truncate(trim($teaser . ($focusTagline !== '' ? ' ' . $focusTagline : '')), 480);
    }
    $langcode = (string) ($r['langcode'] ?? 'en');
    return [
      'title' => $title,
      'status' => '1',
      'langcode' => $langcode,
      'translation_source_title' => (string) ($r['translation_source_title'] ?? ''),
      'body' => $body,
      'body_summary' => $bodySummary,
      'field_short_description' => $teaser,
      'field_country_name' => (string) ($r['field_country_name'] ?? ''),
      'field_type' => (string) ($r['field_type'] ?? ''),
      'field_relationship_focus' => $focusOut,
      'field_external_link_uri' => (string) ($r['field_external_link_uri'] ?? ''),
      'field_external_link_title' => (string) ($r['field_external_link_title'] ?? ''),
      'field_priority' => (string) ($r['field_priority'] ?? '0'),
      'field_featured' => (string) ((int) ($r['field_featured'] ?? 0)),
      'field_meta_tags_json' => $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
      'field_logo_mid' => (string) ($r['field_logo_mid'] ?? ''),
      'field_logo_url' => (string) ($r['field_logo_url'] ?? ''),
      'field_logo_basename' => (string) ($r['field_logo_basename'] ?? ''),
      'field_chamber_featured_stats_json' => $stats !== [] ? json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
      'path_alias' => (string) ($r['path_alias'] ?? ''),
    ];
  }

}
