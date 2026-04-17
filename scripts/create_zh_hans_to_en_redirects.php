<?php

/**
 * @file
 * Create/update 301 redirects from zh-hans node paths to English paths.
 *
 * Run:
 *   drush scr scripts/create_zh_hans_to_en_redirects.php
 */

use Drupal\redirect\Entity\Redirect;

$db = \Drupal::database();
$storage = \Drupal::entityTypeManager()->getStorage('redirect');

$rows = $db->query("
  SELECT z.nid,
         COALESCE(pzh.alias, CONCAT('/node/', z.nid)) AS zh_alias,
         COALESCE(pen.alias, CONCAT('/node/', z.nid)) AS en_alias
  FROM {node_field_data} z
  INNER JOIN {node_field_data} e
    ON e.nid = z.nid AND e.langcode = :en
  LEFT JOIN {path_alias} pzh
    ON pzh.path = CONCAT('/node/', z.nid) AND pzh.langcode = :zh
  LEFT JOIN {path_alias} pen
    ON pen.path = CONCAT('/node/', z.nid) AND pen.langcode = :en
  WHERE z.langcode = :zh
  ORDER BY z.nid
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

  // Redirect module stores source path without leading slash.
  $source_path = ltrim('/zh-hans' . $zh_alias, '/');
  $target_uri = 'internal:' . $en_alias;

  $existing = $storage->loadByProperties(['redirect_source.path' => $source_path]);
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
    if ((string) $redirect->language()->getId() !== 'und') {
      $redirect->set('language', 'und');
      $changed = TRUE;
    }
    if ((int) $redirect->get('enabled')->value !== 1) {
      $redirect->set('enabled', 1);
      $changed = TRUE;
    }

    if ($changed) {
      $redirect->save();
      $updated++;
      print "Updated: /{$source_path} -> {$target_uri}\n";
    }
    else {
      $unchanged++;
    }
  }
  else {
    $redirect = Redirect::create([
      'type' => 'redirect',
      'language' => 'und',
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
    print "Created: /{$source_path} -> {$target_uri}\n";
  }
}

print "\nTotal rows: " . count($rows) . "\n";
print "Created: {$created}\n";
print "Updated: {$updated}\n";
print "Unchanged: {$unchanged}\n";

