<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Image;

use Drupal\Core\File\FileSystemInterface;
use Drupal\imageapi_optimize\Entity\ImageAPIOptimizePipeline;

/**
 * Ensures imageapi_optimize_webp sidecar files (e.g. photo.png.webp) exist.
 *
 * Image styles with WebP conversion expect the sidecar when the sitewide
 * webp_derivative pipeline is enabled. Files copied via zip/rsync skip upload
 * hooks, so sidecars must be generated explicitly on prod.
 */
final class WebpSidecarGenerator {

  /**
   * Raster extensions eligible for a .{ext}.webp sidecar.
   */
  private const RASTER_EXTENSIONS = ['png', 'jpg', 'jpeg', 'gif'];

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Whether GD can write WebP on this environment.
   */
  public function isWebpSupported(): bool {
    return function_exists('imagewebp');
  }

  /**
   * Creates a sidecar for a raster source URI if missing.
   *
   * @param string $uri
   *   File URI, e.g. public://2026-05/photo.png.
   *
   * @return bool
   *   TRUE when sidecar exists or was created.
   */
  public function ensureSidecar(string $uri): bool {
    if (!$this->isWebpSupported()) {
      return FALSE;
    }
    if (!\Drupal::moduleHandler()->moduleExists('imageapi_optimize_webp')) {
      return FALSE;
    }
    if (!$this->isRasterSourceUri($uri)) {
      return FALSE;
    }

    $sidecar_uri = $uri . '.webp';
    if (file_exists($sidecar_uri)) {
      return TRUE;
    }

    $source_path = $this->fileSystem->realpath($uri);
    if ($source_path === FALSE || !is_readable($source_path)) {
      return FALSE;
    }

    $pipeline = ImageAPIOptimizePipeline::load('webp_derivative');
    if ($pipeline === NULL) {
      return FALSE;
    }

    return (bool) $pipeline->applyToImage($uri);
  }

  /**
   * Ensures sidecar before image style delivery for .webp request paths.
   *
   * Handles URIs such as public://dir/photo.png.webp (sidecar) and falls back
   * to the raster sibling when the sidecar file is missing.
   *
   * @param string $uri
   *   Requested source URI (may end with .webp).
   */
  public function ensureForImageStyleRequest(string $uri): bool {
    if (!str_ends_with(strtolower($uri), '.webp')) {
      return $this->ensureSidecar($uri);
    }

    if (file_exists($uri)) {
      return TRUE;
    }

    $base = substr($uri, 0, -strlen('.webp'));
    foreach (self::RASTER_EXTENSIONS as $ext) {
      $candidate = $base . '.' . $ext;
      if (file_exists($candidate)) {
        return $this->ensureSidecar($candidate);
      }
    }

    if (file_exists($base)) {
      return $this->ensureSidecar($base);
    }

    return FALSE;
  }

  /**
   * Whether the URI is a raster image (not an existing sidecar or native webp).
   */
  public function isRasterSourceUri(string $uri): bool {
    $target = \Drupal::service('stream_wrapper_manager')->getTarget($uri);
    if ($target === FALSE) {
      return FALSE;
    }
    $extension = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($extension === 'webp') {
      $basename = pathinfo($target, PATHINFO_FILENAME);
      $inner_ext = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
      return in_array($inner_ext, self::RASTER_EXTENSIONS, TRUE);
    }
    return in_array($extension, self::RASTER_EXTENSIONS, TRUE);
  }

  /**
   * Sidecar URI for a raster source, or NULL when not applicable.
   */
  public function sidecarUri(string $uri): ?string {
    return $this->isRasterSourceUri($uri) ? $uri . '.webp' : NULL;
  }

}
