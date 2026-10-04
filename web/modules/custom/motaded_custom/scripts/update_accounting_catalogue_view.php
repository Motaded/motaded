<?php

/**
 * @file
 * Replaces the hand-built accounting catalogue with a services view slider.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_catalogue_view.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\views\Entity\View;

_motaded_acc_view_ensure_display();

$aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
  'alias' => '/services/accounting-services-saudi-arabia',
  'langcode' => 'en',
]);
$alias = $aliases ? reset($aliases) : NULL;
if (!$alias instanceof PathAlias) {
  throw new \RuntimeException('Accounting landing alias not found.');
}
$nid = (int) str_replace('/node/', '', $alias->getPath());
$node = Node::load($nid);
if (!$node instanceof Node) {
  throw new \RuntimeException('Accounting landing node not found.');
}
$source = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

$block = Paragraph::create([
  'type' => 'view_block',
  'langcode' => 'en',
  'field_body' => [
    'value' => '<h2>Explore our accounting, tax and audit services</h2><p>Find support for a specific requirement or discuss an ongoing arrangement covering several services.</p>',
    'format' => 'full_html',
  ],
  'field_view_block' => [
    'target_id' => 'services',
    'display_id' => 'block_accounting',
    'data' => serialize([
      'argument' => NULL,
      'header' => 0,
      'limit' => '',
      'offset' => '',
      'title' => 0,
      'pager' => NULL,
    ]),
  ],
]);
$block->save();

$refs = [];
$removed = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'catalogue') {
    $removed[] = $paragraph;
    $refs[] = [
      'target_id' => $block->id(),
      'target_revision_id' => $block->getRevisionId(),
    ];
    continue;
  }
  $refs[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

$source->set('field_paragraphs', $refs);
$source->save();
foreach ($removed as $paragraph) {
  foreach ($paragraph->get('field_paragraphs')->referencedEntities() as $child) {
    $child->delete();
  }
  $paragraph->delete();
}
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Replaced catalogue with services view slider on /node/{$nid}.\n";

function _motaded_acc_view_ensure_display(): void {
  $view = View::load('services');
  if (!$view) {
    throw new \RuntimeException('views.view.services is missing.');
  }
  $displays = $view->get('display');
  if (isset($displays['block_accounting'])) {
    echo "views.view.services:block_accounting already exists.\n";
    return;
  }
  $source = $displays['block_2']['display_options'] ?? $displays['default']['display_options'];
  $fields = [];
  foreach (['title', 'body', 'view_node'] as $name) {
    if (isset($source['fields'][$name])) {
      $fields[$name] = $source['fields'][$name];
    }
  }
  if ($fields === []) {
    throw new \RuntimeException('Could not copy service fields for block_accounting.');
  }
  if (isset($fields['body']['alter'])) {
    $fields['body']['alter']['max_length'] = 160;
    $fields['body']['alter']['strip_tags'] = TRUE;
    $fields['body']['alter']['trim'] = TRUE;
    $fields['body']['alter']['word_boundary'] = TRUE;
    $fields['body']['alter']['ellipsis'] = TRUE;
  }
  if (isset($fields['body']['settings']['trim_length'])) {
    $fields['body']['settings']['trim_length'] = 160;
  }
  if (isset($fields['view_node'])) {
    $fields['view_node']['exclude'] = TRUE;
  }
  $filters = $source['filters'] ?? [];
  unset($filters['field_taxonomy_target_id']);
  $filters['field_taxonomy_target_id'] = [
    'id' => 'field_taxonomy_target_id',
    'table' => 'node__field_taxonomy',
    'field' => 'field_taxonomy_target_id',
    'relationship' => 'none',
    'group_type' => 'group',
    'admin_label' => '',
    'plugin_id' => 'taxonomy_index_tid',
    'operator' => 'or',
    'value' => [152 => '152'],
    'group' => 1,
    'exposed' => FALSE,
    'expose' => [
      'operator_id' => '',
      'label' => '',
      'description' => '',
      'use_operator' => FALSE,
      'operator' => '',
      'operator_limit_selection' => FALSE,
      'operator_list' => [],
      'identifier' => '',
      'required' => FALSE,
      'remember' => FALSE,
      'multiple' => FALSE,
      'remember_roles' => ['authenticated' => 'authenticated'],
      'reduce' => FALSE,
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
    'reduce_duplicates' => TRUE,
    'vid' => 'taxonomy',
    'type' => 'select',
    'hierarchy' => FALSE,
    'limit' => TRUE,
    'error_message' => TRUE,
  ];
  $filters['combine'] = [
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
  $displays['block_accounting'] = [
    'id' => 'block_accounting',
    'display_title' => 'Accounting services',
    'display_plugin' => 'block',
    'position' => 5,
    'display_options' => [
      'title' => '',
      'fields' => $fields,
      'pager' => [
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
      ],
      'exposed_form' => [
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
      ],
      'sorts' => [
        'title' => [
          'id' => 'title',
          'table' => 'node_field_data',
          'field' => 'title',
          'relationship' => 'none',
          'group_type' => 'group',
          'admin_label' => '',
          'entity_type' => 'node',
          'entity_field' => 'title',
          'plugin_id' => 'standard',
          'order' => 'ASC',
          'expose' => [
            'label' => 'Title',
            'field_identifier' => 'title',
          ],
          'exposed' => FALSE,
        ],
      ],
      'filters' => $filters,
      'filter_groups' => [
        'operator' => 'AND',
        'groups' => [1 => 'AND'],
      ],
      'style' => [
        'type' => 'default',
        'options' => [
          'grouping' => [],
          'row_class' => '',
          'default_row_class' => FALSE,
        ],
      ],
      'row' => [
        'type' => 'fields',
        'options' => [
          'default_field_elements' => FALSE,
          'inline' => [],
          'separator' => '',
          'hide_empty' => FALSE,
        ],
      ],
      'empty' => [
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
      ],
      'defaults' => [
        'title' => FALSE,
        'css_class' => FALSE,
        'use_ajax' => FALSE,
        'empty' => FALSE,
        'pager' => FALSE,
        'exposed_form' => FALSE,
        'style' => FALSE,
        'row' => FALSE,
        'fields' => FALSE,
        'sorts' => FALSE,
        'filters' => FALSE,
        'filter_groups' => FALSE,
        'header' => FALSE,
      ],
      'css_class' => '',
      'use_ajax' => TRUE,
      'display_description' => 'Paginated list of pages tagged Accounting services.',
      'header' => [],
      'rendering_language' => '***LANGUAGE_language_interface***',
      'display_extenders' => [],
      'block_description' => 'Accounting services list',
    ],
    'cache_metadata' => [
      'max-age' => -1,
      'contexts' => [
        'languages:language_interface',
        'user.node_grants:view',
        'user.permissions',
      ],
      'tags' => [
        'config:field.storage.node.body',
      ],
    ],
  ];
  $view->set('display', $displays);
  $view->save();
  echo "Added views.view.services display block_accounting.\n";
}
