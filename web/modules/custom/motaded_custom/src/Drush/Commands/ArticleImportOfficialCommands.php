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
 * Import article nodes (EN + AR translation) from generated PHP catalog.
 */
final class ArticleImportOfficialCommands extends DrushCommands {

  private const IMAGE_DIR_REL = '/images';

  private const PUBLIC_IMAGE_DIR = 'public://articles/official';

  private const BODY_TEXT_FORMAT = 'full_html';

  private const TAXONOMY_CSV_REL = '/data/article_import_taxonomy.csv';

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {
    parent::__construct();
  }

  /**
   * Import blog articles from spreadsheet catalog (EN node + AR translation).
   */
  #[CLI\Command(name: 'motaded:article-import-official', aliases: ['marticle', 'mai'])]
  #[CLI\Option(name: 'purge', description: 'Delete all article nodes before import.')]
  #[CLI\Option(name: 'dry-run', description: 'Validate only; do not save entities.')]
  #[CLI\Option(name: 'limit', description: 'Max article pairs to process (0 = all).')]
  #[CLI\Option(name: 'update', description: 'Update existing nodes matched by English path alias.')]
  #[CLI\Option(name: 'images-only', description: 'Update field_media on existing articles only.')]
  #[CLI\Option(name: 'taxonomy-only', description: 'Update Sector/Tags/Service type from CSV on existing articles only.')]
  #[CLI\Option(name: 'no-images', description: 'Skip hero image import.')]
  #[CLI\Option(name: 'no-taxonomy', description: 'Skip taxonomy import from CSV.')]
  #[CLI\Option(name: 'image-dir', description: 'Directory with article JPGs (default: project /images).')]
  #[CLI\Option(name: 'taxonomy-csv', description: 'Taxonomy CSV path (default: project data/article_import_taxonomy.csv).')]
  public function import(array $options = [
    'purge' => FALSE,
    'dry-run' => FALSE,
    'limit' => 0,
    'update' => FALSE,
    'images-only' => FALSE,
    'taxonomy-only' => FALSE,
    'no-images' => FALSE,
    'no-taxonomy' => FALSE,
    'image-dir' => '',
    'taxonomy-csv' => '',
  ]): void {
    $catalog_path = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . '/data/article_import_official.catalog.php';
    if (!is_readable($catalog_path)) {
      $this->logger()->error(sprintf(
        'Catalog not found: %s. Run: python3 web/modules/custom/motaded_custom/scripts/build_article_import_catalog.py',
        $catalog_path,
      ));
      return;
    }

    /** @var array<string, array{en: array<string, mixed>, ar: array<string, mixed>}> $pairs */
    $pairs = require $catalog_path;
    ksort($pairs, SORT_NUMERIC);

    $purge = filter_var($options['purge'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $update = filter_var($options['update'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $images_only = filter_var($options['images-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $taxonomy_only = filter_var($options['taxonomy-only'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $skip_images = filter_var($options['no-images'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $skip_taxonomy = filter_var($options['no-taxonomy'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $limit = max(0, (int) ($options['limit'] ?? 0));
    $partial_only = $images_only || $taxonomy_only;
    if ($taxonomy_only) {
      $skip_images = TRUE;
    }

    if ($partial_only && ($purge || !$update)) {
      $this->logger()->error('Use --images-only or --taxonomy-only with --update (without --purge).');
      return;
    }
    if ($images_only && $taxonomy_only) {
      $this->logger()->error('Use --images-only or --taxonomy-only separately, not both.');
      return;
    }

    $image_dir = trim((string) ($options['image-dir'] ?? ''));
    if ($image_dir === '') {
      $image_dir = dirname(\Drupal::root()) . self::IMAGE_DIR_REL;
    }
    elseif (!str_starts_with($image_dir, '/')) {
      $image_dir = getcwd() . '/' . ltrim($image_dir, '/');
    }

    if (!$skip_images && !$taxonomy_only && !is_dir($image_dir)) {
      $this->logger()->error(sprintf('Image directory not found: %s', $image_dir));
      return;
    }

    $taxonomy_csv = trim((string) ($options['taxonomy-csv'] ?? ''));
    if ($taxonomy_csv === '') {
      $taxonomy_csv = dirname(\Drupal::root()) . self::TAXONOMY_CSV_REL;
    }
    elseif (!str_starts_with($taxonomy_csv, '/')) {
      $taxonomy_csv = getcwd() . '/' . ltrim($taxonomy_csv, '/');
    }

    /** @var array<string, array<string, string>> $taxonomy_by_id */
    $taxonomy_by_id = [];
    if (!$skip_taxonomy) {
      if (!is_readable($taxonomy_csv)) {
        $this->logger()->error(sprintf('Taxonomy CSV not found: %s', $taxonomy_csv));
        return;
      }
      $taxonomy_by_id = $this->loadTaxonomyCsv($taxonomy_csv);
      if ($taxonomy_by_id === []) {
        $this->logger()->error(sprintf('Taxonomy CSV is empty: %s', $taxonomy_csv));
        return;
      }
    }

    $term_tid_cache = [];
    $node_storage = $this->entityTypeManager->getStorage('node');
    $deleted = 0;

    if ($purge && !$dry_run) {
      $nids = $node_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'article')
        ->execute();
      if ($nids !== []) {
        $nodes = Node::loadMultiple($nids);
        $node_storage->delete($nodes);
        $deleted = count($nids);
        $this->io()->writeln(sprintf('Purged %d article node(s).', $deleted));
      }
    }

    $created = 0;
    $updated = 0;
    $updated_images = 0;
    $updated_taxonomy = 0;
    $taxonomy_applied = 0;
    $skipped = 0;
    $errors = [];
    $warnings = [];
    $processed = 0;

    foreach ($pairs as $pair_key => $pair) {
      $pair_key = (string) $pair_key;
      if ($limit > 0 && $processed >= $limit) {
        break;
      }
      $processed++;

      $en_row = $this->normalizeRow($pair['en'] ?? [], 'en');
      $ar_row = $this->normalizeRow($pair['ar'] ?? [], 'ar');

      $en_title = $en_row['title'];
      if ($en_title === '') {
        $errors[] = sprintf('Pair %s: empty English title.', $pair_key);
        continue;
      }

      if (strlen($en_row['body']) >= 32767) {
        $warnings[] = sprintf('Pair %s EN: body at Excel char limit — may be truncated.', $pair_key);
      }
      if (strlen($ar_row['body']) >= 32767) {
        $warnings[] = sprintf('Pair %s AR: body at Excel char limit — may be truncated.', $pair_key);
      }

      $taxonomy_id = $pair_key . '_en';
      $taxonomy_row = $taxonomy_by_id[$taxonomy_id] ?? NULL;

      try {
        $existing_nid = motaded_custom_nid_by_path_alias($en_row['path_alias'], 'article', 'en');
        if ($existing_nid !== NULL && !$update && !$partial_only) {
          $this->io()->writeln(sprintf('Skip pair %s (exists nid=%d): %s', $pair_key, $existing_nid, $en_title));
          $skipped++;
          continue;
        }

        if ($dry_run) {
          if (!$partial_only) {
            $this->validateMetatags($en_row, $en_title);
            $this->validateMetatags($ar_row, $ar_row['title'] ?: $en_title);
          }
          if (!$skip_images) {
            foreach (['en' => $en_row, 'ar' => $ar_row] as $lang => $row) {
              $path = $this->resolveLocalImagePath($pair_key, $lang, $row['image'], $image_dir);
              if ($path === NULL && $row['image'] !== '') {
                $warnings[] = sprintf('Pair %s %s: image not found (%s).', $pair_key, $lang, $row['image']);
              }
            }
          }
          if (!$skip_taxonomy && $taxonomy_row === NULL) {
            $warnings[] = sprintf('Pair %s: no taxonomy row for id %s.', $pair_key, $taxonomy_id);
          }
          $this->io()->writeln(sprintf('[dry-run] OK pair %s: %s', $pair_key, $en_title));
          continue;
        }

        if ($existing_nid !== NULL) {
          $node = Node::load($existing_nid);
          if ($node === NULL) {
            $errors[] = sprintf('Pair %s: could not load nid=%d.', $pair_key, $existing_nid);
            continue;
          }
          $needs_save = FALSE;
          if (!$partial_only) {
            $this->applyTranslation($node, 'en', $en_row);
            $this->applyTranslation($node, 'ar', $ar_row);
            $this->savePathAliases($node, $en_row, $ar_row);
            $updated++;
            $needs_save = TRUE;
            $this->io()->writeln(sprintf('Updated nid=%d pair %s: %s', $existing_nid, $pair_key, $en_title));
          }
          if (!$skip_images && $this->attachArticleImages($node, $pair_key, $en_row, $ar_row, $image_dir)) {
            $updated_images++;
            $needs_save = TRUE;
            if ($images_only) {
              $this->io()->writeln(sprintf('Updated images nid=%d pair %s: %s', $existing_nid, $pair_key, $en_title));
            }
          }
          if (!$skip_taxonomy && $taxonomy_row !== NULL && $this->applyArticleTaxonomy($node, $taxonomy_row, $term_tid_cache, $warnings)) {
            $updated_taxonomy++;
            $taxonomy_applied++;
            $needs_save = TRUE;
            if ($taxonomy_only) {
              $this->io()->writeln(sprintf('Updated taxonomy nid=%d pair %s: %s', $existing_nid, $pair_key, $en_title));
            }
          }
          if ($needs_save) {
            $node->save();
          }
        }
        else {
          if ($partial_only) {
            $skipped++;
            continue;
          }
          $node = Node::create([
            'type' => 'article',
            'title' => $en_title,
            'uid' => 1,
            'langcode' => 'en',
            'status' => 0,
          ]);
          $this->applyTranslation($node, 'en', $en_row);
          $this->applyTranslation($node, 'ar', $ar_row);
          if (!$skip_images) {
            $this->attachArticleImages($node, $pair_key, $en_row, $ar_row, $image_dir);
          }
          if (!$skip_taxonomy && $taxonomy_row !== NULL) {
            if ($this->applyArticleTaxonomy($node, $taxonomy_row, $term_tid_cache, $warnings)) {
              $taxonomy_applied++;
            }
          }
          $node->save();
          $this->savePathAliases($node, $en_row, $ar_row);
          $created++;
          $this->io()->writeln(sprintf('Created nid=%d pair %s: %s', (int) $node->id(), $pair_key, $en_title));
        }
      }
      catch (\Throwable $e) {
        $errors[] = sprintf('Pair %s (%s): %s', $pair_key, $en_title, $e->getMessage());
        $this->logger()->error($errors[count($errors) - 1]);
      }
    }

    if (!$dry_run) {
      \Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list']);
    }

    if ($dry_run) {
      $this->io()->success(sprintf('Dry-run: validated %d pair(s). Purge would delete: %d.', $processed, $deleted));
    }
    elseif ($images_only) {
      $this->io()->success(sprintf('Updated images on %d article(s).', $updated_images));
    }
    elseif ($taxonomy_only) {
      $this->io()->success(sprintf('Updated taxonomy on %d article(s).', $updated_taxonomy));
    }
    else {
      $this->io()->success(sprintf(
        'Articles: created %d, updated %d, skipped %d, images %d, taxonomy %d. Purged %d.',
        $created,
        $updated,
        $skipped,
        $updated_images,
        $taxonomy_applied,
        $deleted,
      ));
    }

    foreach (array_slice($warnings, 0, 10) as $warning) {
      $this->io()->warning($warning);
    }
    if (count($warnings) > 10) {
      $this->io()->warning(sprintf('…and %d more warning(s).', count($warnings) - 10));
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
   *
   * @return array{title: string, body: string, path_alias: string, published: int, image: string, meta_title: string, meta_description: string, meta_keywords: string}
   */
  private function normalizeRow(array $row, string $langcode): array {
    $title = trim((string) ($row['title'] ?? ''));
    $body = trim((string) ($row['body'] ?? ''));
    $path_alias = trim((string) ($row['path_alias'] ?? ''));
    $image = trim((string) ($row['image'] ?? ''));

    $seo = motaded_custom_article_seo_from_content($title, $body, $langcode);
    return [
      'title' => $title,
      'body' => $body,
      'path_alias' => $path_alias,
      'published' => 0,
      'image' => $image,
      'meta_title' => trim((string) ($row['meta_title'] ?? $seo['meta_title'])),
      'meta_description' => trim((string) ($row['meta_description'] ?? $seo['meta_description'])),
      'meta_keywords' => trim((string) ($row['meta_keywords'] ?? $seo['meta_keywords'])),
    ];
  }

  /**
   * @param array{title: string, body: string, path_alias: string, published: int, image: string, meta_title: string, meta_description: string, meta_keywords: string} $row
   */
  private function applyTranslation(Node $node, string $langcode, array $row): void {
    if ($row['title'] === '' && $langcode === 'ar') {
      throw new \InvalidArgumentException('Arabic title is required.');
    }

    if (!$node->hasTranslation($langcode)) {
      $defaults = [
        'title' => $row['title'],
        'status' => 0,
      ];
      if ($langcode === 'en') {
        $defaults['uid'] = 1;
      }
      $node->addTranslation($langcode, $defaults);
    }

    $translation = $node->getTranslation($langcode);
    $translation->setTitle($row['title']);
    $translation->set('status', 0);

    $faq_split = motaded_custom_split_faq_from_article_body($row['body'], $langcode);
    $summary = $this->extractBodySummary($faq_split['body']);
    $translation->set('body', [
      'value' => $this->normalizeBodyHtml($faq_split['body']),
      'summary' => $summary,
      'format' => self::BODY_TEXT_FORMAT,
    ]);

    if ($translation->hasField('field_faq')) {
      if ($faq_split['items'] !== []) {
        $translation->set('field_faq', array_map(static fn (array $item): array => [
          'question' => $item['question'],
          'answer' => $item['answer'],
          'answer_format' => 'plain_text',
        ], $faq_split['items']));
      }
      else {
        $translation->set('field_faq', []);
      }
    }

    $this->validateMetatags($row, $row['title']);
    motaded_custom_apply_required_node_metatags($translation, $row, 'field_meta');
  }

  /**
   * @param array{title: string, image: string} $en_row
   * @param array{title: string, image: string} $ar_row
   */
  private function attachArticleImages(Node $node, string $pair_key, array $en_row, array $ar_row, string $image_dir): bool {
    $attached = FALSE;
    foreach (['en' => $en_row, 'ar' => $ar_row] as $langcode => $row) {
      if (!$node->hasTranslation($langcode)) {
        continue;
      }
      $mid = $this->resolveImageMedia($pair_key, $langcode, $row['image'], $image_dir, $row['title']);
      if ($mid <= 0) {
        continue;
      }
      $node->getTranslation($langcode)->set('field_media', ['target_id' => $mid]);
      $attached = TRUE;
    }
    return $attached;
  }

  private function resolveImageMedia(string $pair_key, string $langcode, string $basename, string $image_dir, string $title): int {
    $local = $this->resolveLocalImagePath($pair_key, $langcode, $basename, $image_dir);
    if ($local === NULL) {
      return 0;
    }

    $basename = basename($local);
    $binary = file_get_contents($local);
    if ($binary === FALSE || !$this->isValidImageBinary($binary)) {
      return 0;
    }

    $directory = self::PUBLIC_IMAGE_DIR;
    $this->fileSystem->prepareDirectory(
      $directory,
      FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS,
    );
    $dest = self::PUBLIC_IMAGE_DIR . '/' . $basename;
    $file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);

    $media_name = sprintf('Article %s (%s): %s', strtoupper($langcode), $pair_key, $title);
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

  private function resolveLocalImagePath(string $pair_key, string $langcode, string $basename, string $image_dir): ?string {
    $candidates = [];
    if ($basename !== '') {
      $candidates[] = $image_dir . '/' . $basename;
    }

    $prefix = sprintf('%03d_%s_', (int) $pair_key, $langcode);
    foreach (glob($image_dir . '/' . $prefix . '*') ?: [] as $match) {
      if (is_file($match)) {
        $candidates[] = $match;
      }
    }

    foreach ($candidates as $path) {
      if ($this->isValidImageFile($path)) {
        return $path;
      }
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
    return is_array($info) && !empty($info['mime']);
  }

  private function isValidImageBinary(string $binary): bool {
    if (strlen($binary) < 512) {
      return FALSE;
    }
    $info = @getimagesizefromstring($binary);
    return is_array($info) && !empty($info['mime']);
  }

  /**
   * @param array{path_alias: string} $en_row
   * @param array{path_alias: string} $ar_row
   */
  private function savePathAliases(Node $node, array $en_row, array $ar_row): void {
    if ($en_row['path_alias'] !== '') {
      motaded_custom_save_node_path_alias($node, $en_row['path_alias'], 'en');
    }
    if ($ar_row['path_alias'] !== '') {
      motaded_custom_save_node_path_alias($node, $ar_row['path_alias'], 'ar');
    }
  }

  /**
   * @param array{meta_title: string, meta_description: string, meta_keywords: string} $row
   */
  private function validateMetatags(array $row, string $label): void {
    foreach (['meta_title', 'meta_description', 'meta_keywords'] as $key) {
      if (trim($row[$key]) === '') {
        throw new \InvalidArgumentException(sprintf('Missing %s for "%s".', $key, $label));
      }
    }
  }

  private function normalizeBodyHtml(string $html): string {
    $html = trim($html);
    if ($html === '') {
      return '';
    }
    return str_replace(["\r\n", "\r"], "\n", $html);
  }

  private function extractBodySummary(string $html): string {
    if (preg_match('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
      $text = trim(strip_tags($matches[1]));
      if ($text !== '') {
        return mb_strlen($text) > 300 ? mb_substr($text, 0, 297) . '…' : $text;
      }
    }
    $plain = trim(strip_tags($html));
    if ($plain === '') {
      return '';
    }
    return mb_strlen($plain) > 300 ? mb_substr($plain, 0, 297) . '…' : $plain;
  }

  /**
   * @return array<string, array<string, string>>
   */
  private function loadTaxonomyCsv(string $path): array {
    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      return [];
    }

    $header = fgetcsv($handle);
    if ($header === FALSE) {
      fclose($handle);
      return [];
    }

    $header = array_map(static fn (string $col): string => trim($col), $header);
    $rows = [];
    while (($line = fgetcsv($handle)) !== FALSE) {
      if ($line === [NULL] || $line === []) {
        continue;
      }
      $row = [];
      foreach ($header as $index => $column) {
        $row[$column] = trim((string) ($line[$index] ?? ''));
      }
      $id = $row['id'] ?? '';
      if ($id === '') {
        continue;
      }
      $rows[$id] = $row;
    }
    fclose($handle);
    return $rows;
  }

  /**
   * @param array<string, string> $row
   * @param array<string, int|null> $term_tid_cache
   * @param list<string> $warnings
   */
  private function applyArticleTaxonomy(Node $node, array $row, array &$term_tid_cache, array &$warnings): bool {
    $applied = FALSE;
    $label = $node->label();

    $sector = trim($row['Sector'] ?? '');
    if ($sector !== '' && $node->hasField('field_sector')) {
      $tid = $this->getTermId('sector', $sector, 'en', $term_tid_cache, FALSE);
      if ($tid !== NULL) {
        $node->getTranslation('en')->set('field_sector', ['target_id' => $tid]);
        if ($node->hasTranslation('ar')) {
          $node->getTranslation('ar')->set('field_sector', ['target_id' => $tid]);
        }
        $applied = TRUE;
      }
      else {
        $warnings[] = sprintf('Unknown sector "%s" for %s.', $sector, $label);
      }
    }

    $tags_raw = trim($row['Tags'] ?? '');
    if ($tags_raw !== '' && $node->hasField('field_tags')) {
      $tag_tids = $this->resolveTermList('tags', $tags_raw, 'en', $term_tid_cache, TRUE);
      if ($tag_tids !== []) {
        $node->set('field_tags', array_map(static fn (int $tid): array => ['target_id' => $tid], $tag_tids));
        $applied = TRUE;
      }
    }

    $service_type = $this->normalizeServiceType(trim($row['Service type'] ?? ''));
    if ($service_type !== '' && $node->hasField('field_taxonomy')) {
      $tid = $this->getTermId('taxonomy', $service_type, 'en', $term_tid_cache, FALSE);
      if ($tid !== NULL) {
        $node->set('field_taxonomy', ['target_id' => $tid]);
        $applied = TRUE;
      }
      else {
        $warnings[] = sprintf('Unknown service type "%s" for %s.', $service_type, $label);
      }
    }

    return $applied;
  }

  private function normalizeServiceType(string $label): string {
    if ($label === '') {
      return '';
    }
    $path = \Drupal::service('extension.list.module')->getPath('motaded_custom')
      . '/data/service_type.canonical.php';
    if (!is_readable($path)) {
      return $label;
    }
    /** @var array{aliases?: array<string, string>} $canonical */
    $canonical = require $path;
    return $canonical['aliases'][$label] ?? $label;
  }

  /**
   * @param array<string, int|null> $cache
   */
  private function getTermId(string $vid, string $name, string $langcode, array &$cache, bool $create): ?int {
    $name = trim($name);
    if ($name === '') {
      return NULL;
    }
    $key = $vid . '|' . $name;
    if (array_key_exists($key, $cache)) {
      return $cache[$key];
    }

    $tids = $this->entityTypeManager->getStorage('taxonomy_term')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    if ($tids !== []) {
      $cache[$key] = (int) reset($tids);
      return $cache[$key];
    }

    if (!$create) {
      $cache[$key] = NULL;
      return NULL;
    }

    $term = Term::create(['vid' => $vid, 'name' => $name, 'langcode' => $langcode]);
    $term->save();
    $cache[$key] = (int) $term->id();
    return $cache[$key];
  }

  /**
   * @param array<string, int|null> $cache
   *
   * @return int[]
   */
  private function resolveTermList(string $vid, string $raw, string $langcode, array &$cache, bool $create): array {
    if (trim($raw) === '') {
      return [];
    }
    $parts = preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $ids = [];
    foreach ($parts as $name) {
      $tid = $this->getTermId($vid, $name, $langcode, $cache, $create);
      if ($tid !== NULL) {
        $ids[] = $tid;
      }
    }
    return array_values(array_unique($ids));
  }

}
