<?php

/**
 * @file
 * Seeds Arabic translations for the customs clearance landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/seed_customs_clearance_landing_ar.php
 *
 * Run after the English seed, or from that script.
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

if (!defined('MOTADED_CUSTOMS_LANDING_ALIAS')) {
  define('MOTADED_CUSTOMS_LANDING_ALIAS', '/services/customs-clearance-saudi-arabia');
}

_motaded_customs_seed_arabic();

function _motaded_customs_seed_arabic(): void {
  $data = require dirname(__DIR__) . '/data/customs_clearance_landing_ar.dataset.php';
  _motaded_customs_sync_webform_ar();

  $node = _motaded_customs_ar_load_en_node();
  if (!$node instanceof Node) {
    throw new \RuntimeException('Customs landing node not found. Run seed_customs_clearance_landing.php first.');
  }

  $en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
  $refs = $en->get('field_paragraphs')->getValue();

  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', [
      'title' => $data['node']['title'],
      'status' => 1,
    ]);
  }
  $ar = Node::load((int) $node->id())->getTranslation('ar');
  $ar->setTitle($data['node']['title']);
  $ar->set('field_paragraphs', $refs);
  $ar->set('field_form', []);
  $ar->set('field_faq', []);
  $ar->set('field_meta', json_encode($data['node']['meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  $ar->setPublished(TRUE);
  $ar->save();

  foreach ($en->get('field_paragraphs') as $item) {
    $paragraph = $item->entity;
    if (!$paragraph instanceof ParagraphInterface) {
      continue;
    }
    match ($paragraph->bundle()) {
      'hero_split_banner' => _motaded_customs_ar_hero($paragraph, $data['hero']),
      'media_lead' => _motaded_customs_ar_save($paragraph, [
        'field_title' => $data['overview']['field_title'],
        'field_body' => $data['overview']['field_body'],
        'field_link' => $data['overview']['field_link'],
      ]),
      'trade_market_overview' => _motaded_customs_ar_save($paragraph, $data['trade_market']),
      'glance_block' => _motaded_customs_ar_glance($paragraph, $data['glance']),
      'cards' => _motaded_customs_ar_cards($paragraph, $data['sections']),
      'accordions' => _motaded_customs_ar_faq($paragraph, $data['faq']),
      'webform' => _motaded_customs_ar_save($paragraph, [
        'field_body' => $data['form']['field_body'],
      ]),
      default => NULL,
    };
  }

  _motaded_customs_ar_set_alias((int) $node->id());
  _motaded_customs_ar_locale_strings();
  \Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $node->id()]);
  echo 'Customs landing AR: /node/' . $node->id() . ' → /ar' . MOTADED_CUSTOMS_LANDING_ALIAS . "\n";
}

function _motaded_customs_ar_locale_strings(): void {
  if (!\Drupal::moduleHandler()->moduleExists('locale')) {
    return;
  }
  $pairs = [
    'WhatsApp' => 'WhatsApp',
    'Previous step' => 'الخطوة السابقة',
    'Next step' => 'الخطوة التالية',
    'Total imports and exports' => 'إجمالي الواردات والصادرات',
    'Imports' => 'الواردات',
    'Exports' => 'الصادرات',
    'Monthly imports and exports · SAR billion' => 'الواردات والصادرات الشهرية · مليار ريال',
    'SAR billion' => 'مليار ريال',
    'Source: GASTAT via DataSaudi' => 'المصدر: الهيئة العامة للإحصاء عبر DataSaudi',
    'vs @period' => 'مقابل @period',
    'Imports @v' => 'الواردات @v',
    'Exports @v' => 'الصادرات @v',
    'Monthly merchandise imports and exports in SAR billion' => 'الواردات والصادرات السلعية الشهرية بالمليار ريال',
    'Official trade figures will appear here after the next successful import.' => 'ستظهر الأرقام الرسمية للتجارة هنا بعد نجاح الاستيراد التالي.',
    'Imports plus exports in the same month' => 'الواردات مضافاً إليها الصادرات في الشهر نفسه',
    'Cargo categories' => 'فئات الشحنات',
  ];
  /** @var \Drupal\locale\StringStorageInterface $storage */
  $storage = \Drupal::service('locale.storage');
  foreach ($pairs as $source => $translation) {
    $string = $storage->findString(['source' => $source, 'context' => '']);
    if (!$string) {
      $string = $storage->createString(['source' => $source, 'context' => ''])->save();
    }
    $existing = $storage->findTranslation([
      'lid' => $string->lid,
      'language' => 'ar',
    ]);
    if ($existing && $existing->translation !== NULL && $existing->translation !== '') {
      if ((string) $existing->translation !== $translation) {
        $existing->setValues(['translation' => $translation])->save();
      }
      continue;
    }
    $storage->createTranslation([
      'lid' => $string->lid,
      'language' => 'ar',
      'translation' => $translation,
    ])->save();
  }
}

