<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use RuntimeException;

/**
 * Drush commands for metatag title maintenance.
 */
final class MetatagTitleCommands extends DrushCommands {

  /**
   * Constructs a MetatagTitleCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager.
   * @param \Drupal\motaded_custom\Seo\MetatagTitleSuffixHelper $titleSuffixHelper
   *   The title suffix helper.
   */
  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly AliasManagerInterface $aliasManager,
    protected readonly MetatagTitleSuffixHelper $titleSuffixHelper,
  ) {}

  /**
   * Ensures [site:name] suffix exists in node metatag title.
   *
   * @param array $options
   *   Command options.
   */
  #[CLI\Command(name: 'motaded:metatag-title-suffix', aliases: ['mmts'])]
  #[CLI\Option(name: 'dry-run', description: 'Preview changes without saving nodes.')]
  #[CLI\Option(name: 'report', description: 'Optional CSV report path.')]
  #[CLI\Option(name: 'batch-size', description: 'Nodes per batch (default: 100).')]
  #[CLI\Usage(name: 'drush motaded:metatag-title-suffix --dry-run', description: 'Preview title suffix updates.')]
  #[CLI\Usage(name: 'drush motaded:metatag-title-suffix --report=../tmp/report.csv', description: 'Apply updates and write CSV report.')]
  public function enforceSuffix(array $options = ['dry-run' => FALSE, 'report' => NULL, 'batch-size' => 100]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $report_path = isset($options['report']) && $options['report'] !== '' ? (string) $options['report'] : NULL;
    $batch_size = max(1, (int) ($options['batch-size'] ?? 100));

    $storage = $this->entityTypeManager->getStorage('node');
    $changed_nodes = 0;
    $changed_titles = 0;
    $processed = 0;
    $last_nid = 0;
    $report_handle = NULL;
    $resolved_report_path = NULL;

    if ($report_path !== NULL) {
      $resolved_report_path = $this->prepareReportPath($report_path);
      $report_handle = fopen($resolved_report_path, 'wb');
      if ($report_handle === FALSE) {
        throw new RuntimeException(sprintf('Unable to open report file for writing: %s', $resolved_report_path));
      }
      if (fputcsv($report_handle, ['nid', 'langcode', 'bundle', 'path', 'old_title', 'new_title']) === FALSE) {
        throw new RuntimeException(sprintf('Unable to write report header: %s', $resolved_report_path));
      }
    }
    try {
      while (TRUE) {
        $nids = $storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('nid', $last_nid, '>')
          ->condition('field_meta_tags', NULL, 'IS NOT NULL')
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
          $node_changed = FALSE;
          foreach ($this->getTargetLangcodes($node) as $langcode) {
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

            $old_title = trim((string) $data['title']);
            $new_title = $this->titleSuffixHelper->appendSiteNameSuffix($old_title);
            if ($new_title === $old_title) {
              continue;
            }

            $data['title'] = $new_title;
            $translation->set('field_meta_tags', json_encode($data, JSON_UNESCAPED_UNICODE));
            $node_changed = TRUE;
            ++$changed_titles;

            if ($report_handle !== NULL) {
              $row = [
                'nid' => (string) $node->id(),
                'langcode' => $langcode,
                'bundle' => $node->bundle(),
                'path' => $this->aliasManager->getAliasByPath('/node/' . $node->id(), $langcode),
                'old_title' => $old_title,
                'new_title' => $new_title,
              ];
              if (fputcsv($report_handle, $row) === FALSE) {
                throw new RuntimeException(sprintf('Unable to write report row: %s', $resolved_report_path));
              }
            }
          }

          if ($node_changed) {
            ++$changed_nodes;
            if (!$dry_run) {
              $node->save();
            }
          }
        }
      }
    }
    finally {
      if ($report_handle !== NULL) {
        fclose($report_handle);
      }
    }

    if ($processed === 0) {
      $this->logger()->notice('No nodes with field_meta_tags found.');
      return;
    }

    if ($resolved_report_path !== NULL) {
      $this->logger()->notice(sprintf('Report written: %s', $resolved_report_path));
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
   * If field_meta_tags is not translatable, only default translation is updated.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string[]
   *   Langcodes to process.
   */
  protected function getTargetLangcodes(NodeInterface $node): array {
    $field_definition = $node->getFieldDefinition('field_meta_tags');
    if ($field_definition === NULL || !$field_definition->isTranslatable()) {
      return [$node->language()->getId()];
    }

    return array_keys($node->getTranslationLanguages(FALSE));
  }

  /**
   * Resolves and prepares report path.
   *
   * @param string $reportPath
   *   Output path (absolute or relative to Drupal root).
   *
   * @return string
   *   Resolved absolute path.
   */
  protected function prepareReportPath(string $reportPath): string {
    if (!str_starts_with($reportPath, '/')) {
      $reportPath = DRUPAL_ROOT . '/' . ltrim($reportPath, '/');
    }

    $dir = dirname($reportPath);
    if (!is_dir($dir) && !mkdir($dir, 0775, TRUE) && !is_dir($dir)) {
      throw new RuntimeException(sprintf('Unable to create report directory: %s', $dir));
    }

    return $reportPath;
  }

}

