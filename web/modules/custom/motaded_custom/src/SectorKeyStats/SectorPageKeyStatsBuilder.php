<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorKeyStats;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\motaded_custom\MarketIndicators\MarketIndicatorNormalizer;
use Drupal\motaded_custom\MarketIndicators\WorldBankIndicatorClient;
use Drupal\motaded_custom\SectorIndicators\SectorIndicatorDefinitions;
use Drupal\motaded_custom\SectorIndicators\SectorIndicatorRepository;

/**
 * “Key stats” on sector_page: economy-wide WB + sector WB + GASTAT (DB), all sectors.
 *
 * @phpstan-type StatRow array{label: string, value: string, year?: int}
 */
final class SectorPageKeyStatsBuilder {

  use StringTranslationTrait;

  public function __construct(
    private readonly WorldBankIndicatorClient $worldBank,
    private readonly MarketIndicatorNormalizer $normalizer,
    private readonly SectorIndicatorRepository $sectorIndicatorRepository,
    TranslationInterface $translation,
  ) {
    $this->stringTranslation = $translation;
  }

  /**
   * @return list<StatRow>
   */
  public function build(string $sectorKey): array {
    $rows = [];
    foreach (SectorIndicatorDefinitions::economyWideRows() as $def) {
      $this->appendOneWorldBankStat(
        $rows,
        SectorIndicatorDefinitions::ECONOMY_WIDE_SECTOR_KEY,
        (string) $def['indicator_key'],
        (string) $def['wb_code'],
        (string) $def['format'],
        (string) $def['kpi_label'],
      );
    }
    foreach (SectorIndicatorDefinitions::worldBankRowsForSectorPage($sectorKey) as $def) {
      $this->appendOneWorldBankStat(
        $rows,
        $sectorKey,
        (string) $def['indicator_key'],
        (string) $def['wb_code'],
        (string) $def['format'],
        (string) $def['kpi_label'],
      );
    }
    $this->appendGastatFromDb($rows, $sectorKey);
    return $rows;
  }

  /**
   * @param list<StatRow> $rows
   */
  private function appendOneWorldBankStat(
    array &$rows,
    string $storageSectorKey,
    string $indicatorKey,
    string $wbCode,
    string $format,
    string $labelEnglish,
  ): void {
    $db = $this->sectorIndicatorRepository->loadLatest($storageSectorKey, $indicatorKey);
    $rawStr = $db['value_raw'] ?? NULL;
    if (is_numeric($rawStr)) {
      $raw = (float) $rawStr;
      $year = (int) ($db['year'] ?? 0);
      $rows[] = [
        'label' => (string) $this->t($labelEnglish),
        'value' => $this->formatFromRaw($raw, $format),
        'year' => $year,
      ];
      return;
    }
    $series = $this->worldBank->fetchSeries($wbCode);
    if ($series === []) {
      return;
    }
    $latest = $series[array_key_last($series)];
    $value = (float) $latest['value'];
    $year = (int) $latest['year'];
    $rows[] = [
      'label' => (string) $this->t($labelEnglish),
      'value' => $this->formatFromRaw($value, $format),
      'year' => $year,
    ];
  }

  private function formatFromRaw(float $value, string $format): string {
    return match ($format) {
      'usd_short' => $this->normalizer->formatUsdDollarShort($value),
      'usd' => $this->normalizer->formatUsdCurrent($value),
      'percent_plain' => $this->normalizer->formatPercentPlain($value),
      'compact_int' => $this->normalizer->formatCompactInt($value),
      'index' => $this->normalizer->formatIndex($value),
      'per_100' => $this->normalizer->formatPer100($value),
      'air_freight_wb' => $this->normalizer->formatAirFreightThousandTonKm($value),
      'teu_suffix' => $this->normalizer->formatContainerTeu($value),
      default => (string) $value,
    };
  }

  /**
   * @param list<StatRow> $rows
   */
  private function appendGastatFromDb(array &$rows, string $sectorKey): void {
    foreach (SectorIndicatorDefinitions::GASTAT_IMPORT_ROWS as $def) {
      if (($def['sector_key'] ?? '') !== $sectorKey) {
        continue;
      }
      $indicatorKey = (string) ($def['indicator_key'] ?? '');
      if ($indicatorKey === '') {
        continue;
      }
      $db = $this->sectorIndicatorRepository->loadLatest($sectorKey, $indicatorKey);
      $rawStr = $db['value_raw'] ?? NULL;
      if (!is_numeric($rawStr)) {
        continue;
      }
      $raw = (float) $rawStr;
      $year = (int) ($db['year'] ?? 0);
      $display = (string) ($db['value_display'] ?? '');
      if ($display === '') {
        $display = $this->normalizer->formatPercentPlain($raw);
      }
      $rows[] = [
        'label' => (string) $this->t((string) ($def['kpi_label'] ?? 'GDP growth')),
        'value' => $display,
        'year' => $year,
      ];
    }
  }

}
