<?php

/**
 * @file
 * One-off import for Chamber nodes. Run:
 *   ddev drush php:script scripts/import-chambers.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

$country_keys = [
  'Saudi Arabia' => 'saudi_arabia',
  'United Kingdom' => 'uk',
  'USA' => 'usa',
  'Germany' => 'germany',
  'France' => 'france',
  'UAE' => 'uae',
  'Bahrain' => 'bahrain',
  'India' => 'india',
  'Canada' => 'canada',
  'Australia' => 'australia',
  'Italy' => 'italy',
  'Spain' => 'spain',
  'Japan' => 'japan',
  'South Korea' => 'south_korea',
  'China' => 'china',
  'Switzerland' => 'switzerland',
  'Netherlands' => 'netherlands',
];

$type_keys = [
  'Chamber of Commerce' => 'chamber_of_commerce',
  'Business Council' => 'business_council',
  'Trade Association' => 'trade_association',
];

/** @var list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string, 6: int, 7: bool}> $rows */
$rows = [
  ['Saudi Chambers', 'Official federation representing Saudi Arabia’s regional chambers of commerce and supporting private sector development.', 'Saudi Arabia', 'Chamber of Commerce', 'https://www.saudichambers.sa', 'National business representation', 1, TRUE],
  ['Riyadh Chamber of Commerce', 'Supports business growth and investment activities in Riyadh through services and advocacy.', 'Saudi Arabia', 'Chamber of Commerce', 'https://www.chamber.sa', 'Local business support', 2, TRUE],
  ['Jeddah Chamber of Commerce', 'Facilitates trade and business development in Jeddah and western Saudi Arabia.', 'Saudi Arabia', 'Chamber of Commerce', 'https://www.jcci.org.sa', 'Trade & investment', 3, TRUE],
  ['Eastern Province Chamber', 'Provides services and support for businesses in the Eastern Province, a key industrial region.', 'Saudi Arabia', 'Chamber of Commerce', 'https://www.chamber.org.sa', 'Industrial development', 4, TRUE],
  ['British Saudi Business Council', 'Promotes trade and investment between the United Kingdom and Saudi Arabia.', 'United Kingdom', 'Business Council', 'https://www.bsbc.org.uk', 'UK-Saudi trade relations', 5, TRUE],
  ['US-Saudi Business Council', 'Strengthens commercial ties and investment between the United States and Saudi Arabia.', 'USA', 'Business Council', 'https://www.ussbc.org', 'US-Saudi investment', 6, TRUE],
  ['Saudi French Business Council', 'Encourages economic cooperation and partnerships between France and Saudi Arabia.', 'France', 'Business Council', 'https://www.medefinternational.fr', 'France-Saudi cooperation', 7, FALSE],
  ['Saudi German Liaison Office for Economic Affairs', 'Supports German companies entering the Saudi market and strengthens bilateral relations.', 'Germany', 'Trade Association', 'https://saudiarabien.ahk.de', 'Market entry support', 8, FALSE],
  ['UAE-Saudi Business Council', 'Enhances economic collaboration and trade between the UAE and Saudi Arabia.', 'UAE', 'Business Council', 'https://www.uaesaudibusinesscouncil.org', 'Regional trade cooperation', 9, FALSE],
  ['Bahrain Saudi Business Council', 'Facilitates bilateral trade and investment opportunities between Bahrain and Saudi Arabia.', 'Bahrain', 'Business Council', 'https://www.bahrainsaudi.org', 'Regional investment', 10, FALSE],
  ['Indian Business and Professional Council Riyadh', 'Supports Indian businesses and professionals operating in Saudi Arabia.', 'India', 'Trade Association', 'https://www.ibpc-ksa.com', 'Business networking', 11, FALSE],
  ['Canadian Business Council Riyadh', 'Represents Canadian companies and promotes trade relations with Saudi Arabia.', 'Canada', 'Business Council', 'https://www.cbcksa.com', 'Canada-Saudi trade', 12, FALSE],
  ['Australian Saudi Business Council', 'Strengthens business relations between Australia and Saudi Arabia.', 'Australia', 'Business Council', 'https://www.austrade.gov.au', 'Australia-Saudi cooperation', 13, FALSE],
  ['Italian Saudi Business Council', 'Facilitates partnerships between Italian and Saudi companies across sectors.', 'Italy', 'Business Council', 'https://www.assolombarda.it', 'Italy-Saudi business', 14, FALSE],
  ['Spanish Saudi Business Council', 'Supports trade and investment collaboration between Spain and Saudi Arabia.', 'Spain', 'Business Council', 'https://www.icex.es', 'Spain-Saudi relations', 15, FALSE],
  ['Japanese Saudi Business Council', 'Promotes industrial cooperation and investment between Japan and Saudi Arabia.', 'Japan', 'Business Council', 'https://www.jetro.go.jp', 'Japan-Saudi cooperation', 16, FALSE],
  ['Korean Saudi Business Council', 'Enhances economic partnerships between South Korea and Saudi Arabia.', 'South Korea', 'Business Council', 'https://www.kotra.or.kr', 'Korea-Saudi trade', 17, FALSE],
  ['Chinese Saudi Business Council', 'Supports Chinese companies and investment activities in Saudi Arabia.', 'China', 'Business Council', 'https://www.ccpit.org', 'China-Saudi investment', 18, FALSE],
  ['Swiss Saudi Business Council', 'Encourages Swiss business presence and partnerships in Saudi Arabia.', 'Switzerland', 'Business Council', 'https://www.s-ge.com', 'Swiss-Saudi cooperation', 19, FALSE],
  ['Dutch Saudi Business Council', 'Facilitates trade relations and business opportunities between the Netherlands and Saudi Arabia.', 'Netherlands', 'Business Council', 'https://www.rvo.nl', 'Netherlands-Saudi trade', 20, FALSE],
];

