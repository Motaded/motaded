<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper;
use Drupal\node\NodeInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for metatag title normalization.
 */
final class MetatagTitleCommands extends DrushCommands {

  /**
   * Constructs a MetatagTitleCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper $titleSuffixHelper
   *   The title suffix helper.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly MetatagTitleSuffixHelper $titleSuffixHelper,
  ) {}

  /**
   * Normalizes node metatag titles for SEO consistency.
   *
   * @param array $options
   *   Command options.
   */
  #[CLI\Command(name: 'motaded:metatag-title-suffix', aliases: ['mmts'])]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving nodes.')]
  #[CLI\Option(name: 'batch-size', description: 'Nodes per batch (default: 100).')]
  #[CLI\Usage(name: 'drush motaded:metatag-title-suffix --dry-run', description: 'Preview SEO title normalization updates.')]
  public function enforceSuffix(array $options = ['dry-run' => FALSE, 'batch-size' => 100]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $batch_size = max(1, (int) ($options['batch-size'] ?? 100));

    $storage = $this->entityTypeManager->getStorage('node');
    $changed_nodes = 0;
    $changed_titles = 0;
    $processed = 0;
    $last_nid = 0;
    $title_index = $this->titleSuffixHelper->buildTitleIndex();
    while (TRUE) {
      $nids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('nid', $last_nid, '>')
        ->sort('nid', 'ASC')
        ->range(0, $batch_size)
        ->execute();

      if (empty($nids)) {
        break;
      }

      $last_nid = (int) max($nids);
      $processed += count($nids);
      $nodes = $storage->loadMultiple($nids);
      foreach ($nodes as $node) {
        if (!$node instanceof NodeInterface) {
          continue;
        }
        $metatag_field = $this->titleSuffixHelper->getMetatagFieldName($node);
        if ($metatag_field === NULL) {
          continue;
        }
        $node_changed = FALSE;
        foreach ($this->getTargetLangcodes($node, $metatag_field) as $langcode) {
          if (!$node->hasTranslation($langcode)) {
            continue;
          }
          $translation = $node->getTranslation($langcode);
          if (!$translation->hasField($metatag_field)) {
            continue;
          }
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
          $new_title = $this->titleSuffixHelper->optimizeNodeTitle($node, $langcode, $old_title, $title_index);
          if ($new_title === $old_title) {
            continue;
          }

          $data['title'] = $new_title;
          $translation->set($metatag_field, json_encode($data, JSON_UNESCAPED_UNICODE));
          $node_changed = TRUE;
          ++$changed_titles;
        }

        if ($node_changed) {
          ++$changed_nodes;
          if (!$dry_run) {
            $node->save();
          }
        }
      }
    }

    if ($processed === 0) {
      $this->logger()->notice('No nodes with metatag fields found.');
      return;
    }

    $mode = $dry_run ? 'Dry-run' : 'Applied';
    $this->logger()->success(sprintf(
      '%s: processed %d node(s), changed %d node(s), %d title update(s).',
      $mode,
      $processed,
      $changed_nodes,
      $changed_titles
    ));
  }

  /**
   * Gets target langcodes for metatag updates.
   *
   * If metatag field is not translatable, only default translation is updated.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $metatagField
   *   The metatag field machine name.
   *
   * @return string[]
   *   Langcodes to process.
   */
  protected function getTargetLangcodes(NodeInterface $node, string $metatagField): array {
    $field_definition = $node->getFieldDefinition($metatagField);
    if ($field_definition === NULL || !$field_definition->isTranslatable()) {
      return [$node->language()->getId()];
    }

    return array_keys($node->getTranslationLanguages());
  }

}

