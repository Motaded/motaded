<?php

declare(strict_types=1);

namespace Drupal\motaded_sector_data\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\motaded_sector_data\Entity\SectorDataSource;

/**
 * Add / edit sector data source.
 */
final class SectorDataSourceForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\motaded_sector_data\Entity\SectorDataSourceInterface $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative title'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#description' => $this->t('Shown in the admin list only.'),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [SectorDataSource::class, 'exists'],
        'source' => ['label'],
      ],
      '#disabled' => !$entity->isNew(),
      '#description' => $this->t('Unique ID in configuration. Used in cache tags and code references (e.g. trade_volume_chart consumer).'),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $entity->status(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $entity->getDescription(),
      '#rows' => 4,
      '#description' => $this->t('Document for editors: what this integration shows, caveats, and links to methodology.'),
    ];

    $form['weight'] = [
      '#type' => 'weight',
      '#title' => $this->t('Weight'),
      '#default_value' => $entity->getWeight(),
      '#delta' => 50,
      '#description' => $this->t('When several sources match the same sector + usage, the lowest weight wins.'),
    ];

    $form['placement'] = [
      '#type' => 'details',
      '#title' => $this->t('Where it is used'),
      '#open' => TRUE,
    ];
    $form['placement']['sector_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sector key'),
      '#default_value' => $entity->getSectorKey(),
      '#required' => TRUE,
      '#maxlength' => 64,
      '#description' => $this->t('Canonical sector id from site logic (e.g. <code>logistics</code>, <code>tourism</code>). Must match the value returned for the sector page node.'),
    ];
    $form['placement']['usage_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Usage key'),
      '#default_value' => $entity->getUsageKey(),
      '#required' => TRUE,
      '#maxlength' => 128,
      '#description' => $this->t('Consumer identifier in code. Known keys: <code>trade_volume_chart</code> (World Bank trade volume), <code>sector_gastat_activity_growth</code> (GASTAT YoY GDP growth by activity), <code>logistics_gdp_share_chart</code> (legacy logistics alias). More keys can be added as features ship.'),
    ];

    $form['provider'] = [
      '#type' => 'select',
      '#title' => $this->t('API provider'),
      '#options' => [
        'worldbank' => $this->t('World Bank (WDI API v2)'),
        'gastat' => $this->t('GASTAT (reserved — import / UI wiring next)'),
        'none' => $this->t('None (placeholder / documentation only)'),
      ],
      '#default_value' => $entity->getProvider(),
      '#required' => TRUE,
      '#weight' => 12,
    ];

    $form['wb'] = [
      '#type' => 'details',
      '#title' => $this->t('World Bank parameters'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="provider"]' => ['value' => 'worldbank'],
        ],
      ],
    ];
    $form['wb']['wb_country_iso3'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country ISO3 code'),
      '#size' => 6,
      '#maxlength' => 3,
      '#default_value' => $entity->getWbCountryIso3(),
      '#description' => $this->t('Three-letter code (e.g. SAU). The fetcher in <code>motaded_custom</code> currently requests Saudi Arabia; this field is stored for future multi-country support.'),
    ];
    $form['wb']['wb_indicator_primary'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary WDI indicator code'),
      '#default_value' => $entity->getWbIndicatorPrimary(),
      '#maxlength' => 32,
      '#description' => $this->t('Example merchandise exports: <code>TX.VAL.MRCH.CD.WT</code>. Full URL pattern: <code>https://api.worldbank.org/v2/country/SAU/indicator/{code}?format=json</code>'),
    ];
    $form['wb']['wb_indicator_secondary'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secondary WDI indicator code (optional)'),
      '#default_value' => $entity->getWbIndicatorSecondary(),
      '#maxlength' => 32,
      '#description' => $this->t('Used when merge mode is “Sum two series by year” (e.g. imports <code>TM.VAL.MRCH.CD.WT</code>).'),
    ];
    $form['wb']['wb_api_base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Optional API base URL override'),
      '#default_value' => $entity->getWbApiBaseUrl(),
      '#maxlength' => 512,
      '#description' => $this->t('Leave empty to use the default World Bank v2 base URL in code. Override only if you need a mirror or staging endpoint.'),
    ];

    $form['gastat'] = [
      '#type' => 'details',
      '#title' => $this->t('GASTAT parameters'),
      '#open' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="provider"]' => ['value' => 'gastat'],
        ],
      ],
    ];
    $form['gastat']['gastat_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GASTAT API URL'),
      '#default_value' => $entity->getGastatApiUrl(),
      '#maxlength' => 1024,
      '#description' => $this->t('Full JSON endpoint URL. Wired for imports / charts in follow-up tasks.'),
    ];

    $form['processing'] = [
      '#type' => 'details',
      '#title' => $this->t('Merge & value scaling'),
      '#open' => TRUE,
    ];
    $form['processing']['merge_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Merge mode'),
      '#options' => [
        'none' => $this->t('None — single primary series only'),
        'sum_series_by_year' => $this->t('Sum primary + secondary by matching year'),
      ],
      '#default_value' => $entity->getMergeMode(),
      '#description' => $this->t('For trade volume: choose “Sum…” and set exports + imports codes.'),
    ];
    $form['processing']['value_scale'] = [
      '#type' => 'select',
      '#title' => $this->t('Value scale (display)'),
      '#options' => [
        'none' => $this->t('Raw API values'),
        'divide_1e9' => $this->t('Divide by 10⁹ (USD billions, rounded integer for chart)'),
      ],
      '#default_value' => $entity->getValueScale(),
    ];
    $form['processing']['max_years'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum years on chart'),
      '#min' => 1,
      '#max' => 60,
      '#default_value' => $entity->getMaxYears(),
    ];

    $form['caching'] = [
      '#type' => 'details',
      '#title' => $this->t('Caching'),
      '#open' => FALSE,
    ];
    $form['caching']['cache_max_age'] = [
      '#type' => 'number',
      '#title' => $this->t('Page cache max-age (seconds)'),
      '#min' => 60,
      '#max' => 864000,
      '#default_value' => $entity->getCacheMaxAge(),
      '#description' => $this->t('Merged into the sector page render cache when this source supplies data (minimum 60).'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $provider = (string) $form_state->getValue('provider');
    $wb = $form_state->getValue('wb');
    $wb = is_array($wb) ? $wb : [];
    $processing = $form_state->getValue('processing');
    $processing = is_array($processing) ? $processing : [];
    $p1 = trim((string) ($wb['wb_indicator_primary'] ?? ''));
    $p2 = trim((string) ($wb['wb_indicator_secondary'] ?? ''));
    $merge = (string) ($processing['merge_mode'] ?? '');

    if ($provider === 'gastat') {
      $gastat = $form_state->getValue('gastat');
      $gastat = is_array($gastat) ? $gastat : [];
      $gUrl = trim((string) ($gastat['gastat_api_url'] ?? ''));
      if ($gUrl !== '' && filter_var($gUrl, FILTER_VALIDATE_URL) === FALSE) {
        $form_state->setErrorByName('gastat][gastat_api_url', $this->t('Enter a valid URL or leave the field empty.'));
      }
    }

    if ($provider === 'worldbank') {
      if ($p1 === '') {
        $form_state->setErrorByName('wb][wb_indicator_primary', $this->t('Primary indicator code is required for World Bank.'));
      }
      if ($merge === 'sum_series_by_year' && $p2 === '') {
        $form_state->setErrorByName('wb][wb_indicator_secondary', $this->t('Secondary indicator code is required when merge mode is “Sum two series by year”.'));

      }
    }
  }

  /**
   * Flattens grouped form values so ConfigEntity receives top-level keys.
   */
  private function flattenGroupedFormValues(FormStateInterface $form_state): void {
    foreach (['placement', 'wb', 'gastat', 'processing', 'caching'] as $group) {
      $chunk = $form_state->getValue($group);
      if (!is_array($chunk)) {
        continue;
      }
      foreach ($chunk as $key => $value) {
        $form_state->setValue($key, $value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->flattenGroupedFormValues($form_state);
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);
    $this->messenger()->addStatus($this->t('Saved sector data source %label.', ['%label' => $this->entity->label()]));
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}
