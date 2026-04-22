<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;

/**
 * Sanitizes metatag fields on node save to prevent HTML in title/description.
 */
class MetatagSanitizeHooks {

  use StringTranslationTrait;

  /**
   * Constructs a MetatagSanitizeHooks object.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(
    protected readonly MessengerInterface $messenger,
  ) {}

  /**
   * Strips HTML from metatag fields before saving a node.
   *
   * Handles two patterns found in production data:
   * - <meta name="title" content="Actual Title"> -> extracts content attribute
   * - <title>Actual Title</title> -> extracts inner text via strip_tags()
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being saved.
   */
  #[Hook('node_presave')]
  public function sanitizeMetatags(NodeInterface $node): void {
    if (!$node->hasField('field_meta_tags')) {
      return;
    }

    $field = $node->get('field_meta_tags');
    if ($field->isEmpty()) {
      return;
    }

    $value = $field->value;
    if (empty($value)) {
      return;
    }

    $data = json_decode($value, TRUE);
    if (!is_array($data)) {
      return;
    }

    $changed = FALSE;
    $keys = ['title', 'description', 'abstract', 'keywords'];

    foreach ($keys as $key) {
      if (empty($data[$key]) || !str_contains($data[$key], '<')) {
        continue;
      }

      $cleaned = $this->stripHtml($data[$key]);
      if ($cleaned !== $data[$key]) {
        $data[$key] = $cleaned;
        $changed = TRUE;
      }
    }

    if ($changed) {
      $node->set('field_meta_tags', json_encode($data, JSON_UNESCAPED_UNICODE));
      $this->messenger->addWarning(
        $this->t('HTML tags were automatically removed from Meta tag fields.')
      );
    }
  }

  /**
   * Extracts clean text from a value that may contain HTML markup.
   *
   * @param string $value
   *   The raw metatag value, possibly containing HTML.
   *
   * @return string
   *   The cleaned plain-text value.
   */
  protected function stripHtml(string $value): string {
    // Pattern 1: <meta ... content="text"> — extract content attribute.
    if (preg_match('/content=["\']([^"\']*)["\']/', $value, $matches)) {
      return trim($matches[1]);
    }

    // Pattern 2: <title>text</title> or other tags wrapping text.
    $stripped = strip_tags($value);
    if (!empty(trim($stripped))) {
      return trim($stripped);
    }

    // Fallback: return original if nothing useful was extracted.
    return $value;
  }

}
