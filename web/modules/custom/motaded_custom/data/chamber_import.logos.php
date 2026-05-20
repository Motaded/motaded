<?php

/**
 * @file
 * Optional explicit logo URLs for chamber logo import (drush mcil).
 *
 * Keys: exact chamber title (English). Values: url (+ optional referer).
 * After changing: ddev drush mcil --title="…" --force
 *
 * @return array<string, array{url: string, referer?: string}>
 */
return [
  'UAE-Saudi Business Council' => [
    'url' => 'https://ussaudi.org/wp-content/uploads/2022/10/logo-vertical_low-def.png',
    'referer' => 'https://ussaudi.org',
  ],
  'Saudi French Business Council' => [
    'url' => 'https://cafs.org.sa/wp-content/uploads/2023/02/CAFS-Transparent-background.png',
    'referer' => 'https://cafs.org.sa',
  ],
  'Saudi German Liaison Office for Economic Affairs' => [
    'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSdgyreD9UZwYCtT3DZ-2vduT_qChISVwiHnw&s',
    'referer' => 'https://saudiarabien.ahk.de',
  ],
  'Bahrain Saudi Business Council' => [
    'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSA_n7RCcWTsfUdwWXDNqGOkuwwsjXKrxE3sQ&s',
    'referer' => 'https://www.bahrainsaudi.org',
  ],
  'Indian Business and Professional Council Riyadh' => [
    'url' => 'https://media.licdn.com/dms/image/v2/C560BAQHj1JXEJI9rnA/company-logo_200_200/company-logo_200_200/0/1630636500051/ibpcdubai_logo?e=2147483647&v=beta&t=yHia9obFUkg2RGX71ZUw4V7nSHWNmUwoiyjYjbCK66c',
    'referer' => 'https://www.linkedin.com',
  ],
  'Canadian Business Council Riyadh' => [
    'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRjJeTN5sqmwu1DWvsZzUkF3TZP6J4cY1PfTA&s',
    'referer' => 'https://www.cbcksa.com',
  ],
  'Australian Saudi Business Council' => [
    'url' => 'https://media.licdn.com/dms/image/v2/C560BAQEuoLRaEzkJAw/company-logo_200_200/company-logo_200_200/0/1630591289947?e=2147483647&v=beta&t=PflmDU8ENxsT0i7xQKYdrOuzqwaSmqBH1lrUmzFKGeM',
    'referer' => 'https://www.linkedin.com',
  ],
  'Italian Saudi Business Council' => [
    'url' => 'https://italianbusinessgroup.net/wp-content/uploads/2020/10/ISBG-Logo-Final-Transparent.png',
    'referer' => 'https://italianbusinessgroup.net',
  ],
  'Spanish Saudi Business Council' => [
    'url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQIT_lveekGUaiurgOpq4pWsphe5hpWPHrB5w&s',
    'referer' => 'https://www.icex.es',
  ],
  'Korean Saudi Business Council' => [
    'url' => 'https://images.squarespace-cdn.com/content/v1/669caa4c016b271205c3f7e4/826de7fb-d681-421f-8ab6-78e15177ad08/square+council+logo.png',
    'referer' => 'https://www.kotra.or.kr',
  ],
  'Chinese Saudi Business Council' => [
    'url' => 'https://saudipedia.com/var/site/storage/images/6/6/9/0/1420966-1-eng-GB/7ddbd964eb8c-163920.jpg',
    'referer' => 'https://saudipedia.com',
  ],
  'Swiss Saudi Business Council' => [
    'url' => 'https://media.licdn.com/dms/image/v2/C560BAQFqSuySg-k79A/company-logo_200_200/company-logo_200_200/0/1630615002890/swiss_business_hub_middle_east_logo?e=2147483647&v=beta&t=vLGbxx9txvMe-tdBxSWGsQGlFB1DqRI87cUjoRPtF6A',
    'referer' => 'https://www.linkedin.com',
  ],
];
