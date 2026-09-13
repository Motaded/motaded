<?php

/**
 * @file
 * Adds DataSaudi trade paragraphs to the customs landing without a full reseed.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/attach_datasaudi_trade_paragraphs.php
 */

declare(strict_types=1);

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_TRADE_LANDING_ALIAS = '/customs-clearance-saudi-arabia';

_motaded_trade_ensure_paragraph_types();
_motaded_trade_enable_landing_bundles();
_motaded_trade_attach_to_customs_node();

function _motaded_trade_ensure_paragraph_types(): void {
  $defs = [
    'trade_market_overview' => [
      'label' => 'Trade market overview',
      'description' => 'Monthly merchandise imports and exports from the DataSaudi snapshot.',
    ],
    'trade_by_product' => [
      'label' => 'Trade by product',
      'description' => 'Merchandise import categories from the DataSaudi snapshot.',
    ],
  ];
  foreach ($defs as $id => $def) {
    if (!ParagraphsType::load($id)) {
      ParagraphsType::create([
        'id' => $id,
        'label' => $def['label'],
        'description' => $def['description'],
      ])->save();
    }
    if (!FieldConfig::loadByName('paragraph', $id, 'field_title')) {
      FieldConfig::create([
        'field_name' => 'field_title',
        'entity_type' => 'paragraph',
        'bundle' => $id,
        'label' => 'Title',
        'required' => TRUE,
        'translatable' => TRUE,
      ])->save();
    }
    if (!FieldConfig::loadByName('paragraph', $id, 'field_body')) {
      FieldConfig::create([
        'field_name' => 'field_body',
        'entity_type' => 'paragraph',
        'bundle' => $id,
        'label' => 'Introduction',
        'required' => FALSE,
        'translatable' => TRUE,
        'settings' => [
          'allowed_formats' => ['basic_html', 'plain_text'],
        ],
      ])->save();
    }
    $manager = \Drupal::service('content_translation.manager');
    if (!$manager->isEnabled('paragraph', $id)) {
      $manager->setEnabled('paragraph', $id, TRUE);
    }
  }

  $sync_dir = dirname(\Drupal::root()) . '/config/sync';
  $sync = new FileStorage($sync_dir);
  $storage = \Drupal::service('config.storage');
  foreach ([
    'core.entity_form_display.paragraph.trade_market_overview.default',
    'core.entity_form_display.paragraph.trade_by_product.default',
    'core.entity_view_display.paragraph.trade_market_overview.default',
    'core.entity_view_display.paragraph.trade_by_product.default',
    'language.content_settings.paragraph.trade_market_overview',
    'language.content_settings.paragraph.trade_by_product',
  ] as $name) {
    $data = $sync->read($name);
    if (is_array($data) && !$storage->exists($name)) {
      $storage->write($name, $data);
    }
  }
  \Drupal::service('entity_type.manager')->clearCachedDefinitions();
  \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();
}

function _motaded_trade_enable_landing_bundles(): void {
  $field = FieldConfig::loadByName('node', 'landing_page', 'field_paragraphs');
  if (!$field) {
    return;
  }
  $settings = $field->getSetting('handler_settings') ?: [];
  $changed = FALSE;
  foreach (['trade_market_overview' => 58, 'trade_by_product' => 59] as $bundle => $weight) {
    if (!isset($settings['target_bundles'][$bundle])) {
      $settings['target_bundles'][$bundle] = $bundle;
      $settings['target_bundles_drag_drop'][$bundle] = [
        'weight' => $weight,
        'enabled' => TRUE,
      ];
      $changed = TRUE;
    }
  }
  if ($changed) {
    $field->setSetting('handler_settings', $settings);
    $field->save();
  }
}

