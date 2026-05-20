<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Homepage;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileRepositoryInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Import homepage node 374 paragraph tree from exported dataset.
 */
final class HomepageMigrator {

  public const HOMEPAGE_NID = 374;

  public const DATASET_REL = '/data/homepage_export.php';

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {}

  public function datasetPath(): string {
    return \Drupal::service('extension.list.module')->getPath('motaded_custom') . self::DATASET_REL;
  }

  /**
   * Runs export script against local `db` (subprocess).
   */
  public function runExport(): string {
    $script = 'modules/custom/motaded_custom/scripts/export_homepage_dataset.php';
    $cmd = 'env DRUPAL_DB= drush cr >/dev/null 2>&1; env DRUPAL_DB= drush php:script ' . escapeshellarg($script) . ' 2>&1';
    $output = [];
    $code = 0;
    exec($cmd, $output, $code);
    if ($code !== 0) {
      throw new \RuntimeException('Export failed: ' . implode("\n", $output));
    }
    return $this->datasetPath();
  }

  /**
   * @return array{paragraphs: int, errors: list<string>}
   */
  public function import(bool $dry_run = FALSE): array {
    $path = $this->datasetPath();
    if (!is_readable($path)) {
      throw new \RuntimeException('Dataset not found. Run: drush mhome-export');
    }

    /** @var array<string, mixed> $dataset */
    $dataset = require $path;
    $paragraphs = $dataset['paragraphs'] ?? [];
    if (!is_array($paragraphs) || $paragraphs === []) {
      throw new \RuntimeException('Dataset has no paragraphs.');
    }

    $stats = ['paragraphs' => 0, 'errors' => []];
    $nid = (int) ($dataset['nid'] ?? self::HOMEPAGE_NID);

    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$node instanceof NodeInterface) {
      throw new \RuntimeException(sprintf('Node %d not found.', $nid));
    }

    if ($dry_run) {
      $stats['paragraphs'] = $this->countParagraphsInDataset($paragraphs);
      return $stats;
    }

    $this->deleteParagraphTree($node);

    $node = $this->entityTypeManager->getStorage('node')->load($nid);
    if (!$node instanceof NodeInterface) {
      throw new \RuntimeException(sprintf('Node %d not found after cleanup.', $nid));
    }

    $refs = [];
    foreach ($paragraphs as $row) {
      if (!is_array($row)) {
        continue;
      }
      try {
        $paragraph = $this->importParagraph($row, $stats);
        $refs[] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
      catch (\Throwable $e) {
        $stats['errors'][] = ($row['type'] ?? '?') . ': ' . $e->getMessage();
      }
    }

    $en = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;
    $en->set('field_paragraphs', $refs);

    $nodeFields = $dataset['node']['fields'] ?? [];
    if (is_array($nodeFields)) {
      $this->applyFields($en, $nodeFields, $stats);
    }

    if ($node->getEntityType()->isRevisionable()) {
      $node->setNewRevision(FALSE);
    }
    $node->save();

    $arFields = $dataset['node']['translations']['ar'] ?? NULL;
    if (is_array($arFields) && $arFields !== []) {
      if (!$node->hasTranslation('ar')) {
        $node->addTranslation('ar', $node->getTranslation('en')->toArray());
      }
      $ar = $node->getTranslation('ar');
      $this->applyFields($ar, $arFields, $stats);
      $node->save();
    }

    \Drupal::service('cache_tags.invalidator')->invalidateTags($node->getCacheTags());

    return $stats;
  }

