<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\MarketIndicators;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Fetches indicator time series from the World Bank API v2 (country SAU).
 *
 * Order: **PHP ext-curl first** (often more predictable in DDEV/Docker), then Guzzle
 * (`@http_client`) with HTTP/1.1 + IPv4. Avoids HTTP/2 edge cases to Cloudflare-fronted WB.
 */
final class WorldBankIndicatorClient {

  private const BASE = 'https://api.worldbank.org/v2/country/SAU/indicator';

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly \GuzzleHttp\ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * Returns observations sorted by year ascending.
   *
   * @return list<array{year: int, value: float}>
   */
  public function fetchSeries(string $worldBankIndicatorCode): array {
    $year_hi = (int) gmdate('Y') + 1;
    $year_lo = max(1960, $year_hi - 45);
    $url = self::BASE . '/' . rawurlencode($worldBankIndicatorCode)
      . '?format=json&per_page=120&date=' . $year_lo . ':' . $year_hi;

    $curlPoints = $this->fetchSeriesViaPhpCurl($url, $worldBankIndicatorCode);
    if ($curlPoints !== []) {
      return $curlPoints;
    }

    return $this->fetchSeriesViaGuzzle($url, $worldBankIndicatorCode);
  }

  /**
   * Direct ext-curl GET — same URL you would test with `curl -4` from the container.
   *
   * @return list<array{year: int, value: float}>
   */
  private function fetchSeriesViaPhpCurl(string $url, string $worldBankIndicatorCode): array {
    if (!\extension_loaded('curl') || !\function_exists('curl_init')) {
      return [];
    }
    $ch = curl_init($url);
    if ($ch === FALSE) {
      return [];
    }
    curl_setopt_array($ch, [
      \CURLOPT_RETURNTRANSFER => TRUE,
      \CURLOPT_FOLLOWLOCATION => TRUE,
      \CURLOPT_TIMEOUT => 45,
      \CURLOPT_CONNECTTIMEOUT => 15,
      \CURLOPT_HTTP_VERSION => \CURL_HTTP_VERSION_1_1,
      \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
      \CURLOPT_ENCODING => '',
      \CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'User-Agent: MotadedDrupalMarketIndicators/1.3-php-curl',
        'Connection: close',
      ],
    ]);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $errstr = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === FALSE || $errno !== 0) {
      $this->logger->notice('World Bank PHP-cURL attempt failed for @code: @msg', [
        '@code' => $worldBankIndicatorCode,
        '@msg' => $errstr !== '' ? $errstr : ('errno ' . (string) $errno),
      ]);
      return [];
    }
    if ($httpCode < 200 || $httpCode >= 300) {
      $this->logger->notice('World Bank PHP-cURL HTTP @status for @code', [
        '@status' => (string) $httpCode,
        '@code' => $worldBankIndicatorCode,
      ]);
      return [];
    }

    return $this->decodeWorldBankJson((string) $body, $worldBankIndicatorCode);
  }

  /**
   * @return list<array{year: int, value: float}>
   */
  private function fetchSeriesViaGuzzle(string $url, string $worldBankIndicatorCode): array {
    $lastException = NULL;
    $maxAttempts = 4;
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
      try {
        $response = $this->httpClient->request('GET', $url, [
          'timeout' => 35,
          'connect_timeout' => 15,
          'version' => 1.1,
          'http_errors' => FALSE,
          'curl' => [
            \CURLOPT_HTTP_VERSION => \CURL_HTTP_VERSION_1_1,
            \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
          ],
          'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'MotadedDrupalMarketIndicators/1.2-guzzle',
            'Connection' => 'close',
          ],
        ]);
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
          $this->logger->warning('World Bank HTTP @status for @code (attempt @n)', [
            '@status' => (string) $status,
            '@code' => $worldBankIndicatorCode,
            '@n' => (string) $attempt,
          ]);
          if ($attempt < $maxAttempts) {
            usleep(500000 * (2 ** min($attempt - 1, 4)));
          }
          continue;
        }
        $body = (string) $response->getBody();
        $points = $this->decodeWorldBankJson($body, $worldBankIndicatorCode);
        if ($points !== []) {
          return $points;
        }
        return [];
      }
      catch (GuzzleException $e) {
        $lastException = $e;
        $this->logger->warning('World Bank Guzzle attempt @n failed for @code: @msg', [
          '@n' => (string) $attempt,
          '@code' => $worldBankIndicatorCode,
          '@msg' => $e->getMessage(),
        ]);
        if ($attempt < $maxAttempts) {
          usleep(500000 * (2 ** min($attempt - 1, 4)));
        }
      }
    }
    if ($lastException instanceof \Throwable) {
      $this->logger->error('World Bank Guzzle fetch failed for @code after retries: @msg', [
        '@code' => $worldBankIndicatorCode,
        '@msg' => $lastException->getMessage(),
      ]);
    }
    return [];
  }

  /**
   * @return list<array{year: int, value: float}>
   */
  private function decodeWorldBankJson(string $body, string $worldBankIndicatorCode): array {
    $body = trim($body);
    if ($body === '') {
      return [];
    }
    $decoded = json_decode($body, TRUE);
    if (!is_array($decoded)) {
      $this->logger->notice('World Bank response not JSON for @code (first 120 chars): @snippet', [
        '@code' => $worldBankIndicatorCode,
        '@snippet' => substr($body, 0, 120),
      ]);
      return [];
    }
    // Error envelope, e.g. archived/invalid indicator:
    // [{"message":[{"id":"175","key":"Invalid format","value":"..."}]}]
    if (\count($decoded) === 1 && isset($decoded[0]) && is_array($decoded[0]) && isset($decoded[0]['message'])) {
      $parts = [];
      foreach ((array) $decoded[0]['message'] as $m) {
        if (is_array($m) && isset($m['value'])) {
          $parts[] = (string) $m['value'];
        }
      }
      $this->logger->notice('World Bank API declined @code: @msg', [
        '@code' => $worldBankIndicatorCode,
        '@msg' => $parts !== [] ? implode('; ', $parts) : 'unknown',
      ]);
      return [];
    }
    if (!\array_key_exists(1, $decoded)) {
      $this->logger->notice('World Bank JSON missing observations for @code', ['@code' => $worldBankIndicatorCode]);
      return [];
    }
    if ($decoded[1] === NULL) {
      return [];
    }
    if (!is_array($decoded[1])) {
      $this->logger->notice('World Bank observations not an array for @code', ['@code' => $worldBankIndicatorCode]);
      return [];
    }
    $points = [];
    foreach ($decoded[1] as $row) {
      if (!is_array($row) || !isset($row['date'])) {
        continue;
      }
      $year = (int) $row['date'];
      if ($year < 1960) {
        continue;
      }
      $raw = $row['value'] ?? NULL;
      if ($raw === NULL || $raw === '') {
        continue;
      }
      $points[] = [
        'year' => $year,
        'value' => (float) $raw,
      ];
    }
    usort($points, static fn(array $a, array $b): int => $a['year'] <=> $b['year']);
    return $points;
  }

}