function _motaded_trade_attach_to_customs_node(): void {
  $node = _motaded_trade_load_customs_node();
  if (!$node instanceof Node) {
    throw new \RuntimeException('Customs landing node not found.');
  }
  $en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
  $items = $en->get('field_paragraphs')->referencedEntities();

  $has_market = FALSE;
  $has_product = FALSE;
  $overview_delta = NULL;
  $cargo_delta = NULL;
  foreach ($items as $delta => $paragraph) {
    if (!$paragraph instanceof Paragraph) {
      continue;
    }
    if ($paragraph->bundle() === 'trade_market_overview') {
      $has_market = TRUE;
    }
    if ($paragraph->bundle() === 'trade_by_product') {
      $has_product = TRUE;
    }
    $title = $paragraph->hasField('field_title') ? trim((string) $paragraph->get('field_title')->value) : '';
    if ($paragraph->bundle() === 'media_lead' && $title === 'Service Overview') {
      $overview_delta = (int) $delta;
    }
    if ($paragraph->bundle() === 'cards' && $title === 'Cargo Categories and Additional Requirements') {
      $cargo_delta = (int) $delta;
    }
  }

  $market = $has_market ? NULL : _motaded_trade_create_market_paragraph();
  $product = $has_product ? NULL : _motaded_trade_create_product_paragraph();
  if ($market === NULL && $product === NULL) {
    echo "DataSaudi trade paragraphs already present on nid=" . $en->id() . "\n";
    return;
  }

  $refs = $en->get('field_paragraphs')->getValue();
  $new_refs = [];
  foreach ($refs as $delta => $ref) {
    if ($product !== NULL && $cargo_delta !== NULL && (int) $delta === $cargo_delta) {
      $new_refs[] = [
        'target_id' => $product->id(),
        'target_revision_id' => $product->getRevisionId(),
      ];
    }
    $new_refs[] = $ref;
    if ($market !== NULL && $overview_delta !== NULL && (int) $delta === $overview_delta) {
      $new_refs[] = [
        'target_id' => $market->id(),
        'target_revision_id' => $market->getRevisionId(),
      ];
    }
  }
  if ($market !== NULL && $overview_delta === NULL) {
    array_splice($new_refs, 2, 0, [[
      'target_id' => $market->id(),
      'target_revision_id' => $market->getRevisionId(),
    ]]);
  }
  if ($product !== NULL && $cargo_delta === NULL) {
    $new_refs[] = [
      'target_id' => $product->id(),
      'target_revision_id' => $product->getRevisionId(),
    ];
  }

  $en->set('field_paragraphs', $new_refs);
  $en->save();
  if ($node->hasTranslation('ar')) {
    $ar = Node::load((int) $node->id())->getTranslation('ar');
    $ar->set('field_paragraphs', $new_refs);
    $ar->save();
  }
  \Drupal::service('cache_tags.invalidator')->invalidateTags([
    'node:' . $en->id(),
    'datasaudi_trade',
  ]);
  echo 'Attached DataSaudi trade paragraphs on nid=' . $en->id() . "\n";
}

function _motaded_trade_create_market_paragraph(): Paragraph {
  $paragraph = Paragraph::create([
    'type' => 'trade_market_overview',
    'langcode' => 'en',
    'field_title' => 'Saudi Arabia: A Market for International Trade',
    'field_body' => [
      'value' => '<p>Explore Saudi Arabia’s merchandise trade through official monthly import and export statistics.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $paragraph->save();
  $ar = $paragraph->addTranslation('ar', ['status' => TRUE]);
  $ar->set('field_title', 'المملكة العربية السعودية: سوق للتجارة الدولية');
  $ar->set('field_body', [
    'value' => '<p>استكشف تجارة المملكة العربية السعودية السلعية عبر الإحصاءات الرسمية الشهرية للاستيراد والتصدير.</p>',
    'format' => 'basic_html',
  ]);
  $ar->save();
  return $paragraph;
}

function _motaded_trade_create_product_paragraph(): Paragraph {
  $paragraph = Paragraph::create([
    'type' => 'trade_by_product',
    'langcode' => 'en',
    'field_title' => 'Explore Trade by Product',
    'field_body' => [
      'value' => '<p>See the leading product categories in Saudi Arabia’s merchandise imports.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $paragraph->save();
  $ar = $paragraph->addTranslation('ar', ['status' => TRUE]);
  $ar->set('field_title', 'استكشف التجارة حسب المنتج');
  $ar->set('field_body', [
    'value' => '<p>اطّلع على أبرز فئات المنتجات في واردات المملكة العربية السعودية السلعية.</p>',
    'format' => 'basic_html',
  ]);
  $ar->save();
  return $paragraph;
}

function _motaded_trade_load_customs_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_TRADE_LANDING_ALIAS,
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if (!$alias instanceof PathAlias) {
    return NULL;
  }
  $nid = (int) str_replace('/node/', '', $alias->getPath());
  $node = Node::load($nid);
  return $node instanceof Node ? $node : NULL;
}
