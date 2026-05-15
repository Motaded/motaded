<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\motaded_setup_cost\Calculator\SetupCostCalculator;
use Drupal\motaded_setup_cost\Entity\SetupCostProfileInterface;

/**
 * Loads active profile and builds frontend / API payloads.
 */
final class SetupCostDataBuilder {

  public function __construct(
    private readonly SetupCostCalculator $calculator,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly LanguageManagerInterface $languageManager,
  ) {}

  public function getActiveProfile(): ?SetupCostProfileInterface {
    $id = (string) ($this->configFactory->get('motaded_setup_cost.settings')->get('active_profile') ?? 'default');
    $storage = $this->entityTypeManager->getStorage('setup_cost_profile');
    $this->applyConfigLanguageOverride();
    $storage->resetCache([$id]);
    $entity = $storage->load($id);
    if ($entity instanceof SetupCostProfileInterface && $entity->status()) {
      return $entity;
    }
    foreach ($storage->loadMultiple() as $candidate) {
      if ($candidate instanceof SetupCostProfileInterface && $candidate->status()) {
        return $candidate;
      }
    }
    return NULL;
  }

  /**
   * @return array<string, mixed>|null
   */
  public function buildFrontendConfig(?string $profileId = NULL): ?array {
    $storage = $this->entityTypeManager->getStorage('setup_cost_profile');
    $id = $profileId ?? (string) ($this->configFactory->get('motaded_setup_cost.settings')->get('active_profile') ?? 'default');
    $this->applyConfigLanguageOverride();
    $storage->resetCache([$id]);
    $profile = $storage->load($id);
    if (!$profile instanceof SetupCostProfileInterface || !$profile->status()) {
      return NULL;
    }

    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $data = $profile->toCalculatorArray();
    $data['langcode'] = $langcode;
    $data['webform_id'] = $profile->getWebformId();
    $data['profile_id'] = $profile->id();

    return $data;
  }

  /**
   * @param array<string, mixed> $answers
   *
   * @return array<string, mixed>|null
   */
  public function calculate(?string $profileId, array $answers): ?array {
    $config = $this->buildFrontendConfig($profileId);
    if ($config === NULL) {
      return NULL;
    }
    return $this->calculator->calculate($config, $answers);
  }

  /**
   * Applies interface-language config overrides (e.g. AR profile labels).
   */
  private function applyConfigLanguageOverride(): void {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    if ($langcode === 'en') {
      return;
    }
    $language = $this->languageManager->getLanguage($langcode);
    if ($language !== NULL) {
      $this->languageManager->setConfigOverrideLanguage($language);
    }
  }

}
