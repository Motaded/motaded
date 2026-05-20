<?php

/**
 * @file
 * Sector page SEO: AboutPage schema, about (industry), breadcrumb fixes.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\schema_metatag\SchemaMetatagManager;
use Drupal\taxonomy\TermInterface;

/**
 * Sector page hero/schema image from field_media.
 *
 * @return array<string, mixed>|null
 */
function motaded_custom_sector_page_schema_image(NodeInterface $node): ?array {
  if (!$node->hasField('field_media') || $node->get('field_media')->isEmpty()) {
    return NULL;
  }
  $media = $node->get('field_media')->entity;
  if ($media === NULL || !$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
    return NULL;
  }
  $image_item = $media->get('field_media_image')->first();
  $file = $image_item?->entity;
  if ($file === NULL) {
    return NULL;
  }
  $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
  if ($url === '') {
    return NULL;
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

/**
 * Industry / sector entity for AboutPage "about".
 *
 * @return array<string, mixed>
 */
function motaded_custom_sector_page_about_entity(NodeInterface $node): array {
  $page_url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $name = motaded_custom_sector_page_schema_title($node);
  $description = motaded_custom_sector_page_schema_description($node);
  if ($description === '') {
    $description = trim((string) ($node->get('field_short_description')->value ?? ''));
  }

  $about = [
    '@type' => 'DefinedTerm',
    '@id' => $page_url . '#industry',
    'name' => $name,
    'termCode' => $name,
  ];
  if ($description !== '') {
    $about['description'] = $description;
  }
  return $about;
}

/**
 * Motaded publisher block for sector AboutPage.
 *
 * @return array<string, mixed>
 */
function motaded_custom_sector_page_publisher(): array {
  if (!function_exists('motaded_custom_site_logo_absolute_url')) {
    require_once __DIR__ . '/motaded_custom.platform_metatag.inc';
  }
  $site_name = (string) \Drupal::config('system.site')->get('name');
  $site_url = \Drupal\Core\Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
  $site_url = rtrim($site_url, '/') . '/';
  return [
    '@type' => 'Organization',
    'name' => $site_name,
    'url' => $site_url,
    'logo' => [
      '@type' => 'ImageObject',
      'url' => \Drupal\Core\Url::fromUri('base:' . \Drupal::service('extension.list.theme')->getPath('motaded_theme') . '/logo.svg', ['absolute' => TRUE])->toString(),
    ],
  ];
}

/**
 * Alters merged metatags for sector_page nodes.
 */
function motaded_custom_sector_page_metatags_alter(array &$metatags, NodeInterface $node): void {
  $canonical = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $title = trim((string) ($metatags['title'] ?? ''));
  if ($title === '') {
    $title = $node->label();
  }

  $description = motaded_custom_sector_page_schema_description($node);
  if ($description === '') {
    $description = trim((string) ($metatags['description'] ?? ''));
  }

  if ($description !== '') {
    $metatags['description'] = $description;
    $metatags['schema_web_page_description'] = $description;
    $metatags['og_description'] = $description;
    $metatags['twitter_cards_description'] = $description;
  }

  $metatags['schema_web_page_type'] = 'AboutPage';
  $metatags['schema_web_page_id'] = $canonical . '#webpage';
  if (!empty($metatags['schema_web_page_in_language'])) {
    $metatags['schema_web_page_in_language'] = $node->language()->getId();
  }
  $metatags['schema_web_page_publisher'] = SchemaMetatagManager::serialize(motaded_custom_sector_page_publisher());

  $image = motaded_custom_sector_page_schema_image($node);
  if ($image !== NULL) {
    $image_url = (string) ($image['url'] ?? '');
    if ($image_url !== '') {
      $metatags['og_image'] = $image_url;
      $metatags['twitter_cards_image'] = $image_url;
    }
  }
}

/**
 * Enriches AboutPage / WebPage nodes in JSON-LD @graph for sector pages.
 */
function motaded_custom_sector_page_schema_title(NodeInterface $node): string {
  if ($node->hasField('field_sector') && !$node->get('field_sector')->isEmpty()) {
    $term = $node->get('field_sector')->entity;
    if ($term instanceof TermInterface) {
      return $term->label();
    }
  }
  return $node->label();
}

/**
 * Best available long description for sector schema / meta.
 */
function motaded_custom_sector_page_schema_description(NodeInterface $node): string {
  if ($node->hasField('field_meta_tags') && !$node->get('field_meta_tags')->isEmpty()) {
    $decoded = json_decode((string) $node->get('field_meta_tags')->value, TRUE);
    if (is_array($decoded) && !empty($decoded['description'])) {
      return trim((string) $decoded['description']);
    }
  }
  $description = trim((string) ($node->get('field_short_description')->value ?? ''));
  if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
    $summary = trim(strip_tags((string) $node->get('body')->summary));
    if ($summary !== '' && strlen($summary) > strlen($description)) {
      $description = $summary;
    }
  }
  return $description;
}

/**
 * Enriches AboutPage / WebPage nodes in JSON-LD @graph for sector pages.
 */
function motaded_custom_sector_page_enrich_jsonld_graph(array &$graph, NodeInterface $node): void {
  $canonical = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  $about = motaded_custom_sector_page_about_entity($node);
  $title = motaded_custom_sector_page_schema_title($node);
  $description = motaded_custom_sector_page_schema_description($node);

  $found = FALSE;
  foreach ($graph as &$item) {
    if (!is_array($item)) {
      continue;
    }
    $type = $item['@type'] ?? '';
    if ($type !== 'WebPage' && $type !== 'AboutPage') {
      continue;
    }
    $found = TRUE;
    $item['@type'] = 'AboutPage';
    $item['@id'] = $canonical . '#webpage';
    $item['url'] = $canonical;
    $item['name'] = $title;
    if ($description !== '') {
      $item['description'] = $description;
    }
    $item['inLanguage'] = $node->language()->getId();
    $item['about'] = $about;
    $site_url = rtrim(\Drupal\Core\Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(), '/') . '/';
    $item['isPartOf'] = [
      '@type' => 'WebSite',
      '@id' => $site_url . '#website',
      'url' => $site_url,
      'name' => (string) \Drupal::config('system.site')->get('name'),
    ];
    $item['publisher'] = motaded_custom_sector_page_publisher();
    $image = motaded_custom_sector_page_schema_image($node);
    if ($image !== NULL) {
      $item['primaryImageOfPage'] = $image;
    }
  }
  unset($item);

  if (!$found) {
    $entry = [
      '@type' => 'AboutPage',
      '@id' => $canonical . '#webpage',
      'url' => $canonical,
      'name' => $title,
      'description' => $description,
      'inLanguage' => $node->language()->getId(),
      'about' => $about,
      'isPartOf' => [
        '@type' => 'WebSite',
        '@id' => rtrim(\Drupal\Core\Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(), '/') . '/#website',
        'url' => rtrim(\Drupal\Core\Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(), '/') . '/',
        'name' => (string) \Drupal::config('system.site')->get('name'),
      ],
      'publisher' => motaded_custom_sector_page_publisher(),
    ];
    $image = motaded_custom_sector_page_schema_image($node);
    if ($image !== NULL) {
      $entry['primaryImageOfPage'] = $image;
    }
    $graph[] = $entry;
  }
}

/**
 * Fixes empty BreadcrumbList item URLs (current page).
 */
function motaded_custom_fix_breadcrumb_list_empty_items(array &$graph): void {
  try {
    $current = \Drupal\Core\Url::fromRoute('<current>', [], ['absolute' => TRUE])->toString();
  }
  catch (\Throwable) {
    return;
  }
  foreach ($graph as &$item) {
    if (!is_array($item) || ($item['@type'] ?? '') !== 'BreadcrumbList') {
      continue;
    }
    if (empty($item['itemListElement']) || !is_array($item['itemListElement'])) {
      continue;
    }
    $last = count($item['itemListElement']) - 1;
    foreach ($item['itemListElement'] as $i => &$list_item) {
      if (!is_array($list_item)) {
        continue;
      }
      $url = trim((string) ($list_item['item'] ?? ''));
      if ($url === '' && $i === $last) {
        $list_item['item'] = $current;
      }
    }
    unset($list_item);
  }
  unset($item);
}

/**
 * JSON-LD post-process for sector_page canonical routes.
 */
function motaded_custom_sector_page_page_jsonld_alter(array &$html_head): void {
  $node = \Drupal::routeMatch()->getParameter('node');
  if (!$node instanceof NodeInterface || $node->bundle() !== 'sector_page') {
    return;
  }

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

    motaded_custom_sector_page_enrich_jsonld_graph($data['@graph'], $node);
    motaded_custom_fix_breadcrumb_list_empty_items($data['@graph']);

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
