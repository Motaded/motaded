<?php

/**
 * @file
 * Lists available colors and color schemes for the Bartik theme.
 */
include('pt-color-sets.inc');

$schemes=[];
$i=0;
foreach ($color_schemes as $key => $value) {
  $schemes[$key]=[
      'title' => $value['title'],
      'colors' => [
        'top' => $value['colors']['Primary Color'],
        'bottom' => $value['colors']['Secondary Color'],
        // 'bg' => '#ffffff',
        // 'titleslogan' => '#fffeff',
        // 'text' => '#3b3b3b',
      ],
    ];
}

$info = [
  'fields' => [
    'top' => t('Primary Color'),
    'bottom' => t('Secondary Color'),
    // 'bg' => t('Main background'),
    // 'titleslogan' => t('Title'),
    // 'text' => t('Text color'),
  ],
  // Pre-defined color schemes.
  'schemes' => $schemes,

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => [
    'css/colors.css',
  ],

  // Files to copy.
  'copy' => [
    'logo.svg',
  ],

  // Preview files.
  'preview_library' => 'event_pro/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'bartik'),
        // 'top' => theme_get_setting('top'),
      ],
    ],
  ],
];