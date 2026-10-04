<?php

/**
 * @file
 * Seeds the English customs clearance landing_page, then Arabic.
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

const MOTADED_CUSTOMS_LANDING_ALIAS = '/services/customs-clearance-saudi-arabia';

_motaded_customs_enable_webform_paragraph();
_motaded_customs_sync_webform();
_motaded_customs_hide_setup_prefooter();

$node = _motaded_customs_load_or_create_node();
$paragraphs = [
  _motaded_customs_hero(),
  _motaded_customs_overview(),
  _motaded_customs_cargo(),
  _motaded_customs_scope(),
  _motaded_customs_working(),
  _motaded_customs_trade_market(),
  _motaded_customs_process(),
  _motaded_customs_mid_cta(),
  _motaded_customs_documents(),
  _motaded_customs_fees(),
  _motaded_customs_assessment_form(),
  _motaded_customs_final_cta(),
  _motaded_customs_related(),
];

$refs = [];
foreach ($paragraphs as $paragraph) {
  $refs[] = _motaded_customs_pref($paragraph);
}
$node->setTitle('Customs Clearance in Saudi Arabia');
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
  'title' => 'Customs Clearance in Saudi Arabia | Motaded',
      'description' => 'Customs clearance in Saudi Arabia for import and export: document review, FASAH declaration, SABER and SFDA requirements where applicable, and follow-up to the release decision. Scope and service fee are agreed in writing before work begins.',
], JSON_UNESCAPED_SLASHES));
$node->save();

$nid = (int) $node->id();
_motaded_customs_set_alias($nid, MOTADED_CUSTOMS_LANDING_ALIAS, 'en');
echo "Customs landing EN: /node/{$nid} → " . MOTADED_CUSTOMS_LANDING_ALIAS . "\n";
require __DIR__ . '/seed_customs_clearance_landing_ar.php';

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

function _motaded_customs_create_card(string $title, string $body, string $uri = '', string $link_title = '', string $format = 'basic_html', string $subtitle = ''): array {
  $values = [
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $title,
    'field_body' => _motaded_customs_html($body, $format),
  ];
  if ($subtitle !== '') {
    $values['field_sub_title'] = $subtitle;
  }
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

function _motaded_customs_create_cards(string $title, string $lede, array $items, string $type = 'simple_text_card_unequal_height', array $headers = []): Paragraph {
  $children = [];
  foreach ($items as $item) {
    $children[] = _motaded_customs_create_card(
      $item['title'],
      $item['body'] ?? '',
      $item['uri'] ?? '',
      $item['link_title'] ?? '',
      $item['format'] ?? 'basic_html',
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
    $values['field_body'] = _motaded_customs_html($lede);
  }
  if ($headers) {
    $values['field_sub_title'] = implode('|', $headers);
  }
  $cards = Paragraph::create($values);
  $cards->save();
  return $cards;
}

function _motaded_customs_hero_feature(string $title, string $body, string $icon, string $uri = ''): array {
  $values = [
    'type' => 'why_feature_card',
    'langcode' => 'en',
    'field_title' => $title,
    'field_body' => [
      'value' => $body,
      'format' => 'plain_text',
    ],
    'field_why_feature_icon' => $icon,
  ];
  if ($uri !== '') {
    $values['field_link'] = [
      'uri' => $uri,
      'title' => '',
    ];
  }
  $card = Paragraph::create($values);
  $card->save();
  return _motaded_customs_pref($card);
}

function _motaded_customs_hero(): Paragraph {
  $hero = Paragraph::create([
    'type' => 'hero_split_banner',
    'langcode' => 'en',
    'field_hero_split_eyebrow' => 'Customs services · Saudi Arabia',
    'field_hero_split_headline_prefix' => 'Customs Clearance',
    'field_hero_split_headline_accent' => 'in Saudi Arabia',
    'field_body' => _motaded_customs_html(
      '<p>Customs clearance support for import and export consignments in the Kingdom of Saudi Arabia, covering document review, FASAH declaration procedures and follow-up through to the authority’s release decision.</p>'
    ),
    'field_link' => [
      'uri' => 'internal:#assessment',
      'title' => 'Request a Consultation',
    ],
  ]);
  $hero_media = Media::load(1494);
  if ($hero_media && $hero_media->bundle() === 'image') {
    $hero->set('field_media', ['target_id' => 1494]);
  }
  $hero->set('field_hero_split_highlights', []);
  $hero->set('field_hero_split_features', []);
  $hero->save();
  return $hero;
}

function _motaded_customs_overview(): Paragraph {
  return _motaded_customs_create_cards(
    'Service Overview',
    '<p>Motaded provides customs clearance coordination for companies importing goods into Saudi Arabia or exporting goods from the Kingdom. Support covers shipment document review, coordination of customs declaration preparation, applicable product requirements and release follow-up.</p>'
    . '<p>The engagement is defined according to the goods, shipment route and current consignment status. The written quotation identifies the agreed work, responsibilities and service fees before work begins.</p>',
    [
      [
        'title' => 'Shipment Status',
        'body' => '<ul><li><strong>Before dispatch:</strong> review available documents and identify outstanding requirements.</li><li><strong>In transit:</strong> coordinate remaining documentation and clearance preparation.</li><li><strong>After arrival:</strong> review the current status and outstanding actions required for clearance.</li></ul>',
      ],
    ],
    'overview'
  );
}

function _motaded_customs_trade_market(): Paragraph {
  $paragraph = Paragraph::create([
    'type' => 'trade_market_overview',
    'langcode' => 'en',
    'field_title' => 'Saudi Arabia’s trade in figures',
    'field_body' => _motaded_customs_html('<p>Merchandise imports and exports for the latest month, compared with the same month last year, and the recent monthly trend.</p>'),
  ]);
  $paragraph->save();
  return $paragraph;
}

function _motaded_customs_scope(): Paragraph {
  return _motaded_customs_create_cards(
    'Customs Clearance Scope of Services',
    '<p>Motaded coordinates the agreed customs clearance work between the client, supplier, appointed customs broker and relevant service providers. The scope covers document preparation, declaration coordination, product-related requirements and release follow-up.</p>',
    [
      [
        'title' => 'Shipment Document Preparation',
        'body' => '<p>Review the available invoice, packing list and transport documents for completeness and consistency. Identify missing information and coordinate corrections with the client or supplier before the documents are passed for declaration preparation.</p>',
      ],
      [
        'title' => 'Customs Declaration Coordination',
        'body' => '<p>Organise the shipment information and supporting documents required by the party preparing the customs declaration. Coordinate queries concerning product descriptions, quantities, values, origin and proposed tariff classification.</p>'
          . '<p>The declaration is submitted by the importer or its duly authorised customs broker, as applicable. The quotation identifies the submitting party and distinguishes Motaded’s coordination work from the broker’s declaration services.</p>',
      ],
      [
        'title' => 'Product Requirements Coordination',
        'body' => '<p>Identify product-related matters requiring further review and coordinate the agreed supporting documentation. Where relevant, this may involve conformity records, product registrations or permits.</p>'
          . '<p>Applications for product registration, conformity assessment or permits are included only where specified in the scope of work.</p>',
      ],
      [
        'title' => 'Clearance and Release Follow-up',
        'body' => '<p>Track available clearance updates and communicate requests for documents, clarification or client action. Coordinate the agreed responses with the broker and relevant providers, and report the documented release status.</p>'
          . '<p>Transport, storage and delivery arrangements are defined separately where required.</p>',
      ],
    ],
    'tabs'
  );
}

function _motaded_customs_cargo(): Paragraph {
  return _motaded_customs_create_cards(
    'Cargo Type and Product Requirements',
    '<p>Clearance preparation depends on the goods and the proposed customs procedure. Product descriptions, intended use and tariff classification help identify whether additional conformity documents, registrations or permits need to be considered.</p>'
    . '<p>The table below outlines the initial review for imports into Saudi Arabia. Export consignments are assessed separately against applicable Saudi export controls and destination-country requirements.</p>',
    [
      [
        'title' => 'General commercial goods',
        'subtitle' => 'Accurate descriptions, quantities, values, origin and consistency between shipment documents; applicable conformity requirements',
        'body' => '<p>Coordination of <a href="/platforms/saber">SABER</a> documentation where applicable. General goods should not be assumed to be exempt from product requirements.</p>',
      ],
      [
        'title' => 'Food products',
        'subtitle' => 'Product category, importer information, labelling and available certificates relevant to the consignment',
        'body' => '<p>Coordination of applicable <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> food documentation and clearance requirements.</p>',
      ],
      [
        'title' => 'Medicines and pharmaceutical products',
        'subtitle' => 'Product and establishment records, available authorisations and consignment documentation',
        'body' => '<p><a href="/services/medicine-services-saudi-arabia">Pharmaceutical regulatory and import support</a> where required.</p>',
      ],
      [
        'title' => 'Medical devices and supplies',
        'subtitle' => 'Device identification, intended use, available authorisation records and importer documentation',
        'body' => '<p><a href="/services/medical-device-services-saudi-arabia">Medical-device regulatory support</a> and coordination of applicable <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> shipment requirements.</p>',
      ],
      [
        'title' => 'Cosmetics and personal-care products',
        'subtitle' => 'Product identity, available listing records, manufacturer information and shipment documentation',
        'body' => '<p><a href="/services/cosmetics-services-saudi-arabia">Cosmetics listing and documentation support</a> where required.</p>',
      ],
      [
        'title' => 'Goods subject to specific controls',
        'subtitle' => 'Tariff classification, product characteristics, intended use and any restrictions or permit requirements',
        'body' => '<p>Coordination with the relevant authority or specialist provider within the agreed scope.</p>',
      ],
      [
        'title' => '',
        'body' => '<p>The consignment-specific review establishes which documents and procedures apply. Product registration, conformity assessment and permit applications are separate work items unless included in the quotation.</p>',
      ],
    ],
    'matrix',
    ['Cargo category', 'What needs to be reviewed', 'Additional product support']
  );
}

function _motaded_customs_process(): Paragraph {
  return _motaded_customs_create_cards(
    'Customs clearance procedure',
    '<p>Customs clearance in Saudi Arabia proceeds in the stages below. Duration depends on document completeness, cargo category and any inspection appointments. A written indication of timing is provided with the quotation after the consignment details have been reviewed.</p>',
    [
      [
        'title' => 'Submission of consignment details',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Company details, trade direction, cargo category and available commercial documents. A complete file is not required at this stage.',
          'The submitted information is reviewed. Applicable documents, approvals and actions are confirmed in writing, and a scope of work is proposed.',
          'Written service quotation for acceptance.'
        ),
      ],
      [
        'title' => 'Confirmation of scope and preparation of documents',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Acceptance of the written quotation, together with the commercial documents already held (invoice, packing list, bill of lading or air waybill, Commercial Register data, and any SABER or SFDA evidence already available).',
          'The agreed scope is confirmed. Remaining documents or certificates required before filing are listed in writing.',
          'Documents ready for the declaration, or a written list of outstanding items.'
        ),
      ],
      [
        'title' => 'Declaration and required checks',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Confirmation to file, and any clarifications requested by the authority.',
          'The declaration is submitted on <a href="/platforms/fasah">FASAH</a>, followed with <a href="/platforms/zatca">ZATCA</a>, and <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or <a href="/platforms/saber">SABER</a> inspection is coordinated where required.',
          'Any official charges issued, and the release decision.'
        ),
      ],
      [
        'title' => 'Payment, release and handover',
        'format' => 'full_html',
        'body' => _motaded_customs_stage_body(
          'Payment of official duty, tax and any inspection fees through the authority’s channels; nomination of the transporter.',
          'Payment of official charges is confirmed; the authority’s release decision is followed; the consignment is then handed to the nominated transporter.',
          'Completion confirmed to the named contact.'
        ),
      ],
    ],
    'stack'
  );
}

function _motaded_customs_documents(): Paragraph {
  return _motaded_customs_create_cards(
    'Documents required for customs clearance',
    '<p>The documents listed below are typically required for customs clearance in Saudi Arabia. Available documents are reviewed and any outstanding items are confirmed in writing.</p>',
    [
      [
        'title' => 'Documents for customs clearance',
        'body' => '<ul><li>Commercial invoice</li><li>Packing list</li><li>Transport document (bill of lading or air waybill)</li><li>Company registration details</li><li>Product certificates and permits, where required — <a href="/platforms/saber">SABER</a> and <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a>. These depend on the cargo.</li></ul>',
      ],
    ],
    'lists'
  );
}

function _motaded_customs_fees(): Paragraph {
  return _motaded_customs_create_cards(
    'Fees and quotation',
    '<p>A written quotation is issued for each consignment. The service fee is agreed before work begins and reflects the cargo, the shipment route and the clearance work required.</p>',
    [
      [
        'title' => 'Motaded service fee',
        'body' => '<ul><li>Agreed before work begins</li><li>The quotation states what is included and what is paid separately</li></ul>',
      ],
      [
        'title' => 'Additional charges',
        'body' => '<ul><li>Duty and taxes, if they apply — <a href="/platforms/zatca">ZATCA</a></li><li>Inspections and permits, if required</li><li>Terminal and warehouse charges, if they arise</li></ul>',
        'uri' => 'https://zatca.gov.sa/en/RulesRegulations/Customs/Pages/default.aspx',
        'link_title' => 'ZATCA customs regulations',
      ],
    ],
    'fees'
  );
}

function _motaded_customs_working(): Paragraph {
  return _motaded_customs_create_cards(
    'Working with Motaded',
    '<p>Riyadh-based consultancy, established 2017. Further information is available on <a href="/about-us">About Motaded</a>.</p>',
    [
      [
        'title' => 'One point of contact',
        'body' => '<p>A dedicated coordinator is assigned for the documents, the customs declaration and consignment status.</p>',
      ],
      [
        'title' => 'Clear scope and fees',
        'body' => '<p>The work and the service fee are agreed in writing before the clearance engagement begins.</p>',
      ],
      [
        'title' => 'Updates at key stages',
        'body' => '<p>Status is reported at the document, inspection, payment and release stages.</p>',
      ],
      [
        'title' => 'Arabic and English',
        'body' => '<p>Documents and status updates are provided in Arabic and English.</p>',
      ],
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

function _motaded_customs_related(): Paragraph {
  return _motaded_customs_create_cards(
    'Related Motaded pages',
    '<p>Official platforms and related Motaded pages for customs clearance in Saudi Arabia.</p>',
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
        'body' => '<p>When the consignment uses a port not yet registered for that importer.</p>',
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
    'related'
  );
}

function _motaded_customs_assessment_form(): Paragraph {
  $form = Paragraph::create([
    'type' => 'webform',
    'langcode' => 'en',
    'field_body' => [
      'value' => '<h2>Request a Consultation</h2>',
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

function _motaded_customs_mid_cta(): Paragraph {
  return _motaded_customs_create_cards(
    'Request a written consultation',
    '<p>Company and consignment details are reviewed in consultation. Applicable customs clearance requirements and the service fee are confirmed in writing.</p>',
    [
      [
        'title' => 'Request a Consultation',
        'body' => '',
        'uri' => 'internal:#assessment',
        'link_title' => 'Request a Consultation',
      ],
    ],
    'invite'
  );
}

function _motaded_customs_final_cta(): Paragraph {
  return _motaded_customs_create_cards(
    'Contact Our Customs Clearance Team',
    '<p>Further enquiries may be directed to the customs clearance team by WhatsApp, email or telephone.</p>',
    [
      [
        'title' => 'Request a Consultation',
        'body' => '',
        'uri' => 'internal:#assessment',
        'link_title' => 'Request a Consultation',
      ],
    ],
    'contact'
  );
}

function _motaded_customs_faq_items(): array {
  return [
    [
      'question' => 'How long does customs clearance take in Saudi Arabia?',
      'answer' => '<p>Duration is typically measured in days. It depends on document completeness, cargo category and inspection appointments.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'What happens if clearance documents are incomplete?',
      'answer' => '<p>Outstanding documents and the next steps are identified in writing. A consultation may still be issued on the documents already supplied.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Can customs clearance proceed after the goods have arrived?',
      'answer' => '<p>Assistance remains available after arrival. The entry point, available documents and arrival status should be stated in the consignment details on the form. Official inspection fees are paid to the examining authority. Storage, terminal or warehouse charges are billed by those providers.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'When are additional inspections required?',
      'answer' => '<p>When the cargo category falls under <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> or another examining body, or when <a href="/platforms/zatca">ZATCA</a> or <a href="/platforms/saber">SABER</a> procedures require physical or documentary examination. The appointment is coordinated by Motaded.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'What charges are paid, and to whom?',
      'answer' => '<p>The Motaded service fee is paid to Motaded against the accepted quotation. Customs duty, import VAT and official inspection fees are paid through the official channels. Storage or terminal charges are paid to the relevant provider. See also <a href="/platforms/zatca">ZATCA</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Does the service cover both import and export?',
      'answer' => '<p>Import and export are both supported. The trade direction should be selected on the form. The documents and <a href="/platforms/fasah">FASAH</a> messages differ; clearance requirements are confirmed during consultation. Importers who are not yet on FASAH may require <a href="/services/electronic-registration-importers-and-exporters">electronic registration</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'Is a Commercial Register required for FASAH filing?',
      'answer' => '<p>A <a href="/platforms/fasah">FASAH</a> declaration must match a registered importer. If the company is not yet registered, the consignment may be reviewed and, separately, entity procedures may be advised through the <a href="/platforms/saudi-business-center-meras">Saudi Business Center</a>.</p>',
      'answer_format' => 'basic_html',
    ],
    [
      'question' => 'How does SABER relate to the customs declaration?',
      'answer' => '<p><a href="/platforms/saber">SABER</a> addresses product conformity. <a href="/platforms/fasah">FASAH</a> and <a href="/platforms/zatca">ZATCA</a> address the customs declaration. Both may be required. A conformity certificate does not replace the declaration. The English <a href="/documents/customs-law-english">Customs Law</a> is available in the Motaded library.</p>',
      'answer_format' => 'basic_html',
    ],
  ];
}
