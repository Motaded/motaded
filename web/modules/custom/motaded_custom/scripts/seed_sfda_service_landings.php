<?php

/**
 * @file
 * Seeds the English medicine, medical device and cosmetics landings.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/seed_sfda_service_landings.php
 */

declare(strict_types=1);

use Drupal\block\Entity\Block;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\webform\Entity\Webform;

$landings = require dirname(__DIR__) . '/data/sfda_service_landings.dataset.php';

_motaded_sfda_enable_webform_paragraph();
_motaded_sfda_sync_webform();
_motaded_sfda_hide_setup_prefooter($landings);

foreach ($landings as $landing) {
  $node = _motaded_sfda_load_or_create_node($landing['alias'], $landing['title']);
  $paragraphs = [
    _motaded_sfda_hero($landing['hero']),
    ...(_motaded_sfda_landing_has_requirements($landing)
      ? [
        _motaded_sfda_cards($landing['requirements']['title'], $landing['requirements']['lede'], $landing['requirements']['items'], 'prose'),
        _motaded_sfda_cards($landing['support']['title'], $landing['support']['lede'], $landing['support']['items'], 'matrix', implode('|', $landing['support']['headers'] ?? [])),
        _motaded_sfda_cards($landing['mdma']['title'], $landing['mdma']['lede'], $landing['mdma']['items'], 'tabs'),
        _motaded_sfda_cards($landing['representation']['title'], $landing['representation']['lede'], $landing['representation']['items'], 'formats'),
        _motaded_sfda_cards($landing['features']['title'], $landing['features']['lede'], $landing['features']['items'], 'features'),
        _motaded_sfda_cards($landing['documents']['title'], $landing['documents']['lede'], $landing['documents']['items'], 'matrix', implode('|', $landing['documents']['headers'] ?? [])),
        _motaded_sfda_cards($landing['process']['title'], $landing['process']['lede'] ?? '', $landing['process']['items'], 'stages'),
      ]
      : (_motaded_sfda_landing_has_eligibility($landing)
      ? [
        ...(_motaded_sfda_landing_has_context($landing)
          ? [
            _motaded_sfda_cards($landing['context']['title'], $landing['context']['lede'], $landing['context']['items'], 'context'),
          ]
          : []),
        _motaded_sfda_cards($landing['eligibility']['title'], $landing['eligibility']['lede'], $landing['eligibility']['items'], 'eligibility'),
        _motaded_sfda_cards($landing['catalogue']['title'], $landing['catalogue']['lede'], $landing['catalogue']['items'], 'tabs', $landing['catalogue']['note'] ?? ''),
      ]
      : (_motaded_sfda_landing_has_listing($landing)
      ? [
        _motaded_sfda_cards($landing['listing']['title'], $landing['listing']['lede'], $landing['listing']['items'], 'formats'),
        _motaded_sfda_cards($landing['support']['title'], $landing['support']['lede'], $landing['support']['items'], 'formats'),
        _motaded_sfda_cards($landing['deliverables']['title'], $landing['deliverables']['lede'], $landing['deliverables']['items'], 'formats'),
        _motaded_sfda_cards($landing['process']['title'], $landing['process']['lede'] ?? '', $landing['process']['items'], 'stages'),
        _motaded_sfda_cards($landing['briefing']['title'], $landing['briefing']['lede'], $landing['briefing']['items'] ?? [], 'prose'),
        _motaded_sfda_cards($landing['features']['title'], $landing['features']['lede'], $landing['features']['items'], 'features'),
        _motaded_sfda_cards($landing['composition']['title'], $landing['composition']['lede'], $landing['composition']['items'], 'prose'),
        _motaded_sfda_cards($landing['documents']['title'], $landing['documents']['lede'] ?? '', $landing['documents']['items'], 'matrix', implode('|', $landing['documents']['headers'] ?? [])),
      ]
      : (_motaded_sfda_landing_is_restructured($landing['alias'] ?? '')
        ? [
          _motaded_sfda_cards($landing['needs']['title'], $landing['needs']['lede'], $landing['needs']['items'], 'needs'),
          _motaded_sfda_cards($landing['situations']['title'], $landing['situations']['lede'], $landing['situations']['items'], 'situations'),
          _motaded_sfda_cards($landing['catalogue']['title'], $landing['catalogue']['lede'], $landing['catalogue']['items'], 'tabs', $landing['catalogue']['note'] ?? ''),
        ]
        : [
          _motaded_sfda_cards($landing['needs']['title'], $landing['needs']['lede'], $landing['needs']['items'], 'needs'),
          _motaded_sfda_cards($landing['catalogue']['title'], $landing['catalogue']['lede'], $landing['catalogue']['items'], 'tabs', $landing['catalogue']['note'] ?? ''),
          _motaded_sfda_cards($landing['situations']['title'], $landing['situations']['lede'], $landing['situations']['items'], 'situations'),
        ])))),
    ...((_motaded_sfda_landing_is_restructured($landing['alias'] ?? '') || empty($landing['directory']))
      ? []
      : [
        _motaded_sfda_cards($landing['directory']['title'], $landing['directory']['lede'], $landing['directory']['items'], 'directory', $landing['directory']['note'] ?? ''),
      ]),
    ...((_motaded_sfda_landing_has_requirements($landing) || _motaded_sfda_landing_has_listing($landing))
      ? []
      : [
        _motaded_sfda_cards($landing['features']['title'], $landing['features']['lede'], $landing['features']['items'], 'features'),
      ]),
    ...((_motaded_sfda_landing_has_requirements($landing) || _motaded_sfda_landing_has_listing($landing))
      ? []
      : [
        _motaded_sfda_cards($landing['process']['title'], $landing['process']['lede'] ?? '', $landing['process']['items'], !empty($landing['eligibility']) ? 'stages' : 'stack'),
      ]),
    ...((_motaded_sfda_landing_has_requirements($landing) || _motaded_sfda_landing_has_listing($landing))
      ? []
      : [
        _motaded_sfda_cards($landing['documents']['title'], '', $landing['documents']['items'], 'lists'),
      ]),
    _motaded_sfda_cards($landing['fees']['title'], $landing['fees']['lede'], $landing['fees']['items'], 'fees'),
    _motaded_sfda_form($landing['form']['body']),
    _motaded_sfda_faq($landing['faq']),
    _motaded_sfda_cards('Official guidance and related support', '', $landing['related_extra'], 'related'),
  ];

  $refs = [];
  foreach ($paragraphs as $paragraph) {
    $refs[] = _motaded_sfda_pref($paragraph);
  }

  $node->setTitle($landing['title']);
  $node->set('field_paragraphs', $refs);
  $node->set('field_form', []);
  $node->set('field_faq', []);
  if ($node->hasField('field_phone_number')) {
    $node->set('field_phone_number', '+966 53 979 7197');
  }
  if ($node->hasField('field_service')) {
    $node->set('field_service', 1);
  }
  $node->set('field_meta', json_encode([
    'title' => $landing['meta_title'],
    'description' => $landing['meta_description'],
  ], JSON_UNESCAPED_SLASHES));
  $node->save();

  $nid = (int) $node->id();
  _motaded_sfda_set_alias($nid, $landing['alias'], 'en');
  echo $landing['title'] . ': /node/' . $nid . ' → ' . $landing['alias'] . "\n";
}

