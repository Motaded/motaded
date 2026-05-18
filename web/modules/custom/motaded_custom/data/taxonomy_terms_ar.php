<?php

/**
 * @file
 * Master Arabic taxonomy term map (core + tags).
 */

declare(strict_types=1);

$core = require __DIR__ . '/taxonomy_terms_ar.core.php';
$tags = require __DIR__ . '/taxonomy_terms_ar.tags.php';

$core['tags'] = $tags;

return $core;
