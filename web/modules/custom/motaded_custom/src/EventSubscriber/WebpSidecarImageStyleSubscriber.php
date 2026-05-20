<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Drupal\motaded_custom\Image\WebpSidecarGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Generates missing WebP sidecars before image style derivatives are built.
 */
final class WebpSidecarImageStyleSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly WebpSidecarGenerator $webpSidecar,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 30],
    ];
  }

  /**
   * Ensures sidecar exists for public/private image style download requests.
   */
  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $route = $request->attributes->get('_route');
    if (!in_array($route, ['image.style_public', 'image.style_private'], TRUE)) {
      return;
    }

    $target = $request->query->get('file');
    if (!is_string($target) || $target === '') {
      return;
    }

    $scheme = $route === 'image.style_private' ? 'private' : 'public';
    $uri = $scheme . '://' . $target;
    $this->webpSidecar->ensureForImageStyleRequest($uri);
  }

}
