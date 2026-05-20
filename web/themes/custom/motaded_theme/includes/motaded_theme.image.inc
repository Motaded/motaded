<?php

/**
 * @file
 * Shared helpers for responsive teaser images (src / srcset / dimensions, itok-safe URLs).
 */

use Drupal\Core\Entity\EntityInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Converts an absolute file URL to a root-relative path (keeps ?itok= for image styles).
 */
function motaded_theme_absolute_file_url_to_root_relative(string $absolute_url): string {
  $parts = parse_url($absolute_url);
  if ($parts === FALSE || empty($parts['path'])) {
    return $absolute_url;
  }
  $path = $parts['path'];
  $query = isset($parts['query']) ? '?' . $parts['query'] : '';

  return $path . $query;
}

/**
 * Root-relative URL for an image style derivative, including itok when required.
 *
 * Do not use FileUrlGenerator::generateString(style->buildUri()) alone — it omits
 * the derivative token and can yield 404 on sites that validate image requests.
 */
function motaded_theme_image_style_root_relative_url(string $file_uri, ImageStyle $style): string {
  return motaded_theme_absolute_file_url_to_root_relative($style->buildUrl($file_uri));
}

/**
 * File URI and alt from a Media entity's standard image field (field_media_image).
 *
 * @return array{uri: ?string, alt: string}
 */
function motaded_theme_media_image_uri_and_alt(?EntityInterface $media, string $fallback_alt): array {
  $alt = trim($fallback_alt);
  if (!$media || !$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
    return ['uri' => NULL, 'alt' => $alt];
  }
  $image_item = $media->get('field_media_image')->first();
  if (!$image_item) {
    return ['uri' => NULL, 'alt' => $alt];
  }
  $alt = trim((string) ($image_item->get('alt')->value ?? '')) ?: $alt;
  $file_entity = $image_item->entity;
  $uri = $file_entity ? $file_entity->getFileUri() : NULL;

  return ['uri' => $uri, 'alt' => $alt];
}

/**
 * File URI and alt from an entity with field_media (e.g. node) referencing image media.
 *
 * @return array{uri: ?string, alt: string}
 */
function motaded_theme_entity_field_media_image_uri_and_alt(?EntityInterface $entity, string $fallback_alt): array {
  if (!$entity || !$entity->hasField('field_media') || $entity->get('field_media')->isEmpty()) {
    return ['uri' => NULL, 'alt' => trim($fallback_alt)];
  }
  $media = $entity->get('field_media')->entity;

  return motaded_theme_media_image_uri_and_alt($media, $fallback_alt);
}

