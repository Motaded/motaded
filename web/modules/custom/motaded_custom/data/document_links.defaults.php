<?php

/**
 * @file
 * Default sector/service link rules for Library documents.
 */

declare(strict_types=1);

return [
  // When field_sector is "General", link these sector_page hubs by document category.
  'category_sector_pages' => [
    'Business Setup' => ['Finance & Fintech', 'Technology'],
    'Compliance' => ['Finance & Fintech'],
    'Tax' => ['Finance & Fintech'],
    'HR' => ['Manufacturing', 'Healthcare', 'Logistics & Transport'],
    'Licensing' => ['Finance & Fintech', 'Construction & Infrastructure'],
  ],
  // Sector taxonomy terms without a dedicated hub → substitute hub titles.
  'sector_term_sector_pages' => [
    'Retail' => ['Manufacturing', 'Tourism'],
  ],
  // Max linked items per field (keeps document detail readable).
  'max_services' => 3,
  'max_sector_pages' => 3,
  // Service types with no dedicated pages → use pages from this canonical type.
  'service_type_fallback' => [
    'Supplier registration service' => 'Government Relations Services',
  ],
];
