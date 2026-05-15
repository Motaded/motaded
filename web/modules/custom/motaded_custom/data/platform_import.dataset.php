<?php

/**
 * @file
 * Дані для platform_import_ready.csv (джерела: офіційні сайти / my.gov.sa / HRSD).
 * field_short_description — короткий тизер; детальний текст у body (після вступних абзаців).
 * Генерація EN: php scripts/rebuild_platform_import_csv.php
 * Генерація AR: php scripts/rebuild_platform_import_ar_csv.php
 * AR-рядки: translation_source_title = точний англомовний title; taxonomy names лишаються англійськими.
 *
 * @return list<array<string, string>>
 */

require_once __DIR__ . '/platform_import.i18n.php';

return [
  // --- ZATCA (zatca.gov.sa) ---
  _motaded_pi_row([
    'title' => 'ZATCA',
    'field_short_description' => 'Saudi authority for VAT, zakat, customs, and FATOORA e-invoicing: registration, compliant invoices, filings, and customs—verify requirements on zatca.gov.sa.',
    'field_subtitle' => 'Zakat, Tax and Customs Authority',
    'field_sector_name' => 'Finance & Fintech',
    'field_category_name' => 'Tax',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'MISA (Ministry of Investment Saudi Arabia)|Qiwa|GOSI|Saudi Business Center (Meras)|Balady|Mudad',
    'field_external_link_uri' => 'https://zatca.gov.sa/en/Pages/default.aspx',
    'field_external_link_title' => 'ZATCA official portal',
    'field_cta_link_uri' => 'https://zatca.gov.sa/en/E-Invoicing/Pages/default.aspx',
    'field_cta_link_title' => 'E-invoicing (FATOORA)',
    'field_cta_text' => 'Explore e-invoicing guidance',
    'field_source_link_uri' => 'https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx',
    'field_source_link_title' => 'VAT rules and regulations',
    'field_source_text' => 'Official VAT materials',
    'meta_title' => 'ZATCA Saudi Arabia: VAT registration, FATOORA e-invoicing Phase 2, customs & zakat compliance',
    'meta_description' => 'In-depth guide to ZATCA for CFOs and ops: Saudi VAT, FATOORA e-invoicing integration, customs declarations, zakat, record retention, links to Qiwa/GOSI/Balady workflows, and official zatca.gov.sa resources.',
    'meta_keywords' => 'ZATCA, Saudi VAT registration, FATOORA Phase 2, e-invoicing integration Saudi Arabia, VAT return KSA, customs declaration Saudi Arabia, zakat compliance, tax audit Saudi Arabia, WHT Saudi Arabia',
    'body_summary' => 'ZATCA administers VAT, zakat, customs, and FATOORA e-invoicing. This expanded overview connects tax compliance with employer platforms (Qiwa, GOSI, Mudad), investment entry (MISA), and municipal licensing (Balady) so finance and legal teams see one joined-up operating picture.',
    'body' => '<p>The <strong>Zakat, Tax and Customs Authority (ZATCA)</strong> is the Saudi government body responsible for tax administration, zakat, customs, and the national <strong>e-invoicing system (FATOORA)</strong>. Official materials published by ZATCA describe phased implementation for generating and integrating electronic invoices, as well as VAT rules applicable to taxable supplies in the Kingdom.</p><p>Companies engaged in taxable activities typically need to register, issue compliant invoices, retain records, and submit periodic returns through ZATCA’s digital channels. Customs and cross-border trade obligations are also administered under the same institutional framework.</p><p>Operationally, ZATCA data should stay consistent with <a href="/platforms/qiwa">Qiwa</a> establishment records, <a href="/platforms/gosi">GOSI</a> wage bases used for contributions, and <a href="/platforms/balady">Balady</a> branch/activity licences—discrepancies between CR activity codes and invoiced goods or services invite questions in tax and municipal reviews alike.</p><p>Investors entering through <a href="/platforms/misa">MISA (Ministry of Investment Saudi Arabia)</a> or the <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> should sequence VAT registration, e-invoicing onboarding, and payroll go-live so that first invoices, WPS files in <a href="/platforms/mudad">Mudad</a>, and customs entries share the same legal entity identifiers.</p><p>This page summarizes publicly documented purposes of the platform and links to official ZATCA resources. Always verify requirements against the latest guidance on <a href="https://zatca.gov.sa/">zatca.gov.sa</a> before making compliance decisions.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Any company with taxable supplies, imports subject to customs, or cross-border services into the Kingdom must align invoicing formats, tax point rules, and record retention with ZATCA’s published technical and legal notices—not with third-party summaries alone.\n"
          . "Phase 1 and Phase 2 e-invoicing rules affect how ERP, POS, and B2B marketplaces generate UUID-based invoices, report clearance/clearance statuses, and archive XML/PDF evidence for audits.\n"
          . "VAT registration thresholds, filing frequencies (monthly vs quarterly), and reverse-charge mechanics are defined in official VAT regulations; misclassification of exempt vs zero-rated supplies is a common source of reassessments.\n"
          . "Customs processes intersect with commercial registration data, HS codes, and valuation rules—especially for bonded warehouses, re-exports, and e-commerce fulfilment models landing goods in KSA.\n"
          . "After licensing via MISA (Ministry of Investment Saudi Arabia) or the Saudi Business Center (Meras), finance teams typically wire ZATCA timelines together with payroll (Qiwa, GOSI, Mudad) and municipal permits (Balady) because VAT invoices must reflect correct CR branches and activity codes.\n"
          . "ZATCA portals host e-invoicing simulators, integration guidelines for solution providers, and sector-specific FAQs that operational teams should monitor whenever enforcement bulletins change.\n"
          . "Zakat obligations for Saudi-owned entities remain distinct from VAT; treasury should model both cash flows and board reporting, particularly where consolidated groups share services across entities.\n"
          . "Penalties for late filing, incorrect invoices, or missing integration steps are published in administrative tables—proactive monitoring beats reactive dispute resolution after a field audit.\n"
          . "For inbound investors, aligning transfer pricing documentation with ZATCA disclosures and customs valuation reduces friction during simultaneous tax and customs reviews.\n"
          . "Digital archiving, access logs for invoicing APIs, and segregation of duties between billing and tax review are practical controls teams implement before scale-up or M&A due diligence.\n"
          . "Always confirm the latest circulars on zatca.gov.sa before go-live; this text is an operational orientation, not legal or tax advice."
      ),
    'why_json' => [
      ['title' => 'Regulatory certainty', 'body' => 'Centralized rules and published guidelines help finance and operations teams align invoicing, VAT, and customs processes with national requirements.', 'icon' => 'shield'],
      ['title' => 'Digital compliance', 'body' => 'FATOORA and related services reduce paper processes and support auditability through structured electronic invoice data.', 'icon' => 'document'],
      ['title' => 'Cross-border trade', 'body' => 'Customs and VAT considerations frequently intersect for importers and exporters; ZATCA provides the authoritative channel for related filings.', 'icon' => 'globe'],
    ],
    'help_json' => [
      ['title' => 'VAT registration and filing', 'body' => 'Register for VAT where applicable, maintain accurate records, and submit returns according to published Saudi VAT rules.', 'icon' => 'briefcase'],
      ['title' => 'E-invoicing (FATOORA)', 'body' => 'Generate and exchange compliant electronic invoices, credit notes, and debit notes using solutions aligned with ZATCA’s technical framework.', 'icon' => 'list_check'],
      ['title' => 'Customs and zakat', 'body' => 'Use ZATCA channels for customs declarations and zakat obligations relevant to your entity type and sector.', 'icon' => 'building'],
    ],
    'steps_json' => [
      ['title' => 'Confirm your obligations', 'body' => 'Review VAT registration thresholds, invoicing phase requirements, and sector-specific notices published by ZATCA.'],
      ['title' => 'Prepare data and integrations', 'body' => 'Align ERP/billing systems with FATOORA requirements and keep master data (VAT numbers, addresses, line items) consistent.'],
      ['title' => 'Operate and monitor', 'body' => 'Submit filings on time, reconcile invoice data, and retain evidence for audits and reconciliations.'],
    ],
    'req_json' => [
      ['content' => 'Valid commercial registration and accurate legal entity data for registration and invoicing.'],
      ['content' => 'Taxable activities assessed under Saudi VAT rules (confirm applicability with advisors).'],
      ['content' => 'Accounting records and invoice archives suitable for audit and reconciliation.'],
      ['content' => 'Technical readiness for e-invoicing integration where Phase 2 requirements apply.'],
      ['content' => 'Processes for customs declarations when importing/exporting goods subject to customs control.'],
    ],
    'resources_json' => [
      ['title' => 'ZATCA home (EN)', 'uri' => 'https://zatca.gov.sa/en/Pages/default.aspx', 'link_title' => 'zatca.gov.sa', 'icon' => 'globe'],
      ['title' => 'E-invoicing hub', 'uri' => 'https://zatca.gov.sa/en/E-Invoicing/Pages/default.aspx', 'link_title' => 'E-invoicing', 'icon' => 'document'],
      ['title' => 'VAT rules', 'uri' => 'https://zatca.gov.sa/en/rulesregulations/vat/pages/default.aspx', 'link_title' => 'VAT', 'icon' => 'scale'],
    ],
    'path_alias' => '/platforms/zatca',
  ]),

  _motaded_pi_row([
    'title' => 'MISA (Ministry of Investment Saudi Arabia)',
    'field_short_description' => 'Saudi ministry for investment promotion and licensing; Invest Saudi and MISA e-services support foreign and strategic projects—confirm procedures on misa.gov.sa.',
    'field_subtitle' => 'Ministry of Investment',
    'field_sector_name' => 'General',
    'field_category_name' => 'Investment',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'ZATCA|Qiwa|Saudi Business Center (Meras)|Balady|GOSI|Muqeem|Mudad',
    'field_external_link_uri' => 'https://misa.gov.sa/',
    'field_external_link_title' => 'MISA official site',
    'field_cta_link_uri' => 'https://investsaudi.sa/',
    'field_cta_link_title' => 'Invest Saudi gateway',
    'field_cta_text' => 'Explore investment opportunities',
    'field_source_link_uri' => 'https://misa.gov.sa/about/',
    'field_source_link_title' => 'About MISA',
    'field_source_text' => 'Mission and vision (official)',
    'meta_title' => 'MISA Invest Saudi: foreign investment licence, RHQ, sector strategy & post-licence compliance',
    'meta_description' => 'Detailed MISA overview: investment licensing, Invest Saudi sectors, how MISA connects to Saudi Business Center (Meras), ZATCA VAT/FATOORA, Qiwa, GOSI, Balady permits, and official misa.gov.sa entry points.',
    'meta_keywords' => 'MISA Saudi Arabia, Invest Saudi licence, foreign investment KSA, RHQ programme Saudi Arabia, MISA e-services, post-licence compliance, Vision 2030 FDI, sector licensing Saudi Arabia',
    'body_summary' => 'MISA promotes and regulates investment in Saudi Arabia. This page adds depth on how MISA licences chain into commercial registration, VAT with ZATCA, workforce onboarding in Qiwa, social insurance with GOSI, payroll WPS in Mudad, and municipal licensing in Balady.',
    'body' => '<p>The <strong>Ministry of Investment for Saudi Arabia (MISA)</strong> is responsible for promoting, regulating, and developing investment in the Kingdom. Public materials describe MISA’s mission to attract and retain investors and strengthen Saudi Arabia’s competitiveness as an investment destination.</p><p><strong>Invest Saudi</strong> (investsaudi.sa) is widely positioned as the national gateway for discovering opportunities, understanding sectors, and navigating investor journeys. Licensing and e-services are published through MISA’s official channels.</p><p>After MISA approval, execution teams typically continue in the <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> ecosystem for CR issuance and bundled services, then line up <a href="/platforms/zatca">ZATCA</a> VAT and FATOORA onboarding, <a href="/platforms/qiwa">Qiwa</a> establishment records, <a href="/platforms/gosi">GOSI</a> registration, and <a href="/platforms/balady">Balady</a> municipal permits where retail, logistics, or construction footprints exist.</p><p>Where sponsored talent is involved, plan <a href="/platforms/muqeem">Muqeem</a> and <a href="/platforms/absher">Absher</a> steps in parallel with HR policies, and align salary payment calendars with <a href="/platforms/mudad">Mudad</a> wage file rules once payroll starts.</p><p>Use this page as a structured entry point, but rely on MISA and Invest Saudi for authoritative procedures, eligibility, and sector-specific requirements.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "MISA is Saudi Arabia’s ministry-level authority for investment promotion, investor protection, and licensing of foreign-owned enterprises, regional headquarters, and many strategic joint ventures tied to Vision 2030 programmes.\n"
          . "The Invest Saudi gateway translates national sector strategies (industry, tourism, health, logistics, digital) into discoverable opportunities, while MISA e-services host the transactional side of licence issuance, amendments, and post-licence reporting.\n"
          . "Investors rarely stop at MISA alone: after an investment licence, teams activate commercial registration flows through the Saudi Business Center (Meras), then register for VAT and e-invoicing with ZATCA, onboard establishments in Qiwa, and enrol employees in GOSI and immigration channels such as Muqeem.\n"
          . "Sector-specific caps, Saudization/Nitaqat implications, and technology transfer commitments can be embedded in licence conditions—legal and HR should read the issued licence annexes alongside HRSD circulars.\n"
          . "MISA also coordinates with other regulators when a project touches regulated activities (fintech sandbox, health data, education, defence supply chains), so a single “MISA approved” milestone does not replace sector permits from specialised authorities.\n"
          . "Board packs for international headquarters usually map MISA milestones to cash repatriation, customs bonded plant imports, and Balady construction permits for greenfield factories.\n"
          . "Digital attestations, beneficial ownership disclosures, and apostilled corporate documents are recurring preparation themes in MISA submissions—plan document legalisation early to avoid clock stoppage.\n"
          . "Post-licence, MISA reporting may include progress on committed capex, job creation, and local content; mis-reporting can affect renewals or expansion requests.\n"
          . "Regional headquarters programme participants should reconcile RHQ obligations with municipal service levels in Riyadh, Jeddah, or Eastern Province municipalities via Balady service channels.\n"
          . "Treat MISA communications as the authoritative interpretation of your licence conditions, and use this hub page to jump to tax, labour, insurance, and municipal platforms that execute the next layers of compliance.\n"
          . "Cross-border founders often pair MISA counsel with ZATCA and customs advisors when structuring import-heavy manufacturing or e-commerce fulfilment into the GCC from a Saudi hub.\n"
          . "Official procedures evolve—verify every checklist on misa.gov.sa and investsaudi.sa before signatures and wire transfers."
      ),
    'why_json' => [
      ['title' => 'Market entry clarity', 'body' => 'Investor journeys are supported through published licensing pathways and sector framing aligned with national priorities.', 'icon' => 'target'],
      ['title' => 'Ecosystem coordination', 'body' => 'MISA works across government stakeholders to reduce friction for investors establishing or expanding in Saudi Arabia.', 'icon' => 'users'],
      ['title' => 'Growth alignment', 'body' => 'Programs and communications frequently reference Vision 2030 diversification goals and private-sector participation.', 'icon' => 'chart'],
    ],
    'help_json' => [
      ['title' => 'Investment licensing', 'body' => 'Understand licensing categories and documentation expectations for establishing or expanding an investment entity.', 'icon' => 'briefcase'],
      ['title' => 'Sector opportunities', 'body' => 'Review priority sectors and published materials relevant to your investment thesis and operating model.', 'icon' => 'globe'],
      ['title' => 'Digital services', 'body' => 'Use MISA e-services for investor transactions where available and monitor notices for procedural updates.', 'icon' => 'cog'],
    ],
    'steps_json' => [
      ['title' => 'Define the investment case', 'body' => 'Prepare entity information, ownership structure, and activity scope aligned with licensing categories.'],
      ['title' => 'Submit through official channels', 'body' => 'Follow MISA/Invest Saudi guidance for applications, attachments, and sector-specific requirements.'],
      ['title' => 'Operationalize compliance', 'body' => 'Coordinate with tax, labor, and municipal requirements across other national platforms after licensing milestones.'],
    ],
    'req_json' => [
      ['content' => 'Corporate documentation and ownership disclosures required for licensing reviews.'],
      ['content' => 'Sector-specific approvals where regulations mandate additional clearances.'],
      ['content' => 'Commercial registration and legal presence consistent with the proposed activity.'],
      ['content' => 'Financial and operational plans where requested for large or regulated investments.'],
      ['content' => 'Ongoing compliance with investment law updates and reporting obligations.'],
    ],
    'resources_json' => [
      ['title' => 'MISA', 'uri' => 'https://misa.gov.sa/', 'link_title' => 'misa.gov.sa', 'icon' => 'globe'],
      ['title' => 'Invest Saudi', 'uri' => 'https://investsaudi.sa/', 'link_title' => 'investsaudi.sa', 'icon' => 'briefcase'],
      ['title' => 'About MISA', 'uri' => 'https://misa.gov.sa/about/', 'link_title' => 'About', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/misa',
  ]),

  _motaded_pi_row([
    'title' => 'Qiwa',
    'field_short_description' => 'HRSD digital labour hub for establishments: contracts, establishment records, and employer workflows tied to Qiwa.sa.',
    'field_subtitle' => 'Ministry of Human Resources and Social Development',
    'field_sector_name' => 'General',
    'field_category_name' => 'Labor',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'GOSI|Muqeem|Mudad|Absher|ZATCA|Balady',
    'field_external_link_uri' => 'https://www.hrsd.gov.sa/en/ministry-platforms',
    'field_external_link_title' => 'HRSD ministry platforms',
    'field_cta_link_uri' => 'https://qiwa.sa/',
    'field_cta_link_title' => 'Qiwa portal',
    'field_cta_text' => 'Open Qiwa',
    'field_source_link_uri' => 'https://dga.gov.sa/en/node/508',
    'field_source_link_title' => 'DGA directory entry (Qiwa)',
    'field_source_text' => 'Third-party directory (verify on HRSD)',
    'meta_title' => 'Qiwa Saudi Arabia: HRSD labour platform, contracts, Nitaqat, GOSI & Mudad integration',
    'meta_description' => 'Employer-focused Qiwa guide: electronic contracts, establishment services, Nitaqat quotas, links to GOSI contributions, Mudad wage files, Muqeem visas, Absher authentication, and official HRSD sources.',
    'meta_keywords' => 'Qiwa, HRSD Qiwa, Saudi employment contract digital, Nitaqat compliance, establishment services Saudi Arabia, Qiwa GOSI integration, Qiwa Mudad WPS, labour inspection Saudi Arabia',
    'body_summary' => 'Qiwa is HRSD’s unified digital labour platform. This extended summary explains how Qiwa data drives Muqeem visa eligibility, GOSI classifications, Mudad wage protection files, and why finance, HR, and mobility teams should govern it as one system.',
    'body' => '<p><strong>Qiwa</strong> is presented by the <strong>Ministry of Human Resources and Social Development (HRSD)</strong> as a unified digital platform for labor-system services in Saudi Arabia. Public announcements reference large-scale contract documentation volumes, reflecting broad private-sector adoption.</p><p>Employers typically use Qiwa for establishment-linked HR processes that intersect with <a href="/platforms/gosi">GOSI</a> for social insurance, <a href="/platforms/mudad">Mudad</a> for wage file compliance, <a href="/platforms/muqeem">Muqeem</a> for iqama and visa lifecycle events, and <a href="/platforms/absher">Absher</a> for individual-side authentications where required.</p><p>Retail, construction, and hospitality groups also align Qiwa headcount with <a href="/platforms/balady">Balady</a> municipal labour desk expectations on sites where joint inspections occur.</p><p>News releases on hrsd.gov.sa document policy updates affecting Qiwa workflows—treat them as the authoritative channel for compliance changes.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Qiwa is HRSD’s flagship digital labour platform: employers use it for establishment files, electronic employment contracts, contract amendments, branch updates, and many notifications that used to require physical visits.\n"
          . "Nitaqat colour bands, Saudization percentages, and quota calculations displayed in Qiwa directly affect work permit eligibility in Muqeem and can block visa issuance when establishments fall out of compliance.\n"
          . "Qiwa contract data feeds downstream processes—banks reference it for salary accounts, GOSI for contribution classifications, and Mudad for wage file validation—so a typo in job title, salary, or employee ID propagates across systems.\n"
          . "HR teams run periodic audits reconciling Qiwa headcount with finance payroll registers, GOSI statements, and active iqamas to catch ghost employees, duplicate IDs, or expired work authorisations before inspections.\n"
          . "Policy changes (minimum wage updates, documentation rules for certain nationalities, remote work declarations) are announced via HRSD channels and often require same-week configuration changes inside Qiwa.\n"
          . "Multi-branch retail and logistics operators centralise Qiwa admin roles but must still respect delegation limits and segregation of duties expected by internal audit and insurance underwriters.\n"
          . "Contract notarisation rules, probation clauses, and end-of-service accruals have digital footprints in Qiwa that legal should archive alongside employee files for labour dispute defensibility.\n"
          . "Integration with Absher is part of many employee journeys when individuals authenticate or complete personal-side confirmations tied to sponsor transactions.\n"
          . "Seasonal workforce spikes in hospitality and construction require forward planning in Qiwa so that visa requests in Muqeem align with project mobilisation dates and Balady work permits on site.\n"
          . "Third-party payroll outsourcers need power-of-attorney clarity: Qiwa actions remain the establishment’s regulatory responsibility even when processed by a vendor.\n"
          . "Training records, occupational health commitments, and HRSD inspection responses are increasingly referenced alongside Qiwa transaction logs during sector crackdowns.\n"
          . "Treat Qiwa as the operational source of truth for Saudi labour compliance, then wire outputs intentionally into GOSI, Mudad, ZATCA payroll VAT positions, and immigration tools."
      ),
    'why_json' => [
      ['title' => 'Employer productivity', 'body' => 'Digital workflows reduce manual paperwork for high-frequency HR transactions across establishments.', 'icon' => 'cog'],
      ['title' => 'Policy alignment', 'body' => 'Platform changes track national labor policy updates communicated by HRSD.', 'icon' => 'shield'],
      ['title' => 'Ecosystem connectivity', 'body' => 'Labor data often coordinates with insurance and immigration processes across complementary platforms.', 'icon' => 'link'],
    ],
    'help_json' => [
      ['title' => 'Establishment services', 'body' => 'Manage establishment data, branch updates, and employer records as required by published HRSD notices.', 'icon' => 'building'],
      ['title' => 'Contracts and documentation', 'body' => 'Follow HRSD guidance for notarization and documentation rules applicable to employment contracts in Qiwa.', 'icon' => 'document'],
      ['title' => 'Workforce compliance', 'body' => 'Monitor compliance obligations tied to workforce composition and reporting where published by authorities.', 'icon' => 'users'],
    ],
    'steps_json' => [
      ['title' => 'Register the establishment', 'body' => 'Complete employer onboarding steps and validate commercial registration linkages required by Qiwa.'],
      ['title' => 'Operationalize HR processes', 'body' => 'Configure contract issuance, amendments, and establishment updates according to current HRSD rules.'],
      ['title' => 'Integrate downstream systems', 'body' => 'Align payroll and immigration processes with outputs required by GOSI, banks, and related platforms.'],
    ],
    'req_json' => [
      ['content' => 'Valid commercial registration and authorized establishment representatives.'],
      ['content' => 'Employee data consistent with immigration records where applicable.'],
      ['content' => 'Compliance with published contract documentation requirements.'],
      ['content' => 'Banking readiness for wage-related workflows tied to national wage protection rules.'],
      ['content' => 'Monitoring of HRSD announcements for procedural changes affecting Qiwa.'],
    ],
    'resources_json' => [
      ['title' => 'HRSD ministry platforms', 'uri' => 'https://www.hrsd.gov.sa/en/ministry-platforms', 'link_title' => 'HRSD', 'icon' => 'globe'],
      ['title' => 'Qiwa', 'uri' => 'https://qiwa.sa/', 'link_title' => 'qiwa.sa', 'icon' => 'briefcase'],
      ['title' => 'DGA listing', 'uri' => 'https://dga.gov.sa/en/node/508', 'link_title' => 'DGA', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/qiwa',
  ]),

  _motaded_pi_row([
    'title' => 'Muqeem',
    'field_short_description' => 'Establishment portal for iqama, work permits, and visa-related services—confirm rules and fees on official channels and my.gov.sa.',
    'field_subtitle' => 'General Directorate of Passports (associated digital services)',
    'field_sector_name' => 'General',
    'field_category_name' => 'Operations',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Qiwa|Absher|GOSI|Mudad|Balady|MISA (Ministry of Investment Saudi Arabia)',
    'field_external_link_uri' => 'https://www.elm.sa/en/our-business/digital-products',
    'field_external_link_title' => 'ELM digital products (Muqeem context)',
    'field_cta_link_uri' => 'https://www.my.gov.sa/',
    'field_cta_link_title' => 'National portal (service directory)',
    'field_cta_text' => 'Find Muqeem-related services',
    'field_source_link_uri' => 'https://www.my.gov.sa/',
    'field_source_link_title' => 'my.gov.sa',
    'field_source_text' => 'Service directory references',
    'meta_title' => 'Muqeem iqama & visa services: employer workflows with Qiwa, Absher, GOSI',
    'meta_description' => 'Muqeem deep dive for HR and mobility: iqama lifecycle, exit/re-entry, how Qiwa contracts and GOSI status gate transactions, Absher employee steps, Mudad payroll timing, and official directory links.',
    'meta_keywords' => 'Muqeem Saudi Arabia, iqama renewal online, work visa KSA, exit re-entry visa Saudi Arabia, Muqeem Qiwa integration, establishment immigration compliance',
    'body_summary' => 'Muqeem powers establishment-side iqama and visa services. This summary stresses dependencies on Qiwa labour data, GOSI coverage, coordinated Absher authentications, and payroll timing via Mudad so mobility programmes do not stall at cutover.',
    'body' => '<p><strong>Muqeem</strong> is described in public materials as an online platform that helps establishments manage residency permits (<strong>iqama</strong>) and visa-related services. Government service directories (for example on <strong>my.gov.sa</strong>) list common transactions such as issuance, renewals, and exit/re-entry workflows.</p><p>Because immigration rules and fees change, treat this overview as non-binding context and confirm the latest requirements through official channels and your authorized service provider.</p><p>Muqeem-related processes typically intersect with <a href="/platforms/absher">Absher</a> for individual authentications and <a href="/platforms/qiwa">Qiwa</a> for sponsor-side labour records, while <a href="/platforms/gosi">GOSI</a> activation and <a href="/platforms/mudad">Mudad</a> salary uploads often gate onboarding milestones for new hires.</p><p>Construction and retail sponsors also align Muqeem mobilisation dates with <a href="/platforms/balady">Balady</a> site permits, and <a href="/platforms/misa">MISA</a>-licensed entities should keep visa profession codes consistent with licensed activities.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Muqeem is the establishment-facing digital channel used to issue, renew, print, and manage iqamas, work permits, exit/re-entry visas, and many dependent transactions tied to General Directorate of Passports rules.\n"
          . "Every request depends on upstream data quality: Qiwa contracts must reflect correct salary and job category, GOSI enrolment must be active where required, and establishments must hold valid commercial registration and municipal licences for certain visa categories.\n"
          . "Mobility teams track passport validity windows, medical fitness expiries, and sponsor quota consumption because a single lapsed document can cascade into flight bans or payroll holds.\n"
          . "Exit/re-entry, final exit, and dependent visa rules change with security directives—Muqeem UI labels do not replace reading the latest published fee tables and eligibility matrices.\n"
          . "Large EPC contractors batch hundreds of Muqeem transactions monthly; they implement maker-checker workflows, segregated API keys where licensed, and nightly reconciliation against HRIS headcount.\n"
          . "Retail franchises delegate Muqeem powers to regional HR but retain audit trails because immigration violations can trigger establishment suspensions and bank WPS blocks.\n"
          . "Employees interact with Absher for personal authentications while sponsors act in Muqeem—miscommunication between the two sides delays boarding during Ramadan peaks and project go-lives.\n"
          . "Blue-collar accommodation providers sometimes need proof of housing registrations that municipalities expect; keep Balady documentation accessible when immigration asks for supporting context.\n"
          . "MISA-licensed entities hiring foreign experts should document skill justifications that align with visa profession codes selected in Muqeem to reduce classification pushback.\n"
          . "Insurance and client RFPs increasingly request Muqeem compliance attestations alongside GOSI certificates for mega-project mobilisation.\n"
          . "Data residency and export controls matter when mobility spreadsheets leave the Kingdom—apply least-privilege sharing and mask national IDs outside HR vaults.\n"
          . "Always confirm fees, service availability, and nationality-specific rules on official portals; this overview orients teams but is not immigration legal advice."
      ),
    'why_json' => [
      ['title' => 'Operational speed', 'body' => 'Electronic issuance and renewals reduce physical paperwork for high-volume mobility programs.', 'icon' => 'clock'],
      ['title' => 'Establishment control', 'body' => 'Sponsors can track employee mobility status and complete sponsor-side transactions where permitted.', 'icon' => 'shield'],
      ['title' => 'Auditability', 'body' => 'Digital records support internal controls and compliance reviews for mobility teams.', 'icon' => 'document'],
    ],
    'help_json' => [
      ['title' => 'Iqama lifecycle', 'body' => 'Issue, renew, and print iqama-related outputs according to current published procedures and establishment eligibility.', 'icon' => 'briefcase'],
      ['title' => 'Exit/re-entry', 'body' => 'Process travel permissions where applicable for sponsored employees, following updated rules and fees.', 'icon' => 'globe'],
      ['title' => 'Establishment administration', 'body' => 'Manage subscription and establishment-side settings required for continued access to services.', 'icon' => 'cog'],
    ],
    'steps_json' => [
      ['title' => 'Validate sponsorship data', 'body' => 'Ensure establishment records, contracts, and immigration statuses are aligned before submitting requests.'],
      ['title' => 'Submit through authorized channels', 'body' => 'Use official portals and approved workflows; avoid unofficial intermediaries for regulated transactions.'],
      ['title' => 'Close the loop with HR', 'body' => 'Update HR systems and notify employees when mobility documents change.'],
    ],
    'req_json' => [
      ['content' => 'Active establishment registration and authorized signatories for immigration transactions.'],
      ['content' => 'Valid passport and visa status data for each sponsored employee.'],
      ['content' => 'Payment readiness for government fees associated with selected services.'],
      ['content' => 'Compliance with updated residency and travel rules published by authorities.'],
      ['content' => 'Data privacy controls when exporting mobility records internally.'],
    ],
    'resources_json' => [
      ['title' => 'my.gov.sa', 'uri' => 'https://www.my.gov.sa/', 'link_title' => 'National portal', 'icon' => 'globe'],
      ['title' => 'ELM overview', 'uri' => 'https://www.elm.sa/en/our-business/digital-products', 'link_title' => 'ELM', 'icon' => 'document'],
    ],
    'path_alias' => '/platforms/muqeem',
  ]),

  _motaded_pi_row([
    'title' => 'GOSI',
    'field_short_description' => 'Saudi social insurance organisation: employer contributions, pensions, and occupational hazard schemes—see gosi.gov.sa for rates and services.',
    'field_subtitle' => 'General Organization for Social Insurance',
    'field_sector_name' => 'General',
    'field_category_name' => 'Operations',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Qiwa|Mudad|ZATCA|Absher|Muqeem|Balady',
    'field_external_link_uri' => 'https://www.gosi.gov.sa/',
    'field_external_link_title' => 'GOSI',
    'field_cta_link_uri' => 'https://cmsgosi.gosi.gov.sa/sites/en/AboutGOSI/Pages/default.aspx',
    'field_cta_link_title' => 'About GOSI (EN)',
    'field_cta_text' => 'Read about coverage',
    'field_source_link_uri' => 'https://my.gov.sa/en/agencies/17354',
    'field_source_link_title' => 'my.gov.sa agency profile',
    'field_source_text' => 'Agency summary (my.gov.sa)',
    'meta_title' => 'GOSI Saudi Arabia: social insurance contributions, compliance certificates & payroll links',
    'meta_description' => 'GOSI guide for CFOs and HR: contribution bases, hazard classes, certificates for tenders, reconciliation with Qiwa, Mudad wage files, ZATCA payroll VAT, Muqeem visa gates, official gosi.gov.sa.',
    'meta_keywords' => 'GOSI Saudi Arabia, social insurance contributions KSA, GOSI certificate compliance, employer GOSI registration, occupational hazard contribution, GOSI Qiwa Mudad alignment',
    'body_summary' => 'GOSI administers compulsory social insurance for much of the private sector. This page connects GOSI obligations to Qiwa payroll data, Mudad wage protection, ZATCA scrutiny of recharged management fees, and mobility gates in Muqeem.',
    'body' => '<p>The <strong>General Organization for Social Insurance (GOSI)</strong> is described in official summaries as an independent government organization implementing Saudi social insurance legislation. Public materials outline compulsory coverage for many private-sector Saudi employees and related governance arrangements.</p><p>Employers typically interact with GOSI for registration, contributions, and benefit administration processes that connect to payroll operations and other national compliance programs.</p><p>Operationally, contribution files should stay aligned with <a href="/platforms/qiwa">Qiwa</a> contracts, <a href="/platforms/mudad">Mudad</a> salary disbursements, and finance-led <a href="/platforms/zatca">ZATCA</a> VAT positions on recharged staff costs.</p><p><a href="/platforms/muqeem">Muqeem</a> visa issuance may stall when GOSI arrears exist, while <a href="/platforms/absher">Absher</a> sometimes supports employee-side evidence requests tied to benefits.</p><p>Always confirm contribution categories, wage bases, and filing deadlines using GOSI’s official portals and notices—this summary is not a substitute for legal advice.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "GOSI (General Organization for Social Insurance) implements Saudi Arabia’s compulsory social insurance schemes covering occupational hazards, pensions for Saudi employees, and related benefits with employer and employee contribution shares.\n"
          . "Contribution bases must mirror audited payroll: mismatches between GOSI wage files, Qiwa contracts, and Mudad salary transfers are red flags during labour inspections and bank covenant reviews.\n"
          . "Certificates of compliance from GOSI unlock government tenders, certain Balady licence renewals, and faster corporate banking KYC refresh cycles.\n"
          . "Finance controllers reconcile GOSI invoices monthly against general ledger accruals, especially where housing allowances, commission structures, or multi-entity secondments distort apparent wages.\n"
          . "GOSI registration is prerequisite data for many Qiwa transactions and feeds risk scores that HRSD publishes around establishment health.\n"
          . "Sector-specific hazard tariffs (construction, oil & gas, mining) materially change contribution rates—misclassification creates clawbacks plus interest.\n"
          . "Mergers and acquisitions teams inherit GOSI liabilities; due diligence extracts historical contribution files before signing SPA indemnities.\n"
          . "ZATCA auditors sometimes cross-check VAT on management fees recharged between group entities against GOSI headcount to detect fictitious intercompany services.\n"
          . "Mudad wage protection success rates improve when GOSI IDs, bank IBANs, and employee identifiers are identical across all three datasets.\n"
          . "Absher-enabled employees may need updated GOSI-linked salary evidence when disputing benefit calculations in labour forums.\n"
          . "Muqeem visa quotas can pause if GOSI arrears exist; treasury should treat GOSI cash calls with the same priority as customs duty payments.\n"
          . "Always download the latest contribution schedules and digital service manuals from gosi.gov.sa—this text orients executives but does not replace actuarial or legal counsel."
      ),
    'why_json' => [
      ['title' => 'Worker protection', 'body' => 'Social insurance programs underpin financial protection for covered employees and their families.', 'icon' => 'shield'],
      ['title' => 'Employer compliance', 'body' => 'Contribution accuracy reduces penalties and supports eligibility for government and banking workflows.', 'icon' => 'check'],
      ['title' => 'Benefits administration', 'body' => 'Structured processes help HR teams manage occupational hazard, pension, and related schemes where applicable.', 'icon' => 'users'],
    ],
    'help_json' => [
      ['title' => 'Registration and contributions', 'body' => 'Register eligible employees and remit contributions according to published wage bases and schedules.', 'icon' => 'briefcase'],
      ['title' => 'Certificates and statements', 'body' => 'Generate compliance statements needed for tenders, banking, and government services when required.', 'icon' => 'document'],
      ['title' => 'Support channels', 'body' => 'Use official GOSI help resources for disputes, refunds, and account corrections.', 'icon' => 'info'],
    ],
    'steps_json' => [
      ['title' => 'Classify employees', 'body' => 'Map workforce segments to applicable GOSI schemes based on nationality, contract type, and establishment category.'],
      ['title' => 'Integrate payroll', 'body' => 'Automate contribution calculations and validate monthly files before submission deadlines.'],
      ['title' => 'Reconcile and audit', 'body' => 'Maintain month-to-month reconciliation between payroll, GOSI statements, and finance approvals.'],
    ],
    'req_json' => [
      ['content' => 'Accurate payroll data and employee classifications for contribution calculations.'],
      ['content' => 'Authorized finance and HR signatories for registration changes.'],
      ['content' => 'Banking arrangements compatible with national wage protection processes.'],
      ['content' => 'Retention of contribution evidence and benefit correspondence.'],
      ['content' => 'Monitoring of regulatory updates affecting contribution bases or exemptions.'],
    ],
    'resources_json' => [
      ['title' => 'GOSI', 'uri' => 'https://www.gosi.gov.sa/', 'link_title' => 'gosi.gov.sa', 'icon' => 'globe'],
      ['title' => 'About GOSI (CMS)', 'uri' => 'https://cmsgosi.gosi.gov.sa/sites/en/AboutGOSI/Pages/default.aspx', 'link_title' => 'About', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/gosi',
  ]),

  _motaded_pi_row([
    'title' => 'Absher',
    'field_short_description' => 'Ministry of Interior digital services: civil affairs, passports, traffic, and mobility—use the official Absher portal for current transactions.',
    'field_subtitle' => 'Ministry of Interior',
    'field_sector_name' => 'General',
    'field_category_name' => 'Operations',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Muqeem|Qiwa|GOSI|Balady|ZATCA|Mudad',
    'field_external_link_uri' => 'https://www.absher.sa/',
    'field_external_link_title' => 'Absher portal',
    'field_cta_link_uri' => 'https://www.absher.sa/',
    'field_cta_link_title' => 'Access Absher',
    'field_cta_text' => 'Go to services',
    'field_source_link_uri' => 'https://us.saudiembassy.sa/en/eServices/Pages/Absher.aspx',
    'field_source_link_title' => 'Embassy overview (context)',
    'field_source_text' => 'Secondary reference',
    'meta_title' => 'Absher MOI Saudi Arabia: digital services, traffic, civil affairs & Muqeem coordination',
    'meta_description' => 'Absher overview: Ministry of Interior e-services, authentication, traffic and civil records, how residents pair Absher with Muqeem sponsors, Qiwa HR data, GOSI, Balady fines, ZATCA travel evidence, Mudad mobile KYC.',
    'meta_keywords' => 'Absher Saudi Arabia, Ministry of Interior digital services, Absher traffic fines, civil affairs Absher, Absher Muqeem travel permission, Nafath authentication KSA',
    'body_summary' => 'Absher delivers a wide MOI service catalog to individuals and businesses. This page highlights coordination points with Muqeem immigration, Qiwa labour records, GOSI benefits evidence, municipal enforcement, and payroll mobile number hygiene for Mudad.',
    'body' => '<p><strong>Absher</strong> is described by Saudi government materials as a digital services platform of the <strong>Ministry of Interior</strong>, providing access to a broad catalog of services for citizens, residents, and visitors. Public references commonly highlight passports, traffic, civil affairs, and mobility-related transactions.</p><p>Because Absher integrates with other national systems, many employer-related journeys combine Absher steps with <a href="/platforms/muqeem">Muqeem</a> sponsor actions and HR workflows in <a href="/platforms/qiwa">Qiwa</a>.</p><p>Fleet, facilities, and compliance leads also connect Absher traffic data with <a href="/platforms/balady">Balady</a> municipal enforcement, while finance may archive travel histories that support <a href="/platforms/zatca">ZATCA</a> customs narratives.</p><p><a href="/platforms/gosi">GOSI</a> benefit cases and <a href="/platforms/mudad">Mudad</a> wage OTP flows frequently depend on mobile numbers maintained inside Absher profiles—keep HR master data audits synchronized.</p><p>Service availability and eligibility rules change—verify the latest guidance on the official Absher portal before completing regulated transactions.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Absher is the Ministry of Interior’s omnichannel digital government front-end—web, iOS, and Android—covering civil affairs, passports, traffic violations, appointments, and many mobility workflows that complement establishment actions in Muqeem.\n"
          . "Citizens and residents authenticate through national identity factors (including Nafath where applicable) before accessing sensitive records; security teams should harden device policies because Absher sessions touch highly regulated personal data.\n"
          . "Families manage dependent iqama renewals, travel permissions, and address registrations inside Absher while sponsors still execute payment-heavy steps inside Muqeem—both sides must coordinate SMS OTP timing and data entry.\n"
          . "Traffic fines, vehicle registration, and accident reports link to insurance telematics strategies that fleet operators reconcile with HR driver policies.\n"
          . "Employer security departments use Absher corporate features where available to monitor fleet compliance and employee driving eligibility for company cars.\n"
          . "Border-crossing history exports sometimes support ZATCA customs audits proving temporary export/re-import of demo equipment or exhibition booths.\n"
          . "Balady-linked violations (illegal signage, public nuisance fines) can appear alongside Interior records in broader municipality enforcement sweeps.\n"
          . "GOSI disability or occupational hazard claims occasionally require Absher-stamped civil status extracts as supporting evidence.\n"
          . "Qiwa-related labour disputes may reference Absher-printed travel histories when employees allege illegal retention of passports—policy-wise, custody rules still follow written law, not app screenshots alone.\n"
          . "Mudad wage delays sometimes correlate with blocked Absher services if mobile numbers are stale—HR should run quarterly contact audits.\n"
          . "International assignees should preload Absher before airport transits because offline kiosk availability varies by terminal and season.\n"
          . "Service menus change frequently—verify each transaction path on absher.sa and official MOI notices rather than cached screenshots from consultants."
      ),
    'why_json' => [
      ['title' => 'Citizen-centric delivery', 'body' => 'Digital channels reduce in-person visits for high-frequency government transactions.', 'icon' => 'users'],
      ['title' => 'Security posture', 'body' => 'Strong authentication expectations help protect sensitive identity and mobility data.', 'icon' => 'shield'],
      ['title' => 'Broad catalog', 'body' => 'A wide service surface supports households and businesses across multiple life events.', 'icon' => 'list_check'],
    ],
    'help_json' => [
      ['title' => 'Identity and civil records', 'body' => 'Access national ID, civil status, and registry services where your profile is eligible.', 'icon' => 'document'],
      ['title' => 'Mobility services', 'body' => 'Complete travel permission and dependent services consistent with residency status.', 'icon' => 'globe'],
      ['title' => 'Traffic and public safety', 'body' => 'Manage violations, licensing, and related transactions published under Interior policies.', 'icon' => 'map_pin'],
    ],
    'steps_json' => [
      ['title' => 'Authenticate safely', 'body' => 'Use official Absher channels and national identity verification (e.g., Nafath) as required.'],
      ['title' => 'Select the correct service', 'body' => 'Choose the exact transaction to avoid partial submissions and rework.'],
      ['title' => 'Archive confirmations', 'body' => 'Store digital receipts and reference numbers for HR and audit trails.'],
    ],
    'req_json' => [
      ['content' => 'Eligible national ID / iqama and a verified Absher account.'],
      ['content' => 'Accurate mobile number and authentication factors for OTP workflows.'],
      ['content' => 'Supporting documents per service checklist (varies by transaction).'],
      ['content' => 'Employer authorization where business actions are performed on behalf of employees.'],
      ['content' => 'Compliance with privacy rules when exporting screenshots or PDFs internally.'],
    ],
    'resources_json' => [
      ['title' => 'Absher', 'uri' => 'https://www.absher.sa/', 'link_title' => 'absher.sa', 'icon' => 'globe'],
    ],
    'path_alias' => '/platforms/absher',
  ]),

  _motaded_pi_row([
    'title' => 'Balady',
    'field_short_description' => 'National municipal platform for commercial licences, construction permits, and service requests—check balady.gov.sa for your city.',
    'field_subtitle' => 'Ministry of Municipalities and Housing',
    'field_sector_name' => 'Construction & Infrastructure',
    'field_category_name' => 'Licensing',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Saudi Business Center (Meras)|ZATCA|MISA (Ministry of Investment Saudi Arabia)|Qiwa|GOSI|Muqeem',
    'field_external_link_uri' => 'https://balady.gov.sa/en',
    'field_external_link_title' => 'Balady (EN)',
    'field_cta_link_uri' => 'https://balady.gov.sa/en/about-balady',
    'field_cta_link_title' => 'About Balady',
    'field_cta_text' => 'Learn about Balady',
    'field_source_link_uri' => 'https://balady.gov.sa/en/services/municipal-service-request',
    'field_source_link_title' => 'Municipal service request',
    'field_source_text' => 'Official service page',
    'meta_title' => 'Balady Saudi Arabia: municipal commercial licence, building permit & inspection playbook',
    'meta_description' => 'Balady deep dive: commercial licensing, construction permits, inspections, multi-city operations, links to ZATCA branch data, Qiwa addresses, Saudi Business Center (Meras) CR flows, MISA investments, GOSI site crews, Muqeem mobilisation.',
    'meta_keywords' => 'Balady Saudi Arabia, municipal commercial license KSA, building permit Saudi Arabia, Balady inspection, food business permit Balady, construction compliance Saudi municipalities',
    'body_summary' => 'Balady is the national municipal services platform. This expanded summary explains multi-city permit nuances, engineering evidence expectations, and how Balady ties to ZATCA branch truth, Qiwa establishment addresses, and investor journeys from MISA and the Saudi Business Center (Meras).',
    'body' => '<p><strong>Balady</strong> is described on the official portal as a national platform for municipal services, including <strong>commercial licensing</strong>, <strong>construction permits</strong>, and digital service requests. Public pages highlight large user and license volumes, reflecting broad adoption by businesses and residents.</p><p>Restaurants, retail, construction, and logistics operators frequently rely on Balady for operational permits and compliance with local municipal regulations.</p><p>Finance and tax teams should keep Balady branch addresses aligned with <a href="/platforms/zatca">ZATCA</a> registrations and <a href="/platforms/qiwa">Qiwa</a> establishment records, while investors sequence <a href="/platforms/misa">MISA</a> approvals, <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> CR milestones, and on-site mobilisation dates that depend on <a href="/platforms/muqeem">Muqeem</a> visas for construction crews covered by <a href="/platforms/gosi">GOSI</a> hazard tariffs.</p><p>SLAs and call-center hours are published on Balady—confirm current timelines before planning go-live dates.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Balady is the national municipal super-app and web portal for Saudi Arabia’s cities: commercial licences, temporary event permits, construction safety approvals, food truck routes, façade signage, and dozens of inspection-driven workflows.\n"
          . "Each municipality enforces local urban planning codes, so a permit approved in Riyadh does not automatically transfer to Jeddah or Dammam—multi-city operators maintain separate Balady project folders per branch.\n"
          . "Engineering stamps, soil reports, contractor CR validations, and insurance certificates are routinely uploaded; missing attachments bounce tickets and delay fit-out contractors already on daily rates.\n"
          . "Balady data should match ZATCA branch registrations and Qiwa establishment addresses; auditors cross-reference GPS pins on inspection reports against VAT invoice ship-to locations during sector probes.\n"
          . "Restaurants coordinate food safety inspections with HRSD hygiene expectations while still satisfying Balady occupancy and outdoor seating rules.\n"
          . "Construction giants sync Balady excavation permits with customs temporary import bonds managed through ZATCA channels when cranes or modular units arrive by sea.\n"
          . "Retail rollouts bundle Balady signage permits with landlord letters, shopping mall management approvals, and fire authority certificates before opening weekends.\n"
          . "Investors often start CR and licensing journeys at the Saudi Business Center (Meras) then fan out into Balady for site-specific obligations tied to their MISA-licensed activity.\n"
          . "Call-centre SLAs and digital payment wallets inside Balady shift seasonally—project managers should embed buffer weeks before marketing launch dates.\n"
          . "Municipal fines for illegal partitions or unlicensed outdoor storage can escalate to police referrals; compliance teams treat Balady notices with the same urgency as Mudad violation letters.\n"
          . "GIS layers for flood risk and heritage districts affect permit eligibility—consult municipal planning PDFs linked inside Balady service cards.\n"
          . "Always read the latest circular on balady.gov.sa for your city; hyperlocal nuance defeats one-size-fits-all checklists from consultants."
      ),
    'why_json' => [
      ['title' => 'Permit traceability', 'body' => 'Digital requests create auditable timelines for inspections, payments, and renewals.', 'icon' => 'document'],
      ['title' => 'Urban compliance', 'body' => 'Cadastral and land-use decisions align business operations with municipal planning rules.', 'icon' => 'building'],
      ['title' => 'Operational scale', 'body' => 'High transaction volumes demonstrate system maturity for multi-branch operators.', 'icon' => 'chart'],
    ],
    'help_json' => [
      ['title' => 'Commercial licensing', 'body' => 'Issue and renew commercial activity licenses required for regulated storefront and service operations.', 'icon' => 'briefcase'],
      ['title' => 'Construction permits', 'body' => 'Submit building, demolition, and occupancy workflows with required engineering attachments.', 'icon' => 'building'],
      ['title' => 'Service requests', 'body' => 'Track municipal service tickets and respond to inspector comments inside the portal.', 'icon' => 'cog'],
    ],
    'steps_json' => [
      ['title' => 'Map activities to permit types', 'body' => 'Translate your CR activities into the correct municipal permit categories before applying.'],
      ['title' => 'Prepare attachments', 'body' => 'Engineering drawings, leases, and safety documents are commonly required—use Balady checklists.'],
      ['title' => 'Operate renewals', 'body' => 'Calendar renewals for seasonal permits and construction milestones to avoid business interruptions.'],
    ],
    'req_json' => [
      ['content' => 'Valid commercial registration and correct activity mapping.'],
      ['content' => 'Engineering documents for construction-related permits where mandated.'],
      ['content' => 'Lease or ownership proof for premises-linked licenses.'],
      ['content' => 'Fees and insurance certificates when inspectors require them.'],
      ['content' => 'Arabic/English document readiness depending on municipality reviewer expectations.'],
    ],
    'resources_json' => [
      ['title' => 'Balady home', 'uri' => 'https://balady.gov.sa/en', 'link_title' => 'balady.gov.sa', 'icon' => 'globe'],
      ['title' => 'About Balady', 'uri' => 'https://balady.gov.sa/en/about-balady', 'link_title' => 'About', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/balady',
  ]),

  _motaded_pi_row([
    'title' => 'Mudad',
    'field_short_description' => 'HRSD-linked wage protection (WPS): payroll file submission and salary compliance via mudad.com.sa—follow HRSD notices.',
    'field_subtitle' => 'Ministry of Human Resources and Social Development',
    'field_sector_name' => 'General',
    'field_category_name' => 'Labor',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Qiwa|GOSI|ZATCA|Absher|Muqeem|MISA (Ministry of Investment Saudi Arabia)|Balady',
    'field_external_link_uri' => 'https://www.hrsd.gov.sa/en/ministry-services/services/wage-protection-file-upload-service',
    'field_external_link_title' => 'HRSD wage protection service',
    'field_cta_link_uri' => 'https://mudad.com.sa/',
    'field_cta_link_title' => 'Mudad portal',
    'field_cta_text' => 'Open Mudad',
    'field_source_link_uri' => 'https://www.hrsd.gov.sa/en/ministry-services/services/wage-protection-file-upload-service',
    'field_source_link_title' => 'Official HRSD page',
    'field_source_text' => 'WPS / wage protection (official)',
    'meta_title' => 'Mudad WPS Saudi Arabia: wage protection files, Qiwa payroll data & bank cut-offs',
    'meta_description' => 'Mudad wage protection guide: HRSD WPS file submission, reconciling Qiwa, GOSI, Muqeem IDs, ZATCA payroll VAT evidence, Absher OTP hygiene, MISA reporting, Balady site audits, official HRSD links.',
    'meta_keywords' => 'Mudad, WPS Saudi Arabia, wage protection system KSA, Mudad payroll file, HRSD wage compliance, Mudad Qiwa reconciliation, salary upload Saudi Arabia',
    'body_summary' => 'Mudad enforces wage protection through digital payroll files. This page explains data dependencies on Qiwa, GOSI, Muqeem, Absher OTP paths, ZATCA cross-checks, MISA job metrics, and Balady labour desk evidence.',
    'body' => '<p><strong>Mudad</strong> is referenced in <strong>HRSD</strong> public communications as a digital payroll compliance channel aligned with the <strong>Wage Protection System (WPS)</strong>. Establishments use it to submit payroll files, validate salary payments, and reduce non-compliance risk.</p><p>Mudad interacts with banking and social insurance ecosystems—plan end-to-end controls across <a href="/platforms/qiwa">Qiwa</a>, banks, and <a href="/platforms/gosi">GOSI</a> where applicable.</p><p>Identifiers must align with <a href="/platforms/muqeem">Muqeem</a> iqama data, while <a href="/platforms/absher">Absher</a> mobile authentication quality affects OTP success.</p><p>Finance should archive Mudad confirmations beside <a href="/platforms/zatca">ZATCA</a> VAT working papers, and mega-project teams may present Mudad logs during <a href="/platforms/balady">Balady</a> inspections or <a href="/platforms/misa">MISA</a> progress reviews.</p><p>Penalties and service blocks for non-compliance are described in third-party explainers; confirm enforcement details from HRSD primary sources.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Mudad is HRSD’s wage protection channel: establishments upload bank-validated payroll files so the Kingdom can monitor timely salary payments, reduce wage disputes, and enforce labour law digitally.\n"
          . "Each pay cycle must reconcile employee identifiers with Qiwa contracts, iqama numbers from Muqeem, GOSI wage bases, and bank IBANs—any mismatch rejects the file hours before payday.\n"
          . "Treasury teams coordinate cut-off calendars around Islamic holidays, Friday-only banking windows, and multi-currency assignee packages that still require SAR functional reporting.\n"
          . "Repeated failures trigger HRSD warnings, potential establishment blocks in related systems, and reputational damage during client audits for outsourced manpower.\n"
          . "ZATCA reviewers compare Mudad-confirmed payroll dates with VAT return periods when assessing input VAT on staff recharges between group service companies.\n"
          . "MISA reporting on job creation commitments sometimes asks for Mudad evidence proving sustained payroll after licence issuance.\n"
          . "Balady municipal inspectors on mega-sites may request Mudad logs alongside occupational health records to prove migrant worker wage integrity.\n"
          . "Absher mobile numbers must stay current because OTP paths for employee confirmations can stall wage releases.\n"
          . "Commission-only sales teams need documented formulas inside HR policies so Mudad basic wage lines remain defensible in labour courts.\n"
          . "Bank APIs increasingly push status webhooks into ERP systems—finance should design alert routing identical to treasury fraud alerts.\n"
          . "Partial payments, advance salary deductions, and leave-without-pay weeks each have nuanced file row treatments documented in HRSD technical manuals.\n"
          . "Treat Mudad as a monthly financial control, not an HR afterthought—CFO sign-off should accompany every upload batch."
      ),
    'why_json' => [
      ['title' => 'Salary integrity', 'body' => 'File-based validation reduces missed or delayed salary payments for covered employees.', 'icon' => 'shield'],
      ['title' => 'Bank integration', 'body' => 'Standardized payroll files streamline transfers and reconciliation across banks.', 'icon' => 'link'],
      ['title' => 'Audit readiness', 'body' => 'Digital artifacts support HR and finance audits during inspections and tenders.', 'icon' => 'document'],
    ],
    'help_json' => [
      ['title' => 'WPS file submission', 'body' => 'Generate and upload payroll files in formats mandated by HRSD notices.', 'icon' => 'briefcase'],
      ['title' => 'Payroll automation', 'body' => 'Connect HRIS/payroll engines to Mudad to reduce manual errors and missed cutoffs.', 'icon' => 'cog'],
      ['title' => 'Violation remediation', 'body' => 'Track warnings and remediate root causes with finance and HR joint ownership.', 'icon' => 'info'],
    ],
    'steps_json' => [
      ['title' => 'Activate establishment payroll', 'body' => 'Complete onboarding steps required after iqama issuance and first payroll cycle.'],
      ['title' => 'Validate monthly files', 'body' => 'Run pre-submission checks for employee IDs, amounts, and bank mappings.'],
      ['title' => 'Close accounting loops', 'body' => 'Reconcile bank debits, Mudad confirmations, and payslips each month.'],
    ],
    'req_json' => [
      ['content' => 'Bank accounts approved for WPS salary transfers.'],
      ['content' => 'Accurate employee identifiers aligned with Qiwa and immigration records.'],
      ['content' => 'Payroll calendars aligned with HRSD cutoffs and public holidays.'],
      ['content' => 'Segregation of duties between HR and finance for file approvals.'],
      ['content' => 'Retention of WPS evidence for dispute resolution and audits.'],
    ],
    'resources_json' => [
      ['title' => 'HRSD wage protection', 'uri' => 'https://www.hrsd.gov.sa/en/ministry-services/services/wage-protection-file-upload-service', 'link_title' => 'HRSD', 'icon' => 'globe'],
      ['title' => 'Mudad', 'uri' => 'https://mudad.com.sa/', 'link_title' => 'mudad.com.sa', 'icon' => 'briefcase'],
    ],
    'path_alias' => '/platforms/mudad',
  ]),

  _motaded_pi_row([
    'title' => 'Saudi Business Center (Meras)',
    'field_short_description' => 'Government one-stop business services (branches + business.sa): CR and related procedures—confirm current service owners on official portals.',
    'field_subtitle' => 'Saudi Business Center (business.sa)',
    'field_sector_name' => 'General',
    'field_category_name' => 'Investment',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'MISA (Ministry of Investment Saudi Arabia)|ZATCA|Balady|Qiwa|GOSI|Mudad|Muqeem|Absher',
    'field_external_link_uri' => 'https://business.sa/',
    'field_external_link_title' => 'business.sa',
    'field_cta_link_uri' => 'https://my.gov.sa/en/agencies/17891',
    'field_cta_link_title' => 'my.gov.sa agency profile',
    'field_cta_text' => 'View agency profile',
    'field_source_link_uri' => 'https://my.gov.sa/en/agencies/17891',
    'field_source_link_title' => 'Official summary (my.gov.sa)',
    'field_source_text' => 'Government directory',
    'meta_title' => 'Saudi Business Center business.sa: CR services, Meras & hand-off to ZATCA Qiwa GOSI',
    'meta_description' => 'Saudi Business Center guide: company formation desks, digital business.sa flows, how CR milestones connect to MISA licences, ZATCA VAT, Qiwa, GOSI, Mudad payroll, Balady permits, Muqeem, Absher, official directories.',
    'meta_keywords' => 'Saudi Business Center, business.sa, Meras Saudi Arabia, commercial registration KSA, company formation Saudi Arabia, SBC ZATCA onboarding, one stop shop business Saudi',
    'body_summary' => 'The Saudi Business Center consolidates business-facing services online and in branches. This extended overview explains sequencing into ZATCA, Qiwa, GOSI, Mudad, Balady, and mobility platforms so post-CR compliance does not stall first revenue.',
    'body' => '<p>The <strong>Saudi Business Center</strong> is described in official directory materials as a government entity facilitating procedures for starting, conducting, and terminating businesses. Public summaries reference a nationwide branch footprint and a digital presence at <strong>business.sa</strong>.</p><p>The center is often discussed alongside the broader <strong>Meras</strong> initiative to improve business environment metrics and consolidate service journeys that were historically fragmented.</p><p>After CR milestones, most operators continue with <a href="/platforms/zatca">ZATCA</a> tax registration, <a href="/platforms/qiwa">Qiwa</a> establishment activation, <a href="/platforms/gosi">GOSI</a> enrolment, <a href="/platforms/mudad">Mudad</a> payroll banking, <a href="/platforms/balady">Balady</a> municipal permits, and <a href="/platforms/misa">MISA</a>-driven investment reporting where applicable.</p><p>Mobilising talent then flows through <a href="/platforms/muqeem">Muqeem</a> and <a href="/platforms/absher">Absher</a> with coordinated HR policies.</p><p>Investors should cross-check service ownership (which entity hosts a given transaction today) using business.sa and national portals before planning milestones.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "The Saudi Business Center (business.sa) is the government-led one-stop-shop layer that bundles commercial registration services, notary-adjacent filings, and hand-offs to sector regulators so founders spend fewer days visiting ministries in person.\n"
          . "Branches across the Kingdom offer ticketed queues for SMEs, while enterprise desks sometimes coordinate multi-agency war rooms for mega-investments already approved in principle by MISA.\n"
          . "Digital journeys on business.sa increasingly mirror in-branch steps—upload articles of association, capital deposit evidence, chamber of commerce memberships, and foreign investment licence PDFs before payment cart checkout.\n"
          . "After CR issuance, teams immediately pivot to ZATCA VAT and FATOORA onboarding, Qiwa establishment activation, GOSI registration, and Balady municipal licences for the first physical site.\n"
          . "Bank relationship managers often sit inside Business Center campuses to open operating accounts tied to future Mudad wage files.\n"
          . "Legalisation and translation vendors cluster nearby; budget time for apostilles when foreign parent entities inject capital or intellectual property.\n"
          . "Merger filings, branch rollups, and liquidation procedures each have dedicated service codes—wrong queue selection loses the appointment slot.\n"
          . "Chamber of commerce certificates generated here feed tender portals and supplier onboarding questionnaires for Aramco-style vendor IDs.\n"
          . "Women-led and rural entrepreneurship programmes sometimes publish priority slots; monitor official social channels for quota announcements.\n"
          . "Digital signatures via national ID cards reduce paper, but hardware token expiry surprises teams on Sunday mornings—maintain spare tokens.\n"
          . "International counsel should map Business Center outputs to home-country GAAP reporting because Arabic trade names may differ from English invoice branding audited by ZATCA.\n"
          . "Treat business.sa and my.gov.sa agency profiles as living documents—service ownership shifts between ministries as Vision 2030 reforms continue."
      ),
    'why_json' => [
      ['title' => 'One-stop journeys', 'body' => 'Co-located services reduce physical trips across ministries for common setup tasks.', 'icon' => 'target'],
      ['title' => 'Time and cost', 'body' => 'Published objectives emphasize faster turnaround and fewer hidden steps for businesses.', 'icon' => 'clock'],
      ['title' => 'Investor experience', 'body' => 'Designed to improve service quality metrics relevant to FDI and SME growth.', 'icon' => 'star'],
    ],
    'help_json' => [
      ['title' => 'Business registration', 'body' => 'Navigate commercial registration and related approvals with guided checklists.', 'icon' => 'briefcase'],
      ['title' => 'Licensing coordination', 'body' => 'Connect municipal and sector regulators through consolidated workflows where available.', 'icon' => 'building'],
      ['title' => 'Investor support', 'body' => 'Access help desks and escalation paths for complex multi-agency transactions.', 'icon' => 'users'],
    ],
    'steps_json' => [
      ['title' => 'Book the right service lane', 'body' => 'Choose branch vs digital services based on transaction type and document readiness.'],
      ['title' => 'Prepare a document bundle', 'body' => 'Combine CR, articles, leases, and sector approvals in the order reviewers expect.'],
      ['title' => 'Track downstream compliance', 'body' => 'After issuance, register follow-on obligations in tax, labor, and municipal systems.'],
    ],
    'req_json' => [
      ['content' => 'Commercial registration artifacts and ownership documents.'],
      ['content' => 'Sector-specific approvals for regulated activities.'],
      ['content' => 'Municipal prerequisites for location-based licenses.'],
      ['content' => 'Payment instruments for combined fee schedules.'],
      ['content' => 'Interpreter/legal support for cross-border founders when needed.'],
    ],
    'resources_json' => [
      ['title' => 'business.sa', 'uri' => 'https://business.sa/', 'link_title' => 'business.sa', 'icon' => 'globe'],
      ['title' => 'my.gov.sa profile', 'uri' => 'https://my.gov.sa/en/agencies/17891', 'link_title' => 'Agency profile', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/saudi-business-center-meras',
  ]),

  _motaded_pi_row([
    'title' => 'FASAH',
    'field_short_description' => 'National trade data exchange for customs and logistics—electronic import/export filings via fasah.sa.',
    'field_subtitle' => 'Customs clearance and trade data exchange',
    'field_sector_name' => 'Logistics & Transport',
    'field_category_name' => 'Logistics',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'ZATCA|SABER|Etimad',
    'field_external_link_uri' => 'https://www.fasah.sa/trade/sau/html/en_US/subpage-about.html',
    'field_external_link_title' => 'FASAH',
    'field_cta_link_uri' => 'https://fasah.sa/',
    'field_cta_link_title' => 'FASAH services',
    'field_cta_text' => 'Visit FASAH',
    'field_source_link_uri' => 'https://www.fasah.sa/trade/sau/html/en_US/subpage-about.html',
    'field_source_link_title' => 'About FASAH',
    'field_source_text' => 'Public materials',
    'meta_title' => 'FASAH: Saudi customs data exchange, ports & trade compliance',
    'meta_description' => 'Logistics overview of FASAH: electronic customs data, coordination with ZATCA, SABER product compliance, and Etimad-linked procurement shipments.',
    'meta_keywords' => 'FASAH Saudi Arabia, customs data exchange KSA, import export electronic filing, FASAH ZATCA, trade compliance Saudi ports',
    'body_summary' => 'FASAH underpins electronic exchange of import and export data between public and private parties. This page explains how FASAH interacts with ZATCA customs values, SABER certificates, and Etimad-driven procurement timelines.',
    'body' => '<p><strong>FASAH</strong> is described in official summaries as a nationwide initiative to exchange import and export data electronically between relevant government and private-sector parties, supporting customs and port processes.</p><p>Importers often sequence FASAH filings with <a href="/platforms/zatca">ZATCA</a> declarations and <a href="/platforms/saber">SABER</a> product conformity evidence, while capital projects funded through <a href="/platforms/etimad">Etimad</a> contracts align shipping milestones with payment triggers.</p><p>Confirm the latest technical specifications and port coverage on <a href="https://fasah.sa/">fasah.sa</a>.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Master data for HS codes, weights, and incoterms must match across FASAH, insurer certificates, and finance accruals or demurrage disputes multiply.\n"
          . "Free-zone and bonded warehouse movements need extra document bundles—coordinate with warehouse WMS timestamps versus customs release messages.\n"
          . "Oilfield services companies often batch hundreds of line items per vessel; use automated validation rules before submission windows close.\n"
          . "Retail fashion importers should tie SABER Product Certificate of Conformity numbers to FASAH declarations to avoid inspection holds at air hubs.\n"
          . "Post-audit, retain message-level acknowledgements from FASAH APIs alongside carrier bills of lading for seven-year evidence policies.\n"
          . "Operational guidance only—follow Saudi Customs and FASAH operator publications for legal obligations."
      ),
    'why_json' => [
      ['title' => 'Electronic exchange', 'body' => 'Structured messages replace repetitive paper handoffs between traders and agencies.', 'icon' => 'link'],
      ['title' => 'Port coverage', 'body' => 'Nationwide rollout supports major import and export corridors.', 'icon' => 'globe'],
      ['title' => 'Compliance speed', 'body' => 'Validated data reduces avoidable inspection loops when documents align.', 'icon' => 'clock'],
    ],
    'help_json' => [
      ['title' => 'Submit declarations', 'body' => 'File import/export datasets through certified service providers or in-house integrations.', 'icon' => 'document'],
      ['title' => 'Monitor releases', 'body' => 'Track customs statuses and respond to queries with corrected payloads quickly.', 'icon' => 'cog'],
      ['title' => 'Coordinate partners', 'body' => 'Align forwarders, insurers, and banks on a single shipment truth.', 'icon' => 'users'],
    ],
    'steps_json' => [
      ['title' => 'Validate master data', 'body' => 'Check CR, HS codes, and weights against supplier invoices before first submission.'],
      ['title' => 'Transmit electronically', 'body' => 'Send FASAH messages and attach certificates required for the commodity class.'],
      ['title' => 'Reconcile finance', 'body' => 'Match customs duties and VAT accruals with treasury payments the same week.'],
    ],
    'req_json' => [
      ['content' => 'Authorised broker or in-house integration certified for FASAH messaging.'],
      ['content' => 'Commercial invoice, packing list, and transport documents in accepted formats.'],
      ['content' => 'Product conformity references from SABER where regulations mandate PCoC/SCoC.'],
      ['content' => 'Insurance certificates aligned with Incoterms selected on the declaration.'],
    ],
    'resources_json' => [
      ['title' => 'fasah.sa', 'uri' => 'https://fasah.sa/', 'link_title' => 'fasah.sa', 'icon' => 'globe'],
      ['title' => 'Trader handbook', 'uri' => 'https://fasah.sa/en/help', 'link_title' => 'Handbook', 'icon' => 'document'],
      ['title' => 'Services', 'uri' => 'https://fasah.sa/en/services', 'link_title' => 'Services', 'icon' => 'list_check'],
      ['title' => 'FAQs', 'uri' => 'https://fasah.sa/en/help/FAQ', 'link_title' => 'FAQs', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/fasah',
  ]),

  _motaded_pi_row([
    'title' => 'SABER',
    'field_short_description' => 'SASO product conformity platform: register products and obtain certificates under the Saudi Product Safety Programme—saber.sa.',
    'field_subtitle' => 'Product registration and conformity certificates',
    'field_sector_name' => 'Logistics & Transport',
    'field_category_name' => 'Logistics',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'FASAH|ZATCA|Balady',
    'field_external_link_uri' => 'https://saber.sa/',
    'field_external_link_title' => 'SABER',
    'field_cta_link_uri' => 'https://saber.sa/home/aboutsaber',
    'field_cta_link_title' => 'About SABER',
    'field_cta_text' => 'About SABER',
    'field_source_link_uri' => 'https://saber.sa/home/aboutsaber',
    'field_source_link_title' => 'SASO: SABER',
    'field_source_text' => 'Saudi Standards, Metrology and Quality Organization',
    'meta_title' => 'SABER: SASO product registration & conformity certificates (Saudi market)',
    'meta_description' => 'Importer guide to SABER: Technical Regulations, Shipment Certificates of Conformity, links to FASAH customs clearance, ZATCA valuation evidence, and Balady retail licensing.',
    'meta_keywords' => 'SABER Saudi Arabia, SASO conformity certificate, saber.sa, product registration KSA, SABER FASAH, Saudi Product Safety Programme',
    'body_summary' => 'SABER digitises product and establishment registration for conformity requirements under the Saudi Product Safety Programme. This page connects SABER steps to FASAH customs messages, ZATCA invoice descriptions, and Balady retail compliance.',
    'body' => '<p><strong>SABER</strong> is described as the electronic platform enabling beneficiaries to register establishments and consumer products to obtain certificates required under the <strong>Saudi Product Safety Programme</strong>, administered by <strong>SASO</strong>.</p><p>Importers typically align SABER certificate numbers with <a href="/platforms/fasah">FASAH</a> declarations, ensure invoice line descriptions satisfy <a href="/platforms/zatca">ZATCA</a> audit expectations, and confirm that retail SKUs match <a href="/platforms/balady">Balady</a> licensed activities.</p><p>Always verify applicable Technical Regulations on <a href="https://saber.sa/">saber.sa</a> before shipping.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "High-SKU distributors should automate Technical Regulation mapping tables because a single misclassified HS line blocks entire containers at red-channel inspections.\n"
          . "Renewal calendars for Shipment Certificates of Conformity must align with marketing launch dates especially for seasonal consumer electronics.\n"
          . "Private-label brands need traceable factory audit packs uploaded to SABER attachments to pass subsequent SASO market surveillance visits.\n"
          . "E-commerce fulfilment centres should reconcile SABER product IDs with WMS barcodes to avoid pick-line substitutions that invalidate certificates.\n"
          . "Legal should archive bilingual datasheets because dispute forums may request Arabic originals alongside English marketing claims.\n"
          . "Guidance only—follow SASO notices for mandatory standards."
      ),
    'why_json' => [
      ['title' => 'Market safety', 'body' => 'Digital registration improves traceability of regulated products entering Saudi Arabia.', 'icon' => 'shield'],
      ['title' => 'Faster clearance', 'body' => 'Pre-validated conformity data reduces customs friction when integrated with FASAH.', 'icon' => 'clock'],
      ['title' => 'Importer accountability', 'body' => 'Establishment-level records clarify who owns compliance for each SKU.', 'icon' => 'building'],
    ],
    'help_json' => [
      ['title' => 'Register products', 'body' => 'Create product profiles and select applicable Technical Regulations.', 'icon' => 'document'],
      ['title' => 'Book conformity bodies', 'body' => 'Engage accredited bodies for required tests and certificate issuance.', 'icon' => 'check'],
      ['title' => 'Ship with evidence', 'body' => 'Attach SCoC/PCoC references to customs and finance packages.', 'icon' => 'globe'],
    ],
    'steps_json' => [
      ['title' => 'Classify SKUs', 'body' => 'Map each SKU to HS codes and Technical Regulations before first import.'],
      ['title' => 'Obtain certificates', 'body' => 'Complete testing and upload evidence to SABER for approval.'],
      ['title' => 'Link to logistics', 'body' => 'Push certificate numbers to FASAH and warehouse systems before vessel arrival.'],
    ],
    'req_json' => [
      ['content' => 'Importer establishment registered on SABER with authorised users.'],
      ['content' => 'Test reports from SASO-recognised laboratories where mandated.'],
      ['content' => 'Arabic labels and user manuals when standards require local language.'],
      ['content' => 'Retention of certificate versions superseded by regulatory updates.'],
    ],
    'resources_json' => [
      ['title' => 'saber.sa', 'uri' => 'https://saber.sa/', 'link_title' => 'saber.sa', 'icon' => 'globe'],
      ['title' => 'Conformity steps', 'uri' => 'https://saber.sa/Help', 'link_title' => 'Help', 'icon' => 'shield'],
      ['title' => 'Technical regulations', 'uri' => 'https://saber.sa/Regulations', 'link_title' => 'Regulations', 'icon' => 'document'],
      ['title' => 'FAQs', 'uri' => 'https://saber.sa/Help/FAQ', 'link_title' => 'FAQs', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/saber',
  ]),

  _motaded_pi_row([
    'title' => 'Etimad',
    'field_short_description' => 'Integrated Ministry of Finance portals for government tenders, contracts, budgets, and digital payments—see etimad.sa and portal.etimad.sa.',
    'field_subtitle' => 'Government procurement and finance services',
    'field_sector_name' => 'Finance & Fintech',
    'field_category_name' => 'Finance',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'ZATCA|MISA (Ministry of Investment Saudi Arabia)|FASAH',
    'field_external_link_uri' => 'https://etimad.sa/',
    'field_external_link_title' => 'Etimad',
    'field_cta_link_uri' => 'https://portal.etimad.sa/en-us',
    'field_cta_link_title' => 'Etimad portal',
    'field_cta_text' => 'Open portal',
    'field_source_link_uri' => 'https://www.mof.gov.sa/en/eservices/Pages/Etimad.aspx',
    'field_source_link_title' => 'MOF: Etimad',
    'field_source_text' => 'Ministry of Finance',
    'meta_title' => 'Etimad Saudi Arabia: government tenders, contracts, budgets & digital payments',
    'meta_description' => 'What Etimad covers: procurement portals, contract registration, budget workflows, and links to ZATCA, FASAH, and MISA for suppliers operating in KSA.',
    'meta_keywords' => 'Etimad Saudi Arabia, government tenders KSA, etimad.sa, Ministry of Finance procurement, portal.etimad, public sector contracts Saudi Arabia',
    'body_summary' => 'Etimad groups Ministry of Finance e-services for beneficiaries navigating tenders, contracts, budgets, and payments. This page orients suppliers and public entities and points to official portals.',
    'body' => '<p><strong>Etimad</strong> is described in public materials as an integrated set of portals providing Ministry of Finance automated services across procurement, contracts, budgets, and payments.</p><p>Suppliers typically intersect Etimad journeys with <a href="/platforms/zatca">ZATCA</a> VAT and invoicing rules, <a href="/platforms/fasah">FASAH</a> when goods clear customs against awarded import contracts, and <a href="/platforms/misa">MISA</a> when foreign investors participate in regulated sectors.</p><p>Always confirm portal-specific eligibility and authentication requirements on <a href="https://etimad.sa/">etimad.sa</a> and the live MOF notices.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Tender-to-pay cycles in Saudi Arabia increasingly require digital evidence: bid bonds, award letters, and milestone invoices should reconcile across Etimad, ERP finance modules, and tax filings.\n"
          . "Multi-entity groups should map which legal entity holds the commercial registration that signs framework agreements versus which subsidiary executes purchase orders.\n"
          . "Bank guarantee text must align with solicitation clauses; discrepancies delay award publication and can trigger automatic disqualification rules.\n"
          . "Contract registration steps often precede customs clearance authorisations for capital equipment—sequence cutover dates with logistics and FASAH teams.\n"
          . "Budget portal workflows differ for ministries, authorities, and project development companies—pick the correct beneficiary profile before uploading documents.\n"
          . "Payment and financial rights modules expect structured payroll or vendor master data consistent with ZATCA invoice references where VAT applies.\n"
          . "Internal audit should retain immutable exports of evaluation matrices and committee minutes because dispute windows reopen during supplier appeals.\n"
          . "This summary is operational context only; procurement law and MOF circulars prevail."
      ),
    'why_json' => [
      ['title' => 'Single MoF entry', 'body' => 'Integrated portals reduce fragmented paper approvals across procurement and finance.', 'icon' => 'globe'],
      ['title' => 'Transparent tenders', 'body' => 'Electronic RFPs, bids, and evaluations shorten cycle times when used end-to-end.', 'icon' => 'list_check'],
      ['title' => 'Payment discipline', 'body' => 'Digital claims and disbursement orders improve traceability for vendors and auditors.', 'icon' => 'document'],
    ],
    'help_json' => [
      ['title' => 'Find the right portal', 'body' => 'Match your beneficiary type to tenders, contracts, budgets, or payments modules before registering.', 'icon' => 'search'],
      ['title' => 'Prepare evidence packs', 'body' => 'Upload corporate documents, bank letters, and technical bids in the formats each solicitation specifies.', 'icon' => 'briefcase'],
      ['title' => 'Track status', 'body' => 'Monitor evaluation, award, and registration milestones inside the same workspace where objections are filed.', 'icon' => 'cog'],
    ],
    'steps_json' => [
      ['title' => 'Authenticate and enrol', 'body' => 'Complete national identity and organisation onboarding required for the selected portal.'],
      ['title' => 'Submit digitally', 'body' => 'File bids, contracts, or financial claims with attachments validated by the system.'],
      ['title' => 'Close the loop', 'body' => 'Archive award notices, PO numbers, and VAT references for downstream logistics and tax teams.'],
    ],
    'req_json' => [
      ['content' => 'Valid commercial registration and authorised signatories for the bidding entity.'],
      ['content' => 'Digital certificates and tokens required by the portal operator.'],
      ['content' => 'Bank instruments aligned with solicitation guarantee clauses.'],
      ['content' => 'Arabic/English document packs as mandated by each tender or contract template.'],
    ],
    'resources_json' => [
      ['title' => 'Etimad', 'uri' => 'https://etimad.sa/', 'link_title' => 'etimad.sa', 'icon' => 'globe'],
      ['title' => 'Tenders', 'uri' => 'https://tenders.etimad.sa/', 'link_title' => 'tenders.etimad.sa', 'icon' => 'search'],
      ['title' => 'Supplier handbook', 'uri' => 'https://etimad.sa/Help', 'link_title' => 'Help', 'icon' => 'document'],
      ['title' => 'MOF overview', 'uri' => 'https://www.mof.gov.sa/en/eservices/Pages/Etimad.aspx', 'link_title' => 'MOF', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/etimad',
  ]),

  _motaded_pi_row([
    'title' => 'Taqat',
    'field_short_description' => 'National employment portal (HRDF) connecting job seekers, employers, and training programmes—taqat.sa.',
    'field_subtitle' => 'National Employment Portal',
    'field_sector_name' => 'General',
    'field_category_name' => 'Labor',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Qiwa|Mudad|Saudi Business Center (Meras)',
    'field_external_link_uri' => 'https://www.taqat.sa/web/guest',
    'field_external_link_title' => 'TAQAT',
    'field_cta_link_uri' => 'https://eservices.taqat.sa/Eservices/AboutTaqat.aspx',
    'field_cta_link_title' => 'About Taqat',
    'field_cta_text' => 'Learn about Taqat',
    'field_source_link_uri' => 'https://eservices.taqat.sa/Eservices/AboutTaqat.aspx',
    'field_source_link_title' => 'HRDF: About Taqat',
    'field_source_text' => 'Human Resources Development Fund',
    'meta_title' => 'Taqat: Saudi national employment portal, jobs & HRDF programmes',
    'meta_description' => 'Employer and job-seeker overview of Taqat: hiring, training subsidies, links to Qiwa establishments, Mudad payroll, and Saudi Business Center onboarding.',
    'meta_keywords' => 'Taqat Saudi Arabia, national employment portal KSA, HRDF Taqat, jobs Saudi Arabia, training subsidies Taqat, Taqat Qiwa',
    'body_summary' => 'Taqat is Saudi Arabia’s national employment portal backed by HRDF, connecting vacancies, candidates, and programmes. This page links Taqat usage to Qiwa labour records, Mudad wage compliance, and broader business setup flows.',
    'body' => '<p><strong>Taqat</strong> is presented as Saudi Arabia’s national employment portal, helping job seekers and employers connect through digital services backed by the <strong>Human Resources Development Fund (HRDF)</strong>.</p><p>Employers typically keep establishment and contract data aligned in <a href="/platforms/qiwa">Qiwa</a>, run payroll through <a href="/platforms/mudad">Mudad</a> where applicable, and use <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> milestones when onboarding companies that scale hiring quickly.</p><p>Program availability changes—verify current announcements on taqat.sa and HRDF channels.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Recruiters should reconcile Taqat applicant profiles with active iqama and visa statuses maintained in Muqeem to avoid offers to candidates who cannot legally start on the proposed date.\n"
          . "Training subsidy approvals often require proof of Qiwa establishment health and Nitaqat colour bands—finance and HR should pull screenshots before grant submissions.\n"
          . "Graduate programmes may need coordination with academic calendars; delay posting until intake windows align with municipal ID services if relocations are involved.\n"
          . "Retail and hospitality seasonal hiring spikes should preload Taqat templates to publish dozens of roles without manual retyping errors.\n"
          . "Link Taqat analytics to internal diversity dashboards where boards track Saudization commitments tied to financing covenants.\n"
          . "This text is guidance only; programme rules on taqat.sa govern eligibility."
      ),
    'why_json' => [
      ['title' => 'National reach', 'body' => 'Centralised vacancy and candidate flows support nationwide hiring campaigns.', 'icon' => 'globe'],
      ['title' => 'Programmes', 'body' => 'Training and subsidy tracks help employers close skill gaps.', 'icon' => 'lightbulb'],
      ['title' => 'Digital hiring', 'body' => 'Online applications reduce friction versus fragmented offline channels.', 'icon' => 'users'],
    ],
    'help_json' => [
      ['title' => 'Publish roles', 'body' => 'Create structured job postings with accurate salary bands and contract types.', 'icon' => 'briefcase'],
      ['title' => 'Screen applicants', 'body' => 'Use portal filters and integrations to validate certifications before interviews.', 'icon' => 'search'],
      ['title' => 'Claim subsidies', 'body' => 'Follow HRDF guidance for training reimbursements tied to approved programmes.', 'icon' => 'document'],
    ],
    'steps_json' => [
      ['title' => 'Verify establishment data', 'body' => 'Align CR, Qiwa, and bank details before sponsoring programme seats.'],
      ['title' => 'Launch campaigns', 'body' => 'Publish roles or training seats and route responses to ATS workflows.'],
      ['title' => 'Measure hiring KPIs', 'body' => 'Track time-to-fill, conversion, and subsidy utilisation for leadership reporting.'],
    ],
    'req_json' => [
      ['content' => 'Active commercial registration and authorised HR signatories.'],
      ['content' => 'Accurate job descriptions mapped to national occupation codes where required.'],
      ['content' => 'Banking readiness for subsidy reimbursements or wage support schemes.'],
      ['content' => 'Privacy compliance when exporting candidate data to assessment vendors.'],
    ],
    'resources_json' => [
      ['title' => 'taqat.sa', 'uri' => 'https://taqat.sa/', 'link_title' => 'taqat.sa', 'icon' => 'globe'],
      ['title' => 'Job postings', 'uri' => 'https://taqat.sa/jobs', 'link_title' => 'Jobs', 'icon' => 'search'],
      ['title' => 'Training programmes', 'uri' => 'https://taqat.sa/programs', 'link_title' => 'Programmes', 'icon' => 'lightbulb'],
      ['title' => 'Help', 'uri' => 'https://taqat.sa/help', 'link_title' => 'Help', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/taqat',
  ]),

  _motaded_pi_row([
    'title' => 'Najiz',
    'field_short_description' => 'Ministry of Justice unified portal for courts and notary e-services—najiz.sa.',
    'field_subtitle' => 'Unified electronic judicial services',
    'field_sector_name' => 'General',
    'field_category_name' => 'Legal',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Saudi Business Center (Meras)|MISA (Ministry of Investment Saudi Arabia)',
    'field_external_link_uri' => 'https://najiz.sa/',
    'field_external_link_title' => 'Najiz',
    'field_cta_link_uri' => 'https://najiz.sa/applications/landing/about',
    'field_cta_link_title' => 'About Najiz',
    'field_cta_text' => 'Explore services',
    'field_source_link_uri' => 'https://najiz.sa/about',
    'field_source_link_title' => 'Najiz: About',
    'field_source_text' => 'Ministry of Justice',
    'meta_title' => 'Najiz: Saudi Ministry of Justice e-services for courts & notary public',
    'meta_description' => 'Overview of Najiz judicial digitisation, links to commercial registration checks via Sejel Tijari, MISA licensing context, and Saudi Business Center setup flows.',
    'meta_keywords' => 'Najiz Saudi Arabia, Ministry of Justice e-services, notary online KSA, najiz.sa, judicial services Saudi Arabia',
    'body_summary' => 'Najiz delivers unified electronic justice services for citizens, residents, and businesses. This page highlights how notarised outputs interact with CR updates, investment licensing, and one-stop business services.',
    'body' => '<p><strong>Najiz</strong> is described as the Ministry of Justice platform providing electronic access to court and notary services through a single portal for citizens, residents, and businesses.</p><p>Corporate legal teams often combine Najiz outputs with <a href="/platforms/sejel-tijari">Sejel Tijari</a> CR printouts, <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> formation bundles, and <a href="/platforms/misa">MISA</a> licence annexes when closing transactions.</p><p>Service availability evolves—verify each workflow on <a href="https://najiz.sa/">najiz.sa</a>.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Board resolutions and powers of attorney notarised in Najiz should use exact legal names matching ZATCA VAT certificates to avoid signature mismatches in bank KYC refreshes.\n"
          . "Cross-border M&A teams should calendar apostille steps because some foreign exhibits still require physical embassy stamps before electronic case filing.\n"
          . "Real estate developers linking mortgage releases to Balady occupancy certificates need sequential task lists so inspectors do not reject handovers for pending liens.\n"
          . "HR should route end-of-service settlement deeds through Najiz only after Mudad final payroll files post to prevent arithmetic disputes in labour forums.\n"
          . "Data minimisation matters when exporting case PDFs to external counsel—mask national IDs outside litigation need-to-know circles.\n"
          . "This overview is not legal advice; follow MOJ publications."
      ),
    'why_json' => [
      ['title' => 'Unified access', 'body' => 'One portal reduces fragmented visits across courts and notaries.', 'icon' => 'globe'],
      ['title' => 'Faster service', 'body' => 'Digital submissions shorten queues for routine judicial transactions.', 'icon' => 'clock'],
      ['title' => 'Paper reduction', 'body' => 'Electronic records support sustainability and audit trails.', 'icon' => 'document'],
    ],
    'help_json' => [
      ['title' => 'Notary services', 'body' => 'Complete notarisation and authentication steps required for corporate instruments.', 'icon' => 'scale'],
      ['title' => 'Court filings', 'body' => 'Submit and track cases according to published procedural rules.', 'icon' => 'briefcase'],
      ['title' => 'Evidence management', 'body' => 'Upload exhibits in the formats and sizes the portal specifies.', 'icon' => 'shield'],
    ],
    'steps_json' => [
      ['title' => 'Authenticate', 'body' => 'Use national identity verification as required for the selected service.'],
      ['title' => 'Submit documents', 'body' => 'Provide Arabic or bilingual packs where reviewers expect them.'],
      ['title' => 'Track outcomes', 'body' => 'Download digitally signed outputs and distribute to finance, HR, and regulators.'],
    ],
    'req_json' => [
      ['content' => 'Valid national ID or iqama for natural-person steps.'],
      ['content' => 'Corporate authorisations proving signatory powers for company actions.'],
      ['content' => 'Supporting contracts or board minutes where the service checklist mandates them.'],
      ['content' => 'Payment instruments for judicial fees when applicable.'],
    ],
    'resources_json' => [
      ['title' => 'najiz.sa', 'uri' => 'https://najiz.sa/', 'link_title' => 'najiz.sa', 'icon' => 'globe'],
      ['title' => 'Legal catalogue', 'uri' => 'https://najiz.sa/applications/landing/about', 'link_title' => 'Catalogue', 'icon' => 'document'],
      ['title' => 'Notary', 'uri' => 'https://najiz.sa/applications/notary', 'link_title' => 'Notary', 'icon' => 'shield'],
      ['title' => 'Help', 'uri' => 'https://najiz.sa/help', 'link_title' => 'Help', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/najiz',
  ]),

  _motaded_pi_row([
    'title' => 'Sejel Tijari',
    'field_short_description' => 'Ministry of Commerce commercial registration inquiry and CR services—sejel.mc.gov.sa.',
    'field_subtitle' => 'Commercial registration services',
    'field_sector_name' => 'General',
    'field_category_name' => 'Licensing',
    'field_region_name' => 'Saudi Arabia',
    'field_related_platforms_titles' => 'Saudi Business Center (Meras)|Najiz|MISA (Ministry of Investment Saudi Arabia)',
    'field_external_link_uri' => 'https://sejel.mc.gov.sa/',
    'field_external_link_title' => 'Sejel Tijari',
    'field_cta_link_uri' => 'https://sejel.mc.gov.sa/en/services/inquiry',
    'field_cta_link_title' => 'CR inquiry',
    'field_cta_text' => 'Inquire CR',
    'field_source_link_uri' => 'https://mc.gov.sa/Pages/crqe.aspx',
    'field_source_link_title' => 'Ministry of Commerce',
    'field_source_text' => 'MC e-services',
    'meta_title' => 'Sejel Tijari: Saudi commercial registration inquiry & Ministry of Commerce services',
    'meta_description' => 'How Sejel Tijari supports CR verification, due diligence, and links to Najiz notary flows, Saudi Business Center setup, and MISA investment records.',
    'meta_keywords' => 'Sejel Tijari, commercial registration inquiry Saudi Arabia, MC CR lookup, sejel.mc.gov.sa, CR verification KSA',
    'body_summary' => 'Sejel Tijari provides Ministry of Commerce access to commercial registration data and related services. This page explains practical uses for legal, finance, and onboarding teams alongside Najiz, MISA, and the Saudi Business Center.',
    'body' => '<p><strong>Sejel Tijari</strong> is described as the Ministry of Commerce channel for querying and managing commercial registration information and related administrative services.</p><p>Teams typically cross-check Sejel outputs with <a href="/platforms/najiz">Najiz</a> notarisations, <a href="/platforms/saudi-business-center-meras">Saudi Business Center (Meras)</a> formation milestones, and <a href="/platforms/misa">MISA</a> licensing artefacts before signing major contracts.</p><p>Confirm the latest service catalogue on <a href="https://mc.gov.sa/">mc.gov.sa</a>.</p>'
      . _motaded_pi_append_plain_paragraphs(
        "Procurement desks should snapshot CR status pages into tender binders because dynamic status flags change after capital increases or activity amendments.\n"
          . "Bank relationship managers often require CR printouts less than 30 days old—automate calendar reminders for refresh pulls from Sejel.\n"
          . "Franchisors onboarding master franchisees must verify branch CR numbers map to the correct legal entity that will sign royalty remittance agreements.\n"
          . "Due diligence bots scraping Sejel should rate-limit ethically to stay inside acceptable use policies published by MC.\n"
          . "English trade names on invoices must still match Arabic CR fields used in ZATCA onboarding to avoid reconciliation exceptions.\n"
          . "Guidance only; MC terms govern access."
      ),
    'why_json' => [
      ['title' => 'Authoritative CR data', 'body' => 'Official Ministry of Commerce source reduces reliance on informal screenshots.', 'icon' => 'shield'],
      ['title' => 'Faster verification', 'body' => 'Digital inquiry accelerates KYC and vendor onboarding.', 'icon' => 'clock'],
      ['title' => 'Due diligence', 'body' => 'Structured exports support audits and partner risk reviews.', 'icon' => 'document'],
    ],
    'help_json' => [
      ['title' => 'CR inquiry', 'body' => 'Look up registration status, activities, and authorised signatories.', 'icon' => 'search'],
      ['title' => 'Activity checks', 'body' => 'Validate that listed ISIC activities cover the contract scope.', 'icon' => 'check'],
      ['title' => 'Document exports', 'body' => 'Download evidence packs for banks, insurers, and regulators.', 'icon' => 'briefcase'],
    ],
    'steps_json' => [
      ['title' => 'Gather identifiers', 'body' => 'Collect CR number or unified national number before running an inquiry.'],
      ['title' => 'Run inquiry', 'body' => 'Execute the lookup and capture timestamped outputs.'],
      ['title' => 'Share securely', 'body' => 'Distribute PDFs through controlled channels with least-privilege access.'],
    ],
    'req_json' => [
      ['content' => 'Lawful purpose for each inquiry under MC acceptable use policies.'],
      ['content' => 'Internal data retention policy aligned with privacy obligations.'],
      ['content' => 'Arabic language support where reviewers require original terminology.'],
      ['content' => 'Escalation path to legal counsel for ambiguous activity mappings.'],
    ],
    'resources_json' => [
      ['title' => 'Sejel', 'uri' => 'https://sejel.mc.gov.sa/', 'link_title' => 'sejel.mc.gov.sa', 'icon' => 'globe'],
      ['title' => 'CR inquiry', 'uri' => 'https://sejel.mc.gov.sa/en/services/inquiry', 'link_title' => 'Inquiry', 'icon' => 'search'],
      ['title' => 'MC e-services', 'uri' => 'https://mc.gov.sa/en/eServices', 'link_title' => 'MC', 'icon' => 'document'],
      ['title' => 'FAQs', 'uri' => 'https://mc.gov.sa/en/HelpCenter/Pages/FAQs.aspx', 'link_title' => 'FAQs', 'icon' => 'info'],
    ],
    'path_alias' => '/platforms/sejel-tijari',
  ]),
];
