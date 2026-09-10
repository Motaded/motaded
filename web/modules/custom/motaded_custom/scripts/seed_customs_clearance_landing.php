<?php

/**
 * @file
 * Seeds the English customs clearance landing_page.
 *
 * Usage: ddev drush php:script web/modules/custom/motaded_custom/scripts/seed_customs_clearance_landing.php
 */

declare(strict_types=1);

use Drupal\block\Entity\Block;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\webform\Entity\Webform;

const MOTADED_CUSTOMS_LANDING_ALIAS = '/customs-clearance-saudi-arabia';

_motaded_customs_enable_webform_paragraph();
_motaded_customs_sync_webform();
_motaded_customs_hide_setup_prefooter();

$node = _motaded_customs_load_or_create_node();
$paragraphs = [
  _motaded_customs_hero(),
  _motaded_customs_overview(),
  _motaded_customs_glance(),
  _motaded_customs_scope(),
  _motaded_customs_authorities(),
  _motaded_customs_cargo(),
  _motaded_customs_process(),
  _motaded_customs_documents(),
  _motaded_customs_fees(),
  _motaded_customs_working(),
  _motaded_customs_related(),
  _motaded_customs_assessment_form(),
  _motaded_customs_final_cta(),
];

$refs = [];
foreach ($paragraphs as $paragraph) {
  $refs[] = _motaded_customs_pref($paragraph);
}
$node->setTitle('Customs Clearance in Saudi Arabia');
$node->set('field_paragraphs', $refs);
$node->set('field_form', []);
$node->set('field_faq', _motaded_customs_faq_items());
$node->set('field_meta', json_encode([
  'title' => 'Customs Clearance in Saudi Arabia | Motaded',
  'description' => 'Motaded provides import and export clearance support in Saudi Arabia: document review, declaration procedures, applicable product requirements and release follow-up. Scope and service fee are agreed before work begins.',
], JSON_UNESCAPED_SLASHES));
$node->save();

$nid = (int) $node->id();
_motaded_customs_set_alias($nid, MOTADED_CUSTOMS_LANDING_ALIAS, 'en');
echo "Customs landing EN: /node/{$nid} → " . MOTADED_CUSTOMS_LANDING_ALIAS . "\n";

function _motaded_customs_enable_webform_paragraph(): void {
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

function _motaded_customs_sync_webform(): void {
  $data = \Drupal::service('config.storage.sync')->read('webform.webform.customs_clearance_quote');
  if (!is_array($data)) {
    throw new \RuntimeException('Could not read customs_clearance_quote from config sync.');
  }
  \Drupal::configFactory()->getEditable('webform.webform.customs_clearance_quote')->setData($data)->save(TRUE);
  if (Webform::load('customs_clearance_quote')) {
    echo "Updated webform customs_clearance_quote.\n";
  }
}

function _motaded_customs_hide_setup_prefooter(): void {
  $block = Block::load('motaded_theme_ctaprefooter');
  if (!$block) {
    return;
  }
  $block->setVisibilityConfig('request_path', [
    'id' => 'request_path',
    'negate' => TRUE,
    'pages' => MOTADED_CUSTOMS_LANDING_ALIAS . "\n/ar" . MOTADED_CUSTOMS_LANDING_ALIAS,
  ]);
  $block->save();
}

function _motaded_customs_load_or_create_node(): Node {
  $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'alias' => MOTADED_CUSTOMS_LANDING_ALIAS,
    'langcode' => 'en',
  ]);
  $alias = $aliases ? reset($aliases) : NULL;
  if ($alias instanceof PathAlias) {
    $nid = (int) str_replace('/node/', '', $alias->getPath());
    $node = Node::load($nid);
    if ($node instanceof Node && $node->bundle() === 'landing_page') {
      if ($node->hasTranslation('en')) {
        $node = $node->getTranslation('en');
      }
      $node->setPublished(TRUE);
      echo "Updating landing_page nid={$nid}.\n";
      return $node;
    }
  }
  echo "Creating new landing_page.\n";
  return Node::create([
    'type' => 'landing_page',
    'title' => 'Customs Clearance in Saudi Arabia',
    'langcode' => 'en',
    'status' => 1,
    'uid' => 1,
  ]);
}

