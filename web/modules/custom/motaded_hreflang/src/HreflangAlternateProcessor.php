<?php

declare(strict_types=1);

namespace Drupal\motaded_hreflang;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Post-processes alternate/hreflang links in html_head and html_head_link.
 */
final class HreflangAlternateProcessor {

  /**
   * @param array $build
   *   A render array with #attached (e.g. page attachments).
   */
  public static function process(array &$build, ConfigFactoryInterface $config_factory): void {
    if (empty($build['#attached']) || !is_array($build['#attached'])) {
      return;
    }

    $attached = &$build['#attached'];
    $refs = self::collectAlternateRefs($attached);
    if ($refs === []) {
      self::normalizeCanonicalFrontUrl($attached);
      return;
    }

    self::normalizeAlternateFrontUrls($attached, $refs);

    $config = $config_factory->get('motaded_hreflang.settings');
    $policy = $config->get('policy') ?: 'per_language';
    $strip_redirect = (bool) $config->get('strip_if_redirect');
    $strip_missing = (bool) $config->get('strip_if_missing_translation');

    $node = self::routeNode();
    $remove = [];

    // Manual overrides for the current request path.
    $override = self::matchingOverride($config->get('overrides') ?: []);
    if (is_array($override)) {
      foreach ($refs as $ref_key => $info) {
        $code = self::normalizeHreflangCode($info['hreflang']);
        $key = self::overrideConfigKey($code);
        if ($key === NULL || !array_key_exists($key, $override)) {
          continue;
        }
        $val = $override[$key];
        if ($val === NULL || $val === '') {
          $remove[$ref_key] = TRUE;
          continue;
        }
        $val = trim((string) $val);
        if ($val === '') {
          $remove[$ref_key] = TRUE;
          continue;
        }
        $resolved = self::resolveOverrideUrl($val);
        if ($resolved !== '') {
          self::setHrefForRef($attached, $info, $resolved);
        }
      }
    }

    // Node pages: metatag tokens like [node:url:absolute] resolve in the current
    // language, so en/ar/x-default all get the same href. Rewrite each alternate
    // to the correct translation URL (x-default → site default language, usually EN).
    if ($node instanceof NodeInterface) {
      foreach ($refs as $ref_key => $info) {
        if (isset($remove[$ref_key])) {
          continue;
        }
        $code = self::normalizeHreflangCode($info['hreflang']);
        $url = self::resolveAlternateUrl($node, $code);
        if ($url === NULL || $url === '') {
          if ($strip_missing && $code !== 'x-default') {
            $remove[$ref_key] = TRUE;
          }
          continue;
        }
        self::setHrefForRef($attached, $info, $url);
      }
    }
    else {
      foreach ($refs as $ref_key => $info) {
        if (isset($remove[$ref_key])) {
          continue;
        }
        $href = self::getHrefForRef($attached, $info);
        $code = self::normalizeHreflangCode($info['hreflang']);
        $rewritten = self::rewriteNodeCanonicalHref($href, $code);
        if ($rewritten !== NULL) {
          self::setHrefForRef($attached, $info, self::normalizeHomepageHref($rewritten));
        }
      }
    }

    self::normalizeAlternateFrontUrls($attached, $refs);

    // x-default: if it points at a redirect source or the site default language
    // has no published translation, replace with redirect destination (Redirect
    // module or .htaccess) or the first published translation URL.
    if ($node instanceof NodeInterface) {
      foreach ($refs as $ref_key => $info) {
        if (isset($remove[$ref_key])) {
          continue;
        }
        if (self::normalizeHreflangCode($info['hreflang']) !== 'x-default') {
          continue;
        }
        $href = self::getHrefForRef($attached, $info);
        if ($href === '' || !self::shouldRewriteXDefault($node, $href)) {
          continue;
        }
        $replacement = self::resolveXDefaultReplacement($node, $href);
        if ($replacement !== NULL && $replacement !== '') {
          self::setHrefForRef($attached, $info, self::normalizeHomepageHref($replacement));
        }
      }
    }

    self::normalizeAlternateFrontUrls($attached, $refs);

    if ($node instanceof NodeInterface) {
      self::ensureNodeLanguageAlternates($attached, $refs, $node, $remove);
    }

    foreach ($refs as $ref_key => &$info) {
      if (isset($remove[$ref_key])) {
        continue;
      }
      $info['href'] = self::getHrefForRef($attached, $info);
    }
    unset($info);

    $bad = [];

    foreach ($refs as $ref_key => $info) {
      $lang_attr = self::normalizeHreflangCode($info['hreflang']);
      if ($strip_missing && $node instanceof NodeInterface && $lang_attr !== '' && $lang_attr !== 'x-default') {
        if (self::resolveAlternateUrl($node, $lang_attr) === NULL) {
          $bad[$ref_key] = TRUE;
          continue;
        }
      }

      if ($node instanceof NodeInterface
        && $lang_attr !== ''
        && $lang_attr !== 'x-default'
        && $info['href'] !== ''
        && !self::isPublicHreflangUrl($info['href'])) {
        $bad[$ref_key] = TRUE;
        continue;
      }

      if ($strip_redirect && $info['href'] !== '') {
        if (self::hrefHasRedirect($info['href'], $lang_attr)) {
          $bad[$ref_key] = TRUE;
        }
      }
    }

    if ($bad !== []) {
      if ($policy === 'all_or_nothing') {
        foreach ($refs as $ref_key => $_) {
          $remove[$ref_key] = TRUE;
        }
      }
      else {
        foreach ($bad as $ref_key => $_) {
          $remove[$ref_key] = TRUE;
        }
      }
    }

    foreach (array_keys($remove) as $ref_key) {
      if (!isset($refs[$ref_key])) {
        continue;
      }
      self::unsetRef($attached, $refs[$ref_key]);
      unset($refs[$ref_key]);
    }

    if (isset($attached['html_head'])) {
      $attached['html_head'] = array_values($attached['html_head']);
    }
    if (isset($attached['html_head_link'])) {
      $attached['html_head_link'] = array_values($attached['html_head_link']);
    }

    self::normalizeCanonicalFrontUrl($attached);
  }

