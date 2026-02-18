<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Fixes metatag language context for webform routes.
 *
 * The metatag module uses $entity->language() for token resolution langcode.
 * Webform is a ConfigEntityInterface whose language() always returns 'en',
 * causing tokens like [current-page:url] to ignore the URL language prefix.
 * Clearing the entity forces metatag to fall back to the current content
 * language from language negotiation.
 */
class WebformMetatagHooks {

  /**
   * Constructs a WebformMetatagHooks object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    protected readonly RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Clears the entity context on webform routes for correct language tokens.
   *
   * @param array $metatags
   *   The metatags array.
   * @param array $context
   *   The context array containing the entity reference.
   */
  #[Hook('metatags_alter')]
  public function fixWebformLanguageContext(array &$metatags, array &$context): void {
    if ($this->routeMatch->getRouteName() === 'entity.webform.canonical') {
      $context['entity'] = NULL;
    }
  }

}