function _motaded_customs_set_alias(int $nid, string $alias, string $langcode): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  foreach ($storage->loadByProperties(['path' => '/node/' . $nid, 'langcode' => $langcode]) as $entity) {
    $entity->delete();
  }
  PathAlias::create([
    'path' => '/node/' . $nid,
    'alias' => $alias,
    'langcode' => $langcode,
  ])->save();
}

function _motaded_customs_pref(Paragraph $paragraph): array {
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

function _motaded_customs_html(string $value, string $format = 'basic_html'): array {
  return [
    'value' => $value,
    'format' => $format,
  ];
}

function _motaded_customs_stage_body(string $client, string $motaded, string $next): string {
  return '<div class="customs-stage__grid">'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Client provides</span><p>' . $client . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Motaded does</span><p>' . $motaded . '</p></div>'
    . '<div class="customs-stage__col"><span class="customs-stage__label">Next step</span><p>' . $next . '</p></div>'
    . '</div>';
}

function _motaded_customs_create_card(string $title, string $body, string $uri = '', string $link_title = '', string $format = 'basic_html'): array {
  $values = [
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $title,
    'field_body' => _motaded_customs_html($body, $format),
  ];
  if ($uri !== '') {
    $values['field_link'] = [
      'uri' => $uri,
      'title' => $link_title !== '' ? $link_title : 'Official site',
    ];
  }
  $card = Paragraph::create($values);
  $card->save();
  return _motaded_customs_pref($card);
}

function _motaded_customs_create_cards(string $title, string $lede, array $items, string $type = 'simple_text_card_unequal_height'): Paragraph {
  $children = [];
  foreach ($items as $item) {
    $children[] = _motaded_customs_create_card(
      $item['title'],
      $item['body'],
      $item['uri'] ?? '',
      $item['link_title'] ?? '',
      $item['format'] ?? 'basic_html'
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
    $values['field_body'] = _motaded_customs_html($lede);
  }
  $cards = Paragraph::create($values);
  $cards->save();
  return $cards;
}

function _motaded_customs_highlight(string $label, string $icon): array {
  $item = Paragraph::create([
    'type' => 'hero_split_highlight',
    'langcode' => 'en',
    'field_title' => $label,
    'field_why_feature_icon' => $icon,
  ]);
  $item->save();
  return _motaded_customs_pref($item);
}

function _motaded_customs_hero(): Paragraph {
  $hero = Paragraph::create([
    'type' => 'hero_split_banner',
    'langcode' => 'en',
    'field_hero_split_eyebrow' => 'Customs services · Saudi Arabia',
    'field_hero_split_headline_prefix' => 'Customs Clearance',
    'field_hero_split_headline_accent' => 'in Saudi Arabia',
    'field_body' => _motaded_customs_html(
      '<p>Import and export clearance support for businesses trading with Saudi Arabia.</p>'
      . '<p>Motaded supports the preparation and coordination of customs clearance files, including document review, declaration procedures, applicable product requirements and release follow-up.</p>'
      . '<p>Submit your shipment details for an assessment of the required work and a service quotation.</p>'
    ),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Clearance Assessment',
    ],
    'field_link_secondary' => [
      'uri' => 'internal:/contact-us',
      'title' => 'Contact Our Team',
    ],
  ]);
  $hero->set('field_hero_split_highlights', [
    _motaded_customs_highlight('Import and export', 'globe'),
    _motaded_customs_highlight('Arabic and English', 'document'),
    _motaded_customs_highlight('Scope and service fee agreed before work begins', 'shield_check'),
  ]);
  $hero->save();
  return $hero;
}

function _motaded_customs_overview(): Paragraph {
  $values = [
    'type' => 'media_lead',
    'langcode' => 'en',
    'field_title' => 'Service Overview',
    'field_body' => _motaded_customs_html(
      '<p>This service is for companies that import goods into Saudi Arabia or export goods from the Kingdom and require a structured clearance file.</p>'
      . '<p>Engage Motaded when a shipment is planned or already in transit, when product-specific requirements may apply, or when the importer needs a single coordinator for documents, declaration and release.</p>'
      . '<p><strong>Motaded’s role</strong> is to review the file, prepare and submit the agreed filings, coordinate additional requirements where they apply, and follow the release. Motaded does not replace the competent authority and does not determine duty or tax.</p>'
      . '<p><strong>Expected result:</strong> an agreed scope of work, a complete file for the shipment, and a documented service quotation before execution begins.</p>'
    ),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Clearance Assessment',
    ],
  ];
  $media = Media::load(1484);
  if ($media && $media->bundle() === 'image') {
    $values['field_media'] = ['target_id' => 1484];
  }
  $lead = Paragraph::create($values);
  $lead->save();
  return $lead;
}

