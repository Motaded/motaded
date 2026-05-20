<?php

declare(strict_types=1);

namespace Drupal\motaded_hreflang\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings for hreflang processing and manual overrides.
 */
final class MotadedHreflangSettingsForm extends ConfigFormBase {

  private const ROWS = 15;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'motaded_hreflang_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['motaded_hreflang.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('motaded_hreflang.settings');

    $form['policy'] = [
      '#type' => 'radios',
      '#title' => $this->t('When any hreflang fails validation'),
      '#options' => [
        'per_language' => $this->t('Remove only the failing alternate links'),
        'all_or_nothing' => $this->t('Remove all alternate/hreflang links on this page'),
      ],
      '#default_value' => $config->get('policy') ?: 'per_language',
      '#required' => TRUE,
    ];

    $form['strip_if_redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove alternates whose href matches a Redirect module source (301/302 source path)'),
      '#default_value' => (bool) $config->get('strip_if_redirect'),
    ];

    $form['strip_if_missing_translation'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('On node canonical routes, remove alternates for languages that have no published translation'),
      '#default_value' => (bool) $config->get('strip_if_missing_translation'),
    ];

    $form['parse_htaccess_redirects'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Also treat Apache Redirect rules in web/.htaccess as redirect sources'),
      '#description' => $this->t('Reads simple <code>Redirect 301 /path …</code> lines (not RedirectMatch). Lets hreflang logic match production .htaccess redirects without duplicating them in the Redirect module. Use hook <code>hook_motaded_hreflang_htaccess_redirect_map_alter()</code> to add or override source → target mappings.'),
      '#default_value' => $config->get('parse_htaccess_redirects') !== FALSE,
    ];

    $form['overrides_help'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('When the current page matches <em>Match path</em>, apply overrides. Leave an hreflang field empty to strip that alternate for this page. Absolute (https://…) or site-relative paths (/path) are accepted.') . '</p>',
    ];

    $overrides = $config->get('overrides') ?: [];
    $form['rows'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Manual overrides'),
      '#tree' => TRUE,
    ];

    for ($i = 0; $i < self::ROWS; $i++) {
      $row = $overrides[$i] ?? [];
      $form['rows'][$i] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Rule @n', ['@n' => $i + 1]),
      ];
      $form['rows'][$i]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label (internal note)'),
        '#default_value' => $row['label'] ?? '',
      ];
      $form['rows'][$i]['match_path'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Match path'),
        '#default_value' => $row['match_path'] ?? '',
        '#description' => $this->t('Alias or system path, e.g. /blog/my-post or /node/123'),
      ];
      $form['rows'][$i]['match_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Match type'),
        '#options' => [
          'exact' => $this->t('Exact (normalized internal path)'),
          'prefix' => $this->t('Prefix'),
        ],
        '#default_value' => $row['match_type'] ?? 'exact',
      ];
      foreach (['en' => 'English', 'ar' => 'Arabic', 'x_default' => 'x-default'] as $suffix => $lbl) {
        $key = $suffix === 'x_default' ? 'hreflang_x_default' : 'hreflang_' . $suffix;
        $form['rows'][$i][$key] = [
          '#type' => 'textfield',
          '#title' => $this->t('@lang URL', ['@lang' => $lbl]),
          '#default_value' => $row[$key] ?? '',
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $values = $form_state->getValues();
    $policy = $values['policy'] ?? 'per_language';
    if (!in_array($policy, ['per_language', 'all_or_nothing'], TRUE)) {
      $policy = 'per_language';
    }

    $overrides = [];
    $rows = $values['rows'] ?? [];
    foreach ($rows as $row) {
      if (!is_array($row)) {
        continue;
      }
      $match = isset($row['match_path']) ? trim((string) $row['match_path']) : '';
      if ($match === '') {
        continue;
      }
      $overrides[] = [
        'label' => isset($row['label']) ? trim((string) $row['label']) : '',
        'match_path' => $match,
        'match_type' => ($row['match_type'] ?? 'exact') === 'prefix' ? 'prefix' : 'exact',
        'hreflang_en' => isset($row['hreflang_en']) ? trim((string) $row['hreflang_en']) : '',
        'hreflang_ar' => isset($row['hreflang_ar']) ? trim((string) $row['hreflang_ar']) : '',
        'hreflang_x_default' => isset($row['hreflang_x_default']) ? trim((string) $row['hreflang_x_default']) : '',
      ];
    }

    $this->config('motaded_hreflang.settings')
      ->set('policy', $policy)
      ->set('strip_if_redirect', !empty($values['strip_if_redirect']))
      ->set('strip_if_missing_translation', !empty($values['strip_if_missing_translation']))
      ->set('parse_htaccess_redirects', !empty($values['parse_htaccess_redirects']))
      ->set('overrides', $overrides)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
