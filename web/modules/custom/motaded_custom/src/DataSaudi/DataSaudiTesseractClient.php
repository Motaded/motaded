<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;

/**
 * Narrow Tesseract client for gastat_trade_balance.
 *
 * Offset paging is not used: live API ignores offset (page.offset stays 0).
 * Completeness is count(data) === page.total. Year cuts are the fallback.
 */
final class DataSaudiTesseractClient {

  public const UNIT_MILLION_SAR = 'million_sar';

  private const BASE = 'https://api.datasaudi.sa/tesseract';

  private const TIMEOUT = 45.0;

  private const CONNECT_TIMEOUT = 15.0;

  private const MAX_ATTEMPTS = 3;

  private const MAX_YEAR_SLICES = 16;

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly string $baseUrl = self::BASE,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * Monthly total exports + imports. Values are Million SAR (cube captions).
   *
   * @return DataSaudiFetchResult
   *   Rows: period (YYYY-MM), period_id, exports, imports.
   */
  public function fetchTradeBalance(): DataSaudiFetchResult {
    $query = [
      'cube' => 'gastat_trade_balance',
      'drilldowns' => 'Month',
      'measures' => 'Exports,Imports',
      'locale' => 'en',
    ];
    $fetched = $this->fetchCompleteRecords($query, 2017);
    if (!$fetched->isUsable()) {
      return $fetched;
    }
    $rows = [];
    foreach ($fetched->rows as $raw) {
      $period = $this->parsePeriod($raw['Month'] ?? NULL, $raw['Month ID'] ?? NULL);
      if ($period === NULL) {
        return DataSaudiFetchResult::error('Trade balance row missing a valid Month / Month ID.');
      }
      if (!$this->isFiniteNumber($raw['Exports'] ?? NULL) || !$this->isFiniteNumber($raw['Imports'] ?? NULL)) {
        return DataSaudiFetchResult::error('Trade balance row missing numeric Exports/Imports for ' . $period['period']);
      }
      $rows[$period['period']] = [
        'period' => $period['period'],
        'period_id' => $period['period_id'],
        'exports' => (float) $raw['Exports'],
        'imports' => (float) $raw['Imports'],
        'unit' => self::UNIT_MILLION_SAR,
      ];
    }
    ksort($rows);
    return DataSaudiFetchResult::ok(array_values($rows), count($rows));
  }

  /**
   * @param array<string, scalar> $query
   */
  private function fetchCompleteRecords(array $query, int $yearFrom): DataSaudiFetchResult {
    $first = $this->requestJson('/data.jsonrecords', $query);
    if ($first['status'] !== 'ok') {
      return DataSaudiFetchResult::error($first['message']);
    }
    $parsed = $this->extractDataPage($first['json']);
    if ($parsed['error'] !== '') {
      return DataSaudiFetchResult::error($parsed['error']);
    }
    if ($parsed['rows'] !== [] && count($parsed['rows']) === $parsed['total']) {
      return DataSaudiFetchResult::ok($parsed['rows'], $parsed['total']);
    }
    if ($parsed['rows'] === [] && $parsed['total'] === 0) {
      return DataSaudiFetchResult::empty('DataSaudi returned an empty page.', 0);
    }

    $this->logger->notice('DataSaudi first page incomplete (@got/@total); retrying by Year.', [
      '@got' => (string) count($parsed['rows']),
      '@total' => (string) $parsed['total'],
    ]);

    $merged = [];
    $yearTo = (int) gmdate('Y') + 1;
    $slices = 0;
    for ($year = $yearFrom; $year <= $yearTo; $year++) {
      if ($slices >= self::MAX_YEAR_SLICES) {
        return DataSaudiFetchResult::error('DataSaudi year-slice limit reached before a complete set.');
      }
      $slices++;
      $yearQuery = $query + ['Year' => $year];
      $page = $this->requestJson('/data.jsonrecords', $yearQuery);
      if ($page['status'] !== 'ok') {
        return DataSaudiFetchResult::error($page['message']);
      }
      $yearParsed = $this->extractDataPage($page['json']);
      if ($yearParsed['error'] !== '') {
        return DataSaudiFetchResult::error($yearParsed['error']);
      }
      if ($yearParsed['rows'] === [] && $yearParsed['total'] === 0) {
        continue;
      }
      if (count($yearParsed['rows']) !== $yearParsed['total']) {
        return DataSaudiFetchResult::error(sprintf(
          'DataSaudi Year=%d incomplete (%d/%d) and offset paging is not reliable.',
          $year,
          count($yearParsed['rows']),
          $yearParsed['total']
        ));
      }
      foreach ($yearParsed['rows'] as $i => $row) {
        $merged[$year . ':' . $i] = $row;
      }
    }
    if ($merged === []) {
      return DataSaudiFetchResult::empty('DataSaudi year slices returned no rows.');
    }
    return DataSaudiFetchResult::ok(array_values($merged), count($merged));
  }

