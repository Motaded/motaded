<?php

/**
 * @file
 * Adds a mid-page CTA, node phone field, and quieter form contact row.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_customs_mid_cta_phone.php
 */

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

const MOTADED_CUSTOMS_PHONE = '+966 53 979 7197';
const MOTADED_CUSTOMS_MID_ALIAS = '/services/customs-clearance-saudi-arabia';

_motaded_customs_mid_ensure_phone_field();
$node = _motaded_customs_mid_load_node();
if (!$node instanceof Node) {
  throw new \RuntimeException('Customs landing node not found.');
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$en->set('field_phone_number', MOTADED_CUSTOMS_PHONE);
$en->save();

$invite_exists = FALSE;
$process_delta = NULL;
$refs = $en->get('field_paragraphs')->getValue();
foreach ($en->get('field_paragraphs') as $delta => $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'cards') {
    continue;
  }
  $source = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $paragraph;
  $title = trim((string) $source->get('field_title')->value);
  if (in_array($title, [
    'Talk to the clearance team',
    'Start with a written assessment',
    'Start with a consultation',
  ], TRUE)) {
    $invite_exists = TRUE;
  }
  if (in_array($title, [
    'Clearance Process',
    'إجراءات التخليص',
    'How your customs clearance works',
    'كيف يعمل التخليص الجمركي لديكم',
  ], TRUE)) {
    $process_delta = (int) $delta;
  }
  if ($title === 'Contact Our Customs Clearance Team') {
    _motaded_customs_mid_set_translation($paragraph, 'en', [
      'field_body' => [
        'value' => '<p>The same team answers WhatsApp, email and telephone.</p>',
        'format' => 'basic_html',
      ],
    ]);
    _motaded_customs_mid_set_translation($paragraph, 'ar', [
      'field_body' => [
        'value' => '<p>الفريق نفسه يرد عبر واتساب والبريد والهاتف.</p>',
        'format' => 'basic_html',
      ],
    ]);
  }
}

if (!$invite_exists) {
  $invite = _motaded_customs_mid_create_invite();
  $insert = [
    [
      'target_id' => $invite->id(),
      'target_revision_id' => $invite->getRevisionId(),
    ],
  ];
  $at = $process_delta === NULL ? count($refs) : $process_delta + 1;
  array_splice($refs, $at, 0, $insert);
  $en = Node::load((int) $en->id());
  $en = $en->hasTranslation('en') ? $en->getTranslation('en') : $en;
  $en->set('field_paragraphs', $refs);
  $en->set('field_phone_number', MOTADED_CUSTOMS_PHONE);
  $en->save();
  if ($en->hasTranslation('ar')) {
    $ar = Node::load((int) $en->id())->getTranslation('ar');
    $ar->set('field_paragraphs', $refs);
    $ar->save();
  }
  echo "Inserted mid-page CTA after Clearance Process.\n";
}
else {
  echo "Mid-page CTA already present.\n";
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $en->id()]);
echo 'Phone + contact row updated on /node/' . $en->id() . "\n";

function _motaded_customs_mid_load_node(): ?Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_MID_ALIAS,
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

function _motaded_customs_mid_ensure_phone_field(): void {
  if (!FieldConfig::loadByName('node', 'landing_page', 'field_phone_number')) {
    FieldConfig::create([
      'field_name' => 'field_phone_number',
      'entity_type' => 'node',
      'bundle' => 'landing_page',
      'label' => 'Phone number',
      'description' => 'Shown on the mid-page CTA and in the contact row under the assessment form.',
      'required' => FALSE,
      'translatable' => FALSE,
    ])->save();
    echo "Created landing_page.field_phone_number.\n";
  }

  $form = EntityFormDisplay::load('node.landing_page.default');
  if ($form) {
    $form->setComponent('field_phone_number', [
      'type' => 'string_textfield',
      'weight' => 129,
      'settings' => [
        'size' => 40,
        'placeholder' => MOTADED_CUSTOMS_PHONE,
      ],
    ]);
    $form->save();
  }

  $view = EntityViewDisplay::load('node.landing_page.default');
  if ($view) {
    $view->removeComponent('field_phone_number');
    $view->save();
  }
}

function _motaded_customs_mid_create_invite(): Paragraph {
  $child = Paragraph::create([
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => 'Request a Clearance Assessment',
    'field_body' => [
      'value' => '',
      'format' => 'basic_html',
    ],
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Clearance Assessment',
    ],
  ]);
  $child->save();

  $cards = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'simple_text_card',
    'field_title' => 'Talk to the clearance team',
    'field_body' => [
      'value' => '<p>Company and shipment details are enough to start.</p>',
      'format' => 'basic_html',
    ],
    'field_paragraphs' => [
      [
        'target_id' => $child->id(),
        'target_revision_id' => $child->getRevisionId(),
      ],
    ],
  ]);
  $cards->save();

  $ar_child = $child->hasTranslation('ar')
    ? $child->getTranslation('ar')
    : $child->addTranslation('ar', ['status' => $child->isPublished()]);
  $ar_child->set('field_title', 'طلب تقييم التخليص');
  $link = $child->get('field_link')->getValue();
  if ($link) {
    $link[0]['title'] = 'طلب تقييم التخليص';
    $ar_child->set('field_link', $link);
  }
  $ar_child->save();

  $ar = $cards->addTranslation('ar', ['status' => $cards->isPublished()]);
  $ar->set('field_title', 'تحدث مع فريق التخليص');
  $ar->set('field_body', [
    'value' => '<p>بيانات المنشأة والشحنة كافية للبدء.</p>',
    'format' => 'basic_html',
  ]);
  $ar->set('field_paragraphs', $cards->get('field_paragraphs')->getValue());
  $ar->save();

  return $cards;
}

/**
 * @param array<string, mixed> $values
 */
function _motaded_customs_mid_set_translation(ParagraphInterface $paragraph, string $langcode, array $values): void {
  $tr = $paragraph->hasTranslation($langcode)
    ? $paragraph->getTranslation($langcode)
    : $paragraph->addTranslation($langcode, ['status' => $paragraph->isPublished()]);
  foreach ($values as $field => $value) {
    if ($paragraph->hasField($field)) {
      $tr->set($field, $value);
    }
  }
  $tr->save();
}
