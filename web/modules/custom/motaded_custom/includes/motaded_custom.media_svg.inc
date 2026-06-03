<?php

/**
 * @file
 * SVG-safe rendering for media image fields (responsive_image cannot size SVG).
 */

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Whether a file is SVG (vector images have no raster dimensions for srcset).
 */
function motaded_custom_file_is_svg(?FileInterface $file): bool {
  if ($file === NULL) {
    return FALSE;
  }
  $mime = $file->getMimeType();
  if ($mime === 'image/svg+xml') {
    return TRUE;
  }
  $ext = strtolower(pathinfo($file->getFileName(), PATHINFO_EXTENSION));
  return $ext === 'svg';
}

/**
 * Build a plain image render array for an SVG file (no image styles / srcset).
 *
 * @return array<string, mixed>|null
 *   Render array, or NULL if the media has no SVG image.
 */
function motaded_custom_media_svg_image_build(MediaInterface $media): ?array {
  if (!$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
    return NULL;
  }
  $item = $media->get('field_media_image')->first();
  if ($item === NULL) {
    return NULL;
  }
  $file = $item->entity;
  if (!motaded_custom_file_is_svg($file)) {
    return NULL;
  }

  $alt = (string) ($item->get('alt')->getString() ?? '');
  $title = (string) ($item->get('title')->getString() ?? '');

  $build = [
    '#theme' => 'image',
    '#uri' => $file->getFileUri(),
    '#alt' => $alt,
    '#attributes' => [
      'class' => ['media-image', 'media-image--svg'],
      'loading' => 'lazy',
      'decoding' => 'async',
    ],
    '#cache' => [
      'tags' => Cache::mergeTags($media->getCacheTags(), $file->getCacheTags()),
    ],
  ];
  if ($title !== '') {
    $build['#title'] = $title;
  }
  return $build;
}

/**
 * Replaces responsive_image formatter output for SVG media images.
 */
function motaded_custom_media_svg_entity_view_alter(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display): void {
  if (!$entity instanceof MediaInterface || $entity->bundle() !== 'image') {
    return;
  }
  if (!isset($build['field_media_image']) || !is_array($build['field_media_image'])) {
    return;
  }
  $svg_build = motaded_custom_media_svg_image_build($entity);
  if ($svg_build === NULL) {
    return;
  }
  $svg_build['#weight'] = $build['field_media_image']['#weight'] ?? 0;
  $build['field_media_image'] = $svg_build;
}
