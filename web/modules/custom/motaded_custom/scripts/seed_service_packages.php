<?php

/**
 * @file
 * Seeds / updates Motaded service package landing pages.
 *
 * Simple layout (entrepreneur, RHQ): banner → body → FAQ.
 * Pricing layout (office solutions): banner → pricing section → banner_sm → body → FAQ.
 *
 * Usage:
 *   python3 scripts/split_service_package_bodies.py
 *   drush php:script web/modules/custom/motaded_custom/scripts/seed_service_packages.php
 *   drush cr
 */

declare(strict_types=1);

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;

$data_dir = DRUPAL_ROOT . '/../web/modules/custom/motaded_custom/data/service_packages';
$packages = require DRUPAL_ROOT . '/../web/modules/custom/motaded_custom/data/service_packages.dataset.php';
$pricing_menu_parent = 'menu_link_content:' . MOTADED_PRICING_MENU_PARENT_UUID;

/**
 * Upserts a path alias for a node path.
 */
function _motaded_seed_service_packages_upsert_alias(string $path, string $alias, string $langcode): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  $existing = $storage->loadByProperties([
    'path' => $path,
    'langcode' => $langcode,
  ]);
  $entity = $existing ? reset($existing) : PathAlias::create([
    'path' => $path,
    'langcode' => $langcode,
  ]);
  $entity->set('alias', $alias);
  $entity->save();
  echo "  Alias {$langcode}: {$alias}\n";
}

/**
 * Loads a node by English alias or existing nid.
 */
function _motaded_seed_service_packages_resolve_node(array $package): ?Node {
  $storage = \Drupal::entityTypeManager()->getStorage('node');

  if (!empty($package['existing_nid'])) {
    $node = $storage->load((int) $package['existing_nid']);
    if ($node instanceof Node && $node->bundle() === 'landing_page') {
      return $node;
    }
  }

  $alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  $aliases = $alias_storage->loadByProperties([
    'alias' => $package['alias_en'],
    'langcode' => 'en',
  ]);
  if ($aliases) {
    $path = reset($aliases)->get('path')->value;
    if (preg_match('/node\/(\d+)/', (string) $path, $matches)) {
      $node = $storage->load((int) $matches[1]);
      if ($node instanceof Node) {
        return $node;
      }
    }
  }

  return NULL;
}

/**
 * Builds FAQ field values.
 */
function _motaded_seed_service_packages_faq_values(array $items): array {
  $values = [];
  foreach ($items as $item) {
    $values[] = [
      'question' => $item['question'],
      'answer' => $item['answer'],
      'answer_format' => 'plain_text',
    ];
  }
  return $values;
}

/**
 * Reads generated HTML from disk.
 */
function _motaded_seed_service_packages_read_file(string $path): string {
  if (!is_readable($path)) {
    throw new \RuntimeException('Missing file: ' . $path);
  }
  return (string) file_get_contents($path);
}

/**
 * Reads slim page body HTML.
 */
function _motaded_seed_service_packages_body(string $data_dir, string $machine_name, string $langcode): string {
  return _motaded_seed_service_packages_read_file($data_dir . '/' . $machine_name . '.body.' . $langcode . '.html');
}

/**
 * Reads pricing card plan details HTML.
 */
function _motaded_seed_service_packages_pricing_details(
  string $data_dir,
  string $machine_name,
  int $index,
  string $langcode,
  bool $strip_leading_h3 = FALSE,
): string {
  $path = $data_dir . '/pricing/' . $machine_name . '/card_' . $index . '.' . $langcode . '.html';
  $html = _motaded_seed_service_packages_read_file($path);
  if ($strip_leading_h3) {
    $html = (string) preg_replace('/^\s*<h3>.*?<\/h3>/s', '', $html, 1);
  }
  return $html;
}

/**
 * Saves or updates a paragraph translation.
 *
 * @param array<string, mixed> $values
 */