function _motaded_customs_glance(): Paragraph {
  $stat = static function (string $value, string $title, string $body, string $icon): array {
    $item = Paragraph::create([
      'type' => 'glance_stat_item',
      'langcode' => 'en',
      'field_glance_value' => $value,
      'field_glance_title' => $title,
      'field_glance_body' => $body,
      'field_glance_icon' => $icon,
    ]);
    $item->save();
    return _motaded_customs_pref($item);
  };

  $highlight = Paragraph::create([
    'type' => 'glance_stat_item',
    'langcode' => 'en',
    'field_glance_value' => 'Businesses',
    'field_glance_title' => 'Who it is for',
    'field_glance_body' => 'Companies importing into or exporting from Saudi Arabia, and the PRO or logistics teams acting for them.',
    'field_glance_icon' => 'building',
  ]);
  $highlight->save();

  $glance = Paragraph::create([
    'type' => 'glance_block',
    'langcode' => 'en',
    'field_glance_eyebrow' => 'Service at a glance',
    'field_glance_title' => 'Service at a Glance',
    'field_body' => _motaded_customs_html('<p>Clearance timelines depend on document completeness, cargo category and any inspection appointments set by the competent authority. Motaded does not publish a fixed service-level period.</p>'),
    'field_glance_highlight' => [_motaded_customs_pref($highlight)],
    'field_glance_stats' => [
      $stat('Import / export', 'Trade direction', 'Both directions are supported. The document set and FASAH messages differ by direction.', 'globe'),
      $stat('AR / EN', 'Languages', 'File handling and status updates in Arabic and English.', 'users'),
      $stat('Quoted', 'Service fee', 'Motaded’s fee is calculated per file and agreed in writing before work begins. Official charges are separate.', 'money'),
    ],
  ]);
  $glance->save();
  return $glance;
}

function _motaded_customs_scope(): Paragraph {
  return _motaded_customs_create_cards(
    'Scope of Services',
    '<p>Confirmed in the quotation. A typical file covers the four workstreams below.</p>',
    [
      [
        'title' => 'Document review',
        'body' => '<p>Invoice, packing list, transport document and importer data checked before filing. Gaps are listed in writing.</p>',
      ],
      [
        'title' => 'Declaration',
        'body' => '<p>Customs declaration prepared and submitted on <a href="/platforms/fasah">FASAH</a>, aligned with the Commercial Register.</p>',
      ],
      [
        'title' => 'Inspections',
        'body' => '<p><a href="/platforms/saber">SABER</a> or <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> requirements identified where they apply. Inspection appointments coordinated when required.</p>',
      ],
      [
        'title' => 'Release',
        'body' => '<p>Official charges and the release decision followed, then handover to the client’s transporter.</p>',
      ],
    ],
    'simple_text_card'
  );
}

function _motaded_customs_authorities(): Paragraph {
  return _motaded_customs_create_cards(
    'Relevant Authorities and Platforms',
    '<p>Motaded coordinates the agreed file through the official channels. The systems on a shipment depend on the goods. Rules are published by the authority concerned. Motaded’s platform notes are linked in each row.</p>',
    [
      [
        'title' => 'ZATCA',
        'body' => '<p>Customs procedures, duty and import VAT. Assessment and release stay with the authority. <a href="/platforms/zatca">Motaded on ZATCA</a>.</p>',
        'uri' => 'https://zatca.gov.sa/en/Pages/default.aspx',
        'link_title' => 'zatca.gov.sa',
      ],
      [
        'title' => 'FASAH',
        'body' => '<p>National platform for the customs declaration. Importer data must match the Commercial Register. <a href="/platforms/fasah">Motaded on FASAH</a>.</p>',
        'uri' => 'https://fasah.sa/',
        'link_title' => 'fasah.sa',
      ],
      [
        'title' => 'SABER / SASO',
        'body' => '<p>Product conformity certificates where a technical regulation applies — before arrival, not instead of the declaration. <a href="/platforms/saber">Motaded on SABER</a>.</p>',
        'uri' => 'https://saber.sa/',
        'link_title' => 'saber.sa',
      ],
      [
        'title' => 'SFDA',
        'body' => '<p>Additional controls for food, medicines, medical devices and cosmetics. The decision stays with SFDA. <a href="/platforms/saudi-food-and-drug-authority-sfda">Motaded on SFDA</a>.</p>',
        'uri' => 'https://www.sfda.gov.sa/',
        'link_title' => 'sfda.gov.sa',
      ],
    ]
  );
}

