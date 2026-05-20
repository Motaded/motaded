<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\motaded_custom\Image\WebpSidecarGenerator;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Backfill imageapi_optimize_webp sidecar files on prod after file sync.
 */
final class WebpSidecarCommands extends DrushCommands {

  public function __construct(
    private readonly WebpSidecarGenerator $webpSidecar,
  ) {
    parent::__construct();
  }

  /**
   * Create missing *.png.webp (etc.) sidecars for raster files in file_managed.
   */
  #[CLI\Command(name: 'motaded:webp-sidecars', aliases: ['mwebp'])]
  #[CLI\Option(name: 'dry-run', description: 'List files that would get a sidecar.')]
  #[CLI\Option(name: 'limit', description: 'Max files to process (0 = all).')]
  #[CLI\Option(name: 'file-uri', description: 'Process a single URI, e.g. public://2026-05/photo.png')]
  #[CLI\Usage(name: 'drush mwebp --dry-run', description: 'Audit missing sidecars on prod.')]
  #[CLI\Usage(name: 'drush mwebp', description: 'Generate all missing sidecars.')]
  public function backfill(array $options = [
    'dry-run' => FALSE,
    'limit' => 0,
    'file-uri' => NULL,
  ]): void {
    if (!$this->webpSidecar->isWebpSupported()) {
      $this->logger()->error('GD imagewebp() is not available. Install/enable PHP GD with WebP support on this server.');
      return;
    }

    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);
    $limit = max(0, (int) ($options['limit'] ?? 0));
    $single_uri = isset($options['file-uri']) ? trim((string) $options['file-uri']) : '';

    if ($single_uri !== '') {
      $this->processOne($single_uri, $dry_run);
      return;
    }

    $query = \Drupal::database()->select('file_managed', 'f')
      ->fields('f', ['fid', 'uri'])
      ->condition('f.status', 1)
      ->orderBy('f.fid');

    $uris = $query->execute()->fetchAllKeyed();
    $created = 0;
    $skipped = 0;
    $missing_source = 0;
    $processed = 0;

    foreach ($uris as $fid => $uri) {
      if ($limit > 0 && $processed >= $limit) {
        break;
      }
      if (!is_string($uri) || !$this->webpSidecar->isRasterSourceUri($uri)) {
        continue;
      }
      $processed++;
      $sidecar = $this->webpSidecar->sidecarUri($uri);
      if ($sidecar === NULL) {
        continue;
      }
      if (file_exists($sidecar)) {
        $skipped++;
        continue;
      }
      if (!file_exists($uri)) {
        $missing_source++;
        $this->io()->warning(sprintf('fid=%d missing source: %s', $fid, $uri));
        continue;
      }
      if ($dry_run) {
        $this->io()->writeln(sprintf('[dry-run] Would create: %s', $sidecar));
        $created++;
        continue;
      }
      if ($this->webpSidecar->ensureSidecar($uri)) {
        $this->io()->writeln(sprintf('Created: %s', $sidecar));
        $created++;
      }
      else {
        $this->io()->warning(sprintf('Failed fid=%d: %s', $fid, $uri));
      }
    }

    $this->io()->success(sprintf(
      'Done. created=%d skipped_existing=%d missing_source=%d%s',
      $created,
      $skipped,
      $missing_source,
      $dry_run ? ' (dry-run)' : ''
    ));
  }

  private function processOne(string $uri, bool $dry_run): void {
    if (!$this->webpSidecar->isRasterSourceUri($uri)) {
      $this->logger()->error('URI is not a raster image: %s', ['%s' => $uri]);
      return;
    }
    $sidecar = $this->webpSidecar->sidecarUri($uri);
    if ($sidecar === NULL) {
      return;
    }
    if (file_exists($sidecar)) {
      $this->io()->writeln(sprintf('Already exists: %s', $sidecar));
      return;
    }
    if ($dry_run) {
      $this->io()->writeln(sprintf('[dry-run] Would create: %s', $sidecar));
      return;
    }
    if ($this->webpSidecar->ensureSidecar($uri)) {
      $this->io()->success(sprintf('Created: %s', $sidecar));
    }
    else {
      $this->logger()->error('Failed to create sidecar for %s', ['%s' => $uri]);
    }
  }

}