/** @var \Drupal\Core\Entity\Query\QueryInterface $q */
$nid_list = [];
/** @var \Drupal\node\NodeStorageInterface $storage */
$storage = \Drupal::entityTypeManager()->getStorage('node');

foreach ($rows as $row) {
  [$title, $desc, $country_label, $type_label, $url, $focus, $priority, $featured] = $row;

  if (!isset($country_keys[$country_label])) {
    echo "SKIP (unknown country): {$title}\n";
    continue;
  }
  if (!isset($type_keys[$type_label])) {
    echo "SKIP (unknown type): {$title}\n";
    continue;
  }

  // Normalize URLs for Drupal link field (external URIs).
  $uri = $url;
  if (!str_starts_with($uri, 'http://') && !str_starts_with($uri, 'https://')) {
    $uri = 'https://' . ltrim($uri, '/');
  }

  /** @var int[] $existing */
  $existing = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'chamber')
    ->condition('title', $title)
    ->range(0, 1)
    ->execute();
  $node = NULL;
  if ($existing) {
    $loaded = $storage->load(reset($existing));
    $node = $loaded instanceof NodeInterface ? $loaded : NULL;
  }
  if (!$node) {
    $node = Node::create(['type' => 'chamber', 'title' => $title, 'status' => 1]);
  }

  $node->set('title', $title);
  $node->set('field_short_description', $desc);
  $node->set('field_country', $country_keys[$country_label]);
  $node->set('field_type', $type_keys[$type_label]);
  $node->set('field_relationship_focus', $focus);
  $node->set('field_priority', $priority);
  $node->set('field_featured', $featured ? 1 : 0);
  // Main CTA: open in new tab (matches view display defaults; options stored on entity).
  $node->set('field_external_link', [
    [
      'uri' => $uri,
      'title' => '',
      'options' => [
        'attributes' => [
          'target' => '_blank',
          'rel' => 'noopener noreferrer',
        ],
      ],
    ],
  ]);

  $node->save();
  $nid_list[] = (int) $node->id();
  echo (($existing ? 'UPDATED ' : 'CREATED ') . $node->id() . ": {$title}\n");
}

echo "\nDone. Count: " . count($nid_list) . "\n";
