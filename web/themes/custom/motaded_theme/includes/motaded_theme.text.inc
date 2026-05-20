<?php

/**
 * @file
 * Formatted text helpers (plain_text + HTML fix).
 */

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Uses basic_html when stored plain_text still contains HTML tags.
 */
function motaded_theme_resolved_text_format(string $format, string $text): string {
  if ($format === 'plain_text' && preg_match('/<\s*\w+/u', $text)) {
    return 'basic_html';
  }
  return $format !== '' ? $format : 'basic_html';
}

/**
 * Renders processed markup from a text field on an entity.
 */
function motaded_theme_markup_from_text_field(FieldableEntityInterface $entity, string $field_name): string {
  if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
    return '';
  }
  $item = $entity->get($field_name)->first();
  if ($item === NULL) {
    return '';
  }
  $text = trim((string) ($item->value ?? ''));
  if ($text === '') {
    return '';
  }
  $format = motaded_theme_resolved_text_format((string) ($item->format ?? 'basic_html'), $text);
  return check_markup($text, $format);
}

/**
 * Render array for a formatted text field (Twig-safe).
 */
function motaded_theme_render_formatted_text_field(FieldableEntityInterface $entity, string $field_name): array {
  if (!$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
    return [];
  }
  $item = $entity->get($field_name)->first();
  if ($item === NULL) {
    return [];
  }
  $text = trim((string) ($item->value ?? ''));
  if ($text === '') {
    return [];
  }
  $format = motaded_theme_resolved_text_format((string) ($item->format ?? 'basic_html'), $text);
  return [
    '#type' => 'processed_text',
    '#text' => $text,
    '#format' => $format,
    '#langcode' => $entity->language()->getId(),
  ];
}
