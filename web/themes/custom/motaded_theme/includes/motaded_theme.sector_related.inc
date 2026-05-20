<?php

/**
 * @file
 * Sector hub: list rows + split columns (Invest Saudi style).
 */

declare(strict_types=1);

use Drupal\Core\Cache\Cache;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Builds section data for sector_page full: layouts + row items for Twig.
 *
 * @return array{sections: array<int, array<string, mixed>>, cache_tags: string[]}
 */
function motaded_theme_sector_page_related_build(NodeInterface $node): array {
  $sections = [];
  $cache_tags = ['node_list', 'node:' . $node->id()];

  $em = \Drupal::entityTypeManager();
  $storage = $em->getStorage('node');
  $date_formatter = \Drupal::service('date.formatter');
  $langcode = $node->language()->getId();
  $entity_repository = \Drupal::service('entity.repository');
  $t = static function (string $string, array $args = []) use ($langcode): string {
    return (string) t($string, $args, ['langcode' => $langcode]);
  };

  $sector_label = $node->getTitle();
  $sector_nid = (int) $node->id();
  $tid = NULL;
  if ($node->hasField('field_sector') && !$node->get('field_sector')->isEmpty()) {
    $tid = (int) $node->get('field_sector')->target_id;
    if ($tid > 0) {
      $cache_tags[] = 'taxonomy_term:' . $tid;
    }
    else {
      $tid = NULL;
    }
  }

  $max = 8;
  $max_col = 6;

  $merge_node_tags = static function (array $tags, NodeInterface $n): array {
    return Cache::mergeTags($tags, $n->getCacheTags());
  };

  // —— Events (full-width list) ——
  $event_nids = $storage->getQuery()
    ->condition('type', 'event')
    ->condition('status', 1)
    ->condition('field_sector_pages.target_id', $sector_nid)
    ->sort('field_event_start', 'ASC')
    ->range(0, $max)
    ->accessCheck(TRUE)
    ->execute();
  if ($event_nids) {
    $rows = [];
    $events = $storage->loadMultiple($event_nids);
    foreach ($events as $en) {
      if (!$en instanceof NodeInterface || $en->bundle() !== 'event' || !$en->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $en);
      $rows[] = _motaded_theme_sector_row_event($en, $date_formatter, $langcode, $entity_repository);
    }
    if ($rows !== []) {
      $sections[] = [
        'layout' => 'list',
        'id' => 'sector-related-events',
        'title' => Markup::create($t('Upcoming events in @sector', ['@sector' => $sector_label])),
        'more_url' => Url::fromUserInput('/events')->toString(),
        'more_label' => $t('View all events'),
        'rows' => $rows,
      ];
    }
  }

  if ($tid === NULL) {
    $cache_tags = array_values(array_unique($cache_tags));
    return ['sections' => $sections, 'cache_tags' => $cache_tags];
  }

  // —— Insights + Platforms (split) ——
  $insight_nids = $storage->getQuery()
    ->condition('type', 'market_insight')
    ->condition('status', 1)
    ->condition('field_sector.target_id', $tid)
    ->sort('field_year', 'DESC')
    ->sort('changed', 'DESC')
    ->range(0, $max_col)
    ->accessCheck(TRUE)
    ->execute();
  $insight_rows = [];
  if ($insight_nids) {
    $insights = $storage->loadMultiple($insight_nids);
    foreach ($insights as $in) {
      if (!$in instanceof NodeInterface || $in->bundle() !== 'market_insight' || !$in->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $in);
      $insight_rows[] = _motaded_theme_sector_row_insight($in, $em, $langcode, $entity_repository);
    }
  }

  $platform_nids = $storage->getQuery()
    ->condition('type', 'platform')
    ->condition('status', 1)
    ->condition('field_sector.target_id', $tid)
    ->sort('title', 'ASC')
    ->range(0, $max_col)
    ->accessCheck(TRUE)
    ->execute();
  $platform_rows = [];
  if ($platform_nids) {
    $platforms = $storage->loadMultiple($platform_nids);
    foreach ($platforms as $pf) {
      if (!$pf instanceof NodeInterface || $pf->bundle() !== 'platform' || !$pf->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $pf);
      $platform_rows[] = _motaded_theme_sector_row_platform($pf, $em, $langcode, $entity_repository);
    }
  }

  if ($insight_rows !== [] || $platform_rows !== []) {
    $sections[] = [
      'layout' => 'split',
      'id' => 'sector-split-insights-platforms',
      'left' => [
        'id' => 'sector-related-insights',
        'title' => $t('Market insights'),
        'more_url' => Url::fromUserInput('/insights')->toString(),
        'more_label' => $t('View all insights'),
        'rows' => $insight_rows,
        'empty' => $t('No insights linked yet.'),
      ],
      'right' => [
        'id' => 'sector-related-platforms',
        'title' => $t('Key platforms for this sector'),
        'more_url' => Url::fromUserInput('/platforms')->toString(),
        'more_label' => $t('View all platforms'),
        'rows' => $platform_rows,
        'empty' => $t('No platforms linked yet.'),
      ],
    ];
  }

  // —— Services + Documents (split) ——
  $svc_nids = $storage->getQuery()
    ->condition('type', 'page')
    ->condition('status', 1)
    ->condition('field_display_on_services', 1)
    ->condition('field_sector.target_id', $tid)
    ->sort('title', 'ASC')
    ->range(0, $max_col)
    ->accessCheck(TRUE)
    ->execute();
  $svc_rows = [];
  if ($svc_nids) {
    $pages = $storage->loadMultiple($svc_nids);
    foreach ($pages as $pn) {
      if (!$pn instanceof NodeInterface || $pn->bundle() !== 'page' || !$pn->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $pn);
      $svc_rows[] = _motaded_theme_sector_row_service($pn, $langcode, $entity_repository);
    }
  }

  $doc_nids = $storage->getQuery()
    ->condition('type', 'document')
    ->condition('status', 1)
    ->condition('field_sector.target_id', $tid)
    ->sort('created', 'DESC')
    ->range(0, $max_col)
    ->accessCheck(TRUE)
    ->execute();
  $doc_rows = [];
  if ($doc_nids) {
    $docs = $storage->loadMultiple($doc_nids);
    foreach ($docs as $dn) {
      if (!$dn instanceof NodeInterface || $dn->bundle() !== 'document' || !$dn->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $dn);
      $doc_rows[] = _motaded_theme_sector_row_document($dn, $langcode, $entity_repository);
    }
  }

  if ($svc_rows !== [] || $doc_rows !== []) {
    $sections[] = [
      'layout' => 'split',
      'id' => 'sector-split-services-docs',
      'left' => [
        'id' => 'sector-related-services',
        'title' => Markup::create($t('Services for @sector', ['@sector' => $sector_label])),
        'more_url' => Url::fromUserInput('/services')->toString(),
        'more_label' => $t('View all services'),
        'rows' => $svc_rows,
        'empty' => $t('No services linked yet.'),
      ],
      'right' => [
        'id' => 'sector-related-documents',
        'title' => $t('Documents & resources'),
        'more_url' => Url::fromUserInput('/library')->toString(),
        'more_label' => $t('View all documents'),
        'rows' => $doc_rows,
        'empty' => $t('No documents linked yet.'),
      ],
    ];
  }

  // —— News (full-width date | title rows) ——
  $news_nids = $storage->getQuery()
    ->condition('type', 'news')
    ->condition('status', 1)
    ->condition('field_sector.target_id', $tid)
    ->sort('created', 'DESC')
    ->range(0, $max)
    ->accessCheck(TRUE)
    ->execute();
  if ($news_nids) {
    $news_rows = [];
    $news_nodes = $storage->loadMultiple($news_nids);
    foreach ($news_nodes as $nn) {
      if (!$nn instanceof NodeInterface || $nn->bundle() !== 'news' || !$nn->access('view')) {
        continue;
      }
      $cache_tags = $merge_node_tags($cache_tags, $nn);
      $news_rows[] = _motaded_theme_sector_row_news($nn, $date_formatter, $langcode, $entity_repository);
    }
    if ($news_rows !== []) {
      $sections[] = [
        'layout' => 'list',
        'variant' => 'news',
        'id' => 'sector-related-news',
        'title' => $t('Latest news & updates'),
        'more_url' => Url::fromUserInput('/news')->toString(),
        'more_label' => $t('View all news'),
        'rows' => $news_rows,
      ];
    }
  }

  $cache_tags = array_values(array_unique($cache_tags));

  return [
    'sections' => $sections,
    'cache_tags' => $cache_tags,
  ];
}

