<?php

declare(strict_types=1);

namespace Drupal\email_verification;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\webform\WebformSubmissionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Renders and sends Motaded-branded transactional HTML emails.
 */
final class BrandedEmailBuilder {

  use StringTranslationTrait;

  public function __construct(
    private readonly MailManagerInterface $mailManager,
    private readonly LoggerInterface $logger,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly RendererInterface $renderer,
    private readonly LanguageManagerInterface $languageManager,
    private readonly RequestStack $requestStack,
  ) {}

  /**
   * Sends OTP verification email (same branded layout as other transactional mail).
   */
  public function sendOtp(string $email, string $otp, string $langcode, int $expiryMinutes): bool {
    $t_opts = ['langcode' => $langcode];
    $site_name = trim((string) ($this->configFactory->get('system.site')->get('name') ?: ''));
    $subject = $site_name !== ''
      ? $this->ts('Confirm your email — @site', ['@site' => $site_name], $t_opts)
      : $this->ts('Confirm your email', [], $t_opts);

    $html = $this->renderBrandedEmail($langcode, [
      'preheader' => (string) $this->t(
        'Your Motaded verification code is ready. It expires in @minutes minutes.',
        ['@minutes' => $expiryMinutes],
        $t_opts
      ),
      'headline' => (string) $this->t('Verify your email', [], $t_opts),
      'intro' => (string) $this->t(
        'You’re almost done. To continue with your form submission, use the verification code below. We sent this message because someone entered this email address on the Motaded website.',
        [],
        $t_opts
      ),
      'highlight_label' => (string) $this->t('Your code', [], $t_opts),
      'highlight_value' => $otp,
      'highlight_monospace' => TRUE,
      'notes' => [
        (string) $this->t(
          'This code expires in <strong style="color:#0f2a1d;">@minutes minutes</strong>. If you didn’t request it, you can safely ignore this email — your account details won’t be changed.',
          ['@minutes' => $expiryMinutes],
          $t_opts
        ),
        (string) $this->t(
          'For your security, never share this code with anyone. Motaded staff will never ask you for it by phone or chat.',
          [],
          $t_opts
        ),
      ],
    ]);

    return $this->send($email, $subject, $html, $langcode, 'email_verification_otp');
  }