function _motaded_customs_cargo(): Paragraph {
  return _motaded_customs_create_cards(
    'Cargo Categories and Additional Requirements',
    '<p>Every shipment needs a customs declaration. Extra requirements depend on the product — Motaded confirms them on the file.</p>',
    [
      [
        'title' => 'General cargo',
        'body' => '<p><a href="/platforms/fasah">FASAH</a> + <a href="/platforms/zatca">ZATCA</a>. <a href="/platforms/saber">SABER</a> if a technical regulation applies.</p>',
      ],
      [
        'title' => 'Food',
        'body' => '<p><a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> rules, and inspection where SFDA requires it.</p>',
      ],
      [
        'title' => 'Pharma and medical devices',
        'body' => '<p><a href="/services/sfda-medical-device-registration-mdma">SFDA MDMA registration</a>, in addition to clearance.</p>',
      ],
      [
        'title' => 'Cosmetics',
        'body' => '<p><a href="/services/sfda-cosmetics-product-registration">SFDA cosmetics registration</a>, in addition to the declaration.</p>',
      ],
      [
        'title' => 'Electronics and consumer goods',
        'body' => '<p><a href="/platforms/saber">SABER</a> PCoC/SCoC often required before arrival.</p>',
      ],
      [
        'title' => 'Chemicals and restricted goods',
        'body' => '<p>Additional permits may apply. Not treated as general cargo.</p>',
      ],
    ]
  );
}

function _motaded_customs_process(): Paragraph {
  return _motaded_customs_create_cards(
    'Clearance Process',
    '<p>The sequence below is the standard Motaded working order. Timing depends on document completeness, cargo category and inspection appointments. Motaded does not guarantee a fixed number of days.</p>',
    [
      [
        'title' => 'Initial assessment',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Company details, trade direction, cargo category, and available commercial documents.',
          'Identifies the likely channels (<a href="/platforms/fasah">FASAH</a> / <a href="/platforms/zatca">ZATCA</a> and any product-specific system), lists missing items and proposes a scope of work.',
          'Written service quotation for acceptance.'
        ),
      ],
      [
        'title' => 'Documents and applicable requirements',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Invoice, packing list, bill of lading or air waybill, Commercial Register data, and any SABER or SFDA evidence already held.',
          'Checks consistency of values, weights and consignee data; flags product certificates that must exist before arrival.',
          'Complete file ready for declaration, or a written list of outstanding items.'
        ),
      ],
      [
        'title' => 'Declaration and inspections',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Confirmation to file, and any clarifications requested by the authority.',
          'Submits the declaration on <a href="/platforms/fasah">FASAH</a>, follows the file with <a href="/platforms/zatca">ZATCA</a>, and coordinates <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or <a href="/platforms/saber">SABER</a> inspection where required.',
          'Official charges, if issued, and the authority’s decision.'
        ),
      ],
      [
        'title' => 'Official charges, release and handover',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Payment of official duty, tax and any inspection fees through the authority’s channels; nomination of the transporter.',
          'Follows release and hands the shipment to the nominated transporter. The Motaded invoice covers only the agreed service fee.',
          'Closed file and status confirmation to the named contact.'
        ),
      ],
    ]
  );
}