/**
 * @return array{type: string, url: string, title: string, meta: string, badge: string}
 */
function _motaded_theme_sector_row_event(NodeInterface $node, $date_formatter, string $langcode, $entity_repository): array {
  $node = $entity_repository->getTranslationFromContext($node, $langcode);
  $range = '';
  if ($node->hasField('field_event_start') && !$node->get('field_event_start')->isEmpty()) {
    $start_ts = strtotime((string) $node->get('field_event_start')->value);
    if ($start_ts) {
      $range = $date_formatter->format($start_ts, 'custom', 'j M Y', NULL, $langcode);
    }
  }
  if ($node->hasField('field_event_end') && !$node->get('field_event_end')->isEmpty()
    && $node->hasField('field_event_start') && !$node->get('field_event_start')->isEmpty()) {
    $start_ts = strtotime((string) $node->get('field_event_start')->value);
    $end_ts = strtotime((string) $node->get('field_event_end')->value);
    if ($start_ts && $end_ts) {
      $range = $date_formatter->format($start_ts, 'custom', 'j M', NULL, $langcode)
        . ' – ' . $date_formatter->format($end_ts, 'custom', 'j M Y', NULL, $langcode);
    }
  }

  $place = '';
  if ($node->hasField('field_location') && !$node->get('field_location')->isEmpty()) {
    $place = trim((string) $node->get('field_location')->value);
  }
  $city = '';
  if ($place === '' && $node->hasField('field_city') && !$node->get('field_city')->isEmpty()) {
    $city_term = $node->get('field_city')->entity;
    if ($city_term instanceof TermInterface) {
      $city_term = $entity_repository->getTranslationFromContext($city_term, $langcode);
      $city = $city_term->getName();
    }
  }
  if ($place === '' && $city !== '') {
    $place = $city;
  }

  $type_label = '';
  if ($node->hasField('field_event_type') && !$node->get('field_event_type')->isEmpty()) {
    $term = $node->get('field_event_type')->entity;
    if ($term instanceof TermInterface) {
      $term = $entity_repository->getTranslationFromContext($term, $langcode);
      $type_label = $term->getName();
    }
  }

  $meta_parts = array_values(array_filter([$range, $place, $type_label], static fn ($v) => $v !== '' && $v !== NULL));

  return [
    'type' => 'event',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'meta' => implode(' · ', $meta_parts),
    'badge' => '',
  ];
}

