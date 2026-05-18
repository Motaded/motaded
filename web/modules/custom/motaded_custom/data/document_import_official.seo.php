<?php

/**
 * @file
 * SEO meta for official document import (key = EN title). Built from catalog rows.
 */

declare(strict_types=1);

require_once __DIR__ . '/motaded_custom_seo.helpers.php';

/** @var list<array<string, mixed>> $rows */
$rows = require __DIR__ . '/document_import_official.catalog.php';

return motaded_custom_document_seo_from_catalog($rows);
