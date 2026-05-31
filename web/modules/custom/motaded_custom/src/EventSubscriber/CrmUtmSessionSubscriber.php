<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Drupal\motaded_custom\Crm\CrmLeadAttributionCollector;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Stores UTM query parameters in session for later lead submissions.
 */
final class CrmUtmSessionSubscriber implements EventSubscriberInterface {

  public function __construct(
    private readonly CrmLeadAttributionCollector $attributionCollector,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 30],
    ];
  }

  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }
    $this->attributionCollector->persistUtmFromRequest($event->getRequest());
  }

}
