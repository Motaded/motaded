<?php

/**
 * @file
 * SEO meta for official event import (key = EN title).
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var list<array{0: string, 1: string, 2: list<string>}> $defs */
$defs = [
  ['LEAP 2027', 'Saudi Arabia’s flagship technology and innovation conference in Riyadh.', ['LEAP Saudi', 'tech event KSA', 'digital transformation']],
  ['Future Investment Initiative 2026', 'Global investment forum connecting capital allocators and Saudi policy leaders.', ['FII Saudi', 'Davos in the Desert', 'sovereign investment']],
  ['Cityscape Global 2026', 'Major real estate and urban development exhibition in Saudi Arabia.', ['Cityscape Riyadh', 'Saudi real estate', 'proptech KSA']],
  ['Big 5 Construct Saudi 2026', 'Construction, building materials, and infrastructure trade exhibition.', ['Big 5 Saudi', 'construction expo KSA', 'building materials']],
  ['Global Health Exhibition 2026', 'Healthcare, medtech, and hospital procurement exhibition in KSA.', ['Saudi healthcare expo', 'medtech KSA', 'hospital procurement']],
  ['Saudi Food Expo 2026', 'Food, beverage, and halal industry trade show in Saudi Arabia.', ['F&B Saudi', 'halal food expo', 'food industry KSA']],
  ['Future Minerals Forum 2027', 'Mining, minerals, and downstream investment forum in Riyadh.', ['mining Saudi', 'minerals forum KSA', 'critical minerals']],
  ['BIBAN 2026', 'National entrepreneurship and SME forum supported by Monsha\'at.', ['BIBAN Saudi', 'startup event KSA', 'Monshaat']],
  ['Saudi Travel Market 2026', 'B2B travel, tourism, and hospitality trade exhibition.', ['tourism B2B Saudi', 'hospitality expo KSA', 'Visit Saudi']],
  ['International Petroleum Technology Conference 2027', 'Upstream oil and gas technical conference (IPTC).', ['IPTC Saudi', 'upstream oil gas', 'Aramco conference']],
  ['Black Hat MEA 2026', 'Cybersecurity briefings, trainings, and exhibition for the MEA region.', ['Black Hat Saudi', 'cyber security MEA', 'CISO event KSA']],
  ['World Defense Show 2026', 'Defense, aerospace, and security capabilities exhibition in Riyadh.', ['defense expo Saudi', 'WDS Riyadh', 'defense procurement KSA']],
  ['Global AI Summit 2026', 'National AI strategy, ethics, and enterprise adoption summit.', ['AI summit Saudi', 'SDAIA', 'generative AI KSA']],
  ['INDEX Saudi Arabia 2026', 'Interior design, furniture, and hospitality FF&E exhibition.', ['INDEX Riyadh', 'furniture expo Saudi', 'hospitality design']],
  ['DeepFest 2026', 'AI festival co-located with LEAP in Riyadh.', ['DeepFest Saudi', 'AI conference Riyadh', 'LEAP AI']],
  ['Saudi Warehousing & Logistics Expo 2026', 'Warehousing, automation, and supply-chain technology expo.', ['logistics expo KSA', 'warehouse automation Saudi', 'e-commerce logistics']],
  ['SEA Expo 2026', 'Entertainment, attractions, and leisure industry exhibition.', ['entertainment expo Saudi', 'theme parks KSA', 'FEC Saudi']],
  ['Saudi International Pharma Expo 2026', 'Pharmaceutical manufacturing, distribution, and SFDA context.', ['pharma expo Saudi', 'SFDA pharma', 'healthcare distribution']],
  ['Saudi Build 2026', 'Building materials and construction products exhibition.', ['Saudi Build expo', 'construction Riyadh', 'MEP Saudi']],
  ['JTTX 2026', 'Outbound travel and tourism trade show in Jeddah.', ['JTTX Jeddah', 'travel trade Saudi', 'outbound tourism KSA']],
];

$out = [];
foreach ($defs as [$title, $description, $keywords]) {
  $out[$title] = motaded_custom_seo_row(
    $title,
    $description,
    ' | Business events in Saudi Arabia | Motaded',
    $keywords,
  );
}

return $out;
