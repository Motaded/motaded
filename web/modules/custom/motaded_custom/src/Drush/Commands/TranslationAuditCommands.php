<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Drush\Commands;

use Drupal\content_translation\ContentTranslationManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Exports CSV reports: missing node translations and mixed-script text fields.
 */
final class TranslationAuditCommands extends DrushCommands {

  private const DEFAULT_LANGS = ['en', 'ar', 'zh-hans'];

  /**
   * Field types whose values are scanned for Latin / Arabic / CJK ratios.
   */
  private const TEXTY_FIELD_TYPES = [
    'string',
    'string_long',
    'text',
    'text_long',
    'text_with_summary',
    'list_string',
  ];

  public function __construct(
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly EntityFieldManagerInterface $entityFieldManager,
    protected readonly LanguageManagerInterface $languageManager,
    protected readonly ContentTranslationManagerInterface $contentTranslationManager,
  ) {
    parent::__construct();
  }

  /**
   * Audit node translations (en / ar / zh-hans): gaps + likely wrong-language text.
   */
  #[CLI\Command(name: 'motaded:translation-audit')]
  #[CLI\Option(name: 'output-dir', description: 'Directory for CSV files (created if missing). Default: project root / translation-audit-output.')]
  #[CLI\Option(name: 'published-only', description: 'Only nodes with at least one published translation; source lang must be published for missing pairs (default: true).')]
  #[CLI\Option(name: 'bundles', description: 'Comma-separated node bundles; empty = all bundles with content translation enabled.')]
  #[CLI\Option(name: 'langs', description: 'Comma-separated langcodes to cross-check (default: en,ar,zh-hans).')]
  #[CLI\Usage(name: 'drush motaded:translation-audit', description: 'Write translation_audit_missing.csv and translation_audit_mixed.csv.')]
  public function translationAudit(array $options = [
    'output-dir' => NULL,
    'published-only' => TRUE,
    'bundles' => NULL,
    'langs' => NULL,
  ]): void {
    if (!\Drupal::moduleHandler()->moduleExists('content_translation')) {
      throw new \RuntimeException('Enable the content_translation module first.');
    }

    $publishedOnly = filter_var($options['published-only'] ?? TRUE, FILTER_VALIDATE_BOOLEAN);
    $langs = $this->parseLangs((string) ($options['langs'] ?? ''));
    $bundles = $this->resolveBundles((string) ($options['bundles'] ?? ''));

    if ($langs === []) {
      throw new \InvalidArgumentException('No valid languages after parsing --langs.');
    }
    if ($bundles === []) {
      throw new \RuntimeException('No translatable node bundles found (enable content translation per bundle or pass --bundles).');
    }

    $outDir = $this->resolveOutputDir($options['output-dir'] ?? NULL);
    if (!is_dir($outDir) && !@mkdir($outDir, 0775, TRUE)) {
      throw new \RuntimeException(sprintf('Cannot create output directory: %s', $outDir));
    }

    $missingFile = $outDir . '/translation_audit_missing.csv';
    $mixedFile = $outDir . '/translation_audit_mixed.csv';

    $missingFh = fopen($missingFile, 'wb');
    $mixedFh = fopen($mixedFile, 'wb');
    if ($missingFh === FALSE || $mixedFh === FALSE) {
      throw new \RuntimeException('Cannot open CSV files for writing.');
    }

    fwrite($mixedFh, "\xEF\xBB\xBF");
    fwrite($missingFh, "\xEF\xBB\xBF");

    fputcsv($missingFh, [
      'nid',
      'bundle',
      'source_langcode',
      'missing_langcode',
      'title_in_source',
      'canonical_url_source',
    ]);
    fputcsv($mixedFh, [
      'nid',
      'bundle',
      'langcode',
      'field_name',
      'delta',
      'reason',
      'latin_letters',
      'arabic_letters',
      'cjk_letters',
      'excerpt',
    ]);

    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->sort('nid');
    $query->condition('type', $bundles, 'IN');
    $nids = $query->execute();

    $this->output()->writeln(sprintf('Scanning %d node(s)…', count($nids)));

    foreach ($nids as $nid) {
      /** @var \Drupal\node\NodeInterface|null $node */
      $node = $storage->load($nid);
      if (!$node instanceof NodeInterface) {
        continue;
      }
      if (!$this->contentTranslationManager->isEnabled('node', $node->bundle())) {
        continue;
      }

      if ($publishedOnly && !$this->nodeHasAnyPublishedTranslation($node)) {
        continue;
      }

      $this->collectMissing($node, $langs, $publishedOnly, $missingFh);
      $this->collectMixed($node, $langs, $publishedOnly, $mixedFh);
    }

    fclose($missingFh);
    fclose($mixedFh);

    $this->logger()->success('Wrote @m and @x.', [
      '@m' => $missingFile,
      '@x' => $mixedFile,
    ]);
  }

