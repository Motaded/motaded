<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;

class TextFormatConfigHooks {

  /**
   * Alters CKEditor 5 plugin definitions.
   */
  #[Hook('ckeditor5_plugin_info_alter')]
  public function ckeditor5PluginInfoAlter(array &$plugin_definitions): void {
    if (isset($plugin_definitions['ckeditor5_imageUpload'])) {
      $image_plugin_definition = $plugin_definitions['ckeditor5_imageUpload']->toArray();

      $image_plugin_definition['ckeditor5']['config']['image']['upload']['types'][] = 'webp';

      $plugin_definitions['ckeditor5_imageUpload'] =
        new CKEditor5PluginDefinition($image_plugin_definition);
    }
  }
}
