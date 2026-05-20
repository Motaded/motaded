<?php

/**
 * @file
 * Fetch official event hero images into module data/events/official/.
 *
 * Usage: ddev drush php:script scripts/fetch_event_official_images.php
 */

declare(strict_types=1);

$module_path = DRUPAL_ROOT . '/modules/custom/motaded_custom';
$dir = $module_path . '/data/events/official';
if (!is_dir($dir)) {
  mkdir($dir, 0755, TRUE);
}

/** @var array<string, list<string>> $sources basename => URL candidates (first valid wins) */
$sources = [
  'leap-2027-hero.jpg' => [
    'https://storage.ghost.io/c/3a/4a/3a4a6324-c839-4dcd-afc0-2c7f3ce39f4e/content/images/size/w1200/2026/02/Five-years-of-Leap-Blog_website.jpg',
  ],
  'big5-construct-2026-hero.jpg' => [
    'https://www.big5constructsaudi.com/wp-content/uploads/sites/7/2026/01/hero.jpg',
  ],
  'food-2026-hero.png' => [
    'https://www.saudifoodexpo.com/images/logo.png',
  ],
  'fmf-2027-hero.jpg' => [
    'https://img.youtube.com/vi/uL3Mtr2w8ws/maxresdefault.jpg',
  ],
  'fii-2026-hero.jpg' => [
    'https://fii-institute.org/wp-content/uploads/2025/09/FII10-Social-Card-1.jpg',
    'https://mena.entrepreneur.com/wp-content/uploads/sites/11/2025/10/1761715671-FII-2025-Riyadh.jpg',
  ],
  'cityscape-2026-hero.jpg' => [
    'https://www.cityscapeglobal.com/wp-content/uploads/sites/20/2025/09/Cityscape-Global-2025-Website-Homepage-Banner-Desktop.jpg',
  ],
  'ghe-2026-hero.jpg' => [
    'https://www.globalhealthexhibition.com/wp-content/uploads/sites/33/2025/10/GHE25-Website-Hero-Banner-Desktop.jpg',
    'https://cdn.gccbusinessnews.com/wp-content/uploads/2025/10/31171921/Global-Health-Exhibition-2025-wraps-up-GCC-Business-News.jpg',
  ],
  'stm-2026-hero.jpg' => [
    'https://www.sauditravelmarket.com/wp-content/uploads/sites/48/2025/11/STM25-Website-Hero-Banner-Desktop.jpg',
    'https://rihlattravelnews.com/wp-content/uploads/2025/02/saudi-travel-market-2025.jpeg',
  ],
  'biban-2026-hero.jpg' => [
    'https://biban.sa/wp-content/uploads/2025/11/Biban-2025-Website-Hero-Banner-Desktop.jpg',
    'https://mma.prnewswire.com/media/2815703/Monshaat.jpg?p=publish',
  ],
  'iptc-2027-hero.jpg' => [
    'https://www.spe.org/-/media/spe/images/events/conferences/iptc/2025/iptc25-hero-banner.jpg',
    'https://assets.spe.org/dims4/default/cb786bf/2147483647/strip/true/crop/1024x538+0+44/resize/1200x630!/quality/90/?url=http%3A%2F%2Fspe-brightspot.s3.us-east-2.amazonaws.com%2F80%2F5c%2F4651dbc3ccede22d2256e14dc207%2Fjpt-2020-03-iptcherocropped.jpg',
  ],
  'blackhat-mea-2026-hero.jpg' => [
    'https://www.eyeofriyadh.com/news_images/2025/12/1350c62eb42e7.jpg',
  ],
  'wds-2026-hero.jpg' => [
    'https://event-media.worlddefenseshow.com/gallery/l7MQNIP0dF86pJ1itns3dZKRA-RAH01514.jpg',
  ],
  'gais-2026-hero.jpg' => [
    'https://globalaisummit.org/wp-content/uploads/2025/09/GAIN-Summit-2025-Social-Card.jpg',
    'https://saudigazette.com.sa/uploads/images/2025/11/15/2615096.jpg',
  ],
  'index-saudi-2026-hero.jpg' => [
    'https://www.index-saudi.com/wp-content/uploads/sites/23/2025/09/index-saudi-og-2026.jpg',
  ],
  'deepfest-2026-hero.jpg' => [
    'https://storage.ghost.io/c/3a/4a/3a4a6324-c839-4dcd-afc0-2c7f3ce39f4e/content/images/size/w1200/2026/02/Five-years-of-Leap-Blog_website.jpg',
  ],
  'logistics-2026-hero.png' => [
    'https://www.saudilogisticsexpo.com/wp-content/uploads/sites/18/2025/08/SWLE-web.png',
  ],
  'sea-expo-2026-hero.png' => [
    'https://www.saudientertainmentexpo.com/wp-content/uploads/2026/04/SEA-PPCBanners-640x360-1200-x-628-px.png',
  ],
  'pharma-2026-hero.jpg' => [
    'https://cdn.gccbusinessnews.com/wp-content/uploads/2025/10/31171921/Global-Health-Exhibition-2025-wraps-up-GCC-Business-News.jpg',
  ],
  'saudi-build-2026-hero.jpg' => [
    'https://www.big5constructsaudi.com/wp-content/uploads/sites/7/2026/01/hero.jpg',
  ],
  'jittx-2026-hero.jpg' => [
    'https://rihlattravelnews.com/wp-content/uploads/2025/02/saudi-travel-market-2025.jpeg',
  ],
];

foreach ($sources as $basename => $urls) {
  $dest = $dir . '/' . $basename;
  $saved = FALSE;
  foreach ($urls as $url) {
    $ch = curl_init($url);
    if ($ch === FALSE) {
      continue;
    }
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_FOLLOWLOCATION => TRUE,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MotadedEventImport/1.0)',
      CURLOPT_SSL_VERIFYPEER => TRUE,
      CURLOPT_HTTPHEADER => ['Accept: image/*,*/*;q=0.8'],
    ]);
    $data = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($data === FALSE || $code !== 200 || strlen($data) < 512) {
      echo "SKIP $basename ($url) http=$code\n";
      continue;
    }
    $info = @getimagesizefromstring($data);
    if ($info === FALSE) {
      echo "NOT_IMAGE $basename ($url)\n";
      continue;
    }
    file_put_contents($dest, $data);
    echo "OK $basename ({$info[0]}x{$info[1]} " . strlen($data) . " bytes) ← $url\n";
    $saved = TRUE;
    break;
  }
  if (!$saved) {
    echo "FAIL $basename (all URLs failed)\n";
  }
}
