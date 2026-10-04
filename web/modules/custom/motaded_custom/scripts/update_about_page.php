<?php

/**
 * @file
 * Updates About Motaded companies + mid-page CTAs (EN/AR).
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_about_page.php
 *   drush php:script web/modules/custom/motaded_custom/scripts/update_about_page.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$data = require dirname(__DIR__) . '/data/about_page.dataset.php';

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => $data['alias'],
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('About alias /about-us not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node || $node->bundle() !== 'landing_page') {
  throw new \RuntimeException('About landing node not found.');
}

$group = _motaded_about_find_cards($node, $data['group']['match_titles']);
if (!$group instanceof ParagraphInterface) {
  throw new \RuntimeException('About companies paragraph not found.');
}
_motaded_about_apply_group($group, $data['group']);
echo 'Updated companies paragraph ' . $group->id() . ".\n";

$cta_ids = _motaded_about_sync_ctas($node, $data['cta']);
_motaded_about_place_ctas($node, $data['cta']['after'], $cta_ids);

$node = Node::load($nid);
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "About updated: /node/{$nid} → {$data['alias']}\n";

/**
 * Finds a cards paragraph on the About node by EN/AR title.
 */
function _motaded_about_find_cards(Node $node, array $titles): ?ParagraphInterface {
  $source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
  foreach ($source->get('field_paragraphs') as $item) {
    $paragraph = $item->entity;
    if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
      continue;
    }
    foreach (['en', 'ar'] as $langcode) {
      if (!$paragraph->hasTranslation($langcode) && $paragraph->language()->getId() !== $langcode) {
        continue;
      }
      $tr = $paragraph->hasTranslation($langcode) ? $paragraph->getTranslation($langcode) : $paragraph;
      $title = $tr->hasField('field_title') && !$tr->get('field_title')->isEmpty()
        ? trim((string) $tr->get('field_title')->value)
        : '';
      if (in_array($title, $titles, TRUE)) {
        return $paragraph;
      }
    }
  }
  return NULL;
}

/**
 * Writes Four companies copy and ensures Mavzen exists in both languages.
 */
function _motaded_about_apply_group(ParagraphInterface $group, array $spec): void {
  $cards_by_title = [];
  $en_group = $group->hasTranslation('en') ? $group->getTranslation('en') : $group;
  foreach ($en_group->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface) {
      continue;
    }
    foreach (['en', 'ar'] as $langcode) {
      if (!$card->hasTranslation($langcode) && $card->language()->getId() !== $langcode) {
        continue;
      }
      $tr = $card->hasTranslation($langcode) ? $card->getTranslation($langcode) : $card;
      $title = $tr->hasField('field_title') && !$tr->get('field_title')->isEmpty()
        ? trim((string) $tr->get('field_title')->value)
        : '';
      if ($title !== '') {
        $cards_by_title[$title] = $card;
      }
    }
  }

  $refs = [];
  foreach ($spec['cards'] as $card_spec) {
    $card = NULL;
    foreach ($card_spec['match'] as $title) {
      if (isset($cards_by_title[$title])) {
        $card = $cards_by_title[$title];
        break;
      }
    }
    if (!$card instanceof ParagraphInterface) {
      $card = Paragraph::create([
        'type' => 'card',
        'langcode' => 'en',
        'status' => 1,
      ]);
    }
    _motaded_about_set_text_card($card, $card_spec);
    $card = Paragraph::load((int) $card->id());
    $refs[] = [
      'target_id' => (int) $card->id(),
      'target_revision_id' => (int) $card->getRevisionId(),
    ];
  }

  foreach (['en', 'ar'] as $langcode) {
    $fresh = Paragraph::load((int) $group->id());
    $tr = $fresh->hasTranslation($langcode)
      ? $fresh->getTranslation($langcode)
      : $fresh->addTranslation($langcode, ['status' => TRUE]);
    $tr->set('field_title', $spec[$langcode]['title']);
    $tr->set('field_body', [
      'value' => $spec[$langcode]['lede'],
      'format' => 'basic_html',
    ]);
    $tr->set('field_card_type', 'badges');
    $tr->set('field_paragraphs', $refs);
    $tr->save();
  }
}

/**
 * Sets EN/AR title and body on a card paragraph.
 */
function _motaded_about_set_text_card(ParagraphInterface $card, array $spec): void {
  foreach (['en', 'ar'] as $langcode) {
    $tr = $card->hasTranslation($langcode)
      ? $card->getTranslation($langcode)
      : $card->addTranslation($langcode, ['status' => TRUE]);
    $tr->set('field_title', $spec[$langcode]['title']);
    $tr->set('field_body', [
      'value' => $spec[$langcode]['body'],
      'format' => 'basic_html',
    ]);
    $tr->save();
  }
}

/**
 * Creates or updates the two About CTA blocks and returns their ids.
 *
 * @return list<int>
 */
