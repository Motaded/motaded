<?php
namespace Drupal\motaded_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Drupal\Core\File\FileUrlGenerator;

/**
 * Provides a 'BannerBlock' Block.
 *
 * @Block(
 *   id = "banner_block",
 *   admin_label = @Translation("Banner Block")
 * )
 */
class BannerBlock extends BlockBase {

    public function build() {
        // Get current route match service.
        $route_match = \Drupal::service('current_route_match');
        
        // Get current node.
        $node = $route_match->getParameter('node');

        $output = [];
        if ($node && $node->hasField('field_media')) {
            // Get the current language.
            $current_langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
            $media = NULL;

            // Check if the node has a translation in the current language.
            if ($node->hasTranslation($current_langcode)) {
                // Load the media field value in the current language.
                $media = $node->getTranslation($current_langcode)->get('field_media')->entity;
            } else {
                // Fallback to the default language or handle as needed.
                $default_langcode = $node->getUntranslated()->language()->getId();
                $media = $node->getTranslation($default_langcode)->get('field_media')->entity;
            }

            // Check if the media entity is available.
            if ($media) {
                // Assuming 'field_media_image' is the field that holds the image file.
                $media_image = $media->get('field_media_image')->entity;

                // Check if the media image file is available.
                if ($media_image instanceof File) {
                    // Get the file URI.
                    $file_uri = $media_image->getFileUri();

                    // Use the FileUrlGenerator service to create a URL.
                    $file_url_generator = \Drupal::service('file_url_generator');
                    $output['media_url'] = $file_url_generator->generateAbsoluteString($file_uri);
                }
            }

            // Prepare data for the twig template.
            $output['title'] = $node->getTitle();
        }

        return [
            '#theme' => 'banner_block',
            '#content' => $output,
            '#cache' => [
                'contexts' => ['route', 'languages'],
            ],
        ];
    }
}
