<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\motaded_custom\MarketIndicators\MarketIndicatorsImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Runs World Bank market indicator import off the main cron request.
 *
 * @QueueWorker(
 *   id = "market_indicators_import",
 *   title = @Translation("Import market indicators (World Bank)"),
 *   cron = {"time" = 120}
 * )
 */
final class MarketIndicatorsImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly MarketIndicatorsImporter $importer,
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
      $container->get('motaded_custom.market_indicators_importer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    $this->importer->importAll();
  }

}
