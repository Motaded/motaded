<?php

namespace Drupal\node_view_redirect\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Path\PathValidator;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Routing\Router;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Event Subscriber for node view redirect.
 */
class DefaultSubscriber implements EventSubscriberInterface {

  /**
   * ConfigFactory var.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * CurrentRouteMatch var.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * LanguageManager var.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * AccountProxy var.
   *
   * @var \Drupal\Core\Session\AccountProxy
   */
  protected $currentUser;

  /**
   * Router var.
   *
   * @var \Drupal\Core\Routing\Router
   */
  protected $router;

  /**
   * UrlValidator var.
   *
   * @var \Drupal\Core\Path\PathValidator
   */
  protected $urlValidator;

  /**
   * Construct method.
   *
   * @inheritDoc
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              CurrentRouteMatch $route_match,
                              LanguageManager $language_manager,
                              AccountProxy $current_user,
                              Router $router,
                              PathValidator $url_validator
  ) {
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
    $this->languageManager = $language_manager;
    $this->currentUser = $current_user;
    $this->router = $router;
    $this->urlValidator = $url_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('language_manager'),
      $container->get('current_user'),
      $container->get('router.no_access_checks'),
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['nodeViewRedirect'];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  public function nodeViewRedirect(\Symfony\Component\HttpKernel\Event\RequestEvent $event) {
    $config = $this->configFactory->get('node_view_redirect.config');
    $node = $this->routeMatch->getParameter('node');
    if (!$this->isNodeRoute()) {
      return;
    }

    if (is_numeric($node)) {
      $node = Node::load($node);
    }

    if ($node instanceof NodeInterface) {
      $typeName = $node->bundle();
      $language = $this->languageManager->getCurrentLanguage();

      // If current user has a permissions about this content
      // type.
      $has_permission = FALSE;
      if ($config->get('nvr_no_exception.' . $typeName)) {
        $has_permission = FALSE;
      }
      else {
        if ($this->currentUser->hasPermission('create ' . $typeName . ' content') ||
          $this->currentUser->hasPermission('delete any ' . $typeName . ' content') ||
          $this->currentUser->hasPermission('delete own ' . $typeName . ' content') ||
          $this->currentUser->hasPermission('delete ' . $typeName . ' revisions') ||
          $this->currentUser->hasPermission('edit any ' . $typeName . ' content') ||
          $this->currentUser->hasPermission('revert any ' . $typeName . ' revisions') ||
          $this->currentUser->hasPermission('view ' . $typeName . ' revisions') ||
          $this->currentUser->hasPermission('revert any ' . $typeName . ' revisions')
        ) {
          $has_permission = TRUE;
        }
      }

      if ($config->get('nvr_content_type.' . $typeName) &&
        !$has_permission) {
        $dir = $config->get('nvr_redirect.' . $typeName);
        if (!$this->urlValidator->isValid($dir)) {
          throw new NotFoundHttpException();
        }
        $result = $this->router->match($dir);
        $event->setResponse(new RedirectResponse(Url::fromRoute($result['_route'], $result['_raw_variables']->all(), ['language' => $language])
          ->toString()));
      }
    }
  }

  /**
   * Check if current route is a node route.
   *
   * @return bool
   *   TRUE if node entity route, FALSE otherwise.
   */
  protected function isNodeRoute() {
    return strpos($this->routeMatch->getRouteName(), 'entity.node.canonical') === 0;
  }

}
