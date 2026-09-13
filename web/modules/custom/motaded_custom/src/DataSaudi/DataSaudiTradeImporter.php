<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Fetches DataSaudi trade cubes and replaces local monthly snapshots.
 */
final class DataSaudiTradeImporter {

  public const CACHE_TAG = 'datasaudi_trade';

  public const LOCK_NAME = 'motaded_custom_datasaudi_trade_import';

  private const LOCK_TIMEOUT = 180.0;

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly DataSaudiTesseractClient $client,
    private readonly DataSaudiTradeRepository $repository,
    private readonly LockBackendInterface $lock,
    private readonly TimeInterface $time,
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  public function importAll(): DataSaudiImportOutcome {
    if (!$this->lock->acquire(self::LOCK_NAME, self::LOCK_TIMEOUT)) {
      $this->logger->warning('DataSaudi trade import skipped: lock is held.');
      return DataSaudiImportOutcome::locked();
    }
    try {
      $fetchedAt = gmdate('Y-m-d H:i:s', $this->time->getRequestTime());
      $balanceReplaced = $this->importBalance($fetchedAt);
      $productReplaced = $this->importProduct($fetchedAt);
      if ($balanceReplaced || $productReplaced) {
        $this->cacheTagsInvalidator->invalidateTags([self::CACHE_TAG]);
      }
      return new DataSaudiImportOutcome(
        locked: FALSE,
        balanceReplaced: $balanceReplaced,
        productReplaced: $productReplaced,
      );
    }
    finally {
      $this->lock->release(self::LOCK_NAME);
    }
  }

  private function importBalance(string $fetchedAt): bool {
    $result = $this->client->fetchTradeBalance();
    if (!$result->isUsable()) {
      $this->logger->warning('DataSaudi trade balance fetch not usable (@status): @msg; keeping previous snapshot.', [
        '@status' => $result->status,
        '@msg' => $result->message,
      ]);
      return FALSE;
    }
    $this->repository->replaceBalance($result->rows, $fetchedAt);
    $this->logger->info('DataSaudi trade balance snapshot replaced (@count monthly rows).', [
      '@count' => (string) count($result->rows),
    ]);
    return TRUE;
  }

  private function importProduct(string $fetchedAt): bool {
    $result = $this->client->fetchTradeProduct();
    if (!$result->isUsable()) {
      $this->logger->warning('DataSaudi trade product fetch not usable (@status): @msg; keeping previous snapshot.', [
        '@status' => $result->status,
        '@msg' => $result->message,
      ]);
      return FALSE;
    }
    $this->repository->replaceProduct($result->rows, $fetchedAt);
    $this->logger->info('DataSaudi trade product snapshot replaced (@count monthly section rows).', [
      '@count' => (string) count($result->rows),
    ]);
    return TRUE;
  }

}