function _motaded_seed_service_packages_save_paragraph_translation(Paragraph $paragraph, string $langcode, array $values): void {
  if ($paragraph->hasTranslation($langcode)) {
    $translation = $paragraph->getTranslation($langcode);
  }
  else {
    $translation = $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  }
  foreach ($values as $field => $value) {
    $translation->set($field, $value);
  }
  $translation->save();
}

/**
 * Creates a CTA child paragraph with EN + AR link labels.
 */
function _motaded_seed_service_packages_create_cta(array $en_meta, array $ar_meta): Paragraph {
  $cta = Paragraph::create([
    'type' => 'cta',
    'langcode' => 'en',
    'field_link' => [
      'uri' => $en_meta['cta_uri'],
      'title' => $en_meta['cta_label'],
    ],
  ]);
  $cta->save();
  _motaded_seed_service_packages_save_paragraph_translation($cta, 'ar', [
    'field_link' => [
      'uri' => $ar_meta['cta_uri'],
      'title' => $ar_meta['cta_label'],
    ],
  ]);
  return $cta;
}

/**
 * Creates a pricing card with nested CTA.
 */
function _motaded_seed_service_packages_create_pricing_card(
  array $en_meta,
  array $ar_meta,
  string $plan_details_en,
  string $plan_details_ar,
): Paragraph {
  $cta = _motaded_seed_service_packages_create_cta($en_meta, $ar_meta);

  $pricing = Paragraph::create([
    'type' => 'pricing',
    'langcode' => 'en',
    'field_title' => $en_meta['title'],
    'field_cost' => $en_meta['cost'],
    'field_sub_title' => $en_meta['subtitle'],
    'field_plan_details' => [
      'value' => $plan_details_en,
      'format' => 'full_html',
    ],
    'field_paragraphs' => [
      [
        'target_id' => $cta->id(),
        'target_revision_id' => $cta->getRevisionId(),
      ],
    ],
  ]);
  $pricing->save();

  _motaded_seed_service_packages_save_paragraph_translation($pricing, 'ar', [
    'field_title' => $ar_meta['title'],
    'field_cost' => $ar_meta['cost'],
    'field_sub_title' => $ar_meta['subtitle'],
    'field_plan_details' => [
      'value' => $plan_details_ar,
      'format' => 'full_html',
    ],
  ]);

  return $pricing;
}

/**
 * Creates hero banner paragraph.
 */
function _motaded_seed_service_packages_create_banner(array $en, array $ar): Paragraph {
  $banner = Paragraph::create([
    'type' => 'banner',
    'langcode' => 'en',
    'field_title' => $en['banner_title'],
    'field_sub_title' => $en['banner_subtitle'],
    'field_banner_type' => 'banner_style_3',
    'field_show_quick_links' => 0,
  ]);
  $banner->save();

  if (!$banner->hasTranslation('ar')) {
    $banner->addTranslation('ar', [
      'field_title' => $ar['banner_title'],
      'field_sub_title' => $ar['banner_subtitle'],
      'field_banner_type' => 'banner_style_3',
      'field_show_quick_links' => 0,
    ])->save();
  }
  else {
    $banner_ar = $banner->getTranslation('ar');
    $banner_ar->set('field_title', $ar['banner_title']);
    $banner_ar->set('field_sub_title', $ar['banner_subtitle']);
    $banner_ar->save();
  }

  return $banner;
}

/**
 * Creates bottom CTA banner.
 */
