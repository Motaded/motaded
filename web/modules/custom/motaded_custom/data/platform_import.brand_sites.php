<?php

/**
 * @file
 * Official brand portals to scan for logos (before field_external_link host).
 *
 * Use when CSV links to a parent ministry page (e.g. HRSD) but the product
 * has its own domain (qiwa.sa, muqeem.sa).
 *
 * @return array<string, list<string>>
 */
return [
  'ZATCA' => ['https://zatca.gov.sa'],
  'MISA (Ministry of Investment Saudi Arabia)' => ['https://misa.gov.sa', 'https://investsaudi.sa'],
  'Qiwa' => ['https://www.qiwa.sa', 'https://qiwa.sa'],
  'Muqeem' => ['https://muqeem.sa', 'https://www.muqeem.sa'],
  'GOSI' => ['https://www.gosi.gov.sa'],
  'Absher' => ['https://www.absher.sa', 'https://absher.sa'],
  'Balady' => ['https://balady.gov.sa'],
  'Mudad' => ['https://mudad.sa', 'https://www.mudad.sa'],
  'Saudi Business Center (Meras)' => ['https://business.sa'],
  'FASAH' => ['https://www.fasah.sa'],
  'SABER' => ['https://saber.sa'],
  'Etimad' => ['https://etimad.sa'],
  'Taqat' => ['https://www.taqat.sa'],
  'Najiz' => ['https://najiz.sa'],
  'Sejel Tijari' => ['https://sejel.mc.gov.sa'],
];
