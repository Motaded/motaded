<?php

/**
 * @file
 * Seeds Arabic translations for setup cost calculator landing and profile UI.
 *
 * Usage: drush php:script web/modules/custom/motaded_setup_cost/scripts/seed_setup_cost_landing_ar.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$data = require dirname(__DIR__) . '/data/setup_cost_landing_ar.dataset.php';

$node = Node::load(535);
if (!$node instanceof Node) {
  throw new \RuntimeException('Landing node 535 not found. Run seed_setup_cost_landing.php first.');
}

if (!$node->hasTranslation('ar')) {
  $node->addTranslation('ar', [
    'title' => $data['node']['title'],
    'status' => 1,
  ]);
}
else {
  $node = $node->getTranslation('ar');
}
$node->setTitle($data['node']['title']);
if (!empty($data['node']['meta']) && $node->hasField('field_meta')) {
  $node->set('field_meta', json_encode($data['node']['meta'], JSON_UNESCAPED_UNICODE));
}
$node->setPublished(TRUE);
$node->save();
echo "Node 535 AR title and metatags updated.\n";

// Hero + stats.
$hero = Paragraph::load(3607);
if ($hero) {
  _motaded_setup_cost_save_paragraph_translation($hero, 'ar', [
    'field_hero_split_eyebrow' => $data['hero']['field_hero_split_eyebrow'],
    'field_hero_split_headline_prefix' => $data['hero']['field_hero_split_headline_prefix'],
    'field_hero_split_headline_accent' => $data['hero']['field_hero_split_headline_accent'],
    'field_body' => $data['hero']['field_body'],
    'field_link' => $data['hero']['field_link'],
    'field_link_secondary' => $data['hero']['field_link_secondary'],
  ]);
  $stat_ids = [];
  foreach ($hero->get('field_hero_split_stats') as $item) {
    $stat_ids[] = (int) $item->target_id;
  }
  foreach ($data['hero']['stats'] as $i => $def) {
    if (!isset($stat_ids[$i])) {
      continue;
    }
    $stat = Paragraph::load($stat_ids[$i]);
    if ($stat) {
      _motaded_setup_cost_save_paragraph_translation($stat, 'ar', [
        'field_hero_stat_value' => $def['value'],
        'field_hero_stat_label' => $def['label'],
        'field_hero_stat_icon' => $def['icon'],
      ]);
    }
  }
  echo "Hero paragraph AR saved.\n";
}

// SEO cards.
$cards = Paragraph::load(3614);
if ($cards) {
  _motaded_setup_cost_save_paragraph_translation($cards, 'ar', [
    'field_title' => $data['cards']['field_title'],
  ]);
  $card_ids = [];
  foreach ($cards->get('field_paragraphs') as $item) {
    $card_ids[] = (int) $item->target_id;
  }
  foreach ($data['cards']['items'] as $i => $item) {
    if (!isset($card_ids[$i])) {
      continue;
    }
    $card = Paragraph::load($card_ids[$i]);
    if ($card) {
      _motaded_setup_cost_save_paragraph_translation($card, 'ar', [
        'field_title' => $item['title'],
        'field_body' => ['value' => $item['body'], 'format' => 'basic_html'],
      ]);
    }
  }
  echo "Cards section AR saved.\n";
}

// FAQ accordions.
$accordions = Paragraph::load(3620);
if ($accordions) {
  $accordion_children = $accordions->get('field_paragraphs')->getValue();
  _motaded_setup_cost_save_paragraph_translation($accordions, 'ar', [
    'field_title' => $data['faq']['field_title'],
    'field_paragraphs' => $accordion_children,
  ]);
  $acc_ids = [];
  foreach ($accordions->get('field_paragraphs') as $item) {
    $acc_ids[] = (int) $item->target_id;
  }
  foreach ($data['faq']['items'] as $i => $item) {
    if (!isset($acc_ids[$i])) {
      continue;
    }
    $acc = Paragraph::load($acc_ids[$i]);
    if ($acc) {
      _motaded_setup_cost_save_paragraph_translation($acc, 'ar', [
        'field_title' => $item['q'],
        'field_body' => ['value' => $item['a'], 'format' => 'basic_html'],
      ]);
    }
  }
  echo "FAQ AR saved.\n";
}

// Pricing packages (shared section 1597).
foreach ($data['pricing'] as $pid => $pricing_data) {
  $pricing = Paragraph::load((int) $pid);
  if (!$pricing) {
    continue;
  }
  _motaded_setup_cost_save_paragraph_translation($pricing, 'ar', [
    'field_title' => $pricing_data['field_title'],
    'field_sub_title' => $pricing_data['field_sub_title'],
    'field_cost' => $pricing_data['field_cost'],
    'field_plan_details' => $pricing_data['field_plan_details'],
  ]);
  foreach ($pricing->get('field_paragraphs') as $item) {
    $cta = $item->entity;
    if ($cta && $cta->bundle() === 'cta' && $cta->hasField('field_link')) {
      $link = $cta->get('field_link')->getValue();
      if ($link !== []) {
        $link[0]['title'] = $pricing_data['cta'];
        _motaded_setup_cost_save_paragraph_translation($cta, 'ar', [
          'field_link' => $link,
        ]);
      }
    }
  }
  echo "Pricing paragraph {$pid} AR saved.\n";
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:535']);
echo "Done. Import AR profile config with: drush cim -y\n";

/**
 * Saves or updates a paragraph translation.
 *
 * @param \Drupal\paragraphs\ParagraphInterface $paragraph
 * @param string $langcode
 * @param array<string, mixed> $values
 */
function _motaded_setup_cost_save_paragraph_translation($paragraph, string $langcode, array $values): void {
  if ($paragraph->hasTranslation($langcode)) {
    $tr = $paragraph->getTranslation($langcode);
  }
  else {
    $tr = $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  }
  foreach ($values as $field => $value) {
    $tr->set($field, $value);
  }
  $tr->save();
}
