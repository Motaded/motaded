<?php

/**
 * @file
 * Long-form HTML bodies for official market insight import (EN).
 */

declare(strict_types=1);

return static function (
  string $library,
  string $insights_dir,
  string $sector_tourism,
  string $sector_finance,
  string $sector_energy,
  string $sector_tech,
  string $sector_health,
  string $sector_construction,
  string $sector_logistics,
  string $sector_manufacturing,
  string $inv_law,
): array {
  return [
    'saudi-real-gdp-growth-2024' => <<<HTML
<p><strong>Saudi Arabia’s real GDP</strong> expanded in 2024 as non-oil activities continued to lead growth while hydrocarbon output normalised after prior-year highs. For investors and operators, the headline figure matters less in isolation than the <strong>composition</strong> of growth — services, construction, and manufacturing increasingly drive year-on-year comparisons cited in official releases.</p>
<p>GASTAT national accounts show how Vision 2030-linked investment (giga-projects, tourism, logistics, and digital infrastructure) feeds through to private consumption and government investment. When modelling market entry, pair macro growth with sector licensing data on Motaded — for example the {$sector_tourism} hub and {$library} for regulatory PDFs.</p>
<h3>How to use this insight</h3>
<p>Benchmark your business plan assumptions against the official series rather than press estimates. Cross-check non-oil momentum with labour-market and price indicators published in the same statistical cycle.</p>
HTML,
    'non-oil-activities-growth-2024' => <<<HTML
<p><strong>Non-oil gross domestic product</strong> has become the primary narrative in Saudi macro reporting: GASTAT regularly highlights growth in services, construction, wholesale &amp; retail, and financial activities as evidence of diversification progress under Vision 2030.</p>
<p>For foreign investors, sustained non-oil expansion supports demand for commercial registration, payroll, and sector licences — particularly in {$sector_tech}, tourism, and logistics. Use {$insights_dir} to compare sector-specific insights and link feasibility studies to {$inv_law} where foreign ownership rules apply.</p>
<h3>Planning takeaway</h3>
<p>Non-oil growth does not eliminate cyclicality in oil-linked fiscal revenue; stress-test scenarios should still include conservative oil price assumptions even when operating purely in domestic services.</p>
HTML,
    'foreign-direct-investment-inflows-2024' => <<<HTML
<p><strong>Foreign direct investment (FDI)</strong> inflows into Saudi Arabia reached record levels in recent MISA reporting cycles, reflecting regulatory reforms, new investment law implementation, and large-scale giga-project procurement.</p>
<p>MISA’s Invest in Saudi platform consolidates licensing pathways for wholly owned foreign entities in permitted activities. Motaded’s {$sector_finance} and {$library} sections help operators map <em>pre-</em> and <em>post-licence</em> compliance — from commercial registration to ZATCA and labour platforms.</p>
<h3>Due diligence checklist</h3>
<ul>
<li>Confirm activity is not on the negative list before capital commitment.</li>
<li>Align shareholder structure with Commercial Register and MISA licence data.</li>
<li>Document source-of-funds for banking onboarding in KSA.</li>
</ul>
HTML,
    'saudi-unemployment-rate-2024' => <<<HTML
<p>The <strong>Saudi unemployment rate</strong> published by GASTAT tracks labour-market absorption as nationalisation (Saudisation) policies and private-sector job creation interact. Falling unemployment among Saudi nationals is a stated policy priority tied to Human Capability Development Program goals.</p>
<p>Employers expanding in the Kingdom should align workforce plans with Ministry of Human Resources and Social Development (HRSD) rules, Qiwa, and Mudad wage protection. Sector hubs such as {$sector_health} and {$sector_tourism} summarise licensing plus operational context for hiring at scale.</p>
<h3>Operational note</h3>
<p>Headline unemployment can mask sectoral mismatches — technology and hospitality roles may face different availability of qualified Saudi candidates; localise recruitment assumptions accordingly.</p>
HTML,
    'consumer-price-inflation-2024' => <<<HTML
<p><strong>Consumer price inflation (CPI)</strong> in Saudi Arabia is compiled by GASTAT and reflects housing, transport, food, and administered price components. Moderate inflation in 2024 supported real wage discussions and household purchasing power relative to earlier global shocks.</p>
<p>Businesses pricing B2B contracts in SAR should index assumptions to official CPI sub-indices where relevant and monitor VAT treatment via ZATCA guidance in {$library}. Retail and F&amp;B operators in {$sector_tourism} corridors should watch tourism-season demand spikes separately from national CPI.</p>
<h3>Forecast discipline</h3>
<p>Use GASTAT monthly CPI releases for board reporting; avoid mixing GCC-wide estimates with KSA-specific administered fuel and utility pricing mechanics.</p>
HTML,
    'international-tourism-arrivals-2024' => <<<HTML
<p><strong>International tourism arrivals</strong> to Saudi Arabia continued to climb as the Kingdom targets Vision 2030 hospitality goals, new visa products, and mega-events in Riyadh, Jeddah, and emerging destinations (AlUla, Red Sea, NEOM phases).</p>
<p>The Saudi Tourism Authority publishes visitor metrics and source-market breakdowns useful for hotel, tour, and attraction investors. Motaded’s {$sector_tourism} page connects licensing (MISA / Ministry of Tourism activity codes) with event calendars in {$insights_dir}.</p>
<h3>Investor angle</h3>
<p>Tourism GDP benefits hospitality, transport, and retail — but lease economics and seasonality vary sharply between religious travel, leisure, and business segments; segment your TAM before capex.</p>
HTML,
    'tadawul-market-capitalisation-2025' => <<<HTML
<p>The <strong>Saudi Exchange (Tadawul)</strong> remains the largest equity market in the MENA region by capitalisation, with MSCI/emerging-market inclusion driving passive flows and IPO pipeline activity under CMA oversight.</p>
<p>Public-market signals complement FDI data for {$sector_finance} strategies — family offices and corporates often dual-track MISA licences with CMA-regulated vehicles. Review {$library} for capital-markets rules and corporate governance circulars when structuring local holdings.</p>
<h3>Disclaimer</h3>
<p>This insight is macro context only, not investment advice; consult licensed advisers for security-specific decisions.</p>
HTML,
    'private-sector-share-of-gdp-2024' => <<<HTML
<p><strong>Private sector contribution to GDP</strong> is a core Vision 2030 indicator: policymakers emphasise PPP delivery, privatisation, and SME growth to reduce reliance on public spending. Official communications routinely cite rising private share as reform proof-points.</p>
<p>SMEs and multinational subsidiaries entering via MISA benefit from ecosystem programs (Monsha’at, Saudi Business Center). Use Motaded setup guides in {$library} and sector pages — {$sector_tech} for digital firms, {$sector_energy} for industrial suppliers — to align CR timelines with procurement cycles.</p>
<h3>Execution tip</h3>
<p>Anchor “private sector” narratives in your investor deck to verifiable GASTAT national-accounts tables rather than secondary media round-ups.</p>
HTML,
    'renewable-energy-capacity-2025' => <<<HTML
<p><strong>Renewable electricity capacity</strong> in Saudi Arabia is scaling through utility-scale solar and wind projects led by ACWA Power, SEC, and Public Investment Fund vehicles, supporting net-zero and local content objectives.</p>
<p>Energy investors should track Ministry of Energy and Water licensing, grid connection rules, and localisation requirements. The {$sector_energy} hub summarises adjacent compliance (environment, customs for equipment) and links to {$insights_dir} for macro demand context.</p>
<h3>Supply chain</h3>
<p>Panel and turbine procurement cycles interact with global commodity prices — hedge FX on long-term PPAs where contracts permit.</p>
HTML,
    'ict-sector-growth-saudi-arabia-2024' => <<<HTML
<p><strong>Information and communication technology (ICT)</strong> activity in Saudi Arabia benefits from cloud adoption, fintech licensing, gaming, and government digitalisation (DGA, NCA cybersecurity frameworks). GASTAT sector accounts show ICT as a growth outperformer relative to hydrocarbon extraction.</p>
<p>Technology entrants should map MISA activity codes, data residency expectations, and NCA Essential Cybersecurity Controls where processing Saudi citizen data. Start from {$sector_tech} and platform guides in {$library} before hiring delivery teams in Riyadh or Khobar.</p>
<h3>Go-to-market</h3>
<p>Enterprise sales cycles often require local CR, VAT registration, and Saudisation-compliant headcount — budget 3–6 months for operational readiness after licence approval.</p>
HTML,
    'healthcare-spending-growth-2024' => <<<HTML
<p><strong>Healthcare expenditure</strong> in Saudi Arabia continues to rise with population growth, insurance penetration (CHI mandates), and privatisation of providers under Ministry of Health oversight and the Health Sector Transformation Program.</p>
<p>Medtech, pharma distribution, and hospital operators use GASTAT health accounts and MOH statistical yearbooks for demand modelling. Motaded’s {$sector_health} content connects sector opportunity with licensing pathways for foreign manufacturers and service operators.</p>
<h3>Regulatory</h3>
<p>SFDA registration timelines apply to devices and medicines — sequence MISA / CR steps with product authorisation to avoid stranded inventory.</p>
HTML,
    'construction-sector-output-2024' => <<<HTML
<p><strong>Construction activity</strong> in Saudi Arabia remains a primary engine of non-oil GDP, driven by residential, commercial, and giga-project pipelines (Riyadh, NEOM, Red Sea, Qiddiya). GASTAT construction indicators track permits, cement consumption proxies, and sector gross value added.</p>
<p>EPC contractors and building-materials suppliers entering KSA should align CR and municipality licensing with Saudisation targets for site labour. The {$sector_construction} hub on Motaded links macro demand with {$library} guides on commercial registration and subcontractor compliance.</p>
<h3>Risk note</h3>
<p>Project finance and milestone payments vary by employer — validate counterparty CR status and ZATCA VAT registration before mobilising equipment.</p>
HTML,
    'manufacturing-value-added-2024' => <<<HTML
<p><strong>Manufacturing gross value added</strong> benefits from localisation programs (IKTVA, automotive supply chains, food processing) and industrial cities managed by MODON and RCJY. Official statistics highlight chemicals, refining downstream, and metals processing alongside newer EV and battery supply-chain investments.</p>
<p>Industrial investors should map MISA activity codes, SASO product conformity, and customs duty rules for plant imports. Start with {$sector_manufacturing} and cross-read {$insights_dir} for energy and logistics cost assumptions.</p>
<h3>Local content</h3>
<p>Government procurement increasingly scores local value-add — document Saudi payroll and in-Kingdom capex early in bid responses.</p>
HTML,
    'logistics-port-throughput-2024' => <<<HTML
<p><strong>Logistics performance</strong> in Saudi Arabia is anchored by King Abdulaziz Port (Dammam), Jeddah Islamic Port, and emerging bonded zones supporting e-commerce and trans-shipment. Throughput statistics from the General Authority for Statistics and transport ministries illustrate trade intensity with Asia and Europe.</p>
<p>Freight forwarders and 3PL operators licensing in KSA must coordinate customs (ZATCA/Fasah), transport authority permits, and warehouse CR activities. {$sector_logistics} summarises sector entry alongside {$library} compliance documents.</p>
<h3>Capacity planning</h3>
<p>Peak seasons around Hajj and year-end retail stress port and last-mile networks — contract SLAs with buffer capacity in Jeddah and Riyadh corridors.</p>
HTML,
    'real-estate-price-index-2024' => <<<HTML
<p>GASTAT’s <strong>real estate price index</strong> tracks residential and commercial land and property trends across major cities. Riyadh and Eastern Province markets often lead national averages as housing supply responds to population growth and mortgage product expansion.</p>
<p>Developers and PropTech entrants should separate headline index moves from micro-location absorption in new districts. Pair index data with {$sector_construction} licensing context and SAMA mortgage finance circulars referenced in {$library}.</p>
<h3>Underwriting</h3>
<p>Stress residential pre-sales against delivery timelines on giga-project labour inflows — concentration risk can differ by city.</p>
HTML,
    'sama-net-foreign-assets-2024' => <<<HTML
<p><strong>SAMA net foreign assets</strong> reflect hydrocarbon export proceeds, portfolio investment flows, and reserve management policy. The monthly bulletin is a core macro anchor for SAR liquidity, banking sector lending capacity, and riyal peg stability narratives.</p>
<p>Corporate treasurers and {$sector_finance} participants monitor NFA trends when planning FX exposure, intercompany loans, and dividend repatriation from Saudi subsidiaries. Use {$insights_dir} alongside {$inv_law} for capital-structure decisions tied to inbound FDI.</p>
<h3>Treasury tip</h3>
<p>Align board reporting to SAMA’s official release calendar rather than sell-side “estimate” series that may mix gross reserves with forward sales.</p>
HTML,
    'fiscal-balance-saudi-arabia-2024' => <<<HTML
<p>The <strong>Ministry of Finance</strong> publishes central government budget outturns and preliminary results, including revenue from oil and non-oil sources and expenditure on capital projects and public wages. Surplus or deficit years shape giga-project payment schedules and procurement liquidity.</p>
<p>Contractors bidding government-linked work should read budget annex tables for capital spending envelopes by sector. Investors in PPP structures should cross-check fiscal rules with {$library} and macro insights in {$insights_dir}.</p>
<h3>Planning</h3>
<p>Non-oil revenue growth (VAT, fees, PIF dividends) increasingly offsets oil volatility — model multi-year scenarios, not a single Brent strip.</p>
HTML,
    'female-labour-participation-2024' => <<<HTML
<p><strong>Female labour force participation</strong> in Saudi Arabia has risen sharply under social and economic reforms (transport, workplace regulation, childcare, and remote-work norms). GASTAT labour surveys publish participation and unemployment rates by gender and nationality.</p>
<p>HR policies for retail, tourism, and professional services must comply with HRSD Saudisation categories while competing for talent. Sector pages such as {$sector_tourism} and {$sector_tech} highlight licensing plus workforce compliance platforms (Qiwa, Mudad).</p>
<h3>Recruitment</h3>
<p>Participation rates differ by sector — technology and healthcare may require targeted graduate pipelines versus hospitality seasonal hiring.</p>
HTML,
    'digital-payments-growth-2024' => <<<HTML
<p><strong>Digital payments and fintech</strong> adoption accelerated with SAMA regulatory sandbox graduates, buy-now-pay-later products, and government digitisation of fees and fines. Transaction value and point-of-sale penetration support e-commerce and platform business models.</p>
<p>Fintech licensors coordinate with SAMA, CST, and (where applicable) capital markets rules via CMA. Map requirements through {$sector_finance} and {$sector_tech} before launching wallet or lending products to Saudi residents.</p>
<h3>Compliance</h3>
<p>AML/CFT alignment with SAMA rulebooks is non-negotiable — budget compliance officers before product launch marketing.</p>
HTML,
    'agricultural-output-food-security-2024' => <<<HTML
<p><strong>Agricultural output and food security</strong> programs under the Ministry of Environment, Water and Agriculture (MEWA) target local production of vegetables, dairy, and poultry while managing water scarcity. GASTAT agricultural statistics track crop areas and livestock indicators.</p>
<p>Agri-tech and food-processing investors should review water tariff policies, land lease frameworks, and SFDA food registration. Link operational plans to {$insights_dir} macro demand and import substitution narratives in national strategies.</p>
<h3>Operations</h3>
<p>Cold-chain and port logistics remain critical for perishables — coordinate with {$sector_logistics} partners for Red Sea and Dammam corridors.</p>
HTML,
    'hydrocarbon-production-2024' => <<<HTML
<p><strong>Hydrocarbon production</strong> in Saudi Arabia is coordinated with OPEC+ agreements while maintaining spare capacity managed by Saudi Aramco. Monthly oil and gas statistics affect fiscal revenue, petrochemical feedstock costs, and regional energy pricing benchmarks.</p>
<p>Downstream investors in {$sector_energy} should separate upstream volume data from refining margin cycles and product export netbacks. Use {$library} for environmental and industrial permitting when siting plants in Jubail, Yanbu, or Ras Al-Khair.</p>
<h3>Macro link</h3>
<p>Oil price and volume interact — stress-test models with Ministry of Finance sensitivity tables where available.</p>
HTML,
  ];
};
