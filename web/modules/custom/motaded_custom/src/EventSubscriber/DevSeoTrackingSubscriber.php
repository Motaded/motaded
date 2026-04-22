<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Drupal\Core\Site\Settings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds X-Robots-Tag when dev/staging suppress mode is enabled in settings.php.
 */
final class DevSeoTrackingSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => ['onResponse', -512],
    ];
  }

  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    if (!Settings::get('motaded_suppress_seo_tracking', FALSE)) {
      return;
    }
    $response = $event->getResponse();
    if ($response->getStatusCode() >= 300) {
      return;
    }
    $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
  }

}
