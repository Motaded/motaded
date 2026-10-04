<?php

/**
 * @file
 * English content for the three SFDA service landings.
 */

declare(strict_types=1);

$sfda = 'internal:/platforms/saudi-food-and-drug-authority-sfda';
$customs = 'internal:/services/customs-clearance-saudi-arabia';
$fasah = 'internal:/platforms/fasah';
$saber = 'internal:/platforms/saber';
$zatca = 'internal:/platforms/zatca';
$about = 'internal:/about-us';
$devices = 'internal:/services/medical-device-services-saudi-arabia';
$cosmetics = 'internal:/services/cosmetics-services-saudi-arabia';
$pharma_law = 'internal:/documents/pharmaceutical-and-herbal-establishments-law';
$sfda_href = '/platforms/saudi-food-and-drug-authority-sfda';
$fasah_href = '/platforms/fasah';
$saber_href = '/platforms/saber';
$zatca_href = '/platforms/zatca';
$customs_href = '/services/customs-clearance-saudi-arabia';
$devices_href = '/services/medical-device-services-saudi-arabia';
$cosmetics_href = '/services/cosmetics-services-saudi-arabia';
$medicine_href = '/services/medicine-services-saudi-arabia';
$pharma_law_href = '/documents/pharmaceutical-and-herbal-establishments-law';
$devices_law_href = '/documents/law-medical-devices-and-supplies';
$mdma_href = '/services/sfda-medical-device-registration-mdma';
$ar_href = '/services/sfda-authorized-representative-service';
$cosmetics_listing_href = '/services/sfda-cosmetics-product-registration';
$cosmetics_licence_href = '/services/sfda-cosmetics-establishment-license';
$about_href = '/about-us';

