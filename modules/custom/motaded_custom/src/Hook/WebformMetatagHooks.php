<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Fixes metatag language context for webform routes.
 *
 * The metatag module uses $entity->language() for token resolution langcode.
 * Webform is a ConfigEntityInterface whose language() always returns 'en',
 * causing tokens like [current-page:url] to ignore the URL language prefix.
 * Clearing the entity forces metatag to fall back to the current content
 * language from language negotiation.
 *
 * For contact-us with ?package=&plan=, appends human-readable suffix to title
 * and description (e.g. " | Advanced - Annual").
 */
class WebformMetatagHooks {

  /**
   * Human-readable labels for package and plan query values.
   */
  private const PACKAGE_LABELS = [
    'basic' => 'Basic',
    'advanced' => 'Advanced',
    'premium' => 'Premium',
  ];

  private const PLAN_LABELS = [
    'annual' => 'Annual',
    'monthly' => 'Monthly',
  ];

  /**
   * Constructs a WebformMetatagHooks object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    protected readonly RouteMatchInterface $routeMatch,
    protected readonly RequestStack $requestStack,
  ) {}

  /**
   * Clears the entity context on webform routes for correct language tokens.
   * Appends package/plan suffix to contact-us meta title and description.
   *
   * @param array $metatags
   *   The metatags array.
   * @param array $context
   *   The context array containing the entity reference.
   */
  #[Hook('metatags_alter')]
  public function fixWebformLanguageContext(array &$metatags, array &$context): void {
    if ($this->routeMatch->getRouteName() !== 'entity.webform.canonical') {
      return;
    }

    $context['entity'] = NULL;

    $webform = $this->routeMatch->getParameter('webform');
    $webform_id = $webform?->id() ?? $webform;
    if ($webform_id !== 'contact') {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    if ($request === NULL) {
      return;
    }

    $package = $request->query->get('package');
    $plan = $request->query->get('plan');
    if ($package === NULL && $plan === NULL) {
      return;
    }

    $parts = [];
    if ($package !== NULL && $package !== '') {
      $parts[] = self::PACKAGE_LABELS[strtolower($package)] ?? ucfirst($package);
    }
    if ($plan !== NULL && $plan !== '') {
      $parts[] = self::PLAN_LABELS[strtolower($plan)] ?? ucfirst($plan);
    }
    if ($parts === []) {
      return;
    }

    $suffix = ' | ' . implode(' - ', $parts);

    if (isset($metatags['title']) && $metatags['title'] !== '') {
      $metatags['title'] .= $suffix;
    }
    if (isset($metatags['description']) && $metatags['description'] !== '') {
      $metatags['description'] .= $suffix;
    }
  }

}