  /**
   * Rewrites /front and /{lang}/front homepage aliases to public homepage paths.
   *
   * Drupal often stores the front page alias as /front (301 → /). Hreflang must
   * never point at redirecting URLs.
   */
  public static function normalizeHomepageHref(string $href): string {
    if ($href === '' || !str_contains($href, 'front')) {
      return $href;
    }

    $parts = parse_url($href);
    if ($parts === FALSE) {
      return $href;
    }

    $path = $parts['path'] ?? '';
    if ($path === '' && !str_starts_with($href, '/')) {
      return $href;
    }

    $base_path = \Drupal::request()->getBasePath();
    if ($base_path !== '' && $path !== '' && str_starts_with($path, $base_path)) {
      $path = substr($path, strlen($base_path)) ?: '/';
    }
    $path = '/' . ltrim((string) $path, '/');

    $normalized_path = NULL;
    if ($path === '/front' || $path === '/front/') {
      $normalized_path = '/';
    }
    elseif (preg_match('#^/([a-z]{2,3}(?:-[a-z0-9]+)?)/front/?$#i', $path, $m)) {
      $normalized_path = '/' . $m[1] . '/';
    }

    if ($normalized_path === NULL) {
      return $href;
    }

    if (!isset($parts['scheme'], $parts['host'])) {
      $out = $normalized_path;
      if (!empty($parts['query'])) {
        $out .= '?' . $parts['query'];
      }
      if (!empty($parts['fragment'])) {
        $out .= '#' . $parts['fragment'];
      }
      return $out;
    }

    $out = $parts['scheme'] . '://' . $parts['host'];
    if (isset($parts['port'])) {
      $out .= ':' . $parts['port'];
    }
    $out .= $normalized_path;
    if (!empty($parts['query'])) {
      $out .= '?' . $parts['query'];
    }
    if (!empty($parts['fragment'])) {
      $out .= '#' . $parts['fragment'];
    }

    return $out;
  }

  /**
   * @param array<string, array{bucket: string, idx: int|string, hreflang: string, href: string}> $refs
   */
  private static function normalizeAlternateFrontUrls(array &$attached, array $refs): void {
    foreach ($refs as $info) {
      $href = self::getHrefForRef($attached, $info);
      $normalized = self::normalizeHomepageHref($href);
      if ($normalized !== $href) {
        self::setHrefForRef($attached, $info, $normalized);
      }
    }
  }

