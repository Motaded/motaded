<?php

declare(strict_types=1);

namespace Drupal\motaded_sector_data;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Lists sector data source config entities.
 */
final class SectorDataSourceListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'label' => $this->t('Label'),
      'id' => $this->t('Machine name'),
      'sector_key' => $this->t('Sector key'),
      'usage_key' => $this->t('Usage key'),
      'provider' => $this->t('API provider'),
      'status' => $this->t('Enabled'),
    ] + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\motaded_sector_data\Entity\SectorDataSourceInterface $entity */
    return [
      'label' => [
        'data' => $entity->toLink($entity->label(), 'edit-form')->toRenderable(),
      ],
      'id' => $entity->id(),
      'sector_key' => $entity->getSectorKey(),
      'usage_key' => $entity->getUsageKey(),
      'provider' => $entity->getProvider(),
      'status' => $entity->status() ? $this->t('Yes') : $this->t('No'),
    ] + parent::buildRow($entity);
  }

}
