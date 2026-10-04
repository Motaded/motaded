<?php

/**
 * @file
 * Moves service landing aliases under /services and rewrites stored links.
 *
 * Usage:
 *   ddev drush php:script web/modules/custom/motaded_custom/scripts/update_service_landing_aliases.php
 */

declare(strict_types=1);

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\redirect\Entity\Redirect;

$slugs = [
  'customs-clearance-saudi-arabia',
  'accounting-services-saudi-arabia',
  'medicine-services-saudi-arabia',
  'medical-device-services-saudi-arabia',
  'cosmetics-services-saudi-arabia',
];

function _motaded_service_landing_rewrite(string $text, bool $arabic = FALSE): string {
  $text = (string) preg_replace(
    '#(?<!/services)/(customs-clearance-saudi-arabia|accounting-services-saudi-arabia|medicine-services-saudi-arabia|medical-device-services-saudi-arabia|cosmetics-services-saudi-arabia)#',
    '/services/$1',
    $text
  );
  if ($arabic) {
    $text = (string) preg_replace(
      '#href=(["\'])/services/(customs-clearance-saudi-arabia|accounting-services-saudi-arabia|medicine-services-saudi-arabia|medical-device-services-saudi-arabia|cosmetics-services-saudi-arabia)\1#',
      'href=$1/ar/services/$2$1',
      $text
    );
  }
  return $text;
}

function _motaded_service_landing_rewrite_items(array $values, bool $arabic = FALSE): array {
  foreach ($values as &$item) {
    foreach ($item as $key => $value) {
      if (is_string($value)) {
        $item[$key] = _motaded_service_landing_rewrite($value, $arabic);
      }
    }
  }
  return $values;
}

$alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
$updated_aliases = 0;
foreach ($slugs as $slug) {
  $old = '/' . $slug;
  $new = '/services/' . $slug;
  foreach (['en', 'ar'] as $langcode) {
    $found = $alias_storage->loadByProperties([
      'alias' => $old,
      'langcode' => $langcode,
    ]);
    foreach ($found as $alias) {
      if (!$alias instanceof PathAlias) {
        continue;
      }
      $path = $alias->getPath();
      $alias->setAlias($new);
      $alias->save();
      $updated_aliases++;

      if (\Drupal::moduleHandler()->moduleExists('redirect')) {
        $existing = \Drupal::entityTypeManager()->getStorage('redirect')->loadByProperties([
          'redirect_source.path' => ltrim($old, '/'),
          'language' => $langcode,
        ]);
        if (!$existing) {
          $redirect = Redirect::create();
          $redirect->setSource(ltrim($old, '/'));
          $redirect->setRedirect($path);
          $redirect->setLanguage($langcode);
          $redirect->setStatusCode(301);
          $redirect->save();
        }
      }
    }
  }
}

$rewritten = 0;
$paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraph');
$ids = $paragraph_storage->getQuery()->accessCheck(FALSE)->execute();
foreach (array_chunk($ids, 200) as $chunk) {
  foreach ($paragraph_storage->loadMultiple($chunk) as $paragraph) {
    if (!$paragraph instanceof ParagraphInterface) {
      continue;
    }
    foreach ($paragraph->getTranslationLanguages() as $language) {
      $translation = $paragraph->getTranslation($language->getId());
      $changed = FALSE;
      foreach ($translation->getFields() as $field) {
        if (!$field instanceof FieldItemListInterface) {
          continue;
        }
        $type = $field->getFieldDefinition()->getType();
        if (!in_array($type, ['text_long', 'text_with_summary', 'string_long', 'string', 'link'], TRUE)) {
          continue;
        }
        $before = $field->getValue();
        $after = _motaded_service_landing_rewrite_items($before, $language->getId() === 'ar');
        if ($after !== $before) {
          $field->setValue($after);
          $changed = TRUE;
        }
      }
      if ($changed) {
        $translation->save();
        $rewritten++;
      }
    }
  }
}

$block = \Drupal::configFactory()->getEditable('block.block.motaded_theme_ctaprefooter');
$pages = (string) $block->get('visibility.request_path.pages');
$new_pages = _motaded_service_landing_rewrite($pages);
if ($new_pages !== $pages) {
  $block->set('visibility.request_path.pages', $new_pages)->save();
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['rendered']);
echo "Updated aliases: {$updated_aliases}. Rewrote paragraph translations: {$rewritten}.\n";
