<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\taxonomy\Entity\Term;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Import market_insight nodes from CSV.
 *
 * Параграфи як окремі сутності тут не використовуються: key takeaways — це
 * текстове поле (basic_html). Таксономії (category, region, sector) — за
 * назвами з CSV, терміни створюються, якщо їх ще немає.
 */
final class MarketInsightImportCommands extends DrushCommands {

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly FileSystemInterface $fileSystem,
  ) {}

  /**
   * Import market insights from CSV.
   */
  #[CLI\Command(name: 'motaded:market-insight-import-csv', aliases: ['mmic'])]
  #[CLI\Argument(name: 'path', description: 'Path to CSV (default: ../market_insight_import_ready.csv relative to Drupal root).')]
  #[CLI\Option(name: 'skip-existing', description: 'Skip row when a published insight with the same title already exists.')]
  #[CLI\Option(name: 'dry-run', description: 'Parse and validate only; do not save.')]
  #[CLI\Usage(name: 'drush mmic', description: 'Import from project root market_insight_import_ready.csv')]
  #[CLI\Usage(name: 'drush mmic /path/to.csv', description: 'Import a specific file.')]
  public function import(?string $path = NULL, array $options = [
    'skip-existing' => TRUE,
    'dry-run' => FALSE,
  ]): void {
    $drupal_root = \Drupal::root();
    $default = $drupal_root . '/../market_insight_import_ready.csv';
    $path = $path ?? $default;
    if ($path !== '' && $path[0] !== '/' && !preg_match('#^[a-zA-Z]:\\\\#', $path)) {
      $candidate = $drupal_root . '/../' . $path;
      if (is_readable($candidate)) {
        $path = $candidate;
      }
    }
    $resolved = $this->fileSystem->realpath($path);
    $path = $resolved ?: $path;
    if (!is_readable($path)) {
      $this->logger()->error('Cannot read CSV: @path (tip: put file in project root or pass absolute path)', ['@path' => $path]);
      return;
    }

    $skip_existing = filter_var($options['skip-existing'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $dry_run = filter_var($options['dry-run'] ?? FALSE, FILTER_VALIDATE_BOOLEAN);

    $handle = fopen($path, 'rb');
    if ($handle === FALSE) {
      $this->logger()->error('Could not open CSV.');
      return;
    }

    $langcode = $this->languageManager->getDefaultLanguage()->getId();
    $header = fgetcsv($handle);
    if ($header === FALSE || $header === []) {
      fclose($handle);
      $this->logger()->error('Empty CSV.');
      return;
    }
    if (isset($header[0])) {
      $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
    }
    $indexes = array_flip($header);

    $required_cols = ['title', 'status', 'langcode', 'body', 'field_category_name'];
    foreach ($required_cols as $col) {
      if (!isset($indexes[$col])) {
        fclose($handle);
        $this->logger()->error('Missing column in CSV: @c', ['@c' => $col]);
        return;
      }
    }

    $node_storage = $this->entityTypeManager->getStorage('node');
    $created = 0;
    $skipped = 0;
    $row_num = 1;

    while (($row = fgetcsv($handle)) !== FALSE) {
      $row_num++;
      $row = $this->padRow($row, count($header));
      $get = static function (array $idx, array $r, string $key): string {
        $i = $idx[$key] ?? NULL;
        if ($i === NULL || !isset($r[$i])) {
          return '';
        }
        return trim((string) $r[$i]);
      };

      $title = $get($indexes, $row, 'title');
      if ($title === '') {
        continue;
      }

      if ($skip_existing && !$dry_run) {
        $existing = $node_storage->getQuery()
          ->accessCheck(FALSE)
          ->condition('type', 'market_insight')
          ->condition('title', $title)
          ->condition('status', NodeInterface::PUBLISHED)
          ->range(0, 1)
          ->execute();
        if ($existing) {
          $this->io()->writeln(sprintf('Skip row %d (exists): %s', $row_num, $title));
          $skipped++;
          continue;
        }
      }

      try {
        $status = (int) ($get($indexes, $row, 'status') ?: '1');
        $row_lang = $get($indexes, $row, 'langcode') ?: $langcode;
        $summary = $get($indexes, $row, 'body_summary');
        $body_html = $this->normalizeBodyHtml($get($indexes, $row, 'body'));

        if ($dry_run) {
          $this->io()->writeln(sprintf('[dry-run] Row %d OK: %s', $row_num, $title));
          $created++;
          continue;
        }

        $category_name = $get($indexes, $row, 'field_category_name');
        $category_tid = $this->getOrCreateTerm('market_insight_category', $category_name, $row_lang);

        $values = [
          'type' => 'market_insight',
          'title' => $title,
          'langcode' => $row_lang,
          'status' => $status,
          'uid' => 1,
          'promote' => 0,
          'body' => [
            'value' => $body_html,
            'summary' => $summary,
            'format' => 'basic_html',
          ],
          'field_category' => ['target_id' => $category_tid],
        ];

        $region_name = $get($indexes, $row, 'field_region_name');
        if ($region_name !== '') {
          $values['field_region'] = ['target_id' => $this->getOrCreateTerm('region', $region_name, $row_lang)];
        }

        $sector_name = $get($indexes, $row, 'field_sector_name');
        if ($sector_name !== '') {
          $values['field_sector'] = ['target_id' => $this->getOrCreateTerm('sector', $sector_name, $row_lang)];
        }

        $values['field_stat_prefix'] = $get($indexes, $row, 'field_stat_prefix');
        $values['field_stat_value'] = $get($indexes, $row, 'field_stat_value');
        $values['field_stat_suffix'] = $get($indexes, $row, 'field_stat_suffix');
        $values['field_stat_label'] = $get($indexes, $row, 'field_stat_label');

        $year_raw = $get($indexes, $row, 'field_year');
        if ($year_raw !== '' && ctype_digit($year_raw)) {
          $values['field_year'] = (int) $year_raw;
        }

        $values['field_source_text'] = $get($indexes, $row, 'field_source_text');

        $source_link = $this->buildLinkValue(
          $get($indexes, $row, 'field_source_link_uri'),
          $get($indexes, $row, 'field_source_link_title')
        );
        if ($source_link !== NULL) {
          $values['field_source_link'] = $source_link;
        }

        $values['field_featured'] = (bool) (int) ($get($indexes, $row, 'field_featured') ?: '0');
        $values['field_highlight'] = (bool) (int) ($get($indexes, $row, 'field_highlight') ?: '0');

        $order_raw = $get($indexes, $row, 'field_order');
        if ($order_raw !== '' && is_numeric($order_raw)) {
          $values['field_order'] = (int) $order_raw;
        }

        $shell = $get($indexes, $row, 'field_insight_shell') ?: 'platform';
        if (!in_array($shell, ['standard', 'platform'], TRUE)) {
          $shell = 'platform';
        }
        $values['field_insight_shell'] = $shell;

        $takeaways_raw = $get($indexes, $row, 'field_key_takeaways');
        if ($takeaways_raw !== '') {
          $values['field_key_takeaways'] = [
            'value' => $this->normalizeBodyHtml($takeaways_raw),
            'format' => 'basic_html',
          ];
        }

        $hero_mid = $get($indexes, $row, 'field_media_mid');
        if ($hero_mid !== '' && ctype_digit($hero_mid)) {
          $values['field_media'] = ['target_id' => (int) $hero_mid];
        }

        $thumb_mid = $get($indexes, $row, 'field_insight_thumbnail_mid');
        if ($thumb_mid !== '' && ctype_digit($thumb_mid)) {
          $values['field_insight_thumbnail'] = ['target_id' => (int) $thumb_mid];
        }

        $alias = $get($indexes, $row, 'path_alias');

        $node = $node_storage->create($values);
        $node->save();
        $nid = (int) $node->id();

        if ($alias !== '') {
          $alias = '/' . ltrim($alias, '/');
          PathAlias::create([
            'path' => '/node/' . $nid,
            'alias' => $alias,
            'langcode' => $row_lang,
          ])->save();
        }

        $this->io()->writeln(sprintf('Created nid=%d: %s', $nid, $title));
        $created++;
      }
      catch (\Throwable $e) {
        $this->logger()->error('Row @n: @msg', ['@n' => $row_num, '@msg' => $e->getMessage()]);
      }
    }

    fclose($handle);
    if ($dry_run) {
      $this->logger()->success('Dry-run: @n row(s) OK (nothing saved).', ['@n' => (string) $created]);
    }
    else {
      $this->logger()->success('Imported @n insight(s); skipped @s.', [
        '@n' => (string) $created,
        '@s' => (string) $skipped,
      ]);
    }
  }

  /**
   * @param string[] $row
   * @return string[]
   */
  private function padRow(array $row, int $count): array {
    while (count($row) < $count) {
      $row[] = '';
    }
    return $row;
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

  private function getOrCreateTerm(string $vid, string $name, string $langcode): int {
    $name = trim($name);
    if ($name === '') {
      throw new \InvalidArgumentException('Empty taxonomy term name for vocabulary ' . $vid);
    }
    $storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $tids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('vid', $vid)
      ->condition('name', $name)
      ->range(0, 1)
      ->execute();
    if ($tids) {
      return (int) reset($tids);
    }
    $term = Term::create([
      'vid' => $vid,
      'name' => $name,
      'langcode' => $langcode,
    ]);
    $term->save();
    $this->io()->writeln(sprintf('Created term %s / %s (tid=%d)', $vid, $name, (int) $term->id()));
    return (int) $term->id();
  }

}
