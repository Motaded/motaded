<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Setup cost calculator profile (steps, pricing matrix, UI copy).
 */
interface SetupCostProfileInterface extends ConfigEntityInterface {

  public function getCurrency(): string;

  public function getWebformId(): string;

  public function getGlobalSetupBufferMin(): float;

  public function getGlobalSetupBufferMax(): float;

  public function getDisclaimer(): string;

  public function getLastUpdated(): string;

  public function getMethodologySource(): string;

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getSteps(): array;

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getLineItems(): array;

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getModifiers(): array;

  /**
   * @return array<string, mixed>
   */
  public function getMonthly(): array;

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getTimelines(): array;

  /**
   * @return array{min_weeks: int, max_weeks: int}
   */
  public function getTimelineDefault(): array;

  /**
   * @return array<int, array<string, mixed>>
   */
  public function getPackages(): array;

  /**
   * @return array<string, mixed>
   */
  public function getUi(): array;

  /**
   * Full profile as array for calculator / frontend export.
   *
   * @return array<string, mixed>
   */
  public function toCalculatorArray(): array;

}
