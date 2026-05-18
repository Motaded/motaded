<?php

/**
 * @file
 * Curated events for event CT import (drush mevent).
 *
 * Usage:
 *   drush mevent --purge --download-images
 *   drush mevent --content-only
 *   drush mevent --images-only
 *   drush mevent-ar
 *   drush mevent --dry-run
 *
 * Images download to data/events/images/ (gitignored).
 */

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
$rows = require __DIR__ . '/event_import_official.events.php';
/** @var array<string, array<string, string>> $seo */
$seo = require __DIR__ . '/event_import_official.seo.php';

foreach ($rows as &$row) {
  $title = (string) ($row['title'] ?? '');
  if ($title !== '' && isset($seo[$title])) {
    $row = array_merge($row, $seo[$title]);
  }
}
unset($row);

return $rows;