  /**
   * Normalizes canonical link href when it uses the /front alias.
   */
  private static function normalizeCanonicalFrontUrl(array &$attached): void {
    if (empty($attached['html_head']) || !is_array($attached['html_head'])) {
      return;
    }

    foreach ($attached['html_head'] as $idx => $item) {
      if (!is_array($item) || !isset($item[0]) || !is_array($item[0])) {
        continue;
      }
      $tag = $item[0];
      if (($tag['#tag'] ?? '') !== 'link') {
        continue;
      }
      $attrs = $tag['#attributes'] ?? [];
      if (($attrs['rel'] ?? '') !== 'canonical' || empty($attrs['href'])) {
        continue;
      }
      $normalized = self::normalizeHomepageHref((string) $attrs['href']);
      if ($normalized !== $attrs['href']) {
        $attached['html_head'][$idx][0]['#attributes']['href'] = $normalized;
      }
    }
  }

  /**
   * @return array<string, array{bucket: string, idx: int|string, hreflang: string, href: string}>
   */
  private static function collectAlternateRefs(array &$attached): array {
    $refs = [];

    if (!empty($attached['html_head']) && is_array($attached['html_head'])) {
      foreach ($attached['html_head'] as $idx => $item) {
        if (!is_array($item) || !isset($item[0]) || !is_array($item[0])) {
          continue;
        }
        $tag = $item[0];
        if (($tag['#tag'] ?? '') !== 'link') {
          continue;
        }
        $attrs = $tag['#attributes'] ?? [];
        if (($attrs['rel'] ?? '') !== 'alternate' || empty($attrs['hreflang'])) {
          continue;
        }
        $key = 'html_head:' . $idx;
        $refs[$key] = [
          'bucket' => 'html_head',
          'idx' => $idx,
          'hreflang' => (string) $attrs['hreflang'],
          'href' => (string) ($attrs['href'] ?? ''),
        ];
      }
    }

    if (!empty($attached['html_head_link']) && is_array($attached['html_head_link'])) {
      foreach ($attached['html_head_link'] as $idx => $item) {
        if (!is_array($item) || !isset($item[0]) || !is_array($item[0])) {
          continue;
        }
        $attrs = $item[0];
        if (($attrs['rel'] ?? '') !== 'alternate' || empty($attrs['hreflang'])) {
          continue;
        }
        $key = 'html_head_link:' . $idx;
        $refs[$key] = [
          'bucket' => 'html_head_link',
          'idx' => $idx,
          'hreflang' => (string) $attrs['hreflang'],
          'href' => (string) ($attrs['href'] ?? ''),
        ];
      }
    }

    return $refs;
  }

  /**
   * @param array{bucket: string, idx: int|string, hreflang: string, href: string} $ref
   */
  private static function getHrefForRef(array &$attached, array $ref): string {
    if ($ref['bucket'] === 'html_head') {
      return (string) ($attached['html_head'][$ref['idx']][0]['#attributes']['href'] ?? '');
    }
    return (string) ($attached['html_head_link'][$ref['idx']][0]['href'] ?? '');
  }

  /**
   * @param array{bucket: string, idx: int|string, hreflang: string, href: string} $ref
   */
  private static function setHrefForRef(array &$attached, array $ref, string $href): void {
    if ($ref['bucket'] === 'html_head') {
      $attached['html_head'][$ref['idx']][0]['#attributes']['href'] = $href;
    }
    else {
      $attached['html_head_link'][$ref['idx']][0]['href'] = $href;
    }
  }

  /**
   * @param array{bucket: string, idx: int|string, hreflang: string, href: string} $ref
   */
  private static function unsetRef(array &$attached, array $ref): void {
    if ($ref['bucket'] === 'html_head') {
      unset($attached['html_head'][$ref['idx']]);
    }
    else {
      unset($attached['html_head_link'][$ref['idx']]);
    }
  }

  /**
   * Rewrites a same-site /node/N URL to the language-specific canonical alias.
   *
   * @return string|null
   *   The new absolute URL, or NULL to leave href unchanged.
   */
  private static function rewriteNodeCanonicalHref(string $href, string $normalized_hreflang): ?string {
    if (!self::hrefTargetsThisSite($href)) {
      return NULL;
    }
    $nid = self::parseCanonicalNodeIdFromHref($href);
    if ($nid === NULL) {
      return NULL;
    }

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $node = $storage->load($nid);
    if (!$node instanceof NodeInterface) {
      return NULL;
    }

    $url = self::resolveAlternateUrl($node, $normalized_hreflang);
    return $url !== NULL ? self::normalizeHomepageHref($url) : NULL;
  }

