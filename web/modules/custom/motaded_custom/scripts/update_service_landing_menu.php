<?php

/**
 * @file
 * Adds service landing hubs to the main Services mega menu.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/update_service_landing_menu.php
 */

declare(strict_types=1);

use Drupal\menu_link_content\Entity\MenuLinkContent;

$services_parent = 'menu_link_content:e779afc2-7b4b-428d-b5c1-8bf93137fb5c';
$finance_parent = 'menu_link_content:421a9348-d1ff-4485-89e4-c9611f171ba5';

$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');

$catch = $storage->load(129);
if ($catch instanceof MenuLinkContent) {
  $catch->set('title', 'Accounting Catch-Up');
  $catch->save();
  if ($catch->hasTranslation('ar')) {
    $ar = $catch->getTranslation('ar');
    $ar->set('title', 'خدمات المحاسبة المتأخرة');
    $ar->save();
  }
  echo "Renamed menu link 129 to Accounting Catch-Up.\n";
}

$hub = _motaded_menu_find_or_create([
  'title' => 'Accounting Services',
  'link' => ['uri' => 'entity:node/996'],
  'menu_name' => 'main',
  'parent' => $finance_parent,
  'weight' => -53,
  'langcode' => 'en',
], 'خدمات المحاسبة');
echo 'Accounting hub menu link ' . $hub->id() . ".\n";

_motaded_menu_restore_business_support();

$group = _motaded_menu_find_or_create([
  'title' => 'Regulatory support',
  'link' => ['uri' => 'route:<nolink>'],
  'menu_name' => 'main',
  'parent' => $services_parent,
  'weight' => -43,
  'langcode' => 'en',
  'expanded' => TRUE,
], 'الدعم التنظيمي', TRUE);
if ($group->hasField('field_menu_icon')) {
  $group->set('field_menu_icon', 'building');
  $group->save();
}
echo 'Regulatory group menu link ' . $group->id() . ".\n";

$group_parent = 'menu_link_content:' . $group->uuid();
$children = [
  ['title' => 'Customs Clearance', 'nid' => 995, 'ar' => 'التخليص الجمركي', 'weight' => 0],
  ['title' => 'Medicines', 'nid' => 997, 'ar' => 'الأدوية', 'weight' => 1],
  ['title' => 'Medical Devices', 'nid' => 998, 'ar' => 'الأجهزة الطبية', 'weight' => 2],
  ['title' => 'Cosmetics', 'nid' => 999, 'ar' => 'مستحضرات التجميل', 'weight' => 3],
];
foreach ($children as $child) {
  $link = _motaded_menu_find_or_create([
    'title' => $child['title'],
    'link' => ['uri' => 'entity:node/' . $child['nid']],
    'menu_name' => 'main',
    'parent' => $group_parent,
    'weight' => $child['weight'],
    'langcode' => 'en',
  ], $child['ar']);
  if ($link->getParentId() !== $group_parent) {
    $link->set('parent', $group_parent);
    $link->save();
  }
  echo $child['title'] . ' menu link ' . $link->id() . ".\n";
}

\Drupal::service('plugin.manager.menu.link')->rebuild();
echo "Menu plugin cache rebuilt.\n";

/**
 * Restores Business Support if a previous run reused its <nolink> row.
 */
function _motaded_menu_restore_business_support(): void {
  $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
  $link = $storage->load(108);
  if (!$link instanceof MenuLinkContent) {
    return;
  }
  $link->set('title', 'Business Support');
  $link->set('weight', -52);
  $link->save();
  if ($link->hasTranslation('ar')) {
    $ar = $link->getTranslation('ar');
    $ar->set('title', 'دعم الأعمال');
    $ar->save();
  }
  echo "Restored menu link 108 to Business Support.\n";
}

/**
 * Loads a main-menu link by title + parent, or creates it with an AR title.
 */
function _motaded_menu_find_or_create(array $values, string $ar_title, bool $match_title = FALSE): MenuLinkContent {
  $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
  $uri = $values['link']['uri'];
  $properties = [
    'menu_name' => 'main',
  ];
  if ($match_title || $uri === 'route:<nolink>') {
    $properties['title'] = $values['title'];
    $properties['parent'] = $values['parent'];
  }
  else {
    $properties['link.uri'] = $uri;
  }
  $found = $storage->loadByProperties($properties);
  $link = $found ? reset($found) : NULL;
  if (!$link instanceof MenuLinkContent) {
    $link = MenuLinkContent::create($values);
    $link->save();
  }
  else {
    $link->set('title', $values['title']);
    $link->set('weight', $values['weight']);
    $link->set('parent', $values['parent']);
    $link->save();
  }
  if (!$link->hasTranslation('ar')) {
    $link->addTranslation('ar', ['title' => $ar_title])->save();
  }
  else {
    $ar = $link->getTranslation('ar');
    $ar->set('title', $ar_title);
    $ar->save();
  }
  return $link;
}
