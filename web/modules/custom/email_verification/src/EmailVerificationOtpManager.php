<?php

namespace Drupal\email_verification;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Psr\Log\LoggerInterface;

/**
 * Stores OTP and verified state in private tempstore (session-scoped).
 */
class EmailVerificationOtpManager {

  public const OTP_TTL = 600;

  public const VERIFIED_TTL = 1800;

  public function __construct(
    private PrivateTempStoreFactory $tempStoreFactory,
    private BrandedEmailBuilder $brandedEmail,
    private LoggerInterface $logger,
    private ConfigFactoryInterface $configFactory,
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
    try {
      $store = $this->tempStoreFactory->get('email_verification');
      $data = [
        'otp' => $otp,
        'otp_expires' => $now + self::OTP_TTL,
        'verified' => FALSE,
      ];
      $store->set($this->key($webformId, $email), $data);
    }
    catch (TempStoreException $e) {
      $this->logger->error('OTP tempstore write failed for @email: @message', [
        '@email' => $email,
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }

    $expiry_minutes = (int) (self::OTP_TTL / 60);
    $sent = $this->brandedEmail->sendOtp($email, $otp, $langcode, $expiry_minutes);
    if (!$sent) {
      $this->logger->error('OTP email not sent to @email.', ['@email' => $email]);
    }
    return $sent;
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
    try {
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
    catch (TempStoreException $e) {
      $this->logger->error('OTP verify tempstore failed for @email: @message', [
        '@email' => $email,
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Whether this email passed verification recently (ready to submit).
   */
  public function isVerified(string $webformId, string $email): bool {
    $email = trim($email);
    try {
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
    catch (TempStoreException $e) {
      $this->logger->error('OTP isVerified tempstore failed for @email: @message', [
        '@email' => $email,
        '@message' => $e->getMessage(),
      ]);
      return FALSE;
    }
  }

  /**
   * Clears verification after successful submission.
   */
  public function clearVerification(string $webformId, string $email): void {
    $email = trim($email);
    try {
      $this->tempStoreFactory->get('email_verification')->delete($this->key($webformId, $email));
    }
    catch (TempStoreException $e) {
      $this->logger->warning('OTP clear tempstore failed for @email: @message', [
        '@email' => $email,
        '@message' => $e->getMessage(),
      ]);
    }
  }

}