  /**
   * Absolute canonical URL for an alternate hreflang (translation or language URL).
   */
  private static function resolveAlternateUrl(NodeInterface $node, string $normalized_hreflang): ?string {
    if ($normalized_hreflang === 'x-default') {
      $langcode = self::hreflangToCanonicalLangcode($node, 'x-default');
      return $langcode !== NULL ? self::buildLocalizedNodeUrl($node, $langcode) : NULL;
    }

    $languages = \Drupal::languageManager()->getLanguages();
    if (!isset($languages[$normalized_hreflang])) {
      return NULL;
    }

    if (!self::hasPublishedTranslation($node, $normalized_hreflang)) {
      return NULL;
    }

    return self::buildLocalizedNodeUrl($node, $normalized_hreflang);
  }

  /**
   * Builds a language-specific absolute URL using aliases and path prefixes.
   *
   * Drupal's toUrl() often omits the /ar/ prefix when EN/AR aliases share the same
   * path (e.g. /privacy-policy), which causes missing or duplicate hreflang tags.
   */
  private static function buildLocalizedNodeUrl(NodeInterface $node, string $langcode): ?string {
    $languages = \Drupal::languageManager()->getLanguages();
    if (!isset($languages[$langcode]) || !self::hasPublishedTranslation($node, $langcode)) {
      return NULL;
    }

    $language = $languages[$langcode];
    $system_path = '/node/' . $node->id();

    try {
      $alias = \Drupal::service('path_alias.manager')->getAliasByPath($system_path, $langcode);
      if ($alias === $system_path) {
        return NULL;
      }

      $internal = self::applyPathPrefixForLanguage('/' . ltrim($alias, '/'), $langcode);
      $url = self::normalizeHomepageHref(
        Url::fromUri('internal:' . $internal, ['absolute' => TRUE, 'language' => $language])->toString()
      );
      return self::isPublicHreflangUrl($url) ? $url : NULL;
    }
    catch (\Throwable $e) {
      return NULL;
    }
  }

  /**
   * Hreflang must use a public alias, never an internal /node/{nid} system path.
   */
  private static function isPublicHreflangUrl(string $url): bool {
    $path = parse_url($url, PHP_URL_PATH);
    if ($path === FALSE || $path === NULL || $path === '') {
      return FALSE;
    }

    $base_path = \Drupal::request()->getBasePath();
    if ($base_path !== '' && str_starts_with($path, $base_path)) {
      $path = substr($path, strlen($base_path)) ?: '/';
    }

    return !preg_match('#(?:^|/)node/\d+/?$#', $path);
  }

  /**
   * Applies a language path prefix to an internal alias path.
   */
  private static function applyPathPrefixForLanguage(string $path, string $langcode): string {
    $default = \Drupal::languageManager()->getDefaultLanguage()->getId();
    if ($langcode === $default) {
      return $path;
    }

    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    if (!is_array($prefixes) || !array_key_exists($langcode, $prefixes)) {
      return $path;
    }

    $prefix = (string) $prefixes[$langcode];
    if ($prefix === '') {
      return $path;
    }

    $path = '/' . ltrim($path, '/');
    $prefix_segment = '/' . $prefix;
    if ($path === $prefix_segment || str_starts_with($path, $prefix_segment . '/')) {
      return $path;
    }

    return $prefix_segment . ($path === '/' ? '' : $path);
  }

  /**
   * Adds missing en/ar alternates when Metatag omitted them but URLs are resolvable.
   *
   * @param array<string, array{bucket: string, idx: int|string, hreflang: string, href: string}> $refs
   * @param array<string, true> $remove
   */
  private static function ensureNodeLanguageAlternates(array &$attached, array &$refs, NodeInterface $node, array $remove): void {
    $present = [];
    foreach ($refs as $ref_key => $info) {
      if (isset($remove[$ref_key])) {
        continue;
      }
      $code = self::normalizeHreflangCode($info['hreflang']);
      if ($code !== '') {
        $present[$code] = TRUE;
      }
    }

    if (!isset($attached['html_head']) || !is_array($attached['html_head'])) {
      $attached['html_head'] = [];
    }

    $langcodes = ['en'];
    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    if (is_array($prefixes)) {
      $langcodes = array_values(array_unique(array_merge($langcodes, array_keys($prefixes))));
    }

    foreach ($langcodes as $code) {
      if ($code === '' || isset($present[$code]) || !self::hasPublishedTranslation($node, $code)) {
        continue;
      }
      $url = self::resolveAlternateUrl($node, $code);
      if ($url === NULL || $url === '' || !self::isPublicHreflangUrl($url)) {
        continue;
      }
      $idx = count($attached['html_head']);
      $attached['html_head'][$idx] = [
        [
          '#type' => 'html_tag',
          '#tag' => 'link',
          '#attributes' => [
            'rel' => 'alternate',
            'hreflang' => $code,
            'href' => $url,
          ],
        ],
        'motaded_hreflang_' . $code,
      ];
      $refs['html_head:ensure:' . $code] = [
        'bucket' => 'html_head',
        'idx' => $idx,
        'hreflang' => $code,
        'href' => $url,
      ];
    }
  }

