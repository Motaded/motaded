<?php

/**
 * @file
 * Adds the landing_services view block to the homepage after Integrated Services.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/attach_landing_services_homepage.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;

$nid = 374;
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Homepage node 374 not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface || $paragraph->bundle() !== 'view_block') {
    continue;
  }
  foreach ($paragraph->get('field_view_block')->getValue() as $ref) {
    if (($ref['target_id'] ?? '') === 'landing_services') {
      echo "Homepage already has landing_services.\n";
      return;
    }
  }
}

$paragraph = Paragraph::create([
  'type' => 'view_block',
  'langcode' => 'en',
  'field_body' => [
    'value' => '<h2>Customs, Accounting and SFDA Services in Saudi Arabia</h2><p>Support with customs clearance, bookkeeping, tax and audit, and SFDA requirements for medicines, medical devices and cosmetics. Explore the services to find the help your business needs.</p>',
    'format' => 'full_html',
  ],
  'field_view_block' => [
    [
      'target_id' => 'landing_services',
      'display_id' => 'block_1',
      'data' => serialize([
        'argument' => NULL,
        'header' => 0,
        'limit' => '',
        'offset' => '',
        'title' => 0,
        'pager' => NULL,
      ]),
    ],
  ],
]);
$paragraph->save();

if ($paragraph->hasTranslation('ar') === FALSE) {
  $ar = $paragraph->addTranslation('ar');
  $ar->set('field_body', [
    'value' => '<h2>خدمات الجمارك والمحاسبة وهيئة الغذاء والدواء في المملكة العربية السعودية</h2><p>دعم في التخليص الجمركي ومسك الدفاتر والضرائب والتدقيق ومتطلبات هيئة الغذاء والدواء للأدوية والأجهزة الطبية ومستحضرات التجميل. استكشفوا الخدمات لتحديد الدعم الذي تحتاجه أعمالكم.</p>',
    'format' => 'full_html',
  ]);
  $ar->set('field_view_block', $paragraph->get('field_view_block')->getValue());
  $ar->save();
}

$insert_after = NULL;
$ordered = [];
foreach ($source->get('field_paragraphs') as $item) {
  $existing = $item->entity;
  if (!$existing instanceof ParagraphInterface) {
    continue;
  }
  $ordered[] = $existing;
  if ($existing->bundle() === 'view_block') {
    foreach ($existing->get('field_view_block')->getValue() as $ref) {
      if (($ref['target_id'] ?? '') === 'services' && ($ref['display_id'] ?? '') === 'block_2') {
        $insert_after = count($ordered) - 1;
      }
    }
  }
}

$position = $insert_after === NULL ? count($ordered) : $insert_after + 1;
array_splice($ordered, $position, 0, [$paragraph]);

$refs = [];
foreach ($ordered as $item) {
  $refs[] = [
    'target_id' => $item->id(),
    'target_revision_id' => $item->getRevisionId(),
  ];
}
$source->set('field_paragraphs', $refs);
$source->save();

if ($node->hasTranslation('ar')) {
  $ar_node = $node->getTranslation('ar');
  $ar_node->set('field_paragraphs', $refs);
  $ar_node->save();
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Added landing_services to homepage after Integrated Services.\n";
