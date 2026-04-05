<?php

namespace Drupal\motaded_custom\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;

/**
 * Visibility for core 404 handling.
 *
 * The block condition "response_status" does not match a direct visit to
 * /system/404 (and often not during the 404 subrequest) because Symfony still
 * reports 200 until the response is finalized. Route name is system.404 for
 * both direct access and DefaultExceptionHtmlSubscriber's subrequest.
 *
 * @Condition(
 *   id = "motaded_404_page",
 *   label = @Translation("404 page (system.404)"),
 * )
 */
class Motaded404Page extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    if (\Drupal::routeMatch()->getRouteName() === 'system.404') {
      return TRUE;
    }
    $request = \Drupal::requestStack()->getCurrentRequest();
    $code = $request->query->get('_exception_statuscode') ?? $request->request->get('_exception_statuscode');
    return (int) $code === 404;
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->isNegated()
      ? $this->t('Not the core 404 page')
      : $this->t('Core 404 page (route system.404 or exception status 404)');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'route.name';
    $contexts[] = 'url.query_args:_exception_statuscode';
    return $contexts;
  }

}
