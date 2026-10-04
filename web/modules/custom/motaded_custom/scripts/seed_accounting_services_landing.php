<?php

/**
 * @file
 * Seeds the English accounting services landing page.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/seed_accounting_services_landing.php
 */

declare(strict_types=1);

use Drupal\block\Entity\Block;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\webform\Entity\Webform;

const MOTADED_ACCOUNTING_LANDING_ALIAS = '/services/accounting-services-saudi-arabia';

_motaded_accounting_ensure_card_types();
_motaded_accounting_enable_webform_paragraph();
_motaded_accounting_sync_webform();
_motaded_accounting_hide_setup_prefooter();

$node = _motaded_accounting_load_or_create_node();
$paragraphs = [
  _motaded_accounting_hero(),
  _motaded_accounting_context(),
  _motaded_accounting_needs(),
  _motaded_accounting_situations(),
  _motaded_accounting_catalogue(),
  _motaded_accounting_formats(),
  _motaded_accounting_working(),
  _motaded_accounting_process(),
  _motaded_accounting_documents(),
  _motaded_accounting_fees(),
  _motaded_accounting_assessment_form(),
  _motaded_accounting_faq(),
];

$refs = [];
foreach ($paragraphs as $paragraph) {
  $refs[] = _motaded_accounting_pref($paragraph);
}
$node->setTitle('Accounting Services in Saudi Arabia');
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
  'title' => 'Accounting Services in Saudi Arabia | Motaded',
  'description' => 'Motaded provides bookkeeping, financial reporting, tax filing and audit support for companies in Saudi Arabia. Scope and fee are agreed before work begins.',
], JSON_UNESCAPED_SLASHES));
$node->save();

$nid = (int) $node->id();
_motaded_accounting_set_alias($nid, MOTADED_ACCOUNTING_LANDING_ALIAS, 'en');
echo "Accounting landing EN: /node/{$nid} → " . MOTADED_ACCOUNTING_LANDING_ALIAS . "\n";
require __DIR__ . '/seed_accounting_services_landing_ar.php';

function _motaded_accounting_ensure_card_types(): void {
  $storage = FieldStorageConfig::loadByName('paragraph', 'field_card_type');
  if (!$storage) {
    return;
  }
  $values = $storage->getSetting('allowed_values') ?: [];
  $needed = [
    'needs' => 'Service: need cards',
    'situations' => 'Service: situation rows',
    'catalogue' => 'Service: grouped catalogue',
    'data_note' => 'Service: data note',
    'stages' => 'Service: numbered stages',
    'context' => 'Service: sector briefing',
    'formats' => 'Service: engagement formats',
  ];
  $changed = FALSE;
  foreach ($needed as $key => $label) {
    if (!isset($values[$key])) {
      $values[$key] = $label;
      $changed = TRUE;
    }
  }
  if ($changed) {
    $storage->setSetting('allowed_values', $values);
    $storage->save();
    echo "Updated field_card_type allowed values.\n";
  }
}

function _motaded_accounting_enable_webform_paragraph(): void {
  $field = FieldConfig::loadByName('node', 'landing_page', 'field_paragraphs');
  if (!$field) {
    return;
  }
  $settings = $field->getSetting('handler_settings') ?: [];
  $bundles = $settings['target_bundles'] ?? [];
  if (!isset($bundles['webform'])) {
    $settings['target_bundles']['webform'] = 'webform';
    $settings['target_bundles_drag_drop']['webform'] = [
      'weight' => 57,
      'enabled' => TRUE,
    ];
    $field->setSetting('handler_settings', $settings);
    $field->save();
  }
}

function _motaded_accounting_sync_webform(): void {
  $data = \Drupal::service('config.storage.sync')->read('webform.webform.accounting_assessment');
  if (!is_array($data)) {
    throw new \RuntimeException('Could not read accounting_assessment from config sync.');
  }
  \Drupal::configFactory()->getEditable('webform.webform.accounting_assessment')->setData($data)->save(TRUE);
  if (Webform::load('accounting_assessment')) {
    echo "Updated webform accounting_assessment.\n";
  }
}

