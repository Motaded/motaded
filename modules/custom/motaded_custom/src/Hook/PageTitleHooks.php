<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Appends page number to the metatag title on paginated pages.
 *
 * For view routes (blog, news, services), pagination is handled by
 * ViewsMetatagHooks::appendPaginationSuffix via metatags_alter.
 * This hook handles other paginated routes (e.g. search, taxonomy).
 */
class PageTitleHooks {

  use StringTranslationTrait;

  /**
   * View routes handled by ViewsMetatagHooks - skip to avoid double suffix.
   */
  private const VIEW_PAGINATION_ROUTES = [
    'view.news.page_1',
    'view.news.page_2',
    'view.services.page_1',
  ];

  /**
   * Constructs a PageTitleHooks object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack service.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
  ) {}

  /**
   * Appends page number to the metatag title on paginated pages.
   *
   * Skips view routes (handled by ViewsMetatagHooks).
   *
   * @param array $metatag_attachments
   *   The metatag attachments array.
   */
  #[Hook('metatags_attachments_alter')]
  public function appendPageNumber(array &$metatag_attachments): void {
    if (in_array(\Drupal::routeMatch()->getRouteName(), self::VIEW_PAGINATION_ROUTES, TRUE)) {
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

    if (empty($metatag_attachments['#attached']['html_head'])) {
      return;
    }

    $page_display = $page + 1;
    $suffix = ' | ' . $this->t('Page @number', ['@number' => $page_display]);

    foreach ($metatag_attachments['#attached']['html_head'] as &$item) {
      if (!empty($item[1]) && $item[1] === 'title') {
        if (!empty($item[0]['#attributes']['content'])) {
          $item[0]['#attributes']['content'] .= $suffix;
        }
        break;
      }
    }
  }

}
