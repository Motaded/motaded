<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects /node/{id} and /{lang}/node/{id} to their path aliases.
 *
 * Prevents duplicate content by consolidating node ID URLs to canonical aliases.
 */
final class NodeToAliasRedirectSubscriber implements EventSubscriberInterface {

  /**
   * Language prefixes from language.negotiation (path_prefix).
   */
  private const LANG_PREFIXES = ['ar', 'es', 'fr', 'zh-hans'];

  /**
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(
    private readonly AliasManagerInterface $aliasManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => ['onRequest', 35],
    ];
  }

  /**
   * Redirects node/ID to alias when alias exists.
   */
  public function onRequest(RequestEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $path = $request->getPathInfo();
    $path = '/' . trim($path, '/');

    $matches = [];
    if (preg_match('#^/([a-z-]+)/node/(\d+)$#', $path, $matches)) {
      [, $prefix, $nid] = $matches;
      if (in_array($prefix, self::LANG_PREFIXES, true)) {
        $this->redirectIfAliasExists($event, (int) $nid, $path, $prefix);
        return;
      }
    }
    if (preg_match('#^/node/(\d+)$#', $path, $matches)) {
      $this->redirectIfAliasExists($event, (int) $matches[1], $path, '');
    }
  }

  /**
   * Checks for alias and redirects when it differs from node/ID.
   */
  private function redirectIfAliasExists(RequestEvent $event, int $nid, string $currentPath, string $langPrefix): void {
    $node_storage = $this->entityTypeManager->getStorage('node');
    $node = $node_storage->load($nid);
    if (!$node instanceof NodeInterface || !$node->isPublished()) {
      return;
    }

    $langcode = $langPrefix === '' ? 'en' : $langPrefix;
    if (!$node->hasTranslation($langcode)) {
      return;
    }

    $internal_path = '/node/' . $nid;
    $alias = $this->aliasManager->getAliasByPath($internal_path, $langcode);

    if ($alias === $internal_path || $alias === '') {
      return;
    }

    $alias = '/' . trim($alias, '/');
    $targetPath = $langPrefix === '' ? $alias : '/' . $langPrefix . $alias;

    if ($targetPath === $currentPath) {
      return;
    }

    $request = $event->getRequest();
    $url = $request->getSchemeAndHttpHost() . $targetPath;
    if ($request->getQueryString()) {
      $url .= '?' . $request->getQueryString();
    }

    $event->setResponse(new RedirectResponse($url, 301));
  }

}