/**
 * Resolves a node in the requested content language (falls back per Drupal rules).
 */
function _motaded_theme_sector_translated_node(NodeInterface $node, string $langcode, $entity_repository): NodeInterface {
  return $entity_repository->getTranslationFromContext($node, $langcode);
}

/**
 * @return array{type: string, url: string, title: string, excerpt: string, thumbnail: array}
 */
function _motaded_theme_sector_row_insight(NodeInterface $node, $em, string $langcode, $entity_repository): array {
  $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);
  $thumb = [];
  if ($node->hasField('field_insight_thumbnail') && !$node->get('field_insight_thumbnail')->isEmpty()) {
    $media = $node->get('field_insight_thumbnail')->entity;
    if ($media instanceof MediaInterface) {
      $vm = $media->bundle() === 'image' ? 'news' : 'default';
      $thumb = $em->getViewBuilder('media')->view($media, $vm);
    }
  }

  $excerpt = '';
  if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
    $item = $node->get('body')->first();
    $summary = trim((string) ($item?->summary ?? ''));
    $value = (string) ($item?->value ?? '');
    $raw = $summary !== '' ? $summary : $value;
    if ($raw !== '') {
      $excerpt = motaded_theme_plain_text_truncate(\Drupal\Component\Utility\Html::decodeEntities(strip_tags($raw)), 160);
    }
  }

  return [
    'type' => 'insight',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'excerpt' => $excerpt,
    'thumbnail' => $thumb,
  ];
}

