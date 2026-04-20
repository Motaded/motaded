<?php

/**
 * @file
 * Convert prefixed zh-hans redirects to language-aware redirects.
 *
 * For each redirect with source "zh-hans/<path>" create/update:
 *   language = zh-hans
 *   source   = "<path>"
 *   target   = same target
 *
 * Run:
 *   drush scr scripts/create_zh_hans_prefix_langaware_redirects.php
 */

use Drupal\redirect\Entity\Redirect;

$db = \Drupal::database();
$storage = \Drupal::entityTypeManager()->getStorage('redirect');

$rows = $db->query("
  SELECT redirect_source__path AS src, redirect_redirect__uri AS dst
  FROM {redirect}
  WHERE redirect_source__path LIKE 'zh-hans/%'
    AND enabled = 1
    AND status_code = 301
")->fetchAll();

$created = 0;
$updated = 0;
$unchanged = 0;

foreach ($rows as $row) {
  $src_prefixed = (string) $row->src;
  $src_langaware = preg_replace('#^zh-hans/#', '', $src_prefixed);
  if (!$src_langaware) {
    continue;
  }

  $target_uri = (string) $row->dst;

  $existing = $storage->loadByProperties([
    'redirect_source.path' => $src_langaware,
    'language' => 'zh-hans',
  ]);
  /** @var \Drupal\redirect\Entity\Redirect|null $redirect */
  $redirect = $existing ? reset($existing) : NULL;

  if ($redirect) {
    $changed = FALSE;
    if ((string) $redirect->get('redirect_redirect')->uri !== $target_uri) {
      $redirect->set('redirect_redirect', ['uri' => $target_uri]);
      $changed = TRUE;
    }
    if ((int) $redirect->getStatusCode() !== 301) {
      $redirect->setStatusCode(301);
      $changed = TRUE;
    }
    if ((int) $redirect->get('enabled')->value !== 1) {
      $redirect->set('enabled', 1);
      $changed = TRUE;
    }

    if ($changed) {
      $redirect->save();
      $updated++;
    }
    else {
      $unchanged++;
    }
  }
  else {
    $redirect = Redirect::create([
      'type' => 'redirect',
      'language' => 'zh-hans',
      'uid' => 1,
      'enabled' => 1,
      'status_code' => 301,
      'redirect_source' => [
        'path' => $src_langaware,
        'query' => [],
      ],
      'redirect_redirect' => [
        'uri' => $target_uri,
      ],
    ]);
    $redirect->save();
    $created++;
  }
}

print "Rows scanned: " . count($rows) . "\n";
print "Created: {$created}\n";
print "Updated: {$updated}\n";
print "Unchanged: {$unchanged}\n";

