<?php

/**
 * @file
 * Creates/updates sector_page nodes for Technology and Tourism + Sector terms.
 *
 * Usage (from project root): ddev drush php:script ../scripts/create_sector_pages_tech_tourism.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

$ensure_term = static function (string $name): int {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $terms = $storage->loadByProperties(['vid' => 'sector', 'name' => $name]);
  if ($terms !== []) {
    $term = reset($terms);
    return (int) $term->id();
  }
  $term = Term::create([
    'vid' => 'sector',
    'name' => $name,
    'langcode' => 'en',
    'status' => 1,
  ]);
  $term->save();
  return (int) $term->id();
};

$items = [
  [
    'eyebrow' => 'TECHNOLOGY',
    'title' => 'Technology',
    'term' => 'Technology',
    'short' => 'Driving digital transformation, AI innovation, and fintech growth across Saudi Arabia.',
    'body' => '<p>Saudi Arabia’s technology sector is a key pillar of Vision 2030, focused on digital transformation, artificial intelligence, fintech, and smart infrastructure. The Kingdom is investing heavily in data centers, cloud services, and emerging technologies to position itself as a regional digital hub.</p><p>Major initiatives include NEOM’s cognitive city development, national AI strategies, and rapid growth in fintech adoption. The government actively supports startups, innovation ecosystems, and international tech partnerships.</p>',
    'highlights' => '<ul><li>Strong government backing under Vision 2030</li><li>Rapid growth in fintech and digital payments</li><li>Expansion of data centers and cloud infrastructure</li><li>AI and smart city initiatives (NEOM)</li></ul>',
    'stats' => '<ul><li>Digital economy expected to exceed 19% of GDP</li><li>Saudi Arabia is the largest ICT market in MENA</li><li>Fintech sector growing at double-digit rates</li></ul>',
  ],
  [
    'eyebrow' => 'TOURISM',
    'title' => 'Tourism',
    'term' => 'Tourism',
    'short' => 'Transforming Saudi Arabia into a global tourism destination through mega-projects and cultural heritage.',
    'body' => '<p>Tourism is one of the fastest-growing sectors in Saudi Arabia, driven by giga-projects such as NEOM, Red Sea Global, and Diriyah Gate. The Kingdom aims to attract over 100 million visitors annually by 2030, positioning itself as a leading global destination.</p><p>The sector includes religious tourism (Hajj &amp; Umrah), leisure tourism, luxury resorts, and entertainment experiences.</p>',
    'highlights' => '<ul><li>Massive government investment in giga-projects</li><li>Opening the country to international tourism</li><li>Growth in hospitality and entertainment sectors</li><li>Expansion of visa programs</li></ul>',
    'stats' => '<ul><li>Target: 100M+ visitors annually by 2030</li><li>Tourism contributes significantly to GDP diversification</li><li>Billions invested in Red Sea &amp; NEOM projects</li></ul>',
  ],
];

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$uid = 1;

foreach ($items as $row) {
  $tid = $ensure_term($row['term']);
  $nids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'sector_page')
    ->condition('title', $row['title'])
    ->range(0, 1)
    ->execute();
  if ($nids !== []) {
    $nid = (int) reset($nids);
    $node = $node_storage->load($nid);
  }
  else {
    $node = Node::create([
      'type' => 'sector_page',
      'title' => $row['title'],
      'langcode' => 'en',
      'uid' => $uid,
      'status' => 1,
    ]);
  }
  assert($node instanceof Node);
  $node->set('field_sector_eyebrow', $row['eyebrow']);
  $node->set('field_short_description', $row['short']);
  $node->set('field_sector', [['target_id' => $tid]]);
  $node->set('body', [
    'value' => $row['body'],
    'format' => 'basic_html',
  ]);
  $node->set('field_sector_highlights', [
    'value' => $row['highlights'],
    'format' => 'basic_html',
  ]);
  $node->set('field_sector_stats', [
    'value' => $row['stats'],
    'format' => 'basic_html',
  ]);
  $node->setPublished();
  $node->save();
  \Drupal::logger('motaded')->notice('Sector page saved: @title (nid=@nid)', [
    '@title' => $row['title'],
    '@nid' => $node->id(),
  ]);
}
