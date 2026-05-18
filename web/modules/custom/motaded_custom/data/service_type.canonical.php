<?php

/**
 * @file
 * Canonical Service type (vocabulary machine name: taxonomy) terms.
 */

declare(strict_types=1);

return [
  'terms' => [
    'Accounting services',
    'Government Relations Services',
    'Office space services',
    'Business Support',
    'HR Services',
    'Residency & Visas',
    'Supplier registration service',
  ],
  'aliases' => [
    'Residency and Visas' => 'Residency & Visas',
  ],
  'document_remap' => [
    'Compliance' => 'Accounting services',
    'Investment' => 'Business Support',
    'Technology' => 'Business Support',
  ],
];
