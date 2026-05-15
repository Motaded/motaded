<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Lists setup cost profiles.
 */
final class SetupCostProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    return [
      'label' => $this->t('Label'),
      'id' => $this->t('Machine name'),
      'status' => $this->t('Status'),
      'currency' => $this->t('Currency'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\motaded_setup_cost\Entity\SetupCostProfileInterface $entity */
    $row['label'] = $entity->label();
    $row['id']['data'] = ['#plain_text' => $entity->id()];
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    $row['currency']['data'] = ['#plain_text' => $entity->getCurrency()];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render(): array {
    $build = parent::render();
    $settings_url = Url::fromRoute('motaded_setup_cost.settings');
  $active = \Drupal::config('motaded_setup_cost.settings')->get('active_profile') ?? 'default';
    $build['active'] = [
      '#type' => 'item',
      '#title' => $this->t('Active profile on the site'),
      '#markup' => '<code>' . htmlspecialchars($active, ENT_QUOTES, 'UTF-8') . '</code> — <a href="' . $settings_url->toString() . '">' . $this->t('Change') . '</a>',
      '#weight' => -20,
    ];
    return $build;
  }

}
