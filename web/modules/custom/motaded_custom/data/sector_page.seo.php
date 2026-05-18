<?php

/**
 * @file
 * SEO meta for sector_page nodes (key = EN title).
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var list<array{0: string, 1: string, 2: list<string>}> $defs */
$defs = [
  ['Technology', 'Digital transformation, AI, fintech, and cloud investment in Saudi Arabia.', ['technology sector KSA', 'digital economy Saudi', 'AI Vision 2030']],
  ['Tourism', 'Tourism, hospitality, and entertainment investment in Saudi Arabia.', ['tourism sector Saudi', 'Visit Saudi', 'hospitality investment']],
  ['Logistics & Transport', 'Ports, aviation, and supply-chain hub strategy in KSA.', ['logistics Saudi', 'ports KSA', 'supply chain']],
  ['Finance & Fintech', 'Banking, capital markets, and fintech growth in Saudi Arabia.', ['fintech Saudi', 'SAMA', 'Tadawul']],
  ['Manufacturing', 'Industrial cities, localization, and manufacturing GDP in KSA.', ['manufacturing Saudi', 'MODON', 'localisation']],
  ['Healthcare', 'Health transformation, providers, and medtech in Saudi Arabia.', ['healthcare Saudi', 'health sector KSA', 'MOH']],
  ['Energy', 'Oil, gas, renewables, and hydrogen in Saudi Arabia.', ['energy sector KSA', 'renewables Saudi', 'Aramco']],
  ['Construction & Infrastructure', 'Giga-projects, housing, and contractors in Saudi Arabia.', ['construction Saudi', 'giga projects', 'infrastructure KSA']],
];

$out = [];
foreach ($defs as [$title, $description, $keywords]) {
  $out[$title] = motaded_custom_seo_row(
    $title,
    $description,
    ' | Investment sectors in Saudi Arabia | Motaded',
    array_merge([$title . ' sector Saudi Arabia', 'Vision 2030 ' . $title], $keywords),
  );
}

return $out;
