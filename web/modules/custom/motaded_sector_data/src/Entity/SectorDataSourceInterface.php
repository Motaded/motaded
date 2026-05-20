<?php

declare(strict_types=1);

namespace Drupal\motaded_sector_data\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * A configurable external data source for sector / KPI features.
 */
interface SectorDataSourceInterface extends ConfigEntityInterface {

  public function getDescription(): string;

  public function getWeight(): int;

  public function getSectorKey(): string;

  public function getUsageKey(): string;

  public function getProvider(): string;

  public function getWbCountryIso3(): string;

  public function getWbIndicatorPrimary(): string;

  public function getWbIndicatorSecondary(): string;

  public function getWbApiBaseUrl(): string;

  public function getMergeMode(): string;

  public function getValueScale(): string;

  public function getMaxYears(): int;

  public function getCacheMaxAge(): int;

  public function getGastatApiUrl(): string;

}
