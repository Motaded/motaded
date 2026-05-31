<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Email;
use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Plugin\EmailBuilder\LegacyEmailBuilder;

/**
 * Safe legacy builder for system/mail when params.context is missing.
 *
 * @EmailBuilder(
 *   id = "system.mail",
 *   label = @Translation("System mail"),
 * )
 */
final class SystemMailEmailBuilder extends LegacyEmailBuilder {

  /**
   * {@inheritdoc}
   *
   * Bypasses EmailFactory::initEmail() which would pick the generic "system"
   * legacy builder before this plugin.
   */
  public function fromArray(EmailFactoryInterface $factory, array $message): EmailInterface {
    $email = Email::create(\Drupal::getContainer(), $message['module'], $message['key']);
    $this->createParams($email, $message);
    $this->init($email);
    \Drupal::service('plugin.manager.email_adjuster')->applyPolicy($email);
    $email->initDone();
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function createParams(EmailInterface $email, ?array $legacy_message = NULL): void {
    if (is_array($legacy_message)) {
      $params = $legacy_message['params'] ?? [];
      if (!is_array($params)) {
        $params = [];
      }
      if (!isset($params['context']) || !is_array($params['context'])) {
        $params['context'] = [
          'subject' => $params['subject'] ?? $legacy_message['subject'] ?? '',
          'message' => $params['message'] ?? $params['body'] ?? '',
        ];
        $legacy_message['params'] = $params;
      }
    }
    parent::createParams($email, $legacy_message);
  }

}