function _motaded_seed_service_packages_create_banner_sm(array $en, array $ar): Paragraph {
  $banner_sm = Paragraph::create([
    'type' => 'banner_sm',
    'langcode' => 'en',
    'field_title' => $en['banner_sm']['title'],
    'field_body' => [
      'value' => $en['banner_sm']['body'],
      'format' => 'plain_text',
    ],
    'field_link' => [
      'uri' => $en['banner_sm']['cta_uri'],
      'title' => $en['banner_sm']['cta_label'],
    ],
    'field_show_quick_links' => 1,
    'field_bg_color' => 'green',
  ]);
  $banner_sm->save();

  if (!$banner_sm->hasTranslation('ar')) {
    $banner_sm->addTranslation('ar', [
      'field_title' => $ar['banner_sm']['title'],
      'field_body' => [
        'value' => $ar['banner_sm']['body'],
        'format' => 'plain_text',
      ],
      'field_link' => [
        'uri' => $ar['banner_sm']['cta_uri'],
        'title' => $ar['banner_sm']['cta_label'],
      ],
      'field_show_quick_links' => 1,
      'field_bg_color' => 'green',
    ])->save();
  }
  else {
    $banner_sm_ar = $banner_sm->getTranslation('ar');
    $banner_sm_ar->set('field_title', $ar['banner_sm']['title']);
    $banner_sm_ar->set('field_body', [
      'value' => $ar['banner_sm']['body'],
      'format' => 'plain_text',
    ]);
    $banner_sm_ar->set('field_link', [
      'uri' => $ar['banner_sm']['cta_uri'],
      'title' => $ar['banner_sm']['cta_label'],
    ]);
    $banner_sm_ar->save();
  }

  return $banner_sm;
}

/**
 * Creates pricing section with cards.
 */
function _motaded_seed_service_packages_create_pricing_section(
  string $data_dir,
  string $machine_name,
  array $en,
  array $ar,
  int $card_count,
  bool $strip_leading_h3 = FALSE,
): Paragraph {
  $pricing_refs = [];
  for ($i = 1; $i <= $card_count; $i++) {
    $pricing = _motaded_seed_service_packages_create_pricing_card(
      $en['pricing_cards'][$i - 1],
      $ar['pricing_cards'][$i - 1],
      _motaded_seed_service_packages_pricing_details($data_dir, $machine_name, $i, 'en', $strip_leading_h3),
      _motaded_seed_service_packages_pricing_details($data_dir, $machine_name, $i, 'ar', $strip_leading_h3),
    );
    $pricing_refs[] = [
      'target_id' => $pricing->id(),
      'target_revision_id' => $pricing->getRevisionId(),
    ];
  }

  $section = Paragraph::create([
    'type' => 'section',
    'langcode' => 'en',
    'field_paragraphs' => $pricing_refs,
  ]);
  $section->save();
  _motaded_seed_service_packages_save_paragraph_translation($section, 'ar', []);
  return $section;
}

/**
 * Builds paragraph reference list for field_paragraphs.
 */
