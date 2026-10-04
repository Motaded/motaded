<?php

/**
 * @file
 * Seeds Arabic translations for the cosmetics landing.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/seed_cosmetics_services_landing_ar.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_COSMETICS_LANDING_ALIAS = '/services/cosmetics-services-saudi-arabia';

$data = require dirname(__DIR__) . '/data/cosmetics_services_landing_ar.dataset.php';
_motaded_cosmetics_sync_webform_ar();
_motaded_cosmetics_ar_locale_strings();

$node = _motaded_cosmetics_ar_load_en_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Cosmetics landing node not found. Seed the English page first.');
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
if ($ar->hasField('field_phone_number') && $en->hasField('field_phone_number')) {
  $ar->set('field_phone_number', $en->get('field_phone_number')->getValue());
}
if ($ar->hasField('field_service') && $en->hasField('field_service')) {
  $ar->set('field_service', $en->get('field_service')->getValue());
}
$ar->set('field_meta', json_encode($data['node']['meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
$ar->setPublished(TRUE);
$ar->save();

foreach ($en->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  match ($paragraph->bundle()) {
    'hero_split_banner' => _motaded_cosmetics_ar_hero($paragraph, $data['hero']),
    'cards' => _motaded_cosmetics_ar_cards($paragraph, $data['sections']),
    'accordions' => _motaded_cosmetics_ar_faq($paragraph, $data['faq']),
    'webform' => _motaded_cosmetics_ar_save($paragraph, $data['form']),
    default => NULL,
  };
}

_motaded_cosmetics_ar_set_alias((int) $node->id());
_motaded_cosmetics_ar_rewrite_inbound_links();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $node->id()]);
echo 'Cosmetics landing AR: /node/' . $node->id() . ' → /ar' . MOTADED_COSMETICS_LANDING_ALIAS . "\n";

function _motaded_cosmetics_ar_load_en_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_COSMETICS_LANDING_ALIAS,
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

function _motaded_cosmetics_ar_set_alias(int $nid): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  foreach ($storage->loadByProperties(['path' => '/node/' . $nid, 'langcode' => 'ar']) as $entity) {
    $entity->delete();
  }
  PathAlias::create([
    'path' => '/node/' . $nid,
    'alias' => MOTADED_COSMETICS_LANDING_ALIAS,
    'langcode' => 'ar',
  ])->save();
}

function _motaded_cosmetics_sync_webform_ar(): void {
  $data = \Drupal::service('config.storage.sync')
    ->createCollection('language.ar')
    ->read('webform.webform.sfda_assessment');
  if (!is_array($data)) {
    echo "Warning: language/ar/webform.webform.sfda_assessment.yml not in config sync.\n";
    return;
  }
  \Drupal::languageManager()
    ->getLanguageConfigOverride('ar', 'webform.webform.sfda_assessment')
    ->setData($data)
    ->save();
  echo "Updated webform sfda_assessment AR.\n";
}

function _motaded_cosmetics_ar_locale_strings(): void {
  if (!\Drupal::moduleHandler()->moduleExists('locale')) {
    return;
  }
  $pairs = [
    'Company situation' => 'وضع المنشأة',
    'Recommended direction' => 'الاتجاه الموصى به',
    'Information group' => 'مجموعة المعلومات',
    'Materials to provide' => 'المواد المطلوب تقديمها',
    'Responsible party' => 'الجهة المسؤولة',
    'WhatsApp' => 'WhatsApp',
    'Options' => 'الخيارات',
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

/**
 * @param array<string, mixed> $values
 */
