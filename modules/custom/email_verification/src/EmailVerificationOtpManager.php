<?php

namespace Drupal\email_verification;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;

/**
 * Stores OTP and verified state in private tempstore (session-scoped).
 */
class EmailVerificationOtpManager {

  use StringTranslationTrait;

  public const OTP_TTL = 600;

  public const VERIFIED_TTL = 1800;

  public function __construct(
    private PrivateTempStoreFactory $tempStoreFactory,
    private MailManagerInterface $mailManager,
    private LoggerInterface $logger,
    private ConfigFactoryInterface $configFactory,
    private RendererInterface $renderer,
  ) {}

  /**
   * Webforms config: machine name => [ email_element => string ].
   *
   * @return array<string, array{email_element: string}>
   */
  public function getAllowedWebforms(): array {
    $webforms = $this->configFactory->get('email_verification.settings')->get('webforms');
    return is_array($webforms) ? $webforms : [];
  }

  public function isWebformAllowed(string $webformId): bool {
    return isset($this->getAllowedWebforms()[$webformId]);
  }

  public function getEmailElementKey(string $webformId): ?string {
    $cfg = $this->getAllowedWebforms();
    return $cfg[$webformId]['email_element'] ?? NULL;
  }

  /**
   * Builds storage key for session + webform + email.
   */
  protected function key(string $webformId, string $email): string {
    return $webformId . ':' . mb_strtolower(trim($email));
  }

  /**
   * Generates OTP, stores it, sends email.
   */
  public function sendOtp(string $webformId, string $email, string $langcode): bool {
    if (!$this->isWebformAllowed($webformId)) {
      return FALSE;
    }
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return FALSE;
    }
    $otp = (string) random_int(100000, 999999);
    $now = time();
    $store = $this->tempStoreFactory->get('email_verification');
    $data = [
      'otp' => $otp,
      'otp_expires' => $now + self::OTP_TTL,
      'verified' => FALSE,
    ];
    $store->set($this->key($webformId, $email), $data);

    $result = $this->mailManager->mail(
      'email_verification',
      'email_verification_otp',
      $email,
      $langcode,
      [
        'subject' => $this->t('Email verification code'),
        'html_body' => $this->buildOtpEmailHtml($otp, $langcode),
      ],
      NULL,
      TRUE
    );

    $sent = ($result['result'] ?? NULL) === TRUE;
    if (!$sent) {
      $this->logger->error(
        'OTP email not sent to @email. result=@result message_id=@mid',
        [
          '@email' => $email,
          '@result' => json_encode($result['result'] ?? null),
          '@mid' => $result['id'] ?? 'n/a',
        ]
      );
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Renders the branded HTML email body (Twig template).
   */
  protected function buildOtpEmailHtml(string $otp, string $langcode): string {
    $site_url = rtrim(Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(), '/');
    // Theme variables must use # keys so ThemeManager passes them to Twig
    // (see core ThemeManager::render); un-prefixed keys are child render arrays.
    $build = [
      '#theme' => 'email_verification_otp',
      '#langcode' => $langcode,
      '#site_url' => $site_url,
      '#otp_code' => $otp,
      '#expiry_minutes' => (int) (self::OTP_TTL / 60),
      '#year' => date('Y'),
    ];
    return (string) $this->renderer->renderPlain($build);
  }

  /**
   * Validates OTP and marks email as verified for this session.
   */
  public function verifyOtp(string $webformId, string $email, string $code): bool {
    if (!$this->isWebformAllowed($webformId)) {
      return FALSE;
    }
    $email = trim($email);
    $code = trim($code);
    $store = $this->tempStoreFactory->get('email_verification');
    $k = $this->key($webformId, $email);
    $data = $store->get($k);
    if (!$data || empty($data['otp'])) {
      return FALSE;
    }
    if ($data['otp_expires'] < time()) {
      $store->delete($k);
      return FALSE;
    }
    if (!hash_equals((string) $data['otp'], $code)) {
      return FALSE;
    }
    $now = time();
    $data['verified'] = TRUE;
    $data['verified_expires'] = $now + self::VERIFIED_TTL;
    unset($data['otp'], $data['otp_expires']);
    $store->set($k, $data);
    return TRUE;
  }

  /**
   * Whether this email passed verification recently (ready to submit).
   */
  public function isVerified(string $webformId, string $email): bool {
    $email = trim($email);
    $store = $this->tempStoreFactory->get('email_verification');
    $k = $this->key($webformId, $email);
    $data = $store->get($k);
    if (!$data || empty($data['verified'])) {
      return FALSE;
    }
    if (empty($data['verified_expires']) || $data['verified_expires'] < time()) {
      $store->delete($k);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Clears verification after successful submission.
   */
  public function clearVerification(string $webformId, string $email): void {
    $email = trim($email);
    $this->tempStoreFactory->get('email_verification')->delete($this->key($webformId, $email));
  }

}
