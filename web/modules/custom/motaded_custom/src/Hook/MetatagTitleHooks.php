<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper;
use Drupal\node\NodeInterface;

/**
 * Ensures node metatag title includes a site name suffix on save.
 */
class MetatagTitleHooks {

  /**
   * Request-scope title index to avoid repeated duplicate scans on save.
   *
   * @var array<string, array<string, int>>|null
   */
  protected ?array $titleIndex = NULL;

  /**
   * Constructs a MetatagTitleHooks object.
   *
   * @param \Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper $titleSuffixHelper
   *   The title suffix helper.
   */
  public function __construct(
    protected readonly MetatagTitleSuffixHelper $titleSuffixHelper,
  ) {}

  /**
   * Optimizes metatag title on save and prevents future regressions.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being saved.
   */
  #[Hook('node_presave')]
  public function ensureSiteNameSuffix(NodeInterface $node): void {
    $metatag_field = $this->titleSuffixHelper->getMetatagFieldName($node);
    if ($metatag_field === NULL) {
      return;
    }

    $field_definition = $node->getFieldDefinition($metatag_field);
    $langcodes = ($field_definition !== NULL && $field_definition->isTranslatable())
      ? array_keys($node->getTranslationLanguages())
      : [$node->language()->getId()];

    if ($this->titleIndex === NULL) {
      $this->titleIndex = $this->titleSuffixHelper->buildTitleIndex();
    }

    foreach ($langcodes as $langcode) {
      $translation = $node->getTranslation($langcode);
      $field = $translation->get($metatag_field);
      $data = [];
      if (!$field->isEmpty()) {
        $value = (string) $field->value;
        if ($value !== '') {
          $decoded = json_decode($value, TRUE);
          if (is_array($decoded)) {
            $data = $decoded;
          }
        }
      }

      $old_title = trim((string) ($data['title'] ?? ''));
      $updated_title = $this->titleSuffixHelper->optimizeNodeTitle($node, $langcode, $old_title, $this->titleIndex);
      if ($updated_title === $old_title) {
        continue;
      }

      $data['title'] = $updated_title;
      $translation->set($metatag_field, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
  }

}

