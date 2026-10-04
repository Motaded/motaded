<?php

/**
 * @file
 * Reorders and shortens the first three accounting landing blocks.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_accounting_first_blocks.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;

$needs = [
  'title' => 'Accounting support for every stage of your business',
  'lede' => '',
  'items' => [
    [
      'title' => 'Starting a business',
      'body' => '<p>You need accounting in place before the first invoices and filings.</p><p>Motaded sets up the records so you can run operations and prepare reports correctly from the start.</p>',
    ],
    [
      'title' => 'Running an established business',
      'body' => '<p>You want regular bookkeeping off your plate and a clear view of the numbers.</p><p>Motaded takes on the ongoing records and issues the financial reports agreed in the scope.</p>',
    ],
    [
      'title' => 'Managing growth',
      'body' => '<p>As activity grows, records become harder to control and harder to use.</p><p>Motaded organises the more complex accounting, strengthens oversight and prepares the data needed for management and audit.</p>',
    ],
  ],
];

$situations = [
  'title' => 'When you need accounting support',
  'lede' => '<p>You do not need to know the exact service name before contacting us.</p>',
  'items' => [
    [
      'title' => 'Your records are behind',
      'subtitle' => 'Catch-up',
      'body' => '<p>Transactions are missing, accounts have not been reconciled or previous periods remain unfinished.</p>',
    ],
    [
      'title' => 'Your data is fragmented',
      'subtitle' => 'Reporting',
      'body' => '<p>Figures sit in separate files, errors go unnoticed and you cannot produce a current report.</p>',
    ],
    [
      'title' => 'A filing deadline is approaching',
      'subtitle' => 'Filing support',
      'body' => '<p>You need to establish what is ready, what is missing and what work remains.</p>',
    ],
    [
      'title' => 'You are changing accountants',
      'subtitle' => 'Handover',
      'body' => '<p>You need a clear handover and continuity of records, reporting and filing responsibilities.</p>',
    ],
    [
      'title' => 'An audit is coming up',
      'subtitle' => 'Audit preparation',
      'body' => '<p>Your financial statements and supporting documents need to be organised for review.</p>',
    ],
    [
      'title' => 'You need a clearer financial picture',
      'subtitle' => 'Reporting',
      'body' => '<p>You want to understand revenue, expenses, unpaid customer invoices, supplier balances and cash flow.</p>',
    ],
    [
      'title' => 'Request a Consultation',
      'body' => '',
      'uri' => 'internal:#assessment',
      'link_title' => 'Request a Consultation',
    ],
  ],
];

$link = 'Explore service';
$catalogue = [
  'title' => 'Explore our accounting, tax and audit services',
  'lede' => '<p>Find support for a specific requirement or discuss an ongoing arrangement covering several services.</p>',
  'items' => [
    [
      'title' => 'Bookkeeping & Financial Reporting',
      'subtitle' => 'Books & Payroll',
      'body' => '<p>Motaded records day-to-day transactions, reconciles accounts and prepares the financial reports agreed in the scope.</p>',
    ],
    [
      'title' => 'Accounting Setup',
      'subtitle' => 'Books & Payroll',
      'body' => '<p>Motaded sets up the chart of accounts and the processes for invoices, expenses and supporting records, so operations and reporting can be recorded correctly from the start.</p>',
    ],
    [
      'title' => 'Accounting Catch-Up',
      'subtitle' => 'Books & Payroll',
      'body' => '<p>Motaded brings overdue records up to date, reconciles transactions and identifies missing supporting documents.</p>',
      'uri' => _motaded_acc_first_service_uri('Accounting Catch-Up', 463),
      'link_title' => $link,
    ],
    [
      'title' => 'Accounts Review',
      'subtitle' => 'Books & Payroll',
      'body' => '<p>Motaded reviews your financial records and statements and reports inconsistencies that need attention.</p>',
      'uri' => _motaded_acc_first_service_uri('Accounts Review', 464),
      'link_title' => $link,
    ],
    [
      'title' => 'Payroll Management',
      'subtitle' => 'Books & Payroll',
      'body' => '<p>Motaded prepares payroll figures and keeps the salary records organised.</p>',
      'uri' => _motaded_acc_first_service_uri('Payroll Management Services', 453),
      'link_title' => $link,
    ],
    [
      'title' => 'VAT Registration',
      'subtitle' => 'VAT',
      'body' => '<p>Motaded assesses whether you need to register and prepares the information for the application.</p>',
      'uri' => _motaded_acc_first_service_uri('VAT Registration', 466),
      'link_title' => $link,
    ],
    [
      'title' => 'VAT Filing',
      'subtitle' => 'VAT',
      'body' => '<p>Motaded reviews the relevant transactions and prepares the VAT return. Submission is included where authorised in the proposal.</p>',
      'uri' => _motaded_acc_first_service_uri('VAT Filing', 467),
      'link_title' => $link,
    ],
    [
      'title' => 'VAT Advisory',
      'subtitle' => 'VAT',
      'body' => '<p>Motaded advises how VAT applies to your activities and transactions.</p>',
      'uri' => _motaded_acc_first_service_uri('VAT Advisory', 465),
      'link_title' => $link,
    ],
    [
      'title' => 'VAT Refund',
      'subtitle' => 'VAT',
      'body' => '<p>Motaded assesses refund eligibility and prepares the claim with the supporting invoices and records.</p>',
      'uri' => _motaded_acc_first_service_uri('VAT Refund', 468),
      'link_title' => $link,
    ],
    [
      'title' => 'VAT Deregistration',
      'subtitle' => 'VAT',
      'body' => '<p>Motaded reviews whether you can close the VAT registration and prepares the required records and steps.</p>',
      'uri' => _motaded_acc_first_service_uri('VAT Deregistration', 469),
      'link_title' => $link,
    ],
    [
      'title' => 'Zakat',
      'subtitle' => 'Zakat & Tax',
      'body' => '<p>Motaded prepares the zakat calculation and return from your financial records.</p>',
      'uri' => _motaded_acc_first_service_uri('ZAKAT', 473),
      'link_title' => $link,
    ],
    [
      'title' => 'Corporate Tax',
      'subtitle' => 'Zakat & Tax',
      'body' => '<p>Motaded reviews your corporate tax position and prepares the calculations and documents needed for filing.</p>',
      'uri' => _motaded_acc_first_service_uri('Corporate Tax', 472),
      'link_title' => $link,
    ],
    [
      'title' => 'Withholding Tax — WHT',
      'subtitle' => 'Zakat & Tax',
      'body' => '<p>Motaded reviews payments to non-residents and prepares the withholding tax calculations and returns.</p>',
      'uri' => _motaded_acc_first_service_uri('Withholding', 470),
      'link_title' => $link,
    ],
    [
      'title' => 'Internal Audit',
      'subtitle' => 'Audit',
      'body' => '<p>Motaded reviews financial and operational controls, identifies gaps and recommends improvements.</p>',
      'uri' => _motaded_acc_first_service_uri('Internal Audit', 475),
      'link_title' => $link,
    ],
    [
      'title' => 'External Audit Support',
      'subtitle' => 'Audit',
      'body' => '<p>Motaded prepares the financial statements and supporting schedules and coordinates the appointed auditor. The audit opinion is issued by that auditor, not by Motaded.</p>',
      'uri' => _motaded_acc_first_service_uri('External Audit', 474),
      'link_title' => $link,
    ],
    [
      'title' => 'Inventory & Stock Audit',
      'subtitle' => 'Audit',
      'body' => '<p>Motaded compares physical stock with the inventory records and investigates discrepancies.</p>',
      'uri' => _motaded_acc_first_service_uri('Inventory', 476),
      'link_title' => $link,
    ],
  ],
];

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

$needs_p = NULL;
$situations_p = NULL;
$catalogue_p = NULL;
$ordered = [];
foreach ($source->get('field_paragraphs') as $item) {
  $paragraph = $item->entity;
  if (!$paragraph instanceof ParagraphInterface) {
    continue;
  }
  $type = $paragraph->hasField('field_card_type') && !$paragraph->get('field_card_type')->isEmpty()
    ? (string) $paragraph->get('field_card_type')->value
    : '';
  if ($type === 'needs') {
    $needs_p = $paragraph;
    continue;
  }
  if ($type === 'situations') {
    $situations_p = $paragraph;
    continue;
  }
  if ($type === 'catalogue') {
    $catalogue_p = $paragraph;
    continue;
  }
  $ordered[] = [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

if (!$needs_p || !$situations_p || !$catalogue_p) {
  throw new \RuntimeException('Accounting first-block paragraphs not found.');
}

_motaded_acc_first_replace_cards($needs_p, $needs);
_motaded_acc_first_replace_cards($situations_p, $situations);
_motaded_acc_first_replace_cards($catalogue_p, $catalogue);
$catalogue_p->set('field_sub_title', '');
$catalogue_p->save();

$hero = array_shift($ordered);
$refs = [];
if (is_array($hero)) {
  $refs[] = $hero;
}
$refs[] = [
  'target_id' => $needs_p->id(),
  'target_revision_id' => $needs_p->getRevisionId(),
];
$refs[] = [
  'target_id' => $situations_p->id(),
  'target_revision_id' => $situations_p->getRevisionId(),
];
$refs[] = [
  'target_id' => $catalogue_p->id(),
  'target_revision_id' => $catalogue_p->getRevisionId(),
];
$refs = array_merge($refs, $ordered);

$source->set('field_paragraphs', $refs);
$source->save();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid]);
echo "Updated first three blocks on /node/{$nid}.\n";

function _motaded_acc_first_service_uri(string $needle, int $fallback_nid): string {
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

function _motaded_acc_first_replace_cards(ParagraphInterface $parent, array $data): void {
  $old = $parent->get('field_paragraphs')->referencedEntities();
  $children = [];
  foreach ($data['items'] as $item) {
    $values = [
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $item['title'],
      'field_body' => [
        'value' => $item['body'],
        'format' => 'basic_html',
      ],
    ];
    if (($item['subtitle'] ?? '') !== '') {
      $values['field_sub_title'] = $item['subtitle'];
    }
    if (($item['uri'] ?? '') !== '') {
      $values['field_link'] = [
        'uri' => $item['uri'],
        'title' => $item['link_title'] !== '' ? $item['link_title'] : 'Explore service',
      ];
    }
    $card = Paragraph::create($values);
    $card->save();
    $children[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }
  $parent->set('field_title', $data['title']);
  $parent->set('field_body', [
    'value' => $data['lede'],
    'format' => 'basic_html',
  ]);
  $parent->set('field_paragraphs', $children);
  $parent->save();
  foreach ($old as $child) {
    $child->delete();
  }
}
