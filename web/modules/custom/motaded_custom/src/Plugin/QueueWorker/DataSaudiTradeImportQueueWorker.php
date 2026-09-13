<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\RequeueException;
use Drupal\motaded_custom\DataSaudi\DataSaudiTradeImporter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Runs the DataSaudi trade snapshot import off the main cron request.
 *
 * @QueueWorker(
 *   id = "datasaudi_trade_import",
 *   title = @Translation("Import DataSaudi trade snapshots"),
 *   cron = {"time" = 180}
 * )
 */
final class DataSaudiTradeImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly DataSaudiTradeImporter $importer,
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
      $container->get('motaded_custom.datasaudi_trade_importer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    $outcome = $this->importer->importAll();
    if ($outcome->locked) {
      throw new RequeueException('DataSaudi trade import is already running.');
    }
  }

}
