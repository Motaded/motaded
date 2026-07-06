<?php

/**
 * @file
 * Seeds / updates the setup cost calculator landing page, block, aliases, redirect.
 *
 * Usage: drush php:script web/modules/custom/motaded_setup_cost/scripts/seed_setup_cost_landing.php
 */

declare(strict_types=1);

use Drupal\block\Entity\Block;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\path_alias\Entity\PathAlias;
use Drupal\redirect\Entity\Redirect;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$node = $storage->load(535);

if (!$node instanceof Node || $node->bundle() !== 'landing_page') {
  $node = $storage->create([
    'type' => 'landing_page',
    'title' => 'Business Setup Cost Calculator in Saudi Arabia',
    'langcode' => 'en',
    'status' => 1,
    'uid' => 1,
  ]);
  $is_new = TRUE;
}
else {
  $is_new = FALSE;
  if ($node->hasTranslation('en')) {
    $node = $node->getTranslation('en');
  }
  $node->setTitle('Business Setup Cost Calculator in Saudi Arabia');
  $node->setPublished(TRUE);
}

/** @var \Drupal\paragraphs\ParagraphInterface[] $paragraphs */
$paragraphs = [];

$hero = Paragraph::create([
  'type' => 'hero_split_banner',
  'langcode' => 'en',
  'field_hero_split_eyebrow' => 'Saudi Arabia · Company setup',
  'field_hero_split_headline_prefix' => 'What will it cost to',
  'field_hero_split_headline_accent' => 'launch your business?',
  'field_body' => [
    'value' => '<p>Get an indicative budget in a few steps—MISA and Commercial Registration, office, visas, compliance, and typical monthly operations. Tailored to your activity, structure, and team size. Guidance only; a Motaded advisor can confirm figures for your case.</p>',
    'format' => 'basic_html',
  ],
  'field_link' => [
    'uri' => 'internal:#setup-cost-estimator',
    'title' => 'Calculate my setup cost',
  ],
  'field_link_secondary' => [
    'uri' => 'internal:/contact-us',
    'title' => 'Speak to an advisor',
  ],
]);
$stat_defs = [
  ['value' => '15+', 'label' => 'Years experience', 'icon' => 'clock'],
  ['value' => '100+', 'label' => 'Businesses assisted', 'icon' => 'users'],
  ['value' => 'Gov.', 'label' => 'Systems integration', 'icon' => 'shield'],
];
$stat_refs = [];
foreach ($stat_defs as $def) {
  $stat = Paragraph::create([
    'type' => 'hero_stat',
    'langcode' => 'en',
    'field_hero_stat_value' => $def['value'],
    'field_hero_stat_label' => $def['label'],
    'field_hero_stat_icon' => $def['icon'],
  ]);
  $stat->save();
  $stat_refs[] = [
    'target_id' => $stat->id(),
    'target_revision_id' => $stat->getRevisionId(),
  ];
}
$hero->set('field_hero_split_stats', $stat_refs);
$hero->save();
$paragraphs[] = $hero;

