<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Calculator;

/**
 * Computes indicative setup / monthly ranges from a profile + user answers.
 */
final class SetupCostCalculator {

  /**
   * @param array<string, mixed> $profile
   *   Output of SetupCostProfileInterface::toCalculatorArray().
   * @param array<string, mixed> $answers
   *   Keys = step ids; values = string, int, or string[] (checkboxes).
   *
   * @return array<string, mixed>
   */
  public function calculate(array $profile, array $answers): array {
    $currency = (string) ($profile['currency'] ?? 'SAR');
    $breakdown = [];
    $setupMin = 0.0;
    $setupMax = 0.0;

    foreach ($profile['line_items'] ?? [] as $item) {
      $id = (string) ($item['id'] ?? '');
      if ($id === '') {
        continue;
      }
      $min = (float) ($item['base_min'] ?? 0);
      $max = (float) ($item['base_max'] ?? 0);
      foreach ($profile['modifiers'] ?? [] as $modifier) {
        if ((string) ($modifier['line_item_id'] ?? '') !== $id) {
          continue;
        }
        if (!$this->modifierMatches($answers, $modifier)) {
          continue;
        }
        $min = $this->applyModifier($min, $modifier, 'min');
        $max = $this->applyModifier($max, $modifier, 'max');
      }
      $breakdown[] = [
        'id' => $id,
        'label' => (string) ($item['label'] ?? $id),
        'min' => round(max(0, $min)),
        'max' => round(max(0, $max)),
      ];
      $setupMin += $min;
      $setupMax += $max;
    }

    $bufMin = (float) ($profile['global_setup_buffer_min'] ?? 1);
    $bufMax = (float) ($profile['global_setup_buffer_max'] ?? 1);
    $setupMin = round($setupMin * $bufMin);
    $setupMax = round($setupMax * $bufMax);

    $monthly = $this->calculateMonthly($profile, $answers);
    $timeline = $this->calculateTimeline($profile, $answers);
    $package = $this->calculatePackage($profile, $answers);

    return [
      'currency' => $currency,
      'setup_min' => (int) $setupMin,
      'setup_max' => (int) $setupMax,
      'monthly_min' => (int) $monthly['min'],
      'monthly_max' => (int) $monthly['max'],
      'timeline_min_weeks' => (int) $timeline['min_weeks'],
      'timeline_max_weeks' => (int) $timeline['max_weeks'],
      'breakdown' => $breakdown,
      'package' => $package,
      'answers' => $answers,
    ];
  }

  /**
   * @param array<string, mixed> $answers
   * @param array<string, mixed> $modifier
   */
  private function modifierMatches(array $answers, array $modifier): bool {
    $dimension = (string) ($modifier['dimension'] ?? '');
    if ($dimension === '') {
      return FALSE;
    }
    $answer = $answers[$dimension] ?? NULL;
    $value = (string) ($modifier['value'] ?? '');
    $valueMin = $modifier['value_min'] ?? '';
    $valueMax = $modifier['value_max'] ?? '';

    if (is_array($answer)) {
      if ($value === '*' || $value === '') {
        return !empty($answer);
      }
      return in_array($value, $answer, TRUE);
    }

    if ($valueMin !== '' || $valueMax !== '') {
      $num = is_numeric($answer) ? (float) $answer : 0.0;
      if ($valueMin !== '' && $num < (float) $valueMin) {
        return FALSE;
      }
      if ($valueMax !== '' && $num > (float) $valueMax) {
        return FALSE;
      }
      return TRUE;
    }

    if ($value === '*' || $value === '') {
      return $answer !== NULL && $answer !== '';
    }

    return (string) $answer === $value;
  }

  /**
   * @param array<string, mixed> $modifier
   */
  private function applyModifier(float $amount, array $modifier, string $side): float {
    $add = (float) ($modifier['add_' . $side] ?? 0);
    $mul = (float) ($modifier['multiply_' . $side] ?? 0);
    if ($mul > 0) {
      $amount *= $mul;
    }
    return $amount + $add;
  }

