<?php

/**
 * @file
 * Platform SEO: metatag alignment and Schema.org JSON-LD helpers.
 */

declare(strict_types=1);

use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\schema_metatag\SchemaMetatagManager;
use Drupal\taxonomy\TermInterface;

/**
 * Absolute URL of the Motaded site logo (Schema.org fallback image).
 */
function motaded_custom_site_logo_absolute_url(): string {
  return rtrim((string) \Drupal::config('system.site')->get('url'), '/') . '/themes/custom/motaded_theme/logo.svg';
}

/**
 * Resolves an ImageObject array for platform schema / social images.
 *
 * @return array<string, mixed>|null
 */
function motaded_custom_platform_schema_image(NodeInterface $node): ?array {
  foreach (['field_logo', 'field_hero_image'] as $field_name) {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      continue;
    }
    $media = $node->get($field_name)->entity;
    if (!$media instanceof MediaInterface || !$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
      continue;
    }
    $image_item = $media->get('field_media_image')->first();
    $file = $image_item?->entity;
    if (!$file instanceof FileInterface) {
      continue;
    }
    $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
    if ($url === '') {
      continue;
    }
    $image = [
      '@type' => 'ImageObject',
      'representativeOfPage' => 'True',
      'url' => $url,
    ];
    if ($image_item && $image_item->width) {
      $image['width'] = (string) $image_item->width;
    }
    if ($image_item && $image_item->height) {
      $image['height'] = (string) $image_item->height;
    }
    return $image;
  }

  return [
    '@type' => 'ImageObject',
    'representativeOfPage' => 'True',
    'url' => motaded_custom_site_logo_absolute_url(),
    'width' => '130',
    'height' => '130',
  ];
}

/**
 * Official platform portal URL when field_external_link is set.
 */
function motaded_custom_platform_official_url(NodeInterface $node): string {
  if (!$node->hasField('field_external_link') || $node->get('field_external_link')->isEmpty()) {
    return '';
  }
  try {
    return $node->get('field_external_link')->first()->getUrl()->setAbsolute()->toString();
  }
  catch (\Throwable) {
    return '';
  }
}

/**
 * Platform category label for schema serviceType.
 */
function motaded_custom_platform_category_label(NodeInterface $node): string {
  if (!$node->hasField('field_category') || $node->get('field_category')->isEmpty()) {
    return '';
  }
  $term = $node->get('field_category')->entity;
  return $term instanceof TermInterface ? trim($term->label()) : '';
}

/**
 * Alters merged metatags for platform nodes (SEO + GovernmentService alignment).
 */
function motaded_custom_platform_metatags_alter(array &$metatags, NodeInterface $node): void {
  $guide_url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $official_url = motaded_custom_platform_official_url($node);

  $description = trim((string) ($metatags['description'] ?? ''));
  if ($description === '' && $node->hasField('field_short_description') && !$node->get('field_short_description')->isEmpty()) {
    $description = trim((string) $node->get('field_short_description')->value);
  }
  if ($description !== '') {
    $metatags['description'] = $description;
    $metatags['schema_organization_description'] = $description;
    $metatags['og_description'] = $description;
    $metatags['twitter_cards_description'] = $description;
    if (!empty($metatags['schema_web_page_description'])) {
      $metatags['schema_web_page_description'] = $description;
    }
  }

  $title = trim((string) ($metatags['title'] ?? ''));
  if ($title !== '' && !empty($metatags['schema_web_page_name'])) {
    $metatags['schema_web_page_name'] = $title;
  }

  $image = motaded_custom_platform_schema_image($node);
  if ($image !== NULL) {
    $metatags['schema_organization_logo'] = SchemaMetatagManager::serialize($image);
    $image_url = (string) ($image['url'] ?? '');
    if ($image_url !== '') {
      $metatags['og_image'] = $image_url;
      $metatags['twitter_cards_image'] = $image_url;
      if (!empty($metatags['schema_web_page_image'])) {
        $metatags['schema_web_page_image'] = SchemaMetatagManager::serialize($image);
      }
    }
  }

  $metatags['schema_organization_id'] = $guide_url . '#government-service';
  if (!empty($metatags['schema_web_page_id'])) {
    $metatags['schema_web_page_id'] = $guide_url . '#webpage';
  }
  if (!empty($metatags['schema_web_page_url'])) {
    $metatags['schema_web_page_url'] = $guide_url;
  }

  // GovernmentService.url → official portal when known; guide stays on WebPage.
  $metatags['schema_organization_url'] = $official_url !== '' ? $official_url : $guide_url;

  $same_as = [];
  if ($official_url !== '' && $official_url !== $guide_url) {
    $same_as[] = $official_url;
  }
  $source_uri = '';
  if ($node->hasField('field_source_link') && !$node->get('field_source_link')->isEmpty()) {
    try {
      $source_uri = $node->get('field_source_link')->first()->getUrl()->setAbsolute()->toString();
    }
    catch (\Throwable) {
      $source_uri = '';
    }
  }
  if ($source_uri !== '' && !in_array($source_uri, $same_as, TRUE)) {
    $same_as[] = $source_uri;
  }
  if ($same_as !== []) {
    $metatags['schema_organization_same_as'] = implode(',', $same_as);
  }

}

