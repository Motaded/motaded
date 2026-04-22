<?php

namespace Drupal\devel_debug_log\Twig;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\TwigEnvironment;
use Twig\Extension\AbstractExtension;
use Twig\Template;
use Twig\TwigFunction;

/**
 * Provides the Debug log debugging function within Twig templates.
 */
class DevelDebugLogExtension extends AbstractExtension {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'ddl';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('ddl', [$this, 'ddl'], [
        'needs_environment' => TRUE,
        'needs_context' => TRUE,
      ]),
    ];
  }

  /**
   * Provides ddl function to Twig templates.
   *
   * @param Drupal\Core\Template\TwigEnvironment $env
   *   The twig environment instance.
   * @param array $context
   *   An array of parameters passed to the template.
   */
  public function ddl(TwigEnvironment $env, array $context) {
    // Don't do anything unless twig_debug is enabled. This reads from the Twig.
    if (!$env->isDebug()) {
      return;
    }

    if (func_num_args() === 2) {
      // No arguments passed, display full Twig context.
      $ddl_variables = [];
      foreach ($context as $key => $value) {
        if (!$value instanceof Template) {
          $ddl_variables[$key] = $value;
        }
      }
      ddl($ddl_variables, $this->t('Context as array'));
    }
    else {
      $args = array_slice(func_get_args(), 2);
      if (isset($args[1])) {
        ddl($args[0], (string) $args[1]);
      }
      else {
        ddl($args[0]);
      }
    }
  }

}
