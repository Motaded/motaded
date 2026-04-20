<?php

/**
 * @file
 * Create/update language-aware redirects for active zh-hans language.
 *
 * Source path is stored WITHOUT /zh-hans prefix, with redirect language=zh-hans.
 * This makes /zh-hans/<alias> redirect even while zh-hans language is enabled.
 *
 * Run:
 *   drush scr scripts/create_zh_hans_langaware_redirects.php
 */

use Drupal\redirect\Entity\Redirect;

$db = \Drupal::database();
$storage = \Drupal::entityTypeManager()->getStorage('redirect');

$rows = $db->query("
  SELECT CAST(SUBSTRING(pzh.path, 7) AS UNSIGNED) AS nid,
         pzh.alias AS zh_alias,
         COALESCE(pen.alias, pzh.path) AS en_alias
  FROM {path_alias} pzh
  LEFT JOIN {path_alias} pen
    ON pen.path = pzh.path
   AND pen.langcode = :en
   AND pen.status = 1
  WHERE pzh.langcode = :zh
    AND pzh.status = 1
    AND pzh.path LIKE '/node/%'
  ORDER BY nid
", [
  ':en' => 'en',
  ':zh' => 'zh-hans',
])->fetchAll();

$created = 0;
$updated = 0;
$unchanged = 0;

foreach ($rows as $row) {
  $zh_alias = '/' . ltrim((string) $row->zh_alias, '/');
  $en_alias = '/' . ltrim((string) $row->en_alias, '/');

  $source_path = ltrim($zh_alias, '/');
  $target_uri = 'internal:' . $en_alias;

  $existing = $storage->loadByProperties([
    'redirect_source.path' => $source_path,
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
      print "Updated zh-aware: /zh-hans/{$source_path} -> {$target_uri}\n";
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
        'path' => $source_path,
        'query' => [],
      ],
      'redirect_redirect' => [
        'uri' => $target_uri,
      ],
    ]);
    $redirect->save();
    $created++;
    print "Created zh-aware: /zh-hans/{$source_path} -> {$target_uri}\n";
  }
}

print "\nTotal rows: " . count($rows) . "\n";
print "Created: {$created}\n";
print "Updated: {$updated}\n";
print "Unchanged: {$unchanged}\n";

