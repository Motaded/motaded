<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds noindex header for parameterized contact-us pages.
 */
final class ContactUsNoindexSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onResponse',
    ];
  }

  /**
   * Sets X-Robots-Tag for contact-us URLs with package/plan query params.
   */
  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $path = $request->getPathInfo();

    if (!preg_match('#^/(?:ar/|es/|fr/|zh-hans/)?contact-us/?$#i', $path)) {
      return;
    }

    if (!$request->query->has('package') && !$request->query->has('plan')) {
      return;
    }

    $event->getResponse()->headers->set('X-Robots-Tag', 'noindex, follow');
  }

}