  /**
   * @return array{status: string, json: array<string, mixed>, message: string}
   */
  private function requestJson(string $path, array $query): array {
    $url = rtrim($this->baseUrl, '/') . $path;
    $lastMessage = 'DataSaudi request failed.';
    for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
      try {
        $response = $this->httpClient->request('GET', $url, [
          'query' => $query,
          'timeout' => self::TIMEOUT,
          'connect_timeout' => self::CONNECT_TIMEOUT,
          'http_errors' => FALSE,
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'MotadedDrupalDataSaudi/1.0',
          ],
        ]);
        $status = $response->getStatusCode();
        if ($this->isTransientHttpStatus($status) && $attempt < self::MAX_ATTEMPTS) {
          $lastMessage = 'DataSaudi HTTP ' . $status . ' for ' . $path;
          $this->logger->warning('@msg (attempt @n)', [
            '@msg' => $lastMessage,
            '@n' => (string) $attempt,
          ]);
          usleep(400000 * (2 ** ($attempt - 1)));
          continue;
        }
        if ($status < 200 || $status >= 300) {
          return ['status' => 'error', 'json' => [], 'message' => 'DataSaudi HTTP ' . $status . ' for ' . $path];
        }
        $decoded = json_decode((string) $response->getBody(), TRUE);
        if (!is_array($decoded)) {
          return ['status' => 'error', 'json' => [], 'message' => 'DataSaudi response is not JSON for ' . $path];
        }
        return ['status' => 'ok', 'json' => $decoded, 'message' => ''];
      }
      catch (ConnectException | RequestException $e) {
        $lastMessage = $e->getMessage();
        $this->logger->warning('DataSaudi transport error for @path (attempt @n): @msg', [
          '@path' => $path,
          '@n' => (string) $attempt,
          '@msg' => $lastMessage,
        ]);
        if ($attempt < self::MAX_ATTEMPTS) {
          usleep(400000 * (2 ** ($attempt - 1)));
          continue;
        }
      }
      catch (GuzzleException $e) {
        return ['status' => 'error', 'json' => [], 'message' => $e->getMessage()];
      }
    }
    return ['status' => 'error', 'json' => [], 'message' => $lastMessage];
  }

  /**
   * @param array<string, mixed> $json
   *
   * @return array{rows: list<array<string, mixed>>, total: int, error: string}
   */
  private function extractDataPage(array $json): array {
    if (!isset($json['data']) || !is_array($json['data'])) {
      return ['rows' => [], 'total' => 0, 'error' => 'DataSaudi JSON missing data array.'];
    }
    $rows = [];
    foreach ($json['data'] as $row) {
      if (!is_array($row)) {
        return ['rows' => [], 'total' => 0, 'error' => 'DataSaudi data[] contains a non-object row.'];
      }
      $rows[] = $row;
    }
    $page = $json['page'] ?? NULL;
    if (!is_array($page) || !isset($page['total'])) {
      return ['rows' => [], 'total' => 0, 'error' => 'DataSaudi JSON missing page.total; refusing a partial read.'];
    }
    return ['rows' => $rows, 'total' => (int) $page['total'], 'error' => ''];
  }

  /**
   * @return array{period: string, period_id: int}|null
   */
  private function parsePeriod(mixed $month, mixed $monthId): ?array {
    $period = is_string($month) ? trim($month) : '';
    if (!preg_match('/^(\d{4})-(0[1-9]|1[0-2])$/', $period, $m)) {
      return NULL;
    }
    $periodId = ((int) $m[1] * 100) + (int) $m[2];
    if ($monthId !== NULL && $monthId !== '' && (int) $monthId !== $periodId) {
      return NULL;
    }
    return ['period' => $period, 'period_id' => $periodId];
  }

  private function isFiniteNumber(mixed $value): bool {
    if ($value === NULL || $value === '' || is_bool($value) || is_array($value)) {
      return FALSE;
    }
    if (!is_numeric($value)) {
      return FALSE;
    }
    return is_finite((float) $value);
  }

  private function isTransientHttpStatus(int $status): bool {
    return $status === 429 || $status === 408 || $status >= 500;
  }

}