  /**
   * @param array<string, mixed> $data
   */
  private function importParagraph(array $data, array &$stats): ParagraphInterface {
    $type = (string) ($data['type'] ?? '');
    if ($type === '') {
      throw new \InvalidArgumentException('Missing paragraph type.');
    }

    $paragraph = Paragraph::create([
      'type' => $type,
      'langcode' => 'en',
    ]);

    $fields = $data['fields'] ?? [];
    if (is_array($fields)) {
      foreach ($fields as $name => $value) {
        if (!is_string($name) || !$paragraph->hasField($name)) {
          continue;
        }
        if (is_array($value) && $value !== [] && isset($value[0]['type']) && is_array($value[0])) {
          $refs = [];
          foreach ($value as $childData) {
            $child = $this->importParagraph($childData, $stats);
            $refs[] = [
              'target_id' => $child->id(),
              'target_revision_id' => $child->getRevisionId(),
            ];
          }
          $paragraph->set($name, $refs);
          continue;
        }
        $def = $paragraph->getFieldDefinition($name);
        $normalized = $this->normalizeFieldValue(
          $def->getType(),
          (string) $def->getSetting('target_type'),
          $value,
          $stats,
        );
        if ($normalized !== NULL) {
          $paragraph->set($name, $normalized);
        }
      }
    }

    $paragraph->save();
    $stats['paragraphs']++;

    $arFields = $data['translations']['ar'] ?? NULL;
    if (is_array($arFields) && $arFields !== []) {
      if (!$paragraph->hasTranslation('ar')) {
        $paragraph->addTranslation('ar', $paragraph->getTranslation('en')->toArray());
      }
      $ar = $paragraph->getTranslation('ar');
      $this->applyFields($ar, $arFields, $stats);
      $paragraph->save();
    }

    return $paragraph;
  }

  /**
   * @param array<string, mixed> $fields
   * @param array{paragraphs: int, errors: list<string>} $stats
   */
  private function applyFields(object $entity, array $fields, array &$stats): void {
    foreach ($fields as $name => $value) {
      if (!is_string($name) || !$entity->hasField($name)) {
        continue;
      }
      if ($entity instanceof NodeInterface && $name === 'field_paragraphs') {
        continue;
      }
      $def = $entity->getFieldDefinition($name);
      if ($entity instanceof NodeInterface && $def->getFieldStorageDefinition()->isBaseField()) {
        continue;
      }
      $type = $def->getType();
      if ($type === 'entity_reference_revisions' && is_array($value) && isset($value[0]['type'])) {
        continue;
      }
      $normalized = $this->normalizeFieldValue($type, (string) $def->getSetting('target_type'), $value, $stats);
      if ($normalized !== NULL) {
        $entity->set($name, $normalized);
      }
    }
  }

  /**
   * @param array{paragraphs: int, errors: list<string>} $stats
   */
  private function normalizeFieldValue(string $type, string $targetType, mixed $value, array &$stats): mixed {
    if ($value === NULL) {
      return NULL;
    }

    if ($type === 'entity_reference' && $targetType === 'media' && is_array($value)) {
      $refs = [];
      foreach ($value as $item) {
        if (!is_array($item)) {
          continue;
        }
        $mid = $this->resolveMedia($item, $stats);
        if ($mid !== NULL) {
          $refs[] = ['target_id' => $mid];
        }
      }
      return $refs;
    }

    if ($type === 'entity_reference' && $targetType === 'taxonomy_term' && is_array($value)) {
      $refs = [];
      foreach ($value as $item) {
        if (!is_array($item)) {
          continue;
        }
        $tid = $this->resolveTerm($item);
        if ($tid !== NULL) {
          $refs[] = ['target_id' => $tid];
        }
      }
      return $refs;
    }

    if ($type === 'entity_reference' && $targetType === 'node' && is_array($value)) {
      $refs = [];
      foreach ($value as $item) {
        if (!is_array($item)) {
          continue;
        }
        $nid = $this->resolveNode($item, $stats);
        if ($nid !== NULL) {
          $refs[] = ['target_id' => $nid];
        }
      }
      return $refs;
    }

    return $value;
  }

  /**
   * @param array<string, mixed> $item
   * @param array{paragraphs: int, errors: list<string>} $stats
   */
  private function resolveMedia(array $item, array &$stats): ?int {
    $uuid = (string) ($item['uuid'] ?? '');
    if ($uuid !== '') {
      $found = $this->entityTypeManager->getStorage('media')->loadByProperties(['uuid' => $uuid]);
      if ($found !== []) {
        return (int) reset($found)->id();
      }
    }

    $uri = (string) ($item['uri'] ?? '');
    if ($uri === '') {
      return NULL;
    }

    $real = $this->fileSystem->realpath($uri);
    if ($real === FALSE || !is_readable($real)) {
      $stats['errors'][] = 'Missing file: ' . $uri;
      return NULL;
    }

    $bundle = (string) ($item['bundle'] ?? 'image');
    $name = (string) ($item['name'] ?? basename($uri));

    $files = $this->entityTypeManager->getStorage('file')->loadByProperties(['uri' => $uri]);
    if ($files !== []) {
      $file = reset($files);
    }
    else {
      $data = file_get_contents($real);
      if ($data === FALSE) {
        return NULL;
      }
      $file = $this->fileRepository->writeData($data, $uri, FileSystemInterface::EXISTS_REPLACE);
    }

    if (!$file instanceof File) {
      return NULL;
    }

    $values = [
      'bundle' => $bundle,
      'name' => $name,
      'status' => 1,
    ];
    if ($bundle === 'document') {
      $values['field_media_document'] = ['target_id' => $file->id()];
    }
    else {
      $values['field_media_image'] = ['target_id' => $file->id()];
    }
    $media = Media::create($values);
    $media->save();
    return (int) $media->id();
  }