function _motaded_cosmetics_ar_save(ParagraphInterface $paragraph, array $values): void {
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
 * @return list<array<string, mixed>>
 */
function _motaded_cosmetics_ar_refs(ParagraphInterface $paragraph, string $field): array {
  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  return $en->hasField($field) ? $en->get($field)->getValue() : [];
}

/**
 * @param array<string, mixed> $data
 */
function _motaded_cosmetics_ar_hero(ParagraphInterface $paragraph, array $data): void {
  $values = [
    'field_hero_split_eyebrow' => $data['field_hero_split_eyebrow'],
    'field_hero_split_headline_prefix' => $data['field_hero_split_headline_prefix'],
    'field_hero_split_headline_accent' => $data['field_hero_split_headline_accent'],
    'field_body' => $data['field_body'],
    'field_link' => $data['field_link'],
    'field_hero_split_features' => _motaded_cosmetics_ar_refs($paragraph, 'field_hero_split_features'),
    'field_hero_split_highlights' => [],
  ];
  if ($paragraph->hasField('field_link_secondary')) {
    $secondary = $paragraph->get('field_link_secondary')->getValue();
    if ($secondary === []) {
      $secondary = [$data['field_link_secondary']];
    }
    else {
      $secondary[0]['title'] = $data['field_link_secondary']['title'];
      $secondary[0]['uri'] = $data['field_link_secondary']['uri'] ?? $secondary[0]['uri'];
    }
    $values['field_link_secondary'] = $secondary;
  }
  _motaded_cosmetics_ar_save($paragraph, $values);
}

/**
 * @param array<string, array<string, mixed>> $sections
 */
function _motaded_cosmetics_ar_cards(ParagraphInterface $paragraph, array $sections): void {
  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $en_title = trim((string) $en->get('field_title')->value);
  if (!isset($sections[$en_title])) {
    echo "Warning: no AR copy for cards «{$en_title}».\n";
    return;
  }
  $section = $sections[$en_title];
  $values = [
    'field_title' => $section['title'],
    'field_paragraphs' => _motaded_cosmetics_ar_refs($paragraph, 'field_paragraphs'),
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
  else {
    $values['field_body'] = [];
  }
  if (!empty($section['headers']) && $paragraph->hasField('field_sub_title')) {
    $values['field_sub_title'] = is_array($section['headers'])
      ? implode('|', $section['headers'])
      : $section['headers'];
  }
  _motaded_cosmetics_ar_save($paragraph, $values);

  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $card = $item->entity;
    if (!$card instanceof ParagraphInterface || !isset($section['items'][$i])) {
      continue;
    }
    $row = $section['items'][$i];
    $card_values = [
      'field_title' => $row['title'] ?? '',
      'field_body' => [
        'value' => $row['body'] ?? '',
        'format' => $row['format'] ?? 'basic_html',
      ],
    ];
    if ($card->hasField('field_sub_title')) {
      $subtitle = (string) ($row['subtitle'] ?? '');
      if (($row['badge'] ?? '') !== '') {
        $subtitle = ($subtitle !== '' ? $subtitle . '|' : '') . $row['badge'];
      }
      if ($subtitle !== '' || array_key_exists('subtitle', $row)) {
        $card_values['field_sub_title'] = $subtitle;
      }
    }
    if ($card->hasField('field_link') && !$card->get('field_link')->isEmpty()) {
      $link = $card->get('field_link')->getValue();
      $link[0]['title'] = $row['link_title'] ?? $row['title'];
      $card_values['field_link'] = $link;
    }
    if ($card->hasField('field_media') && !$card->get('field_media')->isEmpty()) {
      $card_values['field_media'] = $card->get('field_media')->getValue();
    }
    _motaded_cosmetics_ar_save($card, $card_values);
    $i++;
  }
}

/**
 * @param array{title: string, items: list<array{question: string, answer: string, answer_format?: string}>} $data
 */
function _motaded_cosmetics_ar_faq(ParagraphInterface $paragraph, array $data): void {
  _motaded_cosmetics_ar_save($paragraph, [
    'field_title' => $data['title'],
    'field_paragraphs' => _motaded_cosmetics_ar_refs($paragraph, 'field_paragraphs'),
  ]);
  $i = 0;
  foreach ($paragraph->get('field_paragraphs') as $item) {
    $row = $item->entity;
    if (!$row instanceof ParagraphInterface || !isset($data['items'][$i])) {
      continue;
    }
    _motaded_cosmetics_ar_save($row, [
      'field_title' => $data['items'][$i]['question'],
      'field_body' => [
        'value' => $data['items'][$i]['answer'],
        'format' => $data['items'][$i]['answer_format'] ?? 'basic_html',
      ],
    ]);
    $i++;
  }
}

/**
 * Point existing AR HTML at the new Arabic cosmetics landing.
 */
function _motaded_cosmetics_ar_rewrite_inbound_links(): void {
  $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('field_body', 'cosmetics-services-saudi-arabia', 'CONTAINS')
    ->execute();
  $updated = 0;
  foreach ($storage->loadMultiple($ids) as $paragraph) {
    if (!$paragraph instanceof ParagraphInterface || !$paragraph->hasTranslation('ar')) {
      continue;
    }
    $tr = $paragraph->getTranslation('ar');
    $changed = FALSE;
    foreach (['field_body'] as $field) {
      if (!$tr->hasField($field) || $tr->get($field)->isEmpty()) {
        continue;
      }
      $item = $tr->get($field)->first();
      if (!$item) {
        continue;
      }
      $value = (string) $item->value;
      $rewritten = (string) preg_replace(
        '#(?<!/ar)/services/cosmetics-services-saudi-arabia#',
        '/ar/services/cosmetics-services-saudi-arabia',
        $value
      );
      if ($rewritten !== $value) {
        $tr->set($field, [
          'value' => $rewritten,
          'format' => $item->format ?? 'basic_html',
        ]);
        $changed = TRUE;
      }
    }
    if ($changed) {
      $tr->save();
      $updated++;
    }
  }
  echo "Rewrote cosmetics AR inbound links in {$updated} paragraphs.\n";
}
