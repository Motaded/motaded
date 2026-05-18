<?php

/**
 * @file
 * Apply Arabic translations for sector_page + event nodes and related taxonomy.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_homepage_sectors_events_ar.php
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/** @var array<string, mixed> $DATA */
$DATA = require dirname(__DIR__) . '/data/homepage_sectors_events_ar.php';

$ensure_node_ar = static function (NodeInterface $node, string $title, ?string $summary = NULL): void {
  if (!$node->hasTranslation('ar')) {
    $node->addTranslation('ar', $node->getTranslation('en')->toArray());
  }
  $ar = $node->getTranslation('ar');
  $ar->setTitle($title);
  if ($summary !== NULL && $node->hasField('body')) {
    $en_body = $node->getTranslation('en')->get('body');
    $row = $en_body->isEmpty() ? ['format' => 'basic_html'] : $en_body->getValue()[0];
    $row['summary'] = $summary;
    if ($en_body->isEmpty()) {
      $row['value'] = '<p>' . htmlspecialchars($summary, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }
    if (preg_match('/<\s*\w+/u', (string) ($row['value'] ?? ''))) {
      $row['format'] = 'basic_html';
    }
    $ar->set('body', [$row]);
  }
  $ar->setPublished($node->isPublished());
  $node->save();
};

$ensure_term_ar = static function (string $vid, string $en_name, string $ar_name): void {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $terms = $storage->loadByProperties(['vid' => $vid, 'name' => $en_name]);
  if ($terms === []) {
    return;
  }
  $term = reset($terms);
  if (!$term instanceof TermInterface) {
    return;
  }
  if (!$term->hasTranslation('ar')) {
    $term->addTranslation('ar', ['name' => $ar_name, 'status' => 1]);
  }
  else {
    $term->getTranslation('ar')->setName($ar_name);
  }
  $term->save();
  \Drupal::logger('motaded')->notice('Term @vid: @en → @ar', [
    '@vid' => $vid,
    '@en' => $en_name,
    '@ar' => $ar_name,
  ]);
};

// Sector taxonomy terms.
foreach (($DATA['sector_terms'] ?? []) as $en => $ar) {
  $ensure_term_ar('sector', (string) $en, (string) $ar);
}

// Event type + region taxonomy.
foreach (($DATA['taxonomy'] ?? []) as $vid => $map) {
  if (!is_array($map)) {
    continue;
  }
  foreach ($map as $en => $ar) {
    $ensure_term_ar((string) $vid, (string) $en, (string) $ar);
  }
}

$node_storage = \Drupal::entityTypeManager()->getStorage('node');

// Sector pages.
foreach (($DATA['sector_pages'] ?? []) as $en_title => $row) {
  if (!is_array($row)) {
    continue;
  }
  $nids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'sector_page')
    ->condition('title', (string) $en_title)
    ->range(0, 1)
    ->execute();
  if ($nids === []) {
    \Drupal::logger('motaded')->warning('sector_page not found: @t', ['@t' => $en_title]);
    continue;
  }
  $node = $node_storage->load((int) reset($nids));
  if ($node instanceof NodeInterface) {
    $ensure_node_ar($node, (string) ($row['title'] ?? $en_title));
    \Drupal::logger('motaded')->notice('sector_page AR: @t', ['@t' => $en_title]);
  }
}

// Events (mapped titles + all other published events get title-only AR if missing).
$mapped_events = $DATA['events'] ?? [];
$event_nids = $node_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'event')
  ->condition('status', 1)
  ->execute();
foreach ($event_nids as $nid) {
  $node = $node_storage->load((int) $nid);
  if (!$node instanceof NodeInterface) {
    continue;
  }
  $en_title = $node->getTranslation('en')->label();
  if (isset($mapped_events[$en_title]) && is_array($mapped_events[$en_title])) {
    $row = $mapped_events[$en_title];
    $ensure_node_ar(
      $node,
      (string) ($row['title'] ?? $en_title),
      isset($row['summary']) ? (string) $row['summary'] : NULL,
    );
    \Drupal::logger('motaded')->notice('event AR: @t', ['@t' => $en_title]);
  }
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'taxonomy_term_list']);
print "Homepage sectors/events Arabic translations applied.\n";
