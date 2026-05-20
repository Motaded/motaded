<?php

/**
 * @file
 * Set basic_html on text fields that contain HTML but were saved as plain_text.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/fix_text_field_html_formats.php
 *   drush mftf
 */

declare(strict_types=1);

use Drupal\Core\Entity\FieldableEntityInterface;

$entity_types = ['node', 'paragraph'];
$field_types = ['text_long', 'text_with_summary'];
$fixed = 0;
$entities = 0;

foreach ($entity_types as $entity_type_id) {
  $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
  $ids = $storage->getQuery()->accessCheck(FALSE)->execute();
  foreach ($storage->loadMultiple($ids) as $entity) {
    if (!$entity instanceof FieldableEntityInterface) {
      continue;
    }
    $changed = FALSE;
    foreach ($entity->getFieldDefinitions() as $field_name => $definition) {
      if (!in_array($definition->getType(), $field_types, TRUE)) {
        continue;
      }
      if (!$definition->isTranslatable()) {
        continue;
      }
      foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
        $translation = $entity->getTranslation($langcode);
        if ($translation->get($field_name)->isEmpty()) {
          continue;
        }
        $item = $translation->get($field_name)->first();
        if ($item === NULL) {
          continue;
        }
        $value = (string) ($item->value ?? '');
        $format = (string) ($item->format ?? 'plain_text');
        if ($format !== 'plain_text' || !preg_match('/<\s*\w+/u', $value)) {
          continue;
        }
        $row = $item->getValue();
        $row['format'] = 'basic_html';
        $translation->set($field_name, [$row]);
        $changed = TRUE;
        $fixed++;
      }
    }
    if ($changed) {
      $entity->save();
      $entities++;
    }
  }
}

print sprintf("Text formats: %d field values updated on %d entities.\n", $fixed, $entities);
