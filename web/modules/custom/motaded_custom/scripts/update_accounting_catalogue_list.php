<?php

/**
 * @file
 * Switch accounting catalogue view from slider to search + pager list.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_catalogue_list.php
 */

declare(strict_types=1);

use Drupal\views\Entity\View;

$view = View::load('services');
if (!$view) {
  throw new \RuntimeException('views.view.services is missing.');
}

$displays = $view->get('display');
if (!isset($displays['block_accounting']['display_options'])) {
  throw new \RuntimeException('block_accounting display is missing.');
}

$opts = $displays['block_accounting']['display_options'];
$page_fields = $displays['page_1']['display_options']['fields'] ?? [];

$fields = $opts['fields'] ?? [];
$ordered = [];
if (isset($page_fields['field_media'])) {
  $ordered['field_media'] = $page_fields['field_media'];
}
foreach (['title', 'body', 'view_node'] as $name) {
  if (isset($fields[$name])) {
    $ordered[$name] = $fields[$name];
  }
  elseif (isset($page_fields[$name])) {
    $ordered[$name] = $page_fields[$name];
  }
}
if (isset($ordered['view_node'])) {
  $ordered['view_node']['exclude'] = TRUE;
  $ordered['view_node']['output_url_as_text'] = TRUE;
}
if (isset($ordered['body']['alter'])) {
  $ordered['body']['alter']['max_length'] = 160;
  $ordered['body']['alter']['strip_tags'] = TRUE;
  $ordered['body']['alter']['trim'] = TRUE;
}
if (isset($ordered['body']['settings'])) {
  $ordered['body']['settings']['trim_length'] = 160;
}
$opts['fields'] = $ordered;

$opts['pager'] = [
  'type' => 'full',
  'options' => [
    'offset' => 0,
    'pagination_heading_level' => 'h6',
    'items_per_page' => 3,
    'total_pages' => NULL,
    'id' => 0,
    'tags' => [
      'next' => '›',
      'previous' => '‹',
      'first' => '«',
      'last' => '»',
    ],
    'expose' => [
      'items_per_page' => FALSE,
      'items_per_page_label' => 'Items per page',
      'items_per_page_options' => '5, 10, 25, 50',
      'items_per_page_options_all' => FALSE,
      'items_per_page_options_all_label' => '- All -',
      'offset' => FALSE,
      'offset_label' => 'Offset',
    ],
    'quantity' => 5,
  ],
];

$opts['exposed_form'] = [
  'type' => 'basic',
  'options' => [
    'submit_button' => 'Search',
    'reset_button' => TRUE,
    'reset_button_label' => 'Reset',
    'exposed_sorts_label' => 'Sort by',
    'expose_sort_order' => TRUE,
    'sort_asc_label' => 'Asc',
    'sort_desc_label' => 'Desc',
  ],
];

$opts['filters']['combine'] = [
  'id' => 'combine',
  'table' => 'views',
  'field' => 'combine',
  'relationship' => 'none',
  'group_type' => 'group',
  'admin_label' => '',
  'plugin_id' => 'combine',
  'operator' => 'allwords',
  'value' => '',
  'group' => 1,
  'exposed' => TRUE,
  'expose' => [
    'operator_id' => 'combine_op',
    'label' => 'Search',
    'description' => '',
    'use_operator' => FALSE,
    'operator' => 'combine_op',
    'operator_limit_selection' => FALSE,
    'operator_list' => [],
    'identifier' => 'search',
    'required' => FALSE,
    'remember' => FALSE,
    'multiple' => FALSE,
    'remember_roles' => ['authenticated' => 'authenticated'],
    'placeholder' => 'Search services…',
  ],
  'is_grouped' => FALSE,
  'group_info' => [
    'label' => '',
    'description' => '',
    'identifier' => '',
    'optional' => TRUE,
    'widget' => 'select',
    'multiple' => FALSE,
    'remember' => FALSE,
    'default_group' => 'All',
    'default_group_multiple' => [],
    'group_items' => [],
  ],
  'fields' => [
    'title' => 'title',
    'body' => 'body',
  ],
];

$opts['empty'] = [
  'area' => [
    'id' => 'area',
    'table' => 'views',
    'field' => 'area',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'plugin_id' => 'text',
    'empty' => TRUE,
    'content' => [
      'value' => 'No results found',
      'format' => 'plain_text',
    ],
    'tokenize' => FALSE,
  ],
];

$opts['defaults']['empty'] = FALSE;
$opts['defaults']['use_ajax'] = FALSE;
$opts['use_ajax'] = TRUE;
$opts['display_description'] = 'Paginated list of pages tagged Accounting services.';
$opts['block_description'] = 'Accounting services list';

$displays['block_accounting']['display_options'] = $opts;
$displays['block_accounting']['cache_metadata']['contexts'] = array_values(array_unique(array_merge(
  $displays['block_accounting']['cache_metadata']['contexts'] ?? [],
  [
    'url',
    'url.query_args',
    'url.query_args:pagers:0',
  ]
)));
$displays['block_accounting']['cache_metadata']['tags'] = array_values(array_unique(array_merge(
  $displays['block_accounting']['cache_metadata']['tags'] ?? [],
  ['config:field.storage.node.field_media']
)));

$view->set('display', $displays);
$view->save();

echo "Updated services:block_accounting to search + pager list.\n";
