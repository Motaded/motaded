<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Configurable setup cost estimator profile.
 */
#[ConfigEntityType(
  id: 'setup_cost_profile',
  label: new TranslatableMarkup('Setup cost profile'),
  label_collection: new TranslatableMarkup('Setup cost profiles'),
  label_singular: new TranslatableMarkup('setup cost profile'),
  label_plural: new TranslatableMarkup('setup cost profiles'),
  handlers: [
    'list_builder' => 'Drupal\motaded_setup_cost\SetupCostProfileListBuilder',
    'form' => [
      'add' => 'Drupal\motaded_setup_cost\Form\SetupCostProfileForm',
      'edit' => 'Drupal\motaded_setup_cost\Form\SetupCostProfileForm',
      'delete' => 'Drupal\Core\Entity\EntityDeleteForm',
    ],
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
  ],
  admin_permission: 'administer setup cost profiles',
  config_prefix: 'profile',
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
    'currency',
    'webform_id',
    'global_setup_buffer_min',
    'global_setup_buffer_max',
    'disclaimer',
    'last_updated',
    'methodology_source',
    'steps',
    'line_items',
    'modifiers',
    'monthly',
    'timelines',
    'timeline_default',
    'packages',
    'ui',
  ],
  links: [
    'add-form' => '/admin/config/motaded/setup-cost/add',
    'edit-form' => '/admin/config/motaded/setup-cost/{setup_cost_profile}',
    'delete-form' => '/admin/config/motaded/setup-cost/{setup_cost_profile}/delete',
    'collection' => '/admin/config/motaded/setup-cost',
  ],
)]
final class SetupCostProfile extends ConfigEntityBase implements SetupCostProfileInterface {

  protected string $currency = 'SAR';

  protected string $webform_id = '';

  protected float $global_setup_buffer_min = 1.0;

  protected float $global_setup_buffer_max = 1.0;

  protected string $disclaimer = '';

  protected string $last_updated = '';

  protected string $methodology_source = '';

  /** @var array<int, array<string, mixed>> */
  protected array $steps = [];

  /** @var array<int, array<string, mixed>> */
  protected array $line_items = [];

  /** @var array<int, array<string, mixed>> */
  protected array $modifiers = [];

  /** @var array<string, mixed> */
  protected array $monthly = [];

  /** @var array<int, array<string, mixed>> */
  protected array $timelines = [];

  /** @var array{min_weeks?: int, max_weeks?: int} */
  protected array $timeline_default = ['min_weeks' => 2, 'max_weeks' => 6];

  /** @var array<int, array<string, mixed>> */
  protected array $packages = [];

  /** @var array<string, mixed> */
  protected array $ui = [];

  public function getCurrency(): string {
    return $this->currency;
  }

  public function getWebformId(): string {
    return $this->webform_id;
  }

  public function getGlobalSetupBufferMin(): float {
    return (float) $this->global_setup_buffer_min;
  }

  public function getGlobalSetupBufferMax(): float {
    return (float) $this->global_setup_buffer_max;
  }

  public function getDisclaimer(): string {
    return $this->disclaimer;
  }

  public function getLastUpdated(): string {
    return $this->last_updated;
  }

  public function getMethodologySource(): string {
    return $this->methodology_source;
  }

  public function getSteps(): array {
    return $this->steps;
  }

  public function getLineItems(): array {
    return $this->line_items;
  }

  public function getModifiers(): array {
    return $this->modifiers;
  }

  public function getMonthly(): array {
    return $this->monthly;
  }

  public function getTimelines(): array {
    return $this->timelines;
  }

  public function getTimelineDefault(): array {
    return [
      'min_weeks' => (int) ($this->timeline_default['min_weeks'] ?? 2),
      'max_weeks' => (int) ($this->timeline_default['max_weeks'] ?? 6),
    ];
  }

  public function getPackages(): array {
    return $this->packages;
  }

  public function getUi(): array {
    return $this->ui;
  }

  public function toCalculatorArray(): array {
    $steps = $this->getSteps();
    usort($steps, static fn(array $a, array $b): int => ((int) ($a['weight'] ?? 0)) <=> ((int) ($b['weight'] ?? 0)));
    foreach ($steps as &$step) {
      if (!empty($step['options']) && is_array($step['options'])) {
        usort($step['options'], static fn(array $a, array $b): int => ((int) ($a['weight'] ?? 0)) <=> ((int) ($b['weight'] ?? 0)));
      }
    }
    unset($step);

    $line_items = $this->getLineItems();
    usort($line_items, static fn(array $a, array $b): int => ((int) ($a['weight'] ?? 0)) <=> ((int) ($b['weight'] ?? 0)));

    $packages = $this->getPackages();
    usort($packages, static fn(array $a, array $b): int => ((int) ($a['min_points'] ?? 0)) <=> ((int) ($b['min_points'] ?? 0)));

    return [
      'id' => $this->id(),
      'currency' => $this->getCurrency(),
      'global_setup_buffer_min' => $this->getGlobalSetupBufferMin(),
      'global_setup_buffer_max' => $this->getGlobalSetupBufferMax(),
      'disclaimer' => $this->getDisclaimer(),
      'last_updated' => $this->getLastUpdated(),
      'methodology_source' => $this->getMethodologySource(),
      'steps' => $steps,
      'line_items' => $line_items,
      'modifiers' => $this->getModifiers(),
      'monthly' => $this->getMonthly(),
      'timelines' => $this->getTimelines(),
      'timeline_default' => $this->getTimelineDefault(),
      'packages' => $packages,
      'ui' => $this->getUi(),
    ];
  }

}