function _motaded_customs_documents(): Paragraph {
  return _motaded_customs_create_cards(
    'Requirements and Documents',
    '<p>The exact set depends on HS code, trade direction and the product. Motaded confirms the list on the file. Commercial Register data must match <a href="/platforms/fasah">FASAH</a> — CR procedures go through the <a href="https://business.sa/">Saudi Business Center</a> (<a href="/platforms/saudi-business-center-meras">Motaded on Meras</a>).</p>',
    [
      [
        'title' => 'Required for filing',
        'body' => '<ul><li>Commercial invoice</li><li>Packing list</li><li>Bill of lading or air waybill</li><li>Importer CR data matching FASAH</li><li>Incoterms and insurance as declared</li></ul>',
      ],
      [
        'title' => 'If the goods require it',
        'body' => '<ul><li>SABER PCoC/SCoC — <a href="https://saber.sa/">saber.sa</a>, <a href="/platforms/saber">Motaded on SABER</a></li><li>SFDA evidence for food, pharma, devices or cosmetics — <a href="https://www.sfda.gov.sa/">sfda.gov.sa</a>, <a href="/platforms/saudi-food-and-drug-authority-sfda">Motaded on SFDA</a></li><li>Any other permit flagged at assessment</li></ul>',
      ],
      [
        'title' => 'Enough to start',
        'body' => '<p>Company name, trade direction, cargo category, and the invoice or packing list if you already have it. The rest can follow before filing.</p>',
      ],
    ],
    'simple_text_card'
  );
}

function _motaded_customs_fees(): Paragraph {
  return _motaded_customs_create_cards(
    'Fees and Quotation',
    '<p>Three lines of cost. Only the Motaded fee is set by Motaded.</p>',
    [
      [
        'title' => 'Motaded service fee',
        'body' => '<p><strong>Paid to Motaded</strong></p><ul><li>Quoted per file, in writing, before work</li><li>Reflects direction, entry point, cargo and inspections</li><li>Payable by bank transfer against the quotation</li></ul>',
      ],
      [
        'title' => 'Duty, VAT and official charges',
        'body' => '<p><strong>Paid to the authority</strong></p><ul><li>Duty and import VAT — set by <a href="/platforms/zatca">ZATCA</a></li><li>VAT currently 15% — confirm on <a href="https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx">ZATCA VAT</a></li><li>Inspection fees if SFDA or another body examines the goods</li></ul>',
        'uri' => 'https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx',
        'link_title' => 'ZATCA customs regulations',
      ],
      [
        'title' => 'What we need to quote',
        'body' => '<p><strong>Send with the form</strong></p><ul><li>Trade direction and cargo category</li><li>Origin or destination, and port if known</li><li>Invoice or packing list — value helps, duty stays official</li></ul>',
      ],
    ],
    'simple_text_card'
  );
}

function _motaded_customs_working(): Paragraph {
  return _motaded_customs_create_cards(
    'Working with Motaded',
    '<p>Riyadh-based consultancy, established 2017. Clearance is a defined professional engagement — not a government service and not a substitute for the authority. More on <a href="/about-us">About Motaded</a>.</p>',
    [
      [
        'title' => 'Named file contact',
        'body' => '<p>One coordinator for documents, filings and status. You name who receives updates.</p>',
      ],
      [
        'title' => 'Agreed scope in writing',
        'body' => '<p>Work starts after the quotation is accepted. Changes are confirmed before they are billed.</p>',
      ],
      [
        'title' => 'Status updates',
        'body' => '<p>Missing documents, inspections, official charges and release — reported to the named contact.</p>',
      ],
    ],
    'simple_text_card'
  );
}

function _motaded_customs_related(): Paragraph {
  return _motaded_customs_create_cards(
    'Related Motaded pages',
    '<p>Official rules stay on the authority sites. These Motaded pages explain the same channels and related work.</p>',
    [
      [
        'title' => 'ZATCA',
        'body' => '<p>Customs, duty and import VAT.</p>',
        'uri' => 'internal:/platforms/zatca',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'FASAH',
        'body' => '<p>Electronic customs declaration.</p>',
        'uri' => 'internal:/platforms/fasah',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'SABER',
        'body' => '<p>Product conformity certificates.</p>',
        'uri' => 'internal:/platforms/saber',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'SFDA',
        'body' => '<p>Food, pharma, devices and cosmetics.</p>',
        'uri' => 'internal:/platforms/saudi-food-and-drug-authority-sfda',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'Importer and exporter registration',
        'body' => '<p>Electronic registration required before FASAH filing.</p>',
        'uri' => 'internal:/services/electronic-registration-importers-and-exporters',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'Importer number at a new port',
        'body' => '<p>When the consignment uses a port not yet on the file.</p>',
        'uri' => 'internal:/services/adding-importer-number-new-port',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'Customs Law',
        'body' => '<p>English text of the Customs Law, in the Motaded library.</p>',
        'uri' => 'internal:/documents/customs-law-english',
        'link_title' => 'Open page',
      ],
      [
        'title' => 'About Motaded',
        'body' => '<p>Who we are and how engagements are delivered.</p>',
        'uri' => 'internal:/about-us',
        'link_title' => 'Open page',
      ],
    ],
    'simple_text_card'
  );
}