function _motaded_seed_service_packages_paragraph_ref(Paragraph $paragraph): array {
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

/**
 * Creates or updates a main-menu link under Pricing.
 */
function _motaded_seed_service_packages_upsert_menu_link(
  int $nid,
  array $menu,
  string $pricing_menu_parent,
): void {
  $uri = 'entity:node/' . $nid;
  $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
  $existing = $storage->loadByProperties([
    'menu_name' => 'main',
    'link.uri' => $uri,
  ]);
  $link = $existing ? reset($existing) : NULL;

  if (!$link) {
    $link = MenuLinkContent::create([
      'title' => $menu['title_en'],
      'link' => ['uri' => $uri],
      'menu_name' => 'main',
      'parent' => $pricing_menu_parent,
      'weight' => (int) $menu['weight'],
      'langcode' => 'en',
      'enabled' => 1,
    ]);
  }
  else {
    $link->set('title', $menu['title_en']);
    $link->set('link', ['uri' => $uri]);
    $link->set('parent', $pricing_menu_parent);
    $link->set('weight', (int) $menu['weight']);
    $link->set('enabled', 1);
  }
  $link->save();

  $values = [
    'title' => $menu['title_ar'],
    'link' => ['uri' => $uri],
    'enabled' => TRUE,
  ];
  if (!$link->hasTranslation('ar')) {
    $link->addTranslation('ar', $values)->save();
  }
  else {
    $link_ar = $link->getTranslation('ar');
    $link_ar->set('title', $menu['title_ar']);
    $link_ar->set('link', ['uri' => $uri]);
    $link_ar->set('enabled', 1);
    $link_ar->save();
  }

  echo "  Menu: {$menu['title_en']} -> {$uri} (AR: {$menu['title_ar']})\n";
}

foreach ($packages as $machine_name => $package) {
  echo "Package: {$machine_name}\n";

  $node = _motaded_seed_service_packages_resolve_node($package);
  if (!$node) {
    $node = Node::create([
      'type' => 'landing_page',
      'title' => $package['en']['title'],
      'langcode' => 'en',
      'status' => 1,
      'uid' => 1,
    ]);
    echo "  Created new landing_page node.\n";
  }
  else {
    echo "  Updating landing_page nid={$node->id()}.\n";
  }

  $en = $package['en'];
  $ar = $package['ar'];
  $layout = $package['layout'] ?? 'simple';

  $banner = _motaded_seed_service_packages_create_banner($en, $ar);
  $paragraph_refs = [_motaded_seed_service_packages_paragraph_ref($banner)];

  if ($layout === 'pricing') {
    $card_count = (int) ($package['pricing_card_count'] ?? count($en['pricing_cards']));
    $section = _motaded_seed_service_packages_create_pricing_section(
      $data_dir,
      $machine_name,
      $en,
      $ar,
      $card_count,
      $machine_name === 'office_solutions_packages',
    );
    $banner_sm = _motaded_seed_service_packages_create_banner_sm($en, $ar);
    $paragraph_refs[] = _motaded_seed_service_packages_paragraph_ref($section);
    $paragraph_refs[] = _motaded_seed_service_packages_paragraph_ref($banner_sm);
    $layout_label = "banner + section ({$card_count} cards) + banner_sm";
  }
  else {
    $layout_label = 'banner only';
  }

  $node->setTitle($en['title']);
  $node->setPublished(TRUE);
  $node->set('body', [
    'value' => _motaded_seed_service_packages_body($data_dir, $machine_name, 'en'),
    'format' => 'full_html',
  ]);
  $node->set('field_faq', _motaded_seed_service_packages_faq_values($en['faq']));
  $node->set('field_paragraphs', $paragraph_refs);
  $node->save();

  $nid = (int) $node->id();
  $path = '/node/' . $nid;
  _motaded_seed_service_packages_upsert_alias($path, $package['alias_en'], 'en');

  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', [
      'title' => $ar['title'],
      'status' => 1,
      'body' => [
        'value' => _motaded_seed_service_packages_body($data_dir, $machine_name, 'ar'),
        'format' => 'full_html',
      ],
      'field_faq' => _motaded_seed_service_packages_faq_values($ar['faq']),
      'field_paragraphs' => $paragraph_refs,
    ])->save();
    echo "  Added AR translation.\n";
  }
  else {
    $node_ar = $node->getTranslation('ar');
    $node_ar->setTitle($ar['title']);
    $node_ar->setPublished(TRUE);
    $node_ar->set('body', [
      'value' => _motaded_seed_service_packages_body($data_dir, $machine_name, 'ar'),
      'format' => 'full_html',
    ]);
    $node_ar->set('field_faq', _motaded_seed_service_packages_faq_values($ar['faq']));
    $node_ar->set('field_paragraphs', $paragraph_refs);
    $node_ar->save();
    echo "  Updated AR translation.\n";
  }

  _motaded_seed_service_packages_upsert_alias($path, $package['alias_ar'], 'ar');
  echo "  Paragraphs: {$layout_label}\n";
  if (!empty($package['menu'])) {
    _motaded_seed_service_packages_upsert_menu_link($nid, $package['menu'], $pricing_menu_parent);
  }
  echo "  Ready: nid={$nid} EN {$package['alias_en']} AR {$package['alias_ar']}\n\n";
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list']);
echo "Service package landing pages seeded.\n";
