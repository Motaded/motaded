<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * CRM lead webhook settings (Contact us + setup cost calculator).
 */
final class CrmWebhookSettingsForm extends ConfigFormBase {

  private const PRIORITY_OPTIONS = [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
  ];

  protected function getEditableConfigNames(): array {
    return ['motaded_custom.crm_webhook'];
  }

  public function getFormId(): string {
    return 'motaded_custom_crm_webhook_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('motaded_custom.crm_webhook');
    $slugs = $config->get('lead_service_slug') ?: [];

    $form['intro'] = [
      '#type' => 'item',
      '#markup' => $this->t('Forwards completed submissions from the <strong>Contact us</strong> and <strong>Setup cost estimate lead</strong> webforms to your CRM webhook API.'),
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable CRM webhook'),
      '#default_value' => (bool) $config->get('enabled'),
    ];

    $form['connection'] = [
      '#type' => 'details',
      '#title' => $this->t('API connection'),
      '#open' => TRUE,
    ];

    $form['connection']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Webhook URL'),
      '#default_value' => $config->get('url') ?? '',
      '#description' => $this->t('Production example: @prod. Local CRM on the same machine: @local (Drupal and CRM both on the host). DDEV/Docker only: @docker (CRM on the host, Drupal in a container).', [
        '@prod' => 'https://crm.example.com/api/webhooks/leads',
        '@local' => 'http://127.0.0.1:8000/api/webhooks/leads',
        '@docker' => 'http://host.docker.internal:8000/api/webhooks/leads',
      ]),
      '#maxlength' => 2048,
      '#required' => FALSE,
    ];

    $hasToken = is_string($config->get('token')) && $config->get('token') !== '';
    $form['connection']['token'] = [
      '#type' => 'password',
      '#title' => $this->t('Bearer token'),
      '#description' => $hasToken
        ? $this->t('A token is saved. Leave blank to keep the current token.')
        : $this->t('Sent as Authorization: Bearer … on each request.'),
      '#size' => 60,
      '#maxlength' => 512,
    ];

    $form['connection']['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Request timeout (seconds)'),
      '#default_value' => $config->get('timeout') ?? 10,
      '#min' => 1,
      '#max' => 120,
      '#step' => 1,
    ];

    $form['defaults'] = [
      '#type' => 'details',
      '#title' => $this->t('Lead defaults'),
      '#open' => TRUE,
    ];

    $form['defaults']['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default country code'),
      '#default_value' => $config->get('country') ?? 'SA',
      '#size' => 4,
      '#maxlength' => 2,
      '#description' => $this->t('ISO 3166-1 alpha-2, e.g. SA.'),
    ];

    $form['defaults']['priority_slug'] = [
      '#type' => 'select',
      '#title' => $this->t('Default priority'),
      '#options' => self::PRIORITY_OPTIONS,
      '#default_value' => $config->get('priority_slug') ?? 'medium',
    ];

    $form['slugs'] = [
      '#type' => 'details',
      '#title' => $this->t('Lead service slugs'),
      '#description' => $this->t('CRM identifiers for each webform source.'),
      '#open' => TRUE,
    ];

    $form['slugs']['lead_service_slug_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Contact us'),
      '#default_value' => $slugs['contact'] ?? 'contact-us',
      '#required' => TRUE,
      '#pattern' => '[a-z0-9\-]+',
    ];

    $form['slugs']['lead_service_slug_calculator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Setup cost calculator'),
      '#default_value' => $slugs['setup_cost_estimate_lead'] ?? 'company-setup',
      '#required' => TRUE,
      '#pattern' => '[a-z0-9\-]+',
    ];

    $form['status'] = [
      '#type' => 'item',
      '#title' => $this->t('Status'),
      '#markup' => $this->buildStatusMarkup($config->getRawData()),
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    if (!$form_state->getValue('enabled')) {
      return;
    }

    $url = trim((string) $form_state->getValue('url'));
    if ($url === '') {
      $form_state->setErrorByName('url', $this->t('Webhook URL is required when integration is enabled.'));
    }

    $config = $this->config('motaded_custom.crm_webhook');
    $token = trim((string) $form_state->getValue('token'));
    $hasStoredToken = is_string($config->get('token')) && $config->get('token') !== '';
    if ($token === '' && !$hasStoredToken) {
      $form_state->setErrorByName('token', $this->t('Bearer token is required when integration is enabled.'));
    }

    $country = strtoupper(trim((string) $form_state->getValue('country')));
    if (!preg_match('/^[A-Z]{2}$/', $country)) {
      $form_state->setErrorByName('country', $this->t('Enter a valid 2-letter country code.'));
    }

    foreach (['lead_service_slug_contact', 'lead_service_slug_calculator'] as $field) {
      $slug = trim((string) $form_state->getValue($field));
      if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
        $form_state->setErrorByName($field, $this->t('Use lowercase letters, numbers, and hyphens only.'));
      }
    }

    if (getenv('IS_DDEV_PROJECT') === 'true' && $form_state->getValue('enabled')) {
      $url = trim((string) $form_state->getValue('url'));
      if (preg_match('#^https?://(127\.0\.0\.1|localhost)([:/]|$)#i', $url)) {
        $this->messenger()->addWarning($this->t('Inside DDEV, use @host instead of 127.0.0.1/localhost so the container can reach CRM on your machine. (Requests are auto-adjusted if you keep 127.0.0.1.)', [
          '@host' => 'http://host.docker.internal:8000/api/webhooks/leads',
        ]));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('motaded_custom.crm_webhook');
    $token = trim((string) $form_state->getValue('token'));

    $config
      ->set('enabled', (bool) $form_state->getValue('enabled'))
      ->set('url', trim((string) $form_state->getValue('url')))
      ->set('timeout', (int) $form_state->getValue('timeout'))
      ->set('country', strtoupper(trim((string) $form_state->getValue('country'))))
      ->set('priority_slug', $form_state->getValue('priority_slug'))
      ->set('lead_service_slug', [
        'contact' => trim((string) $form_state->getValue('lead_service_slug_contact')),
        'setup_cost_estimate_lead' => trim((string) $form_state->getValue('lead_service_slug_calculator')),
      ]);

    if ($token !== '') {
      $config->set('token', $token);
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * @param array<string, mixed> $config
   */
  private function buildStatusMarkup(array $config): string {
    $enabled = !empty($config['enabled'])
      && is_string($config['url'] ?? NULL) && $config['url'] !== ''
      && is_string($config['token'] ?? NULL) && $config['token'] !== '';

    if ($enabled) {
      return (string) $this->t('Ready — new webform leads will be POSTed to the webhook URL.');
    }

    $missing = [];
    if (empty($config['enabled'])) {
      $missing[] = (string) $this->t('integration disabled');
    }
    if (empty($config['url'])) {
      $missing[] = (string) $this->t('URL missing');
    }
    if (empty($config['token'])) {
      $missing[] = (string) $this->t('token missing');
    }

    return (string) $this->t('Not active (@issues).', [
      '@issues' => implode(', ', $missing) ?: $this->t('check settings'),
    ]);
  }

}
