<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorKeyStats;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\motaded_custom\MarketIndicators\WorldBankIndicatorClient;
use Drupal\motaded_sector_data\Entity\SectorDataSourceInterface;

/**
 * Trade volume chart: config from {@link SectorDataSourceInterface}, with WB fallback.
 */
final class TradeVolumeChartBuilder {

  private const FALLBACK_EXPORTS = 'TX.VAL.MRCH.CD.WT';

  private const FALLBACK_IMPORTS = 'TM.VAL.MRCH.CD.WT';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly WorldBankIndicatorClient $worldBank,
  ) {}

  /**
   * @return array{
   *   encoded: string,
   *   has_growth: bool,
   *   cache_tags: string[],
   *   cache_max_age: int
   * }|null
   */
  public function build(string $sectorKey, string $usageKey): ?array {
    $useFallback = $usageKey === 'trade_volume_chart';

    if ($this->entityTypeManager->hasDefinition('sector_data_source')) {
      $entity = $this->loadActiveSource($sectorKey, $usageKey);
      if ($entity instanceof SectorDataSourceInterface && $entity->status()
        && $entity->getProvider() === 'worldbank'
        && $entity->getMergeMode() === 'sum_series_by_year') {
        $code1 = $entity->getWbIndicatorPrimary();
        $code2 = $entity->getWbIndicatorSecondary();
        if ($code1 !== '' && $code2 !== '') {
          $built = $this->buildMergedPayload(
            $code1,
            $code2,
            $entity->getMaxYears(),
            $entity->getValueScale(),
          );
          if ($built !== NULL) {
            return $built + [
              'cache_tags' => $entity->getCacheTagsToInvalidate(),
              'cache_max_age' => $entity->getCacheMaxAge(),
            ];
          }
        }
      }
    }

    if ($useFallback) {
      $built = $this->buildMergedPayload(
        self::FALLBACK_EXPORTS,
        self::FALLBACK_IMPORTS,
        24,
        'divide_1e9',
      );
      if ($built !== NULL) {
        return $built + [
          'cache_tags' => ['sector_indicators'],
          'cache_max_age' => 43200,
        ];
      }
    }

    return NULL;
  }

  /**
   * @return array{encoded: string, has_growth: bool}|null
   */
  private function buildMergedPayload(
    string $code1,
    string $code2,
    int $maxYears,
    string $valueScale,
  ): ?array {
    $exports = $this->worldBank->fetchSeries($code1);
    $imports = $this->worldBank->fetchSeries($code2);
    if ($exports === [] || $imports === []) {
      return NULL;
    }
    $importsByYear = [];
    foreach ($imports as $row) {
      $importsByYear[$row['year']] = $row['value'];
    }
    $merged = [];
    foreach ($exports as $exp) {
      $year = $exp['year'];
      if (!isset($importsByYear[$year])) {
        continue;
      }
      $e = $exp['value'];
      $im = $importsByYear[$year];
      if ($e <= 0.0 || $im <= 0.0) {
        continue;
      }
      $merged[] = [
        'year' => $year,
        'total' => $e + $im,
      ];
    }
    usort($merged, static fn(array $a, array $b): int => $a['year'] <=> $b['year']);
    if ($merged === []) {
      return NULL;
    }
    $maxYears = max(1, $maxYears);
    if (count($merged) > $maxYears) {
      $merged = array_values(array_slice($merged, -$maxYears));
    }

    $valueSeries = [];
    foreach ($merged as $row) {
      $raw = $row['total'];
      $display = match ($valueScale) {
        'divide_1e9' => (int) round($raw / 1_000_000_000.0),
        default => $raw,
      };
      $valueSeries[] = [
        'year' => $row['year'],
        'value' => $display,
      ];
    }

    $growthSeries = [];
    $n = count($merged);
    for ($i = 1; $i < $n; $i++) {
      $prev = $merged[$i - 1]['total'];
      $curr = $merged[$i]['total'];
      if ($prev <= 0.0) {
        continue;
      }
      $pct = (($curr - $prev) / $prev) * 100.0;
      $growthSeries[] = [
        'year' => $merged[$i]['year'],
        'growth' => round($pct, 1),
      ];
    }
    if ($valueSeries === []) {
      return NULL;
    }
    $payload = [
      'value' => $valueSeries,
      'growth' => $growthSeries,
    ];
    return [
      'encoded' => Json::encode($payload),
      'has_growth' => $growthSeries !== [],
    ];
  }

  private function loadActiveSource(string $sectorKey, string $usageKey): ?SectorDataSourceInterface {
    if (!$this->entityTypeManager->hasDefinition('sector_data_source')) {
      return NULL;
    }
    $storage = $this->entityTypeManager->getStorage('sector_data_source');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', TRUE)
      ->condition('sector_key', $sectorKey)
      ->condition('usage_key', $usageKey)
      ->sort('weight')
      ->sort('id')
      ->range(0, 1)
      ->execute();
    if ($ids === []) {
      return NULL;
    }
    $id = reset($ids);
    $entity = $storage->load($id);
    return $entity instanceof SectorDataSourceInterface ? $entity : NULL;
  }

}
