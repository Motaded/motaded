<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Crm;

use GuzzleHttp\Psr7\Uri;

/**
 * Adjusts CRM webhook URLs for DDEV (container → host machine).
 */
final class CrmLeadWebhookUrlResolver {

  private const DDEV_HOST_ALIASES = [
    '127.0.0.1',
    'localhost',
    '::1',
  ];

  /**
   * Rewrites loopback hosts to host.docker.internal inside DDEV.
   */
  public function resolve(string $url): string {
    if (getenv('IS_DDEV_PROJECT') !== 'true' || $url === '') {
      return $url;
    }

    try {
      $uri = new Uri($url);
    }
    catch (\InvalidArgumentException) {
      return $url;
    }

    $host = strtolower($uri->getHost());
    if (!in_array($host, self::DDEV_HOST_ALIASES, TRUE)) {
      return $url;
    }

    return (string) $uri->withHost('host.docker.internal');
  }

  public function wasAdjusted(string $original, string $resolved): bool {
    return $original !== $resolved;
  }

}
