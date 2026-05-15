<?php

/**
 * @file
 * Apply Arabic (ar) translations to landing node 374 and all nested paragraphs.
 *
 * Usage (from project root):
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_node_374_ar.php
 */

declare(strict_types=1);

use Drupal\paragraphs\ParagraphInterface;

/** @var array<int, array<string, mixed>> $TRANSLATIONS */
$TRANSLATIONS = require __DIR__ . '/translate_node_374_ar.translations.php';

/**
 * Post-order traversal: children before parent.
 */
function motaded374_postorder_paragraphs(ParagraphInterface $p, array &$order, array &$seen): void {
  $pid = (int) $p->id();
  if (isset($seen[$pid])) {
    return;
  }
  foreach ($p->getFields() as $field) {
    if ($field->getFieldDefinition()->getType() !== 'entity_reference_revisions') {
      continue;
    }
    if ($field->isEmpty()) {
      continue;
    }
    foreach ($field as $item) {
      $e = $item->entity;
      if ($e instanceof ParagraphInterface) {
        motaded374_postorder_paragraphs($e, $order, $seen);
      }
    }
  }
  if (!isset($seen[$pid])) {
    $seen[$pid] = TRUE;
    $order[] = $p;
  }
}

function motaded374_apply_paragraph_map(ParagraphInterface $p, array $map): void {
  if ($map === []) {
    return;
  }
  if (!$p->hasTranslation('ar')) {
    $p->addTranslation('ar', $p->getTranslation('en')->toArray());
  }
  $en = $p->getTranslation('en');
  $ar = $p->getTranslation('ar');

  foreach ($map as $fieldName => $newValue) {
    if (!$p->hasField($fieldName)) {
      continue;
    }
    $def = $p->getFieldDefinition($fieldName);
    if (!$def->isTranslatable()) {
      continue;
    }
    $type = $def->getType();
    if ($type === 'string' || $type === 'string_long') {
      $ar->set($fieldName, [['value' => (string) $newValue]]);
    }
    elseif ($type === 'text_long' || $type === 'text_with_summary') {
      $src = $en->get($fieldName)->isEmpty() ? [] : $en->get($fieldName)->getValue();
      $row = $src[0] ?? ['format' => 'basic_html'];
      $row['value'] = (string) $newValue;
      $ar->set($fieldName, [$row]);
    }
    elseif ($type === 'link' && is_array($newValue)) {
      $opts = [];
      if (!$en->get($fieldName)->isEmpty()) {
        $opts = $en->get($fieldName)->first()->get('options')->getValue() ?? [];
      }
      $ar->set($fieldName, [[
        'uri' => (string) ($newValue['uri'] ?? ''),
        'title' => (string) ($newValue['title'] ?? ''),
        'options' => is_array($newValue['options'] ?? NULL) ? $newValue['options'] : $opts,
      ]]);
    }
  }
  $p->save();
}

// --- Node 374 title + metatag title ---
$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');
/** @var \Drupal\node\NodeInterface $node */
$node = $nodeStorage->load(374);
if (!$node) {
  throw new \RuntimeException('Node 374 not found.');
}
if (!$node->hasTranslation('ar')) {
  $node->addTranslation('ar', array_merge($node->getTranslation('en')->toArray(), [
    'title' => 'الرئيسية',
    'status' => (int) $node->isPublished(),
  ]));
}
$nodeAr = $node->getTranslation('ar');
$nodeAr->setTitle('الرئيسية');
if (!$node->get('field_meta')->isEmpty()) {
  $metaRow = $node->getTranslation('en')->get('field_meta')->getValue();
  if (!empty($metaRow[0]['value'])) {
    $decoded = json_decode($metaRow[0]['value'], TRUE, 512, JSON_THROW_ON_ERROR);
    $decoded['title'] = 'موتاد | تمكين الأعمال في المملكة العربية السعودية';
    $metaRow[0]['value'] = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $nodeAr->set('field_meta', $metaRow);
  }
}
$node->save();

// --- Paragraphs (deepest first) ---
$order = [];
$seen = [];
foreach ($node->get('field_paragraphs') as $item) {
  $p = $item->entity;
  if ($p instanceof ParagraphInterface) {
    motaded374_postorder_paragraphs($p, $order, $seen);
  }
}

foreach ($order as $paragraph) {
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $pid = (int) $paragraph->id();
  if (!isset($TRANSLATIONS[$pid])) {
    continue;
  }
  motaded374_apply_paragraph_map($paragraph, $TRANSLATIONS[$pid]);
}

\Drupal::service('cache_tags.invalidator')->invalidateTags($node->getCacheTags());
\Drupal::logger('motaded_custom')->notice('Node 374 Arabic paragraph translations applied (@count paragraphs).', ['@count' => (string) count($order)]);
