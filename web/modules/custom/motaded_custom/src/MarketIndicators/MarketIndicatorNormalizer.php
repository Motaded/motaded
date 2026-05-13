<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\MarketIndicators;

/**
 * Builds value_display strings for v1 (EN, USD as US$, percents).
 */
final class MarketIndicatorNormalizer {

  public function formatPercent(float $value): string {
    $rounded = round($value, 1);
    if ($rounded > 0) {
      return '+' . $this->stripTrailingZero($rounded) . '%';
    }
    if ($rounded < 0) {
      return $this->stripTrailingZero($rounded) . '%';
    }
    return '0%';
  }

  /**
   * Percent without leading plus (inflation, unemployment).
   */
  public function formatPercentPlain(float $value): string {
    $rounded = round($value, 1);
    return $this->stripTrailingZero($rounded) . '%';
  }

  public function formatPopulation(float $value): string {
    if ($value >= 1_000_000_000) {
      return $this->stripTrailingZero(round($value / 1_000_000_000, 2)) . 'B';
    }
    if ($value >= 1_000_000) {
      return $this->stripTrailingZero(round($value / 1_000_000, 1)) . 'M';
    }
    if ($value >= 1_000) {
      return $this->stripTrailingZero(round($value / 1_000, 1)) . 'K';
    }
    return (string) (int) round($value);
  }

  /**
   * Formats current US$ amounts from World Bank (no FX conversion).
   */
  /**
   * Large absolute counts (tourist arrivals, air passengers, etc.).
   */
  public function formatCompactInt(float $value): string {
    return $this->formatPopulation($value);
  }

  /**
   * Index-style scores (e.g. LPI 1–5).
   */
  public function formatIndex(float $value): string {
    $rounded = round($value, 1);
    return $this->stripTrailingZero($rounded);
  }

  /**
   * “Per 100 people” style (mobile subscriptions).
   */
  public function formatPer100(float $value): string {
    return $this->stripTrailingZero(round($value, 1)) . ' /100';
  }

  public function formatUsdCurrent(float $value): string {
    $abs = abs($value);
    if ($abs >= 1e12) {
      return 'US$ ' . $this->stripTrailingZero(round($value / 1e12, 2)) . 'T';
    }
    if ($abs >= 1e9) {
      return 'US$ ' . $this->stripTrailingZero(round($value / 1e9, 1)) . 'B';
    }
    if ($abs >= 1e6) {
      return 'US$ ' . $this->stripTrailingZero(round($value / 1e6, 1)) . 'M';
    }
    if ($abs >= 1e3) {
      return 'US$ ' . $this->stripTrailingZero(round($value / 1e3, 1)) . 'K';
    }
    return 'US$ ' . $this->stripTrailingZero(round($value, 0));
  }

  /**
   * Short $ prefix for sector “Key stats” cards (merchandise trade, etc.).
   */
  public function formatUsdDollarShort(float $value): string {
    $abs = abs($value);
    if ($abs >= 1e12) {
      return '$' . $this->stripTrailingZero(round($value / 1e12, 2)) . 'T';
    }
    if ($abs >= 1e9) {
      return '$' . $this->stripTrailingZero(round($value / 1e9, 1)) . 'B';
    }
    if ($abs >= 1e6) {
      return '$' . $this->stripTrailingZero(round($value / 1e6, 1)) . 'M';
    }
    if ($abs >= 1e3) {
      return '$' . $this->stripTrailingZero(round($value / 1e3, 1)) . 'K';
    }
    return '$' . $this->stripTrailingZero(round($value, 0));
  }

  /**
   * Air freight (WB IS.AIR.GOOD.MT.K1) — thousand metric ton-km wording for stored display.
   */
  public function formatAirFreightThousandTonKm(float $value): string {
    return $this->formatCompactInt($value) . ' thousand metric ton-km';
  }

  /**
   * Container port traffic in TEU (compact magnitude + unit).
   */
  public function formatContainerTeu(float $value): string {
    return $this->formatCompactInt($value) . ' TEU';
  }

  private function stripTrailingZero(float $num): string {
    $s = (string) $num;
    if (str_contains($s, '.')) {
      $s = rtrim(rtrim($s, '0'), '.');
    }
    return $s === '' || $s === '-' ? '0' : $s;
  }

}
