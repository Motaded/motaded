<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\media\Entity\Media;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import event nodes from PHP catalog (official / curated data).
 */
final class EventImportOfficialCommands extends DrushCommands {

  private const IMAGE_DIR_REL = '/data/events/images';

  private const OFFICIAL_DIR_REL = '/data/events/official';

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {
    parent::__construct();
  }

  /**
   * Import events from official PHP catalog.
   */
  #[CLI\Command(name: 'motaded:event-import-official', aliases: ['mevent'])]
  #[CLI\Option(name: 'purge', description: 'Delete all existing event nodes before import.')]
  #[CLI\Option(name: 'download-images', description: 'Download missing hero images from hero_image_url into data/events/images/.')]
  #[CLI\Option(name: 'images-only', description: 'Update hero/featured images on existing events (match by title).')]
  #[CLI\Option(name: 'content-only', description: 'Update body/summary (About the event) on existing events (match by title).')]
  #[CLI\Option(name: 'meta-only', description: 'Update field_meta (SEO) on existing events (match by title).')]
  #[CLI\Option(name: 'dry-run', description: 'Validate only; do not save entities.')]
  #[CLI\Option(name: 'limit', description: 'Max catalog rows to process (0 = all).')]
  public function import(array $options = [
    'purge' => FALSE,
    'download-images' => FALSE,
    'images-only' => FALSE,
    'content-only' => FALSE,
    'meta-only' => FALSE,
    'dry-run' => FALSE,
    'limit' => 0,
  ]): void {
    $catalog_path = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . '/data/event_import_official.catalog.php';
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
    $meta_only = filter_var($options['meta-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $limit = max(0, (int) ($options['limit'] ?? 0));

    if ($purge && ($images_only || $content_only || $meta_only)) {
      $this->logger()->error('Use --purge alone, or --images-only / --content-only / --meta-only without --purge.');
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
        ->condition('type', 'event')
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
      $this->io()->writeln(sprintf($dry_run ? '[dry-run] Would purge %d event node(s).' : 'Purged %d event node(s).', $deleted));
    }

    $term_tid_cache = [];
    $created = 0;
    $updated_images = 0;
    $updated_content = 0;
    $updated_meta = 0;
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
            $basename = trim((string) ($row['hero_image_basename'] ?? ''));
            if ($basename !== '') {
              $bundled = $official_dir . '/' . $basename;
              $source = is_readable($bundled) && $this->isValidImageFile($bundled)
                ? 'bundled:' . $basename
                : 'url:' . ($row['hero_image_url'] ?? '');
              $this->io()->writeln(sprintf('  image: %s (%s)', $basename, $source));
            }
          }
          elseif ($content_only) {
            $body = trim((string) ($row['body'] ?? ''));
            $this->io()->writeln(sprintf('[dry-run] Update content: %s (%d chars)', $title, strlen($body)));
          }
          elseif ($meta_only) {
            $this->io()->writeln(sprintf('[dry-run] Update meta: %s', $title));
          }
          else {
            $this->io()->writeln(sprintf('[dry-run] Import: %s', $title));
          }
          continue;
        }

        if ($content_only) {
          $nids = $node_storage->getQuery()
            ->accessCheck(FALSE)
            ->condition('type', 'event')
            ->condition('title', $title)
            ->execute();
          if ($nids === []) {
            $errors[] = sprintf('%s: no matching event node', $title);
            continue;
          }
          foreach ($nids as $nid) {
            $node = $node_storage->load((int) $nid);
            if (!$node instanceof Node) {
              continue;
            }
            if ($this->applyEventBody($node, $row)) {
              motaded_custom_apply_node_metatags($node, $row, 'field_meta');
              $node->save();
              $this->io()->writeln(sprintf('Updated content nid=%d: %s', $nid, $title));
              $updated_content++;
            }
            else {
              $errors[] = sprintf('%s (nid=%d): empty body in catalog', $title, $nid);
            }
          }
          continue;
        }

        if ($meta_only) {
          $nids = $node_storage->getQuery()
            ->accessCheck(FALSE)
            ->condition('type', 'event')
            ->condition('title', $title)
            ->execute();
          if ($nids === []) {
            $errors[] = sprintf('%s: no matching event node', $title);
            continue;
          }
          foreach ($nids as $nid) {
            $node = $node_storage->load((int) $nid);
            if (!$node instanceof Node) {
              continue;
            }
            motaded_custom_apply_node_metatags($node, $row, 'field_meta');
            $node->save();
            $this->io()->writeln(sprintf('Updated meta nid=%d: %s', $nid, $title));
            $updated_meta++;
          }
          continue;
        }

        if ($images_only) {
          $nids = $node_storage->getQuery()
            ->accessCheck(FALSE)
            ->condition('type', 'event')
            ->condition('title', $title)
            ->execute();
          if ($nids === []) {
            $errors[] = sprintf('%s: no matching event node', $title);
            continue;
          }
          foreach ($nids as $nid) {
            $node = $node_storage->load((int) $nid);
            if (!$node instanceof Node) {
              continue;
            }
            if ($this->attachEventImages($node, $row, $image_dir, $official_dir, $title, $download_images)) {
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

        $existing_nids = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'event')
          ->condition('title', $title)
          ->range(0, 1)
          ->execute();
        if ($existing_nids !== []) {
          $this->io()->writeln(sprintf('Skip (exists nid=%d): %s', (int) reset($existing_nids), $title));
          continue;
        }

        $node = Node::create([
          'type' => 'event',
          'title' => $title,
          'uid' => 1,
          'langcode' => 'en',
          'status' => 1,
        ]);

        $summary = trim((string) ($row['body_summary'] ?? ''));
        $body = trim((string) ($row['body'] ?? ''));
        $node->set('body', [
          'value' => $body,
          'summary' => $summary,
          'format' => 'basic_html',
        ]);

        $node->set('field_event_start', $this->normalizeDatetime((string) ($row['field_event_start'] ?? ''), FALSE));
        $node->set('field_event_end', $this->normalizeDatetime((string) ($row['field_event_end'] ?? ''), TRUE));

        $event_type = trim((string) ($row['field_event_type'] ?? ''));
        if ($event_type !== '') {
          $node->set('field_event_type', ['target_id' => $this->getOrCreateTerm('event_type', $event_type, 'en', $term_tid_cache)]);
        }

        foreach (['field_city' => 'city', 'field_country' => 'country', 'field_region' => 'region'] as $field => $vid) {
          $name = trim((string) ($row[$field] ?? ''));
          if ($name !== '') {
            $node->set($field, ['target_id' => $this->getOrCreateTerm($vid, $name, 'en', $term_tid_cache)]);
          }
        }

        $location = trim((string) ($row['field_location'] ?? ''));
        if ($location !== '') {
          $node->set('field_location', $location);
        }

        $node->set('field_featured', (bool) (int) ($row['field_featured'] ?? '0'));
        $node->set('field_meeting_enabled', (bool) (int) ($row['field_meeting_enabled'] ?? '0'));

        $format = $this->normalizeEventFormat((string) ($row['field_event_format'] ?? ''));
        if ($format !== '') {
          $node->set('field_event_format', $format);
        }
        $price = $this->normalizePriceRange((string) ($row['field_price_range'] ?? ''));
        if ($price !== '') {
          $node->set('field_price_range', $price);
        }
        $price_amount = trim((string) ($row['field_event_price'] ?? ''));
        if ($price_amount !== '' && is_numeric($price_amount)) {
          $node->set('field_event_price', $price_amount);
        }
        $attendance = $this->normalizeAttendanceType((string) ($row['field_attendance_type'] ?? ''));
        if ($attendance !== '') {
          $node->set('field_attendance_type', $attendance);
        }

        $cta_type = $this->normalizeCtaType((string) ($row['field_cta_type'] ?? 'register'));
        $node->set('field_cta_type', $cta_type);

        $cta = $this->buildLinkValue((string) ($row['field_cta_link_uri'] ?? ''), (string) ($row['field_cta_link_title'] ?? 'Register'));
        if ($cta !== NULL) {
          $node->set('field_cta_link', [$cta]);
        }
        $secondary = $this->buildLinkValue((string) ($row['field_secondary_cta_uri'] ?? ''), (string) ($row['field_secondary_cta_title'] ?? ''));
        if ($secondary !== NULL) {
          $node->set('field_secondary_cta', [$secondary]);
        }
        $official = $this->buildLinkValue((string) ($row['field_official_website'] ?? ''), (string) ($row['field_official_website_title'] ?? 'Official website'));
        if ($official !== NULL) {
          $node->set('field_official_website', [$official]);
        }

        $organizers = $this->resolveTermList('event_organizer', (string) ($row['field_organizer'] ?? ''), 'en', $term_tid_cache);
        if ($organizers !== []) {
          $node->set('field_organizer', array_map(static fn (int $tid): array => ['target_id' => $tid], $organizers));
        }
        $tags = $this->resolveTermList('tags', (string) ($row['field_tags'] ?? ''), 'en', $term_tid_cache);
        if ($tags !== []) {
          $node->set('field_tags', array_map(static fn (int $tid): array => ['target_id' => $tid], $tags));
        }

        $sector_nids = $this->resolveSectorPageNids((string) ($row['field_sector_pages'] ?? ''));
        if ($sector_nids !== []) {
          $node->set('field_sector_pages', array_map(static fn (int $nid): array => ['target_id' => $nid], $sector_nids));
        }

        $this->attachParagraphSections($node, $row);

        $this->attachEventImages($node, $row, $image_dir, $official_dir, $title, $download_images);
        motaded_custom_apply_node_metatags($node, $row, 'field_meta');

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
      $this->io()->success(sprintf('Updated images on %d event node(s).', $updated_images));
    }
    elseif ($content_only) {
      $this->io()->success(sprintf('Updated content on %d event node(s).', $updated_content));
    }
    elseif ($meta_only) {
      $this->io()->success(sprintf('Updated meta on %d event node(s).', $updated_meta));
    }
    else {
      $this->io()->success(sprintf('Created %d event(s). Purged %d.', $created, $deleted));
    }
    if ($errors !== []) {
      $this->io()->warning(sprintf('%d error(s):', count($errors)));
      foreach (array_slice($errors, 0, 15) as $err) {
        $this->io()->writeln('  - ' . $err);
      }
    }
  }

  /**
   * @param array<string, mixed> $row
   */
  private function attachParagraphSections(Node $node, array $row): void {
    $map = [
      'why_attend' => 'field_why_attend',
      'agenda' => 'field_agenda',
      'what_we_offer' => 'field_what_we_offer',
      'who_should_attend' => 'field_who_should_attend',
    ];
    foreach ($map as $key => $field_name) {
      if (!isset($row[$key]) || !is_array($row[$key])) {
        continue;
      }
      $node->set($field_name, []);
      foreach ($row[$key] as $item) {
        if (!is_array($item)) {
          continue;
        }
        $p_title = trim((string) ($item['title'] ?? ''));
        $p_body = trim((string) ($item['body'] ?? ''));
        if ($p_title === '' && $p_body === '') {
          continue;
        }
        $p = Paragraph::create([
          'type' => 'event_content_item',
          'langcode' => 'en',
          'field_title' => $p_title,
          'field_body' => [
            'value' => $this->normalizeBodyHtml($p_body),
            'format' => 'basic_html',
          ],
        ]);
        $p->save();
        $node->get($field_name)->appendItem([
          'target_id' => (int) $p->id(),
          'target_revision_id' => (int) $p->getRevisionId(),
        ]);
      }
    }
  }

  /**
   * @param array<string, mixed> $row
   */
  private function applyEventBody(Node $node, array $row): bool {
    $summary = trim((string) ($row['body_summary'] ?? ''));
    $body = trim((string) ($row['body'] ?? ''));
    if ($body === '') {
      return FALSE;
    }
    $node->set('body', [
      'value' => $this->normalizeBodyHtml($body),
      'summary' => $summary,
      'format' => 'basic_html',
    ]);
    return TRUE;
  }

  /**
   * @param array<string, mixed> $row
   */
  private function attachEventImages(Node $node, array $row, string $image_dir, string $official_dir, string $title, bool $allow_url_download): bool {
    $hero_mid = $this->resolveImageMedia($row, 'hero', $image_dir, $official_dir, $title, 0, $allow_url_download);
    if ($hero_mid <= 0) {
      return FALSE;
    }
    $node->set('field_hero_image', ['target_id' => $hero_mid]);
    $featured_mid = $this->resolveImageMedia($row, 'featured', $image_dir, $official_dir, $title, $hero_mid, $allow_url_download);
    if ($featured_mid > 0) {
      $node->set('field_featured_image', ['target_id' => $featured_mid]);
    }
    return TRUE;
  }

  /**
   * @param array<string, mixed> $row
   */
  private function resolveImageMedia(array $row, string $role, string $image_dir, string $official_dir, string $title, int $fallback_mid = 0, bool $allow_url_download = FALSE): int {
    $prefix = $role === 'featured' ? 'featured_image' : 'hero_image';
    $basename = trim((string) ($row[$prefix . '_basename'] ?? ''));
    $url = trim((string) ($row[$prefix . '_url'] ?? ''));
    $referer = trim((string) ($row[$prefix . '_referer'] ?? ''));
    if ($basename === '' && $role === 'featured') {
      $basename = trim((string) ($row['hero_image_basename'] ?? ''));
      $url = trim((string) ($row['hero_image_url'] ?? ''));
      $referer = trim((string) ($row['hero_image_referer'] ?? ''));
    }
    if ($basename === '') {
      return $role === 'featured' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $local = $this->resolveLocalImagePath($basename, $url, $referer, $image_dir, $official_dir, $allow_url_download);
    if ($local === NULL) {
      return $role === 'featured' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $binary = file_get_contents($local);
    if ($binary === FALSE || !$this->isValidImageBinary($binary)) {
      return $role === 'featured' && $fallback_mid > 0 ? $fallback_mid : 0;
    }

    $ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    $dest = 'public://events/official/' . $basename;
    $directory = 'public://events/official';
    $this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);

    $media_name = 'Event ' . ucfirst($role) . ': ' . $title;
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

  private function resolveLocalImagePath(string $basename, string $url, string $referer, string $image_dir, string $official_dir, bool $allow_url_download): ?string {
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

    if ($this->downloadImage($url, $cached, $referer) && $this->isValidImageFile($cached)) {
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

  private function downloadImage(string $url, string $dest, string $referer = ''): bool {
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
    $headers = ['Accept: image/*,*/*;q=0.8'];
    if ($referer !== '') {
      $headers[] = 'Referer: ' . $referer;
    }
    curl_setopt_array($ch, [
      CURLOPT_FILE => $fp,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_TIMEOUT => 90,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MotadedEventImport/1.0)',
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_HTTPHEADER => $headers,
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
   * @return int[]
   */
  private function resolveSectorPageNids(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
      return [];
    }
    $storage = $this->entityTypeManager->getStorage('node');
    $nids = [];
    foreach (preg_split('/\s*[|;]\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $sector_title) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'sector_page')
        ->condition('title', trim($sector_title))
        ->range(0, 1)
        ->execute();
      if ($ids) {
        $nids[] = (int) reset($ids);
      }
    }
    return array_values(array_unique($nids));
  }

  /**
   * @param array<string, int|null> $cache
   */
  private function getOrCreateTerm(string $vid, string $name, string $langcode, array &$cache): int {
    $name = trim($name);
    $key = $vid . '|' . $name;
    if (isset($cache[$key])) {
      return (int) $cache[$key];
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
      return (int) $cache[$key];
    }
    $term = Term::create(['vid' => $vid, 'name' => $name, 'langcode' => $langcode]);
    $term->save();
    $cache[$key] = (int) $term->id();
    $this->io()->writeln(sprintf('Created term %s / %s (tid=%d)', $vid, $name, (int) $term->id()));
    return (int) $cache[$key];
  }

  /**
   * @param array<string, int|null> $cache
   * @return int[]
   */
  private function resolveTermList(string $vid, string $raw, string $langcode, array &$cache): array {
    if (trim($raw) === '') {
      return [];
    }
    $parts = preg_split('/\s*[|,]\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $ids = [];
    foreach ($parts as $name) {
      $ids[] = $this->getOrCreateTerm($vid, $name, $langcode, $cache);
    }
    return array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
  }

  private function normalizeDatetime(string $raw, bool $endOfDay): string {
    $raw = trim($raw);
    if ($raw === '') {
      throw new \InvalidArgumentException('Empty datetime.');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
      return $raw . ($endOfDay ? 'T17:00:00' : 'T09:00:00');
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?/', $raw)) {
      return str_replace(' ', 'T', $raw);
    }
    throw new \InvalidArgumentException('Invalid datetime: ' . $raw);
  }

  private function normalizeBodyHtml(string $raw): string {
    $raw = trim($raw);
    if ($raw === '') {
      return '';
    }
    if (str_contains($raw, '<') && preg_match('/<[a-z][\s\S]*>/i', $raw)) {
      return $raw;
    }
    return '<p>' . htmlspecialchars($raw, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
  }

  private function normalizeEventFormat(string $raw): string {
    $raw = strtolower(trim($raw));
    return match ($raw) {
      'in-person', 'in_person', 'inperson', 'in person' => 'in_person',
      'online' => 'online',
      'hybrid' => 'hybrid',
      default => '',
    };
  }

  private function normalizeCtaType(string $raw): string {
    $raw = strtolower(trim($raw));
    return match ($raw) {
      'register', 'registration' => 'register',
      'meeting', 'book meeting', 'book_a_meeting' => 'meeting',
      'external', 'website', 'visit' => 'external',
      default => in_array($raw, ['register', 'meeting', 'external'], TRUE) ? $raw : 'register',
    };
  }

  private function normalizePriceRange(string $raw): string {
    $raw = strtolower(trim($raw));
    return match ($raw) {
      'free' => 'free',
      'paid' => 'paid',
      'invite only', 'invite-only', 'invite_only' => 'invite_only',
      default => '',
    };
  }

  private function normalizeAttendanceType(string $raw): string {
    $raw = strtolower(trim($raw));
    return match ($raw) {
      'public' => 'public',
      'private' => 'private',
      'invite only', 'invite-only', 'invite_only' => 'invite_only',
      default => '',
    };
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

}
