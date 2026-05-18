<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import market_insight nodes from PHP catalog (official / curated data).
 */
final class MarketInsightImportOfficialCommands extends DrushCommands {

  private const IMAGE_DIR_REL = '/data/market_insights/images';

  private const OFFICIAL_DIR_REL = '/data/market_insights/official';

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {
    parent::__construct();
  }

  /**
   * Import market insights from official PHP catalog.
   */
  #[CLI\Command(name: 'motaded:market-insight-import-official', aliases: ['minsight'])]
  #[CLI\Option(name: 'purge', description: 'Delete all existing market_insight nodes before import.')]
  #[CLI\Option(name: 'download-images', description: 'Download missing hero images into data/market_insights/images/.')]
  #[CLI\Option(name: 'images-only', description: 'Update hero/thumbnail images on existing insights (match by title).')]
  #[CLI\Option(name: 'content-only', description: 'Update body/summary/fields on existing insights (match by title).')]
  #[CLI\Option(name: 'dry-run', description: 'Validate only; do not save entities.')]
  #[CLI\Option(name: 'limit', description: 'Max catalog rows to process (0 = all).')]
  public function import(array $options = [
    'purge' => FALSE,
    'download-images' => FALSE,
    'images-only' => FALSE,
    'content-only' => FALSE,
    'dry-run' => FALSE,
    'limit' => 0,
  ]): void {
    $catalog_path = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . '/data/market_insight_import_official.catalog.php';
    if (!is_readable($catalog_path)) {
      $this->logger()->error(sprintf('Catalog not found: %s', $catalog_path));
      return;
    }

    /** @var list<array<string, mixed>> $rows */
    $rows = require $catalog_path;
    $purge = filter_var($options['purge'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $download_images = filter_var($options['download-images'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $images_only = filter_var($options['images-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $content_only = filter_var($options['content-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $limit = max(0, (int) ($options['limit'] ?? 0));

    if ($purge && ($images_only || $content_only)) {
      $this->logger()->error('Use --purge alone, or --images-only / --content-only without --purge.');
      return;
    }

    $image_dir = dirname(\Drupal::root()) . self::IMAGE_DIR_REL;
    $official_dir = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . self::OFFICIAL_DIR_REL;
    if (!$dry_run && $download_images && !is_dir($image_dir)) {
      mkdir($image_dir, 0755, TRUE);
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $deleted = 0;

    if ($purge) {
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'market_insight')
        ->execute();
      foreach ($nids as $nid) {
        $node = $node_storage->load((int) $nid);
        if (!$node) {
          continue;
        }
        if ($dry_run) {
          $this->io()->writeln(sprintf('[dry-run] Purge nid=%d: %s', $nid, $node->label()));
        }
        else {
          $node->delete();
        }
        $deleted++;
      }
      $this->io()->writeln(sprintf(
        $dry_run ? '[dry-run] Would purge %d market_insight node(s).' : 'Purged %d market_insight node(s).',
        $deleted
      ));
    }

    $term_tid_cache = [];
    $created = 0;
    $updated_images = 0;
    $updated_content = 0;
    $errors = [];
    $processed = 0;

    foreach ($rows as $row) {
      if ($limit > 0 && $processed >= $limit) {
        break;
      }
      $title = trim((string) ($row['title'] ?? ''));
      if ($title === '') {
        continue;
      }
      $processed++;

      try {
        if ($dry_run) {
          if ($images_only) {
            $this->io()->writeln(sprintf('[dry-run] Update images: %s', $title));
          }
          elseif ($content_only) {
            $this->io()->writeln(sprintf('[dry-run] Update content: %s', $title));
          }
          else {
            $this->io()->writeln(sprintf('[dry-run] Import: %s', $title));
          }
          continue;
        }

        if ($content_only) {
          $nids = $this->loadInsightNidsByTitle($title);
          if ($nids === []) {
            $errors[] = sprintf('%s: no matching insight node', $title);
            continue;
          }
          foreach ($nids as $nid) {
            $node = $node_storage->load($nid);
            if (!$node instanceof Node) {
              continue;
            }
            $this->applyInsightFields($node, $row, $term_tid_cache);
            $node->save();
            $this->io()->writeln(sprintf('Updated content nid=%d: %s', $nid, $title));
            $updated_content++;
          }
          continue;
        }

        if ($images_only) {
          $nids = $this->loadInsightNidsByTitle($title);
          if ($nids === []) {
            $errors[] = sprintf('%s: no matching insight node', $title);
            continue;
          }
          foreach ($nids as $nid) {
            $node = $node_storage->load($nid);
            if (!$node instanceof Node) {
              continue;
            }
            if ($this->attachInsightImages($node, $row, $image_dir, $official_dir, $title, $download_images)) {
              $node->save();
              $this->io()->writeln(sprintf('Updated images nid=%d: %s', $nid, $title));
              $updated_images++;
            }
            else {
              $errors[] = sprintf('%s (nid=%d): could not resolve hero image', $title, $nid);
            }
          }
          continue;
        }

        $existing_nids = $this->loadInsightNidsByTitle($title);
        if ($existing_nids !== []) {
          $this->io()->writeln(sprintf('Skip (exists nid=%d): %s', reset($existing_nids), $title));
          continue;
        }

        $node = Node::create([
          'type' => 'market_insight',
          'title' => $title,
          'uid' => 1,
          'langcode' => 'en',
          'status' => 1,
          'promote' => 0,
          'field_insight_shell' => 'platform',
        ]);

        $this->applyInsightFields($node, $row, $term_tid_cache);
        $this->attachInsightImages($node, $row, $image_dir, $official_dir, $title, $download_images);

        $node->save();
        $nid = (int) $node->id();

        $alias = trim((string) ($row['path_alias'] ?? ''));
        if ($alias !== '') {
          $alias = '/' . ltrim($alias, '/');
          PathAlias::create([
            'path' => '/node/' . $nid,
            'alias' => $alias,
            'langcode' => 'en',
          ])->save();
        }

        $this->io()->writeln(sprintf('Created nid=%d: %s', $nid, $title));
        $created++;
      }
      catch (\Throwable $e) {
        $errors[] = sprintf('%s: %s', $title, $e->getMessage());
        $this->logger()->error($errors[count($errors) - 1]);
      }
    }

    if ($dry_run) {
      $this->io()->success(sprintf('Dry-run complete (%d row(s)). Purge: %d.', $processed, $deleted));
    }
    elseif ($images_only) {
      $this->io()->success(sprintf('Updated images on %d insight node(s).', $updated_images));
    }
    elseif ($content_only) {
      $this->io()->success(sprintf('Updated content on %d insight node(s).', $updated_content));
    }
    else {
      $this->io()->success(sprintf('Created %d insight(s). Purged %d.', $created, $deleted));
    }

    if ($errors !== []) {
      $this->io()->warning(sprintf('%d error(s):', count($errors)));
      foreach (array_slice($errors, 0, 15) as $err) {
        $this->io()->writeln('  - ' . $err);
      }
    }
  }

  /**
   * @return int[]
   */
  private function loadInsightNidsByTitle(string $title): array {
    $ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'market_insight')
      ->condition('title', $title)
      ->execute();
    return array_map('intval', array_values($ids));
  }

  /**
   * @param array<string, mixed> $row
   * @param array<string, int> $term_tid_cache
   */
  private function applyInsightFields(Node $node, array $row, array &$term_tid_cache): void {
    $summary = trim((string) ($row['body_summary'] ?? ''));
    $body = trim((string) ($row['body'] ?? ''));
    if ($body !== '') {
      $node->set('body', [
        'value' => $body,
        'summary' => $summary,
        'format' => 'basic_html',
      ]);
    }

    $node->set('field_stat_prefix', trim((string) ($row['field_stat_prefix'] ?? '')));
    $node->set('field_stat_value', trim((string) ($row['field_stat_value'] ?? '')));
    $node->set('field_stat_suffix', trim((string) ($row['field_stat_suffix'] ?? '')));
    $node->set('field_stat_label', trim((string) ($row['field_stat_label'] ?? '')));

    $year_raw = trim((string) ($row['field_year'] ?? ''));
    if ($year_raw !== '' && ctype_digit($year_raw)) {
      $node->set('field_year', (int) $year_raw);
    }

    $node->set('field_source_text', trim((string) ($row['field_source_text'] ?? '')));
    $source = $this->buildLinkValue(
      (string) ($row['field_source_link_uri'] ?? ''),
      (string) ($row['field_source_link_title'] ?? '')
    );
    if ($source !== NULL) {
      $node->set('field_source_link', [$source]);
    }

    $node->set('field_featured', (bool) (int) ($row['field_featured'] ?? '0'));
    $node->set('field_highlight', (bool) (int) ($row['field_highlight'] ?? '0'));

    $order_raw = trim((string) ($row['field_order'] ?? ''));
    if ($order_raw !== '' && is_numeric($order_raw)) {
      $node->set('field_order', (int) $order_raw);
    }

    $takeaways = trim((string) ($row['field_key_takeaways'] ?? ''));
    if ($takeaways !== '') {
      $node->set('field_key_takeaways', [
        'value' => $takeaways,
        'format' => 'basic_html',
      ]);
    }

    foreach ([
      'field_category' => 'market_insight_category',
      'field_sector' => 'sector',
      'field_region' => 'region',
    ] as $field => $vid) {
      $name = trim((string) ($row[$field] ?? ''));
      if ($name !== '') {
        $node->set($field, ['target_id' => $this->getOrCreateTerm($vid, $name, 'en', $term_tid_cache)]);
      }
    }

    motaded_custom_apply_node_metatags($node, $row);
  }

  /**
   * @param array<string, mixed> $row
   */
  private function attachInsightImages(Node $node, array $row, string $image_dir, string $official_dir, string $title, bool $allow_url_download): bool {
    $hero_mid = $this->resolveImageMedia($row, 'hero', $image_dir, $official_dir, $title, 0, $allow_url_download);
    if ($hero_mid <= 0) {
      return FALSE;
    }
    $node->set('field_media', ['target_id' => $hero_mid]);
    $thumb_mid = $this->resolveImageMedia($row, 'thumbnail', $image_dir, $official_dir, $title, $hero_mid, $allow_url_download);
    if ($thumb_mid > 0) {
      $node->set('field_insight_thumbnail', ['target_id' => $thumb_mid]);
    }
    return TRUE;
  }

  /**
   * @param array<string, mixed> $row
   */
  private function resolveImageMedia(array $row, string $role, string $image_dir, string $official_dir, string $title, int $fallback_mid = 0, bool $allow_url_download = FALSE): int {
    $prefix = $role === 'thumbnail' ? 'thumbnail_image' : 'hero_image';
    $basename = trim((string) ($row[$prefix . '_basename'] ?? ''));
    $url = trim((string) ($row[$prefix . '_url'] ?? ''));
    if ($basename === '' && $role === 'thumbnail') {
      $basename = trim((string) ($row['hero_image_basename'] ?? ''));
      $url = trim((string) ($row['hero_image_url'] ?? ''));
    }
    if ($basename === '') {
      return $role === 'thumbnail' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $local = $this->resolveLocalImagePath($basename, $url, $image_dir, $official_dir, $allow_url_download);
    if ($local === NULL) {
      return $role === 'thumbnail' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $binary = file_get_contents($local);
    if ($binary === FALSE || !$this->isValidImageBinary($binary)) {
      return $role === 'thumbnail' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $dest = 'public://market-insights/official/' . $basename;
    $directory = 'public://market-insights/official';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);

    $media_name = 'Insight ' . ucfirst($role) . ': ' . $title;
    $existing = $this->entityTypeManager->getStorage('media')->loadByProperties([
      'bundle' => 'image',
      'name' => $media_name,
    ]);
    if ($existing) {
      $media = reset($existing);
      assert($media instanceof Media);
      $media->set('field_media_image', [
        'target_id' => $file->id(),
        'alt' => $title,
      ]);
    }
    else {
      $media = Media::create([
        'bundle' => 'image',
        'uid' => 1,
        'name' => $media_name,
        'field_media_image' => [
          'target_id' => $file->id(),
          'alt' => $title,
        ],
        'status' => 1,
      ]);
    }
    $media->save();
    return (int) $media->id();
  }

  private function resolveLocalImagePath(string $basename, string $url, string $image_dir, string $official_dir, bool $allow_url_download): ?string {
    $bundled = $official_dir . '/' . $basename;
    if (is_readable($bundled) && $this->isValidImageFile($bundled)) {
      return $bundled;
    }

    $cached = $image_dir . '/' . $basename;
    if (is_readable($cached) && $this->isValidImageFile($cached)) {
      return $cached;
    }

    if (!$allow_url_download || $url === '') {
      return NULL;
    }

    if ($this->downloadImage($url, $cached) && $this->isValidImageFile($cached)) {
      return $cached;
    }

    return NULL;
  }

  private function isValidImageFile(string $path): bool {
    if (!is_readable($path)) {
      return FALSE;
    }
    $size = filesize($path);
    if ($size === FALSE || $size < 512) {
      return FALSE;
    }
    $info = @getimagesize($path);
    return $info !== FALSE && !empty($info[0]) && !empty($info[1]);
  }

  private function isValidImageBinary(string $binary): bool {
    $info = @getimagesizefromstring($binary);
    return $info !== FALSE && !empty($info[0]) && !empty($info[1]);
  }

  private function downloadImage(string $url, string $dest): bool {
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
      CURLOPT_TIMEOUT => 90,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MotadedInsightImport/1.0)',
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_HTTPHEADER => ['Accept: image/*,*/*;q=0.8'],
    ]);
    $ok = curl_exec($ch) !== FALSE && (int) curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
    curl_close($ch);
    fclose($fp);
    if (!$ok && is_file($dest)) {
      unlink($dest);
      return FALSE;
    }
    return $ok && $this->isValidImageFile($dest);
  }

  /**
   * @return array{uri: string, title: string}|null
   */
  private function buildLinkValue(string $uri, string $title): ?array {
    $uri = trim($uri);
    if ($uri === '') {
      return NULL;
    }
    if (!str_starts_with($uri, 'internal:')
      && !str_starts_with($uri, 'entity:')
      && !str_starts_with($uri, 'mailto:')
      && !str_starts_with($uri, 'tel:')
      && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $uri)) {
      $uri = 'https://' . ltrim($uri, '/');
    }

    return ['uri' => $uri, 'title' => trim($title)];
  }

  /**
   * @param array<string, int> $cache
   */
  private function getOrCreateTerm(string $vid, string $name, string $langcode, array &$cache): int {
    $name = trim($name);
    $key = $vid . '|' . $name;
    if (isset($cache[$key])) {
      return $cache[$key];
    }
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    if ($tids) {
      $cache[$key] = (int) reset($tids);
      return $cache[$key];
    }
    $term = Term::create(['vid' => $vid, 'name' => $name, 'langcode' => $langcode]);
    $term->save();
    $cache[$key] = (int) $term->id();
    $this->io()->writeln(sprintf('Created term %s / %s (tid=%d)', $vid, $name, $cache[$key]));
    return $cache[$key];
  }

}