/**
 * Builds WebPage + publisher graph fragment for a platform detail page.
 *
 * @return array<string, mixed>|null
 */
function motaded_custom_platform_webpage_graph(NodeInterface $node, string $description, string $title): ?array {
  $guide_url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $site_name = (string) \Drupal::config('system.site')->get('name');
  $site_url = rtrim((string) \Drupal::config('system.site')->get('url'), '/') . '/';

  return [
    '@type' => 'WebPage',
    '@id' => $guide_url . '#webpage',
    'url' => $guide_url,
    'name' => $title !== '' ? $title : $node->label(),
    'description' => $description,
    'inLanguage' => $node->language()->getId(),
    'isPartOf' => [
      '@type' => 'WebSite',
      '@id' => $site_url . '#website',
      'url' => $site_url,
      'name' => $site_name,
    ],
    'about' => [
      '@id' => $guide_url . '#government-service',
    ],
    'publisher' => [
      '@type' => 'Organization',
      'name' => $site_name,
      'url' => $site_url,
      'logo' => [
        '@type' => 'ImageObject',
        'url' => motaded_custom_site_logo_absolute_url(),
      ],
    ],
  ];
}

/**
 * Enriches GovernmentService objects inside JSON-LD @graph for platform pages.
 */
function motaded_custom_platform_enrich_jsonld_graph(array &$graph, NodeInterface $node): void {
  $guide_url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $official_url = motaded_custom_platform_official_url($node);
  $category = motaded_custom_platform_category_label($node);
  $region = '';
  if ($node->hasField('field_region') && !$node->get('field_region')->isEmpty()) {
    $term = $node->get('field_region')->entity;
    $region = $term instanceof TermInterface ? trim($term->label()) : '';
  }

  foreach ($graph as &$item) {
    if (!is_array($item) || ($item['@type'] ?? '') !== 'GovernmentService') {
      continue;
    }

    $item['@id'] = $guide_url . '#government-service';
    if ($official_url !== '') {
      $item['url'] = $official_url;
    }
    if ($category !== '') {
      $item['serviceType'] = $category;
    }
    if ($region !== '') {
      $item['areaServed'] = [
        '@type' => 'Country',
        'name' => $region,
      ];
    }
    $image = motaded_custom_platform_schema_image($node);
    if ($image !== NULL) {
      $item['image'] = $image;
    }
    $item['provider'] = [
      '@type' => 'GovernmentOrganization',
      'name' => $node->label(),
      'url' => $official_url !== '' ? $official_url : $guide_url,
    ];
  }
  unset($item);
}

/**
 * ItemList schema for the /platforms directory.
 *
 * @return array<string, mixed>|null
 */
function motaded_custom_platforms_directory_itemlist(): ?array {
  $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
  try {
    $nids = \Drupal::entityQuery('node')
      ->accessCheck(TRUE)
      ->condition('type', 'platform')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('langcode', $langcode)
      ->sort('title', 'ASC')
      ->execute();
  }
  catch (\Throwable) {
    return NULL;
  }
  if ($nids === []) {
    return NULL;
  }

  $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);
  $items = [];
  $position = 1;
  foreach ($nids as $nid) {
    $node = $nodes[$nid] ?? NULL;
    if (!$node instanceof NodeInterface) {
      continue;
    }
    if ($node->hasTranslation($langcode)) {
      $node = $node->getTranslation($langcode);
    }
    $items[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => $node->label(),
      'url' => $node->toUrl('canonical', ['absolute' => TRUE])->toString(),
    ];
  }
  if ($items === []) {
    return NULL;
  }

  $list_url = Url::fromRoute('view.platforms.page_1', [], ['absolute' => TRUE])->toString();
  $is_ar = $langcode === 'ar';

  return [
    '@type' => 'ItemList',
    '@id' => $list_url . '#itemlist',
    'url' => $list_url,
    'name' => $is_ar ? 'المنصات الحكومية في السعودية' : 'Government platforms in Saudi Arabia',
    'description' => $is_ar
      ? 'دليل المنصات الحكومية والرقمية لدعم الأعمال في المملكة العربية السعودية.'
      : 'Directory of official government and digital platforms supporting business in Saudi Arabia.',
    'numberOfItems' => count($items),
    'itemListElement' => $items,
  ];
}

