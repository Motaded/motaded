<?php

declare(strict_types=1);

namespace Drupal\motaded_custom\Seo;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Provides helpers for enforcing site name suffix in metatag titles.
 */
class MetatagTitleSuffixHelper {

  /**
   * Minimum preferred final title length.
   */
  protected const MIN_FINAL_LENGTH = 35;

  /**
   * Maximum preferred final title length.
   */
  protected const MAX_FINAL_LENGTH = 65;

  /**
   * Canonical suffix token.
   */
  protected const SUFFIX = ' | [site:name]';

  /**
   * Candidate metatag field names used in this project.
   */
  protected const METATAG_FIELD_CANDIDATES = ['field_meta_tags', 'field_meta'];

  /**
   * Constructs a MetatagTitleSuffixHelper object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager.
   */
  public function __construct(
    protected readonly ConfigFactoryInterface $configFactory,
    protected readonly EntityTypeManagerInterface $entityTypeManager,
    protected readonly AliasManagerInterface $aliasManager,
  ) {}

  /**
   * Ensures title contains a site name suffix.
   *
   * @param string $title
   *   The existing metatag title.
   *
   * @return string
   *   Title with suffix appended when missing.
   */
  public function appendSiteNameSuffix(string $title): string {
    $title = trim($title);
    if ($title === '' || $this->hasSiteNameSuffix($title)) {
      return $title;
    }

    return $title . self::SUFFIX;
  }

  /**
   * Checks if title already has a site name suffix/token.
   *
   * @param string $title
   *   The title to inspect.
   *
   * @return bool
   *   TRUE when suffix is already present.
   */
  public function hasSiteNameSuffix(string $title): bool {
    $title = trim($title);
    if ($title === '') {
      return FALSE;
    }

    if (preg_match('/\|\s*\[site:name\]\s*$/iu', $title) === 1) {
      return TRUE;
    }

    $site_name = trim((string) $this->configFactory->get('system.site')->get('name'));
    if ($site_name === '') {
      return FALSE;
    }

    return preg_match('/\|\s*' . preg_quote($site_name, '/') . '\s*$/iu', $title) === 1;
  }

  /**
   * Optimizes a metatag title for length, uniqueness, and site suffix.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being processed.
   * @param string $langcode
   *   The title language.
   * @param string $rawTitle
   *   Existing metatag title (can be empty).
   * @param array<string, array<string, int>>|null $titleIndex
   *   Optional in-memory title index by langcode and normalized key.
   *
   * @return string
   *   Optimized metatag title.
   */
  public function optimizeNodeTitle(
    NodeInterface $node,
    string $langcode,
    string $rawTitle,
    ?array &$titleIndex = NULL,
  ): string {
    $base = $this->normalizeBaseTitle($rawTitle);
    if ($base === '') {
      $base = $this->normalizeBaseTitle($node->label() ?? '');
    }

    if ($base === '') {
      $base = 'Page';
    }

    // Avoid duplicate H1/title by enriching pure heading-like titles.
    $h1 = trim((string) ($node->label() ?? ''));
    if ($h1 !== '' && mb_strtolower($base) === mb_strtolower($h1)) {
      $qualifier = $this->getAliasQualifier($node, $langcode);
      if ($qualifier !== '') {
        $base .= ' - ' . $qualifier;
      }
    }

    $base = $this->fitBaseLength($base);
    $title = $base . self::SUFFIX;

    if (mb_strlen($title) < self::MIN_FINAL_LENGTH) {
      $qualifier = $this->getAliasQualifier($node, $langcode);
      if ($qualifier !== '' && !str_contains(mb_strtolower($base), mb_strtolower($qualifier))) {
        $base = $this->fitBaseLength($base . ' - ' . $qualifier);
        $title = $base . self::SUFFIX;
      }
    }

    $title = $this->ensureUniqueTitle($node, $langcode, $title, $titleIndex);
    return $title;
  }