function _motaded_customs_ar_load_en_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_LANDING_ALIAS,
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if (!$alias instanceof PathAlias) {
    return NULL;
  }
  $nid = (int) str_replace('/node/', '', $alias->getPath());
  $node = Node::load($nid);
  if (!$node instanceof Node || $node->bundle() !== 'landing_page') {
    return NULL;
  }
  return $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
}

function _motaded_customs_ar_set_alias(int $nid): void {
  if (function_exists('_motaded_customs_set_alias')) {
    _motaded_customs_set_alias($nid, MOTADED_CUSTOMS_LANDING_ALIAS, 'ar');
    return;
  }
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  foreach ($storage->loadByProperties(['path' => '/node/' . $nid, 'langcode' => 'ar']) as $entity) {
    $entity->delete();
  }
  PathAlias::create([
    'path' => '/node/' . $nid,
    'alias' => MOTADED_CUSTOMS_LANDING_ALIAS,
    'langcode' => 'ar',
  ])->save();
}

function _motaded_customs_sync_webform_ar(): void {
  $data = \Drupal::service('config.storage.sync')
    ->createCollection('language.ar')
    ->read('webform.webform.customs_clearance_quote');
  if (!is_array($data)) {
    echo "Warning: language/ar/webform.webform.customs_clearance_quote.yml not in config sync.\n";
    return;
  }
  \Drupal::languageManager()
    ->getLanguageConfigOverride('ar', 'webform.webform.customs_clearance_quote')
    ->setData($data)
    ->save();
  echo "Updated webform customs_clearance_quote AR.\n";
}

/**
 * @param array<string, mixed> $values
 */
function _motaded_customs_ar_save(ParagraphInterface $paragraph, array $values): void {
  if ($paragraph->hasTranslation('ar')) {
    $tr = $paragraph->getTranslation('ar');
  }
  else {
    $tr = $paragraph->addTranslation('ar', ['status' => $paragraph->isPublished()]);
  }
  foreach ($values as $field => $value) {
    if ($paragraph->hasField($field)) {
      $tr->set($field, $value);
    }
  }
  $tr->save();
}

/**
 * @param array<string, mixed> $data
 */
function _motaded_customs_ar_refs(ParagraphInterface $paragraph, string $field): array {
  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  return $en->hasField($field) ? $en->get($field)->getValue() : [];
}

function _motaded_customs_ar_hero(ParagraphInterface $paragraph, array $data): void {
  $values = [
    'field_hero_split_eyebrow' => $data['field_hero_split_eyebrow'],
    'field_hero_split_headline_prefix' => $data['field_hero_split_headline_prefix'],
    'field_hero_split_headline_accent' => $data['field_hero_split_headline_accent'],
    'field_body' => $data['field_body'],
    'field_link' => $data['field_link'],
    'field_hero_split_features' => _motaded_customs_ar_refs($paragraph, 'field_hero_split_features'),
    'field_hero_split_highlights' => [],
  ];
  if ($paragraph->hasField('field_link_secondary') && !$paragraph->get('field_link_secondary')->isEmpty()) {
    $secondary = $paragraph->get('field_link_secondary')->getValue();
    $secondary[0]['title'] = $data['field_link_secondary']['title'] ?? ($secondary[0]['title'] ?? 'WhatsApp');
    $values['field_link_secondary'] = $secondary;
  }
  _motaded_customs_ar_save($paragraph, $values);
  $i = 0;
  foreach ($paragraph->get('field_hero_split_features') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($data['features'][$i])) {
      continue;
    }
    _motaded_customs_ar_save($card, [
      'field_title' => $data['features'][$i]['title'],
      'field_body' => [
        'value' => $data['features'][$i]['body'],
        'format' => 'plain_text',
      ],
    ]);
    $i++;
  }
}

