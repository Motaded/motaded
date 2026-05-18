<?php

/**
 * @file
 * Regenerates document_import_official.meta.php (service type + long summaries).
 *
 * Usage: php scripts/build_document_import_meta.php
 */

declare(strict_types=1);

$out = dirname(__DIR__) . '/web/modules/custom/motaded_custom/data/document_import_official.meta.php';

/** @var array<string, array{field_taxonomy: string, field_short_description: string}> $meta */
$meta = [
  'Investment Law' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'The Updated Saudi Investment Law (MISA laws hub) is the cornerstone statute for domestic and foreign investment after the former Foreign Investment Law was repealed. It defines investor rights, registration with the Ministry of Investment, excluded and restricted activities, and how licences interact with commercial registration on Saudi Business Center (Meras). Compliance teams should read it together with the implementing regulations and MISA’s English executive summary, then confirm any amendment on misa.gov.sa before board or bank reliance.',
  ],
  'Investment Law — Implementing Regulations' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'These implementing regulations operationalise the Updated Investment Law: definitions, application procedures, competent authorities, penalties, and detailed rules for excluded activities. They are essential for legal and operations teams translating a high-level MISA licence into day-to-day obligations and document checklists. Pair with the base law and Invest Saudi / Meras workflows when onboarding a new entity or amending an investment licence.',
  ],
  'Updated Investment Law — Executive Summary' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'MISA publishes this English executive summary to explain key changes in the Updated Investment Law for international boards and advisors who need orientation before reading the Arabic statute. It is not a substitute for the official Arabic text or implementing regulations but helps structure diligence questions on registration, national ownership rules, and sector conditions. Always confirm the latest PDF on misa.gov.sa alongside your Arabic legal review.',
  ],
  'Anti-Money Laundering Policy' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Anti-Money Laundering Law on the MISA laws hub sets the Kingdom’s framework for preventing money laundering and terrorist financing, with implications for financial institutions, designated non-financial businesses, and investor due diligence. Establishments should align KYC, beneficial ownership disclosure, and suspicious activity reporting with SAMA and FIU guidance referenced in related circulars. Use this text with your compliance programme and ZATCA / banking onboarding requirements.',
  ],
  'Companies Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Companies Law governs formation, governance, capital, shareholder rights, and dissolution of Saudi companies—directly relevant to articles of association, board resolutions, and Meras commercial registration. CFOs and company secretaries rely on it when choosing LLC vs joint stock structures, managing general assemblies, and planning dividends or restructuring. Cross-reference the Commercial Register Law and Bankruptcy Law for registration and insolvency contexts.',
  ],
  'Commercial Register Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'This law regulates the commercial register maintained for businesses in Saudi Arabia, including registration, amendment, suspension, and publication duties tied to Saudi Business Center (Meras) and CR numbers used across government platforms. Accurate activity codes and registered data feed Qiwa, ZATCA, GOSI, and Balady processes—discrepancies often block renewals or VAT registration. Operations teams should treat CR data as the master identity record for the establishment.',
  ],
  'Data Protection Regulation' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Personal Data Protection Law (PDPL) establishes rules for processing personal data, consent, privacy notices, cross-border transfers, and breach notification in Saudi Arabia. Technology, HR, and marketing teams need it when deploying SaaS, employee records, customer databases, or analytics that touch Saudi residents. Align operational controls with SDAIA guidance and contract clauses with processors; verify the Arabic text for legal reliance.',
  ],
  'Environmental Regulation' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Environmental Law sets permitting, impact assessment, pollution control, and enforcement expectations for projects with industrial, energy, or infrastructure footprints. Investors in manufacturing, logistics yards, and real estate development use it alongside Balady municipal permits and sector environmental approvals. Factory and plant teams should map waste, emissions, and remediation duties before civil works start.',
  ],
  'Electronic Transactions Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'This law recognises electronic records, signatures, and transactions—foundational for e-government services, digital contracts, and online onboarding across MISA, Meras, ZATCA FATOORA, and banking channels. Legal and IT teams reference it when approving remote signing, archived PDFs, and customer terms for digital products. Combine with sector rules (finance, health) where stricter evidence standards apply.',
  ],
  'Tourism Law' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'The Tourism Law frames licensing, operator obligations, and regulatory oversight for tourism activities and investments aligned with Saudi tourism sector strategy. Hospitality developers and tour operators use it with MISA investment licensing and Balady municipal requirements for sites and attractions. Confirm sector-specific circulars and Ministry of Tourism guidance for your asset class.',
  ],
  'VAT Implementing Regulations (English)' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'ZATCA’s English translation of the VAT implementing regulations details registration thresholds, taxable supplies, invoicing, returns, refunds, and special schemes—core reading for tax and finance teams after the VAT Law. It supports mapping ERP and FATOORA e-invoicing rules to chart of accounts and contract tax clauses. English is for orientation only; verify Arabic text and live ZATCA circulars before filings.',
  ],
  'VAT Registration Guide' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'This library item links VAT registration and compliance practice to ZATCA’s official implementing regulations and portal guidance for taxable persons in Saudi Arabia. Finance teams use it when deciding mandatory vs voluntary registration, grouping entities, and documenting tax invoices for audits. Maintain consistency with CR activity codes on Meras and ZATCA taxpayer profiles.',
  ],
  'Excise Tax Guide' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'Excise tax targets specific goods (e.g. tobacco, soft drinks, energy products) under GCC harmonised rules administered by ZATCA. Manufacturers and importers need product classification, registration, warehouse controls, and return mechanics aligned with the GCC excise agreement referenced in ZATCA materials. Review Arabic agreement text and ZATCA excise pages for product-level rates.',
  ],
  'Company Incorporation Guide' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'MISA’s official Investor Guide (compressed PDF) walks through establishment steps, licensing context, and links to national e-services for foreign and local investors. It is the practical companion to the Investment Law for project teams sequencing MISA approval, Meras CR, ZATCA tax registration, Qiwa, GOSI, and municipal permits. Download updates from misa.gov.sa when MISA refreshes the guide.',
  ],
  'Articles of Association Template' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Articles of association must comply with the Companies Law—covering capital, management, shareholder meetings, and transfer restrictions before notarisation and Meras filing. This document provides the statutory reference; legal counsel should draft AoA to match your chosen company form (LLC, JSC). Update AoA after capital changes or governance reforms.',
  ],
  'Business Activity Classification Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'ISIC-aligned activity codes on the commercial register determine which regulators, licences, and Saudization categories apply to your establishment. The Commercial Register Law underpins registration accuracy—wrong codes can block Qiwa transfers, VAT activities, or Balady permits. Review Meras activity picker with legal before CR issuance and amendments.',
  ],
  'Business Registration Form' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Initial business registration flows through Meras integrate investment licences, partner IDs, and statutory documents governed by the Commercial Register Law. Use this entry alongside the official CR law PDF until the live Meras form is attached. Plan parallel timelines for MISA, ZATCA, and bank account opening.',
  ],
  'Business Restructuring Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Bankruptcy Law provides preventive settlement, financial restructuring, and liquidation tools for distressed companies—critical when refinancing, selling assets, or closing Saudi operations. CFOs and boards should involve licensed trustees and courts as required, and coordinate CR amendments on Meras. Early engagement can preserve licences and employment continuity where feasible.',
  ],
  'Commercial License Renewal Form' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Chambers of Commerce membership and commercial documentation often support licence renewals and attestations used with municipal and sector regulators. The Chambers of Commerce Law defines membership duties relevant to certificates of origin and business letters. Pair with Balady renewal calendars and sector licence expiries (tourism, health, industrial).',
  ],
  'Company Closure Form' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Closing a Saudi company requires settling liabilities, labour endings in Qiwa, GOSI cessation, ZATCA deregistration, and often bankruptcy or voluntary liquidation steps under the Bankruptcy Law and Companies Law. This reference supports wind-down planning; attach Meras closure checklists when filed. Protect directors by documenting creditor notices and final CR cancellation.',
  ],
  'Construction Permit Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Real estate registration and title clarity precede most Balady building permits for factories, warehouses, and commercial fit-outs. The Real Estate Registration Law governs recording ownership and encumbrances used by municipalities and lenders. Developers should sequence land title, environmental approvals, and Balady engineering submissions before site mobilisation.',
  ],
  'Corporate Governance Guide' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Companies Law sets board composition, duties, conflicts, audits, and shareholder protections expected of Saudi companies—baseline corporate governance for JSCs and large LLCs. Listed or regulated entities add Capital Market Law and sector rules. Document governance policies and committee charters aligned with this statute for investor and bank diligence.',
  ],
  'Corporate Tax Overview' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'ZATCA’s English Income Tax Law text outlines corporate tax residence, rates, deductions, and filing for Saudi and foreign entities with Saudi-source income. Multinationals should integrate it with transfer pricing bylaws, withholding tax on outbound payments, and treaty positions. Arabic text and annual ZATCA updates govern legal filings—use English for orientation only.',
  ],
  'Employee Onboarding Checklist' => [
    'field_taxonomy' => 'HR Services',
    'field_short_description' => 'Onboarding in Saudi Arabia typically requires Qiwa contracts, GOSI registration under the Social Insurance Law, valid immigration status via Muqeem where applicable, and wage files through Mudad for WPS compliance. HR operations should use this checklist with the Social Insurance Law PDF to avoid penalties during inspections. Align job titles and nationalities with Nitaqat bands.',
  ],
  'Employee Termination Policy' => [
    'field_taxonomy' => 'HR Services',
    'field_short_description' => 'Termination must follow HRSD labour rules and contractual limits, while the Civil Transactions Law informs broader contract remedies and notices. Document disciplinary processes, end-of-service calculations, and visa cancellation in Muqeem for expatriate staff. Obtain legal review before collective redundancies or branch closures.',
  ],
  'Factory Setup Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Industrial facilities face Waste Management Law duties, Environmental Law permits, Balady construction approvals, and often energy licences under the Law of Energy Supplies. This guide ties factory investors to those statutes before equipment import via FASAH/ZATCA customs. Plan SABER conformity for machinery and production inputs where required.',
  ],
  'Healthcare Licensing Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Law of Practicing Healthcare Professions regulates licensing of facilities and professionals, complementing MOH and SFDA requirements for clinics, hospitals, and allied services. Investors pair it with the Law of Medical Devices and Supplies for equipment markets. Allow long lead times for facility inspection and staff credential verification.',
  ],
  'HR Policy Framework' => [
    'field_taxonomy' => 'HR Services',
    'field_short_description' => 'A Saudi HR policy framework should reflect Social Insurance Law contribution rules, HRSD labour obligations, anti-discrimination principles, and data protection for employee records. Use this entry with GOSI, Qiwa, and Mudad operational manuals when drafting handbooks. Update policies after major HRSD circulars or PDPL guidance.',
  ],
  'Import License Regulation' => [
    'field_taxonomy' => 'Government Relations Services',
    'field_short_description' => 'The Commercial Maritime Law governs shipping, ports, and maritime commerce relevant to import logistics and sea freight into Saudi ports. Importers coordinate with freight forwarders, FASAH clearance, and ZATCA customs declarations. Review port service fee laws when budgeting landed cost for bulk and container cargo.',
  ],
  'Industrial License Application' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'Energy-intensive and utility-dependent industrial projects fall under the Law of Energy Supplies alongside MISA investment licensing and environmental permits. Applicants should align load requests with national utility planners and Environmental Law assessments. Attach sector-specific industrial licences where ministries impose extra conditions.',
  ],
  'Logistics Investment Guide' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'Logistics and port-related investments reference the Commercial Maritime Law for shipping, stevedoring, and maritime services in Saudi waters. Combine with Road Transport Law for inland distribution and ZATCA/FASAH for customs. Vision 2030 logistics zones may add special purpose authority requirements beyond this statute.',
  ],
  'Municipality License Guide' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Balady municipal licences govern shop signage, occupancy, health, and construction for brick-and-mortar operations—alongside Chambers of Commerce attestations and CR activity codes. This entry links chamber law context; confirm site-specific Balady procedures on balady.gov.sa. Retail and F&B chains should track renewal dates per branch.',
  ],
  'Payroll Template' => [
    'field_taxonomy' => 'HR Services',
    'field_short_description' => 'Payroll in Saudi Arabia must respect Social Insurance Law contribution bases, WPS uploads through Mudad, and Qiwa wage data used in inspections. Finance and HR should reconcile monthly GOSI files with accounting accruals and ZATCA payroll-related tax positions where applicable. Document overtime and allowances per HRSD rules.',
  ],
  'Product Compliance Guide' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Food Law sets safety, labelling, and market rules for food products—overlapping SABER technical regulations and SFDA registration for imports and local production. Quality teams map product categories to conformity certificates before customs clearance. Non-food goods may fall under other product laws and SABER schemes.',
  ],
  'Supplier Registration Guide' => [
    'field_taxonomy' => 'Supplier registration service',
    'field_short_description' => 'Government Tenders and Procurement Law defines how suppliers qualify for public tenders on Etimad—registration, declarations, bid bonds, and performance guarantees. Vendors targeting ministries and SOEs should read this law with Etimad user manuals and sector prequalification lists. Maintain CR, ZATCA, and GOSI certificates current for audit.',
  ],
  'Technology Startup Guide' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'Technology ventures face the Telecommunications and Information Technology Law for licensing telecom and IT services, plus PDPL for data, and often Capital Market or Banking rules for fintech models. Startups should sequence MISA/CR setup with CITC requirements and cloud data residency decisions. Review Payments Law if offering wallets or payment services.',
  ],
  'Tourism Investment Guide' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'Tourism investments must satisfy the Tourism Law’s licensing rules in addition to MISA foreign investment steps and Balady permits for hospitality assets. Operators plan staffing under HRSD rules and seasonal marketing compliance. Link financing covenants to tourism authority inspection milestones.',
  ],
  'VAT Return Form' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'VAT returns follow mechanics in ZATCA’s implementing regulations—tax periods, adjustments, input tax recovery, and e-invoice reconciliation via FATOORA. This entry stores the regulations PDF as the authoritative reference for preparers building return workpapers. Reconcile GL VAT control accounts monthly to avoid assessment differences.',
  ],
  'Bankruptcy Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Bankruptcy Law modernises insolvency, preventive settlement, and creditor rights for Saudi businesses—used in restructuring discussions with banks and suppliers. Directors must understand fiduciary duties during insolvency proceedings and Meras filing obligations. Engage licensed practitioners early when liquidity stress appears.',
  ],
  'Anti-Concealment Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Anti-Concealment Law criminalises disguised ownership arrangements that circumvent foreign ownership or sector restrictions—relevant to nominee structures and informal partnerships. Investors should ensure transparent CR and MISA beneficial ownership disclosures. Penalties can include fines, publication, and business closure.',
  ],
  'Civil Transactions Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Civil Transactions Law codifies general contract, tort, and property rules forming the civil law backbone for commercial deals in Saudi Arabia. Legal teams cite it for damages, agency, and limitation periods alongside Islamic law principles. Use with sector-specific statutes (companies, employment, real estate) for complete advice.',
  ],
  'E-Commerce Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'The E-Commerce Law regulates online stores, consumer disclosures, returns, and provider obligations for digital sales in Saudi Arabia. Retailers integrate it with PDPL privacy notices, ZATCA VAT invoicing, and SABER product conformity for shipped goods. Marketplace operators review additional liability articles with counsel.',
  ],
  'Franchise Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Franchise Law requires disclosure, registration, and fair dealing for franchisors and franchisees operating in the Kingdom—important for F&B, retail, and services brands expanding via master franchise. Due diligence packs should include Arabic disclosure documents and cooling-off rules. Align with Commercial Register and trademark registrations.',
  ],
  'Law of Arbitration' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Law of Arbitration governs domestic and international arbitration seated in Saudi Arabia, including enforcement of awards and interaction with courts. Commercial contracts for construction, energy, and investment often specify SCCA or international rules with Saudi seat. Review recent reforms when drafting dispute resolution clauses.',
  ],
  'Commercial Maritime Law' => [
    'field_taxonomy' => 'Government Relations Services',
    'field_short_description' => 'This law regulates maritime commerce, vessels, ports, and related services—core for shipping lines, freight forwarders, and port operators serving Saudi trade corridors. Importers and exporters reference it with customs and bill of lading practice. Check companion port fees and transport laws for integrated logistics contracts.',
  ],
  'Competition Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Competition Law prohibits anti-competitive agreements, abuse of dominance, and certain mergers without General Authority for Competition notification. M&A and commercial teams assess thresholds before closing share deals or joint ventures in Saudi markets. Marketing cooperatives and resale price maintenance need competition review.',
  ],
  'The Capital Market Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Capital Market Law frames securities offerings, exchanges, licensing of capital market institutions, and investor protection under CMA supervision. Listed companies and fund managers align disclosures and governance with CMA regulations built on this statute. Private placements still face prospectus and licensing rules where applicable.',
  ],
  'Banking Control Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Banking Control Law empowers SAMA to license and supervise banks, setting prudential and conduct expectations for deposit-taking institutions. Corporate treasurers understand account opening, AML, and sanctions processes banks apply under this framework. Fintech payment institutions follow separate but related SAMA rules.',
  ],
  'Government Tenders and Procurement Law' => [
    'field_taxonomy' => 'Supplier registration service',
    'field_short_description' => 'This law governs public procurement methods, competition, transparency, and contract management for government entities—implemented through Etimad and ministry systems. Contractors track local content, SME preferences, and bid protest procedures. Keep insurance, performance bonds, and ZATCA tax certificates ready for award conditions.',
  ],
  'Social Insurance Law' => [
    'field_taxonomy' => 'HR Services',
    'field_short_description' => 'The Social Insurance Law establishes GOSI schemes for pensions, occupational hazards, and SANED unemployment support—driving monthly contribution filings tied to Qiwa wage data. Employers classify workers correctly and report injuries promptly. Changes in wage components affect contribution bases audited against Mudad payments.',
  ],
  'Telecommunications and Information Technology Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'This law regulates telecom networks, ICT services, licensing, and consumer protections under CITC—relevant to carriers, cloud providers, and enterprises using radio spectrum or VoIP services. Data centres and SaaS vendors review licensing categories before commercial launch. Cross-border connectivity may need additional approvals.',
  ],
  'Real Estate Registration Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Real estate registration creates public title records for land and property transactions, supporting mortgages, sales, and municipal development control. Developers verify title before off-plan sales governed by separate real estate laws on the MISA hub. Integrate with notarisation and zakat/tax planning on disposals.',
  ],
  'Railway Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'The Railway Law regulates construction and operation of rail infrastructure and services in Saudi Arabia—relevant to logistics investors and industrial sites seeking rail connectivity. Operators coordinate with transport authorities and safety regulations. Land acquisition may involve real estate registration and expropriation procedures.',
  ],
  'Road Transport Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Road transport rules cover freight and passenger operations, vehicle standards, and operator licensing for last-mile and domestic distribution after port clearance. Fleet owners align driver work time with HRSD rules and commercial insurance. Violations can affect Balady and sector transport permits.',
  ],
  'Waste Management Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Waste Management Law imposes duties on waste generators, transporters, and treatment facilities—critical for factories, hospitals, and municipalities. Environmental permits and Balady health rules add operational detail. Contracts with licensed hauliers should reference statutory liability for illegal dumping.',
  ],
  'Food Law' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'The Food Law protects public health through food safety standards, establishment licensing, and market surveillance—overlapping SFDA registration and SABER for imported goods. F&B manufacturers document HACCP and labelling in Arabic per regulator guidance. Recall and penalty regimes apply to serious violations.',
  ],
  'Electricity Law' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'The Electricity Law governs generation, transmission, distribution, and consumer supply licences in Saudi Arabia’s power sector—key for industrial parks and renewable projects. Investors coordinate with the national utility and Energy Supplies Law for grid connection. Power purchase agreements reference this regulatory framework.',
  ],
  'Law of Medical Devices and Supplies' => [
    'field_taxonomy' => 'Compliance',
    'field_short_description' => 'This law regulates registration, distribution, and advertising of medical devices and supplies—complementing SFDA technical rules and SABER for imported equipment. Distributors maintain vigilance reporting and storage standards. Hospitals verify supplier licences before procurement tenders.',
  ],
  'Patents Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'The Patents Law (with integrated circuits layout provisions) protects inventions and layout designs filed with SAIP—supporting R&D and manufacturing investors. Employers draft IP assignment clauses consistent with statutory employee invention rules. Enforcement and compulsory licensing articles matter for technology transfers.',
  ],
  'Trademarks Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'The Trademarks Law governs registration, infringement, and licensing of marks in Saudi Arabia, including well-known mark protection. Brand owners file through SAIP before market entry and monitor Gulf Cooperation Council cross-border issues. Franchise and distribution agreements should align trademark licences with this law.',
  ],
  'Copyright Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'Copyright protection covers software, content, media, and creative works—relevant to publishers, game studios, and enterprises licensing third-party assets. Employment and contractor agreements should assign or license rights clearly. Digital platforms address takedown and piracy under enforcement mechanisms.',
  ],
  'Payments and Payment Services Law' => [
    'field_taxonomy' => 'Technology',
    'field_short_description' => 'This law regulates payment systems, e-money, and payment service providers under SAMA oversight—core for fintech wallets, BNPL, and merchant acquirers. Institutions obtain licences and safeguard customer funds per implementing rules. AML and PDPL compliance integrate with onboarding and transaction monitoring.',
  ],
  'Chambers of Commerce Law' => [
    'field_taxonomy' => 'Business Support',
    'field_short_description' => 'Chambers of Commerce support members with certificates, training, and advocacy defined by this law—often required for export documentation and some licence renewals. Businesses register with the appropriate regional chamber after CR issuance. Verify membership fees and attestation turnaround for tender deadlines.',
  ],
  'Agriculture Law' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'The Agriculture Law frames investment and regulation in agricultural production, land use, and related services—relevant to agribusiness and food security projects. Investors pair it with water rights, environmental permits, and SABER for inputs. MISA may attach conditions for foreign ownership in sensitive activities.',
  ],
  'Private Sector Participation Law' => [
    'field_taxonomy' => 'Investment',
    'field_short_description' => 'The Private Sector Participation Law enables PPP structures and concession projects with government entities—used in infrastructure, utilities, and social services. Sponsors review risk allocation, revenue models, and Etimad procurement interfaces. Lenders require full approval chains from sector ministries and MISA where foreign investors participate.',
  ],
  'Income Tax Law (English)' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'ZATCA publishes this English reference to the Income Tax Law covering corporate taxpayers, permanent establishments, and withholding interactions. Tax directors use it to design structures and provision models before Arabic filings. Update models when ZATCA issues new regulations or amending decisions.',
  ],
  'Income Tax Regulations (English)' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'Implementing regulations detail deductions, returns, documentation, and assessments under the Income Tax Law—companion reading for corporate tax compliance teams. Align transfer pricing documentation and related-party disclosures with the Transfer Pricing Bylaws. Maintain evidence for zakat and tax authority reviews.',
  ],
  'Transfer Pricing Bylaws (English)' => [
    'field_taxonomy' => 'Accounting services',
    'field_short_description' => 'ZATCA’s transfer pricing bylaws set documentation, benchmarking, and disclosure expectations for related-party transactions involving Saudi entities. Multinationals align master and local files with OECD-style methods accepted locally. Penalties apply for inadequate documentation during tax audits—coordinate with customs valuation for import pricing.',
  ],
];

$meta = array_merge($meta, require dirname(__DIR__) . '/web/modules/custom/motaded_custom/data/document_import_official.meta.additions.php');

$catalog = require dirname(__DIR__) . '/web/modules/custom/motaded_custom/data/document_import_official.catalog.php';
foreach ($catalog as $row) {
  $action = strtolower((string) ($row['action'] ?? 'update'));
  if ($action === 'delete') {
    unset($meta[$row['title'] ?? '']);
  }
}
foreach ($catalog as $row) {
  $title = $row['title'] ?? '';
  $action = strtolower((string) ($row['action'] ?? 'update'));
  if ($action === 'delete' || $title === '') {
    continue;
  }
  if (!isset($meta[$title])) {
    fwrite(STDERR, "Missing meta for catalog title: {$title}\n");
  }
}

$buf = "<?php\n\n/**\n * @file\n * Service type (field_taxonomy) and extended summaries for document import.\n *\n * Merged by drush mdoc; overrides catalog field_short_description when present.\n */\n\ndeclare(strict_types=1);\n\nreturn " . var_export($meta, TRUE) . ";\n";
file_put_contents($out, $buf);
echo "Wrote " . count($meta) . " entries to {$out}\n";