  /**
   * Sends setup cost lead confirmation to the submitter.
   */
  public function sendSetupCostLeadConfirmation(WebformSubmissionInterface $submission): bool {
    $data = $submission->getData();
    $email = trim((string) ($data['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return FALSE;
    }

    $langcode = $submission->getLangcode() ?: $this->languageManager->getDefaultLanguage()->getId();
    $t_opts = ['langcode' => $langcode];
    $site_name = trim((string) ($this->configFactory->get('system.site')->get('name') ?: ''));
    $subject = $site_name !== ''
      ? $this->ts('We received your request — @site', ['@site' => $site_name], $t_opts)
      : $this->ts('We received your request', [], $t_opts);

    $first_name = trim((string) ($data['first_name'] ?? ''));
    $greeting_name = $first_name !== '' ? $first_name : $this->ts('there', [], $t_opts);

    $intro = (string) $this->t(
      'Hi @name, thank you for submitting your business setup cost estimate request on Motaded. Our team will review your details and contact you with a tailored breakdown and recommended next steps.',
      ['@name' => $greeting_name],
      $t_opts
    );

    $summary = $this->buildSetupCostSummaryLine($data, $t_opts);
    if ($summary !== '') {
      $intro .= ' ' . $summary;
    }

    $sid = (int) $submission->id();
    $reference = $sid > 0
      ? 'REQ-' . date('Y') . '-' . $sid
      : '';

    $notes = [
      (string) $this->t(
        'This estimate is indicative only. Final government fees, office costs, and professional charges depend on your activity, ownership, and scope of services.',
        [],
        $t_opts
      ),
      (string) $this->t(
        'If you did not submit this request, you can safely ignore this email.',
        [],
        $t_opts
      ),
    ];

    $html = $this->renderBrandedEmail($langcode, [
      'preheader' => (string) $this->t(
        'We received your setup cost estimate request and will be in touch soon.',
        [],
        $t_opts
      ),
      'headline' => (string) $this->t('Thank you for your request', [], $t_opts),
      'intro' => $intro,
      'highlight_label' => $reference !== '' ? (string) $this->t('Your reference', [], $t_opts) : NULL,
      'highlight_value' => $reference !== '' ? $reference : NULL,
      'highlight_monospace' => FALSE,
      'notes' => $notes,
    ]);

    return $this->send($email, $subject, $html, $langcode, 'setup_cost_lead_confirmation');
  }

  /**
   * Sends updates subscription confirmation to the submitter (event_updates webform).
   */
  public function sendUpdatesSubscriptionConfirmation(WebformSubmissionInterface $submission): bool {
    $data = $submission->getData();
    $email = trim((string) ($data['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return FALSE;
    }

    $langcode = $submission->getLangcode() ?: $this->languageManager->getDefaultLanguage()->getId();
    $t_opts = ['langcode' => $langcode];
    $site_name = trim((string) ($this->configFactory->get('system.site')->get('name') ?: ''));
    $subject = $site_name !== ''
      ? $this->ts('You\'re subscribed to updates — @site', ['@site' => $site_name], $t_opts)
      : $this->ts('You\'re subscribed to updates', [], $t_opts);

    $sid = (int) $submission->id();
    $reference = $sid > 0
      ? 'SUB-' . date('Y') . '-' . $sid
      : '';

    $notes = [
      $this->ts(
        'We will only send updates that are relevant. You can contact us at any time if you wish to change your preferences.',
        [],
        $t_opts
      ),
      $this->ts(
        'If you did not subscribe, you can safely ignore this email.',
        [],
        $t_opts
      ),
    ];

    $html = $this->renderBrandedEmail($langcode, [
      'preheader' => $this->ts(
        'Thanks for subscribing — we will keep you posted with relevant Motaded updates.',
        [],
        $t_opts
      ),
      'headline' => $this->ts('You\'re subscribed', [], $t_opts),
      'intro' => $this->ts(
        'Thank you for subscribing to Motaded updates. Our team will share relevant invitations, briefings, and insights when they apply to your interests.',
        [],
        $t_opts
      ),
      'highlight_label' => $reference !== '' ? $this->ts('Your reference', [], $t_opts) : NULL,
      'highlight_value' => $reference !== '' ? $reference : NULL,
      'highlight_monospace' => FALSE,
      'notes' => $notes,
    ]);

    return $this->send($email, $subject, $html, $langcode, 'updates_subscription_confirmation');
  }

  /**
   * @param array<string, mixed> $data
   * @param array{langcode: string} $t_opts
   */
  private function buildSetupCostSummaryLine(array $data, array $t_opts): string {
    $parts = [];
    $setup_min = trim((string) ($data['estimate_setup_min'] ?? ''));
    $setup_max = trim((string) ($data['estimate_setup_max'] ?? ''));
    if ($setup_min !== '' && $setup_max !== '') {
      $parts[] = (string) $this->t(
        'Estimated initial setup: @min–@max SAR.',
        ['@min' => $setup_min, '@max' => $setup_max],
        $t_opts
      );
    }
    $package = trim((string) ($data['estimate_package'] ?? ''));
    if ($package !== '') {
      $parts[] = (string) $this->t('Recommended package: @package.', ['@package' => $package], $t_opts);
    }
    return implode(' ', $parts);
  }

  /**
   * @param array<string, mixed> $content
   */
  private function renderBrandedEmail(string $langcode, array $content): string {
    $languages = $this->languageManager->getLanguages();
    if (isset($languages[$langcode])) {
      $text_direction = $languages[$langcode]->getDirection();
    }
    else {
      $text_direction = in_array($langcode, ['ar', 'he', 'fa', 'ur', 'ps'], TRUE)
        ? LanguageInterface::DIRECTION_RTL
        : LanguageInterface::DIRECTION_LTR;
    }

    $notes = [];
    foreach ($content['notes'] ?? [] as $note) {
      if (is_string($note) && $note !== '') {
        $notes[] = new FormattableMarkup($note, []);
      }
    }

    $build = [
      '#theme' => 'motaded_branded_email',
      '#langcode' => $langcode,
      '#text_direction' => $text_direction,
      '#site_url' => $this->getSiteBaseUrlWithoutLanguagePrefix(),
      '#year' => date('Y'),
      '#preheader' => $content['preheader'] ?? '',
      '#headline' => $content['headline'] ?? '',
      '#intro' => $content['intro'] ?? '',
      '#highlight_label' => $content['highlight_label'] ?? NULL,
      '#highlight_value' => $content['highlight_value'] ?? NULL,
      '#highlight_monospace' => !empty($content['highlight_monospace']),
      '#notes' => $notes,
    ];

    return (string) $this->renderer->renderPlain($build);
  }

  /**
   * Translates and returns a plain string (for strict-typed mail API).
   *
   * @param array<string, mixed> $args
   * @param array<string, mixed> $options
   */
  private function ts(string $string, array $args = [], array $options = []): string {
    return (string) $this->t($string, $args, $options);
  }

  private function send(string $to, string $subject, string $html, string $langcode, string $mail_key): bool {
    $result = $this->mailManager->mail(
      'email_verification',
      $mail_key,
      $to,
      $langcode,
      [
        'subject' => $subject,
        'html_body' => $html,
      ],
      NULL,
      TRUE
    );

    $sent = ($result['result'] ?? NULL) === TRUE;
    if (!$sent) {
      $this->logger->error(
        'Branded email (@key) not sent to @email. result=@result',
        [
          '@key' => $mail_key,
          '@email' => $to,
          '@result' => json_encode($result['result'] ?? NULL),
        ]
      );
    }
    return $sent;
  }

  private function getSiteBaseUrlWithoutLanguagePrefix(): string {
    $request = $this->requestStack->getCurrentRequest();
    if ($request) {
      return rtrim($request->getSchemeAndHttpHost() . $request->getBasePath(), '/');
    }
    return rtrim(Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString(), '/');
  }

}
