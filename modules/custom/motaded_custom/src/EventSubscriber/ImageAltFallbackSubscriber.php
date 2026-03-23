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

    $dom = new \DOMDocument();
    libxml_use_internal_errors(TRUE);
    $loaded = $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    if (!$loaded) {
      return;
    }

    $xpath = new \DOMXPath($dom);
    $page_title = $this->extractPageTitle($xpath);
    $changed = FALSE;

    /** @var \DOMElement $img */
    foreach ($dom->getElementsByTagName('img') as $img) {
      $src = trim((string) $img->getAttribute('src'));
      if ($src === '' || strpos($src, '/sites/default/files/inline-images/') === FALSE) {
        continue;
      }

      $alt = trim((string) $img->getAttribute('alt'));
      if ($alt !== '') {
        continue;
      }

      $filename_label = $this->filenameToLabel($src);
      $generated_alt = $this->buildAlt($page_title, $filename_label);
      if ($generated_alt === '') {
        continue;
      }

      $img->setAttribute('alt', $generated_alt);
      $changed = TRUE;
    }

    if ($changed) {
      $response->setContent($dom->saveHTML());
    }
  }

  /**
   * Extracts normalized page title from HTML.
   */
  private function extractPageTitle(\DOMXPath $xpath): string {
    $meta = $xpath->query("//meta[@property='og:title']/@content");
    if ($meta !== FALSE && $meta->length > 0) {
      return $this->normalizeTitle((string) $meta->item(0)->nodeValue);
    }

    $title = $xpath->query('//title');
    if ($title !== FALSE && $title->length > 0) {
      return $this->normalizeTitle((string) $title->item(0)->textContent);
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