/**
 * @param array<string, mixed> $data
 */
function _motaded_customs_ar_glance(ParagraphInterface $paragraph, array $data): void {
  _motaded_customs_ar_save($paragraph, [
    'field_glance_eyebrow' => $data['field_glance_eyebrow'],
    'field_glance_title' => $data['field_glance_title'],
    'field_body' => $data['field_body'],
    'field_glance_highlight' => _motaded_customs_ar_refs($paragraph, 'field_glance_highlight'),
    'field_glance_stats' => _motaded_customs_ar_refs($paragraph, 'field_glance_stats'),
  ]);
  foreach ($paragraph->get('field_glance_highlight') as $item) {
    $highlight = $item->entity;
    if ($highlight instanceof ParagraphInterface) {
      _motaded_customs_ar_save($highlight, $data['highlight']);
    }
  }
  $i = 0;
  foreach ($paragraph->get('field_glance_stats') as $item) {
    $stat = $item->entity;
    if (!$stat instanceof ParagraphInterface || !isset($data['stats'][$i])) {
      continue;
    }
    _motaded_customs_ar_save($stat, $data['stats'][$i]);
    $i++;
  }
}

/**
 * @param array<string, array<string, mixed>> $sections
 */
function _motaded_customs_ar_cards(ParagraphInterface $paragraph, array $sections): void {
  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $en_title = trim((string) $en->get('field_title')->value);
  if (!isset($sections[$en_title])) {
    echo "Warning: no AR copy for cards «{$en_title}».\n";
    return;
  }
  $section = $sections[$en_title];
  $values = [
    'field_title' => $section['title'],
    'field_paragraphs' => _motaded_customs_ar_refs($paragraph, 'field_paragraphs'),
  ];
  if ($en->hasField('field_card_type') && !$en->get('field_card_type')->isEmpty()) {
    $values['field_card_type'] = $en->get('field_card_type')->value;
  }
  if (($section['lede'] ?? '') !== '') {
    $values['field_body'] = [
      'value' => $section['lede'],
      'format' => 'basic_html',
    ];
  }
  if (!empty($section['headers']) && $paragraph->hasField('field_sub_title')) {
    $values['field_sub_title'] = implode('|', $section['headers']);
  }
  _motaded_customs_ar_save($paragraph, $values);

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($section['items'][$i])) {
      continue;
    }
    $row = $section['items'][$i];
    $card_values = [
      'field_title' => $row['title'],
      'field_body' => [
        'value' => $row['body'],
        'format' => $row['format'] ?? 'basic_html',
      ],
    ];
    if ($card->hasField('field_sub_title') && isset($row['subtitle'])) {
      $card_values['field_sub_title'] = $row['subtitle'];
    }
    if ($card->hasField('field_link') && !$card->get('field_link')->isEmpty()) {
      $link = $card->get('field_link')->getValue();
      $link[0]['title'] = $row['link_title'] ?? 'فتح الصفحة';
      $card_values['field_link'] = $link;
    }
    _motaded_customs_ar_save($card, $card_values);
    $i++;
  }
}

/**
 * @param list<array{question: string, answer: string, answer_format?: string}> $items
 */
function _motaded_customs_ar_faq(ParagraphInterface $paragraph, array $items): void {
  _motaded_customs_ar_save($paragraph, [
    'field_title' => 'الأسئلة الشائعة',
    'field_paragraphs' => _motaded_customs_ar_refs($paragraph, 'field_paragraphs'),
  ]);
  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $row = $item->entity;
    if (!$row instanceof ParagraphInterface || !isset($items[$i])) {
      continue;
    }
    _motaded_customs_ar_save($row, [
      'field_title' => $items[$i]['question'],
      'field_body' => [
        'value' => $items[$i]['answer'],
        'format' => $items[$i]['answer_format'] ?? 'basic_html',
      ],
    ]);
    $i++;
  }
}
