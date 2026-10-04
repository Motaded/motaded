<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Builds render variables from the DataSaudi monthly snapshot (no HTTP).
 */
final class DataSaudiTradePresenter {

  use StringTranslationTrait;

  public function __construct(
    private readonly DataSaudiTradeRepository $repository,
    private readonly DataSaudiTradeCharts $charts,
    private readonly DateFormatterInterface $dateFormatter,
    TranslationInterface $stringTranslation,
  ) {
    $this->setStringTranslation($stringTranslation);
  }

  /**
   * @return array<string, mixed>
   */
  public function buildMarketOverview(string $langcode): array {
    $build = $this->baseChrome($langcode);
    $latest = $this->repository->loadLatestBalance();
    if ($latest === NULL) {
      return $build;
    }

    $importsM = (float) $latest['imports_million_sar'];
    $exportsM = (float) $latest['exports_million_sar'];
    $period = (string) $latest['period'];
    $priorPeriod = $this->sameMonthPreviousYear($period);
    $prior = $priorPeriod !== NULL ? $this->repository->loadBalancePeriod($priorPeriod) : NULL;

    $series = $this->repository->loadLatestAvailableBalanceMonths(24);
    $chartPoints = [];
    foreach ($series as $row) {
      $chartPoints[] = [
        'period' => (string) $row['period'],
        'label' => $this->formatPeriod((string) $row['period'], $langcode, TRUE),
        'imports_bn' => $this->toBillion((float) $row['imports_million_sar']),
        'exports_bn' => $this->toBillion((float) $row['exports_million_sar']),
      ];
    }

    $build['has_data'] = TRUE;
    $build['period'] = $period;
    $build['period_label'] = $this->formatPeriod($period, $langcode, FALSE);
    $firstPeriod = (string) ($series[0]['period'] ?? $period);
    $lastPeriod = (string) ($series[array_key_last($series)]['period'] ?? $period);
    $build['range_label'] = $this->formatPeriod($firstPeriod, $langcode, FALSE)
      . '–'
      . $this->formatPeriod($lastPeriod, $langcode, FALSE);
    $build['hero'] = $this->kpi(
      (string) $this->t('Total imports and exports'),
      $importsM + $exportsM,
      NULL,
      NULL,
      $langcode,
      (string) $this->t('Imports plus exports in the same month'),
    );
    $build['imports'] = $this->kpi(
      (string) $this->t('Imports'),
      $importsM,
      $prior !== NULL ? (float) $prior['imports_million_sar'] : NULL,
      $priorPeriod,
      $langcode,
    );
    $build['exports'] = $this->kpi(
      (string) $this->t('Exports'),
      $exportsM,
      $prior !== NULL ? (float) $prior['exports_million_sar'] : NULL,
      $priorPeriod,
      $langcode,
    );
    $build['chart_svg'] = $this->charts->lineImportsExports($chartPoints, $langcode);
    $build['chart_title'] = $this->t('Monthly imports and exports · SAR billion');
    return $build;
  }

  public function cacheableMetadata(): CacheableMetadata {
    $metadata = new CacheableMetadata();
    $metadata->addCacheTags([DataSaudiTradeImporter::CACHE_TAG]);
    $metadata->addCacheContexts(['languages:language_interface', 'languages:language_content']);
    return $metadata;
  }

  /**
   * @return array<string, mixed>
   */
  private function baseChrome(string $langcode): array {
    $isAr = str_starts_with($langcode, 'ar');
    return [
      'has_data' => FALSE,
      'period' => '',
      'period_label' => '',
      'hero' => [],
      'imports' => [],
      'exports' => [],
      'chart_svg' => NULL,
      'chart_title' => '',
      'range_label' => '',
      'unit_label' => $this->t('SAR billion'),
      'source_label' => $this->t('Source: GASTAT via DataSaudi'),
      'source_url' => $isAr ? 'https://datasaudi.sa/ar' : 'https://datasaudi.sa/en',
      'empty_message' => $this->t('Official trade figures will appear here after the next successful import.'),
    ];
  }

  /**
   * @return array<string, mixed>
   */
  private function kpi(string $label, float $millionSar, ?float $priorMillion, ?string $priorPeriod, string $langcode, string $hint = ''): array {
    $billion = $this->toBillion($millionSar);
    $change = NULL;
    $changeDisplay = '';
    $changeHint = '';
    if ($priorMillion !== NULL && $priorMillion != 0.0 && $priorPeriod !== NULL) {
      $change = (($millionSar - $priorMillion) / $priorMillion) * 100.0;
      $changeDisplay = $this->formatChange($change);
      $changeHint = (string) $this->t('vs @period', [
        '@period' => $this->formatPeriod($priorPeriod, $langcode, FALSE),
      ]);
    }
    return [
      'label' => $label,
      'value_display' => $this->formatBillion($billion),
      'unit_label' => $this->t('SAR billion'),
      'hint' => $hint,
      'change' => $change,
      'change_display' => $changeDisplay,
      'change_hint' => $changeHint,
    ];
  }

  private function toBillion(float $millionSar): float {
    return $millionSar / 1000.0;
  }

  private function formatBillion(float $billion): string {
    return number_format(round($billion, 1), 1, '.', '');
  }

  private function formatChange(float $percent): string {
    $rounded = round($percent, 1);
    if ($rounded > 0) {
      return '+' . $this->stripTrailingZero($rounded) . '%';
    }
    return $this->stripTrailingZero($rounded) . '%';
  }

  private function stripTrailingZero(float $value): string {
    $formatted = number_format($value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
  }

  private function formatPeriod(string $period, string $langcode, bool $short): string {
    if (!preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
      return $period;
    }
    $ts = gmmktime(0, 0, 0, (int) $m[2], 1, (int) $m[1]);
    $pattern = $short ? 'M Y' : 'F Y';
    return $this->dateFormatter->format((int) $ts, 'custom', $pattern, 'UTC', $langcode);
  }

  private function sameMonthPreviousYear(string $period): ?string {
    if (!preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
      return NULL;
    }
    return sprintf('%04d-%s', ((int) $m[1]) - 1, $m[2]);
  }

}
