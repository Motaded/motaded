<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Atomic snapshot replace for DataSaudi monthly trade tables.
 */
final class DataSaudiTradeRepository {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly Connection $connection,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * @param list<array<string, mixed>> $rows
   */
  public function replaceBalance(array $rows, string $fetchedAt): void {
    if ($rows === []) {
      throw new \InvalidArgumentException('Refusing to replace trade balance with an empty snapshot.');
    }
    $transaction = $this->connection->startTransaction();
    try {
      $this->connection->delete('datasaudi_trade_balance')->execute();
      foreach (array_chunk($rows, 200) as $chunk) {
        $insert = $this->connection->insert('datasaudi_trade_balance')->fields([
          'period',
          'period_id',
          'exports_million_sar',
          'imports_million_sar',
          'unit',
          'fetched_at',
        ]);
        foreach ($chunk as $row) {
          $insert->values([
            'period' => $row['period'],
            'period_id' => $row['period_id'],
            'exports_million_sar' => $row['exports'],
            'imports_million_sar' => $row['imports'],
            'unit' => $row['unit'],
            'fetched_at' => $fetchedAt,
          ]);
        }
        $insert->execute();
      }
      $this->upsertStatus('balance', $rows, $fetchedAt, 'period');
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      $this->logger->error('datasaudi_trade_balance replace failed: @msg', ['@msg' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * @param list<array<string, mixed>> $rows
   */
  public function replaceProduct(array $rows, string $fetchedAt): void {
    if ($rows === []) {
      throw new \InvalidArgumentException('Refusing to replace trade product with an empty snapshot.');
    }
    $transaction = $this->connection->startTransaction();
    try {
      $this->connection->delete('datasaudi_trade_product')->execute();
      foreach (array_chunk($rows, 200) as $chunk) {
        $insert = $this->connection->insert('datasaudi_trade_product')->fields([
          'flow_id',
          'flow_key',
          'section_id',
          'period',
          'period_id',
          'value_million_sar',
          'unit',
          'flow_label_en',
          'flow_label_ar',
          'section_label_en',
          'section_label_ar',
          'fetched_at',
        ]);
        foreach ($chunk as $row) {
          $insert->values([
            'flow_id' => $row['flow_id'],
            'flow_key' => $row['flow_key'],
            'section_id' => $row['section_id'],
            'period' => $row['period'],
            'period_id' => $row['period_id'],
            'value_million_sar' => $row['value'],
            'unit' => $row['unit'],
            'flow_label_en' => $row['flow_label_en'],
            'flow_label_ar' => $row['flow_label_ar'],
            'section_label_en' => $row['section_label_en'],
            'section_label_ar' => $row['section_label_ar'],
            'fetched_at' => $fetchedAt,
          ]);
        }
        $insert->execute();
      }
      $this->upsertStatus('product', $rows, $fetchedAt, 'period');
    }
    catch (\Throwable $e) {
      $transaction->rollBack();
      $this->logger->error('datasaudi_trade_product replace failed: @msg', ['@msg' => $e->getMessage()]);
      throw $e;
    }
  }

  /**
   * @return array<string, mixed>|null
   */
  public function loadStatus(string $dataset): ?array {
    $row = $this->connection->select('datasaudi_trade_import_status', 's')
      ->fields('s')
      ->condition('dataset', $dataset)
      ->execute()
      ->fetchAssoc();
    return is_array($row) ? $row : NULL;
  }

  public function countBalance(): int {
    return (int) $this->connection->select('datasaudi_trade_balance', 'b')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  public function countProduct(): int {
    return (int) $this->connection->select('datasaudi_trade_product', 'p')
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * @return array{period: string, exports_million_sar: string, imports_million_sar: string}|null
   */
  public function loadBalancePeriod(string $period): ?array {
    $row = $this->connection->select('datasaudi_trade_balance', 'b')
      ->fields('b', ['period', 'exports_million_sar', 'imports_million_sar'])
      ->condition('period', $period)
      ->execute()
      ->fetchAssoc();
    return is_array($row) ? $row : NULL;
  }

  /**
   * Months present for a calendar year. 12 means the year is complete.
   */
  public function countBalanceMonthsForYear(int $year): int {
    $prefix = sprintf('%04d-', $year);
    $query = $this->connection->select('datasaudi_trade_balance', 'b');
    $query->addExpression('COUNT(DISTINCT period)', 'c');
    $query->condition('period', $this->connection->escapeLike($prefix) . '%', 'LIKE');
    return (int) $query->execute()->fetchField();
  }

  /**
   * @return list<array{flow_id: int, flow_key: string, months: int, period_min: string, period_max: string}>
   */
  public function productFlowCoverage(): array {
    $query = $this->connection->select('datasaudi_trade_product', 'p');
    $query->addField('p', 'flow_id');
    $query->addField('p', 'flow_key');
    $query->addExpression('COUNT(DISTINCT period)', 'months');
    $query->addExpression('MIN(period)', 'period_min');
    $query->addExpression('MAX(period)', 'period_max');
    $query->groupBy('flow_id');
    $query->groupBy('flow_key');
    $query->orderBy('flow_id');
    $out = [];
    foreach ($query->execute()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
      $out[] = [
        'flow_id' => (int) $row['flow_id'],
        'flow_key' => (string) $row['flow_key'],
        'months' => (int) $row['months'],
        'period_min' => (string) $row['period_min'],
        'period_max' => (string) $row['period_max'],
      ];
    }
    return $out;
  }

  /**
   * Latest monthly balance row (not an annual total).
   *
   * @return array<string, mixed>|null
   */
  public function loadLatestBalance(): ?array {
    $row = $this->connection->select('datasaudi_trade_balance', 'b')
      ->fields('b')
      ->orderBy('period', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();
    return is_array($row) ? $row : NULL;
  }

  /**
   * Most recent existing months, oldest first. Missing months are omitted.
   *
   * @return list<array<string, mixed>>
   */
  public function loadLatestAvailableBalanceMonths(int $limit = 24): array {
    $limit = max(1, $limit);
    $query = $this->connection->select('datasaudi_trade_balance', 'b')
      ->fields('b')
      ->orderBy('period', 'DESC')
      ->range(0, $limit);
    $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if ($rows === []) {
      return [];
    }
    return array_reverse($rows);
  }

  public function loadLatestProductPeriod(string $flowKey): ?string {
    $period = $this->connection->select('datasaudi_trade_product', 'p')
      ->fields('p', ['period'])
      ->condition('flow_key', $flowKey)
      ->orderBy('period', 'DESC')
      ->range(0, 1)
      ->execute()
      ->fetchField();
    return $period !== FALSE && $period !== NULL && $period !== '' ? (string) $period : NULL;
  }

  /**
   * All stored sections for one flow and month. Absent rows are not invented.
   *
   * @return list<array<string, mixed>>
   */
  public function loadProductRowsForPeriod(string $flowKey, string $period): array {
    $query = $this->connection->select('datasaudi_trade_product', 'p')
      ->fields('p')
      ->condition('flow_key', $flowKey)
      ->condition('period', $period)
      ->orderBy('value_million_sar', 'DESC')
      ->orderBy('section_id', 'ASC');
    $out = [];
    foreach ($query->execute()->fetchAll(\PDO::FETCH_ASSOC) as $row) {
      $out[] = $row;
    }
    return $out;
  }

  /**
   * @param list<array<string, mixed>> $rows
   */
  private function upsertStatus(string $dataset, array $rows, string $fetchedAt, string $periodKey): void {
    $periods = [];
    foreach ($rows as $row) {
      $periods[] = (string) $row[$periodKey];
    }
    sort($periods);
    $this->connection->merge('datasaudi_trade_import_status')
      ->keys(['dataset' => $dataset])
      ->fields([
        'fetched_at' => $fetchedAt,
        'period_min' => $periods[0],
        'period_max' => $periods[array_key_last($periods)],
        'row_count' => count($rows),
        'unit' => DataSaudiTesseractClient::UNIT_MILLION_SAR,
      ])
      ->execute();
  }

}
