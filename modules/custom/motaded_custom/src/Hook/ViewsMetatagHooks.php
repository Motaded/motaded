<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Fixes canonical language prefix on localized listing pages.
 *
 * Views page routes may emit canonical URLs in default language without the
 * active language URL prefix. We override canonical with current request path
 * so canonical always matches the localized route being rendered.
 */
class ViewsMetatagHooks {

  /**
   * Constructs a ViewsMetatagHooks object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Rewrites canonical in final head attachments for Views page routes.
   *
   * @param array $metatag_attachments
   *   The metatag attachments array.
   */
  #[Hook('metatags_attachments_alter')]
  public function fixViewsCanonicalAttachment(array &$metatag_attachments): void {
    $route_name = (string) $this->routeMatch->getRouteName();
    if (!str_starts_with($route_name, 'view.')) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    if ($request === NULL) {
      return;
    }

    if (empty($metatag_attachments['#attached']['html_head'])) {
      return;
    }

    $path = $request->getPathInfo();
    $parts = explode('/', trim($path, '/'));
    $prefix = $parts[0] ?? '';
    if ($prefix === '' || $this->languageManager->getLanguage($prefix) === NULL) {
      return;
    }

    $localized_canonical = $request->getSchemeAndHttpHost() . $path;
    foreach ($metatag_attachments['#attached']['html_head'] as &$item) {
      if (
        !isset($item[0]['#tag'], $item[0]['#attributes']['rel'])
        || $item[0]['#tag'] !== 'link'
        || $item[0]['#attributes']['rel'] !== 'canonical'
      ) {
        continue;
      }

      $current_href = (string) ($item[0]['#attributes']['href'] ?? '');
      $current_path = (string) parse_url($current_href, PHP_URL_PATH);
      $has_language_prefix = $current_path === '/' . $prefix || str_starts_with($current_path, '/' . $prefix . '/');

      if (!$has_language_prefix) {
        $item[0]['#attributes']['href'] = $localized_canonical;
      }
      break;
    }
  }

}