$seo_sections = [
  [
    'title' => 'What affects company setup cost in Saudi Arabia?',
    'body' => '<p>Setup cost depends on your <strong>business activity</strong>, <strong>legal structure</strong>, <strong>ownership profile</strong>, workforce and visa needs, and <strong>office type</strong>. Government fees (MISA, Commercial Registration, municipality) are only part of the picture—professional, compliance, and operational costs often represent a significant share of year-one spend.</p><p>Use the estimator above for an indicative range; Motaded advisors can refine figures once your activity and ownership structure are confirmed.</p>',
  ],
  [
    'title' => 'Foreign investor requirements',
    'body' => '<p>Foreign investors typically require MISA licensing and may face additional documentation, attestation, and timeline steps compared with Saudi or GCC-owned structures. Ownership rules vary by activity—some sectors allow 100% foreign ownership; others require local partnership or additional approvals.</p>',
  ],
  [
    'title' => 'Office requirements explained',
    'body' => '<p>Office solutions range from virtual addresses and coworking to private offices and headquarters facilities. Your choice affects setup cost, ongoing rent, Ejari/commercial address requirements, and how authorities view your operational presence in the Kingdom.</p>',
  ],
  [
    'title' => 'MISA licensing fees',
    'body' => '<p>MISA (Ministry of Investment) fees depend on investment type, activity, and structure. Branch and Regional HQ routes have different fee profiles than a standard LLC. Published schedules change—our calculator uses indicative ranges aligned with current public guidance.</p>',
  ],
  [
    'title' => 'Hidden operational costs',
    'body' => '<p>Beyond registration: VAT registration, accounting, payroll (Qiwa/GOSI), PRO services, legal review, bank account opening, and GM/dependant visas. Budget for monthly compliance and renewal cycles, not only the initial setup invoice.</p>',
  ],
  [
    'title' => 'How long does registration take?',
    'body' => '<p>Simple Saudi-owned LLC setups may complete in a few weeks when documentation is ready. Foreign-investor and complex structures often require 4–12 weeks depending on activity, approvals, and third-party processing times.</p>',
  ],
];

$card_children = [];
foreach ($seo_sections as $section) {
  $card = Paragraph::create([
    'type' => 'card',
    'langcode' => 'en',
    'field_title' => $section['title'],
    'field_body' => [
      'value' => $section['body'],
      'format' => 'basic_html',
    ],
  ]);
  $card->save();
  $card_children[] = [
    'target_id' => $card->id(),
    'target_revision_id' => $card->getRevisionId(),
  ];
}

$cards = Paragraph::create([
  'type' => 'cards',
  'langcode' => 'en',
  'field_card_type' => 'simple_text_card',
  'field_title' => 'Understanding setup costs in Saudi Arabia',
  'field_paragraphs' => $card_children,
]);
$cards->save();
$paragraphs[] = $cards;

// Fixed Motaded packages (Basic / Pro / Inclusive) — shown after calculator via preprocess.
$packages_section = Paragraph::load(1597);
if (!$packages_section || $packages_section->bundle() !== 'section') {
  $packages_section = _motaded_setup_cost_seed_duplicate_packages_section(1597);
}
if ($packages_section) {
  $paragraphs[] = $packages_section;
}

$promo_defs = [
  [
    'existing_id' => 2673,
    'title_en' => 'HR & Workforce Support Packages',
    'body_en' => 'Additional HR support services for hiring, onboarding, workforce management, payroll setup, and employee compliance in Saudi Arabia.',
    'link_uri' => 'entity:node/400',
    'link_title_en' => 'View HR Packages',
    'title_ar' => 'حزم دعم الموارد البشرية وإدارة القوى العاملة',
    'body_ar' => 'خدمات إضافية لدعم التوظيف، وإجراءات انضمام الموظفين، وإدارة القوى العاملة، وإعداد الرواتب، والامتثال الوظيفي في المملكة العربية السعودية.',
    'link_title_ar' => 'عرض حزم الموارد البشرية',
    'media_id' => 1260,
    'side' => 'image_right',
  ],
];
foreach ($promo_defs as $def) {
  $promo = _motaded_setup_cost_seed_promo_split($def);
  if ($promo) {
    $paragraphs[] = $promo;
  }
}

$service_packages_cards = _motaded_setup_cost_seed_service_package_cards();
if ($service_packages_cards) {
  $paragraphs[] = $service_packages_cards;
}

