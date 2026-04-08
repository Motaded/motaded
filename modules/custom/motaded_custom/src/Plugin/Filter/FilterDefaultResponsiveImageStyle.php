<?php

namespace Drupal\motaded_custom\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Adds a default responsive image style to inline file images.
 *
 * @Filter(
 *   id = "motaded_default_responsive_image_style",
 *   title = @Translation("Default responsive image style for inline images"),
 *   description = @Translation("Adds data-responsive-image-style to inline <img> tags (data-entity-type=file) when missing, so inline responsive images can render srcset automatically."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
final class FilterDefaultResponsiveImageStyle extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Only touch markup that looks relevant.
    if (stripos($text, 'data-entity-type="file"') === FALSE) {
      return new FilterProcessResult($text);
    }

    $default_style = $this->settings['default_style'] ?? 'news_full';
    if (!is_string($default_style) || $default_style === '') {
      return new FilterProcessResult($text);
    }

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//*[@data-entity-type="file" and @data-entity-uuid and not(@data-responsive-image-style)]') as $node) {
      // Only apply to images that have a file source (avoid accidental matches).
      if (strtolower($node->nodeName) !== 'img') {
        continue;
      }
      $node->setAttribute('data-responsive-image-style', $default_style);
    }

    return new FilterProcessResult(Html::serialize($dom));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'default_style' => 'news_full',
    ] + parent::defaultConfiguration();
  }

}