  /**
   * @param resource $fh
   */
  private function collectMissing(NodeInterface $node, array $langs, bool $publishedOnly, $fh): void {
    foreach ($langs as $source) {
      if (!$node->hasTranslation($source)) {
        continue;
      }
      $sourceEntity = $node->getTranslation($source);
      if ($publishedOnly && !$sourceEntity->isPublished()) {
        continue;
      }
      foreach ($langs as $target) {
        if ($source === $target) {
          continue;
        }
        if ($node->hasTranslation($target)) {
          continue;
        }
        $url = $this->safeCanonicalUrl($sourceEntity, $source);
        fputcsv($fh, [
          $node->id(),
          $node->bundle(),
          $source,
          $target,
          $sourceEntity->label(),
          $url,
        ]);
      }
    }
  }

  /**
   * @param resource $fh
   */
  private function collectMixed(NodeInterface $node, array $langs, bool $publishedOnly, $fh): void {
    $definitions = $this->entityFieldManager->getFieldDefinitions('node', $node->bundle());
    foreach ($langs as $langcode) {
      if (!$node->hasTranslation($langcode)) {
        continue;
      }
      $tr = $node->getTranslation($langcode);
      if ($publishedOnly && !$tr->isPublished()) {
        continue;
      }
      foreach ($definitions as $name => $definition) {
        if (!$definition->isTranslatable()) {
          continue;
        }
        if (!in_array($definition->getType(), self::TEXTY_FIELD_TYPES, TRUE)) {
          continue;
        }
        $list = $tr->get($name);
        if (!$list instanceof FieldItemListInterface || $list->isEmpty()) {
          continue;
        }
        foreach ($list as $delta => $item) {
          $text = $this->flattenFieldItemToText($item);
          if ($text === '') {
            continue;
          }
          $counts = $this->countScripts($text);
          $reason = $this->detectScriptMismatch($langcode, $counts);
          if ($reason === NULL) {
            continue;
          }
          fputcsv($fh, [
            $node->id(),
            $node->bundle(),
            $langcode,
            $name,
            (string) $delta,
            $reason,
            (string) $counts['latin'],
            (string) $counts['arabic'],
            (string) $counts['cjk'],
            $this->excerpt($text),
          ]);
        }
      }
    }
  }

