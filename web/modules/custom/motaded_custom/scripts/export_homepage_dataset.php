<?php

/**
 * @file
 * Export homepage node 374 + paragraph tree to data/homepage_export.php.
 *
 * Must run against local `db` (not motaded_prod):
 *   ddev exec env DRUPAL_DB= drush php:script modules/custom/motaded_custom/scripts/export_homepage_dataset.php
 *
 * Or: drush mhome-export
 */

declare(strict_types=1);

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\TermInterface;

const MOTADED_HOMEPAGE_NID = 374;

/**
 * @return list<string>
 */
function motaded_homepage_skip_fields(): array {
  return [
    'field_paragraphs',
    'id',
    'uuid',
    'revision_id',
    'revision_uid',
    'revision_timestamp',
    'revision_log',
    'revision_default',
    'langcode',
    'type',
    'status',
    'created',
    'changed',
    'uid',
    'parent_id',
    'parent_type',
    'parent_field_name',
    'behavior_settings',
    'default_langcode',
    'revision_translation_affected',
    'content_translation_source',
    'content_translation_outdated',
    'content_translation_uid',
    'content_translation_status',
    'content_translation_created',
  ];
}

/**
 * @return array<string, mixed>|null
 */
function motaded_homepage_export_media(?MediaInterface $media): ?array {
  if ($media === NULL) {
    return NULL;
  }
  $file = NULL;
  if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
    $file = $media->get('field_media_image')->entity;
  }
  elseif ($media->hasField('field_media_document') && !$media->get('field_media_document')->isEmpty()) {
    $file = $media->get('field_media_document')->entity;
  }
  $uri = $file?->getFileUri();

  return [
    'uuid' => $media->uuid(),
    'bundle' => $media->bundle(),
    'name' => $media->label(),
    'uri' => $uri,
  ];
}

/**
 * @return array<string, mixed>|null
 */
function motaded_homepage_export_term(?TermInterface $term): ?array {
  if ($term === NULL) {
    return NULL;
  }
  return [
    'vid' => $term->bundle(),
    'name' => $term->label(),
  ];
}

/**
 * Exports one field value list for storage in dataset.
 *
 * @return mixed
 */
function motaded_homepage_export_field_value(string $fieldType, mixed $items, string $targetType = ''): mixed {
  if ($items === NULL || $items === []) {
    return [];
  }

  if ($fieldType === 'entity_reference_revisions') {
    $children = [];
    foreach ($items as $item) {
      $entity = $item->entity ?? NULL;
      if ($entity instanceof ParagraphInterface) {
        $children[] = motaded_homepage_export_paragraph($entity);
      }
    }
    return $children;
  }

  if ($fieldType === 'entity_reference') {
    if ($targetType === 'media') {
      $out = [];
      foreach ($items as $item) {
        $entity = $item->entity ?? NULL;
        if ($entity instanceof MediaInterface) {
          $out[] = motaded_homepage_export_media($entity);
        }
      }
      return $out;
    }
    if ($targetType === 'taxonomy_term') {
      $out = [];
      foreach ($items as $item) {
        $entity = $item->entity ?? NULL;
        if ($entity instanceof TermInterface) {
          $exported = motaded_homepage_export_term($entity);
          if ($exported !== NULL) {
            $out[] = $exported;
          }
        }
      }
      return $out;
    }
    if ($targetType === 'node') {
      $out = [];
      foreach ($items as $item) {
        $entity = $item->entity ?? NULL;
        if ($entity instanceof NodeInterface) {
          $out[] = [
            'bundle' => $entity->bundle(),
            'title' => $entity->getTitle(),
          ];
        }
      }
      return $out;
    }
  }

  if (is_object($items) && method_exists($items, 'getValue')) {
    return $items->getValue();
  }

  return $items;
}

/**
 * @return array<string, mixed>
 */
function motaded_homepage_export_entity_fields(object $entity, string $langcode): array {
  $skip = motaded_homepage_skip_fields();
  $fields = [];
  foreach ($entity->getFields() as $name => $field) {
    if (in_array($name, $skip, TRUE) || $field->isEmpty()) {
      continue;
    }
    $def = $field->getFieldDefinition();
    $type = $def->getType();
    $target = (string) $def->getSetting('target_type');
    $fields[$name] = motaded_homepage_export_field_value($type, $entity->get($name), $target);
  }
  return $fields;
}

/**
 * @return array<string, mixed>
 */
function motaded_homepage_export_paragraph(ParagraphInterface $paragraph): array {
  $source = $paragraph->getUntranslated();
  $langcode = $source->language()->getId();
  $en = $paragraph->hasTranslation('en') ? $paragraph->getTranslation('en') : $source;

  $data = [
    'type' => $paragraph->bundle(),
    'fields' => motaded_homepage_export_entity_fields($en, 'en'),
    'translations' => [],
  ];

  if ($paragraph->hasTranslation('ar')) {
    $data['translations']['ar'] = motaded_homepage_export_entity_fields($paragraph->getTranslation('ar'), 'ar');
  }

  return $data;
}

$node = \Drupal::entityTypeManager()->getStorage('node')->load(MOTADED_HOMEPAGE_NID);
if (!$node instanceof NodeInterface) {
  throw new \RuntimeException('Homepage node ' . MOTADED_HOMEPAGE_NID . ' not found in ' . \Drupal::database()->getConnectionOptions()['database']);
}

$en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
$paragraphs = [];
foreach ($en->get('field_paragraphs') as $item) {
  $p = $item->entity;
  if ($p instanceof ParagraphInterface) {
    $paragraphs[] = motaded_homepage_export_paragraph($p);
  }
}

$dataset = [
  'version' => 1,
  'nid' => MOTADED_HOMEPAGE_NID,
  'exported_at' => gmdate('c'),
  'source_database' => \Drupal::database()->getConnectionOptions()['database'] ?? '',
  'node' => [
    'fields' => motaded_homepage_export_entity_fields($en, 'en'),
    'translations' => $node->hasTranslation('ar')
      ? ['ar' => motaded_homepage_export_entity_fields($node->getTranslation('ar'), 'ar')]
      : [],
  ],
  'paragraphs' => $paragraphs,
];

$path = \Drupal::service('extension.list.module')->getPath('motaded_custom') . '/data/homepage_export.php';
$export = var_export($dataset, TRUE);
$php = <<<PHP
<?php

declare(strict_types=1);

/**
 * @file
 * Homepage (node 374) export. Import: drush mhome-import
 *
 * Generated: {$dataset['exported_at']}
 * Source DB: {$dataset['source_database']}
 */

return {$export};

PHP;

file_put_contents($path, $php);
print "Exported homepage to {$path} (" . count($paragraphs) . " top-level paragraphs).\n";