  /**
   * Builds existing title index for fast duplicate checks in batch updates.
   *
   * @return array<string, array<string, int>>
   *   Title index by langcode and normalized title key.
   */
  public function buildTitleIndex(): array {
    $index = [];
    $storage = $this->entityTypeManager->getStorage('node');
    $last_nid = 0;
    $batch_size = 250;

    while (TRUE) {
      $nids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('nid', $last_nid, '>')
        ->sort('nid', 'ASC')
        ->range(0, $batch_size)
        ->execute();

      if (empty($nids)) {
        break;
      }

      $last_nid = (int) max($nids);
      $nodes = $storage->loadMultiple($nids);
      foreach ($nodes as $node) {
        if (!$node instanceof NodeInterface) {
          continue;
        }

        $metatag_field = $this->getMetatagFieldName($node);
        if ($metatag_field === NULL) {
          continue;
        }

        $field_definition = $node->getFieldDefinition($metatag_field);
        $langcodes = ($field_definition !== NULL && $field_definition->isTranslatable())
          ? array_keys($node->getTranslationLanguages(FALSE))
          : [$node->language()->getId()];

        foreach ($langcodes as $langcode) {
          $translation = $node->getTranslation($langcode);
          $field = $translation->get($metatag_field);
          if ($field->isEmpty()) {
            continue;
          }

          $value = (string) $field->value;
          if ($value === '') {
            continue;
          }

          $data = json_decode($value, TRUE);
          if (!is_array($data) || empty($data['title'])) {
            continue;
          }

          $key = $this->titleKey((string) $data['title']);
          if ($key === '') {
            continue;
          }

          $index[$langcode][$key] = (int) $node->id();
        }
      }
    }

    return $index;
  }

  /**
   * Ensures title is unique in one language.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   * @param string $langcode
   *   The language code.
   * @param string $title
   *   Candidate title with suffix.
   * @param array<string, array<string, int>>|null $titleIndex
   *   Optional in-memory index.
   *
   * @return string
   *   Unique title.
   */
  protected function ensureUniqueTitle(
    NodeInterface $node,
    string $langcode,
    string $title,
    ?array &$titleIndex = NULL,
  ): string {
    if (!$this->titleExistsInLanguage($title, $langcode, (int) $node->id(), $titleIndex)) {
      $this->registerTitle($title, $langcode, (int) $node->id(), $titleIndex);
      return $title;
    }

    $base = $this->removeSuffix($title);
    $qualifier = $this->getAliasQualifier($node, $langcode);
    if ($qualifier === '') {
      $qualifier = $node->bundle();
    }

    $variant = $this->fitBaseLength($base . ' - ' . $qualifier) . self::SUFFIX;
    if (!$this->titleExistsInLanguage($variant, $langcode, (int) $node->id(), $titleIndex)) {
      $this->registerTitle($variant, $langcode, (int) $node->id(), $titleIndex);
      return $variant;
    }

    $variant = $this->fitBaseLength($base . ' - ' . (int) $node->id()) . self::SUFFIX;
    $this->registerTitle($variant, $langcode, (int) $node->id(), $titleIndex);
    return $variant;
  }

  /**
   * Checks if title already exists in a language.
   */
  protected function titleExistsInLanguage(
    string $title,
    string $langcode,
    int $excludeNid,
    ?array $titleIndex = NULL,
  ): bool {
    $key = $this->titleKey($title);
    if ($key === '') {
      return FALSE;
    }

    if ($titleIndex === NULL) {
      $titleIndex = $this->buildTitleIndex();
    }

    return isset($titleIndex[$langcode][$key]) && $titleIndex[$langcode][$key] !== $excludeNid;
  }

  /**
   * Registers title in in-memory index.
   */
  protected function registerTitle(string $title, string $langcode, int $nid, ?array &$titleIndex): void {
    if ($titleIndex === NULL) {
      return;
    }

    $key = $this->titleKey($title);
    if ($key !== '') {
      $titleIndex[$langcode][$key] = $nid;
    }
  }