  private function nodeHasAnyPublishedTranslation(NodeInterface $node): bool {
    foreach (array_keys($node->getTranslationLanguages()) as $langcode) {
      $t = $node->getTranslation($langcode);
      if ($t->isPublished()) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * @return list<string>
   */
  private function parseLangs(string $raw): array {
    $raw = trim($raw);
    if ($raw === '') {
      return self::DEFAULT_LANGS;
    }
    $parts = array_map('trim', explode(',', $raw));
    $valid = [];
    foreach ($parts as $code) {
      if ($code !== '' && $this->languageManager->getLanguage($code) instanceof LanguageInterface) {
        $valid[] = $code;
      }
    }
    return array_values(array_unique($valid));
  }

  /**
   * @return list<string>
   */
  private function resolveBundles(string $raw): array {
    $raw = trim($raw);
    if ($raw !== '') {
      return array_values(array_unique(array_map('trim', explode(',', $raw))));
    }
    $bundles = [];
    foreach (array_keys($this->entityTypeManager->getStorage('node_type')->loadMultiple()) as $bundle) {
      if ($this->contentTranslationManager->isEnabled('node', (string) $bundle)) {
        $bundles[] = (string) $bundle;
      }
    }
    return $bundles;
  }

  private function resolveOutputDir(?string $option): string {
    if ($option !== NULL && trim($option) !== '') {
      $dir = trim($option);
      return str_starts_with($dir, '/') ? $dir : $this->getProjectRootDirectory() . '/' . ltrim($dir, '/');
    }
    return $this->getProjectRootDirectory() . '/translation-audit-output';
  }

  private function getProjectRootDirectory(): string {
    $drupal_root = \Drupal::root();
    $dir = $drupal_root;
    for ($i = 0; $i < 6; $i++) {
      if (is_file($dir . '/composer.json')) {
        return $dir;
      }
      $parent = dirname($dir);
      if ($parent === $dir) {
        break;
      }
      $dir = $parent;
    }
    return $drupal_root;
  }

  private function safeCanonicalUrl(NodeInterface $node, string $langcode): string {
    try {
      $language = $this->languageManager->getLanguage($langcode);
      return Url::fromRoute('entity.node.canonical', ['node' => $node->id()], [
        'language' => $language,
        'absolute' => TRUE,
      ])->toString();
    }
    catch (\Throwable) {
      return '';
    }
  }

  /**
   * @param object $item
   */
  private function flattenFieldItemToText(object $item): string {
    $chunks = [];
    if (method_exists($item, 'getValue')) {
      $value = $item->getValue();
      if (is_array($value)) {
        foreach (['value', 'summary', 'title'] as $key) {
          if (!empty($value[$key]) && is_string($value[$key])) {
            $chunks[] = $value[$key];
          }
        }
      }
    }
    if ($chunks === [] && method_exists($item, '__toString')) {
      $chunks[] = (string) $item;
    }
    $combined = implode("\n", $chunks);
    $combined = html_entity_decode(strip_tags($combined), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $combined = preg_replace('#https?://\S+#u', ' ', $combined) ?? $combined;
    return trim(preg_replace('/\s+/u', ' ', $combined) ?? '');
  }

  /**
   * @return array{latin: int, arabic: int, cjk: int, letters: int}
   */
  private function countScripts(string $text): array {
    preg_match_all('/[a-zA-Z]/', $text, $m);
    $latin = count($m[0] ?? []);
    preg_match_all('/\p{Arabic}/u', $text, $m2);
    $arabic = count($m2[0] ?? []);
    preg_match_all('/\p{Han}/u', $text, $m3);
    $cjk = count($m3[0] ?? []);
    return [
      'latin' => $latin,
      'arabic' => $arabic,
      'cjk' => $cjk,
      'letters' => $latin + $arabic + $cjk,
    ];
  }

  /**
   * Heuristic flags; tune thresholds after reviewing a first export.
   *
   * @param array{latin: int, arabic: int, cjk: int, letters: int} $c
   */
  private function detectScriptMismatch(string $langcode, array $c): ?string {
    if ($c['letters'] < 40) {
      return NULL;
    }
    switch ($langcode) {
      case 'zh-hans':
        if ($c['latin'] >= 25 && ($c['latin'] / max($c['cjk'], 1)) >= 0.12) {
          return 'high_latin_in_zh_hans';
        }
        break;

      case 'ar':
        if ($c['latin'] >= 30 && ($c['latin'] / max($c['arabic'], 1)) >= 0.15) {
          return 'high_latin_in_ar';
        }
        break;

      case 'en':
        if ($c['arabic'] >= 25 && ($c['arabic'] / max($c['latin'], 1)) >= 0.18) {
          return 'high_arabic_in_en';
        }
        if ($c['cjk'] >= 25 && ($c['cjk'] / max($c['latin'], 1)) >= 0.18) {
          return 'high_cjk_in_en';
        }
        break;
    }
    return NULL;
  }

  private function excerpt(string $text): string {
    $oneLine = preg_replace('/\s+/u', ' ', $text) ?? $text;
    if (function_exists('mb_substr')) {
      $slice = mb_substr($oneLine, 0, 240);
    }
    else {
      $slice = substr($oneLine, 0, 240);
    }
    return $slice;
  }

}