  /**
   * Whether an absolute/relative href matches the current request path.
   */
  private static function hrefMatchesCurrentRequest(string $href): bool {
    $request = \Drupal::request();
    $current = rtrim($request->getSchemeAndHttpHost() . $request->getBasePath() . $request->getPathInfo(), '/');
    $parts = parse_url($href);
    if (!is_array($parts)) {
      return FALSE;
    }

    if (!empty($parts['host'])) {
      $compare = ($parts['scheme'] ?? 'https') . '://' . $parts['host'];
      if (isset($parts['port'])) {
        $compare .= ':' . $parts['port'];
      }
      $compare .= $parts['path'] ?? '/';
      if (!empty($parts['query'])) {
        $compare .= '?' . $parts['query'];
      }
      return rtrim($compare, '/') === $current;
    }

    $path = rtrim($parts['path'] ?? '', '/');
    $current_path = rtrim((string) parse_url($current, PHP_URL_PATH), '/');
    return $path !== '' && $path === $current_path;
  }

  /**
   * Whether the href points at this site (relative or same host).
   */
  private static function hrefTargetsThisSite(string $href): bool {
    $href = trim($href);
    if ($href === '') {
      return FALSE;
    }
    if (str_starts_with($href, '/')) {
      return TRUE;
    }
    $parts = parse_url($href);
    if (!is_array($parts) || empty($parts['host'])) {
      return TRUE;
    }
    $site_host = \Drupal::request()->getHost();
    return strcasecmp((string) $parts['host'], $site_host) === 0;
  }

  /**
   * Extracts node ID from a canonical /node/{nid} path (with optional base path).
   */
  private static function parseCanonicalNodeIdFromHref(string $href): ?int {
    $path = parse_url($href, PHP_URL_PATH);
    if ($path === FALSE || $path === NULL || $path === '') {
      return NULL;
    }
    $base = \Drupal::request()->getBasePath();
    if ($base !== '' && str_starts_with($path, $base)) {
      $path = substr($path, strlen($base)) ?: '/';
    }
    $path = '/' . ltrim((string) $path, '/');
    if (!preg_match('#^/node/(\d+)$#', $path, $m)) {
      return NULL;
    }
    return (int) $m[1];
  }

  /**
   * Resolves which entity translation URL to use for an alternate hreflang.
   *
   * For explicit languages (en, ar, …): only if that translation is published.
   * For x-default: site default language if published; otherwise the node's
   * original language if published; otherwise the first published translation.
   * This avoids pointing x-default at a missing default-language page (e.g. no EN).
   */
  private static function hreflangToCanonicalLangcode(NodeInterface $node, string $normalized_hreflang): ?string {
    $languages = \Drupal::languageManager()->getLanguages();

    if ($normalized_hreflang !== 'x-default') {
      if (!isset($languages[$normalized_hreflang])) {
        return NULL;
      }
      return self::hasPublishedTranslation($node, $normalized_hreflang)
        ? $normalized_hreflang
        : NULL;
    }

    $default_site = \Drupal::languageManager()->getDefaultLanguage()->getId();
    if (isset($languages[$default_site]) && self::hasPublishedTranslation($node, $default_site)) {
      return $default_site;
    }

    $original_lang = $node->getUntranslated()->language()->getId();
    if (isset($languages[$original_lang]) && self::hasPublishedTranslation($node, $original_lang)) {
      return $original_lang;
    }

    foreach ($languages as $langcode => $_language) {
      if (self::hasPublishedTranslation($node, $langcode)) {
        return $langcode;
      }
    }

    return NULL;
  }

