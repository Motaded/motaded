<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Selects which profile powers the public estimator block.
 */
final class SetupCostSettingsForm extends ConfigFormBase {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
    );
  }

  protected function getEditableConfigNames(): array {
    return ['motaded_setup_cost.settings'];
  }

  public function getFormId(): string {
    return 'motaded_setup_cost_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('motaded_setup_cost.settings');
    $options = [];
    foreach ($this->entityTypeManager->getStorage('setup_cost_profile')->loadMultiple() as $entity) {
      $options[$entity->id()] = $entity->label();
    }

    $form['active_profile'] = [
      '#type' => 'select',
      '#title' => $this->t('Active profile'),
      '#options' => $options,
      '#default_value' => $config->get('active_profile') ?? 'default',
      '#required' => TRUE,
      '#description' => $this->t('Used by the Setup cost estimator block when no profile is selected in block settings.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('motaded_setup_cost.settings')
      ->set('active_profile', $form_state->getValue('active_profile'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
