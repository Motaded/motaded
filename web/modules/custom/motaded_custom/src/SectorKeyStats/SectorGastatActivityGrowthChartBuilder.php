<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\SectorKeyStats;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\motaded_custom\SectorIndicators\GastatStatisticsClient;
use Drupal\motaded_custom\SectorIndicators\SectorIndicatorDefinitions;
use Drupal\motaded_sector_data\Entity\SectorDataSourceInterface;

/**
 * GASTAT YoY GDP growth (%) bar chart per economic activity — optional sector_data_source.
 */
final class SectorGastatActivityGrowthChartBuilder {

  /** Primary usage key in motaded_sector_data. */
  public const USAGE_KEY = 'sector_gastat_activity_growth';

  /** Legacy config row (logistics only). */
  private const LEGACY_USAGE_KEY = 'logistics_gdp_share_chart';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly GastatStatisticsClient $gastat,
  ) {}

  /**
   * @return array{
   *   encoded: string,
   *   cache_tags: string[],
   *   cache_max_age: int
   * }|null
   */
  public function build(string $sectorKey): ?array {
    $patterns = SectorIndicatorDefinitions::sectorGastatChartMatch();
    if (!isset($patterns[$sectorKey])) {
      return NULL;
    }
    $match = $patterns[$sectorKey];
    $defaultUrl = SectorIndicatorDefinitions::GASTAT_API_URL;
    $url = $defaultUrl;
    $cacheTags = ['sector_indicators'];
    $cacheMaxAge = 43200;

    $entity = $this->loadSourceConfig($sectorKey);
    if ($entity instanceof SectorDataSourceInterface && !$entity->status()) {
      return NULL;
    }
    if ($entity instanceof SectorDataSourceInterface && $entity->status() && $entity->getProvider() === 'gastat') {
      $override = $entity->getGastatApiUrl();
      if ($override !== '') {
        $url = $override;
      }
      $cacheMaxAge = $entity->getCacheMaxAge();
      $cacheTags = $entity->getCacheTagsToInvalidate();
    }

    $series = $this->gastat->fetchSeriesForGastatMatch($match, $url);
    if ($series === []) {
      return NULL;
    }
    if (count($series) > 18) {
      $series = array_values(array_slice($series, -18));
    }
    return [
      'encoded' => Json::encode(['series' => $series]),
      'cache_tags' => $cacheTags,
      'cache_max_age' => $cacheMaxAge,
    ];
  }

  private function loadSourceConfig(string $sectorKey): ?SectorDataSourceInterface {
    if (!$this->entityTypeManager->hasDefinition('sector_data_source')) {
      return NULL;
    }
    $storage = $this->entityTypeManager->getStorage('sector_data_source');
    $usageKeys = [self::USAGE_KEY];
    if ($sectorKey === 'logistics') {
      $usageKeys[] = self::LEGACY_USAGE_KEY;
    }
    foreach ($usageKeys as $usageKey) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('sector_key', $sectorKey)
        ->condition('usage_key', $usageKey)
        ->sort('weight')
        ->sort('id')
        ->range(0, 1)
        ->execute();
      if ($ids === []) {
        continue;
      }
      $id = reset($ids);
      $entity = $storage->load($id);
      if ($entity instanceof SectorDataSourceInterface) {
        return $entity;
      }
    }
    return NULL;
  }

}