  /**
   * Creates normalized key for duplicate comparisons.
   */
  protected function titleKey(string $title): string {
    return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $title) ?? ''));
  }

  /**
   * Normalizes raw title by removing repeated branding and noise.
   */
  protected function normalizeBaseTitle(string $rawTitle): string {
    $title = trim(strip_tags($rawTitle));
    $title = preg_replace('/\s+/u', ' ', $title) ?? '';
    if ($title === '') {
      return '';
    }

    // Remove existing branded suffixes before rebuilding canonical format.
    $title = $this->removeSuffix($title);

    // Remove clickbait punctuation tails and duplicate separators.
    $title = preg_replace('/\s*[\|\-–—]\s*$/u', '', $title) ?? $title;
    $title = trim($title);
    return $title;
  }

  /**
   * Removes known site suffix variants.
   */
  protected function removeSuffix(string $title): string {
    $title = trim($title);

    // Precise cleanup for concrete site-name suffix.
    $site_name = trim((string) $this->configFactory->get('system.site')->get('name'));
    if ($site_name !== '') {
      $title = preg_replace('/\s*(\|\s*' . preg_quote($site_name, '/') . ')\s*$/iu', '', $title) ?? $title;
    }
    $title = preg_replace('/\s*(\|\s*\[site:name\])\s*$/iu', '', $title) ?? $title;
    $title = preg_replace('/\s*(-\s*Motaded)\s*$/iu', '', $title) ?? $title;
    $title = preg_replace('/\s*(\|\s*Motaded)\s*$/iu', '', $title) ?? $title;
    return trim($title);
  }

  /**
   * Fits base part to preferred final title length.
   */
  protected function fitBaseLength(string $base): string {
    $base = trim($base);
    $max_base_len = self::MAX_FINAL_LENGTH - mb_strlen(self::SUFFIX);
    if (mb_strlen($base) <= $max_base_len) {
      return $base;
    }

    $cut = mb_substr($base, 0, $max_base_len);
    // Try cutting on word boundary for Latin-script text.
    $word_cut = preg_replace('/\s+\S*$/u', '', $cut) ?? $cut;
    if (mb_strlen(trim($word_cut)) >= (int) ($max_base_len * 0.7)) {
      return trim($word_cut);
    }
    return trim($cut);
  }

  /**
   * Creates short qualifier from node alias.
   */
  protected function getAliasQualifier(NodeInterface $node, string $langcode): string {
    $alias = $this->aliasManager->getAliasByPath('/node/' . $node->id(), $langcode);
    $segment = trim((string) preg_replace('/^.*\//', '', $alias));
    $segment = urldecode($segment);
    $segment = str_replace(['-', '_'], ' ', $segment);
    $segment = preg_replace('/\s+/u', ' ', $segment) ?? '';
    $segment = trim($segment);

    if ($segment === '' || mb_strlen($segment) < 3) {
      return '';
    }

    // Keep qualifier concise to reduce risk of too-long titles.
    if (mb_strlen($segment) > 28) {
      $segment = trim(mb_substr($segment, 0, 28));
      $segment = preg_replace('/\s+\S*$/u', '', $segment) ?? $segment;
      $segment = trim($segment);
    }

    return $segment;
  }

  /**
   * Returns metatag field name available on this node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   *
   * @return string|null
   *   Metatag field machine name or NULL.
   */
  public function getMetatagFieldName(NodeInterface $node): ?string {
    foreach (self::METATAG_FIELD_CANDIDATES as $field_name) {
      if ($node->hasField($field_name)) {
        return $field_name;
      }
    }

    foreach ($node->getFieldDefinitions() as $field_name => $definition) {
      if ($definition->getType() === 'metatag' && $node->hasField($field_name)) {
        return $field_name;
      }
    }

    return NULL;
  }

}