$faq_items = [
  [
    'q' => 'How much does it cost to open a company in Saudi Arabia?',
    'a' => '<p>Most SMEs see indicative setup ranges from roughly SAR 45,000 to SAR 120,000+ depending on structure, visas, and office—use the calculator for a range tailored to your inputs.</p>',
  ],
  [
    'q' => 'Is office rental mandatory?',
    'a' => '<p>Requirements depend on structure and activity. Many routes require a valid commercial address; virtual and coworking solutions may be acceptable for certain activities—confirm for your license type.</p>',
  ],
  [
    'q' => 'Can foreigners own 100%?',
    'a' => '<p>Many activities allow 100% foreign ownership under MISA; others are restricted or require local participation. Your activity code determines eligibility.</p>',
  ],
  [
    'q' => 'What is MISA?',
    'a' => '<p>The Ministry of Investment (MISA) regulates and facilitates foreign investment, including licensing for many company types in Saudi Arabia.</p>',
  ],
  [
    'q' => 'How long does setup take?',
    'a' => '<p>Typically 2–6 weeks for straightforward cases; foreign investors and complex structures often need 4–12 weeks.</p>',
  ],
];

$accordion_children = [];
foreach ($faq_items as $item) {
  $acc = Paragraph::create([
    'type' => 'accordion',
    'langcode' => 'en',
    'field_title' => $item['q'],
    'field_body' => [
      'value' => $item['a'],
      'format' => 'basic_html',
    ],
  ]);
  $acc->save();
  $accordion_children[] = [
    'target_id' => $acc->id(),
    'target_revision_id' => $acc->getRevisionId(),
  ];
}

$accordions = Paragraph::create([
  'type' => 'accordions',
  'langcode' => 'en',
  'field_title' => 'Frequently asked questions',
  'field_paragraphs' => $accordion_children,
]);
$accordions->save();
$paragraphs[] = $accordions;

$refs = [];
foreach ($paragraphs as $p) {
  $refs[] = [
    'target_id' => $p->id(),
    'target_revision_id' => $p->getRevisionId(),
  ];
}
$node->set('field_paragraphs', $refs);
$node->save();

$nid = (int) $node->id();
echo "Landing node: {$nid}\n";

// Path aliases.
$aliases = [
  ['alias' => '/business-setup-cost-calculator', 'langcode' => 'en'],
  ['alias' => '/حاسبة-تكلفة-تأسيس-الأعمال', 'langcode' => 'ar'],
];

foreach ($aliases as $item) {
  $existing = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
    'path' => '/node/' . $nid,
    'langcode' => $item['langcode'],
  ]);
  foreach ($existing as $entity) {
    $entity->delete();
  }
  PathAlias::create([
    'path' => '/node/' . $nid,
    'alias' => $item['alias'],
    'langcode' => $item['langcode'],
  ])->save();
  echo "Alias ({$item['langcode']}): {$item['alias']}\n";
}

// Redirect legacy /cost-calculator.
$redirect_storage = \Drupal::entityTypeManager()->getStorage('redirect');
$existing_redirects = $redirect_storage->loadByProperties(['redirect_source__path' => 'cost-calculator']);
foreach ($existing_redirects as $r) {
  $r->delete();
}
Redirect::create([
  'redirect_source' => 'cost-calculator',
  'redirect_redirect' => 'internal:/business-setup-cost-calculator',
  'language' => 'en',
  'status_code' => 301,
])->save();
echo "Redirect: /cost-calculator → /business-setup-cost-calculator\n";

// Block placement (weight -7: after node hero/cards, before FAQ at -6 via preprocess).
$block_id = 'motaded_theme_setupcostestimator';
$block_storage = \Drupal::entityTypeManager()->getStorage('block');
$block = $block_storage->load($block_id);
if (!$block) {
  $block = Block::create([
    'id' => $block_id,
    'theme' => 'motaded_theme',
    'region' => 'content',
    'weight' => -7,
    'plugin' => 'setup_cost_estimator',
    'settings' => [
      'id' => 'setup_cost_estimator',
      'label' => 'Setup cost estimator',
      'label_display' => '0',
      'provider' => 'motaded_setup_cost',
      'profile_id' => '',
      'show_webform' => TRUE,
      'show_packages' => TRUE,
    ],
    'visibility' => [
      'request_path' => [
        'id' => 'request_path',
        'negate' => FALSE,
        'pages' => "/business-setup-cost-calculator\r\n/ar/حاسبة-تكلفة-تأسيس-الأعمال\r\n/حاسبة-تكلفة-تأسيس-الأعمال\r\n/node/535",
      ],
    ],
  ]);
  $block->save();
  echo "Block placed: {$block_id}\n";
}
else {
  $block->setStatus(TRUE);
  $block->setRegion('content');
  $block->setWeight(-7);
  $block->save();
  echo "Block updated: {$block_id}\n";
}

