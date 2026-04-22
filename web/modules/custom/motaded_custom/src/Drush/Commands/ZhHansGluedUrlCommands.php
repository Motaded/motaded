<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Database\Connection;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Fixes Chinese (zh-hans) absolute URLs where / was dropped after the prefix.
 *
 * Wrong: motaded.com.sa/zh-hansblog/... or .../zh-hansservices/...
 * Right: motaded.com.sa/zh-hans/blog/... and .../zh-hans/services/...
 */
final class ZhHansGluedUrlCommands extends DrushCommands {

  public function __construct(
    protected readonly Connection $database,
  ) {
    parent::__construct();
  }

  /**
   * Rewrites glued zh-hans + blog|services segments in body HTML (nodes + blocks).
   */
  #[CLI\Command(name: 'motaded:fix-zh-hans-glued-urls')]
  #[CLI\Option(name: 'dry-run', description: 'Only print how many rows would be updated per table.')]
  #[CLI\Usage(name: 'drush motaded:fix-zh-hans-glued-urls --dry-run', description: 'Preview affected row counts.')]
  #[CLI\Usage(name: 'drush motaded:fix-zh-hans-glued-urls -y', description: 'Apply fixes (use -y to skip confirmation).')]
  public function fixGluedUrls(array $options = ['dry-run' => FALSE]): void {
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $targets = [
      [
        'table' => 'node__body',
        'where' => "langcode = :langcode AND (body_value LIKE :like_blog OR body_value LIKE :like_services)",
        'args' => [
          ':langcode' => 'zh-hans',
          ':like_blog' => '%zh-hansblog%',
          ':like_services' => '%zh-hansservices%',
        ],
      ],
      [
        'table' => 'node_revision__body',
        'where' => "langcode = :langcode AND (body_value LIKE :like_blog OR body_value LIKE :like_services)",
        'args' => [
          ':langcode' => 'zh-hans',
          ':like_blog' => '%zh-hansblog%',
          ':like_services' => '%zh-hansservices%',
        ],
      ],
      [
        'table' => 'block_content__body',
        'where' => "(body_value LIKE :like_blog OR body_value LIKE :like_services)",
        'args' => [
          ':like_blog' => '%zh-hansblog%',
          ':like_services' => '%zh-hansservices%',
        ],
      ],
      [
        'table' => 'block_content_revision__body',
        'where' => "(body_value LIKE :like_blog OR body_value LIKE :like_services)",
        'args' => [
          ':like_blog' => '%zh-hansblog%',
          ':like_services' => '%zh-hansservices%',
        ],
      ],
    ];

    $setExpr = <<<'SQL'
body_value = REPLACE(
  REPLACE(
    REPLACE(
      REPLACE(
        REPLACE(
          REPLACE(
            body_value,
            'zh-hansservices/', 'zh-hans/services/'),
            'zh-hansblog/', 'zh-hans/blog/'),
            'zh-hansservices"', 'zh-hans/services"'),
            'zh-hansservices>', 'zh-hans/services>'),
            'zh-hansblog"', 'zh-hans/blog"'),
            'zh-hansblog>', 'zh-hans/blog>')
SQL;

    $total = 0;
    foreach ($targets as $target) {
      $table = $target['table'];
      if (!$this->database->schema()->tableExists($table)) {
        $this->logger()->warning('Table @t missing; skipped.', ['@t' => $table]);
        continue;
      }

      $count_query = 'SELECT COUNT(*) FROM {' . $table . '} WHERE ' . $target['where'];
      $count = (int) $this->database->query($count_query, $target['args'])->fetchField();
      $this->io()->writeln(dt('@table: @count row(s) with glued zh-hans URLs.', [
        '@table' => $table,
        '@count' => $count,
      ]));
      $total += $count;
    }

    if ($total === 0) {
      $this->logger()->success(dt('Nothing to update.'));
      return;
    }

    if ($dry_run) {
      $this->logger()->notice(dt('Dry run only; no updates applied.'));
      return;
    }

    if (!$this->io()->confirm(dt('Update @total row(s) across body field tables?', ['@total' => $total]))) {
      $this->logger()->warning(dt('Aborted.'));
      return;
    }

    foreach ($targets as $target) {
      $table = $target['table'];
      if (!$this->database->schema()->tableExists($table)) {
        continue;
      }
      $update = 'UPDATE {' . $table . '} SET ' . $setExpr . ' WHERE ' . $target['where'];
      $this->database->query($update, $target['args']);
      $this->logger()->notice(dt('Updated @table.', ['@table' => $table]));
    }

    $this->logger()->success(dt('Done. Clear caches: drush cr'));
  }

}
