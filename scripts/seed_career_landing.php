<?php

/**
 * @file
 * Career landing page at /career (replaces page node /services/career).
 *
 * Usage: drush php:script scripts/seed_career_landing.php
 */

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\redirect\Entity\Redirect;

/**
 * Upserts a path alias for a node path.
 */
function _motaded_seed_career_upsert_alias(string $path, string $alias, string $langcode): void {
  $storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  $existing = $storage->loadByProperties([
    'path' => $path,
    'langcode' => $langcode,
  ]);
  $entity = $existing ? reset($existing) : PathAlias::create([
    'path' => $path,
    'langcode' => $langcode,
  ]);
  $entity->set('alias', $alias);
  $entity->save();
  echo "Alias {$langcode}: {$alias} -> {$path}\n";
}

/**
 * Creates or updates a 301 redirect.
 */
function _motaded_seed_career_upsert_redirect(string $source, string $destination, string $langcode): void {
  $source = trim($source, '/');
  $storage = \Drupal::entityTypeManager()->getStorage('redirect');
  $existing = $storage->loadByProperties([
    'redirect_source__path' => $source,
    'language' => $langcode,
  ]);
  $redirect = $existing ? reset($existing) : Redirect::create([
    'redirect_source' => ['path' => $source],
    'language' => $langcode,
    'status_code' => 301,
  ]);
  $destination = '/' . trim($destination, '/');
  $redirect->set('redirect_redirect', ['uri' => 'internal:' . $destination]);
  $redirect->set('status_code', 301);
  $redirect->save();
  echo "Redirect {$langcode}: /{$source} -> /{$destination}\n";
}

$storage = \Drupal::entityTypeManager()->getStorage('node');

// Reuse existing career landing if present (by alias).
$career_nid = NULL;
$alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
$aliases = $alias_storage->loadByProperties(['alias' => '/career', 'langcode' => 'en']);
if ($aliases) {
  $path = reset($aliases)->get('path')->value;
  if (preg_match('/node\/(\d+)/', $path, $m)) {
    $career_nid = (int) $m[1];
  }
}

$node = $career_nid ? $storage->load($career_nid) : NULL;
if (!$node instanceof Node || $node->bundle() !== 'landing_page') {
  $node = Node::create([
    'type' => 'landing_page',
    'title' => 'Career',
    'langcode' => 'en',
    'status' => 1,
    'uid' => 1,
  ]);
  echo "Created new career landing_page node.\n";
}
else {
  echo "Updating career landing_page nid={$node->id()}.\n";
}

$node->setTitle('Career');
$node->setPublished(TRUE);
$node->set('field_form', ['target_id' => 'career']);
$node->set('body', []);
$node->set('field_paragraphs', []);
$node->save();

$nid = (int) $node->id();
$path = '/node/' . $nid;

_motaded_seed_career_upsert_alias($path, '/career', 'en');

if (!$node->hasTranslation('ar')) {
  $ar = $node->addTranslation('ar', [
    'title' => 'حياة مهنية',
    'status' => 1,
  ]);
  $ar->set('field_form', ['target_id' => 'career']);
  $ar->save();
  echo "Added AR translation.\n";
}
else {
  $ar = $node->getTranslation('ar');
  $ar->setTitle('حياة مهنية');
  $ar->setPublished(TRUE);
  $ar->set('field_form', ['target_id' => 'career']);
  $ar->save();
  echo "Updated AR translation.\n";
}

_motaded_seed_career_upsert_alias($path, '/ar/career', 'ar');

// Retire legacy page node (/services/career).
$legacy = $storage->load(106);
if ($legacy instanceof Node && $legacy->bundle() === 'page') {
  $legacy->setUnpublished();
  $legacy->save();
  echo "Unpublished legacy page nid=106.\n";
}

_motaded_seed_career_upsert_redirect('services/career', '/career', 'en');
_motaded_seed_career_upsert_redirect('ar/services/career', '/ar/career', 'ar');

echo "Career landing ready: nid={$nid} EN /career AR /ar/career\n";
