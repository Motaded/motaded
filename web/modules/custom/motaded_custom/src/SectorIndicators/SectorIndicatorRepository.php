<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorIndicators;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Persists sector indicator time series ({sector_indicator_points}).
 */
final class SectorIndicatorRepository {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly Connection $connection,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * @param array{
   *   sector_key: string,
   *   indicator_key: string,
   *   wb_code: string,
   *   year: int,
   *   value_raw: ?float,
   *   value_display: ?string,
   *   source_name: string,
   *   source_url: string,
   *   provider: string,
   *   fetched_at: string,
   * } $row
   */
  public function upsertPoint(array $row): void {
    try {
      $this->connection->merge('sector_indicator_points')
        ->keys([
          'sector_key' => $row['sector_key'],
          'indicator_key' => $row['indicator_key'],
          'year' => $row['year'],
        ])
        ->fields([
          'wb_code' => $row['wb_code'],
          'value_raw' => $row['value_raw'],
          'value_display' => $row['value_display'],
          'source_name' => $row['source_name'],
          'source_url' => $row['source_url'],
          'provider' => $row['provider'],
          'fetched_at' => $row['fetched_at'],
        ])
        ->execute();
    }
    catch (\Throwable $e) {
      $this->logger->error('sector_indicator_points upsert failed for @s/@i/@y: @msg', [
        '@s' => $row['sector_key'],
        '@i' => $row['indicator_key'],
        '@y' => (string) $row['year'],
        '@msg' => $e->getMessage(),
      ]);
      throw $e;
    }
  }

  /**
   * @return array<string, mixed>|null
   *   Latest row by year, or NULL.
   */
  public function loadLatest(string $sectorKey, string $indicatorKey): ?array {
    $id = $this->connection->select('sector_indicator_points', 's')
      ->fields('s')
      ->condition('sector_key', $sectorKey)
      ->condition('indicator_key', $indicatorKey)
      ->orderBy('year', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();
    return $id ?: NULL;
  }

  /**
   * @return list<array{year: int, value: float}>
   */
  public function loadSeries(string $sectorKey, string $indicatorKey, int $yearMin, int $yearMax): array {
    $q = $this->connection->select('sector_indicator_points', 's')
      ->fields('s', ['year', 'value_raw'])
      ->condition('sector_key', $sectorKey)
      ->condition('indicator_key', $indicatorKey)
      ->condition('year', [$yearMin, $yearMax], 'BETWEEN')
      ->orderBy('year', 'ASC');
    $out = [];
    foreach ($q->execute() as $row) {
      if ($row->value_raw === NULL) {
        continue;
      }
      $out[] = [
        'year' => (int) $row->year,
        'value' => (float) $row->value_raw,
      ];
    }
    return $out;
  }

}