  /**
   * @param array<string, mixed> $profile
   * @param array<string, mixed> $answers
   *
   * @return array{min: float, max: float}
   */
  private function calculateMonthly(array $profile, array $answers): array {
    $monthly = $profile['monthly'] ?? [];
    $min = (float) ($monthly['base_min'] ?? 0);
    $max = (float) ($monthly['base_max'] ?? 0);

    $employees = 0;
    $officeAnswer = '';
    foreach ($profile['steps'] ?? [] as $step) {
      $stepId = (string) ($step['id'] ?? '');
      if (($step['type'] ?? '') === 'slider') {
        $employees = (int) ($answers[$stepId] ?? 0);
      }
      if ($stepId === 'office' || ($step['type'] ?? '') === 'cards' && str_contains($stepId, 'office')) {
        $officeAnswer = (string) ($answers[$stepId] ?? '');
      }
    }

    $min += $employees * (float) ($monthly['per_employee_min'] ?? 0);
    $max += $employees * (float) ($monthly['per_employee_max'] ?? 0);

    if ($officeAnswer === '') {
      $officeAnswer = (string) ($answers['office'] ?? '');
    }
    foreach ($monthly['office'] ?? [] as $row) {
      if ((string) ($row['option_id'] ?? '') === $officeAnswer) {
        $min += (float) ($row['add_min'] ?? 0);
        $max += (float) ($row['add_max'] ?? 0);
        break;
      }
    }

    $selectedServices = $answers['services'] ?? [];
    if (!is_array($selectedServices)) {
      foreach ($profile['steps'] ?? [] as $step) {
        if (($step['type'] ?? '') === 'checkboxes') {
          $selectedServices = $answers[(string) ($step['id'] ?? 'services')] ?? [];
          break;
        }
      }
    }
    if (!is_array($selectedServices)) {
      $selectedServices = $selectedServices !== '' && $selectedServices !== NULL ? [(string) $selectedServices] : [];
    }
    foreach ($monthly['service'] ?? [] as $row) {
      if (in_array((string) ($row['option_id'] ?? ''), $selectedServices, TRUE)) {
        $min += (float) ($row['add_min'] ?? 0);
        $max += (float) ($row['add_max'] ?? 0);
      }
    }

    return ['min' => round(max(0, $min)), 'max' => round(max(0, $max))];
  }

  /**
   * @param array<string, mixed> $profile
   * @param array<string, mixed> $answers
   *
   * @return array{min_weeks: int, max_weeks: int}
   */
  private function calculateTimeline(array $profile, array $answers): array {
    foreach ($profile['timelines'] ?? [] as $rule) {
      if ($this->conditionsMatch($answers, $rule['conditions'] ?? [])) {
        return [
          'min_weeks' => (int) ($rule['min_weeks'] ?? 2),
          'max_weeks' => (int) ($rule['max_weeks'] ?? 6),
        ];
      }
    }
    $def = $profile['timeline_default'] ?? ['min_weeks' => 2, 'max_weeks' => 6];
    return [
      'min_weeks' => (int) ($def['min_weeks'] ?? 2),
      'max_weeks' => (int) ($def['max_weeks'] ?? 6),
    ];
  }

  /**
   * @param array<string, mixed> $answers
   * @param array<int, array<string, string>> $conditions
   */
  private function conditionsMatch(array $answers, array $conditions): bool {
    if ($conditions === []) {
      return TRUE;
    }
    foreach ($conditions as $cond) {
      $dim = (string) ($cond['dimension'] ?? '');
      $val = (string) ($cond['value'] ?? '');
      if ($dim === '') {
        continue;
      }
      $answer = $answers[$dim] ?? NULL;
      if (is_array($answer)) {
        if (!in_array($val, $answer, TRUE)) {
          return FALSE;
        }
      }
      elseif ((string) $answer !== $val) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * @param array<string, mixed> $profile
   * @param array<string, mixed> $answers
   *
   * @return array<string, mixed>|null
   */
  private function calculatePackage(array $profile, array $answers): ?array {
    $packages = $profile['packages'] ?? [];
    if ($packages === []) {
      return NULL;
    }
    $points = 0;
    foreach ($packages as $pkg) {
      foreach ($pkg['rules'] ?? [] as $rule) {
        $dim = (string) ($rule['dimension'] ?? '');
        $val = (string) ($rule['value'] ?? '');
        $pts = (int) ($rule['points'] ?? 0);
        if ($dim === '' || $pts === 0) {
          continue;
        }
        $answer = $answers[$dim] ?? NULL;
        if (is_array($answer) && in_array($val, $answer, TRUE)) {
          $points += $pts;
        }
        elseif ((string) $answer === $val) {
          $points += $pts;
        }
      }
    }

    $best = NULL;
    foreach ($packages as $pkg) {
      $minPts = (int) ($pkg['min_points'] ?? 0);
      if ($points >= $minPts) {
        $best = $pkg;
      }
    }
    return $best;
  }

}
