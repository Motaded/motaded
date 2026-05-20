<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorIndicators;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Fetches GASTAT JSON (SDMX-style) for Saudi sector time series used in imports.
 */
final class GastatStatisticsClient {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly \GuzzleHttp\ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * GDP growth (current prices) for “Transport, Storage & Communication” (EN label in API).
   *
   * @return list<array{year: int, value: float}>
   */
  public function fetchTransportStorageCommunicationGrowthSeries(?string $url = NULL): array {
    return $this->fetchSeriesForGastatMatch(SectorIndicatorDefinitions::GASTAT_MATCH_TRANSPORT_STORAGE_COMM, $url);
  }

  /**
   * YoY GDP growth (%) rows for a configured GASTAT economic-activity match.
   *
   * @return list<array{year: int, value: float}>
   */
  public function fetchSeriesForGastatMatch(string $match, ?string $url = NULL): array {
    $effectiveUrl = $url !== NULL && $url !== '' ? $url : SectorIndicatorDefinitions::GASTAT_API_URL;
    try {
      $response = $this->httpClient->request('GET', $effectiveUrl, [
        'timeout' => 45,
        'connect_timeout' => 15,
        'version' => 1.1,
        'headers' => [
          'Accept' => 'application/json',
          'User-Agent' => 'MotadedDrupalGastatImport/1.0',
        ],
      ]);
      $decoded = json_decode((string) $response->getBody(), TRUE);
      if (!is_array($decoded)) {
        return [];
      }
      $data = $decoded['data'] ?? $decoded['Data'] ?? $decoded['value'] ?? NULL;
      if (!is_array($data)) {
        return [];
      }
      $matches = [];
      foreach ($data as $row) {
        if (!is_array($row)) {
          continue;
        }
        $activity = trim((string) ($row['ECONOMIC_ACTIVITY_ENGL'] ?? $row['ECONOMIC_ACTIVITY'] ?? $row['economic_activity'] ?? ''));
        if ($activity === '' || !$this->rowMatchesGastatMatch($activity, $match)) {
          continue;
        }
        $period = $row['YEAR_TIME'] ?? $row['TIME_PERIOD'] ?? $row['time_period'] ?? NULL;
        $obs = $row['OBSVALUE_OBSV'] ?? $row['OBS_VALUE'] ?? $row['obs_value'] ?? NULL;
        if ($period === NULL || $obs === NULL || $obs === '') {
          continue;
        }
        $year = (int) $period;
        if ($year < 1990) {
          continue;
        }
        $matches[] = ['year' => $year, 'value' => (float) $obs];
      }
      if ($matches === []) {
        return [];
      }
      usort($matches, static fn(array $a, array $b): int => $a['year'] <=> $b['year']);
      return $matches;
    }
    catch (GuzzleException $e) {
      $this->logger->warning('GASTAT request failed: @m', ['@m' => $e->getMessage()]);
      return [];
    }
    catch (\Throwable $e) {
      $this->logger->warning('GASTAT parse failed: @m', ['@m' => $e->getMessage()]);
      return [];
    }
  }

  private function rowMatchesGastatMatch(string $activity, string $match): bool {
    $a = trim($activity);
    if ($a === '') {
      return FALSE;
    }
    return match ($match) {
      SectorIndicatorDefinitions::GASTAT_MATCH_TRANSPORT_STORAGE_COMM => stripos($a, 'Transport') !== FALSE
        && (stripos($a, 'Storage') !== FALSE || stripos($a, 'Communication') !== FALSE),
      SectorIndicatorDefinitions::GASTAT_MATCH_CONSTRUCTION => strcasecmp($a, 'Construction') === 0
        || (stripos($a, 'Construction') !== FALSE && stripos($a, 'Infrastructure') === FALSE),
      SectorIndicatorDefinitions::GASTAT_MATCH_MANUFACTURING => stripos($a, 'Manufacturing') !== FALSE
        && stripos($a, 'excluding') === FALSE,
      SectorIndicatorDefinitions::GASTAT_MATCH_ELECTRICITY_GAS_WATER => stripos($a, 'Electricity') !== FALSE
        && stripos($a, 'Gas') !== FALSE
        && stripos($a, 'Water') !== FALSE,
      default => FALSE,
    };
  }

}
