<?php

namespace Drupal\email_verification\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure which webforms require email OTP before submit.
 */
class EmailVerificationSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_verification_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['email_verification.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('email_verification.settings');
    $webforms = $config->get('webforms') ?: [];

    $ids = array_keys($webforms);
    $email_element = 'email';
    if ($webforms !== []) {
      $first = reset($webforms);
      if (is_array($first) && isset($first['email_element'])) {
        $email_element = $first['email_element'];
      }
    }

    $form['webform_ids'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Webform machine names'),
      '#description' => $this->t('One per line. These forms will require a one-time code sent to the email field before the submission is accepted.'),
      '#default_value' => implode("\n", $ids),
      '#rows' => 6,
      '#required' => FALSE,
    ];

    $form['email_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email element key'),
      '#description' => $this->t('The machine name of the email element (same for all webforms listed above), e.g. %example.', ['%example' => 'email']),
      '#default_value' => $email_element,
      '#required' => TRUE,
      '#size' => 40,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $lines = preg_split('/\R/', (string) $form_state->getValue('webform_ids')) ?: [];
    $seen = [];
    foreach ($lines as $line) {
      $id = trim($line);
      if ($id === '') {
        continue;
      }
      if (!preg_match('/^[a-z0-9_]+$/', $id)) {
        $form_state->setErrorByName('webform_ids', $this->t('Invalid webform machine name: %id. Use only lowercase letters, numbers, and underscores.', ['%id' => $id]));
        return;
      }
      if (isset($seen[$id])) {
        $form_state->setErrorByName('webform_ids', $this->t('Duplicate webform: %id.', ['%id' => $id]));
        return;
      }
      $seen[$id] = TRUE;
    }

    $key = trim((string) $form_state->getValue('email_element'));
    if ($key === '' || !preg_match('/^[a-z0-9_]+$/', $key)) {
      $form_state->setErrorByName('email_element', $this->t('Enter a valid element machine name (e.g. email).'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $lines = preg_split('/\R/', (string) $form_state->getValue('webform_ids')) ?: [];
    $email_element = trim((string) $form_state->getValue('email_element'));

    $webforms = [];
    foreach ($lines as $line) {
      $id = trim($line);
      if ($id === '') {
        continue;
      }
      $webforms[$id] = ['email_element' => $email_element];
    }

    $this->config('email_verification.settings')
      ->set('webforms', $webforms)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
