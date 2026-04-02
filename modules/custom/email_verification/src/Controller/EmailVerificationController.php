<?php

namespace Drupal\email_verification\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
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

  /**
   * Cache TTL for issued CSRF tokens (seconds).
   */
  private const CSRF_CACHE_TTL = 600;

  public function __construct(
    private EmailVerificationOtpManager $otpManager,
    private CacheBackendInterface $cache,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email_verification.otp_manager'),
      $container->get('cache.default'),
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
   * Issues a short-lived CSRF token (cache-backed, no session seed).
   *
   * Anonymous users often lack a stable session between two fetch() calls;
   * Drupal's session CSRF then fails. This endpoint is safe: token is random
   * and stored server-side until expiry.
   */
  public function getCsrfToken(): JsonResponse {
    $token = bin2hex(random_bytes(32));
    $cid = $this->csrfCacheId($token);
    $this->cache->set($cid, 1, \Drupal::time()->getRequestTime() + self::CSRF_CACHE_TTL);
    return new JsonResponse(
      ['csrf_token' => $token],
      200,
      [
        'Cache-Control' => 'no-store, private',
      ],
    );
  }

  /**
   * Sends OTP to the given email.
   */
  public function sendOtp(Request $request): JsonResponse {
    $payload = $this->getJsonPayload($request);
    $this->assertValidCsrf($request, $payload);

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
    $payload = $this->getJsonPayload($request);
    $this->assertValidCsrf($request, $payload);

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
   * Validates CSRF token from header and/or JSON body against cache.
   */
  protected function assertValidCsrf(Request $request, array $payload = []): void {
    $token = trim((string) ($request->headers->get('X-CSRF-Token') ?? ''));
    if ($token === '' && isset($payload['csrf_token']) && is_string($payload['csrf_token'])) {
      $token = trim($payload['csrf_token']);
    }
    if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
      throw new AccessDeniedHttpException();
    }
    $cid = $this->csrfCacheId($token);
    if (!$this->cache->get($cid)) {
      throw new AccessDeniedHttpException();
    }
    $this->cache->delete($cid);
  }

  /**
   * Stable cache key for a client-provided token string.
   */
  private function csrfCacheId(string $token): string {
    return 'email_verification:csrf:' . hash('sha256', $token . Settings::getHashSalt());
  }

}
