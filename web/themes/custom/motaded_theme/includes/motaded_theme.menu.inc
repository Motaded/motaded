<?php

/**
 * @file
 * Main-menu helpers (icons, localized titles).
 */

use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;

/**
 * Replaces menu item titles with Arabic sector_page node labels on /ar.
 */
function motaded_theme_menu_apply_content_language_titles(array &$items): void {
  $langcode = \Drupal::languageManager()
    ->getCurrentLanguage(LanguageInterface::TYPE_INTERFACE)
    ->getId();
  if ($langcode !== 'ar') {
    return;
  }

  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $repository = \Drupal::service('entity.repository');
  $path_validator = \Drupal::service('path.validator');

  foreach ($items as &$item) {
    if (!empty($item['below']) && is_array($item['below'])) {
      motaded_theme_menu_apply_content_language_titles($item['below']);
    }

    if (empty($item['url']) || !($item['url'] instanceof Url)) {
      continue;
    }

    $url = $item['url'];
    $nid = 0;
    if ($url->isRouted() && $url->getRouteName() === 'entity.node.canonical') {
      $nid = (int) ($url->getRouteParameters()['node'] ?? 0);
    }
    elseif (!$url->isExternal()) {
      $path = '';
      if ($url->isRouted()) {
        $internal = $url->getInternalPath();
        if ($internal !== '') {
          $path = '/' . ltrim($internal, '/');
        }
      }
      else {
        $uri = $url->getUri();
        if (str_starts_with($uri, 'internal:')) {
          $path = parse_url($uri, PHP_URL_PATH) ?: '';
        }
      }
      if ($path !== '') {
        $valid = $path_validator->getUrlIfValidWithoutAccessCheck($path);
        if ($valid instanceof Url
          && $valid->isRouted()
          && $valid->getRouteName() === 'entity.node.canonical') {
          $nid = (int) ($valid->getRouteParameters()['node'] ?? 0);
        }
      }
    }

    if ($nid <= 0) {
      continue;
    }

    $node = $storage->load($nid);
    if (!$node instanceof NodeInterface || $node->bundle() !== 'sector_page') {
      continue;
    }
    if (!$node->hasTranslation('ar')) {
      continue;
    }

    $title = (string) $repository->getTranslationFromContext($node, 'ar')->label();
    if ($title === '') {
      continue;
    }

    $item['title'] = $title;
    $item['#cache']['tags'] = Cache::mergeTags(
      $item['#cache']['tags'] ?? [],
      $node->getCacheTags()
    );
  }
}
