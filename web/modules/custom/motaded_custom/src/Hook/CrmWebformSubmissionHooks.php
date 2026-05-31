<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\motaded_custom\Crm\CrmLeadPayloadBuilder;
use Drupal\motaded_custom\Crm\CrmLeadWebhookClient;
use Drupal\motaded_custom\Crm\CrmLeadWebhookSettings;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Forwards selected Webform leads to the CRM webhook API.
 */
final class CrmWebformSubmissionHooks {

  public function __construct(
    private readonly CrmLeadWebhookSettings $settings,
    private readonly CrmLeadPayloadBuilder $payloadBuilder,
    private readonly CrmLeadWebhookClient $webhookClient,
  ) {}

  /**
   * Sends completed webform submissions to CRM.
   */
  #[Hook('webform_submission_insert')]
  public function onSubmissionInsert(WebformSubmissionInterface $webform_submission): void {
    if (!$this->settings->isEnabled()) {
      return;
    }

    $webformId = $webform_submission->getWebform()->id();
    if (!in_array($webformId, CrmLeadWebhookSettings::LEAD_WEBFORMS, TRUE)) {
      return;
    }

    $payload = $this->payloadBuilder->build($webform_submission);
    if ($payload === NULL) {
      return;
    }

    $this->webhookClient->sendLead($payload);
  }

}