  /**
   * @param array<string, mixed> $item
   */
  private function resolveTerm(array $item): ?int {
    $vid = (string) ($item['vid'] ?? '');
    $name = trim((string) ($item['name'] ?? ''));
    if ($vid === '' || $name === '') {
      return NULL;
    }
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties([
      'vid' => $vid,
      'name' => $name,
    ]);
    if ($terms !== []) {
      return (int) reset($terms)->id();
    }
    $term = Term::create([
      'vid' => $vid,
      'name' => $name,
      'langcode' => 'en',
    ]);
    $term->save();
    return (int) $term->id();
  }

  /**
   * @param array<string, mixed> $item
   * @param array{paragraphs: int, errors: list<string>} $stats
   */
  private function resolveNode(array $item, array &$stats): ?int {
    $bundle = (string) ($item['bundle'] ?? '');
    $title = trim((string) ($item['title'] ?? ''));
    if ($bundle === '' || $title === '') {
      return NULL;
    }
    $nids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->condition('title', $title)
      ->range(0, 1)
      ->execute();
    if ($nids === []) {
      $stats['errors'][] = sprintf('Node not found: %s (%s)', $title, $bundle);
      return NULL;
    }
    return (int) reset($nids);
  }

  private function deleteParagraphTree(NodeInterface $node): void {
    $nid = (int) $node->id();
    $toDelete = [];
    foreach ($node->get('field_paragraphs') as $item) {
      $p = $item->entity;
      if ($p instanceof ParagraphInterface) {
        $this->collectParagraphs($p, $toDelete);
      }
    }

    $pStorage = $this->entityTypeManager->getStorage('paragraph');
    if ($toDelete !== []) {
      $pStorage->delete($pStorage->loadMultiple(array_keys($toDelete)));
    }

    $db = \Drupal::database();
    foreach (['node__field_paragraphs', 'node_revision__field_paragraphs'] as $table) {
      if ($db->schema()->tableExists($table)) {
        $db->delete($table)
          ->condition('entity_id', $nid)
          ->execute();
      }
    }

    $this->entityTypeManager->getStorage('node')->resetCache([$nid]);
  }

  /**
   * @param array<int, true> $collector
   */
  private function collectParagraphs(ParagraphInterface $paragraph, array &$collector): void {
    $id = (int) $paragraph->id();
    if (isset($collector[$id])) {
      return;
    }
    $collector[$id] = TRUE;

    foreach ($paragraph->getFields() as $field) {
      if ($field->getFieldDefinition()->getType() !== 'entity_reference_revisions' || $field->isEmpty()) {
        continue;
      }
      foreach ($field as $item) {
        $child = $item->entity;
        if ($child instanceof ParagraphInterface) {
          $this->collectParagraphs($child, $collector);
        }
      }
    }
  }

  /**
   * @param list<array<string, mixed>> $paragraphs
   */
  private function countParagraphsInDataset(array $paragraphs): int {
    $count = 0;
    foreach ($paragraphs as $row) {
      $count += $this->countParagraphRow(is_array($row) ? $row : []);
    }
    return $count;
  }

  /**
   * @param array<string, mixed> $row
   */
  private function countParagraphRow(array $row): int {
    $count = 1;
    $fields = $row['fields'] ?? [];
    if (!is_array($fields)) {
      return $count;
    }
    foreach ($fields as $value) {
      if (!is_array($value) || !isset($value[0]['type'])) {
        continue;
      }
      foreach ($value as $child) {
        if (is_array($child)) {
          $count += $this->countParagraphRow($child);
        }
      }
    }
    return $count;
  }

}
