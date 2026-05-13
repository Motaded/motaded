<?php

declare(strict_types=1);

namespace Drupal\motaded_sector_data\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a sector data source (API mapping).
 */
#[ConfigEntityType(
  id: 'sector_data_source',
  label: new TranslatableMarkup('Sector data source'),
  label_collection: new TranslatableMarkup('Sector data sources'),
  label_singular: new TranslatableMarkup('sector data source'),
  label_plural: new TranslatableMarkup('sector data sources'),
  label_count: [
    'singular' => '@count sector data source',
    'plural' => '@count sector data sources',
  ],
  handlers: [
    'list_builder' => 'Drupal\motaded_sector_data\SectorDataSourceListBuilder',
    'form' => [
      'add' => 'Drupal\motaded_sector_data\Form\SectorDataSourceForm',
      'edit' => 'Drupal\motaded_sector_data\Form\SectorDataSourceForm',
      'delete' => 'Drupal\Core\Entity\EntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
  ],
  admin_permission: 'administer sector data sources',
  config_prefix: 'source',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
    'status' => 'status',
  ],
  config_export: [
    'uuid',
    'langcode',
    'status',
    'dependencies',
    'id',
    'label',
    'description',
    'weight',
    'sector_key',
    'usage_key',
    'provider',
    'wb_country_iso3',
    'wb_indicator_primary',
    'wb_indicator_secondary',
    'wb_api_base_url',
    'merge_mode',
    'value_scale',
    'max_years',
    'cache_max_age',
    'gastat_api_url',
  ],
  links: [
    'add-form' => '/admin/config/motaded/sector-data/add',
    'edit-form' => '/admin/config/motaded/sector-data/manage/{sector_data_source}',
    'delete-form' => '/admin/config/motaded/sector-data/manage/{sector_data_source}/delete',
    'collection' => '/admin/config/motaded/sector-data',
  ],
)]
final class SectorDataSource extends ConfigEntityBase implements SectorDataSourceInterface {

  protected string $description = '';

  protected int $weight = 0;

  protected string $sector_key = '';

  protected string $usage_key = '';

  /** worldbank | gastat | none */
  protected string $provider = 'none';

  protected string $wb_country_iso3 = 'SAU';

  protected string $wb_indicator_primary = '';

  protected string $wb_indicator_secondary = '';

  protected string $wb_api_base_url = '';

  /** none | sum_series_by_year */
  protected string $merge_mode = 'none';

  /** none | divide_1e9 */
  protected string $value_scale = 'none';

  protected int $max_years = 24;

  protected int $cache_max_age = 43200;

  protected string $gastat_api_url = '';

  public function getDescription(): string {
    return $this->description;
  }

  public function getWeight(): int {
    return (int) $this->weight;
  }

  public function getSectorKey(): string {
    return $this->sector_key;
  }

  public function getUsageKey(): string {
    return $this->usage_key;
  }

  public function getProvider(): string {
    return $this->provider;
  }

  public function getWbCountryIso3(): string {
    return strtoupper(trim($this->wb_country_iso3)) ?: 'SAU';
  }

  public function getWbIndicatorPrimary(): string {
    return trim($this->wb_indicator_primary);
  }

  public function getWbIndicatorSecondary(): string {
    return trim($this->wb_indicator_secondary);
  }

  public function getWbApiBaseUrl(): string {
    return trim($this->wb_api_base_url);
  }

  public function getMergeMode(): string {
    return $this->merge_mode;
  }

  public function getValueScale(): string {
    return $this->value_scale;
  }

  public function getMaxYears(): int {
    return max(1, (int) $this->max_years);
  }

  public function getCacheMaxAge(): int {
    return max(60, (int) $this->cache_max_age);
  }

  public function getGastatApiUrl(): string {
    return trim($this->gastat_api_url);
  }

  public static function exists(string $id, array $element, FormStateInterface $form_state): bool {
    return (bool) \Drupal::entityTypeManager()->getStorage('sector_data_source')->load($id);
  }

  /**
   * {@inheritdoc}
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    $wa = $a instanceof self ? $a->getWeight() : 0;
    $wb = $b instanceof self ? $b->getWeight() : 0;
    if ($wa !== $wb) {
      return $wa <=> $wb;
    }
    return parent::sort($a, $b);
  }

}
