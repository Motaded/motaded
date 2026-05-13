<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\motaded_custom\SectorIndicators\SectorIndicatorsImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Runs sector indicator import (World Bank + GASTAT) off the main cron request.
 *
 * @QueueWorker(
 *   id = "sector_indicators_import",
 *   title = @Translation("Import sector indicators (World Bank + GASTAT)"),
 *   cron = {"time" = 120}
 * )
 */
final class SectorIndicatorsImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly SectorIndicatorsImporter $importer,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('motaded_custom.sector_indicators_importer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    $this->importer->importAll();
  }

}
