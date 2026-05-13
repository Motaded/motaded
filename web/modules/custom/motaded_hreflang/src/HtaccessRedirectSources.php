<?php

declare(strict_types=1);

namespace Drupal\motaded_hreflang;

use Drupal\Core\Cache\Cache;

/**
 * Reads mod_alias Redirect lines from web/.htaccess for href checks.
 *
 * Does not evaluate RedirectMatch regex rules (too ambiguous); use the alter
 * hook to append map entries if needed.
 */
final class HtaccessRedirectSources {

  /**
   * Whether the internal path (no leading slash, lowercase) is a Redirect source.
   */
  public static function pathIsRedirectSource(string $internal_path): bool {
    $internal_path = strtolower(ltrim($internal_path, '/'));
    if ($internal_path === '') {
      return FALSE;
    }
    return array_key_exists($internal_path, self::getMap());
  }

  /**
   * Absolute destination URL from .htaccess for this source path, if any.
   */
  public static function getRedirectTargetAbsoluteUrl(string $internal_path): ?string {
    $internal_path = strtolower(ltrim($internal_path, '/'));
    if ($internal_path === '') {
      return NULL;
    }
    $map = self::getMap();
    if (!isset($map[$internal_path])) {
      return NULL;
    }
    $url = trim((string) $map[$internal_path]);
    return $url !== '' ? $url : NULL;
  }

  /**
   * @return array<string, string>
   *   Normalized source path (no leading slash) => absolute destination URL.
   */
  private static function getMap(): array {
    $path = \Drupal::root() . '/.htaccess';
    if (!is_readable($path)) {
      return [];
    }

    $mtime = (int) filemtime($path);
    $cid = 'motaded_hreflang:htaccess_redirect_map_v2:' . $mtime;
    $cache = \Drupal::cache()->get($cid);
    if ($cache !== FALSE && isset($cache->data) && is_array($cache->data)) {
      /** @var array<string, string> $data */
      $data = $cache->data;
      return $data;
    }

    $content = @file_get_contents($path);
    if ($content === FALSE || $content === '') {
      return [];
    }

    $map = self::parseRedirectMap($content);
    \Drupal::moduleHandler()->alter('motaded_hreflang_htaccess_redirect_map', $map);

    $normalized_map = [];
    foreach ($map as $src => $dest) {
      $key = strtolower(ltrim((string) $src, '/'));
      if ($key !== '' && is_string($dest) && trim($dest) !== '') {
        $normalized_map[$key] = trim($dest);
      }
    }
    $map = $normalized_map;

    \Drupal::cache()->set($cid, $map, Cache::PERMANENT, []);

    return $map;
  }

  /**
   * Parses simple Redirect lines into source path => destination URL.
   *
   * @return array<string, string>
   */
  private static function parseRedirectMap(string $content): array {
    $map = [];
    foreach (explode("\n", $content) as $line) {
      $line = trim($line);
      if ($line === '' || str_starts_with($line, '#')) {
        continue;
      }
      if (preg_match('/^\s*RedirectMatch\s/i', $line)) {
        continue;
      }
      if (preg_match(
        '/^\s*Redirect\s+(?:(?:30[123789]|permanent|temp|seeother|gone)\s+)?(\/\S+)\s+(\S+)/i',
        $line,
        $m
      )) {
        $src = strtolower(ltrim($m[1], '/'));
        $dest = trim($m[2]);
        if ($src !== '' && $dest !== '') {
          $map[$src] = $dest;
        }
      }
    }

    return $map;
  }

}
