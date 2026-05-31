<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Crm;

use Drupal\webform\WebformSubmissionInterface;

/**
 * Maps Webform submissions to CRM webhook JSON payloads.
 */
final class CrmLeadPayloadBuilder {

  private const CONTACT_CATEGORY_LABELS = [
    'suggestion' => 'Suggestion',
    'general' => 'General Inquiry',
    'support' => 'Support',
    'complaint' => 'Complaint',
  ];

  private const CALCULATOR_ACTIVITY_LABELS = [
    'it' => 'IT / Software',
    'trading' => 'Trading',
    'consulting' => 'Consulting',
    'industrial' => 'Industrial',
    'ecommerce' => 'E-commerce',
    'marketing' => 'Marketing',
    'logistics' => 'Logistics',
    'other' => 'Other',
  ];

  public function __construct(
    private readonly CrmLeadAttributionCollector $attributionCollector,
    private readonly CrmLeadWebhookSettings $settings,
  ) {}

  /**
   * @return array<string, mixed>|null
   *   Payload or NULL when the webform is not supported.
   */
  public function build(WebformSubmissionInterface $submission): ?array {
    $webformId = $submission->getWebform()->id();
    return match ($webformId) {
      'contact' => $this->buildContactPayload($submission),
      'setup_cost_estimate_lead' => $this->buildCalculatorPayload($submission),
      default => NULL,
    };
  }