/**
 * @return array{type: string, url: string, title: string, logo: array}
 */
function _motaded_theme_sector_row_platform(NodeInterface $node, $em, string $langcode, $entity_repository): array {
  $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);
  $logo = [];
  if ($node->hasField('field_logo') && !$node->get('field_logo')->isEmpty()) {
    $media = $node->get('field_logo')->entity;
    if ($media instanceof MediaInterface) {
      $logo = $em->getViewBuilder('media')->view($media, 'platform_logo');
    }
  }

  return [
    'type' => 'platform',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'logo' => $logo,
  ];
}

/**
 * @return array{type: string, url: string, title: string, excerpt: string}
 */
function _motaded_theme_sector_row_service(NodeInterface $node, string $langcode, $entity_repository): array {
  $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);
  $excerpt = '';
  if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
    $raw = (string) $node->get('body')->value;
    if ($raw !== '') {
      $excerpt = motaded_theme_plain_text_truncate(\Drupal\Component\Utility\Html::decodeEntities(strip_tags($raw)), 140);
    }
  }

  return [
    'type' => 'service',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'excerpt' => $excerpt,
  ];
}

/**
 * @return array{type: string, url: string, title: string, meta: string}
 */
function _motaded_theme_sector_row_document(NodeInterface $node, ?string $langcode = NULL, $entity_repository = NULL): array {
  if ($langcode !== NULL && $entity_repository !== NULL) {
    $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);
  }
  $meta = '';
  if ($node->hasField('field_document') && !$node->get('field_document')->isEmpty()) {
    $media = $node->get('field_document')->entity;
    if ($media instanceof MediaInterface && $media->hasField('field_media_document') && !$media->get('field_media_document')->isEmpty()) {
      $file = $media->get('field_media_document')->entity;
      if ($file) {
        $ext = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION) ?: 'FILE');
        $bytes = (int) $file->getSize();
        $human = _motaded_theme_format_bytes($bytes);
        $meta = $human !== '' ? ($ext . ' · ' . $human) : $ext;
      }
    }
  }

  if ($meta === '' && $node->hasField('field_short_description') && !$node->get('field_short_description')->isEmpty()) {
    $meta = motaded_theme_plain_text_truncate(trim((string) $node->get('field_short_description')->value), 80);
  }

  return [
    'type' => 'document',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'meta' => $meta,
  ];
}

/**
 * @return array{type: string, url: string, title: string, date: string, excerpt: string}
 */
function _motaded_theme_sector_row_news(NodeInterface $node, $date_formatter, string $langcode, $entity_repository): array {
  $node = _motaded_theme_sector_translated_node($node, $langcode, $entity_repository);
  $created_ts = (int) $node->getCreatedTime();
  $date_line = $date_formatter->format($created_ts, 'custom', 'j M Y', NULL, $langcode);

  $excerpt = '';
  if ($node->hasField('body') && !$node->get('body')->isEmpty()) {
    $item = $node->get('body')->first();
    $summary = trim((string) ($item?->summary ?? ''));
    $value = (string) ($item?->value ?? '');
    $raw = $summary !== '' ? $summary : $value;
    if ($raw !== '') {
      $excerpt = motaded_theme_plain_text_truncate(\Drupal\Component\Utility\Html::decodeEntities(strip_tags($raw)), 180);
    }
  }

  return [
    'type' => 'news',
    'url' => $node->toUrl('canonical', ['language' => $node->language()])->toString(),
    'title' => $node->getTitle(),
    'date' => $date_line,
    'excerpt' => $excerpt,
  ];
}

function _motaded_theme_format_bytes(int $bytes): string {
  if ($bytes <= 0) {
    return '';
  }
  $units = ['B', 'KB', 'MB', 'GB'];
  $i = 0;
  $v = (float) $bytes;
  while ($v >= 1024 && $i < count($units) - 1) {
    $v /= 1024;
    $i++;
  }
  return ($i === 0 ? (string) (int) $v : number_format($v, $i >= 2 ? 1 : 0, '.', '')) . ' ' . $units[$i];
}
