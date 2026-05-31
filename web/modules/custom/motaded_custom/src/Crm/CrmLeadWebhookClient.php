<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Crm;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Sends lead payloads to the CRM webhook API.
 */
final class CrmLeadWebhookClient {

  private readonly LoggerInterface $logger;

  public function __construct(
    private readonly \GuzzleHttp\ClientInterface $httpClient,
    LoggerChannelFactoryInterface $loggerFactory,
    private readonly CrmLeadWebhookSettings $settings,
    private readonly CrmLeadWebhookUrlResolver $urlResolver,
  ) {
    $this->logger = $loggerFactory->get('motaded_custom');
  }

  /**
   * POSTs a lead payload when CRM webhook integration is enabled.
   *
   * @param array<string, mixed> $payload
   */
  public function sendLead(array $payload): bool {
    if (!$this->settings->isEnabled()) {
      return FALSE;
    }

    $url = $this->settings->getUrl();
    $requestUrl = $this->urlResolver->resolve($url);
    if ($this->urlResolver->wasAdjusted($url, $requestUrl)) {
      $this->logger->notice('CRM webhook URL adjusted for DDEV: @from → @to', [
        '@from' => $url,
        '@to' => $requestUrl,
      ]);
    }

    try {
      $response = $this->httpClient->request('POST', $requestUrl, [
        'headers' => [
          'Authorization' => 'Bearer ' . $this->settings->getToken(),
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => $this->settings->getTimeout(),
        'http_errors' => FALSE,
      ]);
    }
    catch (GuzzleException $exception) {
      $this->logger->error('CRM lead webhook request failed: @message', [
        '@message' => $exception->getMessage(),
      ]);
      return FALSE;
    }

    $status = $response->getStatusCode();
    if ($status < 200 || $status >= 300) {
      $this->logger->error('CRM lead webhook returned HTTP @status for @email', [
        '@status' => $status,
        '@email' => (string) ($payload['email'] ?? ''),
        'response' => (string) $response->getBody(),
      ]);
      return FALSE;
    }

    $this->logger->info('CRM lead webhook accepted submission for @email (HTTP @status).', [
      '@email' => (string) ($payload['email'] ?? ''),
      '@status' => $status,
    ]);
    return TRUE;
  }

}
