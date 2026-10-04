<?php

/**
 * @file
 * Adds landing_page.field_service and enables it on the customs landing.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/update_landing_service_field.php
 */

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\path_alias\Entity\PathAlias;

if (!FieldStorageConfig::loadByName('node', 'field_service')) {
  FieldStorageConfig::create([
    'uuid' => 'caf9f19a-06d3-49ae-8e42-0339f3535f77',
    'field_name' => 'field_service',
    'entity_type' => 'node',
    'type' => 'boolean',
    'cardinality' => 1,
    'translatable' => TRUE,
  ])->save();
  echo "Created field storage node.field_service.\n";
}

if (!FieldConfig::loadByName('node', 'landing_page', 'field_service')) {
  FieldConfig::create([
    'uuid' => '71bba0bc-1242-4361-8af4-d3d7ff28d0ef',
    'field_name' => 'field_service',
    'entity_type' => 'node',
    'bundle' => 'landing_page',
    'label' => 'Service',
    'description' => 'Enables the service landing layout (section templates and CSS). Use for pages like customs clearance.',
    'required' => FALSE,
    'translatable' => FALSE,
    'default_value' => [['value' => 0]],
    'settings' => [
      'on_label' => 'On',
      'off_label' => 'Off',
    ],
  ])->save();
  echo "Created landing_page.field_service.\n";
}

$form = EntityFormDisplay::load('node.landing_page.default');
if ($form) {
  $form->setComponent('field_service', [
    'type' => 'boolean_checkbox',
    'weight' => 18,
    'settings' => [
      'display_label' => TRUE,
    ],
  ]);
  $form->save();
}

foreach (['default', 'search_index', 'search_result'] as $mode) {
  $view = EntityViewDisplay::load('node.landing_page.' . $mode);
  if ($view) {
    $view->removeComponent('field_service');
    $view->save();
  }
}

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/customs-clearance-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Customs landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node || $node->bundle() !== 'landing_page') {
  throw new \RuntimeException('Customs landing node not found.');
}
$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$en->set('field_service', 1);
$en->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Enabled field_service on nid={$nid}.\n";
