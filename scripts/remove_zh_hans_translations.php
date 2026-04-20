<?php

/**
 * @file
 * Remove zh-hans translations for content entities.
 *
 * Run:
 *   drush scr scripts/remove_zh_hans_translations.php
 */

$langcode = 'zh-hans';

$entity_types = [
  'node',
  'taxonomy_term',
];

foreach ($entity_types as $entity_type_id) {
  $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('langcode', $langcode)
    ->execute();

  $removed = 0;
  $skipped_default = 0;

  if ($ids) {
    $entities = $storage->loadMultiple($ids);
    foreach ($entities as $entity) {
      if (!$entity->hasTranslation($langcode)) {
        continue;
      }

      if ($entity->language()->getId() === $langcode) {
        $skipped_default++;
        print "Skipped default {$entity_type_id}: {$entity->id()}\n";
        continue;
      }

      $translation = $entity->getTranslation($langcode);
      $translation->removeTranslation($langcode);
      $entity->save();
      $removed++;
      print "Removed {$entity_type_id} zh-hans: {$entity->id()}\n";
    }
  }

  print strtoupper($entity_type_id) . " summary: removed={$removed}, skipped_default={$skipped_default}\n";
}