function _motaded_sfda_landing_has_requirements(array $landing): bool {
  return !empty($landing['requirements']['title']);
}

function _motaded_sfda_landing_has_eligibility(array $landing): bool {
  return !empty($landing['eligibility']['items']);
}

function _motaded_sfda_landing_has_listing(array $landing): bool {
  return !empty($landing['listing']['title']);
}

/**
 * @param array{lede?: string, items?: list<array<string, mixed>>} $listing
 */
function _motaded_sfda_listing_body(array $listing): string {
  $html = (string) ($listing['lede'] ?? '');
  foreach ($listing['items'] ?? [] as $item) {
    $heading = trim((string) ($item['title'] ?? ''));
    if ($heading !== '') {
      $html .= '<h3>' . $heading . '</h3>';
    }
    $html .= (string) ($item['body'] ?? '');
  }
  return $html;
}

function _motaded_sfda_landing_has_context(array $landing): bool {
  return !empty($landing['context']['items']);
}

function _motaded_sfda_landing_is_restructured(string $alias): bool {
  return in_array($alias, [
    '/services/medicine-services-saudi-arabia',
    '/services/medical-device-services-saudi-arabia',
    '/services/cosmetics-services-saudi-arabia',
  ], TRUE);
}

function _motaded_sfda_enable_webform_paragraph(): void {
  $field = FieldConfig::loadByName('node', 'landing_page', 'field_paragraphs');
  if (!$field) {
    return;
  }
  $settings = $field->getSetting('handler_settings') ?: [];
  $bundles = $settings['target_bundles'] ?? [];
  if (isset($bundles['webform'])) {
    return;
  }
  $settings['target_bundles']['webform'] = 'webform';
  $settings['target_bundles_drag_drop']['webform'] = [
    'weight' => 57,
    'enabled' => TRUE,
  ];
  $field->setSetting('handler_settings', $settings);
  $field->save();
}

