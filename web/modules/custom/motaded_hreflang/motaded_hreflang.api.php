<?php

/**
 * @file
 * Hooks for Motaded hreflang.
 */

/**
 * Alter parsed Redirect map from web/.htaccess.
 *
 * Keys are internal paths without leading slash (e.g. blog/type-license).
 * Values are destination URLs as written in .htaccess (often absolute https).
 *
 * @param array<string, string> $map
 *   Source path => redirect target URL.
 *
 * @see \Drupal\motaded_hreflang\HtaccessRedirectSources
 */
function hook_motaded_hreflang_htaccess_redirect_map_alter(array &$map): void {
}