  /**
   * Maps hreflang attribute to override config keys.
   */
  private static function normalizeHreflangCode(string $code): string {
    $code = strtolower(trim($code));
    return $code;
  }

  /**
   * Maps hreflang attribute to override row keys.
   */
  private static function overrideConfigKey(string $code): ?string {
    return match ($code) {
      'en' => 'hreflang_en',
      'ar' => 'hreflang_ar',
      'x-default' => 'hreflang_x_default',
      default => NULL,
    };
  }

  private static function routeNode(): ?NodeInterface {
    $route_match = \Drupal::routeMatch();
    $route = $route_match->getRouteName();
    if ($route !== 'entity.node.canonical') {
      return NULL;
    }
    $node = $route_match->getParameter('node');
    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Whether the node has a published translation for the language.
   */
  private static function hasPublishedTranslation(NodeInterface $node, string $langcode): bool {
    if (!$node->isTranslatable()) {
      return $node->language()->getId() === $langcode && $node->isPublished();
    }
    if (!$node->hasTranslation($langcode)) {
      return FALSE;
    }
    $tr = $node->getTranslation($langcode);
    return $tr instanceof EntityInterface && $tr->isPublished();
  }

  private static function hrefHasRedirect(string $href, string $hreflang_code): bool {
    if (self::hrefMatchesCurrentRequest($href)) {
      return FALSE;
    }

    $path = self::hrefToInternalSourcePath($href);
    if ($path === '') {
      return FALSE;
    }

    if (\Drupal::hasService('redirect.repository')) {
      $repo = \Drupal::service('redirect.repository');
      foreach (self::redirectLanguagesToTry($hreflang_code) as $lang) {
        try {
          if ($repo->findMatchingRedirect($path, [], $lang)) {
            return TRUE;
          }
        }
        catch (\Throwable $e) {
          continue;
        }
      }
    }

    $parse_htaccess = \Drupal::config('motaded_hreflang.settings')->get('parse_htaccess_redirects');
    if ($parse_htaccess === FALSE) {
      return FALSE;
    }

    return HtaccessRedirectSources::pathIsRedirectSource($path);
  }

  /**
   * Whether to replace x-default href (redirect stub or no published default lang).
   */
  private static function shouldRewriteXDefault(NodeInterface $node, string $href): bool {
    if ($href === '') {
      return FALSE;
    }
    if (self::hrefHasRedirect($href, 'x-default')) {
      return TRUE;
    }
    $default = \Drupal::languageManager()->getDefaultLanguage()->getId();
    return !self::hasPublishedTranslation($node, $default);
  }

  /**
   * Redirect destination URL, else first published translation canonical.
   */
  private static function resolveXDefaultReplacement(NodeInterface $node, string $href): ?string {
    $dest = self::findRedirectDestinationAbsoluteUrl($href);
    if ($dest !== NULL && $dest !== '') {
      return $dest;
    }
    return self::firstPublishedCanonicalUrl($node);
  }

  /**
   * Absolute URL from Redirect entity or .htaccess map for this href's path.
   */
  private static function findRedirectDestinationAbsoluteUrl(string $href): ?string {
    $path = self::hrefToInternalSourcePath($href);
    if ($path === '') {
      return NULL;
    }

    if (\Drupal::hasService('redirect.repository')) {
      $repo = \Drupal::service('redirect.repository');
      foreach (self::redirectLanguagesToTry('x-default') as $lang) {
        try {
          $redirect = $repo->findMatchingRedirect($path, [], $lang);
          if ($redirect !== NULL && method_exists($redirect, 'getRedirectUrl')) {
            $url = $redirect->getRedirectUrl();
            if ($url instanceof Url) {
              return $url->setAbsolute()->toString();
            }
          }
        }
        catch (\Throwable $e) {
          continue;
        }
      }
    }

    $parse_htaccess = \Drupal::config('motaded_hreflang.settings')->get('parse_htaccess_redirects');
    if ($parse_htaccess === FALSE) {
      return NULL;
    }

    return HtaccessRedirectSources::getRedirectTargetAbsoluteUrl($path);
  }

  /**
   * Language codes to try when resolving redirects (matches hrefHasRedirect order).
   *
   * @return list<string>
   */
  private static function redirectLanguagesToTry(string $hreflang_code): array {
    $normalized = self::normalizeHreflangCode($hreflang_code);
    $languages_to_try = [];
    if ($normalized !== '' && $normalized !== 'x-default') {
      $languages_to_try[] = $normalized;
    }
    $languages_to_try[] = Language::LANGCODE_NOT_SPECIFIED;
    $languages_to_try[] = \Drupal::languageManager()->getDefaultLanguage()->getId();
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $_language) {
      $languages_to_try[] = $langcode;
    }
    return array_values(array_unique($languages_to_try));
  }