// Homepage business_cost_estimation CTA link (node 374 EN).
$home = $storage->load(374);
if ($home instanceof Node && $home->hasField('field_paragraphs')) {
  $updated = FALSE;
  foreach ($home->get('field_paragraphs') as $item) {
    $p = $item->entity;
    if ($p && $p->bundle() === 'business_cost_estimation' && $p->hasField('field_link')) {
      $p->set('field_link', [
        'uri' => 'internal:/business-setup-cost-calculator',
        'title' => $p->get('field_link')->first()->title ?? 'Open cost calculator',
      ]);
      $p->save();
      $updated = TRUE;
      echo "Updated business_cost_estimation CTA on homepage.\n";
      break;
    }
  }
  if (!$updated) {
    echo "Note: business_cost_estimation paragraph not found on node 374.\n";
  }
}

// Dedicated packages page (separate URL from calculator).
$packages_nid = _motaded_setup_cost_seed_packages_landing_page($storage);
echo "Packages landing node: {$packages_nid}\n";

\Drupal::service('router.builder')->rebuild();
\Drupal::service('cache_tags.invalidator')->invalidateTags(['node:' . $nid, 'node:' . $packages_nid]);
echo "Done. Calculator: /business-setup-cost-calculator | Packages: /Company-Setup-Cost-Packages-In-Saudi-Arabia\n";

/**
 * Cards block linking to entrepreneur, RHQ, and office solution package pages.
 */
function _motaded_setup_cost_seed_service_package_cards(): ?Paragraph {
  $card_defs = [
    [
      'title_en' => 'Entrepreneur License Package',
      'body_en' => '<p>MISA Entrepreneur License for innovative startups — support letter, application, and virtual office in one package.</p>',
      'link_uri' => 'entity:node/991',
      'link_title_en' => 'View package',
      'title_ar' => 'باقة ترخيص ريادة الأعمال',
      'body_ar' => '<p>ترخيص ريادة الأعمال من وزارة الاستثمار — خطاب دعم، طلب، ومكتب افتراضيّ في باقة واحدة.</p>',
      'link_title_ar' => 'عرض الباقة',
    ],
    [
      'title_en' => 'Regional Headquarters (RHQ) Package',
      'body_en' => '<p>Complete RHQ license and company formation for multinational corporations — end-to-end government and PRO services.</p>',
      'link_uri' => 'entity:node/992',
      'link_title_en' => 'View package',
      'title_ar' => 'باقة المقرّ الإقليميّ (RHQ)',
      'body_ar' => '<p>ترخيص المقرّ الإقليميّ وتأسيس الشركة الكامل — خدمات حكوميّة و PRO شاملة من البداية للنهاية.</p>',
      'link_title_ar' => 'عرض الباقة',
    ],
    [
      'title_en' => 'Office Solutions Packages',
      'body_en' => '<p>Premium workspaces in Riyadh — coworking, private offices, virtual office, meeting rooms, and conference facilities.</p>',
      'link_uri' => 'entity:node/993',
      'link_title_en' => 'View packages',
      'title_ar' => 'باقات حلول المكاتب',
      'body_ar' => '<p>مساحات عمل مميّزة في الرياض — مساحات مشتركة، مكاتب خاصّة، مكتب افتراضيّ، وقاعات اجتماعات.</p>',
      'link_title_ar' => 'عرض الباقات',
    ],
  ];

  $card_refs = [];
  foreach ($card_defs as $def) {
    $card = Paragraph::create([
      'type' => 'card',
      'langcode' => 'en',
      'field_title' => $def['title_en'],
      'field_body' => [
        'value' => $def['body_en'],
        'format' => 'basic_html',
      ],
      'field_link' => [
        'uri' => $def['link_uri'],
        'title' => $def['link_title_en'],
      ],
    ]);
    $card->save();
    $card->addTranslation('ar', [
      'field_title' => $def['title_ar'],
      'field_body' => [
        'value' => $def['body_ar'],
        'format' => 'basic_html',
      ],
      'field_link' => [
        'uri' => $def['link_uri'],
        'title' => $def['link_title_ar'],
      ],
    ]);
    $card->save();
    $card_refs[] = [
      'target_id' => $card->id(),
      'target_revision_id' => $card->getRevisionId(),
    ];
  }

  $cards = Paragraph::create([
    'type' => 'cards',
    'langcode' => 'en',
    'field_card_type' => 'chips_card',
    'field_title' => 'Specialized service packages',
    'field_body' => [
      'value' => '<p>Dedicated fixed packages for entrepreneur licenses, regional headquarters, and office solutions in Saudi Arabia.</p>',
      'format' => 'basic_html',
    ],
    'field_paragraphs' => $card_refs,
  ]);
  $cards->save();
  $cards->addTranslation('ar', [
    'field_card_type' => 'chips_card',
    'field_title' => 'باقات الخدمات المتخصّصة',
    'field_body' => [
      'value' => '<p>باقات ثابتة مخصّصة لتراخيص ريادة الأعمال، المقرّات الإقليميّة، وحلول المكاتب في المملكة العربية السعودية.</p>',
      'format' => 'basic_html',
    ],
  ]);
  $cards->save();

  return Paragraph::load($cards->id());
}

