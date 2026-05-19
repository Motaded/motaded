<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import official government PDFs into document nodes.
 */
final class DocumentImportOfficialCommands extends DrushCommands {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {
    parent::__construct();
  }

  /**
   * Upsert document nodes from official catalog (per-row PDF).
   */
  #[CLI\Command(name: 'motaded:document-import-official', aliases: ['mdoc'])]
  #[CLI\Option(name: 'download', description: 'Download missing PDFs from source_url into data/documents/official/.')]
  #[CLI\Option(name: 'dry-run', description: 'Validate only; do not save entities.')]
  #[CLI\Option(name: 'skip-no-file', description: 'Skip rows without a readable PDF (default true).')]
  #[CLI\Option(name: 'metadata-only', description: 'Update taxonomy/links/description without replacing field_document.')]
  public function import(array $options = [
    'download' => FALSE,
    'dry-run' => FALSE,
    'skip-no-file' => TRUE,
    'metadata-only' => FALSE,
  ]): void {
    $catalog_path = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . '/data/document_import_official.catalog.php';
    if (!is_readable($catalog_path)) {
      $this->logger()->error(sprintf('Catalog not found: %s', $catalog_path));
      return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = require $catalog_path;
    $meta_path = dirname($catalog_path) . '/document_import_official.meta.php';
    /** @var array<string, array<string, string>> $meta_by_title */
    $meta_by_title = is_readable($meta_path) ? require $meta_path : [];
    $seo_path = dirname($catalog_path) . '/document_import_official.seo.php';
    /** @var array<string, array<string, string>> $seo_by_title */
    $seo_by_title = is_readable($seo_path) ? require $seo_path : [];

    $download = filter_var($options['download'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $skip_no_file = filter_var($options['skip-no-file'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $metadata_only = filter_var($options['metadata-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $official_dir = dirname(\Drupal::root()) . '/data/documents/official';
    if (!$dry_run && !is_dir($official_dir)) {
      mkdir($official_dir, 0755, TRUE);
    }

    $document_type_map = [];
    $tids = $this->entityTypeManager->getStorage('taxonomy_term')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', 'document_type')
      ->execute();
    foreach (Term::loadMultiple($tids) as $term) {
      $document_type_map[$term->label()] = (int) $term->id();
    }

    $term_tid_cache = [];
    $termId = static function (string $vid, string $name) use (&$term_tid_cache): ?int {
      $name = trim($name);
      if ($name === '') {
        return NULL;
      }
      $key = $vid . '|' . $name;
      if (array_key_exists($key, $term_tid_cache)) {
        return $term_tid_cache[$key];
      }
      $ids = \Drupal::entityQuery('taxonomy_term')
        ->accessCheck(FALSE)
        ->condition('vid', $vid)
        ->condition('name', $name)
        ->range(0, 1)
        ->execute();
      $tid = $ids ? (int) reset($ids) : NULL;
      $term_tid_cache[$key] = $tid;
      return $tid;
    };

    $nodeId = static function (string $bundle, string $title): ?int {
      $title = trim($title);
      if ($title === '') {
        return NULL;
      }
      $ids = \Drupal::entityQuery('node')
        ->accessCheck(FALSE)
        ->condition('type', $bundle)
        ->condition('title', $title)
        ->range(0, 1)
        ->execute();
      return $ids ? (int) reset($ids) : NULL;
    };

    $node_storage = $this->entityTypeManager->getStorage('node');
    $media_storage = $this->entityTypeManager->getStorage('media');
    $created = 0;
    $updated = 0;
    $deleted = 0;
    $skipped = 0;
    $errors = [];
    $skipped_titles = [];

    foreach ($rows as $row) {
      $title = trim((string) ($row['title'] ?? ''));
      if ($title === '') {
        continue;
      }

      $action = strtolower(trim((string) ($row['action'] ?? 'update')));

      if ($action === 'delete') {
        $existing = $node_storage->loadByProperties(['type' => 'document', 'title' => $title]);
        if ($existing === []) {
          $this->io()->writeln(sprintf('Delete skipped (not found): %s', $title));
          continue;
        }
        foreach ($existing as $node) {
          if ($dry_run) {
            $this->io()->writeln(sprintf('[dry-run] Delete nid=%d: %s', $node->id(), $title));
          }
          else {
            $node->delete();
          }
          $deleted++;
        }
        continue;
      }

      if (isset($meta_by_title[$title])) {
        $row = array_replace($row, $meta_by_title[$title]);
      }
      if (isset($seo_by_title[$title])) {
        $row = array_replace($row, $seo_by_title[$title]);
      }
      $basename = trim((string) ($row['file_basename'] ?? ''));
      $source_url = trim((string) ($row['source_url'] ?? ''));
      $local_path = $basename !== '' ? $official_dir . '/' . $basename : '';

      if ($download && $basename !== '' && $source_url !== '' && !is_readable($local_path)) {
        $ok = $this->downloadPdf($source_url, $local_path);
        if ($ok) {
          $this->io()->writeln(sprintf('Downloaded: %s', $basename));
        }
        else {
          $errors[] = "Download failed for: {$title} ({$source_url})";
        }
      }

      $has_file = $local_path !== '' && is_readable($local_path);
      if (!$has_file && $skip_no_file && !$metadata_only) {
        $note = trim((string) ($row['note'] ?? ''));
        $skipped++;
        $skipped_titles[] = $title . ($note !== '' ? " ({$note})" : '');
        $this->io()->warning(sprintf('Skipped (no PDF): %s%s', $title, $note !== '' ? " — {$note}" : ''));
        continue;
      }

      $type_label = trim((string) ($row['field_document_type'] ?? ''));
      if ($type_label === '' || !isset($document_type_map[$type_label])) {
        $errors[] = "Unknown document_type \"{$type_label}\" for: {$title}";
        continue;
      }

      $existing = $node_storage->loadByProperties(['type' => 'document', 'title' => $title]);
      if ($existing) {
        if ($action === 'create') {
          $this->io()->writeln(sprintf('Exists (updating metadata): %s', $title));
        }
        $first = reset($existing);
        $node = $node_storage->load($first->id());
        assert($node instanceof Node);
      }
      elseif ($action === 'update') {
        $skipped++;
        $this->io()->warning(sprintf('Update skipped (missing node): %s', $title));
        continue;
      }
      else {
        $node = Node::create(['type' => 'document', 'title' => $title, 'uid' => 1]);
      }

      $node->set('field_short_description', trim((string) ($row['field_short_description'] ?? '')));
      $node->set('field_document_type', ['target_id' => $document_type_map[$type_label]]);
      $node->set('status', 1);

      $feat = trim((string) ($row['field_featured'] ?? '0'));
      $node->set('field_featured', (bool) (int) $feat);

      $year = trim((string) ($row['field_year'] ?? ''));
      if ($year !== '' && ctype_digit($year)) {
        $node->set('field_year', (int) $year);
      }

      $link = trim((string) ($row['field_source_link'] ?? ''));
      if ($link !== '') {
        $node->set('field_source_link', [['uri' => $link, 'title' => '']]);
      }

      $cat = trim((string) ($row['field_category'] ?? ''));
      if ($cat !== '') {
        $tid = $termId('document_category', $cat);
        if ($tid) {
          $node->set('field_category', ['target_id' => $tid]);
        }
      }

      $sector = trim((string) ($row['field_sector'] ?? ''));
      if ($sector !== '') {
        $tid = $termId('sector', $sector);
        if ($tid) {
          $node->set('field_sector', ['target_id' => $tid]);
          require_once \Drupal::root() . '/modules/custom/motaded_custom/includes/motaded_custom.document_sector_pages.inc';
          motaded_custom_document_apply_sector_pages_from_sector($node, FALSE);
        }
      }

      $service_type = trim((string) ($row['field_taxonomy'] ?? ''));
      if ($service_type !== '') {
        $tid = $termId('taxonomy', $service_type);
        if ($tid) {
          $node->set('field_taxonomy', ['target_id' => $tid]);
        }
        else {
          $errors[] = "Unknown service type \"{$service_type}\" for: {$title}";
        }
      }

      $plat = trim((string) ($row['field_platforms'] ?? ''));
      if ($plat !== '') {
        $targets = [];
        foreach (preg_split('/\s*;\s*/', $plat, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $ptitle) {
          $pnid = $nodeId('platform', $ptitle);
          if ($pnid) {
            $targets[] = ['target_id' => $pnid];
          }
          else {
            $errors[] = "No platform \"{$ptitle}\" for: {$title}";
          }
        }
        $node->set('field_platforms', $targets ?: NULL);
      }

      if ($has_file && !$metadata_only) {
        $binary = file_get_contents($local_path);
        if ($binary === FALSE) {
          $errors[] = "Could not read file for: {$title}";
          continue;
        }
        $dest = 'public://documents/official/' . $basename;
        $directory = 'public://documents/official';
        $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
        $file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);

        $media_name = 'Official: ' . $title;
        $existing_media = $media_storage->loadByProperties([
          'bundle' => 'document',
          'name' => $media_name,
        ]);
        if ($existing_media) {
          /** @var \Drupal\media\MediaInterface $media */
          $media = reset($existing_media);
          $media->set('field_media_document', $file->id());
          $media->set('status', 1);
        }
        else {
          $media = Media::create([
            'bundle' => 'document',
            'uid' => 1,
            'name' => $media_name,
            'field_media_document' => ['target_id' => $file->id()],
            'status' => 1,
          ]);
        }

        if (!$dry_run) {
          $media->save();
          $node->set('field_document', ['target_id' => $media->id()]);
        }
      }

      motaded_custom_apply_node_metatags($node, $row);

      if ($dry_run) {
        $this->output()->writeln(sprintf('[dry-run] %s: %s', $action, $title));
        continue;
      }

      $is_new = $node->isNew();
      $node->save();
      if ($is_new) {
        $created++;
      }
      else {
        $updated++;
      }
    }

    $this->io()->success(sprintf('Created %d, updated %d, deleted %d, skipped %d.', $created, $updated, $deleted, $skipped));
    if ($skipped_titles !== []) {
      $this->io()->writeln('Skipped (add PDF to data/documents/official/ then re-run mdoc):');
      foreach ($skipped_titles as $label) {
        $this->io()->writeln('  - ' . $label);
      }
    }
    if ($errors !== []) {
      $this->output()->writeln('Warnings (' . count($errors) . '):');
      foreach (array_slice($errors, 0, 25) as $err) {
        $this->output()->writeln('  - ' . $err);
      }
    }
  }

  private function downloadPdf(string $url, string $dest): bool {
    $dir = dirname($dest);
    if (!is_dir($dir)) {
      mkdir($dir, 0755, TRUE);
    }

    $ch = curl_init($url);
    if ($ch === FALSE) {
      return FALSE;
    }
    $fp = fopen($dest, 'wb');
    if ($fp === FALSE) {
      curl_close($ch);
      return FALSE;
    }
    curl_setopt_array($ch, [
      CURLOPT_FILE => $fp,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_TIMEOUT => 120,
      CURLOPT_USERAGENT => 'MotadedDocumentImport/1.0',
      CURLOPT_SSL_VERIFYPEER => TRUE,
    ]);
    $ok = curl_exec($ch) !== FALSE && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);
    fclose($fp);
    if (!$ok && is_file($dest)) {
      unlink($dest);
    }
    return $ok && is_readable($dest) && filesize($dest) > 1024;
  }

}
