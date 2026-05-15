<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\motaded_setup_cost\SetupCostDataBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Business setup cost step estimator with optional Webform lead capture.
 *
 * @Block(
 *   id = "setup_cost_estimator",
 *   admin_label = @Translation("Setup cost estimator"),
 *   category = @Translation("Motaded"),
 * )
 */
final class SetupCostEstimatorBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly SetupCostDataBuilder $dataBuilder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('motaded_setup_cost.data_builder'),
    );
  }

  public function defaultConfiguration(): array {
    return [
      'profile_id' => '',
      'show_webform' => TRUE,
      'show_packages' => TRUE,
    ] + parent::defaultConfiguration();
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $options = ['' => $this->t('Active profile (settings)')];
    $storage = \Drupal::entityTypeManager()->getStorage('setup_cost_profile');
    foreach ($storage->loadMultiple() as $entity) {
      $options[$entity->id()] = $entity->label();
    }
    $form['profile_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Profile'),
      '#options' => $options,
      '#default_value' => $this->configuration['profile_id'],
    ];
    $form['show_webform'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show lead capture webform'),
      '#default_value' => $this->configuration['show_webform'],
    ];
    $form['show_packages'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show recommended package'),
      '#default_value' => $this->configuration['show_packages'],
    ];
    return $form;
  }

  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['profile_id'] = $form_state->getValue('profile_id');
    $this->configuration['show_webform'] = (bool) $form_state->getValue('show_webform');
    $this->configuration['show_packages'] = (bool) $form_state->getValue('show_packages');
  }

  public function build(): array {
    $profileId = $this->configuration['profile_id'] ?: NULL;
    $config = $this->dataBuilder->buildFrontendConfig($profileId);
    if ($config === NULL) {
      return [
        '#markup' => '<p>' . $this->t('No enabled setup cost profile. Configure one at @link.', [
          '@link' => '/admin/config/motaded/setup-cost',
        ]) . '</p>',
        '#cache' => ['max-age' => 0],
      ];
    }

    $webformId = (string) ($config['webform_id'] ?? '');
    $webform = NULL;
    if ($this->configuration['show_webform'] && $webformId !== '' && \Drupal::moduleHandler()->moduleExists('webform')) {
      $webform = [
        '#type' => 'webform',
        '#webform' => $webformId,
        '#lazy' => FALSE,
      ];
    }

    $build = [
      '#theme' => 'setup_cost_estimator',
      '#profile' => $config,
      '#show_packages' => (bool) $this->configuration['show_packages'],
      '#webform' => $webform,
      '#attached' => [
        'library' => ['motaded_setup_cost/estimator'],
        'drupalSettings' => [
          'motadedSetupCost' => $config,
        ],
      ],
      '#cache' => [
        'tags' => ['config:setup_cost_profile:' . $config['profile_id']],
        'contexts' => ['languages', 'user.roles'],
      ],
    ];

    return $build;
  }

}