/**
 * Creates or updates a promo_split paragraph for calculator landing cross-links.
 *
 * @param array<string, mixed> $def
 */
function _motaded_setup_cost_seed_promo_split(array $def): ?Paragraph {
  $promo = NULL;
  if (!empty($def['existing_id'])) {
    $promo = Paragraph::load((int) $def['existing_id']);
    if ($promo && $promo->bundle() !== 'promo_split') {
      $promo = NULL;
    }
  }
  if (!$promo) {
    $promo = Paragraph::create([
      'type' => 'promo_split',
      'langcode' => 'en',
    ]);
  }
  elseif ($promo->hasTranslation('en')) {
    $promo = $promo->getTranslation('en');
  }

  $promo->set('field_title', $def['title_en']);
  $promo->set('field_body', [
    'value' => '<p>' . $def['body_en'] . '</p>',
    'format' => 'basic_html',
  ]);
  $promo->set('field_link', [
    'uri' => $def['link_uri'],
    'title' => $def['link_title_en'],
  ]);
  $promo->set('field_promo_split_side', $def['side'] ?? 'image_right');
  $promo->set('field_promo_split_list_style', 'icons_row');
  if (!empty($def['media_id'])) {
    $promo->set('field_media', ['target_id' => (int) $def['media_id']]);
  }
  $promo->save();

  if (!$promo->hasTranslation('ar')) {
    $promo->addTranslation('ar', [
      'field_title' => $def['title_ar'],
      'field_body' => [
        'value' => '<p>' . $def['body_ar'] . '</p>',
        'format' => 'basic_html',
      ],
      'field_link' => [
        'uri' => $def['link_uri'],
        'title' => $def['link_title_ar'],
      ],
    ]);
  }
  else {
    $ar = $promo->getTranslation('ar');
    $ar->set('field_title', $def['title_ar']);
    $ar->set('field_body', [
      'value' => '<p>' . $def['body_ar'] . '</p>',
      'format' => 'basic_html',
    ]);
    $ar->set('field_link', [
      'uri' => $def['link_uri'],
      'title' => $def['link_title_ar'],
    ]);
    $ar->save();
  }
  $promo->save();

  return Paragraph::load($promo->id());
}

/**
 * Duplicates a packages section paragraph tree (pricing cards + CTAs).
 */
