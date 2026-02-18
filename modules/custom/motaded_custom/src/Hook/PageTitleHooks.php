<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Appends page number to the title tag on paginated pages.
 *
 * Prevents duplicate title tags across paginated views (blog, news, etc.)
 * by appending "| Page N" to the title starting from the second page.
 */
class PageTitleHooks {

  use StringTranslationTrait;

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
   * Only modifies the title when the page query parameter is > 0,
   * so the first page keeps its original title unchanged.
   *
   * @param array $metatag_attachments
   *   The metatag attachments array.
   */
  #[Hook('metatags_attachments_alter')]
  public function appendPageNumber(array &$metatag_attachments): void {
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

    foreach ($metatag_attachments['#attached']['html_head'] as &$item) {
      if (!empty($item[1]) && $item[1] === 'title') {
        if (!empty($item[0]['#attributes']['content'])) {
          $item[0]['#attributes']['content'] .= ' | ' . $this->t('Page @number', [
            '@number' => $page,
          ]);
        }
        break;
      }
    }
  }

}
