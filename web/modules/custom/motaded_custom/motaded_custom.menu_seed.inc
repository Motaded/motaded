<?php

/**
 * @file
 * One-time seed for Main navigation (header mega menu structure).
 */

declare(strict_types=1);

use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Seeds the main menu with top-level items and Services mega subtree.
 *
 * Idempotent via state key motaded_custom.main_menu_seed_v4.
 * v4 adds field_menu_icon values on Services category groups so the header
 * mega menu can render category icons.
 */
function motaded_custom_seed_main_navigation(): void {
  if (\Drupal::state()->get('motaded_custom.main_menu_seed_v4', FALSE)) {
    return;
  }

  $storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');

  $find_child = static function (string $title, string $parent_id) use ($storage): ?MenuLinkContent {
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('menu_name', 'main')
      ->condition('title', $title)
      ->condition('parent', $parent_id)
      ->range(0, 1)
      ->execute();
    if (!$ids) {
      return NULL;
    }
    $id = reset($ids);
    return $storage->load($id);
  };

  $create = static function (
    string $title,
    string $uri,
    string $parent_id,
    int $weight,
    bool $expanded = FALSE,
    ?string $icon_key = NULL,
  ) use ($storage, $find_child): MenuLinkContent {
    $existing = $find_child($title, $parent_id);
    if ($existing) {
      // Backfill the icon on an existing link if the value is currently empty.
      if ($icon_key !== NULL
        && $existing->hasField('field_menu_icon')
        && $existing->get('field_menu_icon')->isEmpty()) {
        $existing->set('field_menu_icon', $icon_key);
        $existing->save();
      }
      return $existing;
    }
    $values = [
      'title' => $title,
      'link' => ['uri' => $uri],
      'menu_name' => 'main',
      'parent' => $parent_id,
      'weight' => $weight,
      'expanded' => $expanded,
    ];
    if ($icon_key !== NULL) {
      $values['field_menu_icon'] = $icon_key;
    }
    $entity = MenuLinkContent::create($values);
    $entity->save();
    return $entity;
  };

  // Top level (order matches design). Home is usually the core front-page link — do not add a second one.
  $about_parent = $create('About', 'route:<nolink>', '', -49, TRUE);
  $create('Who we are', 'internal:/services', 'menu_link_content:' . $about_parent->uuid(), 0, FALSE);
  $create('Our mission', 'internal:/library', 'menu_link_content:' . $about_parent->uuid(), 1, FALSE);

  $create('Sectors', 'internal:/sectors', '', -48, FALSE);

  $services = $create('Services', 'internal:/services', '', -47, TRUE);
  $sid = 'menu_link_content:' . $services->uuid();

  // Each group: [icon_key, [children…]]. Icon key matches allowed_values of
  // menu_link_content.field_menu_icon.
  $groups = [
    'Business Setup' => ['briefcase', [
      'Company Formation',
      'Trade License',
      'MISA License',
      'Business Setup in Free Zones',
      'Corporate Bank Account',
    ]],
    'PRO & Government Services' => ['building', [
      'Government Approvals',
      'Document Clearing',
      'Visa Services',
      'Legal Translation',
      'Attestation Services',
    ]],
    'Business Support' => ['users', [
      'HR & Payroll Services',
      'Accounting & Tax Services',
      'Business Consulting',
      'Feasibility Studies',
      'Market Entry Strategy',
    ]],
    'Office Solutions' => ['lamp', [
      'Premium Residency',
      'Coworking Spaces',
      'Serviced Offices',
      'Virtual Offices',
    ]],
    'Growth & Investment' => ['chart', [
      'Business Incubators',
      'Investor Support',
      'Partnership Opportunities',
      'Expansion Services',
    ]],
    'Popular Links' => ['star', [
      'Investor Journey',
      'Business Setup Guide',
      'Cost Calculator',
      'Compare Business Structures',
    ]],
  ];

  $gw = 0;
  foreach ($groups as $group_title => [$icon_key, $links]) {
    $group = $create($group_title, 'route:<nolink>', $sid, $gw, TRUE, $icon_key);
    $gid = 'menu_link_content:' . $group->uuid();
    $lw = 0;
    foreach ($links as $label) {
      $create($label, 'internal:/services', $gid, $lw, FALSE);
      $lw++;
    }
    $gw++;
  }

  $create('Government Platforms', 'internal:/platforms', '', -46, FALSE);

  $res_parent = $create('Resources', 'route:<nolink>', '', -45, TRUE);
  $rid = 'menu_link_content:' . $res_parent->uuid();
  $create('Library', 'internal:/library', $rid, 0, FALSE);
  $create('Insights', 'internal:/insights', $rid, 1, FALSE);
  $create('News', 'internal:/news', $rid, 2, FALSE);
  $create('Events', 'internal:/events', $rid, 3, FALSE);

  $create('Contact', 'internal:/contact-us', '', -44, FALSE);
  $create('Setup Packages', 'internal:/packages', '', -43, FALSE);

  \Drupal::state()->set('motaded_custom.main_menu_seed_v4', TRUE);
}
