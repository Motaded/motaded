<?php

/**
 * @file
 * Creates/updates sector_page: Logistics, Finance & Fintech, Manufacturing, Healthcare.
 *
 * Usage: ddev drush php:script ../scripts/create_sector_pages_batch_5_to_8.php
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
    'eyebrow' => 'LOGISTICS',
    'title' => 'Logistics & Transport',
    'term' => 'Logistics & Transport',
    'short' => 'Positioning Saudi Arabia as a global logistics hub connecting three continents.',
    'body' => '<p>Saudi Arabia is strategically located between Europe, Asia, and Africa, making it a key logistics hub. The National Transport and Logistics Strategy aims to transform the Kingdom into a global supply chain center.</p><p>The sector includes ports, airports, railways, and warehousing infrastructure.</p>',
    'highlights' => '<ul><li>Strategic geographic location</li><li>Investment in ports and airports</li><li>Growth in e-commerce logistics</li><li>Expansion of free zones</li></ul>',
    'stats' => '<ul><li>Target: top global logistics hub by 2030</li><li>Major investments in infrastructure</li><li>Increasing trade volumes</li></ul>',
  ],
  [
    'eyebrow' => 'FINANCE',
    'title' => 'Finance & Fintech',
    'term' => 'Finance & Fintech',
    'short' => 'Expanding financial services and fintech innovation across the Kingdom.',
    'body' => '<p>Saudi Arabia’s financial sector is rapidly evolving, with strong growth in fintech, digital banking, and capital markets. Riyadh is positioning itself as a regional financial hub, supported by regulatory reforms and innovation initiatives.</p><p>The Saudi Central Bank (SAMA) and Capital Market Authority (CMA) drive sector development.</p>',
    'highlights' => '<ul><li>Growth in fintech startups</li><li>Expansion of capital markets</li><li>Regulatory support for innovation</li><li>Digital banking transformation</li></ul>',
    'stats' => '<ul><li>Fintech sector growing rapidly</li><li>Strong IPO activity in Saudi markets</li><li>Increased foreign investment</li></ul>',
  ],
  [
    'eyebrow' => 'MANUFACTURING',
    'title' => 'Manufacturing',
    'term' => 'Manufacturing',
    'short' => 'Developing local industries and boosting industrial production capabilities.',
    'body' => '<p>Saudi Arabia is investing heavily in manufacturing to reduce reliance on imports and strengthen local production. The sector includes industrial zones, petrochemicals, automotive, and advanced manufacturing.</p><p>Programs like "Made in Saudi" support local industry growth.</p>',
    'highlights' => '<ul><li>Industrial diversification strategy</li><li>Growth in local production</li><li>Support for SMEs and factories</li><li>Development of industrial cities</li></ul>',
    'stats' => '<ul><li>Significant contribution to GDP</li><li>Expansion of industrial zones</li><li>Government incentives for manufacturers</li></ul>',
  ],
  [
    'eyebrow' => 'HEALTHCARE',
    'title' => 'Healthcare',
    'term' => 'Healthcare',
    'short' => 'Expanding healthcare infrastructure and private sector participation.',
    'body' => '<p>Saudi Arabia is modernizing its healthcare system with increased private sector involvement and digital health solutions. The sector includes hospitals, clinics, telemedicine, and pharmaceuticals.</p>',
    'highlights' => '<ul><li>Growing demand for healthcare services</li><li>Investment in hospital infrastructure</li><li>Digital health transformation</li><li>Public-private partnerships</li></ul>',
    'stats' => NULL,
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
  if ($row['stats'] !== NULL) {
    $node->set('field_sector_stats', [
      'value' => $row['stats'],
      'format' => 'basic_html',
    ]);
  }
  else {
    $node->set('field_sector_stats', []);
  }
  $node->setPublished();
  $node->save();
  \Drupal::logger('motaded')->notice('Sector page saved: @title (nid=@nid)', [
    '@title' => $row['title'],
    '@nid' => $node->id(),
  ]);
}
