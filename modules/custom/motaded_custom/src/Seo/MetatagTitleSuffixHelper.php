<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Seo;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Provides helpers for enforcing site name suffix in metatag titles.
 */
class MetatagTitleSuffixHelper {

  /**
   * Constructs a MetatagTitleSuffixHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Ensures title contains a site name suffix.
   *
   * @param string $title
   *   The existing metatag title.
   *
   * @return string
   *   Title with suffix appended when missing.
   */
  public function appendSiteNameSuffix(string $title): string {
    $title = trim($title);
    if ($title === '' || $this->hasSiteNameSuffix($title)) {
      return $title;
    }

    return $title . ' | [site:name]';
  }

  /**
   * Checks if title already has a site name suffix/token.
   *
   * @param string $title
   *   The title to inspect.
   *
   * @return bool
   *   TRUE when suffix is already present.
   */
  public function hasSiteNameSuffix(string $title): bool {
    $title = trim($title);
    if ($title === '') {
      return FALSE;
    }

    if (preg_match('/\|\s*\[site:name\]\s*$/iu', $title) === 1) {
      return TRUE;
    }

    $site_name = trim((string) $this->configFactory->get('system.site')->get('name'));
    if ($site_name === '') {
      return FALSE;
    }

    return preg_match('/\|\s*' . preg_quote($site_name, '/') . '\s*$/iu', $title) === 1;
  }

}

