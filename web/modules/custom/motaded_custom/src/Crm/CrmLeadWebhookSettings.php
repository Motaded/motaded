<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Crm;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;

/**
 * CRM webhook configuration (admin config + optional $settings override).
 */
final class CrmLeadWebhookSettings {

  /**
   * Webform IDs that forward leads to the CRM webhook.
   */
  public const LEAD_WEBFORMS = [
    'contact',
    'setup_cost_estimate_lead',
  ];

  /**
   * @param array<string, mixed> $config
   *   Raw settings array.
   */
  public function __construct(
    private readonly array $config,
  ) {}

  /**
   * Factory callback for the service container.
   */
  public static function create(ConfigFactoryInterface $configFactory, Settings $settings): self {
    $config = $configFactory->get('motaded_custom.crm_webhook')->get();
    $config = is_array($config) ? $config : [];

    $override = $settings->get('motaded_crm_webhook', []);
    if (is_array($override) && $override !== []) {
      $config = array_replace_recursive($config, $override);
    }

    return new self($config);
  }

  public function isEnabled(): bool {
    return !empty($this->config['enabled'])
      && is_string($this->config['url'] ?? NULL)
      && $this->config['url'] !== ''
      && is_string($this->config['token'] ?? NULL)
      && $this->config['token'] !== '';
  }

  public function getUrl(): string {
    return (string) ($this->config['url'] ?? '');
  }

  public function getToken(): string {
    return (string) ($this->config['token'] ?? '');
  }

  public function getDefaultCountry(): string {
    $country = $this->config['country'] ?? 'SA';
    return is_string($country) && $country !== '' ? strtoupper($country) : 'SA';
  }

  public function getDefaultPrioritySlug(): string {
    $priority = $this->config['priority_slug'] ?? 'medium';
    return is_string($priority) && $priority !== '' ? $priority : 'medium';
  }

  /**
   * Maps webform machine name to CRM lead_service_slug.
   */
  public function getLeadServiceSlug(string $webformId): string {
    $map = $this->config['lead_service_slug'] ?? [];
    if (is_array($map) && isset($map[$webformId]) && is_string($map[$webformId]) && $map[$webformId] !== '') {
      return $map[$webformId];
    }
    return match ($webformId) {
      'contact' => 'contact-us',
      'setup_cost_estimate_lead' => 'company-setup',
      default => 'website',
    };
  }

  /**
   * HTTP request timeout in seconds.
   */
  public function getTimeout(): float {
    $timeout = $this->config['timeout'] ?? 10.0;
    return is_numeric($timeout) && $timeout > 0 ? (float) $timeout : 10.0;
  }

}