function _motaded_setup_cost_seed_duplicate_packages_section(?Paragraph $source): ?Paragraph {
  $source = $source ?? Paragraph::load(1597);
  if (!$source || $source->bundle() !== 'section') {
    return NULL;
  }
  $children = [];
  foreach ($source->get('field_paragraphs') as $item) {
    $child = $item->entity;
    if (!$child || $child->bundle() !== 'pricing') {
      continue;
    }
    $cta_refs = [];
    foreach ($child->get('field_paragraphs') as $cta_item) {
      $cta_src = $cta_item->entity;
      if (!$cta_src || $cta_src->bundle() !== 'cta') {
        continue;
      }
      $cta = Paragraph::create([
        'type' => 'cta',
        'langcode' => 'en',
        'field_link' => $cta_src->get('field_link')->getValue(),
      ]);
      $cta->save();
      $cta_refs[] = [
        'target_id' => $cta->id(),
        'target_revision_id' => $cta->getRevisionId(),
      ];
      break;
    }
    $pricing = Paragraph::create([
      'type' => 'pricing',
      'langcode' => 'en',
      'field_title' => $child->get('field_title')->value,
      'field_cost' => $child->get('field_cost')->value,
      'field_sub_title' => $child->get('field_sub_title')->value,
      'field_plan_details' => $child->get('field_plan_details')->getValue(),
      'field_paragraphs' => $cta_refs,
    ]);
    $pricing->save();
    $children[] = [
      'target_id' => $pricing->id(),
      'target_revision_id' => $pricing->getRevisionId(),
    ];
  }
  if ($children === []) {
    return NULL;
  }
  $section = Paragraph::create([
    'type' => 'section',
    'langcode' => 'en',
    'field_paragraphs' => $children,
  ]);
  $section->save();
  return $section;
}

/**
 * Creates or updates the fixed packages sales landing (separate from calculator).
 */
function _motaded_setup_cost_seed_packages_landing_page($storage): int {
  $existing = $storage->loadByProperties([
    'type' => 'landing_page',
    'title' => 'Company Setup Cost Packages In Saudi Arabia',
  ]);
  $packages_node = $existing ? reset($existing) : NULL;
  if (!$packages_node instanceof Node) {
    $packages_node = Node::create([
      'type' => 'landing_page',
      'title' => 'Company Setup Cost Packages In Saudi Arabia',
      'langcode' => 'en',
      'status' => 1,
      'uid' => 1,
    ]);
  }
  else {
    $packages_node->setPublished(TRUE);
  }

  $hero = Paragraph::create([
    'type' => 'hero_split_banner',
    'langcode' => 'en',
    'field_hero_split_eyebrow' => 'Setup Business Services',
    'field_hero_split_headline_prefix' => 'Choose the "Motaded" package',
    'field_hero_split_headline_accent' => 'for business continuity',
    'field_body' => [
      'value' => '<p>Each package includes proactive support in government relations, guidance on localization of jobs, and day-to-day employee care, so you can focus on growth.</p>',
      'format' => 'basic_html',
    ],
    'field_link' => [
      'uri' => 'internal:/contact-us',
      'title' => 'Talk to an advisor',
    ],
  ]);
  $hero->save();

  $packages_section = _motaded_setup_cost_seed_duplicate_packages_section(Paragraph::load(1597));
  $refs = [
    [
      'target_id' => $hero->id(),
      'target_revision_id' => $hero->getRevisionId(),
    ],
  ];
  if ($packages_section) {
    $refs[] = [
      'target_id' => $packages_section->id(),
      'target_revision_id' => $packages_section->getRevisionId(),
    ];
  }
  $packages_node->set('field_paragraphs', $refs);
  $packages_node->save();

  $packages_nid = (int) $packages_node->id();
  $alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');
  foreach ($alias_storage->loadByProperties(['path' => '/node/' . $packages_nid, 'langcode' => 'en']) as $entity) {
    $entity->delete();
  }
  PathAlias::create([
    'path' => '/node/' . $packages_nid,
    'alias' => '/Company-Setup-Cost-Packages-In-Saudi-Arabia',
    'langcode' => 'en',
  ])->save();

  return $packages_nid;
}
