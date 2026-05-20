<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\MarketIndicators;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Datetime\TimeInterface;
use Psr\Log\LoggerInterface;

/**
 * Orchestrates World Bank fetch, normalization, and DB writes (v1).
 */
final class MarketIndicatorsImporter {

  private const SOURCE_NAME = 'World Bank';

  private const SOURCE_URL = 'https://data.worldbank.org';

  /**
   * World Bank-backed rows (v1 table order subset).
   *
   * @var list<array{key: string, label: string, wb_code: string, format: string}>
   */
  private const WB_INDICATORS = [
    ['key' => 'gdp_growth', 'label' => 'GDP Growth', 'wb_code' => 'NY.GDP.MKTP.KD.ZG', 'format' => 'percent'],
    ['key' => 'population', 'label' => 'Population', 'wb_code' => 'SP.POP.TOTL', 'format' => 'population'],
    ['key' => 'fdi_inflow', 'label' => 'FDI Inflows', 'wb_code' => 'BX.KLT.DINV.CD.WD', 'format' => 'usd'],
    ['key' => 'inflation', 'label' => 'Inflation Rate', 'wb_code' => 'FP.CPI.TOTL.ZG', 'format' => 'percent_plain'],
    ['key' => 'unemployment', 'label' => 'Unemployment Rate', 'wb_code' => 'SL.UEM.TOTL.ZS', 'format' => 'percent_plain'],
  ];

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly WorldBankIndicatorClient $worldBank,
    private readonly MarketIndicatorNormalizer $normalizer,
    private readonly MarketIndicatorRepository $repository,
    private readonly ConfigFactoryInterface $configFactory,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly TimeInterface $time,
    private readonly CacheTagsInvalidatorInterface $cacheTagsInvalidator,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * Imports all configured World Bank indicators and manual fallbacks.
   *
   * @return bool
   *   TRUE if every World Bank fetch returned at least one point; FALSE if any failed.
   */
  public function importAll(): bool {
    $fetchedAt = gmdate('Y-m-d H:i:s', $this->time->getRequestTime());
    $currentYear = (int) gmdate('Y', $this->time->getRequestTime());
    $yearMin = $currentYear - 5;
    $yearMax = $currentYear;

    $allWorldBankOk = TRUE;
    foreach (self::WB_INDICATORS as $def) {
      try {
        $points = $this->worldBank->fetchSeries($def['wb_code']);
        if ($points === []) {
          $this->logger->warning('No World Bank data returned for @key (@code); keeping previous DB values.', [
            '@key' => $def['key'],
            '@code' => $def['wb_code'],
          ]);
          $allWorldBankOk = FALSE;
          continue;
        }

        $byYear = [];
        foreach ($points as $p) {
          $byYear[$p['year']] = $p['value'];
        }
        $latestYear = max(array_keys($byYear));
        $latestValue = $byYear[$latestYear];
        $valueDisplay = $this->formatValue($def['format'], $latestValue);
        $this->repository->upsertIndicator([
          'indicator_key' => $def['key'],
          'label' => $def['label'],
          'value_raw' => $latestValue,
          'value_display' => $valueDisplay,
          'year' => $latestYear,
          'period' => NULL,
          'period_type' => 'annual',
          'source_name' => self::SOURCE_NAME,
          'source_url' => self::SOURCE_URL,
          'provider' => 'worldbank',
          'fetched_at' => $fetchedAt,
        ]);
        $this->repository->pruneIndicatorToYear($def['key'], $latestYear);

        if ($def['key'] === 'gdp_growth') {
          $seriesPoints = [];
          for ($y = $yearMin; $y <= $yearMax; $y++) {
            if (isset($byYear[$y])) {
              $seriesPoints[] = ['year' => $y, 'value' => $byYear[$y]];
            }
          }
          $this->repository->replaceSeriesWindow('gdp_growth', $yearMin, $yearMax, $seriesPoints, self::SOURCE_NAME);
        }
      }
      catch (\Throwable $e) {
        $this->logger->error('Market indicator import failed for @key: @msg', [
          '@key' => $def['key'],
          '@msg' => $e->getMessage(),
        ]);
        $allWorldBankOk = FALSE;
      }
    }

    try {
      $this->applyManualNonOil($fetchedAt);
    }
    catch (\Throwable $e) {
      $this->logger->error('Manual non-oil GDP import failed: @msg', ['@msg' => $e->getMessage()]);
    }

    $this->cacheTagsInvalidator->invalidateTags(['market_indicators']);

    return $allWorldBankOk;
  }

  private function formatValue(string $format, float $value): string {
    return match ($format) {
      'percent' => $this->normalizer->formatPercent($value),
      'percent_plain' => $this->normalizer->formatPercentPlain($value),
      'population' => $this->normalizer->formatPopulation($value),
      'usd' => $this->normalizer->formatUsdCurrent($value),
      default => (string) $value,
    };
  }

  private function applyManualNonOil(string $fetchedAt): void {
    $config = $this->configFactory->get('motaded_custom.market_indicators');
    $manual = $config->get('manual_indicators') ?? [];
    $row = $manual['non_oil_gdp_growth'] ?? [];
    if (empty($row['enabled'])) {
      return;
    }
    $year = isset($row['year']) ? (int) $row['year'] : 0;
    if ($year < 1960) {
      return;
    }
    $label = trim((string) ($row['label'] ?? 'Non-oil GDP Growth'));
    if ($label === '') {
      $label = 'Non-oil GDP Growth';
    }
    $sourceName = trim((string) ($row['source_name'] ?? ''));
    if ($sourceName === '') {
      $sourceName = 'Manual';
    }
    $sourceUrl = trim((string) ($row['source_url'] ?? ''));

    $valueRaw = isset($row['value_raw']) && $row['value_raw'] !== '' && $row['value_raw'] !== NULL
      ? (float) $row['value_raw']
      : NULL;
    $valueDisplay = trim((string) ($row['value_display'] ?? ''));
    if ($valueDisplay === '' && $valueRaw !== NULL) {
      $valueDisplay = $this->normalizer->formatPercent($valueRaw);
    }
    if ($valueDisplay === '') {
      return;
    }

    $this->repository->upsertIndicator([
      'indicator_key' => 'non_oil_gdp_growth',
      'label' => $label,
      'value_raw' => $valueRaw,
      'value_display' => $valueDisplay,
      'year' => $year,
      'period' => NULL,
      'period_type' => 'annual',
      'source_name' => $sourceName,
      'source_url' => $sourceUrl,
      'provider' => 'manual',
      'fetched_at' => $fetchedAt,
    ]);
    $this->repository->pruneIndicatorToYear('non_oil_gdp_growth', $year);
  }

}
