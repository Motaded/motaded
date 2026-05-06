<?php

/**
 * @file
 * Creates/updates sector_page nodes for Energy and Construction & Infrastructure.
 *
 * Usage: ddev drush php:script ../scripts/create_sector_pages_energy_construction.php
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
    'eyebrow' => 'ENERGY',
    'title' => 'Energy',
    'term' => 'Energy',
    'short' => 'Leading global energy production while accelerating investment in renewables and hydrogen.',
    'body' => '<p>Saudi Arabia remains the world’s largest oil exporter while simultaneously investing in renewable energy and green hydrogen. The Kingdom is developing large-scale solar and wind projects as part of its transition to a more sustainable energy mix.</p><p>Initiatives such as NEOM’s hydrogen project position Saudi Arabia as a future leader in clean energy.</p>',
    'highlights' => '<ul><li>Global leader in oil production</li><li>Strong push into renewable energy</li><li>Hydrogen economy development</li><li>Large-scale solar and wind investments</li></ul>',
    'stats' => '<ul><li>Target: 50% renewable energy by 2030</li><li>Among top global crude oil exporters</li><li>Multi-billion investments in clean energy</li></ul>',
  ],
  [
    'eyebrow' => 'CONSTRUCTION & INFRASTRUCTURE',
    'title' => 'Construction & Infrastructure',
    'term' => 'Construction & Infrastructure',
    'short' => 'Powering giga-projects and large-scale infrastructure development across the Kingdom.',
    'body' => '<p>Saudi Arabia’s construction sector is experiencing unprecedented growth, driven by Vision 2030 giga-projects such as NEOM, The Line, and Qiddiya. The sector includes residential, commercial, transport, and smart infrastructure development.</p><p>The government continues to invest heavily in urban expansion and logistics infrastructure.</p>',
    'highlights' => '<ul><li>Giga-projects driving demand</li><li>Smart cities and urban development</li><li>Infrastructure modernization</li><li>Strong demand for contractors and suppliers</li></ul>',
    'stats' => '<ul><li>Hundreds of billions in active projects</li><li>One of the largest construction markets globally</li><li>Rapid urbanization across key cities</li></ul>',
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
