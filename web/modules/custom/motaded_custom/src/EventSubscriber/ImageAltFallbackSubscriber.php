<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Adds meaningful fallback alt for CKEditor inline images.
 */
final class ImageAltFallbackSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::RESPONSE => 'onResponse',
    ];
  }

  /**
   * Injects meaningful alt text for inline images missing alt.
   */
  public function onResponse(ResponseEvent $event): void {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();
    $content_type = (string) $response->headers->get('Content-Type', '');
    if ($content_type !== '' && stripos($content_type, 'text/html') === FALSE) {
      return;
    }

    $content = $response->getContent();
    if (!is_string($content) || $content === '' || stripos($content, '<img') === FALSE) {
      return;
    }

    $page_title = $this->extractPageTitleFromHtml($content);

    $updated = preg_replace_callback(
      '/<img\b[^>]*>/iu',
      function (array $matches) use ($page_title): string {
        $img_tag = $matches[0];

        // Skip if alt already exists (even empty).
        if (preg_match('/\balt\s*=/iu', $img_tag) === 1) {
          return $img_tag;
        }

        // Apply only to inline uploaded images.
        if (preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/iu', $img_tag, $src_match) !== 1) {
          return $img_tag;
        }
        $src = trim($src_match[2]);
        if ($src === '' || strpos($src, '/sites/default/files/inline-images/') === FALSE) {
          return $img_tag;
        }

        $filename_label = $this->filenameToLabel($src);
        $generated_alt = $this->buildAlt($page_title, $filename_label);
        if ($generated_alt === '') {
          return $img_tag;
        }

        $escaped_alt = htmlspecialchars($generated_alt, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return preg_replace('/\s*\/?>$/u', ' alt="' . $escaped_alt . '"$0', $img_tag, 1) ?? $img_tag;
      },
      $content
    );

    if (is_string($updated) && $updated !== $content) {
      $response->setContent($updated);
    }
  }

  /**
   * Extracts normalized page title from full HTML.
   */
  private function extractPageTitleFromHtml(string $html): string {
    if (preg_match('/<meta[^>]+property=(["\'])og:title\1[^>]+content=(["\'])(.*?)\2[^>]*>/iu', $html, $m) === 1) {
      return $this->normalizeTitle($m[3]);
    }

    if (preg_match('/<meta[^>]+content=(["\'])(.*?)\1[^>]+property=(["\'])og:title\3[^>]*>/iu', $html, $m) === 1) {
      return $this->normalizeTitle($m[2]);
    }

    if (preg_match('/<title[^>]*>(.*?)<\/title>/isu', $html, $m) === 1) {
      return $this->normalizeTitle(strip_tags($m[1]));
    }

    return '';
  }

  /**
   * Normalizes node/page title and strips site suffixes.
   */
  private function normalizeTitle(string $title): string {
    $title = trim(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($title === '') {
      return '';
    }

    // Typical format: "Page title | Motaded" or "Page title - Motaded".
    $title = preg_replace('/\s*[\|\-–—]\s*motaded\s*$/iu', '', $title) ?? $title;
    return trim($title);
  }

  /**
   * Converts image src filename into readable label.
   */
  private function filenameToLabel(string $src): string {
    $path = (string) parse_url($src, PHP_URL_PATH);
    $filename = pathinfo($path, PATHINFO_FILENAME);
    $filename = urldecode($filename);
    $filename = str_replace(['_', '-'], ' ', $filename);
    $filename = preg_replace('/\s+/', ' ', $filename) ?? $filename;
    return trim($filename);
  }

  /**
   * Builds final alt text from title and file label.
   */
  private function buildAlt(string $page_title, string $filename_label): string {
    if ($page_title !== '' && $filename_label !== '') {
      return $page_title . ' - ' . $filename_label;
    }
    if ($page_title !== '') {
      return $page_title;
    }
    return $filename_label;
  }

}