function _motaded_accounting_hide_setup_prefooter(): void {
  $block = Block::load('motaded_theme_ctaprefooter');
  if (!$block) {
    return;
  }
  $needed = [
    '/services/customs-clearance-saudi-arabia',
    '/ar/services/customs-clearance-saudi-arabia',
    MOTADED_ACCOUNTING_LANDING_ALIAS,
    '/ar' . MOTADED_ACCOUNTING_LANDING_ALIAS,
  ];
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

function _motaded_accounting_load_or_create_node(): Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_ACCOUNTING_LANDING_ALIAS,
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if ($alias instanceof PathAlias) {
    $nid = (int) str_replace('/node/', '', $alias->getPath());
    $node = Node::load($nid);
    if ($node instanceof Node && $node->bundle() === 'landing_page') {
      return $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
    }
  }
  $node = Node::create([
    'type' => 'landing_page',
    'langcode' => 'en',
    'status' => 1,
    'title' => 'Accounting Services in Saudi Arabia',
  ]);
  $node->save();
  return $node;
}

function _motaded_accounting_set_alias(int $nid, string $alias, string $langcode): void {
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

function _motaded_accounting_pref(Paragraph $paragraph): array {
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

function _motaded_accounting_html(string $value, string $format = 'basic_html'): array {
  return [
    'value' => $value,
    'format' => $format,
  ];
}

function _motaded_accounting_create_card(string $title, string $body, string $uri = '', string $link_title = '', string $subtitle = ''): array {
  $values = [
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $title,
    'field_body' => _motaded_accounting_html($body),
  ];
  if ($subtitle !== '') {
    $values['field_sub_title'] = $subtitle;
  }
  if ($uri !== '') {
    $values['field_link'] = [
      'uri' => $uri,
      'title' => $link_title !== '' ? $link_title : 'Explore service',
    ];
  }
  $card = Paragraph::create($values);
  $card->save();
  return _motaded_accounting_pref($card);
}

function _motaded_accounting_create_cards(string $title, string $lede, array $items, string $type = 'simple_text_card_unequal_height', string $subtitle = ''): Paragraph {
  $children = [];
  foreach ($items as $item) {
    $children[] = _motaded_accounting_create_card(
      $item['title'],
      $item['body'],
      $item['uri'] ?? '',
      $item['link_title'] ?? '',
      $item['subtitle'] ?? ''
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
    $values['field_body'] = _motaded_accounting_html($lede);
  }
  if ($subtitle !== '') {
    $values['field_sub_title'] = $subtitle;
  }
  $cards = Paragraph::create($values);
  $cards->save();
  return $cards;
}

function _motaded_accounting_service_uri(string $needle, int $fallback_nid): string {
  $query = \Drupal::entityQuery('node')
    ->accessCheck(FALSE)
    ->condition('type', 'page')
    ->condition('status', 1)
    ->condition('title', $needle, 'CONTAINS')
    ->range(0, 1);
  $nids = $query->execute();
  $nid = $nids ? (int) reset($nids) : $fallback_nid;
  return 'entity:node/' . $nid;
}

function _motaded_accounting_hero(): Paragraph {
  $hero = Paragraph::create([
    'type' => 'hero_split_banner',
    'langcode' => 'en',
    'field_hero_split_eyebrow' => 'Accounting · Tax · Audit Support',
    'field_hero_split_headline_prefix' => 'Accounting Services',
    'field_hero_split_headline_accent' => 'in Saudi Arabia',
    'field_body' => _motaded_accounting_html(
      '<p>Bookkeeping, financial reporting, tax compliance and audit support for companies operating in Saudi Arabia. <a href="/about-us">Motaded</a> assists with ongoing accounting requirements and individual assignments, with the scope of work and fees agreed before the engagement begins.</p>'
    ),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Consultation',
    ],
  ]);
  if ($hero->hasField('field_link_secondary')) {
    $hero->set('field_link_secondary', [
      'uri' => 'internal:#catalogue',
      'title' => 'Explore Accounting Services',
    ]);
  }
  $hero_media = Media::load(1494);
  if ($hero_media && $hero_media->bundle() === 'image') {
    $hero->set('field_media', ['target_id' => 1494]);
  }
  $hero->set('field_hero_split_highlights', []);
  $hero->set('field_hero_split_features', []);
  $hero->save();
  return $hero;
}

function _motaded_accounting_context(): Paragraph {
  return _motaded_accounting_create_cards(
    'Accounting and Tax Compliance in Saudi Arabia',
    '<p>Accounting and tax compliance involve distinct responsibilities: maintaining reliable financial records, preparing financial statements and meeting applicable registration, filing and invoicing requirements. The requirements relevant to a business depend on its legal structure, ownership, activities and transactions.</p>',
    [
      [
        'title' => 'Financial Records and Reporting',
        'body' => '<p>Organised accounting records provide the basis for financial reporting, reconciliation and tax preparation. Financial statements are prepared under the applicable reporting framework, including IFRS Accounting Standards endorsed in Saudi Arabia or the endorsed IFRS for SMEs Accounting Standard, as appropriate to the entity.</p>',
      ],
      [
        'title' => 'Zakat and Tax Obligations',
        'body' => '<p><a href="/platforms/zatca">ZATCA</a> administers <a href="/services/zakat">Zakat</a>, <a href="/services/corporate-tax">corporate income tax</a>, <a href="/services/vat-advisory-services-saudi-arabia">VAT</a> and <a href="/services/withholding-tax-wht-services">withholding tax</a>. These obligations do not apply uniformly to every company. Each business should identify the requirements relevant to its circumstances and maintain the records needed for the applicable returns and calculations.</p>',
      ],
      [
        'title' => 'VAT and Electronic Invoicing',
        'body' => '<p>Businesses within the scope of <a href="/documents/vat-law-english">VAT</a> requirements must address applicable <a href="/services/vat-registration-services-saudi-arabia">registration</a>, invoicing and <a href="/services/vat-filing-services-saudi-arabia">return</a> obligations. Electronic invoicing is governed by <a href="/platforms/zatca">ZATCA</a>’s <a href="/blog/zatca-e-invoicing-phase-2-saudi-arabia-motaded">FATOORA</a> requirements, with generation and integration requirements applied according to the relevant rules and implementation phases.</p>',
      ],
      [
        'title' => 'Reporting and Filing Coordination',
        'body' => '<p>Financial reporting and tax filing are related but separate processes. A clear schedule for collecting documents, reconciling balances, reviewing reports and preparing returns helps ensure that the information required for each obligation is available.</p>',
      ],
    ],
    'context'
  );
}

function _motaded_accounting_needs(): Paragraph {
  return _motaded_accounting_create_cards(
    'Accounting Support at Every Stage of Your Business',
    '<p>Accounting requirements evolve as a company begins operations, establishes regular processes and expands its activities. <a href="/about-us">Motaded</a> provides support appropriate to the company’s stage, available records and reporting needs.</p>',
    [
      [
        'title' => 'Newly Established Companies',
        'body' => '<p>Establish an organised accounting foundation from the start of operations. Support includes setting up records and reporting processes so that transactions are documented consistently and financial information is available for subsequent accounting and <a href="/platforms/zatca">tax</a> work.</p>',
      ],
      [
        'title' => 'Operating Companies',
        'body' => '<p>Maintain current accounting records and a consistent reporting cycle. Regular support helps management understand the company’s financial position, monitor outstanding balances and prepare the information required for applicable <a href="/services/vat-filing-services-saudi-arabia">filings</a>.</p>',
      ],
      [
        'title' => 'Growing Companies',
        'body' => '<p>Adapt accounting processes to increased transaction volumes and more complex operations. Support focuses on reliable reporting, reconciliation and financial information for management review and <a href="/services/external-audit">audit preparation</a>.</p>',
      ],
    ],
    'needs'
  );
}

function _motaded_accounting_catalogue(): Paragraph {
  _motaded_accounting_ensure_services_view_display();
  $block = Paragraph::create([
    'type' => 'view_block',
    'langcode' => 'en',
    'field_body' => _motaded_accounting_html(
      '<h2>Accounting, tax and audit services</h2><p>Support may be requested for a specific requirement or for an ongoing arrangement covering several services.</p>',
      'full_html'
    ),
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
  return $block;
}

function _motaded_accounting_ensure_services_view_display(): void {
  $view = \Drupal\views\Entity\View::load('services');
  if (!$view) {
    throw new \RuntimeException('views.view.services is missing.');
  }
  $displays = $view->get('display');
  if (isset($displays['block_accounting'])) {
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
  $dependencies = $view->get('dependencies') ?: [];
  $dependencies['config'] = array_values(array_unique(array_merge($dependencies['config'] ?? [], [
    'taxonomy.vocabulary.taxonomy',
  ])));
  $view->set('dependencies', $dependencies);
  $view->set('display', $displays);
  $view->save();
  echo "Added views.view.services display block_accounting.\n";
}

function _motaded_accounting_situations(): Paragraph {
  return _motaded_accounting_create_cards(
    'Accounting Challenges We Address',
    '<p>Support is available for companies requiring corrections to existing records, continuity during an accounting handover or preparation for reporting and filing obligations.</p>',
    _motaded_accounting_situation_items(),
    'situations'
  );
}

function _motaded_accounting_situation_items(): array {
  return [
    [
      'title' => 'Accounting Records Are Behind',
      'body' => '<p>Review outstanding periods, identify missing documents and <a href="/services/accounting-catch-services-saudi-arabia">bring the agreed accounting records up to date</a>.</p>',
    ],
    [
      'title' => 'A Change of Accountant or Service Provider',
      'body' => '<p>Organise the transfer of available records, opening balances and outstanding matters to support continuity of accounting work.</p>',
    ],
    [
      'title' => 'Financial Reports Are Incomplete or Unclear',
      'body' => '<p>Review the underlying records and reconciliations to prepare <a href="/services/accounts-review-services-saudi-arabia">financial reports</a> that provide a clearer view of business performance and financial position.</p>',
    ],
    [
      'title' => 'A Tax Filing Deadline Is Approaching',
      'body' => '<p>Assess the available information, identify outstanding requirements and prepare the agreed <a href="/services/vat-filing-services-saudi-arabia">filing</a>. Submission is included where authorised and specified in the engagement.</p>',
    ],
    [
      'title' => 'An External Audit Is Being Prepared',
      'body' => '<p>Organise accounting schedules and supporting documents, identify gaps in the records and coordinate responses to the appointed <a href="/services/external-audit">auditor</a>’s information requests.</p>',
    ],
    [
      'title' => 'Request a Consultation',
      'body' => '',
      'uri' => 'internal:#assessment',
      'link_title' => 'Request a Consultation',
    ],
  ];
}

function _motaded_accounting_formats(): Paragraph {
  return _motaded_accounting_create_cards(
    'Ongoing Accounting and One-Off Assignments',
    '<p>Accounting support can be arranged as a recurring engagement or a defined assignment. The appropriate format depends on whether the company requires regular accounting operations or completion of a specific task.</p>',
    [
      [
        'title' => 'Ongoing Accounting Support',
        'body' => '<p>For companies requiring consistent maintenance of their accounting records and periodic reporting.</p><p>The engagement may include:</p><ul><li>Recording and classification of business transactions.</li><li>Bank and account reconciliations.</li><li>Periodic financial and management reports.</li><li><a href="/services/payroll-management-services-saudi-arabia">Payroll</a> calculations and related accounting entries.</li><li>Preparation of applicable <a href="/services/vat-filing-services-saudi-arabia">tax returns</a>, where included.</li></ul><p>The agreed schedule defines reporting periods, document submission deadlines and responsibilities. Each cycle concludes with the agreed reports and a record of outstanding matters.</p>',
      ],
      [
        'title' => 'One-Off Assignments',
        'body' => '<p>For companies requiring work on a defined accounting period, application or reporting requirement.</p><p>Assignments may include:</p><ul><li>Accounting setup for a new business.</li><li><a href="/services/accounting-catch-services-saudi-arabia">Catch-up accounting</a> for outstanding periods.</li><li><a href="/services/accounts-review-services-saudi-arabia">Review and reconciliation</a> of existing accounts.</li><li>Preparation of a specific <a href="/services/vat-filing-services-saudi-arabia">tax return</a>.</li><li>Accounting handover from a previous provider.</li><li>Preparation of schedules and documents for an <a href="/services/external-audit">external audit</a>.</li></ul><p>Each assignment has a defined scope, required inputs, deliverables and completion schedule. The client receives the completed materials and a record of any unresolved items requiring further action.</p>',
      ],
    ],
    'formats'
  );
}

function _motaded_accounting_working(): Paragraph {
  return _motaded_accounting_create_cards(
    'Accounting Coordination and Progress Updates',
    '<p>A designated <a href="/about-us">Motaded</a> coordinator manages document requests, transaction queries and the reporting schedule. Outstanding information and client actions are tracked throughout the engagement. Draft reports are shared for review and clarification before final delivery, while tasks requiring client approval are identified separately.</p>',
    [
      [
        'title' => 'Request a Consultation',
        'body' => '',
        'uri' => 'internal:#assessment',
        'link_title' => 'Request a Consultation',
      ],
      [
        'title' => 'WhatsApp',
        'body' => '',
        'uri' => 'https://wa.me/966539797197',
        'link_title' => 'WhatsApp',
      ],
    ],
    'features'
  );
}

function _motaded_accounting_process(): Paragraph {
  return _motaded_accounting_create_cards(
    'Accounting Engagement Process',
    '<p>Each engagement begins with a review of the company’s requirements and available records. The process below applies to ongoing accounting support and individual assignments, with deliverables and responsibilities defined for the selected services.</p>',
    [
      [
        'title' => 'Assessment of Existing Records',
        'body' => '<p><a href="/about-us">Motaded</a> reviews the company’s activities, accounting period, available records and reporting requirements. The assessment identifies the condition of the records, outstanding information and priorities for the engagement.</p>',
      ],
      [
        'title' => 'Agreement on Scope and Deliverables',
        'body' => '<p>The written proposal defines the services, accounting periods, expected deliverables, responsibilities, schedule and fees. For ongoing support, it also establishes the reporting frequency and deadlines for providing documents.</p>',
      ],
      [
        'title' => 'Transfer of Documents and Data',
        'body' => '<p>The client provides the relevant accounting records and supporting documents through the agreed transfer method. Where access to existing accounting software is required, permissions are arranged separately. Missing information and opening balances requiring confirmation are recorded for follow-up.</p>',
      ],
      [
        'title' => 'Accounting Work and Review',
        'body' => '<p>The team completes the agreed accounting, reconciliation, reporting or <a href="/services/vat-filing-services-saudi-arabia">tax preparation</a> work. Transaction queries are referred to the client for clarification, and prepared records and reports undergo review before delivery. Filings requiring client approval are submitted where authorised and included in the engagement.</p>',
      ],
      [
        'title' => 'Delivery of Results and Outstanding Actions',
        'body' => '<p>The client receives the agreed records, reports or filing documentation, together with any matters requiring further action. For ongoing engagements, the next reporting period and outstanding tasks are confirmed. For individual assignments, the completed work and handover materials are documented.</p>',
      ],
    ],
    'stages'
  );
}

function _motaded_accounting_documents(): Paragraph {
  return _motaded_accounting_create_cards(
    'Documents we may need',
    '',
    [
      [
        'title' => '',
        'body' => '<ul><li>Company and <a href="/platforms/zatca">tax registration</a> details.</li><li>Existing accounting records and financial statements.</li><li>Invoices, bank statements and expense records.</li><li>Depending on the service: <a href="/services/payroll-management-services-saudi-arabia">payroll</a> records, <a href="/services/inventory-stock-audit">inventory</a> lists or previous <a href="/services/vat-filing-services-saudi-arabia">tax returns</a>.</li></ul>',
      ],
      [
        'title' => '',
        'body' => '<p>Incomplete records do not preclude a consultation. Outstanding items are confirmed in writing.</p>',
      ],
    ],
    'lists'
  );
}

function _motaded_accounting_fees(): Paragraph {
  return _motaded_accounting_create_cards(
    'Fees and quotation',
    '<p>A quotation may be requested for a one-off assignment or for ongoing bookkeeping support. The quotation states the work ordered and the fee, for agreement before work begins.</p>',
    [
      [
        'title' => 'The quotation takes into account',
        'body' => '<ul><li>The volume and period of work.</li><li>The condition of existing records.</li><li>The services required.</li></ul>',
      ],
      [
        'title' => 'The quotation includes',
        'body' => '<ul><li>The agreed work.</li><li>The expected results.</li><li>The timeline.</li><li>The fee.</li><li>Any separate costs.</li></ul><p>Taxes, <a href="/services/zakat">zakat</a> and any third-party payments are separate from <a href="/about-us">Motaded</a>’s fee.</p>',
        'uri' => 'internal:#assessment',
        'link_title' => 'Request a Quotation',
      ],
    ],
    'fees'
  );
}

function _motaded_accounting_assessment_form(): Paragraph {
  $form = Paragraph::create([
    'type' => 'webform',
    'langcode' => 'en',
    'field_body' => [
      'value' => '<h2>Request a Consultation</h2>',
      'format' => 'full_html',
    ],
    'field_webform' => [
      'target_id' => 'accounting_assessment',
      'status' => 'open',
    ],
  ]);
  $form->save();
  return $form;
}

function _motaded_accounting_faq(): Paragraph {
  $children = [];
  foreach (_motaded_accounting_faq_items() as $item) {
    $row = Paragraph::create([
      'type' => 'accordion',
      'langcode' => 'en',
      'field_title' => $item['question'],
      'field_body' => [
        'value' => $item['answer'],
        'format' => $item['answer_format'],
      ],
    ]);
    $row->save();
    $children[] = _motaded_accounting_pref($row);
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

function _motaded_accounting_faq_items(): array {
  return [
    [
      'question' => 'Can you help a newly established company?',
      'answer' => '<p>Yes. We can help organise your accounting setup, design a chart of accounts and establish processes for invoices, expenses and reporting. <a href="/services/vat-registration-services-saudi-arabia">Tax registration</a> guidance can be included where needed.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Can we engage you for ongoing bookkeeping?',
      'answer' => '<p>Yes. We can discuss ongoing bookkeeping, reconciliations, financial reporting and related <a href="/services/vat-filing-services-saudi-arabia">filing support</a>. The frequency and deliverables are agreed in the proposal.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Our records are incomplete or several months behind. Where do we start?',
      'answer' => '<p>Tell us which periods are affected and share the records you have. We assess the gaps and propose <a href="/services/accounting-catch-services-saudi-arabia">catch-up work</a>, including any dependencies affecting reporting or filing.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Can you take over from our current accountant?',
      'answer' => '<p>We can review your handover requirements and the available records. This helps establish opening balances, previous filings, outstanding work and responsibilities going forward.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Can we request only one service?',
      'answer' => '<p>Yes. You can request a specific service, such as an <a href="/services/accounts-review-services-saudi-arabia">accounts review</a>, <a href="/services/vat-filing-services-saudi-arabia">VAT return</a> preparation, <a href="/services/inventory-stock-audit">inventory audit</a> or <a href="/services/external-audit">audit preparation</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Can you help us understand which tax services apply?',
      'answer' => '<p>We can review your company structure, activities and transactions to identify the relevant work. Applicable requirements should be checked against current <a href="/platforms/zatca">ZATCA</a> rules and guidance.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'What is the difference between audit preparation and an external audit?',
      'answer' => '<p>Audit preparation involves organising financial statements, records and supporting schedules. An independent <a href="/services/external-audit">external audit</a> is a separate engagement, and the audit opinion is issued by the appointed licensed auditor. Your proposal will clarify <a href="/about-us">Motaded</a>’s role.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'How is the price determined?',
      'answer' => '<p>Pricing depends on the volume and complexity of the work, the state of your records and the required deliverables. We confirm the scope and fee in writing before work begins.</p>',
      'answer_format' => 'basic_html',
    ],
  ];
}
