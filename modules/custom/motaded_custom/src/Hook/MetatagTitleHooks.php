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
   * Constructs a MetatagTitleHooks object.
   *
   * @param \Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper $titleSuffixHelper
   *   The title suffix helper.
   */
  public function __construct(
    protected readonly MetatagTitleSuffixHelper $titleSuffixHelper,
  ) {}

  /**
   * Appends site name suffix to metatag title when missing.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being saved.
   */
  #[Hook('node_presave')]
  public function ensureSiteNameSuffix(NodeInterface $node): void {
    if (!$node->hasField('field_meta_tags')) {
      return;
    }

    $field_definition = $node->getFieldDefinition('field_meta_tags');
    $langcodes = ($field_definition !== NULL && $field_definition->isTranslatable())
      ? array_keys($node->getTranslationLanguages(FALSE))
      : [$node->language()->getId()];

    foreach ($langcodes as $langcode) {
      $translation = $node->getTranslation($langcode);
      $field = $translation->get('field_meta_tags');
      if ($field->isEmpty()) {
        continue;
      }

      $value = (string) $field->value;
      if ($value === '') {
        continue;
      }

      $data = json_decode($value, TRUE);
      if (!is_array($data) || empty($data['title'])) {
        continue;
      }

      $title = trim((string) $data['title']);
      $updated_title = $this->titleSuffixHelper->appendSiteNameSuffix($title);
      if ($updated_title === $title) {
        continue;
      }

      $data['title'] = $updated_title;
      $translation->set('field_meta_tags', json_encode($data, JSON_UNESCAPED_UNICODE));
    }
  }

}

