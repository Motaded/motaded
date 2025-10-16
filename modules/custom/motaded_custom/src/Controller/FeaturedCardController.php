<?php

namespace Drupal\motaded_custom\Controller;

use Drupal\Core\Controller\ControllerBase;

class FeaturedCardController extends ControllerBase {

  public function featuredCardPage() {
    return [
      '#theme' => 'featured_card_page_template',
      '#attached' => [
        'library' => [
          'motaded_custom/featured_card_page',
        ],
      ],
    ];
  }
}
