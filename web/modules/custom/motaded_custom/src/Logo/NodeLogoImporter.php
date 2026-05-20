<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Logo;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Psr\Log\LoggerInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Downloads logos from the web and attaches field_logo on nodes.
 */
final class NodeLogoImporter {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileRepositoryInterface $fileRepository,
  ) {}

  /**
   * @param array{force?: bool, dry-run?: bool, title?: string, limit?: int, repair?: bool, only-overrides?: bool} $options
   */
  public function run(LogoImportConfig $config, array $options, SymfonyStyle $io, LoggerInterface $logger): void {
    $force = filter_var($options['force'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $repair = filter_var($options['repair'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $only_overrides = filter_var($options['only-overrides'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $title_filter = trim((string) ($options['title'] ?? ''));
    $limit = max(0, (int) ($options['limit'] ?? 0));

    if ($repair) {
      $this->repairExistingLogos($config, $dry_run, $title_filter, $limit, $io, $logger);
      return;
    }

    $module_path = \Drupal::service('extension.list.module')->getPath('motaded_custom');
    $image_dir = dirname(\Drupal::root()) . $config->imageDirRel;
    $official_dir = $module_path . $config->officialDirRel;
    if (!$dry_run && !is_dir($image_dir)) {
      mkdir($image_dir, 0755, TRUE);
    }
    if (!$dry_run && !is_dir($official_dir)) {
      mkdir($official_dir, 0755, TRUE);
    }

    $overrides = $this->loadLogoOverrides($module_path . '/' . $config->overridesFile);
    $dataset_urls = $this->loadDatasetLogoUrls($module_path . '/' . $config->datasetFile);
    $brand_sites = $config->brandSitesFile !== NULL && $config->brandSitesFile !== ''
      ? $this->loadBrandSites($module_path . '/' . $config->brandSitesFile)
      : [];

    $default_lang = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $config->bundle)
      ->condition('langcode', $default_lang)
      ->condition('status', NodeInterface::PUBLISHED);
    foreach ($config->sort as [$field, $direction]) {
      $query->sort($field, $direction);
    }
    if ($title_filter !== '') {
      $query->condition('title', $title_filter);
    }
    $nids = array_values($query->execute());
    if ($nids === []) {
      $logger->warning('No @bundle nodes found.', ['@bundle' => $config->bundle]);
      return;
    }

    $ok = 0;
    $skipped = 0;
    $failed = 0;
    $processed = 0;

    foreach ($nids as $nid) {
      if ($limit > 0 && $processed >= $limit) {
        break;
      }
      $processed++;

      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (!$node instanceof NodeInterface) {
        continue;
      }
      $title = $node->getTitle();
      if ($only_overrides && !isset($overrides[$title])) {
        continue;
      }
      if (!$force && !$node->get('field_logo')->isEmpty()) {
        $io->writeln(sprintf('Skip (has logo): %s', $title));
        $skipped++;
        continue;
      }

      $site_url = $this->nodeWebsiteUrl($node);
      $override = $overrides[$title] ?? [];
      if ($site_url === '' && $override === [] && !isset($brand_sites[$title])) {
        $io->writeln(sprintf('[fail] No website URL: %s', $title));
        $failed++;
        continue;
      }

      $slug = $this->logoSlug($node, $config);
      $basename_base = $slug . '-logo';
      $referer = trim((string) ($override['referer'] ?? '')) ?: ($site_url !== '' ? $site_url : 'https://www.' . $slug . '.sa');
      $candidates = $this->buildLogoCandidates(
        $slug,
        $site_url,
        $override,
        $dataset_urls[$title] ?? '',
        $brand_sites[$title] ?? [],
        $referer,
        $config->userAgent,
      );

      if ($dry_run) {
        $io->writeln(sprintf('[dry-run] %s — %d candidate URL(s), first: %s', $title, count($candidates), $candidates[0] ?? '(none)'));
        $ok++;
        continue;
      }

      $resolved = NULL;
      $png_basename = $basename_base . '.png';
      foreach ($candidates as $url) {
        $local = $this->resolveLocalLogoPath($png_basename, $url, $referer, $image_dir, $official_dir, TRUE, $config->userAgent, $force);
        if ($local !== NULL) {
          $resolved = ['path' => $local, 'basename' => $png_basename, 'url' => $url];
          break;
        }
      }

      if ($resolved === NULL) {
        $io->writeln(sprintf('[fail] No logo found: %s (%s)', $title, $site_url));
        $failed++;
        continue;
      }

      $mid = $this->createLogoMedia($title, $resolved['path'], $resolved['basename'], $config);
      if ($mid <= 0) {
        $io->writeln(sprintf('[fail] Media create: %s', $title));
        $failed++;
        continue;
      }

      $this->attachLogo($node, $mid);
      $io->writeln(sprintf('OK nid=%d: %s ← %s (mid=%d)', $nid, $title, $resolved['url'], $mid));
      $ok++;
    }

    $logger->info('@label logos: @ok OK, @skipped skipped, @failed failed (processed @processed).', [
      '@label' => $config->label,
      '@ok' => $ok,
      '@skipped' => $skipped,
      '@failed' => $failed,
      '@processed' => $processed,
    ]);
  }

  /**
   * @param array{force?: bool, dry-run?: bool, title?: string, limit?: int, repair?: bool} $options
   */
  public function repairExistingLogos(
    LogoImportConfig $config,
    bool $dry_run,
    string $title_filter,
    int $limit,
    SymfonyStyle $io,
    LoggerInterface $logger,
  ): void {
    $default_lang = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $config->bundle)
      ->condition('langcode', $default_lang)
      ->condition('status', NodeInterface::PUBLISHED)
      ->exists('field_logo');
    if ($title_filter !== '') {
      $query->condition('title', $title_filter);
    }
    $nids = array_values($query->execute());

    $fixed = 0;
    $skipped = 0;
    $processed = 0;

    foreach ($nids as $nid) {
      if ($limit > 0 && $processed >= $limit) {
        break;
      }
      $processed++;

      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      if (!$node instanceof NodeInterface || $node->get('field_logo')->isEmpty()) {
        continue;
      }

      $media = $node->get('field_logo')->entity;
      if (!$media instanceof Media) {
        continue;
      }

      $file = $media->get('field_media_image')->entity;
      if ($file === NULL) {
        continue;
      }

      $realpath = $this->fileSystem->realpath($file->getFileUri());
      if ($realpath === FALSE || !is_readable($realpath)) {
        $io->writeln(sprintf('[fail] Missing file: %s', $node->getTitle()));
        continue;
      }

      if (!$this->needsLogoNormalization($realpath)) {
        $skipped++;
        continue;
      }

      $png_basename = $this->logoSlug($node, $config) . '-logo.png';
      $cache_dir = dirname(\Drupal::root()) . $config->imageDirRel;

      if ($dry_run) {
        $io->writeln(sprintf('[dry-run] Would convert: %s → %s', $node->getTitle(), $png_basename));
        $fixed++;
        continue;
      }

      $png_path = $this->normalizeLogoFile($realpath, $png_basename, $cache_dir);
      if ($png_path === NULL) {
        $io->writeln(sprintf('[fail] Convert: %s', $node->getTitle()));
        continue;
      }

      $binary = file_get_contents($png_path);
      if ($binary === FALSE) {
        continue;
      }

      $logo_dir = $config->publicUriDir;
      $dest = $logo_dir . '/' . $png_basename;
      $this->fileSystem->prepareDirectory($logo_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $new_file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);
      $this->flushImageDerivatives($dest);

      $media->set('field_media_image', [
        'target_id' => $new_file->id(),
        'alt' => $node->getTitle(),
      ]);
      $media->save();

      if ((int) $file->id() !== (int) $new_file->id()) {
        $file->delete();
      }

      $io->writeln(sprintf('Repaired: %s → %s', $node->getTitle(), $png_basename));
      $fixed++;
    }

    $logger->info('@label repair: @fixed converted, @skipped already OK (processed @processed).', [
      '@label' => $config->label,
      '@fixed' => $fixed,
      '@skipped' => $skipped,
      '@processed' => $processed,
    ]);
  }

  /**
   * @return array<string, array{url?: string, referer?: string}>
   */
  private function loadLogoOverrides(string $path): array {
    if (!is_readable($path)) {
      return [];
    }
    /** @var mixed $data */
    $data = require $path;
    if (!is_array($data)) {
      return [];
    }
    $out = [];
    foreach ($data as $title => $row) {
      if (!is_string($title) || !is_array($row)) {
        continue;
      }
      $out[$title] = $row;
    }
    return $out;
  }

  /**
   * @return array<string, list<string>>
   */
  private function loadBrandSites(string $path): array {
    if (!is_readable($path)) {
      return [];
    }
    /** @var mixed $data */
    $data = require $path;
    if (!is_array($data)) {
      return [];
    }
    $out = [];
    foreach ($data as $title => $bases) {
      if (!is_string($title) || !is_array($bases)) {
        continue;
      }
      $urls = [];
      foreach ($bases as $base) {
        if (!is_string($base)) {
          continue;
        }
        $base = trim($base);
        if ($base === '') {
          continue;
        }
        if (!preg_match('#^https?://#i', $base)) {
          $base = 'https://' . ltrim($base, '/');
        }
        $urls[] = $base;
      }
      if ($urls !== []) {
        $out[$title] = $urls;
      }
    }
    return $out;
  }

  /**
   * @return array<string, string>
   */
  private function loadDatasetLogoUrls(string $path): array {
    if (!is_readable($path)) {
      return [];
    }
    /** @var list<array<string, string>> $rows */
    $rows = require $path;
    $out = [];
    foreach ($rows as $row) {
      $title = trim((string) ($row['title'] ?? ''));
      $url = trim((string) ($row['field_logo_url'] ?? ''));
      if ($title !== '' && $url !== '') {
        $out[$title] = $url;
      }
    }
    return $out;
  }

  private function nodeWebsiteUrl(NodeInterface $node): string {
    if ($node->get('field_external_link')->isEmpty()) {
      return '';
    }
    $uri = trim((string) $node->get('field_external_link')->uri);
    if ($uri === '') {
      return '';
    }
    if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
      return $uri;
    }
    if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $uri)) {
      return '';
    }
    return 'https://' . ltrim($uri, '/');
  }

  private function logoSlug(NodeInterface $node, LogoImportConfig $config): string {
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $node->id());
    $slug = trim((string) basename($alias), '/');
    if ($slug === '' || $slug === $config->aliasParent) {
      $slug = $this->slugify($node->getTitle(), $config->defaultSlug);
    }
    return $slug;
  }

  private function slugify(string $text, string $fallback): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: $fallback;
  }

  /**
   * @param array{url?: string, referer?: string} $override
   * @param list<string> $brand_bases
   *
   * @return list<string>
   */
  private function buildLogoCandidates(
    string $slug,
    string $site_url,
    array $override,
    string $dataset_url,
    array $brand_bases,
    string $referer,
    string $userAgent,
  ): array {
    $candidates = [];

    $explicit = trim((string) ($override['url'] ?? ''));
    if ($explicit !== '') {
      $candidates[] = $explicit;
    }
    if ($dataset_url !== '') {
      $candidates[] = $dataset_url;
    }

    foreach ($brand_bases as $brand_base) {
      $this->appendPathAndHomepageCandidates($candidates, $brand_base, $brand_base, $userAgent);
    }

    if ($slug !== '' && strlen($slug) <= 24) {
      foreach ([
        'https://www.' . $slug . '.sa',
        'https://' . $slug . '.sa',
        'https://www.' . $slug . '.gov.sa',
        'https://' . $slug . '.gov.sa',
      ] as $guessed) {
        $this->appendPathAndHomepageCandidates($candidates, $guessed, $guessed, $userAgent);
      }
    }

    if ($site_url !== '') {
      $this->appendPathAndHomepageCandidates($candidates, $site_url, $referer, $userAgent);
    }

    $hosts = [];
    foreach ($candidates as $url) {
      $host = parse_url($url, PHP_URL_HOST);
      if (is_string($host) && $host !== '') {
        $hosts[$host] = TRUE;
      }
    }
    if ($site_url !== '') {
      $link_host = parse_url($site_url, PHP_URL_HOST);
      if (is_string($link_host) && $link_host !== '') {
        $hosts[$link_host] = TRUE;
      }
    }
    foreach (array_keys($hosts) as $host) {
      $candidates[] = 'https://icons.duckduckgo.com/ip3/' . $host . '.ico';
      $candidates[] = 'https://www.google.com/s2/favicons?domain=' . rawurlencode($host) . '&sz=128';
    }

    $unique = [];
    foreach ($candidates as $url) {
      $url = trim($url);
      if ($url === '' || isset($unique[$url])) {
        continue;
      }
      if (!preg_match('#^https?://#i', $url)) {
        continue;
      }
      $unique[$url] = TRUE;
    }

    return array_keys($unique);
  }

  /**
   * @param list<string> $candidates
   */
  private function appendPathAndHomepageCandidates(
    array &$candidates,
    string $base_url,
    string $referer,
    string $userAgent,
  ): void {
    foreach ([
      '/apple-touch-icon.png',
      '/apple-touch-icon-precomposed.png',
      '/favicon.ico',
      '/logo.png',
      '/logo.svg',
      '/images/logo.png',
      '/images/logo.svg',
      '/assets/images/logo.png',
      '/assets/logo.png',
      '/wp-content/uploads/logo.png',
    ] as $path) {
      $candidates[] = $this->resolveUrl($base_url, $path);
    }

    foreach ($this->discoverFromHomepage($base_url, $referer, $userAgent) as $url) {
      $candidates[] = $url;
    }
  }

  /**
   * @return list<string>
   */
  private function discoverFromHomepage(string $site_url, string $referer, string $userAgent): array {
    $html = $this->fetchHtml($site_url, $referer, $userAgent);
    if ($html === '') {
      return [];
    }

    $found = [];

    if (preg_match_all('/<link\b[^>]*>/i', $html, $link_tags)) {
      foreach ($link_tags[0] as $tag) {
        if (!preg_match('/\brel=["\']([^"\']+)["\']/i', $tag, $rel_m)) {
          continue;
        }
        $rels = preg_split('/\s+/', strtolower($rel_m[1])) ?: [];
        $is_icon = in_array('icon', $rels, TRUE)
          || in_array('shortcut', $rels, TRUE)
          || in_array('apple-touch-icon', $rels, TRUE);
        if (!$is_icon) {
          continue;
        }
        if (!preg_match('/\bhref=["\']([^"\']+)["\']/i', $tag, $href_m)) {
          continue;
        }
        $found[] = $this->resolveUrl($site_url, html_entity_decode($href_m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
      }
    }

    if (preg_match_all('/<img\b[^>]*>/i', $html, $img_tags)) {
      foreach ($img_tags[0] as $tag) {
        $blob = strtolower($tag);
        if (!str_contains($blob, 'logo') && !str_contains($blob, 'brand')) {
          continue;
        }
        if (!preg_match('/\bsrc=["\']([^"\']+)["\']/i', $tag, $src_m)) {
          continue;
        }
        $found[] = $this->resolveUrl($site_url, html_entity_decode($src_m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
      }
    }

    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $og)) {
      $found[] = $this->resolveUrl($site_url, html_entity_decode($og[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    elseif (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $og2)) {
      $found[] = $this->resolveUrl($site_url, html_entity_decode($og2[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    return $found;
  }

  private function fetchHtml(string $url, string $referer, string $userAgent): string {
    $ch = curl_init($url);
    if ($ch === FALSE) {
      return '';
    }
    $headers = ['Accept: text/html,application/xhtml+xml;q=0.9,*/*;q=0.8'];
    if ($referer !== '') {
      $headers[] = 'Referer: ' . $referer;
    }
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_CONNECTTIMEOUT => 8,
      CURLOPT_TIMEOUT => 20,
      CURLOPT_USERAGENT => $userAgent,
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_HTTPHEADER => $headers,
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === FALSE || $code < 200 || $code >= 400) {
      return '';
    }
    return is_string($body) ? $body : '';
  }

  private function resolveUrl(string $base, string $href): string {
    $href = trim($href);
    if ($href === '' || str_starts_with($href, 'data:')) {
      return '';
    }
    if (preg_match('#^https?://#i', $href)) {
      return $href;
    }
    $parts = parse_url($base);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
      return '';
    }
    $origin = $parts['scheme'] . '://' . $parts['host'];
    if (str_starts_with($href, '//')) {
      return $parts['scheme'] . ':' . $href;
    }
    if (str_starts_with($href, '/')) {
      return $origin . $href;
    }
    $path = $parts['path'] ?? '/';
    $dir = rtrim(str_contains($path, '/') ? dirname($path) : '', '/');
    return $origin . ($dir !== '' ? $dir . '/' : '/') . $href;
  }

  private function resolveLocalLogoPath(
    string $basename,
    string $url,
    string $referer,
    string $image_dir,
    string $official_dir,
    bool $allow_download,
    string $userAgent,
    bool $skip_cache = FALSE,
  ): ?string {
    $png_basename = preg_replace('/\.[^.]+$/', '.png', $basename) ?? ($basename . '.png');

    if (!$skip_cache) {
      foreach ([$official_dir . '/' . $png_basename, $image_dir . '/' . $png_basename] as $png_path) {
        if (is_readable($png_path) && $this->isValidImageFile($png_path)) {
          return $png_path;
        }
      }
    }

    $download_tmp = $image_dir . '/.tmp-' . md5($url) . '.bin';
    if (!$allow_download || $url === '') {
      return NULL;
    }

    if (!$this->downloadImage($url, $download_tmp, $referer, $userAgent)) {
      return NULL;
    }

    $normalized = $this->normalizeLogoFile($download_tmp, $png_basename, $image_dir);
    if ($normalized === NULL) {
      if (is_file($download_tmp)) {
        unlink($download_tmp);
      }
      return NULL;
    }

    return $normalized;
  }

  private function normalizeLogoFile(string $source_path, string $png_basename, string $output_dir): ?string {
    if (!is_readable($source_path)) {
      return NULL;
    }

    $png_path = rtrim($output_dir, '/') . '/' . $png_basename;
    if (!is_dir($output_dir)) {
      mkdir($output_dir, 0755, TRUE);
    }

    $info = @getimagesize($source_path);
    if ($info === FALSE) {
      return NULL;
    }

    $mime = $info['mime'] ?? '';
    if ($mime === 'image/png' && $source_path === $png_path && $this->isValidImageFile($png_path)) {
      return $png_path;
    }

    $converted = FALSE;
    if (!in_array($mime, ['image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'], TRUE)
      && function_exists('imagecreatefromstring')
      && function_exists('imagepng')) {
      $binary = file_get_contents($source_path);
      if ($binary !== FALSE) {
        $image = @imagecreatefromstring($binary);
        if ($image !== FALSE) {
          imagealphablending($image, FALSE);
          imagesavealpha($image, TRUE);
          $converted = imagepng($image, $png_path);
          imagedestroy($image);
        }
      }
    }

    if (!$converted) {
      $converted = $this->convertImageWithImageMagick($source_path, $png_path);
    }

    if (!$converted) {
      return NULL;
    }

    if ($source_path !== $png_path && is_file($source_path)) {
      unlink($source_path);
    }

    return $this->isValidImageFile($png_path) ? $png_path : NULL;
  }

  private function convertImageWithImageMagick(string $source_path, string $png_path): bool {
    $src = escapeshellarg($source_path);
    $dest = escapeshellarg($png_path);
    foreach (['convert', 'magick'] as $binary) {
      $cmd = $binary === 'magick'
        ? "magick {$src} {$dest}"
        : "convert {$src} {$dest}";
      exec($cmd . ' 2>/dev/null', $_out, $code);
      if ($code === 0 && $this->isValidImageFile($png_path)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  private function needsLogoNormalization(string $path): bool {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (in_array($ext, ['ico', 'svg', 'gif'], TRUE)) {
      return TRUE;
    }
    $info = @getimagesize($path);
    if ($info === FALSE) {
      return TRUE;
    }
    $mime = $info['mime'] ?? '';
    return in_array($mime, ['image/x-icon', 'image/vnd.microsoft.icon', 'image/svg+xml'], TRUE);
  }

  private function isValidImageFile(string $path): bool {
    if (!is_readable($path)) {
      return FALSE;
    }
    $size = filesize($path);
    if ($size === FALSE || $size < 200) {
      return FALSE;
    }
    $info = @getimagesize($path);
    return $info !== FALSE && !empty($info[0]) && !empty($info[1]);
  }

  private function isValidImageBinary(string $binary): bool {
    $info = @getimagesizefromstring($binary);
    return $info !== FALSE && !empty($info[0]) && !empty($info[1]);
  }

  private function downloadImage(string $url, string $dest, string $referer, string $userAgent): bool {
    if ($this->downloadImageAttempt($url, $dest, $referer, $userAgent, TRUE)) {
      return TRUE;
    }
    $host = parse_url($url, PHP_URL_HOST);
    if (is_string($host) && preg_match('/\.sa$/i', $host)) {
      return $this->downloadImageAttempt($url, $dest, $referer, $userAgent, FALSE);
    }
    return FALSE;
  }

  private function downloadImageAttempt(
    string $url,
    string $dest,
    string $referer,
    string $userAgent,
    bool $verify_ssl,
  ): bool {
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
      CURLOPT_CONNECTTIMEOUT => 8,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_USERAGENT => $userAgent,
      CURLOPT_SSL_VERIFYPEER => $verify_ssl,
      CURLOPT_SSL_VERIFYHOST => $verify_ssl ? 2 : 0,
      CURLOPT_HTTPHEADER => $headers,
    ]);
    $ok = curl_exec($ch) !== FALSE;
    curl_close($ch);
    fclose($fp);
    if (!$ok) {
      if (is_file($dest)) {
        unlink($dest);
      }
      return FALSE;
    }
    if (!$this->isValidImageFile($dest)) {
      if (is_file($dest)) {
        unlink($dest);
      }
      return FALSE;
    }
    return TRUE;
  }

  private function createLogoMedia(string $title, string $local_path, string $basename, LogoImportConfig $config): int {
    $binary = file_get_contents($local_path);
    if ($binary === FALSE || !$this->isValidImageBinary($binary)) {
      return 0;
    }

    $logo_dir = $config->publicUriDir;
    $dest = $logo_dir . '/' . $basename;
    $this->fileSystem->prepareDirectory($logo_dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $file = $this->fileRepository->writeData($binary, $dest, FileSystemInterface::EXISTS_REPLACE);
    $this->flushImageDerivatives($dest);

    $media_name = $config->mediaNamePrefix . $title;
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

  private function flushImageDerivatives(string $uri): void {
    $storage = $this->entityTypeManager->getStorage('image_style');
    foreach ($storage->loadMultiple() as $style) {
      $style->flush($uri);
    }
  }

  private function attachLogo(NodeInterface $node, int $media_id): void {
    $storage = $this->entityTypeManager->getStorage('node');
    $full = $storage->load($node->id());
    if (!$full instanceof NodeInterface) {
      return;
    }
    $full->getUntranslated()->set('field_logo', ['target_id' => $media_id]);
    foreach ($full->getTranslationLanguages(FALSE) as $langcode => $_lang) {
      if (!$full->hasTranslation($langcode)) {
        continue;
      }
      $tr = $full->getTranslation($langcode);
      if ($tr->get('field_logo')->isEmpty()) {
        $tr->set('field_logo', ['target_id' => $media_id]);
      }
    }
    $full->save();
  }

}
