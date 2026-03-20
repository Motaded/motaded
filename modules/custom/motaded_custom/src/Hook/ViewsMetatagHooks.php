<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Fixes canonical language prefix on localized listing pages.
 *
 * Views page routes may emit canonical URLs in default language without the
 * active language URL prefix. We override canonical with current request path
 * so canonical always matches the localized route being rendered.
 *
 * Appends " | Page N" to title and description on paginated views (blog, news,
 * services) to avoid duplicate meta tags across pages.
 */
class ViewsMetatagHooks {

  use StringTranslationTrait;

  /**
   * View routes that support pagination suffix in meta tags.
   */
  private const PAGINATED_VIEW_ROUTES = [
    'view.news.page_1',   // blog
    'view.news.page_2',   // news
    'view.services.page_1',
  ];

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
   * Appends page number to title and description on paginated view pages.
   *
   * Ensures unique meta tags per page to avoid SEO duplicate content issues.
   *
   * @param array $metatags
   *   The metatags array.
   * @param array $context
   *   The context array.
   */
  #[Hook('metatags_alter')]
  public function appendPaginationSuffix(array &$metatags, array &$context): void {
    $route_name = (string) $this->routeMatch->getRouteName();
    if (!in_array($route_name, self::PAGINATED_VIEW_ROUTES, TRUE)) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    if ($request === NULL) {
      return;
    }

    $page = $request->query->getInt('page', 0);
    if ($page <= 0) {
      return;
    }

    $page_display = $page + 1;
    $suffix = ' | ' . $this->t('Page @number', ['@number' => $page_display]);

    if (isset($metatags['title']) && $metatags['title'] !== '') {
      $metatags['title'] .= $suffix;
    }
    if (isset($metatags['description']) && $metatags['description'] !== '') {
      $metatags['description'] .= $suffix;
    }
  }

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
    $query_string = $request->getQueryString();
    if ($query_string !== null && $query_string !== '') {
      $localized_canonical .= '?' . $query_string;
    }
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

      // Fix: set canonical to full current URL (path + query) so paginated pages
      // like /fr/blog?page=0 have self-consistent canonical and hreflang.
      if (!$has_language_prefix || $query_string !== null) {
        $item[0]['#attributes']['href'] = $localized_canonical;
      }
      break;
    }
  }

}
