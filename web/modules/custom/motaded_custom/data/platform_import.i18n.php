<?php

/**
 * @file
 * Спільні допоміжні функції для platform_import.dataset.php та platform_import_ar.dataset.php.
 */

declare(strict_types=1);

if (!function_exists('_motaded_pi_row')) {

  /**
   * @param array<string, mixed> $r
   *
   * @return array<string, string>
   */
  function _motaded_pi_row(array $r): array {
    $enc = static function (array $a): string {
      return json_encode(array_values($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    };
    $meta = array_filter([
      'title' => $r['meta_title'] ?? '',
      'description' => $r['meta_description'] ?? '',
      'keywords' => $r['meta_keywords'] ?? '',
    ], static fn ($v) => $v !== '');
    $langcode = (string) ($r['langcode'] ?? 'en');
    return [
      'title' => (string) ($r['title'] ?? ''),
      'status' => '1',
      'langcode' => $langcode,
      'translation_source_title' => (string) ($r['translation_source_title'] ?? ''),
      'body' => (string) ($r['body'] ?? ''),
      'body_summary' => (string) ($r['body_summary'] ?? ''),
      'field_short_description' => (string) ($r['field_short_description'] ?? ''),
      'field_subtitle' => (string) ($r['field_subtitle'] ?? ''),
      'field_cta_text' => (string) ($r['field_cta_text'] ?? ''),
      'field_featured' => (string) ((int) ($r['field_featured'] ?? 0)),
      'field_source_text' => (string) ($r['field_source_text'] ?? ''),
      'field_category_name' => (string) ($r['field_category_name'] ?? ''),
      'field_region_name' => (string) ($r['field_region_name'] ?? ''),
      'field_sector_name' => (string) ($r['field_sector_name'] ?? ''),
      'field_cta_link_uri' => (string) ($r['field_cta_link_uri'] ?? ''),
      'field_cta_link_title' => (string) ($r['field_cta_link_title'] ?? ''),
      'field_external_link_uri' => (string) ($r['field_external_link_uri'] ?? ''),
      'field_external_link_title' => (string) ($r['field_external_link_title'] ?? ''),
      'field_source_link_uri' => (string) ($r['field_source_link_uri'] ?? ''),
      'field_source_link_title' => (string) ($r['field_source_link_title'] ?? ''),
      'field_logo_mid' => (string) ($r['field_logo_mid'] ?? ''),
      'field_logo_url' => (string) ($r['field_logo_url'] ?? ''),
      'field_logo_basename' => (string) ($r['field_logo_basename'] ?? ''),
      'field_hero_image_mid' => (string) ($r['field_hero_image_mid'] ?? ''),
      'field_partner_logos_mids' => (string) ($r['field_partner_logos_mids'] ?? ''),
      'field_meta_tags_json' => $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '',
      'field_why_matters_json' => $enc($r['why_json'] ?? []),
      'field_how_we_help_json' => $enc($r['help_json'] ?? []),
      'field_process_steps_json' => $enc($r['steps_json'] ?? []),
      'field_requirements_json' => $enc($r['req_json'] ?? []),
      'field_resources_json' => $enc($r['resources_json'] ?? []),
      'field_faq_json' => $enc($r['faq_json'] ?? []),
      'field_related_platforms_titles' => (string) ($r['field_related_platforms_titles'] ?? ''),
      'path_alias' => (string) ($r['path_alias'] ?? ''),
    ];
  }

  /**
   * Appends newline-separated plain text as basic_html paragraphs (escaped).
   */
  function _motaded_pi_append_plain_paragraphs(string $text): string {
    $text = str_replace("\r\n", "\n", $text);
    $html = '';
    foreach (explode("\n", $text) as $line) {
      $line = trim($line);
      if ($line === '') {
        continue;
      }
      $html .= '<p>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }
    return $html;
  }

}
