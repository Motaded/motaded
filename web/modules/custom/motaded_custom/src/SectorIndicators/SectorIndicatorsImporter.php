<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorIndicators;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\motaded_custom\MarketIndicators\MarketIndicatorNormalizer;
use Drupal\motaded_custom\MarketIndicators\WorldBankIndicatorClient;
use Psr\Log\LoggerInterface;

/**
 * Fetches World Bank series for sector indicators and writes {sector_indicator_points}.
 */
final class SectorIndicatorsImporter {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly WorldBankIndicatorClient $worldBank,
    private readonly MarketIndicatorNormalizer $normalizer,
    private readonly SectorIndicatorRepository $repository,
    private readonly GastatStatisticsClient $gastat,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly TimeInterface $time,
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * Imports all configured sector series.
   *
   * @return bool
   *   TRUE if every World Bank fetch and every GASTAT import returned data.
   */
  public function importAll(): bool {
    $fetchedAt = gmdate('Y-m-d H:i:s', $this->time->getRequestTime());
    $allOk = TRUE;
    foreach (SectorIndicatorDefinitions::ALL_ROWS as $def) {
      try {
        $points = $this->worldBank->fetchSeries($def['wb_code']);
        if ($points === []) {
          $this->logger->warning('No World Bank data for sector @s indicator @i (@code); keeping previous DB values.', [
            '@s' => $def['sector_key'],
            '@i' => $def['indicator_key'],
            '@code' => $def['wb_code'],
          ]);
          $allOk = FALSE;
          continue;
        }
        foreach ($points as $p) {
          $year = (int) $p['year'];
          $value = (float) $p['value'];
          $display = $this->formatValue((string) $def['format'], $value);
          $this->repository->upsertPoint([
            'sector_key' => $def['sector_key'],
            'indicator_key' => $def['indicator_key'],
            'wb_code' => $def['wb_code'],
            'year' => $year,
            'value_raw' => $value,
            'value_display' => $display,
            'source_name' => SectorIndicatorDefinitions::SOURCE_NAME,
            'source_url' => SectorIndicatorDefinitions::SOURCE_URL,
            'provider' => SectorIndicatorDefinitions::PROVIDER,
            'fetched_at' => $fetchedAt,
          ]);
        }
      }
      catch (\Throwable $e) {
        $this->logger->error('Sector indicator import failed for @s/@i: @msg', [
          '@s' => $def['sector_key'],
          '@i' => $def['indicator_key'],
          '@msg' => $e->getMessage(),
        ]);
        $allOk = FALSE;
      }
    }

    $allOk = $allOk && $this->importGastat($fetchedAt);

    $this->cacheTagsInvalidator->invalidateTags(['sector_indicators']);

    return $allOk;
  }

  private function importGastat(string $fetchedAt): bool {
    if (SectorIndicatorDefinitions::GASTAT_IMPORT_ROWS === []) {
      return TRUE;
    }
    $ok = TRUE;
    foreach (SectorIndicatorDefinitions::GASTAT_IMPORT_ROWS as $def) {
      $match = (string) ($def['gastat_match'] ?? SectorIndicatorDefinitions::GASTAT_MATCH_TRANSPORT_STORAGE_COMM);
      try {
        $series = $this->gastat->fetchSeriesForGastatMatch($match);
        if ($series === []) {
          $this->logger->warning('No GASTAT series for @s/@i (match @m); keeping previous DB values.', [
            '@s' => $def['sector_key'],
            '@i' => $def['indicator_key'],
            '@m' => $match,
          ]);
          $ok = FALSE;
          continue;
        }
        foreach ($series as $p) {
          $year = (int) $p['year'];
          $value = (float) $p['value'];
          $display = $this->formatValue((string) $def['format'], $value);
          $this->repository->upsertPoint([
            'sector_key' => $def['sector_key'],
            'indicator_key' => $def['indicator_key'],
            'wb_code' => $def['external_code'],
            'year' => $year,
            'value_raw' => $value,
            'value_display' => $display,
            'source_name' => SectorIndicatorDefinitions::GASTAT_SOURCE_NAME,
            'source_url' => SectorIndicatorDefinitions::GASTAT_SOURCE_URL,
            'provider' => SectorIndicatorDefinitions::GASTAT_PROVIDER,
            'fetched_at' => $fetchedAt,
          ]);
        }
      }
      catch (\Throwable $e) {
        $this->logger->error('GASTAT sector indicator import failed for @s/@i: @msg', [
          '@s' => $def['sector_key'],
          '@i' => $def['indicator_key'],
          '@msg' => $e->getMessage(),
        ]);
        $ok = FALSE;
      }
    }
    return $ok;
  }

  private function formatValue(string $format, float $value): string {
    return match ($format) {
      'usd' => $this->normalizer->formatUsdCurrent($value),
      'usd_short' => $this->normalizer->formatUsdDollarShort($value),
      'percent_plain' => $this->normalizer->formatPercentPlain($value),
      'compact_int' => $this->normalizer->formatCompactInt($value),
      'index' => $this->normalizer->formatIndex($value),
      'per_100' => $this->normalizer->formatPer100($value),
      'air_freight_wb' => $this->normalizer->formatAirFreightThousandTonKm($value),
      'teu_suffix' => $this->normalizer->formatContainerTeu($value),
      default => (string) $value,
    };
  }

}
