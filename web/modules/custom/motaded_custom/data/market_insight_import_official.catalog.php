<?php

/**
 * @file
 * Official market insights for market_insight CT import (drush minsight).
 *
 * Usage:
 *   drush minsight --purge --download-images
 *   drush minsight --content-only
 *   drush minsight --images-only
 *   drush minsight --dry-run
 *
 * Images download to data/market_insights/images/ (gitignored).
 */

declare(strict_types=1);

/** @var list<array<string, mixed>> $rows */
$rows = require __DIR__ . '/market_insight_import_official.insights.php';
/** @var array<string, array<string, string>> $seo */
$seo = require __DIR__ . '/market_insight_import_official.seo.php';

foreach ($rows as &$row) {
  $title = (string) ($row['title'] ?? '');
  if ($title !== '' && isset($seo[$title])) {
    $row = array_merge($row, $seo[$title]);
  }
}
unset($row);

return $rows;
