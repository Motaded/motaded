<?php

/**
 * @file
 * Apply Arabic (ar) translations to menu_link_content titles from dataset + locale.
 *
 * Usage:
 *   ddev drush php:script modules/custom/motaded_custom/scripts/translate_menu_links_ar.php
 *   drush mmnar
 */

declare(strict_types=1);

use Drupal\menu_link_content\MenuLinkContentInterface;

/**
 * Normalizes menu titles for lookup (trim, collapse whitespace).
 */
function motaded_menu_normalize_label(string $label): string {
  return trim(preg_replace('/\s+/u', ' ', $label) ?? $label);
}

/**
 * Returns TRUE when the string is predominantly Arabic script.
 */
function motaded_menu_is_mostly_arabic(string $text): bool {
  if ($text === '') {
    return FALSE;
  }
  $letters = preg_replace('/[^\p{Arabic}]/u', '', $text) ?? '';
  $latin = preg_replace('/[^a-zA-Z]/', '', $text) ?? '';
  return mb_strlen($letters) > mb_strlen($latin);
}

/**
 * Resolves an Arabic menu title from dataset and interface translation.
 */
function motaded_menu_resolve_ar_label(string $en_label, array $map): ?string {
  $key = motaded_menu_normalize_label($en_label);
  if ($key === '') {
    return NULL;
  }
  if (isset($map[$key])) {
    return $map[$key];
  }
  $translated = (string) t($key, [], ['langcode' => 'ar']);
  if ($translated !== '' && $translated !== $key) {
    return $translated;
  }
  return NULL;
}

/** @var array<string, string> $MAP */
$MAP = require dirname(__DIR__) . '/data/menu_links_ar.php';

$normalized = [];
foreach ($MAP as $en => $ar) {
  $key = motaded_menu_normalize_label((string) $en);
  if ($key !== '') {
    $normalized[$key] = (string) $ar;
  }
}

// Services mega-menu and related links.
$services_file = dirname(__DIR__) . '/data/menu_links_ar.services.php';
if (is_readable($services_file)) {
  $services = require $services_file;
  foreach ($services as $en => $ar) {
    $key = motaded_menu_normalize_label((string) $en);
    if ($key !== '' && is_string($ar) && $ar !== '') {
      $normalized[$key] = (string) $ar;
    }
  }
}

// Sector taxonomy + sector_page titles (Energy, Finance & Fintech, …).
$sectors_file = dirname(__DIR__) . '/data/sectors_ar.dataset.php';
if (is_readable($sectors_file)) {
  $sectors = require $sectors_file;
  foreach (($sectors['taxonomy'] ?? []) as $en => $ar) {
    $key = motaded_menu_normalize_label((string) $en);
    if ($key !== '' && is_string($ar) && $ar !== '') {
      $normalized[$key] = (string) $ar;
    }
  }
  foreach (($sectors['nodes'] ?? []) as $en => $row) {
    if (!is_array($row)) {
      continue;
    }
    $ar_title = (string) ($row['title'] ?? '');
    if ($ar_title === '') {
      continue;
    }
    $key = motaded_menu_normalize_label((string) $en);
    if ($key !== '') {
      $normalized[$key] = $ar_title;
    }
  }
}

$storage = \Drupal::entityTypeManager()->getStorage('menu_link_content');
$ids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->sort('id')
  ->execute();

$applied = 0;
$skipped = 0;
$fallback = 0;
$unresolved = [];

$ensure_ar = static function (MenuLinkContentInterface $link, string $ar_title) use (&$applied): void {
  $ar_title = motaded_menu_normalize_label($ar_title);
  if ($ar_title === '') {
    return;
  }

  $source = $link->getUntranslated();
  $values = [
    'title' => $ar_title,
    'enabled' => (bool) $source->get('enabled')->value,
  ];
  if ($source->hasField('link') && !$source->get('link')->isEmpty()) {
    $values['link'] = $source->get('link')->getValue();
  }
  if ($source->hasField('description') && !$source->get('description')->isEmpty()) {
    $values['description'] = $source->get('description')->getValue();
  }

  if (!$link->hasTranslation('ar')) {
    $link->addTranslation('ar', $values);
  }
  else {
    $translation = $link->getTranslation('ar');
    $translation->set('title', $ar_title);
    if (isset($values['link'])) {
      $translation->set('link', $values['link']);
    }
  }

  $link->save();
  $applied++;
};

foreach ($ids as $id) {
  $link = $storage->load((int) $id);
  if (!$link instanceof MenuLinkContentInterface) {
    continue;
  }

  $source = $link->getUntranslated();
  $en = $link->hasTranslation('en')
    ? $link->getTranslation('en')
    : $source;
  $label = motaded_menu_normalize_label((string) $en->label());

  $ar_title = motaded_menu_resolve_ar_label($label, $normalized);
  if ($ar_title === NULL) {
    if (motaded_menu_is_mostly_arabic($label)) {
      $ar_title = $label;
      $fallback++;
    }
    else {
      $menu_name = $link->get('menu_name')->value ?? '';
      $unresolved[] = $menu_name . '|' . $id . '|' . $label;
      continue;
    }
  }

  if ($link->hasTranslation('ar')) {
    $existing = motaded_menu_normalize_label((string) $link->getTranslation('ar')->label());
    if ($existing === motaded_menu_normalize_label($ar_title)) {
      $skipped++;
      continue;
    }
  }

  $ensure_ar($link, $ar_title);
}

\Drupal::service('cache_tags.invalidator')->invalidateTags(['config:system.menu']);

print sprintf(
  "Menu AR: applied=%d, unchanged=%d, arabic_fallback=%d, unresolved=%d\n",
  $applied,
  $skipped,
  $fallback,
  count($unresolved),
);

if ($unresolved !== []) {
  print "Unresolved menu links (add to data/menu_links_ar.php):\n";
  foreach ($unresolved as $line) {
    print '  ' . $line . "\n";
  }
}