/**
 * CollectionPage schema for /platforms (companion to ItemList).
 *
 * @return array<string, mixed>|null
 */
function motaded_custom_platforms_directory_collection_page(): ?array {
  $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
  $list_url = Url::fromRoute('view.platforms.page_1', [], ['absolute' => TRUE])->toString();
  $is_ar = $langcode === 'ar';
  $site_name = (string) \Drupal::config('system.site')->get('name');
  $site_url = rtrim((string) \Drupal::config('system.site')->get('url'), '/') . '/';

  return [
    '@type' => 'CollectionPage',
    '@id' => $list_url . '#webpage',
    'url' => $list_url,
    'name' => $is_ar ? 'المنصات الحكومية | ' . $site_name : 'Government platforms in Saudi Arabia | ' . $site_name,
    'description' => $is_ar
      ? 'تصفّح المنصات الحكومية والرقمية لدعم الأعمال في المملكة العربية السعودية.'
      : 'Browse official government and digital platforms supporting business in Saudi Arabia.',
    'inLanguage' => $langcode,
    'isPartOf' => [
      '@type' => 'WebSite',
      '@id' => $site_url . '#website',
      'url' => $site_url,
      'name' => $site_name,
    ],
  ];
}

/**
 * Appends platform-related nodes to a JSON-LD @graph in html_head.
 */
function motaded_custom_platform_jsonld_append_to_html_head(array &$html_head, array $nodes): void {
  if ($nodes === []) {
    return;
  }
  foreach ($html_head as $id => &$attachment) {
    $element = is_array($attachment) && isset($attachment[0]) ? $attachment[0] : $attachment;
    $value = $element['#value'] ?? NULL;
    if (!is_string($value) || $value === '') {
      continue;
    }
    $data = json_decode($value, TRUE);
    if (!is_array($data) || empty($data['@graph']) || !is_array($data['@graph'])) {
      continue;
    }
    foreach ($nodes as $node) {
      $data['@graph'][] = $node;
    }
    $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (isset($attachment[0])) {
      $attachment[0]['#value'] = $encoded;
    }
    else {
      $attachment['#value'] = $encoded;
    }
    return;
  }

  $html_head[] = [
    [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => json_encode([
        '@context' => 'https://schema.org',
        '@graph' => $nodes,
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
      '#attributes' => ['type' => 'application/ld+json'],
    ],
    'motaded_custom_platform_schema',
  ];
}

/**
 * Extends JSON-LD on platform routes (detail + directory).
 */
function motaded_custom_platform_page_jsonld_alter(array &$html_head): void {
  $route_match = \Drupal::routeMatch();
  $route_name = (string) $route_match->getRouteName();

  if ($route_name === 'view.platforms.page_1') {
    $extra = array_values(array_filter([
      motaded_custom_platforms_directory_collection_page(),
      motaded_custom_platforms_directory_itemlist(),
    ]));
    if ($extra !== []) {
      motaded_custom_platform_jsonld_append_to_html_head($html_head, $extra);
    }
    return;
  }

  $node = $route_match->getParameter('node');
  if (!$node instanceof NodeInterface || $node->bundle() !== 'platform') {
    return;
  }

  $manager = \Drupal::service('metatag.manager');
  $tags = $manager->tagsFromEntityWithDefaults($node);
  $title = trim((string) ($tags['title'] ?? ''));
  $description = trim((string) ($tags['description'] ?? ''));

  foreach ($html_head as &$attachment) {
    $element = is_array($attachment) && isset($attachment[0]) ? $attachment[0] : $attachment;
    $value = $element['#value'] ?? NULL;
    if (!is_string($value) || $value === '') {
      continue;
    }
    $data = json_decode($value, TRUE);
    if (!is_array($data) || empty($data['@graph']) || !is_array($data['@graph'])) {
      continue;
    }

    motaded_custom_platform_enrich_jsonld_graph($data['@graph'], $node);

    $has_webpage = FALSE;
    foreach ($data['@graph'] as $item) {
      if (is_array($item) && ($item['@type'] ?? '') === 'WebPage') {
        $has_webpage = TRUE;
        break;
      }
    }
    if (!$has_webpage) {
      $webpage = motaded_custom_platform_webpage_graph($node, $description, $title);
      if ($webpage !== NULL) {
        $data['@graph'][] = $webpage;
      }
    }

    $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (isset($attachment[0])) {
      $attachment[0]['#value'] = $encoded;
    }
    else {
      $attachment['#value'] = $encoded;
    }
    return;
  }
  unset($attachment);
}
