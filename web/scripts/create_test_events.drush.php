<?php

/**
 * @file
 * One-off: create demo taxonomy terms (event_type) and test event nodes.
 *
 * Usage: ddev drush php:script scripts/create_test_events.drush.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

$etm = \Drupal::entityTypeManager();

$type_names = ['Networking', 'Conference', 'Workshop'];
$tids = [];
foreach ($type_names as $name) {
  $ids = $etm->getStorage('taxonomy_term')->getQuery()
    ->accessCheck(FALSE)
    ->condition('vid', 'event_type')
    ->condition('name', $name)
    ->range(0, 1)
    ->execute();
  if ($ids) {
    $tids[$name] = (int) reset($ids);
  }
  else {
    $term = Term::create([
      'vid' => 'event_type',
      'name' => $name,
      'status' => 1,
    ]);
    $term->save();
    $tids[$name] = (int) $term->id();
  }
}

$mids = $etm->getStorage('media')->getQuery()
  ->accessCheck(FALSE)
  ->condition('bundle', 'image')
  ->condition('status', 1)
  ->range(0, 1)
  ->execute();
if (!$mids) {
  throw new \RuntimeException('No published image media found. Add at least one image media entity.');
}
$featured_mid = (int) reset($mids);

$definitions = [
  [
    'title' => 'Demo: Business Networking Riyadh',
    'event_type' => 'Networking',
    'summary' => 'Short networking session for founders and investors.',
    'body' => '<p>Join us for coffee and speed introductions. Dress: business casual.</p>',
    'start' => '2026-06-01T17:00:00',
    'end' => '2026-06-01T19:30:00',
    'location' => 'King Abdullah Financial District, Riyadh',
  ],
  [
    'title' => 'Demo: Annual SME Conference 2026',
    'event_type' => 'Conference',
    'summary' => 'Full-day programme on funding and regulation.',
    'body' => '<p>Keynotes, panels, and breakout tracks. Lunch included.</p>',
    'start' => '2026-07-12T09:00:00',
    'end' => '2026-07-12T17:00:00',
    'location' => 'Convention Centre, Jeddah',
  ],
  [
    'title' => 'Demo: UX Workshop (hands-on)',
    'event_type' => 'Workshop',
    'summary' => 'Practical UX exercises for product teams.',
    'body' => '<p>Bring your laptop. Limited to 30 seats.</p>',
    'start' => '2026-08-03T13:00:00',
    'end' => '2026-08-03T16:00:00',
    'location' => 'Innovation Hub, Dammam',
  ],
  [
    'title' => 'Demo: Investor Office Hours',
    'event_type' => 'Networking',
    'summary' => '15-minute slots with selected VCs.',
    'body' => '<p>By application only. Remote slots available.</p>',
    'start' => '2026-09-10T10:00:00',
    'end' => '2026-09-10T14:00:00',
    'location' => 'Virtual + onsite: Riyadh',
  ],
];

$nids = [];
foreach ($definitions as $i => $def) {
  $node = Node::create([
    'type' => 'event',
    'title' => $def['title'],
    'status' => 1,
    'langcode' => 'en',
    'body' => [
      'summary' => $def['summary'],
      'value' => $def['body'],
      'format' => 'basic_html',
    ],
    'field_event_type' => ['target_id' => $tids[$def['event_type']]],
    'field_featured_image' => ['target_id' => $featured_mid],
    'field_event_start' => ['value' => $def['start']],
    'field_event_end' => ['value' => $def['end']],
    'field_location' => $def['location'],
    'field_featured' => (bool) ($i % 2),
    'field_meeting_enabled' => (bool) ($i === 0 || $i === 3),
  ]);
  $node->save();
  $nids[] = $node->id();
}

print 'event_type tids: ' . json_encode($tids) . "\n";
print 'featured media mid: ' . $featured_mid . "\n";
print 'Created event nids: ' . implode(', ', $nids) . "\n";
