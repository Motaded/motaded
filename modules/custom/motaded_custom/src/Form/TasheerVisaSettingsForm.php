<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings for Tasheer visa conditions.
 */
final class TasheerVisaSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'motaded_custom_tasheer_visa_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['motaded_custom.tasheer_visa'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('motaded_custom.tasheer_visa');

    $form['meta'] = [
      '#type' => 'details',
      '#title' => $this->t('General settings'),
      '#open' => TRUE,
    ];

    $form['meta']['webform_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Webform machine name'),
      '#default_value' => (string) ($config->get('webform_id') ?: 'tasheer_visa_appointment'),
      '#required' => TRUE,
    ];

    $form['meta']['json_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('JSON fallback path (relative to DRUPAL_ROOT)'),
      '#default_value' => (string) ($config->get('json_path') ?: 'tasheer-analysis/drupal-webform-data.json'),
      '#description' => $this->t('Used when condition maps below are empty.'),
      '#required' => TRUE,
    ];

    $form['meta']['no_options_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('No options message'),
      '#default_value' => (string) ($config->get('messages.no_options') ?: 'No options available'),
      '#required' => TRUE,
    ];

    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Webform options IDs'),
      '#open' => TRUE,
    ];

    $defaults = (array) $config->get('options');
    $form['options']['options_nationality'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nationality options ID'),
      '#default_value' => (string) ($defaults['nationality'] ?? 'tasheer_nationality'),
      '#required' => TRUE,
    ];
    $form['options']['options_visa_type'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Visa Type options ID'),
      '#default_value' => (string) ($defaults['visa_type'] ?? 'tasheer_visa_type'),
      '#required' => TRUE,
    ];
    $form['options']['options_number_of_entries'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of Entries options ID'),
      '#default_value' => (string) ($defaults['number_of_entries'] ?? 'tasheer_number_of_entries'),
      '#required' => TRUE,
    ];
    $form['options']['options_visa_validity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Visa Validity options ID'),
      '#default_value' => (string) ($defaults['visa_validity'] ?? 'tasheer_visa_validity'),
      '#required' => TRUE,
    ];

    $conditions = (array) $config->get('conditions');
    $form['conditions'] = [
      '#type' => 'details',
      '#title' => $this->t('Conditions (YAML)'),
      '#open' => TRUE,
      '#description' => $this->t('Keys and values must be IDs (strings). Leave empty to use JSON fallback.'),
    ];

    $form['conditions']['visa_type_by_nationality'] = [
      '#type' => 'textarea',
      '#title' => $this->t('visa_type_by_nationality'),
      '#rows' => 10,
      '#default_value' => Yaml::encode((array) ($conditions['visa_type_by_nationality'] ?? [])),
    ];
    $form['conditions']['entries_by_nat_visa_type'] = [
      '#type' => 'textarea',
      '#title' => $this->t('entries_by_nat_visa_type'),
      '#rows' => 10,
      '#default_value' => Yaml::encode((array) ($conditions['entries_by_nat_visa_type'] ?? [])),
    ];
    $form['conditions']['validity_by_nat_visa_type_entry'] = [
      '#type' => 'textarea',
      '#title' => $this->t('validity_by_nat_visa_type_entry'),
      '#rows' => 10,
      '#default_value' => Yaml::encode((array) ($conditions['validity_by_nat_visa_type_entry'] ?? [])),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    foreach (['visa_type_by_nationality', 'entries_by_nat_visa_type', 'validity_by_nat_visa_type_entry'] as $field) {
      $raw = trim((string) $form_state->getValue($field));
      if ($raw === '') {
        continue;
      }
      try {
        $decoded = Yaml::decode($raw);
      }
      catch (\Throwable) {
        $decoded = NULL;
      }
      if (!is_array($decoded)) {
        $form_state->setErrorByName($field, $this->t('Invalid YAML for %name.', ['%name' => $field]));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $decode = static function (string $input): array {
      $input = trim($input);
      if ($input === '') {
        return [];
      }
      $decoded = Yaml::decode($input);
      return is_array($decoded) ? $decoded : [];
    };

    $this->configFactory->getEditable('motaded_custom.tasheer_visa')
      ->set('webform_id', (string) $form_state->getValue('webform_id'))
      ->set('json_path', trim((string) $form_state->getValue('json_path'), '/'))
      ->set('messages.no_options', (string) $form_state->getValue('no_options_message'))
      ->set('options.nationality', (string) $form_state->getValue('options_nationality'))
      ->set('options.visa_type', (string) $form_state->getValue('options_visa_type'))
      ->set('options.number_of_entries', (string) $form_state->getValue('options_number_of_entries'))
      ->set('options.visa_validity', (string) $form_state->getValue('options_visa_validity'))
      ->set('conditions.visa_type_by_nationality', $decode((string) $form_state->getValue('visa_type_by_nationality')))
      ->set('conditions.entries_by_nat_visa_type', $decode((string) $form_state->getValue('entries_by_nat_visa_type')))
      ->set('conditions.validity_by_nat_visa_type_entry', $decode((string) $form_state->getValue('validity_by_nat_visa_type_entry')))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