  /**
   * First published translation canonical URL (prefers default lang, then original, then rest).
   */
  private static function firstPublishedCanonicalUrl(NodeInterface $node): ?string {
    $languages = \Drupal::languageManager()->getLanguages();
    $order = [];
    $order[] = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $order[] = $node->getUntranslated()->language()->getId();
    foreach ($languages as $langcode => $_language) {
      $order[] = $langcode;
    }
    $order = array_values(array_unique($order));

    foreach ($order as $langcode) {
      if (!isset($languages[$langcode]) || !self::hasPublishedTranslation($node, $langcode)) {
        continue;
      }
      try {
        $translation = $node->getTranslation($langcode);
        $language = \Drupal::languageManager()->getLanguage($langcode);
        return $translation->toUrl('canonical', ['absolute' => TRUE, 'language' => $language])->toString();
      }
      catch (\Throwable $e) {
        continue;
      }
    }

    return NULL;
  }

  /**
   * Converts absolute or relative URL to redirect source path (no leading slash).
   */
  private static function hrefToInternalSourcePath(string $href): string {
    $href = trim($href);
    if ($href === '') {
      return '';
    }

    if (str_starts_with($href, '/')) {
      $path = $href;
    }
    else {
      $parts = parse_url($href);
      if (empty($parts['path'])) {
        return '';
      }
      $path = $parts['path'];
    }

    $path = '/' . ltrim($path, '/');
    // Strip base path if site runs in subdirectory.
    $base = \Drupal::request()->getBasePath();
    if ($base !== '' && str_starts_with($path, $base)) {
      $path = substr($path, strlen($base)) ?: '/';
    }

    return ltrim($path, '/');
  }

  private static function resolveOverrideUrl(string $value): string {
    $value = trim($value);
    if ($value === '') {
      return '';
    }

    if (preg_match('#^https?://#i', $value)) {
      return $value;
    }

    try {
      if (str_starts_with($value, '/')) {
        return Url::fromUri('internal:' . $value)->setAbsolute()->toString();
      }
      return Url::fromUri('internal:/' . ltrim($value, '/'))->setAbsolute()->toString();
    }
    catch (\Throwable $e) {
      return $value;
    }
  }

  /**
   * Finds first override whose match_path applies to the current internal path.
   *
   * @param array<int, array<string, mixed>> $overrides
   *   Config override rows.
   *
   * @return array<string, mixed>|null
   *   The matching row or NULL.
   */
  private static function matchingOverride(array $overrides): ?array {
    $current = self::resolvePathKey(\Drupal::service('path.current')->getPath());
    if ($current === '') {
      return NULL;
    }

    foreach ($overrides as $row) {
      if (!is_array($row)) {
        continue;
      }
      $match_path = isset($row['match_path']) ? trim((string) $row['match_path']) : '';
      if ($match_path === '') {
        continue;
      }
      $match_type = ($row['match_type'] ?? 'exact') === 'prefix' ? 'prefix' : 'exact';

      $normalized_match = self::resolvePathKey($match_path);
      if ($normalized_match === '') {
        continue;
      }

      if ($match_type === 'exact' && $current === $normalized_match) {
        return $row;
      }
      if ($match_type === 'prefix') {
        $base = rtrim($normalized_match, '/');
        if ($current === $base || ($base !== '' && str_starts_with($current, $base . '/'))) {
          return $row;
        }
      }
    }

    return NULL;
  }

  /**
   * Normalizes a user-entered path or internal path to the system path (lowercase).
   */
  private static function resolvePathKey(string $path): string {
    $path = '/' . ltrim(trim($path), '/');
    $base = \Drupal::request()->getBasePath();
    if ($base !== '' && str_starts_with($path, $base)) {
      $path = substr($path, strlen($base)) ?: '/';
    }
    try {
      $lang = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $system = \Drupal::service('path_alias.manager')->getPathByAlias($path, $lang);
    }
    catch (\Throwable $e) {
      $system = $path;
    }
    return strtolower($system);
  }

}