function _motaded_sfda_sync_webform(): void {
  $data = \Drupal::service('config.storage.sync')->read('webform.webform.sfda_assessment');
  if (!is_array($data)) {
    throw new \RuntimeException('Could not read sfda_assessment from config sync.');
  }
  \Drupal::configFactory()->getEditable('webform.webform.sfda_assessment')->setData($data)->save(TRUE);
  if (Webform::load('sfda_assessment')) {
    echo "Updated webform sfda_assessment.\n";
  }
}

function _motaded_sfda_hide_setup_prefooter(array $landings): void {
  $block = Block::load('motaded_theme_ctaprefooter');
  if (!$block) {
    return;
  }
  $needed = [];
  foreach ($landings as $landing) {
    $needed[] = $landing['alias'];
    $needed[] = '/ar' . $landing['alias'];
  }
  $visibility = $block->getVisibility();
  $existing = preg_split('/\s+/', trim((string) ($visibility['request_path']['pages'] ?? ''))) ?: [];
  $pages = array_values(array_unique(array_filter(array_merge($existing, $needed))));
  $block->setVisibilityConfig('request_path', [
    'id' => 'request_path',
    'negate' => TRUE,
    'pages' => implode("\n", $pages),
  ]);
  $block->save();
}

function _motaded_sfda_load_or_create_node(string $alias, string $title): Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => $alias,
    'langcode' => 'en',
  ]);
  $alias_entity = $aliases ? reset($aliases) : NULL;
  if ($alias_entity instanceof PathAlias) {
    $nid = (int) str_replace('/node/', '', $alias_entity->getPath());
    $node = Node::load($nid);
    if ($node instanceof Node && $node->bundle() === 'landing_page') {
      return $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
    }
  }
  $node = Node::create([
    'type' => 'landing_page',
    'langcode' => 'en',
    'status' => 1,
    'title' => $title,
  ]);
  $node->save();
  return $node;
}

function _motaded_sfda_set_alias(int $nid, string $alias, string $langcode): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  $existing = $storage->loadByProperties([
    'path' => '/node/' . $nid,
    'langcode' => $langcode,
  ]);
  foreach ($existing as $entity) {
    if ($entity instanceof PathAlias && $entity->getAlias() === $alias) {
      return;
    }
    if ($entity instanceof PathAlias) {
      $entity->setAlias($alias)->save();
      return;
    }
  }
  PathAlias::create([
    'path' => '/node/' . $nid,
    'alias' => $alias,
    'langcode' => $langcode,
  ])->save();
}

