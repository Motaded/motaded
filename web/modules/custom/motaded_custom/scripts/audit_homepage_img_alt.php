<?php

/**
 * @file
 * List <img> tags on the front page with empty or missing alt.
 */

declare(strict_types=1);

$base = rtrim(\Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBaseUrl(), '/');
$url = $base . '/';
$html = @file_get_contents($url);
if ($html === FALSE) {
  print "Could not fetch {$url}\n";
  return;
}

if (!preg_match_all('/<img\b[^>]*>/i', $html, $matches)) {
  print "No images found on homepage.\n";
  return;
}

$issues = [];
foreach ($matches[0] as $tag) {
  $has_alt = preg_match('/\balt=(["\'])(.*?)\1/i', $tag, $alt_match);
  $alt = $has_alt ? trim(html_entity_decode($alt_match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')) : '';
  if ($alt === '') {
    $src = '';
    if (preg_match('/\bsrc=(["\'])(.*?)\1/i', $tag, $src_match)) {
      $src = $src_match[2];
    }
    $issues[] = ['tag' => $tag, 'src' => $src];
  }
}

print 'Homepage images: ' . count($matches[0]) . ', empty/missing alt: ' . count($issues) . "\n\n";

$skip_patterns = [
  '/icon-menu\.svg/',
  '/icon-close\.svg/',
  '/icon-search\.svg/',
  '/icon-arrow-down\.svg/',
  '/mot-mobile-menu/',
];
$content_issues = [];
foreach ($issues as $issue) {
  $skip = FALSE;
  foreach ($skip_patterns as $pattern) {
    if (preg_match($pattern, $issue['src'] . $issue['tag'])) {
      $skip = TRUE;
      break;
    }
  }
  if (!$skip) {
    $content_issues[] = $issue;
  }
}

print 'Content-area images with empty alt (excl. nav UI icons): ' . count($content_issues) . "\n\n";
foreach ($content_issues as $issue) {
  print $issue['src'] . "\n  " . $issue['tag'] . "\n\n";
}