/**
 * Responsive metadata for card/teaser images: max_650 default src, max_650 + max_1300 srcset.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function motaded_theme_responsive_teaser_image_metadata(?string $file_uri): array {
  $fallback = [
    'src' => '',
    'srcset' => '',
    'width' => 151,
    'height' => 120,
  ];
  if ($file_uri === NULL || $file_uri === '') {
    return $fallback;
  }

  $file_url_generator = \Drupal::service('file_url_generator');
  $original_url = $file_url_generator->generateString($file_uri);

  if (str_ends_with(strtolower($file_uri), '.svg')) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 151,
      'height' => 120,
    ];
  }

  $image = \Drupal::service('image.factory')->get($file_uri);
  if (!$image->isValid()) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 151,
      'height' => 120,
    ];
  }

  $w_orig = $image->getWidth();
  $h_orig = $image->getHeight();

  $srcset_parts = [];
  foreach (['max_650x650' => 650, 'max_1300x1300' => 1300] as $style_id => $descriptor_w) {
    $style = ImageStyle::load($style_id);
    if ($style) {
      $srcset_parts[] = motaded_theme_image_style_root_relative_url($file_uri, $style) . ' ' . $descriptor_w . 'w';
    }
  }

  $src = $original_url;
  $style_650 = ImageStyle::load('max_650x650');
  if ($style_650) {
    $src = motaded_theme_image_style_root_relative_url($file_uri, $style_650);
  }

  $display_w = min(650, $w_orig);
  $display_h = (int) max(1, round($h_orig * ($display_w / max(1, $w_orig))));

  return [
    'src' => $src,
    'srcset' => implode(', ', $srcset_parts),
    'width' => $display_w,
    'height' => $display_h,
  ];
}

/**
 * Publications & Updates (insights_updates) grid cards — 3-col / 2-col / 1-col.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function motaded_theme_publication_card_image_metadata(?string $file_uri): array {
  $fallback = [
    'src' => '',
    'srcset' => '',
    'width' => 400,
    'height' => 250,
  ];
  if ($file_uri === NULL || $file_uri === '') {
    return $fallback;
  }

  $file_url_generator = \Drupal::service('file_url_generator');
  $original_url = $file_url_generator->generateString($file_uri);

  if (str_ends_with(strtolower($file_uri), '.svg')) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 400,
      'height' => 250,
    ];
  }

  $image = \Drupal::service('image.factory')->get($file_uri);
  if (!$image->isValid()) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 400,
      'height' => 250,
    ];
  }

  $w_orig = $image->getWidth();
  $h_orig = $image->getHeight();

  $srcset_parts = [];
  foreach (['max_325x325' => 325, 'max_480x480' => 480, 'max_650x650' => 650] as $style_id => $descriptor_w) {
    $style = ImageStyle::load($style_id);
    if ($style) {
      $srcset_parts[] = motaded_theme_image_style_root_relative_url($file_uri, $style) . ' ' . $descriptor_w . 'w';
    }
  }

  $src = $original_url;
  $style_325 = ImageStyle::load('max_325x325');
  if ($style_325) {
    $src = motaded_theme_image_style_root_relative_url($file_uri, $style_325);
  }
  else {
    $style_650 = ImageStyle::load('max_650x650');
    if ($style_650) {
      $src = motaded_theme_image_style_root_relative_url($file_uri, $style_650);
    }
  }

  $display_w = min(420, $w_orig);
  $display_h = (int) max(1, round($h_orig * ($display_w / max(1, $w_orig))));

  return [
    'src' => $src,
    'srcset' => implode(', ', $srcset_parts),
    'width' => $display_w,
    'height' => $display_h,
  ];
}

/**
 * Blog listing (/blog) card image: smaller default + tighter srcset than teasers.
 *
 * Uses max_325x325 as the default `src` when available so mobile rows do not
 * download a 650px-wide derivative for a ~360px slot. Adds 650w/1300w for DPR.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function motaded_theme_blog_listing_card_image_metadata(?string $file_uri): array {
  $fallback = [
    'src' => '',
    'srcset' => '',
    'width' => 325,
    'height' => 200,
  ];
  if ($file_uri === NULL || $file_uri === '') {
    return $fallback;
  }

  $file_url_generator = \Drupal::service('file_url_generator');
  $original_url = $file_url_generator->generateString($file_uri);

  if (str_ends_with(strtolower($file_uri), '.svg')) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 325,
      'height' => 200,
    ];
  }

  $image = \Drupal::service('image.factory')->get($file_uri);
  if (!$image->isValid()) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 325,
      'height' => 200,
    ];
  }

  $w_orig = $image->getWidth();
  $h_orig = $image->getHeight();

  $srcset_parts = [];
  foreach (['max_325x325' => 325, 'max_650x650' => 650, 'max_1300x1300' => 1300] as $style_id => $descriptor_w) {
    $style = ImageStyle::load($style_id);
    if ($style) {
      $srcset_parts[] = motaded_theme_image_style_root_relative_url($file_uri, $style) . ' ' . $descriptor_w . 'w';
    }
  }

  $src = $original_url;
  $style_325 = ImageStyle::load('max_325x325');
  if ($style_325) {
    $src = motaded_theme_image_style_root_relative_url($file_uri, $style_325);
  }
  else {
    $style_650 = ImageStyle::load('max_650x650');
    if ($style_650) {
      $src = motaded_theme_image_style_root_relative_url($file_uri, $style_650);
    }
  }

  $display_w = min(325, $w_orig);
  $display_h = (int) max(1, round($h_orig * ($display_w / max(1, $w_orig))));

  return [
    'src' => $src,
    'srcset' => implode(', ', $srcset_parts),
    'width' => $display_w,
    'height' => $display_h,
  ];
}

/**
 * Partner marquee logos: small derivatives matching on-screen size (~180×48).
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function motaded_theme_partner_logo_image_metadata(?string $file_uri): array {
  $fallback = [
    'src' => '',
    'srcset' => '',
    'width' => 180,
    'height' => 48,
  ];
  if ($file_uri === NULL || $file_uri === '') {
    return $fallback;
  }

  $file_url_generator = \Drupal::service('file_url_generator');
  $original_url = $file_url_generator->generateString($file_uri);

  if (str_ends_with(strtolower($file_uri), '.svg')) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 180,
      'height' => 48,
    ];
  }

  $image = \Drupal::service('image.factory')->get($file_uri);
  if (!$image->isValid()) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 180,
      'height' => 48,
    ];
  }

  $w_orig = $image->getWidth();
  $h_orig = $image->getHeight();

  $srcset_parts = [];
  foreach (['partner_logo_sm' => 120, 'partner_logo' => 180] as $style_id => $descriptor_w) {
    $style = ImageStyle::load($style_id);
    if ($style) {
      $srcset_parts[] = motaded_theme_image_style_root_relative_url($file_uri, $style) . ' ' . $descriptor_w . 'w';
    }
  }

  $src = $original_url;
  $style_sm = ImageStyle::load('partner_logo_sm');
  if ($style_sm) {
    $src = motaded_theme_image_style_root_relative_url($file_uri, $style_sm);
  }
  else {
    $style_lg = ImageStyle::load('partner_logo');
    if ($style_lg) {
      $src = motaded_theme_image_style_root_relative_url($file_uri, $style_lg);
    }
  }

  $display_w = min(180, $w_orig);
  $display_h = (int) max(1, round($h_orig * ($display_w / max(1, $w_orig))));
  $display_h = min(48, $display_h);

  return [
    'src' => $src,
    'srcset' => implode(', ', $srcset_parts),
    'width' => $display_w,
    'height' => $display_h,
  ];
}

/**
 * Platform logos (investor journey, systems list, teasers): ~44–128px on screen.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function motaded_theme_platform_logo_image_metadata(?string $file_uri): array {
  $fallback = [
    'src' => '',
    'srcset' => '',
    'width' => 64,
    'height' => 64,
  ];
  if ($file_uri === NULL || $file_uri === '') {
    return $fallback;
  }

  $file_url_generator = \Drupal::service('file_url_generator');
  $original_url = $file_url_generator->generateString($file_uri);

  if (str_ends_with(strtolower($file_uri), '.svg')) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 128,
      'height' => 128,
    ];
  }

  $image = \Drupal::service('image.factory')->get($file_uri);
  if (!$image->isValid()) {
    return [
      'src' => $original_url,
      'srcset' => '',
      'width' => 128,
      'height' => 128,
    ];
  }

  $w_orig = $image->getWidth();
  $h_orig = $image->getHeight();

  $srcset_parts = [];
  foreach (['platform_logo_sm' => 64, 'platform_logo' => 128] as $style_id => $descriptor_w) {
    $style = ImageStyle::load($style_id);
    if ($style) {
      $srcset_parts[] = motaded_theme_image_style_root_relative_url($file_uri, $style) . ' ' . $descriptor_w . 'w';
    }
  }

  $src = $original_url;
  $style_sm = ImageStyle::load('platform_logo_sm');
  if ($style_sm) {
    $src = motaded_theme_image_style_root_relative_url($file_uri, $style_sm);
  }
  else {
    $style_lg = ImageStyle::load('platform_logo');
    if ($style_lg) {
      $src = motaded_theme_image_style_root_relative_url($file_uri, $style_lg);
    }
  }

  $display_w = min(128, $w_orig);
  $display_h = (int) max(1, round($h_orig * ($display_w / max(1, $w_orig))));
  $display_h = min(128, $display_h);

  return [
    'src' => $src,
    'srcset' => implode(', ', $srcset_parts),
    'width' => $display_w,
    'height' => $display_h,
  ];
}