function _motaded_sfda_pref(Paragraph $paragraph): array {
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

function _motaded_sfda_html(string $value, string $format = 'basic_html'): array {
  return [
    'value' => $value,
    'format' => $format,
  ];
}

function _motaded_sfda_create_card(string $title, string $body, string $uri = '', string $link_title = '', string $subtitle = '', int $media_id = 0): array {
  $values = [
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $title,
    'field_body' => _motaded_sfda_html($body),
  ];
  if ($subtitle !== '') {
    $values['field_sub_title'] = $subtitle;
  }
  if ($uri !== '') {
    $values['field_link'] = [
      'uri' => $uri,
      'title' => $link_title !== '' ? $link_title : 'Open page',
    ];
  }
  if ($media_id > 0) {
    $values['field_media'] = ['target_id' => $media_id];
  }
  $card = Paragraph::create($values);
  $card->save();
  return _motaded_sfda_pref($card);
}

function _motaded_sfda_cards(string $title, string $lede, array $items, string $type, string $subtitle = ''): Paragraph {
  $children = [];
  foreach ($items as $item) {
    $card_subtitle = (string) ($item['subtitle'] ?? '');
    if (($item['badge'] ?? '') !== '') {
      $card_subtitle = ($card_subtitle !== '' ? $card_subtitle . '|' : '') . $item['badge'];
    }
    $children[] = _motaded_sfda_create_card(
      $item['title'],
      $item['body'] ?? '',
      $item['uri'] ?? '',
      $item['link_title'] ?? '',
      $card_subtitle,
      (int) ($item['media'] ?? 0)
    );
  }
  $values = [
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => $type,
    'field_title' => $title,
    'field_paragraphs' => $children,
  ];
  if ($lede !== '') {
    $values['field_body'] = _motaded_sfda_html($lede);
  }
  if ($subtitle !== '') {
    $values['field_sub_title'] = $subtitle;
  }
  $cards = Paragraph::create($values);
  $cards->save();
  return $cards;
}

function _motaded_sfda_hero(array $hero): Paragraph {
  $paragraph = Paragraph::create([
    'type' => 'hero_split_banner',
    'langcode' => 'en',
    'field_hero_split_eyebrow' => $hero['eyebrow'],
    'field_hero_split_headline_prefix' => $hero['prefix'],
    'field_hero_split_headline_accent' => $hero['accent'],
    'field_body' => _motaded_sfda_html($hero['body']),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => $hero['primary'],
    ],
  ]);
  if ($paragraph->hasField('field_link_secondary')) {
    $paragraph->set('field_link_secondary', [
      'uri' => 'internal:#catalogue',
      'title' => $hero['secondary'],
    ]);
  }
  $hero_media = Media::load(1494);
  if ($hero_media && $hero_media->bundle() === 'image') {
    $paragraph->set('field_media', ['target_id' => 1494]);
  }
  $paragraph->set('field_hero_split_highlights', []);
  $paragraph->set('field_hero_split_features', []);
  $paragraph->save();
  return $paragraph;
}

function _motaded_sfda_form(string $body): Paragraph {
  $form = Paragraph::create([
    'type' => 'webform',
    'langcode' => 'en',
    'field_body' => [
      'value' => $body,
      'format' => 'full_html',
    ],
    'field_webform' => [
      'target_id' => 'sfda_assessment',
      'status' => 'open',
    ],
  ]);
  $form->save();
  return $form;
}

function _motaded_sfda_faq(array $items): Paragraph {
  $children = [];
  foreach ($items as $item) {
    $row = Paragraph::create([
      'type' => 'accordion',
      'langcode' => 'en',
      'field_title' => $item['question'],
      'field_body' => [
        'value' => $item['answer'],
        'format' => 'basic_html',
      ],
    ]);
    $row->save();
    $children[] = _motaded_sfda_pref($row);
  }
  $faq = Paragraph::create([
    'type' => 'accordions',
    'langcode' => 'en',
    'field_title' => 'Frequently Asked Questions',
    'field_paragraphs' => $children,
  ]);
  $faq->save();
  return $faq;
}