return [
  'medicine' => [
    'alias' => '/services/medicine-services-saudi-arabia',
    'title' => 'Pharmaceutical Regulatory Services in Saudi Arabia',
    'meta_title' => 'Pharmaceutical Regulatory Services in Saudi Arabia | Motaded',
    'meta_description' => 'SFDA drug registration support, pharmaceutical establishment licensing and import coordination in Saudi Arabia. Motaded prepares the file and, where authorised, supports submission. SFDA decides.',
    'hero' => [
      'eyebrow' => 'SFDA · Pharmaceutical products',
      'prefix' => 'Pharmaceutical Regulatory Services',
      'accent' => 'in Saudi Arabia',
      'body' => '<p>Motaded provides regulatory support for pharmaceutical products that require <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> review before they are placed on the Saudi market. The work covers drug registration files, pharmaceutical establishment licensing and import coordination with <a href="/platforms/fasah">FASAH</a>, where those services are agreed in writing.</p>',
      'primary' => 'Request a Consultation',
      'secondary' => 'Explore the work',
    ],
    'context' => [
      'title' => 'Medicines in Saudi Arabia',
      'lede' => '<p>Saudi Arabia’s pharmaceutical sector operates under the regulatory oversight of the <a href="' . $sfda_href . '">Saudi Food and Drug Authority (SFDA)</a>. The Authority evaluates the quality, safety and efficacy of pharmaceutical products and oversees drug registration, pricing and post-marketing safety monitoring.</p>',
      'items' => [
        [
          'title' => 'Drug Registration and Evaluation',
          'body' => '<p>The registration process involves the assessment of product documentation and supporting evidence. Requirements vary according to the product category and application type. For human medicinal and biological products, the evaluation includes relevant quality, safety and efficacy data published by <a href="' . $sfda_href . '">SFDA</a>.</p>',
        ],
        [
          'title' => 'Pharmaceutical Establishments',
          'body' => '<p>Product registration and establishment licensing are distinct regulatory matters under the <a href="' . $pharma_law_href . '">pharmaceutical and herbal establishments law</a>. Companies planning pharmaceutical activities should identify the requirements relevant to their business operations alongside those applicable to the products they intend to supply.</p>',
        ],
        [
          'title' => 'Product Safety and Traceability',
          'body' => '<p>Regulatory oversight continues after a medicine enters the market. <a href="' . $sfda_href . '">SFDA</a> activities include post-marketing surveillance, pharmacovigilance and pharmaceutical product traceability through the RASD system.</p>',
        ],
        [
          'title' => 'Official Pharmaceutical Information',
          'body' => '<p><a href="' . $sfda_href . '">SFDA</a> provides drug registration guidance, electronic application services and public lists of drug companies and licensed establishments. These resources support the review of applicable requirements and the verification of regulatory information.</p>',
        ],
      ],
    ],
    'eligibility' => [
      'title' => 'Service Overview and Eligibility',
      'lede' => '<p><a href="' . $about_href . '">Motaded</a> provides pharmaceutical regulatory support for companies preparing to enter the Saudi market or manage existing <a href="' . $sfda_href . '">SFDA</a> applications. Services cover drug registration, pharmaceutical establishment licensing and import coordination, according to the company’s activities, product requirements and application status.</p>',
      'items' => [
        [
          'title' => 'Who the service is for',
          'body' => '<ul><li><strong>Pharmaceutical manufacturers and marketing authorisation holders</strong> preparing product documentation and registration applications for the Saudi market.</li><li><strong>Importers and distributors</strong> requiring support with establishment licensing, product registration or <a href="' . $customs_href . '">pharmaceutical import</a> procedures.</li><li><strong>Companies entering the Saudi market</strong> seeking clarification of the regulatory requirements and documentation relevant to their planned activities.</li></ul>',
        ],
        [
          'title' => 'Scope of support',
          'body' => '<ul><li><strong>New product registration:</strong> review of available documentation and preparation of the agreed application package.</li><li><strong>Establishment licensing:</strong> assistance with identifying applicable requirements under the <a href="' . $pharma_law_href . '">establishments law</a> and preparing licensing documentation.</li><li><strong>Import coordination:</strong> review of product and shipping documentation and coordination of the agreed <a href="' . $fasah_href . '">FASAH</a> and <a href="' . $customs_href . '">customs clearance</a> procedures.</li><li><strong>Existing applications:</strong> review of application records, <a href="' . $sfda_href . '">SFDA</a> correspondence and outstanding requests, with support for the remaining work.</li></ul>',
        ],
        [
          'title' => '',
          'body' => '<p>The applicable requirements and scope of service are determined following an initial review. Application submission and regulatory follow-up are included where agreed and authorised.</p>',
        ],
      ],
    ],
    'catalogue' => [
      'title' => 'Drug Registration, Establishment Licensing and Import Support',
      'lede' => '',
      'note' => '',
      'items' => [
        [
          'title' => 'SFDA Drug Registration Support',
          'body' => '<p>Support with the preparation of pharmaceutical product registration applications for the Saudi market through <a href="' . $sfda_href . '">SFDA</a>.</p><h4>Motaded’s scope of work</h4><ul><li>Review the available product dossier and supporting documentation.</li><li>Identify missing materials and inconsistencies requiring clarification.</li><li>Prepare the agreed application package.</li><li>Submit the application and coordinate responses to <a href="' . $sfda_href . '">SFDA</a> requests where submission and follow-up are included in the engagement.</li></ul><h4>Client responsibilities</h4><p>Provide product and manufacturer documentation, arrange any additional technical evidence and authorise the agreed application activities.</p><h4>Service outcome</h4><p>A prepared application package or, where submission is included, a submitted application with a record of correspondence and outstanding requirements.</p>',
          'media' => 1494,
        ],
        [
          'title' => 'Pharmaceutical Establishment Licensing Support',
          'body' => '<p>Support with licensing documentation for the company’s planned pharmaceutical activities under the <a href="' . $pharma_law_href . '">pharmaceutical and herbal establishments law</a>.</p><h4>Motaded’s scope of work</h4><ul><li>Review the proposed activities and available company documentation.</li><li>Identify the relevant <a href="' . $sfda_href . '">SFDA</a> licensing requirements and outstanding documents.</li><li>Prepare the agreed licensing application.</li><li>Coordinate submission and responses to requests where included and authorised.</li></ul><h4>Client responsibilities</h4><p>Provide company and premises documentation, appoint the required responsible personnel and complete any operational or premises-related requirements.</p><h4>Service outcome</h4><p>A prepared licensing application or, where submission is included, a submitted application with a documented status and remaining actions.</p>',
          'media' => 1495,
        ],
        [
          'title' => 'Pharmaceutical Import Coordination',
          'body' => '<p>Support with the documentation and coordination required for planned pharmaceutical consignments or shipments already in Saudi Arabia, including <a href="' . $fasah_href . '">FASAH</a> and <a href="' . $customs_href . '">customs clearance</a> where those procedures apply.</p><h4>Motaded’s scope of work</h4><ul><li>Review available product, company and shipping documentation.</li><li>Identify outstanding information relevant to the consignment.</li><li>Coordinate the agreed import-related work with the client and relevant service providers.</li><li>Track requests, correspondence and outstanding actions within the agreed scope.</li></ul><h4>Client responsibilities</h4><p>Provide accurate shipment information and supporting documents, coordinate with the supplier and arrange applicable payments and authorisations.</p><h4>Service outcome</h4><p>An organised consignment documentation package and a record of the coordinated procedures, current status and outstanding actions.</p>',
          'media' => 1484,
        ],
      ],
    ],
    'features' => [
      'title' => 'Service Scope and Client Coordination',
      'lede' => '<p>A designated <a href="' . $about_href . '">Motaded</a> coordinator manages document requests, client communications and progress updates throughout the engagement. Before work begins, the written proposal defines the services, expected deliverables, responsibilities, service fees and payment terms, with applicable government and third-party charges identified separately. Progress updates record completed work, outstanding requirements and actions requiring the client’s attention.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'Request a Consultation'],
        ['title' => 'WhatsApp', 'body' => '', 'uri' => 'https://wa.me/966539797197', 'link_title' => 'WhatsApp'],
      ],
    ],
    'process' => [
      'title' => 'Application Preparation and Follow-up',
      'lede' => '<p>The stages below describe registration and licensing support. The applicable stages are confirmed in the written proposal; <a href="' . $customs_href . '">import coordination</a> follows the requirements of the individual consignment.</p>',
      'items' => [
        [
          'title' => 'Initial Review and Scope Confirmation',
          'body' => '<p>Motaded reviews the product or establishment information, available documents and any existing application records. The required work, outstanding materials, responsibilities and deliverables are defined before preparation begins.</p>',
        ],
        [
          'title' => 'Documentation and Application Preparation',
          'body' => '<p>The agreed application package is prepared using the materials supplied by the client. Missing information and inconsistencies are referred to the client or manufacturer for clarification before submission.</p>',
        ],
        [
          'title' => 'Authorised Submission',
          'body' => '<p>Where submission is included in the engagement, Motaded submits the application through the applicable channel following client authorisation. The submission reference and available confirmation are recorded and communicated to the client.</p>',
        ],
        [
          'title' => 'Regulatory Follow-up and Delivery of Records',
          'body' => '<p>Where follow-up is included, Motaded coordinates responses to regulatory requests and communicates application updates. At completion of the agreed work, the client receives the prepared materials, submission records and correspondence, together with any outstanding actions. Regulatory decisions remain with <a href="' . $sfda_href . '">SFDA</a>.</p>',
        ],
      ],
    ],
    'documents' => [
      'title' => 'Pharmaceutical Product and Company Documentation',
      'lede' => '<p>Motaded reviews the records already held and confirms in writing which items are still required for the agreed application.</p>',
      'items' => [
        ['title' => '', 'body' => '<ul><li>Commercial Register and relevant pharmaceutical establishment licences.</li><li>Previous <a href="' . $sfda_href . '">SFDA</a> application references and correspondence, if available.</li><li>Product and manufacturer documentation: existing dossier, manufacturer certificates, composition or specification data, and current labelling.</li><li>Shipping documents and <a href="' . $fasah_href . '">FASAH</a> or <a href="' . $customs_href . '">import</a> records, if the request concerns a consignment.</li></ul>'],
        ['title' => '', 'body' => '<p>A consultation may proceed with an incomplete file. Motaded confirms the outstanding items in writing.</p>'],
      ],
    ],
    'fees' => [
      'title' => 'Service Fees and Regulatory Charges',
      'lede' => '<p>The quotation reflects the agreed service — drug registration, establishment licensing or import coordination — the condition of the product documentation and the status of any existing SFDA application.</p>',
      'items' => [
        [
          'title' => 'Motaded service fee',
          'body' => '<ul><li>Charged for the agreed scope of work.</li><li>Depends on the service and the readiness of the documents.</li><li>Confirmed in the written proposal before work begins.</li></ul>',
        ],
        [
          'title' => 'Government and third-party charges',
          'body' => '<ul><li>Paid separately, if they apply.</li><li>Depend on the specific <a href="' . $sfda_href . '">SFDA</a> or <a href="' . $customs_href . '">customs</a> procedure, including <a href="' . $zatca_href . '">ZATCA</a> charges where they arise.</li><li>Known costs at the time of the proposal are listed separately.</li></ul><p>Current government fees are confirmed on the <a href="' . $sfda_href . '">SFDA</a> published schedule.</p>',
          'uri' => 'internal:#assessment',
          'link_title' => 'Request a Quotation',
        ],
      ],
    ],
    'form' => [
      'title' => 'Request a Consultation',
      'body' => '<h2>Request a Consultation</h2>',
    ],
    'faq' => [
      ['question' => 'Are pharmaceutical services the same as medical device or cosmetics support?', 'answer' => '<p>Medicines, <a href="' . $devices_href . '">medical devices</a> and <a href="' . $cosmetics_href . '">cosmetics</a> are processed in different <a href="' . $sfda_href . '">SFDA</a> systems. The landing that matches the product should be used. If classification is unclear, that should be stated on the form.</p>'],
      ['question' => 'Can the SFDA government fee be confirmed in advance?', 'answer' => '<p><a href="' . $sfda_href . '">SFDA</a> publishes the government fee for each application. Motaded confirms the service fee after reviewing the file. The Authority’s published schedule is not replaced.</p>'],
      ['question' => 'Does Motaded decide whether the medicine is registered?', 'answer' => '<p><a href="' . $sfda_href . '">SFDA</a> decides registration, licensing and any inspection. Motaded prepares the file and, where authorised, supports submission and follow-up.</p>'],
      ['question' => 'Can Motaded support a consignment that has already arrived?', 'answer' => '<p>For consignments already in Saudi Arabia, Motaded reviews the available shipping documents, the SFDA records already held and outstanding clearance requirements. Customs clearance may also be required through <a href="/services/customs-clearance-saudi-arabia">customs clearance</a>.</p>'],
      ['question' => 'How is the Motaded service fee determined?', 'answer' => '<p>The fee depends on the product, the completeness of the file and whether the scope includes drug registration, establishment licensing or import coordination. It is confirmed in writing before work begins.</p>'],
    ],
    'related_extra' => [
      ['title' => 'Medical device services', 'body' => '<p>MDMA registration and authorized representative support.</p>', 'uri' => 'internal:/services/medical-device-services-saudi-arabia'],
      ['title' => 'Cosmetics services', 'body' => '<p>eCosma listing and cosmetics establishment licensing.</p>', 'uri' => 'internal:/services/cosmetics-services-saudi-arabia'],
      ['title' => 'SFDA', 'body' => '<p>Official systems and sectors supervised by the Authority.</p>', 'uri' => $sfda],
      ['title' => 'FASAH', 'body' => '<p>Customs declaration messages for imported goods.</p>', 'uri' => $fasah],
      ['title' => 'ZATCA', 'body' => '<p>Duty, import VAT and the customs assessment on the goods.</p>', 'uri' => $zatca],
      ['title' => 'Pharmaceutical and herbal establishments law', 'body' => '<p>Published text of the establishments law in the Motaded library.</p>', 'uri' => 'internal:/documents/pharmaceutical-and-herbal-establishments-law'],
      ['title' => 'Customs clearance', 'body' => '<p>Declaration and inspection coordination for imported goods.</p>', 'uri' => $customs],
      ['title' => 'About Motaded', 'body' => '<p>How the company works with clients in Saudi Arabia.</p>', 'uri' => $about],
    ],
  ],

  'devices' => [
    'alias' => '/services/medical-device-services-saudi-arabia',
    'title' => 'Medical Devices in Saudi Arabia',
    'meta_title' => 'Medical Devices in Saudi Arabia | Motaded',
    'meta_description' => 'Medical devices in Saudi Arabia: SFDA market authorisation, MDMA application preparation, authorised representative arrangements and import coordination.',
    'hero' => [
      'eyebrow' => 'SFDA · Medical Devices',
      'prefix' => 'Medical Devices',
      'accent' => 'in Saudi Arabia',
      'body' => '<p>Medical devices are subject to <a href="' . $sfda_href . '">Saudi Food and Drug Authority (SFDA)</a> requirements for market authorisation and relevant establishment activities.</p><p><a href="' . $about_href . '">Motaded</a> supports manufacturers and local businesses with <a href="' . $mdma_href . '">Medical Devices Marketing Authorization (MDMA)</a> application preparation, <a href="' . $ar_href . '">authorised representative</a> arrangements and <a href="' . $customs_href . '">import coordination</a>.</p>',
      'primary' => 'Request a Consultation',
      'secondary' => 'Explore Regulatory Support',
    ],
    'requirements' => [
      'title' => 'What Determines the Regulatory Requirements?',
      'lede' => '<p>The applicable requirements are assessed using the manufacturer’s intended purpose, the device’s characteristics and the proposed activities in Saudi Arabia. A product name or photograph alone is insufficient to establish the appropriate regulatory approach.</p><p>The initial review considers:</p><ul><li><strong>Intended purpose:</strong> the medical function described by the manufacturer, intended users and conditions of use.</li><li><strong>Device characteristics and risk classification:</strong> the technology, mode of operation and characteristics relevant to the applicable <a href="' . $sfda_href . '">SFDA</a> classification rules.</li><li><strong>Manufacturer and local arrangements:</strong> the legal manufacturer’s details and the roles of the proposed <a href="' . $ar_href . '">authorised representative</a>, importer or distributor.</li><li><strong>Existing regulatory status:</strong> available Saudi authorisations, previous applications and any changes to the device or its documentation.</li><li><strong>Planned activity:</strong> initial market entry, continuation of an application, changes to an existing authorisation or coordination of a particular shipment.</li></ul><p>The manufacturer is responsible for determining the device’s classification under <a href="' . $sfda_href . '">SFDA</a> rules. <a href="' . $about_href . '">Motaded</a>’s review identifies the documentation and support required for the proposed engagement.</p>',
      'items' => [],
    ],
    'support' => [
      'title' => 'Which Support Does Your Company Need?',
      'lede' => '<p>The scope of support depends on the company’s role, the device’s regulatory status and the work already completed. The situations below provide a starting point for defining the engagement.</p>',
      'headers' => ['Company situation', 'Relevant support', "Motaded’s scope of work"],
      'items' => [
        [
          'title' => 'An overseas manufacturer is preparing to enter the Saudi market',
          'subtitle' => 'MDMA application preparation and authorised representative arrangements',
          'body' => '<p>Review available manufacturer and device documentation, identify outstanding materials and assist with the agreed <a href="' . $mdma_href . '">application</a> and <a href="' . $ar_href . '">representation</a> arrangements.</p>',
        ],
        [
          'title' => 'A Saudi importer or distributor is preparing to supply medical devices',
          'subtitle' => 'Regulatory documentation review and import coordination',
          'body' => '<p>Review available product authorisations, establishment documentation and shipment information; identify outstanding requirements and coordinate the agreed work.</p>',
        ],
        [
          'title' => 'A company has an existing or incomplete application',
          'subtitle' => 'Application review and regulatory follow-up',
          'body' => '<p>Review submitted materials, <a href="' . $sfda_href . '">SFDA</a> correspondence and outstanding requests; prepare the agreed additions and coordinate authorised responses.</p>',
        ],
        [
          'title' => 'A company is planning changes to an authorised device or its documentation',
          'subtitle' => 'Review of existing authorisation and proposed changes',
          'body' => '<p>Compare the proposed changes with available application records and identify the documentation and procedural support required.</p>',
        ],
        [
          'title' => '',
          'body' => '<p>Application submission and follow-up are included where agreed and authorised. Support with <a href="' . $ar_href . '">authorised representative</a> arrangements does not itself constitute <a href="' . $about_href . '">Motaded</a>’s appointment as the manufacturer’s authorised representative.</p>',
        ],
      ],
    ],
    'mdma' => [
      'title' => 'MDMA Application Support',
      'lede' => '<p><a href="' . $about_href . '">Motaded</a> assists with the preparation and coordination of <a href="' . $mdma_href . '">Medical Devices Marketing Authorization (MDMA)</a> applications. The engagement covers the agreed documentation review, application preparation and, where authorised, submission and regulatory follow-up.</p>',
      'items' => [
        [
          'title' => 'Review of Device and Manufacturer Information',
          'body' => '<p>The initial review covers the device description, intended purpose, models and accessories, manufacturer details and available regulatory records. Existing applications and <a href="' . $sfda_href . '">SFDA</a> correspondence are reviewed where relevant.</p><p>The review identifies missing documents, inconsistent information and matters requiring clarification from the manufacturer.</p>',
        ],
        [
          'title' => 'Preparation of the Application Package',
          'body' => '<p>Available technical and administrative materials are organised for the agreed application. Device names, model references, manufacturer details and supporting documents are checked for consistency.</p><p>Outstanding technical evidence is requested from the manufacturer. Documentation review does not replace product testing, clinical evaluation or the manufacturer’s responsibility for the underlying evidence.</p>',
        ],
        [
          'title' => 'Submission and Regulatory Follow-up',
          'body' => '<p>Where included in the engagement, the application is submitted through the applicable <a href="' . $sfda_href . '">SFDA</a> channel following the necessary authorisation. Submission references and available confirmations are recorded.</p><p>During review, Motaded coordinates requests for additional information, obtains client or manufacturer input and prepares the agreed responses. Technical questions requiring manufacturer confirmation are referred to the responsible party.</p>',
        ],
        [
          'title' => 'Application Records and Deliverables',
          'body' => '<p>The client receives the agreed application materials, available submission confirmations and a record of regulatory correspondence. Progress updates identify completed work, outstanding requests and actions requiring client or manufacturer attention.</p><p>The engagement supports application preparation and follow-up. Assessment and the decision on marketing authorisation remain with <a href="' . $sfda_href . '">SFDA</a>.</p>',
        ],
      ],
    ],
    'representation' => [
      'title' => 'Authorised Representation and Import Coordination',
      'lede' => '<p>Authorised representation and import coordination are distinct services. They may be requested together or separately, according to the company’s role and the status of the device or consignment.</p>',
      'items' => [
        [
          'title' => 'Authorised Representative Arrangements',
          'body' => '<p>For overseas manufacturers, <a href="' . $about_href . '">Motaded</a> reviews local representation arrangements and prepares the agreed mandate documentation.</p><p>The engagement identifies the appointed <a href="' . $ar_href . '">representative</a> and each party’s responsibilities. It does not appoint Motaded as authorised representative; that appointment is established separately.</p>',
        ],
        [
          'title' => 'Medical Device Import Coordination',
          'body' => '<p>For planned or arrived consignments, Motaded reviews device authorisation records, importer documents and shipping information, then coordinates the agreed actions.</p><p><a href="' . $customs_href . '">Customs</a> declarations, freight, storage and third-party charges are included only where specified. Coordination does not guarantee <a href="' . $fasah_href . '">clearance</a> or release.</p>',
        ],
      ],
    ],
    'features' => [
      'title' => 'Request a Consultation',
      'lede' => '<p><a href="' . $about_href . '">Motaded</a> reviews the company’s role, the available device documentation and the status of any existing application or consignment. The written proposal defines the agreed support, deliverables and fee.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'Request a Consultation'],
        ['title' => 'WhatsApp', 'body' => '', 'uri' => 'https://wa.me/966539797197', 'link_title' => 'WhatsApp'],
      ],
    ],
    'process' => [
      'title' => 'From Document Review to Application Outcome',
      'lede' => '<p>Medical device application support involves coordinated work between the applicant, manufacturer and relevant local parties. The stages below describe an <a href="' . $mdma_href . '">MDMA</a> engagement; submission and follow-up are included where agreed and authorised.</p>',
      'items' => [
        [
          'title' => 'Initial Review and Scope Confirmation',
          'subtitle' => 'Motaded and the client',
          'body' => '<p>The client provides device details, manufacturer information and available application records. <a href="' . $about_href . '">Motaded</a> reviews the materials, identifies outstanding information and defines the proposed work, deliverables and responsibilities.</p>',
        ],
        [
          'title' => 'Technical Documentation and Clarifications',
          'subtitle' => 'Manufacturer and client, coordinated by Motaded',
          'body' => '<p>The manufacturer supplies the relevant technical evidence and confirms device information. Motaded organises the documentation, checks consistency and records questions requiring clarification. The client coordinates access to manufacturer materials and relevant local-party records.</p>',
        ],
        [
          'title' => 'Application Preparation and Authorised Submission',
          'subtitle' => 'Motaded within the agreed scope; the authorised applicant confirms submission',
          'body' => '<p>Motaded prepares the agreed application package. The client and manufacturer confirm the accuracy of their supplied information and final document versions. Where submission is included, the application is submitted through the applicable channel under the appropriate authorisation, and available submission references are recorded.</p>',
        ],
        [
          'title' => 'Regulatory Review and Responses',
          'subtitle' => 'SFDA for assessment; applicant and manufacturer for responses, coordinated by Motaded',
          'badge' => 'SFDA assessment',
          'body' => '<p><a href="' . $sfda_href . '">SFDA</a> assesses the submitted application and may request additional information or clarification. Where follow-up is included, Motaded tracks requests and coordinates the agreed responses. Technical explanations and additional evidence are provided or confirmed by the manufacturer.</p>',
        ],
        [
          'title' => 'Decision, Records and Outstanding Actions',
          'subtitle' => 'SFDA for the regulatory decision; Motaded for the agreed handover',
          'badge' => 'SFDA decision',
          'body' => '<p>The regulatory decision is issued by <a href="' . $sfda_href . '">SFDA</a>. Motaded communicates the available outcome and provides the agreed application records and correspondence, including authorisation documents where issued.</p><p>If further action is required, the handover identifies outstanding matters and the responsible parties. Where the engagement ends before a regulatory decision, the client receives the current application status and records of the completed work.</p>',
        ],
      ],
    ],
    'documents' => [
      'title' => 'Technical Documentation Readiness',
      'lede' => '<p>Application preparation begins with an inventory of available technical and administrative materials. The review records what has been supplied, identifies gaps and assigns requests to the client, manufacturer or appointed <a href="' . $ar_href . '">representative</a>.</p><p>The groups below support the initial documentation review. The final document list is established for the device and proposed procedure.</p>',
      'headers' => ['Document group', 'Materials for the initial review', 'Where gaps are addressed'],
      'items' => [
        [
          'title' => 'Device identification and intended purpose',
          'subtitle' => 'Product description, intended purpose, model list, configurations and relevant accessories',
          'body' => '<p>The manufacturer confirms the device information and supplies missing specifications.</p>',
        ],
        [
          'title' => 'Manufacturer and local party information',
          'subtitle' => 'Legal manufacturer details, relevant company records and available representative or importer information',
          'body' => '<p>The manufacturer and relevant local parties provide or confirm their records.</p>',
        ],
        [
          'title' => 'Classification information',
          'subtitle' => 'Proposed risk classification and the manufacturer’s supporting rationale',
          'body' => '<p>The manufacturer supplies or clarifies the classification rationale.</p>',
        ],
        [
          'title' => 'Quality and conformity documentation',
          'subtitle' => 'Available quality management certificates, declarations of conformity and related supporting records',
          'body' => '<p>The manufacturer supplies current documents and clarifies their scope.</p>',
        ],
        [
          'title' => 'Safety and performance evidence',
          'subtitle' => 'Available risk management records, test reports, performance evidence and clinical documentation, as applicable',
          'body' => '<p>The manufacturer provides the relevant evidence or arranges additional technical work.</p>',
        ],
        [
          'title' => 'Labelling and instructions for use',
          'subtitle' => 'Current labels, packaging artwork and instructions for use for the proposed models',
          'body' => '<p>The manufacturer supplies controlled versions and confirms consistency with the device information.</p>',
        ],
        [
          'title' => 'Existing regulatory records',
          'subtitle' => 'Previous MDMA records, application references, SFDA correspondence and outstanding requests',
          'body' => '<p>The client or appointed <a href="' . $ar_href . '">representative</a> provides the available <a href="' . $mdma_href . '">application</a> history.</p>',
        ],
        [
          'title' => 'Documentation Review Outcome',
          'body' => '<p>The review produces a documented list of available materials, missing items and required clarifications. Each outstanding item identifies the responsible party and the action needed before the agreed application work can proceed.</p>',
        ],
      ],
    ],
    'fees' => [
      'title' => 'Fees and quotation',
      'lede' => '<p>The quotation takes into account the required service, the product documentation and the status of any existing application.</p>',
      'items' => [
        [
          'title' => 'Motaded service fee',
          'body' => '<ul><li>Charged for the agreed scope of work.</li><li>Depends on the service and how ready the documents are.</li><li>Confirmed in the written proposal before work begins.</li></ul>',
        ],
        [
          'title' => 'Government and third-party charges',
          'body' => '<ul><li>Paid separately, if they apply.</li><li>Depend on the specific procedure.</li><li>Known costs at the time of the proposal are listed separately.</li></ul><p>Confirm current government fees on the <a href="' . $sfda_href . '">Authority</a>’s published schedule.</p>',
          'uri' => 'internal:#assessment',
          'link_title' => 'Request a Quotation',
        ],
      ],
    ],
    'form' => [
      'title' => 'Let’s discuss your medical device file',
      'body' => '<h2>Let’s discuss your medical device file</h2><p>Company details, the device name, intended use and any class information already issued are enough for the first consultation.</p><p><a href="' . $about_href . '">Motaded</a> replies with the required work and a service quotation.</p>',
    ],
    'faq' => [
      ['question' => 'Is an Authorized Representative always required?', 'answer' => '<p><a href="' . $sfda_href . '">SFDA</a> requires a licensed Saudi <a href="' . $ar_href . '">Authorized Representative</a> for foreign manufacturers. Confirm the current rule on SFDA and on the <a href="' . $ar_href . '">Authorized Representative service page</a>.</p>'],
      ['question' => 'What is MDMA?', 'answer' => '<p><a href="' . $mdma_href . '">MDMA</a> is the Medical Device Marketing Authorization issued by <a href="' . $sfda_href . '">SFDA</a> before a device is stored or sold in the Kingdom. Details are on the <a href="' . $mdma_href . '">MDMA service page</a>.</p>'],
      ['question' => 'Can Motaded classify the device for SFDA?', 'answer' => '<p><a href="' . $sfda_href . '">SFDA</a> decides the class. We review the information you provide and prepare the file on that basis. We do not replace the Authority’s classification.</p>'],
      ['question' => 'Do you handle import as well as MDMA?', 'answer' => '<p>Yes, when it is included in the scope. Import work follows the <a href="' . $customs_href . '">customs clearance</a> process and any <a href="' . $sfda_href . '">SFDA</a> or <a href="' . $saber_href . '">SABER</a> evidence that applies.</p>'],
      ['question' => 'How is Motaded’s price determined?', 'answer' => '<p>The fee depends on the applications included, the completeness of the file and whether <a href="' . $ar_href . '">representation</a> is required. We confirm it in writing before work begins.</p>'],
    ],
    'related_extra' => [
      ['title' => 'Medicine services', 'body' => '<p>SFDA medicine files and import coordination.</p>', 'uri' => 'internal:/services/medicine-services-saudi-arabia'],
      ['title' => 'Cosmetics services', 'body' => '<p>eCosma listing and cosmetics establishment licensing.</p>', 'uri' => 'internal:/services/cosmetics-services-saudi-arabia'],
      ['title' => 'SFDA', 'body' => '<p>Official systems and sectors supervised by the Authority.</p>', 'uri' => $sfda],
      ['title' => 'FASAH', 'body' => '<p>Customs declaration messages for imported goods.</p>', 'uri' => $fasah],
      ['title' => 'SABER', 'body' => '<p>Conformity certificates when a technical regulation also applies.</p>', 'uri' => $saber],
      ['title' => 'ZATCA', 'body' => '<p>Duty, import VAT and the customs assessment on the goods.</p>', 'uri' => $zatca],
      ['title' => 'Law of Medical Devices and Supplies', 'body' => '<p>Official text on registration, distribution and related duties for devices and supplies.</p>', 'uri' => 'internal:/documents/law-medical-devices-and-supplies'],
      ['title' => 'Customs clearance', 'body' => '<p>Declaration and inspection coordination for imported goods.</p>', 'uri' => $customs],
      ['title' => 'About Motaded', 'body' => '<p>How the company works with clients in Saudi Arabia.</p>', 'uri' => $about],
    ],
  ],

  'cosmetics' => [
    'alias' => '/services/cosmetics-services-saudi-arabia',
    'title' => 'Cosmetics Services in Saudi Arabia',
    'meta_title' => 'Cosmetics Services in Saudi Arabia | Motaded',
    'meta_description' => 'Cosmetic product listing support in Saudi Arabia: product documentation review, labelling review and listing submission for brands, manufacturers and importers.',
    'hero' => [
      'eyebrow' => 'SFDA · Cosmetics',
      'prefix' => 'Cosmetics Services',
      'accent' => 'in Saudi Arabia',
      'body' => '<p>Cosmetic product listing support, product documentation review and labelling review for brands, manufacturers and importers entering the Saudi market.</p><p><a href="' . $about_href . '">Motaded</a> assists with organising product information, identifying documentation gaps and preparing the agreed listing submission.</p>',
      'primary' => 'Request a Consultation',
      'secondary' => 'Explore Listing Support',
    ],
    'briefing' => [
      'title' => 'Cosmetics and Personal-Care Products in Saudi Arabia',
      'lede' => '<p>Cosmetic products are generally intended for purposes such as cleansing, perfuming, changing appearance, correcting body odours or maintaining external parts of the body in good condition.</p><p>Cosmetics and personal-care products in Saudi Arabia fall under the oversight of the <a href="' . $sfda_href . '">Saudi Food and Drug Authority (SFDA)</a>. The regulatory framework addresses product safety, ingredients, claims, labelling and notification requirements.</p>',
      'items' => [
        [
          'title' => '',
          'body' => '',
          'media' => 1495,
        ],
      ],
    ],
    'composition' => [
      'title' => 'Product Composition, Claims and Labelling',
      'lede' => '<p>Whether a product falls within this category depends on its intended use, composition and presentation, including claims on packaging and in promotional materials. Products presented with <a href="' . $medicine_href . '">medicinal or therapeutic claims</a> may require a different regulatory assessment. A commercial description as skincare, beauty or personal care is insufficient on its own to establish the regulatory category.</p>',
      'items' => [
        [
          'title' => 'Product Composition',
          'body' => '<p>Ingredient identities, concentrations where relevant, restrictions and conditions of use are reviewed against the applicable cosmetic ingredient requirements.</p>',
        ],
        [
          'title' => 'Intended Purpose and Product Claims',
          'body' => '<p>Claims on packaging, websites and advertising should correspond to the product’s cosmetic purpose and be supported by relevant evidence.</p>',
        ],
        [
          'title' => 'Labelling and Packaging',
          'body' => '<p>The container, outer packaging and accompanying information are reviewed against the applicable Saudi labelling requirements, including product identification, ingredient listing, directions, warnings and language requirements.</p>',
        ],
      ],
    ],
    'features' => [
      'title' => 'Request a Consultation',
      'lede' => '<p><a href="' . $about_href . '">Motaded</a> reviews the product information, listing status and available documentation. The written quotation identifies the agreed work, responsibilities and service fees before work begins.</p>',
      'items' => [
        ['title' => 'Request a Consultation', 'body' => '', 'uri' => 'internal:#assessment', 'link_title' => 'Request a Consultation'],
        ['title' => 'WhatsApp', 'body' => '', 'uri' => 'https://wa.me/966539797197', 'link_title' => 'WhatsApp'],
      ],
    ],
    'support' => [
      'title' => 'Support for Brands, Manufacturers and Importers',
      'lede' => '<p>The situations below identify the relevant direction. The initial review then confirms the products covered and the work to be ordered.</p>',
      'headers' => ['Company situation', 'Recommended direction'],
      'items' => [
        [
          'title' => 'A brand owner is preparing a Saudi market launch',
          'subtitle' => 'Listing readiness for a defined portfolio',
        ],
        [
          'title' => 'A manufacturer is preparing products for listing',
          'subtitle' => 'Documentation review and listing preparation',
        ],
        [
          'title' => 'An importer is adding products to its portfolio',
          'subtitle' => 'Coordination of supplier information',
        ],
        [
          'title' => 'A company has an incomplete or existing submission',
          'subtitle' => 'Review of the current file and follow-up',
        ],
        [
          'title' => '',
          'body' => '<p>The initial review confirms the products covered, outstanding materials and who supplies them.</p>',
        ],
      ],
    ],
    'deliverables' => [
      'title' => 'Service Deliverables',
      'lede' => '<p>The agreed engagement provides documented results for the products covered by the scope of work.</p>',
      'items' => [
        [
          'title' => 'Documentation Review Summary',
          'body' => '<p>A record of missing materials, inconsistencies and clarifications required from the client or manufacturer.</p>',
        ],
        [
          'title' => 'Prepared Product Information',
          'body' => '<p>An organised set of product data and supporting documents prepared for the agreed listing work.</p>',
        ],
        [
          'title' => 'Submission Records and Status',
          'body' => '<p>Where submission is included, available listing references and submission confirmations, together with outstanding requests and required actions.</p>',
        ],
        [
          'title' => '',
          'body' => '<p>For an initial consultation, provide the brand name, product type, approximate number of products and current listing status. A complete documentation package is not required to make an enquiry.</p>',
        ],
        [
          'title' => 'Request a Cosmetics Consultation',
          'body' => '',
          'uri' => 'internal:#assessment',
          'link_title' => 'Request a Cosmetics Consultation',
        ],
      ],
    ],
    'listing' => [
      'title' => 'Cosmetic Product Listing Support',
      'lede' => '<p><a href="' . $about_href . '">Motaded</a> reviews product information and labelling, then prepares the agreed <a href="' . $cosmetics_listing_href . '">listing</a> file through the applicable <a href="' . $sfda_href . '">SFDA</a> channel. Where authorised, Motaded submits the file and returns the available references and records.</p>',
      'items' => [
        [
          'title' => 'Product Information Review',
          'body' => '<p>Motaded checks product names, manufacturer details and composition records. The client receives a list of missing or inconsistent information.</p>',
        ],
        [
          'title' => 'Documentation and Labelling Review',
          'body' => '<p>Motaded reviews labels and artwork against the proposed listing data. The client receives the revisions required before submission.</p>',
        ],
        [
          'title' => 'Listing Preparation and Submission',
          'body' => '<p>Motaded organises the agreed file and, where authorised, submits it. The client receives the submission references and status.</p>',
        ],
        [
          'title' => 'Follow-up and Delivery of Records',
          'body' => '<p>Motaded coordinates responses to outstanding requests. The client receives the product package, available records and remaining actions.</p>',
        ],
      ],
    ],
    'needs' => [
      'title' => 'Who this support is for',
      'lede' => '<p>Use this page if you need support with a cosmetic or personal-care product under SFDA.</p>',
      'items' => [
        [
          'title' => 'Brand owners and manufacturers',
          'body' => '<p>You need the product documents ready before a cosmetic can be listed on the Saudi market.</p><p>Motaded helps prepare that file so you can take the next step toward supply in the Kingdom.</p>',
        ],
        [
          'title' => 'Importers and warehouses',
          'body' => '<p>You need the company, the product and the planned import to meet the applicable requirements.</p><p>Motaded reviews those requirements and prepares the work that follows.</p>',
        ],
        [
          'title' => 'Companies new to the Saudi market',
          'body' => '<p>You need a clear list of the actions and documents required before shipment or sale.</p><p>Motaded identifies the applicable steps and what to prepare first.</p>',
        ],
        [
          'title' => 'Medicine services',
          'body' => '',
          'uri' => 'internal:/services/medicine-services-saudi-arabia',
          'link_title' => 'Medicine services',
        ],
        [
          'title' => 'Medical device services',
          'body' => '',
          'uri' => 'internal:/services/medical-device-services-saudi-arabia',
          'link_title' => 'Medical device services',
        ],
        [
          'title' => 'Not sure which product category applies? Request a Consultation',
          'body' => '',
          'uri' => 'internal:#assessment',
          'link_title' => 'Not sure which product category applies? Request a Consultation',
        ],
      ],
    ],
    'catalogue' => [
      'title' => 'How Motaded can help',
      'lede' => '',
      'note' => '',
      'items' => [
        [
          'title' => 'Product listing',
          'body' => '<p>Motaded reviews your product information, lists the gaps and prepares the eCosma listing. Where filing is included, we submit it through <a href="/platforms/saudi-food-and-drug-authority-sfda">SFDA</a> and coordinate replies.</p>',
          'uri' => 'entity:node/976',
          'link_title' => 'Explore service',
          'media' => 1478,
        ],
        [
          'title' => 'Establishment licensing',
          'body' => '<p>Motaded identifies the SFDA establishment requirements and prepares the GHAD file from the records you hold. Filing can be included in the agreed scope.</p>',
          'uri' => 'entity:node/977',
          'link_title' => 'Explore service',
          'media' => 1495,
        ],
        [
          'title' => 'Import coordination',
          'body' => '<p>Motaded checks the SFDA evidence against the shipment and coordinates the <a href="/platforms/fasah">FASAH</a> declaration. Arrival does not guarantee release.</p>',
          'uri' => $customs,
          'link_title' => 'Customs clearance',
          'media' => 1484,
        ],
      ],
    ],
    'situations' => [
      'title' => 'When to contact us',
      'lede' => '<p>You do not need the exact eCosma or GHAD form name before writing to us.</p>',
      'items' => [
        [
          'title' => 'You plan to place a cosmetic on the Saudi market',
          'body' => '<p>You need the next actions identified and to know whether the documents are ready.</p>',
        ],
        [
          'title' => 'The documents are incomplete',
          'body' => '<p>You need to see what is missing and who should supply it.</p>',
        ],
        [
          'title' => 'An import is planned, or the shipment has already arrived',
          'body' => '<p>You need the current status checked and the next possible actions identified. Arrival alone does not close the file.</p>',
        ],
        [
          'title' => 'You need to continue an existing application',
          'body' => '<p>You need Motaded to review the file as it stands and the work still required.</p>',
        ],
        [
          'title' => 'Request a Consultation',
          'body' => '',
          'uri' => 'internal:#assessment',
          'link_title' => 'Request a Consultation',
        ],
      ],
    ],
    'process' => [
      'title' => 'From Product Review to Listing Submission',
      'lede' => '<p>The stages below record responsibilities and outstanding materials from the first review to the agreed listing submission.</p>',
      'items' => [
        [
          'title' => 'Define the Product Portfolio',
          'body' => '<p>The client identifies the products covered. <a href="' . $about_href . '">Motaded</a> confirms the inventory and the scope of work.</p>',
        ],
        [
          'title' => 'Review Information and Resolve Gaps',
          'body' => '<p>Motaded records missing documents and inconsistencies. The client coordinates manufacturer responses and supplies revised materials.</p>',
        ],
        [
          'title' => 'Prepare and Confirm Submission Information',
          'body' => '<p>Motaded organises the listing file. The client confirms product identities and final materials before submission.</p>',
        ],
        [
          'title' => 'Submit and Record the Status',
          'body' => '<p>Where authorised, Motaded submits the listing and records the status. The client receives the materials, available records and outstanding actions.</p>',
        ],
      ],
    ],
    'documents' => [
      'title' => 'Product Information and Documentation',
      'lede' => '<p>A consistent set of product records supports documentation review and listing preparation. The table below outlines the materials used for the initial assessment; the final requirements are confirmed for the products and work covered by the engagement.</p>',
      'headers' => ['Information group', 'Materials to provide', 'Responsible party'],
      'items' => [
        [
          'title' => 'Product identity and portfolio',
          'subtitle' => 'Brand and product names, product categories, variants, pack sizes and available product references or barcodes',
          'body' => '<p>The brand owner or client provides the portfolio; the manufacturer confirms technical product identities.</p>',
        ],
        [
          'title' => 'Intended use and product claims',
          'subtitle' => 'Product purpose, directions for use and claims appearing on packaging or promotional materials',
          'body' => '<p>The brand owner supplies the proposed wording; the manufacturer provides relevant clarification and supporting evidence.</p>',
        ],
        [
          'title' => 'Composition',
          'subtitle' => 'Available ingredient lists and manufacturer-issued formulation information, including concentrations where required for the review',
          'body' => '<p>The manufacturer supplies and confirms the composition information.</p>',
        ],
        [
          'title' => 'Labels and packaging',
          'subtitle' => 'Clear images or artwork of the container and outer packaging, including applicable language versions and accompanying information',
          'body' => '<p>The brand owner or manufacturer supplies the current versions and arranges agreed revisions.</p>',
        ],
        [
          'title' => 'Manufacturer information',
          'subtitle' => 'Legal manufacturer name, address, manufacturing site details and available supporting records',
          'body' => '<p>The manufacturer provides current and consistent information.</p>',
        ],
        [
          'title' => 'Safety and supporting documentation',
          'subtitle' => 'Available product safety assessments, specifications, test reports and documents supporting relevant claims',
          'body' => '<p>The manufacturer supplies available evidence and addresses technical gaps.</p>',
        ],
        [
          'title' => 'Applicant and company records',
          'subtitle' => 'Relevant company registration details, applicant information and authorisations for the agreed work',
          'body' => '<p>The client provides company records and confirms authorised contacts.</p>',
        ],
        [
          'title' => 'Existing listing history',
          'subtitle' => 'Previous listing references, submission records, correspondence and outstanding requests',
          'body' => '<p>The client or existing account holder provides the available records.</p>',
        ],
        [
          'title' => 'Document Coordination',
          'body' => '<p><a href="' . $about_href . '">Motaded</a> maintains a record of received materials, outstanding requests and document versions used in the agreed work. Technical questions are directed to the manufacturer, while the client coordinates access to supplier information and confirms revised materials.</p><p>Documents should identify the relevant product or variant clearly. Where information is shared across several products, its applicability should be confirmed by the manufacturer.</p>',
        ],
      ],
    ],
    'fees' => [
      'title' => 'Fees and quotation',
      'lede' => '<p>The quotation takes into account the required service, the product documentation and the status of any existing application.</p>',
      'items' => [
        [
          'title' => 'Motaded service fee',
          'body' => '<ul><li>Charged for the agreed scope of work.</li><li>Depends on the service and how ready the documents are.</li><li>Confirmed in the written proposal before work begins.</li></ul>',
        ],
        [
          'title' => 'Government and third-party charges',
          'body' => '<ul><li>Paid separately, if they apply.</li><li>Depend on the specific procedure.</li><li>Known costs at the time of the proposal are listed separately.</li></ul><p>Confirm current government fees on the Authority’s published schedule.</p>',
          'uri' => 'internal:#assessment',
          'link_title' => 'Request a Quotation',
        ],
      ],
    ],
    'form' => [
      'title' => 'Let’s discuss your cosmetics file',
      'body' => '<h2>Let’s discuss your cosmetics file</h2><p>Company details, the product names and whether you manufacture, store or only import are enough for the first consultation.</p><p>Motaded replies with the required work and a service quotation.</p>',
    ],
    'faq' => [
      ['question' => 'Is listing the same as registration?', 'answer' => '<p>SFDA’s term in eCosma is listing. The market often says registration. Both refer to the same product procedure. Details are on the <a href="/services/sfda-cosmetics-product-registration">product registration page</a>.</p>'],
      ['question' => 'Do I need an establishment licence before listing?', 'answer' => '<p>An establishment licence is a usual prerequisite before cosmetics are listed or stored. Confirm the current GHAD rule on SFDA and on the <a href="/services/sfda-cosmetics-establishment-license">establishment licence page</a>.</p>'],
      ['question' => 'Can you list a product for a company with no Saudi warehouse?', 'answer' => '<p>Share the company structure and the intended import route. The assessment will state which licence, if any, is required before listing.</p>'],
      ['question' => 'Do you handle import as well as listing?', 'answer' => '<p>Yes, when it is included in the scope. Import work follows the <a href="/services/customs-clearance-saudi-arabia">customs clearance</a> process.</p>'],
      ['question' => 'How is Motaded’s price determined?', 'answer' => '<p>The fee depends on the number of products, whether a licence is included and the completeness of the file. We confirm it in writing before work begins.</p>'],
    ],
    'related_extra' => [
      ['title' => 'Medicine services', 'body' => '<p>SFDA medicine files and import coordination.</p>', 'uri' => 'internal:/services/medicine-services-saudi-arabia'],
      ['title' => 'Medical device services', 'body' => '<p>MDMA registration and authorized representative support.</p>', 'uri' => 'internal:/services/medical-device-services-saudi-arabia'],
      ['title' => 'SFDA', 'body' => '<p>Official systems and sectors supervised by the Authority.</p>', 'uri' => $sfda],
      ['title' => 'FASAH', 'body' => '<p>Customs declaration messages for imported goods.</p>', 'uri' => $fasah],
      ['title' => 'SABER', 'body' => '<p>Conformity certificates when a technical regulation also applies.</p>', 'uri' => $saber],
      ['title' => 'ZATCA', 'body' => '<p>Duty, import VAT and the customs assessment on the goods.</p>', 'uri' => $zatca],
      ['title' => 'Customs clearance', 'body' => '<p>Declaration and inspection coordination for imported goods.</p>', 'uri' => $customs],
      ['title' => 'About Motaded', 'body' => '<p>How the company works with clients in Saudi Arabia.</p>', 'uri' => $about],
    ],
  ],
];