function _motaded_about_sync_ctas(Node $node, array $cta): array {
  $existing = [];
  $source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
  foreach ($source->get('field_paragraphs') as $item) {
    $paragraph = $item->entity;
    if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
      continue;
    }
    $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
      ? (string) $paragraph->get('field_card_type')->value
      : '';
    if ($type === 'cta_block') {
      $existing[] = $paragraph;
    }
  }

  $ids = [];
  for ($i = 0; $i < 2; $i++) {
    $block = $existing[$i] ?? Paragraph::create([
      'type' => 'cards',
      'langcode' => 'en',
      'field_card_type' => 'cta_block',
    ]);
    $child = NULL;
    if (!$block->get('field_paragraphs')->isEmpty()) {
      $child = $block->get('field_paragraphs')->entity;
    }
    if (!$child instanceof ParagraphInterface) {
      $child = Paragraph::create([
        'type' => 'card',
        'langcode' => 'en',
        'status' => 1,
      ]);
    }
    $en_copy = $cta['en'];
    $en_card = $child->hasTranslation('en') ? $child->getTranslation('en') : $child;
    $en_card->set('field_title', $en_copy['title']);
    $en_card->set('field_body', [
      'value' => $en_copy['body'],
      'format' => 'basic_html',
    ]);
    $en_card->set('field_link', [
      'uri' => $cta['uri'],
      'title' => $en_copy['link_title'],
    ]);
    $en_card->save();
    $child = Paragraph::load((int) $child->id());
    $child_ref = [
      'target_id' => (int) $child->id(),
      'target_revision_id' => (int) $child->getRevisionId(),
    ];
    if (!$block->id()) {
      $block->set('field_card_type', 'cta_block');
      $block->set('field_paragraphs', [$child_ref]);
      $block->save();
    }
    $block = Paragraph::load((int) $block->id());
    foreach (['en', 'ar'] as $langcode) {
      $fresh_block = Paragraph::load((int) $block->id());
      $block_tr = $fresh_block->hasTranslation($langcode)
        ? $fresh_block->getTranslation($langcode)
        : $fresh_block->addTranslation($langcode, [
          'status' => TRUE,
          'field_card_type' => 'cta_block',
        ]);
      $block_tr->set('field_card_type', 'cta_block');
      $block_tr->set('field_paragraphs', [$child_ref]);
      $block_tr->save();
    }
    $child = Paragraph::load((int) $child->id());
    $ar_copy = $cta['ar'];
    $ar_card = $child->hasTranslation('ar')
      ? $child->getTranslation('ar')
      : $child->addTranslation('ar', ['status' => TRUE]);
    $ar_card->set('field_title', $ar_copy['title']);
    $ar_card->set('field_body', [
      'value' => $ar_copy['body'],
      'format' => 'basic_html',
    ]);
    $ar_card->set('field_link', [
      'uri' => $cta['uri'],
      'title' => $ar_copy['link_title'],
    ]);
    $ar_card->save();
    $ids[] = (int) $block->id();
    echo 'CTA block ' . $block->id() . " ready.\n";
  }
  return $ids;
}

/**
 * Places the two CTA blocks after team and after clients.
 *
 * @param list<array<int, string>> $after_titles
 * @param list<int> $cta_ids
 */
function _motaded_about_place_ctas(Node $node, array $after_titles, array $cta_ids): void {
  $items = $node->get('field_paragraphs')->getValue();
  $kept = [];
  foreach ($items as $item) {
    if (!in_array((int) $item['target_id'], $cta_ids, TRUE)) {
      $kept[] = $item;
    }
  }

  $inserts = [];
  foreach ($cta_ids as $offset => $cta_id) {
    $after = $after_titles[$offset] ?? [];
    $index = _motaded_about_index_by_titles($kept, $after);
    if ($index === NULL) {
      throw new \RuntimeException('CTA anchor paragraph not found: ' . implode(' / ', $after));
    }
    $inserts[] = ['after' => $index, 'item' => [
      'target_id' => $cta_id,
      'target_revision_id' => Paragraph::load($cta_id)?->getRevisionId(),
    ]];
  }

  usort($inserts, static fn(array $a, array $b): int => $b['after'] <=> $a['after']);
  foreach ($inserts as $insert) {
    array_splice($kept, $insert['after'] + 1, 0, [$insert['item']]);
  }

  $node->set('field_paragraphs', $kept);
  $node->save();
  echo 'CTA order: after team and after clients.' . "\n";
}

/**
 * @param list<array<string, mixed>> $items
 * @param list<string> $titles
 */
function _motaded_about_index_by_titles(array $items, array $titles): ?int {
  foreach ($items as $index => $item) {
    $paragraph = Paragraph::load((int) $item['target_id']);
    if (!$paragraph instanceof ParagraphInterface) {
      continue;
    }
    foreach (['en', 'ar'] as $langcode) {
      if (!$paragraph->hasTranslation($langcode) && $paragraph->language()->getId() !== $langcode) {
        continue;
      }
      $tr = $paragraph->hasTranslation($langcode) ? $paragraph->getTranslation($langcode) : $paragraph;
      $title = $tr->hasField('field_title') && !$tr->get('field_title')->isEmpty()
        ? trim((string) $tr->get('field_title')->value)
        : '';
      if (in_array($title, $titles, TRUE)) {
        return (int) $index;
      }
    }
  }
  return NULL;
}
