<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Crm;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collects referrer and UTM parameters for CRM lead payloads.
 */
final class CrmLeadAttributionCollector {

  private const UTM_KEYS = [
    'utm_source' => 'source',
    'utm_medium' => 'medium',
    'utm_campaign' => 'campaign',
    'utm_term' => 'term',
    'utm_content' => 'content',
  ];

  public function __construct(
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * @return array{referrer: string|null, utm: array<string, string>|null}
   */
  public function collect(): array {
    $request = $this->requestStack->getCurrentRequest();
    if (!$request instanceof Request) {
      return ['referrer' => NULL, 'utm' => NULL];
    }

    $referrer = $this->normalizeReferrer($request->headers->get('referer'));
    $utm = $this->extractUtm($request);

    if ($utm === [] && $request->hasSession()) {
      $stored = $request->getSession()->get('motaded_crm_utm');
      if (is_array($stored)) {
        $utm = array_filter($stored, static fn ($value) => is_string($value) && $value !== '');
      }
    }

    return [
      'referrer' => $referrer,
      'utm' => $utm !== [] ? $utm : NULL,
    ];
  }

  /**
   * Persists UTM query params in session for later form submissions.
   */
  public function persistUtmFromRequest(Request $request): void {
    if (!$request->hasSession()) {
      return;
    }
    $utm = $this->extractUtm($request);
    if ($utm === []) {
      return;
    }
    $request->getSession()->set('motaded_crm_utm', $utm);
  }

  /**
   * @return array<string, string>
   */
  private function extractUtm(Request $request): array {
    $utm = [];
    foreach (self::UTM_KEYS as $queryKey => $payloadKey) {
      $value = trim((string) $request->query->get($queryKey, ''));
      if ($value !== '') {
        $utm[$payloadKey] = $value;
      }
    }
    return $utm;
  }

  private function normalizeReferrer(?string $referrer): ?string {
    if ($referrer === NULL || $referrer === '') {
      return NULL;
    }
    return filter_var($referrer, FILTER_VALIDATE_URL) ? $referrer : NULL;
  }

}
