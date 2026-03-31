<?php

namespace Drupal\email_verification\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\email_verification\EmailVerificationOtpManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * JSON endpoints for send / verify OTP (used by modal before webform AJAX submit).
 */
class EmailVerificationController extends ControllerBase {

  public function __construct(
    private EmailVerificationOtpManager $otpManager,
    private CsrfTokenGenerator $csrfToken,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email_verification.otp_manager'),
      $container->get('csrf_token')
    );
  }

  /**
   * Parses JSON body or throws 400.
   *
   * @return array<string, mixed>
   */
  protected function getJsonPayload(Request $request): array {
    try {
      $decoded = json_decode($request->getContent(), TRUE, 512, JSON_THROW_ON_ERROR);
    }
    catch (\JsonException $e) {
      throw new BadRequestHttpException('Invalid JSON');
    }
    return is_array($decoded) ? $decoded : [];
  }

  /**
   * Sends OTP to the given email.
   */
  public function sendOtp(Request $request): JsonResponse {
    $this->assertValidCsrf($request);

    $payload = $this->getJsonPayload($request);

    $webformId = $payload['webform_id'] ?? '';
    $email = is_string($payload['email'] ?? NULL) ? trim($payload['email']) : '';
    if (!is_string($webformId) || $webformId === '' || $email === '') {
      throw new BadRequestHttpException('Missing webform_id or email');
    }

    if (!$this->otpManager->isWebformAllowed($webformId)) {
      throw new BadRequestHttpException('Unsupported webform');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return new JsonResponse([
        'message' => (string) $this->t('Invalid email address.'),
      ], 400);
    }

    if ($this->otpManager->isVerified($webformId, $email)) {
      return new JsonResponse(['status' => 'already_verified']);
    }

    $langcode = $this->languageManager()->getCurrentLanguage()->getId();
    if (!$this->otpManager->sendOtp($webformId, $email, $langcode)) {
      return new JsonResponse([
        'message' => (string) $this->t('Could not send verification email. Check the address and try again.'),
      ], 400);
    }

    return new JsonResponse(['status' => 'sent']);
  }

  /**
   * Verifies OTP and marks session as verified for this email + webform.
   */
  public function verifyOtp(Request $request): JsonResponse {
    $this->assertValidCsrf($request);

    $payload = $this->getJsonPayload($request);

    $webformId = $payload['webform_id'] ?? '';
    $email = is_string($payload['email'] ?? NULL) ? trim($payload['email']) : '';
    $code = is_string($payload['code'] ?? NULL) ? trim($payload['code']) : '';
    if (!is_string($webformId) || $webformId === '' || $email === '' || $code === '') {
      throw new BadRequestHttpException('Missing webform_id, email, or code');
    }

    if (!$this->otpManager->isWebformAllowed($webformId)) {
      throw new BadRequestHttpException('Unsupported webform');
    }

    if (!$this->otpManager->verifyOtp($webformId, $email, $code)) {
      return new JsonResponse(['message' => (string) $this->t('Invalid or expired code.')], 400);
    }

    return new JsonResponse(['status' => 'verified']);
  }

  /**
   * Validates X-CSRF-Token for the "rest" token id (same as core JS uses).
   */
  protected function assertValidCsrf(Request $request): void {
    $token = $request->headers->get('X-CSRF-Token');
    if (!$token || !$this->csrfToken->validate($token, 'rest')) {
      throw new AccessDeniedHttpException();
    }
  }

}
