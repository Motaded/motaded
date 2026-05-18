<?php

/**
 * @file
 * Long-form “About the event” copy (SEO) for official event import.
 */

declare(strict_types=1);

return static function (
  string $library,
  string $events_dir,
  string $inc_guide,
  string $inv_law,
  string $sector_tech,
  string $sector_energy,
  string $sector_health,
  string $sector_construction,
  string $sector_tourism,
  string $sector_logistics,
  string $sector_finance,
  string $platform_misa,
  string $platform_zatca,
): array {
  return [
    'leap-2027' => <<<HTML
<p><strong>LEAP 2027</strong> is Saudi Arabia’s flagship technology and innovation conference, held in <strong>Riyadh</strong> at the Riyadh Front Exhibition &amp; Conference Center. The event brings together global founders, enterprise buyers, systems integrators, and public-sector leaders to discuss AI, cloud, cybersecurity, fintech, and digital government — themes central to Vision 2030 and the Kingdom’s diversification agenda.</p>
<p>For international companies evaluating Saudi market entry, LEAP is a practical venue to validate positioning, meet channel partners, and compare localization requirements before committing budget. Exhibitors span software, infrastructure, and govtech; visitors include C-suite sponsors, procurement teams, and innovation labs from across the GCC and wider MENA region.</p>
<h3>Why LEAP matters for market entry</h3>
<p>Technology adoption in Saudi Arabia is accelerating across banking, energy, healthcare, and smart-city programs. Attending LEAP helps you map buyer priorities, understand sovereign-cloud and data-residency conversations, and identify integrators who already hold government or enterprise references. Pair your visit with Motaded’s {$sector_tech} overview and the {$library} guides on incorporation and investment rules.</p>
<h3>Planning your visit</h3>
<p>Confirm registration categories and meeting formats on the official LEAP website. Build a short target list of halls and partners, and allow travel time across a large campus. Before travel, review {$inc_guide} and {$inv_law}; operators invoicing locally should also check {$platform_zatca}, while licensing questions often route through {$platform_misa}.</p>
<p>Compare dates, formats, and sectors across other flagship gatherings in the {$events_dir}.</p>
<h3>What to prepare</h3>
<ul>
<li>One-page company profile and compliance snapshot (CR, VAT status if applicable).</li>
<li>Short list of target partners by sub-sector (AI, cloud, govtech, cybersecurity).</li>
<li>Meeting calendar with buffer time — walking distances between halls add up.</li>
</ul>
HTML,
    'fii-2026' => <<<HTML
<p>The <strong>Future Investment Initiative (FII) 2026</strong> is a global investment forum in <strong>Riyadh, Saudi Arabia</strong>, convening policymakers, sovereign wealth funds, CEOs, and asset managers. Hosted at the King Abdulaziz International Conference Center, FII focuses on capital flows, geopolitical risk, and long-term economic transformation rather than a traditional trade-show floor.</p>
<p>For firms expanding into the Kingdom, FII offers strategic visibility into giga-project pipelines, privatization themes, and sector allocation priorities led by Saudi Arabia’s Public Investment Fund ecosystem. It is particularly relevant for executives framing Saudi allocation, joint ventures, or co-investment with local partners.</p>
<h3>Investment and policy context</h3>
<p>Sessions typically cover energy transition, digital economy, healthcare investment, and financial-market development — aligning with Motaded resources on {$sector_energy}, {$sector_finance}, and {$sector_tech}. After the forum, practical next steps often involve investor services via {$platform_misa} and regulatory reading in the {$library}, starting with {$inv_law}.</p>
<h3>Delegate planning</h3>
<p>Registration tiers and accreditation rules vary by year; confirm delegate categories early. Side meetings and bilaterals often deliver more value than plenary attendance alone — book Riyadh logistics and security clearance windows well ahead. Use the {$events_dir} to line up complementary sector exhibitions before or after FII.</p>
<h3>Practical notes</h3>
<ul>
<li>Business formal dress and official communications govern access rules.</li>
<li>Prepare a concise Saudi thesis: sector, ticket size, and local partner model.</li>
<li>Follow up within 48 hours — calendars fill quickly during forum week.</li>
</ul>
HTML,
    'cityscape-2026' => <<<HTML
<p><strong>Cityscape Global 2026</strong> is an international real-estate and urban-development exhibition in <strong>Riyadh, Saudi Arabia</strong>, at the Riyadh International Convention &amp; Exhibition Center. Organized by Informa Markets, the show connects master developers, investors, proptech vendors, contractors, and facilities-management providers active in Saudi housing, mixed-use, and hospitality pipelines.</p>
<p>Saudi Arabia’s residential and commercial build-out — linked to Vision 2030 quality-of-life and tourism goals — makes Cityscape a reference point for land, financing structures, and supplier specifications. International attendees use the event to meet Saudi developers, understand affordable-housing programs, and scout smart-building technologies.</p>
<h3>Sectors and opportunities</h3>
<p>Exhibitors cover architecture, MEP, interiors, digital twins, and property-management platforms. Investors and family offices attend to source real-asset deals and joint ventures. Cross-read Motaded’s {$sector_construction} page and {$inc_guide} before meetings; contractor VAT and e-invoicing topics often tie back to {$platform_zatca}.</p>
<h3>Who benefits most</h3>
<p>Developers, main contractors, proptech scale-ups, and hospitality investors tracking Riyadh and secondary-city projects gain the most from Cityscape. Plan hall routes by developer pavilion and conference track — housing policy, financing models, and urban design sessions run parallel to the exhibition.</p>
<p>Browse related construction and infrastructure events in the {$events_dir} and deepen compliance research in the {$library}.</p>
HTML,
    'big5-2026' => <<<HTML
<p><strong>Big 5 Construct Saudi 2026</strong> is a major construction-industry exhibition in <strong>Riyadh</strong>, covering building materials, MEP, tools, machinery, digital construction, and project-delivery solutions for Saudi giga-projects and urban expansion. Organized by dmg events, Big 5 is procurement-oriented: international manufacturers and specialist subcontractors meet Saudi main contractors, distributors, and specification consultants.</p>
<p>With NEOM, ROSHN, Red Sea, and other mega-programs driving demand, the show is a practical map of approved suppliers, safety standards, and local partnership models. Attendees typically include procurement heads, project directors, and country managers establishing KSA representation.</p>
<h3>Construction market entry</h3>
<p>Entering the Saudi supply chain requires clarity on commercial registration, agency agreements, and sometimes in-Kingdom warehousing. Review {$sector_construction} and logistics implications on {$sector_logistics}; new entities should start with {$inc_guide} and {$platform_misa} in the {$library}.</p>
<h3>Making the most of Big 5</h3>
<p>Structured product zones cover concrete, MEP, tools, and digital construction — plan demos and technical talks alongside booth meetings. Bring specification sheets, SASO or local compliance notes where relevant, and a list of target main contractors. Compare other Riyadh industry calendars via the {$events_dir}.</p>
HTML,
    'ghe-2026' => <<<HTML
<p><strong>Global Health Exhibition 2026</strong> is a healthcare and life-sciences trade show in <strong>Riyadh, Saudi Arabia</strong>, gathering hospitals, medtech OEMs, pharma companies, diagnostics firms, and digital-health vendors serving the Kingdom’s expanding care network. Organized by Informa Markets, the exhibition aligns with Saudi localization programs, private-hospital expansion, and Ministry of Health procurement cycles.</p>
<p>International suppliers attend to meet distributors, understand SFDA pathways, and discuss medical-device and pharmaceutical manufacturing incentives. Hospital groups and insurers use the floor to source technology, services, and specialty partnerships.</p>
<h3>Healthcare market entry in KSA</h3>
<p>Clinical and corporate setup intersect: commercial registration, product registration, data protection, and employment rules all matter. Motaded’s {$sector_health} hub and {$library} guides — including {$inc_guide} and {$platform_misa} — help translate exhibition conversations into actionable checklists.</p>
<h3>Conference and exhibition focus</h3>
<p>Tracks typically span clinical innovation, hospital operations, investment, and digital health (telehealth, AI diagnostics, hospital IT). Plan meetings with both public and private buyers; localization and halal supply-chain requirements are frequent discussion topics. Explore complementary events in the {$events_dir}.</p>
HTML,
    'food-2026' => <<<HTML
<p><strong>Saudi Food Expo 2026</strong> is a food and beverage trade exhibition in <strong>Riyadh</strong> at the Riyadh International Convention &amp; Exhibition Center. Organized by dmg events, the show connects F&amp;B manufacturers, ingredients suppliers, packaging companies, and retail-technology vendors with Saudi distributors, hypermarkets, convenience chains, and e-grocery platforms.</p>
<p>Saudi Arabia’s young population, tourism growth, and retail modernization drive demand for imported and locally produced brands. Exporters attend to evaluate distribution agreements, halal certification, labeling rules, and cold-chain partners for hot-climate logistics.</p>
<h3>F&amp;B go-to-market topics</h3>
<p>Key conversations include halal compliance, Arabic labeling, shelf-life testing, and VAT/e-invoicing for local sales — see {$platform_zatca} and {$sector_logistics} on Motaded. Manufacturing investors may also review {$inc_guide} and sector context in the {$library}.</p>
<h3>Exhibition experience</h3>
<p>National and international pavilions feature product launches, tastings, and packaging innovation zones. Retail procurement teams scout new SKUs; distributors seek exclusive territories. Plan follow-ups on incoterms, customs brokers, and Saudi Standards requirements before committing listings.</p>
<p>Discover other manufacturing and retail-facing events in the {$events_dir}.</p>
HTML,
    'fmf-2027' => <<<HTML
<p>The <strong>Future Minerals Forum 2027</strong> is a government-backed mining and minerals conference in <strong>Riyadh, Saudi Arabia</strong>, at the King Abdulaziz International Conference Center. Convened under the Ministry of Industry and Mineral Resources, FMF addresses exploration, processing, critical minerals, and supply-chain security as Saudi Arabia scales its mining sector as a third economic pillar.</p>
<p>Operators, investors, equipment OEMs, and ESG advisors attend to track auction rounds, geological surveys, downstream processing investments, and cross-border cooperation on battery metals and industrial minerals.</p>
<h3>Energy transition and mining strategy</h3>
<p>FMF sits at the intersection of energy transition materials, industrial policy, and capital allocation. Pair attendance with Motaded’s {$sector_energy} research and {$inv_law} in the {$library}. New legal entities and joint ventures often coordinate licensing through {$platform_misa}.</p>
<h3>Forum programming</h3>
<p>Ministerial sessions set policy direction; project showcases highlight mining and beneficiation pipelines. Technical discussions cover exploration technology, water stewardship, and community engagement. Use the {$events_dir} to align FMF with upstream energy conferences such as IPTC in the Eastern Province.</p>
HTML,
    'biban-2026' => <<<HTML
<p><strong>BIBAN 2026</strong> is Saudi Arabia’s national entrepreneurship forum, held in <strong>Riyadh</strong> at the Riyadh Exhibition &amp; Convention Center in Malham. Organized by Monsha\'at, BIBAN combines pitching competitions, SME support clinics, investor meetings, and government-service booths for founders scaling in the Kingdom.</p>
<p>The forum suits early-stage startups validating product-market fit, corporates running open-innovation programs, and international accelerators seeking Saudi LPs or pilot customers. Free public access and high founder density make it a flagship touchpoint in the local startup calendar.</p>
<h3>Founder and investor ecosystem</h3>
<p>Workshops cover fundraising, go-to-market, employment rules, and regulatory basics. Angels, VCs, and corporate venture units hold office hours alongside Monsha\'at partners. Founders should read {$inc_guide}, explore {$sector_finance} and {$sector_tech}, and plan tax setup with {$platform_misa} and {$platform_zatca} where applicable.</p>
<h3>How to prepare</h3>
<p>Refine a one-minute pitch, book mentoring slots early, and target sector-themed pitch tracks. Corporate attendees should define pilot criteria and procurement paths before scouting startups. More business-setup resources live in the {$library} and {$events_dir}.</p>
HTML,
    'stm-2026' => <<<HTML
<p><strong>Saudi Travel Market 2026</strong> is a B2B travel and tourism trade exhibition in <strong>Riyadh, Saudi Arabia</strong>, at the Riyadh International Convention &amp; Exhibition Center. Organized by Informa Markets, STM connects destination management organizations, airlines, hotel groups, tour operators, and travel-technology providers as the Kingdom scales leisure and business visitation under Vision 2030 tourism targets.</p>
<p>International suppliers attend to contract with Saudi DMOs, hospitality developers, and aviation partners; local operators use the floor to source outbound product and technology. Recent editions have reported strong hosted-buyer activity and deal-making across tourism, hospitality, aviation, and travel tech.</p>
<h3>Tourism market entry</h3>
<p>Operators expanding in Saudi Arabia should review hospitality licensing, employment, and consumer regulations via Motaded’s {$sector_tourism} page and the {$library}. Hiring locally triggers Qiwa and immigration checkpoints — cross-read {$inc_guide} before staffing a Riyadh desk.</p>
<h3>Maximizing STM</h3>
<p>Verify eligibility for hosted-buyer programs and pre-schedule meetings. Exhibition zones cover destinations, hotels, aviation, and travel tech; conference sessions feature industry leaders and policy updates. Explore related calendars in the {$events_dir}.</p>
HTML,
    'iptc-2027' => <<<HTML
<p>The <strong>International Petroleum Technology Conference (IPTC) 2027</strong> is a multidisciplinary upstream energy conference in <strong>Dhahran, Saudi Arabia</strong>, at Dhahran Expo in the Eastern Province. Organized by the Society of Petroleum Engineers (SPE) with partner societies, IPTC is a flagship technical forum for drilling, reservoir engineering, geoscience, production, and digital oilfield innovation in the Eastern Hemisphere.</p>
<p>Global operators, national oil companies, service companies, and technology vendors attend for peer-reviewed papers, field case studies, and vendor meetings anchored in Saudi Arabia’s energy cluster. IPTC 2027 continues the Kingdom’s role hosting world-class upstream technical discourse alongside Aramco and regional operator priorities.</p>
<h3>Upstream engineering and digital oilfield</h3>
<p>Tracks span drilling, completions, reservoir management, decarbonization, and data-driven operations. Vendors showcase automation, subsea, and emissions-reduction technologies. Motaded’s {$sector_energy} resources and {$library} guides support environmental, safety, and corporate-setup questions; multinationals often coordinate {$platform_misa} licensing separately from operator vendor qualification.</p>
<h3>Attendance tips</h3>
<p>Review the technical program early and book travel to the Eastern Province. Poster sessions and student programs are useful for talent and R&amp;D partnerships. Pair IPTC with Riyadh energy forums listed in the {$events_dir} for a full Saudi energy calendar.</p>
HTML,
    'blackhat-mea-2026' => <<<HTML
<p><strong>Black Hat MEA 2026</strong> is the Middle East and Africa’s leading cybersecurity conference and exhibition, taking place <strong>1–3 December 2026</strong> at the Riyadh Exhibition &amp; Convention Centre in Malham, <strong>Saudi Arabia</strong>. Co-organized with Saudi Arabia’s information-security community, the event combines executive briefings, technical trainings, Arsenal tool demos, and a large exhibition floor.</p>
<p>Black Hat MEA attracts CISOs, SOC leaders, red teams, MSSPs, and security vendors targeting Saudi government and enterprise buyers. For companies entering the Kingdom’s cyber market, it is a concentrated venue to meet integrators, compare sovereign and cloud security models, and align with national digitization programs.</p>
<h3>Cybersecurity market entry</h3>
<p>Saudi organizations are scaling SOC maturity, zero-trust architectures, and critical-infrastructure protection. Pair attendance with Motaded’s {$sector_tech} resources, {$inc_guide}, and {$platform_misa} licensing context in the {$library}. VAT and invoicing for local contracts may involve {$platform_zatca}.</p>
<h3>Program highlights</h3>
<p>Expect multi-track briefings, hands-on trainings, Capture-the-Flag competitions, and an exhibition with hundreds of security brands. Plan meetings around executive summit sessions and technical deep-dives. See other technology events in the {$events_dir}.</p>
HTML,
    'wds-2026' => <<<HTML
<p><strong>World Defense Show 2026</strong> is a tri-service defense and security exhibition in <strong>Riyadh, Saudi Arabia</strong>, held <strong>8–12 February 2026</strong> under the patronage of the Kingdom’s Ministry of Defense. The show spans air, land, and sea domains with live demonstrations, national pavilions, and procurement-focused B2B meetings across aerospace, land systems, naval systems, and security technologies.</p>
<p>International OEMs, tier suppliers, and systems integrators attend to meet Saudi and GCC buyers, understand offset and localization requirements, and track giga-project security demand. Exhibition space has expanded significantly since the inaugural edition, reflecting Saudi Arabia’s growing role as a regional defense-industry hub.</p>
<h3>Defense industry and localization</h3>
<p>Market entry often involves joint ventures, local maintenance/repair/overhaul capabilities, and export compliance. Review {$inv_law} and investment frameworks in the {$library}, and coordinate entity setup via {$platform_misa}. Supply-chain partners should also read {$sector_logistics} for spares and depot planning.</p>
<h3>Visitor planning</h3>
<p>Accreditation and security rules are strict — register early and confirm demonstration schedules. Use the {$events_dir} to align WDS with other Riyadh industrial exhibitions during your trip.</p>
HTML,
    'gais-2026' => <<<HTML
<p>The <strong>Global AI Summit 2026</strong> (GAIN Summit) is Saudi Arabia’s flagship artificial-intelligence conference, organized by the Saudi Data and Artificial Intelligence Authority (SDAIA) in <strong>Riyadh</strong> on <strong>15–17 September 2026</strong> at the King Abdulaziz International Conference Center. The fourth edition convenes ministers, researchers, enterprise AI leaders, and startups around trustworthy AI, generative models, and national AI strategies.</p>
<p>With participation from dozens of countries and hundreds of speakers, the summit is a policy and commercial barometer for AI investment in the GCC. Vendors showcase sovereign AI, Arabic LLMs, computer vision, and industry solutions for government and regulated sectors.</p>
<h3>AI market entry in Saudi Arabia</h3>
<p>Data governance, cloud residency, and sector regulations shape AI deployments. Explore {$sector_tech}, {$inc_guide}, and {$platform_misa} on Motaded before meetings. Enterprises selling into government should prepare compliance narratives alongside product demos.</p>
<h3>Making the most of GAIN</h3>
<p>Book plenary and sector roundtables early; side events fill quickly. Connect summit themes with LEAP and DeepFest in the {$events_dir} for a full Riyadh technology calendar.</p>
HTML,
    'index-saudi-2026' => <<<HTML
<p><strong>INDEX Saudi Arabia 2026</strong> is the Kingdom’s largest interiors, furniture, and fit-out trade fair, held <strong>6–8 September 2026</strong> at the Riyadh Front Exhibition &amp; Conference Center. Organized by dmg events, INDEX connects architects, hospitality developers, contractors, distributors, and design brands supplying Saudi hotels, residences, offices, and retail rollouts.</p>
<p>Exhibitors span furniture, lighting, kitchens, bathrooms, fabrics, and smart-building finishes. Trade visitors include procurement teams from giga-projects, hotel chains, and residential developers implementing Vision 2030 quality-of-life targets.</p>
<h3>Hospitality and construction supply chain</h3>
<p>INDEX complements housing and tourism build-out. Cross-read {$sector_construction} and {$sector_tourism}, plus {$inc_guide} for new showrooms or agencies. Contractor invoicing may involve {$platform_zatca}.</p>
<h3>Trade-only attendance</h3>
<p>The event is professional trade only — register with business credentials. Plan walking routes across halls and schedule meetings with key Saudi distributors. More Riyadh build and design events are listed in the {$events_dir}.</p>
HTML,
    'deepfest-2026' => <<<HTML
<p><strong>DeepFest 2026</strong> is a premier artificial-intelligence conference co-located with <strong>LEAP 2026</strong> in <strong>Riyadh</strong>, running <strong>31 August – 3 September 2026</strong> at the Riyadh Exhibition and Convention Center in Malham. DeepFest focuses on AI research, ethics, governance, robotics, and enterprise adoption with speaker programs, live demos, and an AI exhibition floor.</p>
<p>Founders, data scientists, product leaders, and policy teams attend alongside LEAP’s broader technology audience. For international AI vendors, DeepFest offers targeted meetings with Saudi enterprises and government digital units pursuing Arabic models and regulated AI deployments.</p>
<h3>AI commercialization and compliance</h3>
<p>Pair DeepFest conversations with Motaded {$sector_tech} guides, {$library} documents, and {$platform_misa} setup pathways. Data residency and sector-specific rules often appear in procurement discussions — prepare reference architectures and local partner options.</p>
<h3>Co-located planning</h3>
<p>Book accommodation and transport for Malham campus scale; coordinate LEAP and DeepFest badges on official sites. Browse related events in the {$events_dir}.</p>
HTML,
    'logistics-2026' => <<<HTML
<p><strong>Saudi Warehousing &amp; Logistics Expo 2026</strong> is a dedicated intralogistics and supply-chain exhibition in <strong>Riyadh</strong> on <strong>30 August – 1 September 2026</strong> at the Riyadh Front Exhibition &amp; Conference Center. Organized by dmg events, the expo covers warehousing automation, WMS, material handling, cold chain, last-mile, and freight technologies serving Saudi Arabia’s e-commerce, retail, and industrial growth.</p>
<p>3PL operators, retailers, manufacturers, and port-linked logistics firms attend to source automation, packaging, and fleet solutions. International vendors use the show to appoint Saudi distributors and understand SASO, customs, and VAT workflows for equipment imports.</p>
<h3>Logistics market entry</h3>
<p>Review {$sector_logistics}, {$inc_guide}, and {$platform_zatca} for e-invoicing and customs context. Licensing and foreign-investment questions may route through {$platform_misa} in the {$library}.</p>
<h3>Exhibition strategy</h3>
<p>Target meetings with retail logistics heads and giga-project supply-chain teams; live demos of AMR/ASRS systems are popular. Align your visit with LEAP and SEA Expo dates in the {$events_dir}.</p>
HTML,
    'sea-expo-2026' => <<<HTML
<p><strong>SEA Expo 2026</strong> (Saudi Entertainment &amp; Amusement Expo) is the Kingdom’s leading trade show for theme parks, family entertainment centers, attractions, and live events, held <strong>4–6 May 2026</strong> at the Riyadh Front Exhibition &amp; Conference Center. Strategic partners include the General Entertainment Authority, reflecting Saudi Arabia’s rapid expansion of leisure and tourism assets under Vision 2030.</p>
<p>Exhibitors supply rides, games, water-park equipment, AV, ticketing, F&amp;B concepts, and operations consultancy. Developers, operators, and investors attend to source technology, plan attractions, and meet regulators and contractors.</p>
<h3>Entertainment market entry</h3>
<p>Entertainment projects combine construction, safety, and tourism licensing. Read {$sector_tourism} and {$sector_construction}, plus {$inc_guide} and {$library} compliance guides. Local contracting may require {$platform_misa} and tax registration via {$platform_zatca}.</p>
<h3>Summit and networking</h3>
<p>SEA includes conference sessions on visitor experience, operations, and industry trends — reserve summit seats early. Discover related tourism events in the {$events_dir}.</p>
HTML,
    'pharma-2026' => <<<HTML
<p><strong>Saudi International Pharma Expo 2026</strong> is a pharmaceutical and life-sciences trade exhibition in <strong>Riyadh</strong> on <strong>12–14 October 2026</strong> at the Riyadh International Convention &amp; Exhibition Center. The expo connects API suppliers, finished-dosage manufacturers, packaging firms, laboratories, distributors, and healthcare networks across the Saudi and GCC markets.</p>
<p>With SFDA-regulated imports and growing local manufacturing incentives, the show is a key venue for licensing partners, contract manufacturing, and cold-chain distribution. Attendees include hospital procurement, wholesale distributors, and regulatory affairs teams.</p>
<h3>Pharma market entry</h3>
<p>Align product registration, labeling, and pharmacovigilance plans with Motaded’s {$sector_health} resources and {$library} guides. New entities should review {$inc_guide} and {$platform_misa}; VAT flows may involve {$platform_zatca}.</p>
<h3>Exhibition planning</h3>
<p>Bring regulatory summaries and stability data for serious buyer meetings; compare with Global Health Exhibition in the {$events_dir} for a full healthcare calendar.</p>
HTML,
    'saudi-build-2026' => <<<HTML
<p><strong>Saudi Build 2026</strong> is the 35th international construction and building-technology exhibition in <strong>Riyadh</strong>, held <strong>2–5 November 2026</strong> at the Riyadh International Convention &amp; Exhibition Center on King Abdullah Road. Organized by Riyadh Exhibitions Company, Saudi Build covers structural materials, MEP, environmental technology, stone, tools, and services for one of the world’s busiest construction markets.</p>
<p>Main contractors, consultants, developers, and distributors attend to source products and meet international manufacturers establishing Saudi representation. The event complements other construction shows while maintaining a long-standing local buyer base.</p>
<h3>Construction supply chain entry</h3>
<p>Review {$sector_construction}, {$inc_guide}, and {$platform_misa} before exhibiting. Import and VAT questions for materials and equipment often involve {$platform_zatca} and customs brokers documented in the {$library}.</p>
<h3>Buyer meetings</h3>
<p>Prepare technical datasheets in metric units and evidence of local projects or references. See Big 5 Construct and Cityscape in the {$events_dir} to plan a full Q4 construction itinerary.</p>
HTML,
    'jittx-2026' => <<<HTML
<p><strong>JTTX 2026</strong> (Jeddah International Travel &amp; Tourism Exhibition) is a leading travel trade fair in <strong>Jeddah, Saudi Arabia</strong>, on <strong>28–30 January 2026</strong> at the Jeddah Superdome / Jeddah Centre for Forums and Events. Organized with support from Saudi tourism authorities, JTTX blends B2B trade meetings with consumer promotion for destinations, airlines, hotels, DMCs, and online travel platforms.</p>
<p>International tourism boards and operators attend to reach Saudi outbound travelers and inbound partnerships as the Kingdom invests in airports, resorts, and visa facilitation. Exhibitors span aviation, hospitality, cruises, and travel technology.</p>
<h3>Tourism business in the Western Province</h3>
<p>Jeddah is a commercial gateway — align licensing and employment plans with {$sector_tourism} and {$inc_guide} on Motaded. Compare STM in Riyadh via the {$events_dir} for a national tourism calendar.</p>
<h3>Exhibitor tips</h3>
<p>Prepare Arabic marketing collateral where possible; promote packages tailored to Saudi public holidays and school breaks. Coordinate {$platform_zatca} if selling packages locally.</p>
HTML,
  ];
};