  /**
   * @return array<string, mixed>
   */
  private function buildContactPayload(WebformSubmissionInterface $submission): array {
    $data = $submission->getData();
    $firstName = trim((string) ($data['name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $phone = trim((string) ($data['ph_number'] ?? ''));
    $category = (string) ($data['category'] ?? '');
    $subject = trim((string) ($data['subject'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));
    $serviceUrl = trim((string) ($data['service'] ?? ''));

    $attribution = $this->attributionCollector->collect();
    $categoryLabel = self::CONTACT_CATEGORY_LABELS[$category] ?? $category;

    $noteParts = [
      'Submitted from Contact us form.',
      'Category: ' . ($categoryLabel !== '' ? $categoryLabel : '—'),
    ];
    if ($subject !== '') {
      $noteParts[] = 'Subject: ' . $subject;
    }
    if ($message !== '') {
      $noteParts[] = $message;
    }

    $payload = [
      'full_name' => trim($firstName . ' ' . $lastName),
      'email' => trim((string) ($data['email'] ?? '')),
      'phone' => $phone,
      'whatsapp_number' => $phone,
      'country' => $this->settings->getDefaultCountry(),
      'preferred_language' => $submission->getLangcode(),
      'business_activity' => $categoryLabel !== '' ? $categoryLabel : 'Contact inquiry',
      'lead_service_slug' => $this->resolveContactLeadServiceSlug($serviceUrl),
      'priority_slug' => $this->settings->getDefaultPrioritySlug(),
      'note' => implode("\n\n", $noteParts),
    ];

    return $this->applyAttribution($payload, $attribution, $serviceUrl !== '' ? $serviceUrl : NULL);
  }

  /**
   * @return array<string, mixed>
   */
  private function buildCalculatorPayload(WebformSubmissionInterface $submission): array {
    $data = $submission->getData();
    $firstName = trim((string) ($data['first_name'] ?? ''));
    $lastName = trim((string) ($data['last_name'] ?? ''));
    $phone = trim((string) ($data['phone'] ?? ''));
    $companyName = trim((string) ($data['company_name'] ?? ''));
    $answers = $this->decodeJsonMap($data['estimate_answers_json'] ?? '');
    $result = $this->decodeJsonMap($data['estimate_result_json'] ?? '');

    $setupMin = $this->toFloat($data['estimate_setup_min'] ?? $result['setup_min'] ?? NULL);
    $setupMax = $this->toFloat($data['estimate_setup_max'] ?? $result['setup_max'] ?? NULL);
    $estimateSar = $this->resolveEstimateValue($setupMin, $setupMax);
    $package = trim((string) ($data['estimate_package'] ?? ''));
    if ($package === '' && isset($result['package']) && is_array($result['package'])) {
      $package = trim((string) ($result['package']['id'] ?? $result['package']['label'] ?? ''));
    }

    $activityId = is_string($answers['activity'] ?? NULL) ? $answers['activity'] : '';
    $activityLabel = self::CALCULATOR_ACTIVITY_LABELS[$activityId] ?? $activityId;

    $attribution = $this->attributionCollector->collect();
    $noteParts = [
      'Submitted from business setup cost calculator.',
      'Timeline: ' . trim((string) ($data['estimate_timeline'] ?? '')),
      'Setup range (SAR): ' . $this->formatRange($setupMin, $setupMax),
      'Monthly range (SAR): ' . $this->formatRange(
        $this->toFloat($data['estimate_monthly_min'] ?? $result['monthly_min'] ?? NULL),
        $this->toFloat($data['estimate_monthly_max'] ?? $result['monthly_max'] ?? NULL),
      ),
    ];
    if ($answers !== []) {
      $noteParts[] = 'Answers: ' . json_encode($answers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    $payload = [
      'full_name' => trim($firstName . ' ' . $lastName),
      'email' => trim((string) ($data['email'] ?? '')),
      'phone' => $phone,
      'whatsapp_number' => $phone,
      'country' => $this->settings->getDefaultCountry(),
      'preferred_language' => $submission->getLangcode(),
      'business_activity' => $activityLabel !== '' ? $activityLabel : 'Business setup',
      'lead_service_slug' => $this->settings->getLeadServiceSlug('setup_cost_estimate_lead'),
      'priority_slug' => $this->settings->getDefaultPrioritySlug(),
      'calculator' => array_filter([
        'package' => $package !== '' ? $package : NULL,
        'estimate_sar' => $estimateSar,
        'setup_min' => $setupMin,
        'setup_max' => $setupMax,
        'monthly_min' => $this->toFloat($data['estimate_monthly_min'] ?? $result['monthly_min'] ?? NULL),
        'monthly_max' => $this->toFloat($data['estimate_monthly_max'] ?? $result['monthly_max'] ?? NULL),
        'timeline' => trim((string) ($data['estimate_timeline'] ?? '')),
        'profile_id' => trim((string) ($data['estimate_profile_id'] ?? '')),
      ], static fn ($value) => $value !== NULL && $value !== ''),
      'note' => implode("\n", array_filter($noteParts)),
    ];

    if ($companyName !== '') {
      $payload['company_name'] = $companyName;
    }
    if ($estimateSar !== NULL) {
      $payload['estimated_deal_value'] = $estimateSar;
    }

    return $this->applyAttribution($payload, $attribution);
  }

  /**
   * @param array<string, mixed> $payload
   * @param array{referrer: string|null, utm: array<string, string>|null} $attribution
   *
   * @return array<string, mixed>
   */
  private function applyAttribution(array $payload, array $attribution, ?string $referrerOverride = NULL): array {
    $referrer = $referrerOverride ?? $attribution['referrer'];
    if (is_string($referrer) && $referrer !== '') {
      $payload['referrer'] = $referrer;
    }
    if (is_array($attribution['utm']) && $attribution['utm'] !== []) {
      $payload['utm'] = $attribution['utm'];
    }
    return $this->filterEmptyValues($payload);
  }

  private function resolveContactLeadServiceSlug(string $serviceUrl): string {
    if ($serviceUrl !== '' && filter_var($serviceUrl, FILTER_VALIDATE_URL)) {
      $path = (string) parse_url($serviceUrl, PHP_URL_PATH);
      $path = trim($path, '/');
      if ($path !== '') {
        return $path;
      }
    }
    return $this->settings->getLeadServiceSlug('contact');
  }

  /**
   * @param array<string, mixed> $payload
   *
   * @return array<string, mixed>
   */
  private function filterEmptyValues(array $payload): array {
    $filtered = [];
    foreach ($payload as $key => $value) {
      if ($value === NULL || $value === '') {
        continue;
      }
      if (is_array($value) && $value === []) {
        continue;
      }
      $filtered[$key] = $value;
    }
    return $filtered;
  }

  /**
   * @return array<string, mixed>
   */
  private function decodeJsonMap(mixed $json): array {
    if (!is_string($json) || $json === '') {
      return [];
    }
    try {
      $decoded = json_decode($json, TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException) {
      return [];
    }
    return is_array($decoded) ? $decoded : [];
  }

  private function toFloat(mixed $value): ?float {
    if ($value === NULL || $value === '') {
      return NULL;
    }
    if (!is_numeric($value)) {
      return NULL;
    }
    return (float) $value;
  }

  private function resolveEstimateValue(?float $min, ?float $max): ?int {
    if ($max !== NULL && $max > 0) {
      return (int) round($max);
    }
    if ($min !== NULL && $min > 0) {
      return (int) round($min);
    }
    if ($min !== NULL && $max !== NULL && ($min > 0 || $max > 0)) {
      return (int) round(($min + $max) / 2);
    }
    return NULL;
  }

  private function formatRange(?float $min, ?float $max): string {
    if ($min === NULL && $max === NULL) {
      return '—';
    }
    if ($min !== NULL && $max !== NULL) {
      return number_format($min, 0, '.', ',') . ' – ' . number_format($max, 0, '.', ',');
    }
    $single = $min ?? $max;
    return $single !== NULL ? number_format($single, 0, '.', ',') : '—';
  }

}
