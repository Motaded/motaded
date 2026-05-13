<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\MarketIndicators;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Persists normalized market indicators (custom tables).
 */
final class MarketIndicatorRepository {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly Connection $connection,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * @param array{
   *   indicator_key: string,
   *   label: string,
   *   value_raw: ?float,
   *   value_display: ?string,
   *   year: int,
   *   period: ?string,
   *   period_type: string,
   *   source_name: string,
   *   source_url: string,
   *   provider: string,
   *   fetched_at: string,
   * } $row
   */
  public function upsertIndicator(array $row): void {
    try {
      $this->connection->merge('market_indicators')
        ->keys([
          'indicator_key' => $row['indicator_key'],
          'year' => $row['year'],
        ])
        ->fields([
          'label' => $row['label'],
          'value_raw' => $row['value_raw'],
          'value_display' => $row['value_display'],
          'period' => $row['period'],
          'period_type' => $row['period_type'],
          'source_name' => $row['source_name'],
          'source_url' => $row['source_url'],
          'provider' => $row['provider'],
          'fetched_at' => $row['fetched_at'],
        ])
        ->execute();
    }
    catch (\Throwable $e) {
      $this->logger->error('market_indicators upsert failed for @k/@y: @msg', [
        '@k' => $row['indicator_key'],
        '@y' => (string) $row['year'],
        '@msg' => $e->getMessage(),
      ]);
      throw $e;
    }
  }

  /**
   * @param list<array{year: int, value: float}> $points
   */
  public function replaceSeriesWindow(string $indicator_key, int $yearMin, int $yearMax, array $points, string $source_name): void {
    try {
      $outside = $this->connection->condition('OR');
      $outside->condition('year', $yearMin, '<');
      $outside->condition('year', $yearMax, '>');
      $this->connection->delete('market_indicator_series')
        ->condition('indicator_key', $indicator_key)
        ->condition($outside)
        ->execute();

      foreach ($points as $pt) {
        $year = (int) $pt['year'];
        if ($year < $yearMin || $year > $yearMax) {
          continue;
        }
        $this->connection->merge('market_indicator_series')
          ->keys([
            'indicator_key' => $indicator_key,
            'year' => $year,
          ])
          ->fields([
            'value' => $pt['value'],
            'source_name' => $source_name,
          ])
          ->execute();
      }
    }
    catch (\Throwable $e) {
      $this->logger->error('market_indicator_series replace failed for @k: @msg', [
        '@k' => $indicator_key,
        '@msg' => $e->getMessage(),
      ]);
      throw $e;
    }
  }

  /**
   * Removes older years for an indicator so the table holds one row per key (latest).
   */
  public function pruneIndicatorToYear(string $indicator_key, int $keep_year): void {
    $this->connection->delete('market_indicators')
      ->condition('indicator_key', $indicator_key)
      ->condition('year', $keep_year, '<>')
      ->execute();
  }

  /**
   * @param list<string> $order
   *
   * @return list<array<string, mixed>>
   */
  public function loadLatestRowsOrdered(array $order): array {
    if ($order === []) {
      return [];
    }
    $q = $this->connection->select('market_indicators', 'm');
    $q->fields('m');
    $q->condition('m.indicator_key', $order, 'IN');
    $result = $q->execute()->fetchAllAssoc('indicator_key', \PDO::FETCH_ASSOC);
    $rows = [];
    foreach ($order as $key) {
      if (isset($result[$key]) && is_array($result[$key])) {
        $rows[] = $result[$key];
      }
    }
    return $rows;
  }

  /**
   * @return list<array{year: int, value: float}>
   */
  public function loadSeries(string $indicator_key, int $yearMin, int $yearMax): array {
    $q = $this->connection->select('market_indicator_series', 's');
    $q->fields('s', ['year', 'value']);
    $q->condition('indicator_key', $indicator_key);
    $q->condition('year', $yearMin, '>=');
    $q->condition('year', $yearMax, '<=');
    $q->orderBy('year', 'ASC');
    $out = [];
    foreach ($q->execute()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
      $out[] = [
        'year' => (int) $row['year'],
        'value' => (float) $row['value'],
      ];
    }
    return $out;
  }

}
