<?php

/**
 * @file
 * Turns SFDA "How Motaded can help" into image + title tabs.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_sfda_help_tabs.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$landings = require dirname(__DIR__) . '/data/sfda_service_landings.dataset.php';

foreach (['medicine', 'devices', 'cosmetics'] as $key) {
  $copy = $landings[$key];
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => $copy['alias'],
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if (!$alias instanceof PathAlias) {
    throw new \RuntimeException($copy['alias'] . ' not found.');
  }
  $nid = (int) str_replace('/node/', '', $alias->getPath());
  $node = Node::load($nid);
  if (!$node instanceof Node) {
    throw new \RuntimeException('Node missing for ' . $copy['alias']);
  }
  $source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

  $help = NULL;
  foreach ($source->get('field_paragraphs') as $item) {
    $paragraph = $item->entity;
    if (!$paragraph instanceof ParagraphInterface) {
      continue;
    }
    $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
      ? (string) $paragraph->get('field_card_type')->value
      : '';
    $title = $paragraph->hasField('field_title') ? trim((string) $paragraph->get('field_title')->value) : '';
    if (in_array($type, ['catalogue', 'tabs'], TRUE) || $title === 'How Motaded can help') {
      $help = $paragraph;
      break;
    }
  }
  if (!$help instanceof ParagraphInterface) {
    throw new \RuntimeException('Help block missing on ' . $copy['alias']);
  }

  $old = $help->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($copy['catalogue']['items'] as $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'],
      'field_body' => [
        'value' => $item['body'] ?? '',
        'format' => 'basic_html',
      ],
    ];
    if (($item['uri'] ?? '') !== '') {
      $values['field_link'] = [
        'uri' => $item['uri'],
        'title' => ($item['link_title'] ?? '') !== '' ? $item['link_title'] : $item['title'],
      ];
    }
    if (!empty($item['media'])) {
      $values['field_media'] = ['target_id' => (int) $item['media']];
    }
    $card = Paragraph::create($values);
    $card->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  $help->set('field_card_type', 'tabs');
  $help->set('field_title', $copy['catalogue']['title']);
  $help->set('field_body', []);
  $help->set('field_sub_title', $copy['catalogue']['note'] ?? '');
  $help->set('field_paragraphs', $children);
  $help->save();
  foreach ($old as $child) {
    $child->delete();
  }

  $refs = [];
  foreach ($source->get('field_paragraphs') as $item) {
    $paragraph = $item->entity;
    if (!$paragraph instanceof ParagraphInterface) {
      continue;
    }
    if ((int) $paragraph->id() === (int) $help->id()) {
      $refs[] = [
        'target_id' => $help->id(),
        'target_revision_id' => $help->getRevisionId(),
      ];
      continue;
    }
    $refs[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }
  $source->set('field_paragraphs', $refs);
  $source->save();
  \Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
  echo "Updated help tabs on {$copy['alias']} (/node/{$nid}).\n";
}
