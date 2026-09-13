<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\DataSaudi;

use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Server-rendered SVG for DataSaudi trade blocks (same approach as glance/sector).
 */
final class DataSaudiTradeCharts {

  use StringTranslationTrait;

  public function __construct(TranslationInterface $stringTranslation) {
    $this->setStringTranslation($stringTranslation);
  }

  /**
   * @param list<array{period: string, label: string, imports_bn: float, exports_bn: float}> $points
   */
  public function lineImportsExports(array $points, string $langcode): ?Markup {
    if ($points === []) {
      return NULL;
    }
    $w = 720;
    $h = 320;
    $pl = 36;
    $pr = 58;
    $pt = 18;
    $pb = 32;
    $pw = $w - $pl - $pr;
    $ph = $h - $pt - $pb;
    $vals = [];
    foreach ($points as $point) {
      $vals[] = $point['imports_bn'];
      $vals[] = $point['exports_bn'];
    }
    $dataMax = max($vals);
    $ticks = $this->niceTicks($dataMax);
    $yMin = 0.0;
    $yMax = (float) $ticks[array_key_last($ticks)];
    if ($yMax <= $yMin) {
      $yMax = $yMin + 1.0;
    }
    $n = count($points);
    $xAt = static function (int $i) use ($pl, $pw, $n): float {
      if ($n <= 1) {
        return $pl + ($pw / 2);
      }
      return $pl + ($i / ($n - 1)) * $pw;
    };
    $yAt = static function (float $v) use ($pt, $ph, $yMin, $yMax): float {
      return $pt + (1.0 - (($v - $yMin) / ($yMax - $yMin))) * $ph;
    };

    $importPts = [];
    $exportPts = [];
    foreach ($points as $i => $point) {
      $x = $xAt($i);
      $importPts[] = sprintf('%.2f,%.2f', $x, $yAt($point['imports_bn']));
      $exportPts[] = sprintf('%.2f,%.2f', $x, $yAt($point['exports_bn']));
    }

    $grid = '';
    foreach ($ticks as $tv) {
      $gy = $yAt((float) $tv);
      $grid .= sprintf(
        '<line class="datasaudi-trade__grid" x1="%.2f" y1="%.2f" x2="%.2f" y2="%.2f"/>',
        $pl,
        $gy,
        $pl + $pw,
        $gy
      );
      $grid .= sprintf(
        '<text class="datasaudi-trade__tick" x="%.2f" y="%.2f">%s</text>',
        4.0,
        $gy + 3.5,
        $this->e($this->formatTick((float) $tv))
      );
    }

    $xlabels = '';
    foreach ($points as $i => $point) {
      if (!$this->showXLabel($i, $n, (string) $point['period'])) {
        continue;
      }
      $anchor = $i === $n - 1 ? 'end' : ($i === 0 ? 'start' : 'middle');
      $xlabels .= sprintf(
        '<text class="datasaudi-trade__xlabel" x="%.2f" y="%.2f" text-anchor="%s">%s</text>',
        $xAt($i),
        $h - 10.0,
        $anchor,
        $this->e($point['label'])
      );
    }

    $last = $n - 1;
    $endLabels = '';
    $hits = '';
    foreach ($points as $i => $point) {
      $hits .= sprintf(
        '<circle class="datasaudi-trade__hit" cx="%.2f" cy="%.2f" r="7"><title>%s: %s</title></circle>',
        $xAt($i),
        $yAt($point['imports_bn']),
        $this->e($point['label']),
        $this->e((string) $this->t('Imports @v', ['@v' => $this->formatEnd($point['imports_bn'])]))
      );
      $hits .= sprintf(
        '<circle class="datasaudi-trade__hit" cx="%.2f" cy="%.2f" r="7"><title>%s: %s</title></circle>',
        $xAt($i),
        $yAt($point['exports_bn']),
        $this->e($point['label']),
        $this->e((string) $this->t('Exports @v', ['@v' => $this->formatEnd($point['exports_bn'])]))
      );
    }
    $lastX = $xAt($last);
    $impY = $yAt($points[$last]['imports_bn']);
    $expY = $yAt($points[$last]['exports_bn']);
    $endLabels .= sprintf(
      '<circle class="datasaudi-trade__dot datasaudi-trade__dot--imports" cx="%.2f" cy="%.2f" r="3"/>',
      $lastX,
      $impY
    );
    $endLabels .= sprintf(
      '<circle class="datasaudi-trade__dot datasaudi-trade__dot--exports" cx="%.2f" cy="%.2f" r="3"/>',
      $lastX,
      $expY
    );
    $endLabels .= sprintf(
      '<text class="datasaudi-trade__end datasaudi-trade__end--imports" x="%.2f" y="%.2f">%s</text>',
      $lastX + 6,
      $impY + 4,
      $this->e($this->formatEnd($points[$last]['imports_bn']))
    );
    $endLabels .= sprintf(
      '<text class="datasaudi-trade__end datasaudi-trade__end--exports" x="%.2f" y="%.2f">%s</text>',
      $lastX + 6,
      $expY - 2,
      $this->e($this->formatEnd($points[$last]['exports_bn']))
    );

    $aria = $this->e((string) $this->t('Monthly merchandise imports and exports in SAR billion'));
    $svg = sprintf(
      '<svg class="datasaudi-trade__svg" viewBox="0 0 %d %d" width="%d" height="%d" role="img" aria-label="%s" dir="ltr">%s<polyline class="datasaudi-trade__line datasaudi-trade__line--imports" fill="none" stroke="#0f766e" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" points="%s"/><polyline class="datasaudi-trade__line datasaudi-trade__line--exports" fill="none" stroke="#334155" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" points="%s"/>%s%s%s</svg>',
      $w,
      $h,
      $w,
      $h,
      $aria,
      $grid,
      $this->e(implode(' ', $importPts)),
      $this->e(implode(' ', $exportPts)),
      $endLabels,
      $xlabels,
      $hits
    );
    unset($langcode);
    return Markup::create($svg);
  }

  /**
   * @return list<float>
   */
  private function niceTicks(float $max): array {
    if ($max <= 0) {
      return [0.0, 1.0];
    }
    $raw = $max / 5;
    $pow = 10 ** (int) floor(log10($raw));
    $err = $raw / $pow;
    $nice = 10.0;
    if ($err <= 1) {
      $nice = 1.0;
    }
    elseif ($err <= 2) {
      $nice = 2.0;
    }
    elseif ($err <= 2.5) {
      $nice = 2.5;
    }
    elseif ($err <= 5) {
      $nice = 5.0;
    }
    $step = $nice * $pow;
    $top = ceil($max / $step) * $step;
    $ticks = [];
    for ($v = 0.0; $v <= $top + ($step * 0.001); $v += $step) {
      $ticks[] = $v;
    }
    return $ticks;
  }

  private function showXLabel(int $index, int $count, string $period): bool {
    if ($index === 0 || $index === $count - 1) {
      return TRUE;
    }
    return str_ends_with($period, '-01') || str_ends_with($period, '-07');
  }

  private function formatTick(float $value): string {
    if (abs($value - round($value)) < 0.001) {
      return (string) (int) round($value);
    }
    return number_format($value, 1, '.', '');
  }

  private function formatEnd(float $value): string {
    return number_format(round($value, 1), 1, '.', '');
  }

  private function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }

}