function _motaded_customs_assessment_form(): Paragraph {
  $form = Paragraph::create([
    'type' => 'webform',
    'langcode' => 'en',
    'field_body' => [
      'value' => '<h2>Request a Clearance Assessment</h2><p>Company and shipment details are enough to start. Motaded replies with the required work and a service quotation. Official duty and tax stay with the authority.</p>',
      'format' => 'full_html',
    ],
    'field_webform' => [
      'target_id' => 'customs_clearance_quote',
      'status' => 'open',
    ],
  ]);
  $form->save();
  return $form;
}

function _motaded_customs_final_cta(): Paragraph {
  $cta = Paragraph::create([
    'type' => 'promo_split',
    'langcode' => 'en',
    'field_title' => 'Contact Our Customs Clearance Team',
    'field_body' => _motaded_customs_html(
      '<p>Request an assessment on this page, write to <a href="mailto:info@motaded.com.sa">info@motaded.com.sa</a>, or contact the team on WhatsApp or by telephone: +966 53 979 7197.</p>'
    ),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Clearance Assessment',
    ],
    'field_promo_split_side' => 'image_right',
  ]);
  $cta->save();
  return $cta;
}

function _motaded_customs_faq_items(): array {
  return [
    [
      'question' => 'How long does clearance take?',
      'answer' => '<p>Duration is typically measured in days. It depends on document completeness, cargo category and inspection appointments set by the competent authority. Motaded does not offer a guaranteed number of days.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'What if documents are incomplete?',
      'answer' => '<p>Motaded lists the outstanding items and does not file an incomplete declaration unless the client accepts the associated risk in writing. An assessment can still be issued on the documents already supplied.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'The goods have already arrived. Can Motaded still assist?',
      'answer' => '<p>Yes. State the entry point and the documents on hand in the assessment form. Additional storage or inspection costs, if any, are official charges and are not included in the Motaded fee unless the quotation says so.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'When are additional inspections required?',
      'answer' => '<p>When the cargo category falls under <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or another examining body, or when <a href="/platforms/zatca">ZATCA</a> or <a href="/platforms/saber">SABER</a> procedures require physical or documentary examination. Motaded coordinates the appointment; the authority decides the outcome.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'What does the client pay, and to whom?',
      'answer' => '<p>The Motaded service fee is paid to Motaded against the accepted quotation. Customs duty, import VAT and any inspection fees are paid through the official channels to the competent authority. See also <a href="/platforms/zatca">Motaded on ZATCA</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Do you handle both import and export?',
      'answer' => '<p>Yes. Select the trade direction on the form. The document set and <a href="/platforms/fasah">FASAH</a> messages differ; Motaded confirms the file requirements during assessment. Importers who are not yet on FASAH may need <a href="/services/electronic-registration-importers-and-exporters">electronic registration</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Is a Commercial Register required?',
      'answer' => '<p>A <a href="/platforms/fasah">FASAH</a> declaration must match a registered importer. If the company is not yet registered, Motaded can assess the shipment and, separately, advise on entity procedures through the <a href="https://business.sa/">Saudi Business Center</a> or <a href="/platforms/saudi-business-center-meras">Motaded’s Meras page</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'How does SABER relate to the customs declaration?',
      'answer' => '<p><a href="/platforms/saber">SABER</a> addresses product conformity. <a href="/platforms/fasah">FASAH</a> and <a href="/platforms/zatca">ZATCA</a> address the shipment file. Both may be required. A conformity certificate does not replace the declaration. The English <a href="/documents/customs-law-english">Customs Law</a> is in the Motaded library.</p>',
      'answer_format' => 'basic_html',
    ],
  ];
}
