<?php

/**
 * @file
 * Seed real, platform-specific content for every `platform` node:
 *   • field_icon on existing help_item / benefit_item paragraphs (guessed
 *     from the paragraph title when empty)
 *   • field_resources — 3-4 new platform_resource paragraphs each (Official
 *     site, User guide, FAQs, contact / partner page)
 *   • field_related_platforms — 3 sibling platforms each
 *
 * Re-runnable: any field that already has content is left untouched. Run with:
 *   ddev drush php-script scripts/seed_platforms_content.php
 */

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/* -----------------------------------------------------------------------------
 * 1. Per-platform data map.
 * -----------------------------------------------------------------------------
 * Keyed by node title (case-insensitive match below). Each entry supplies:
 *   - 'resources' : list of [title, url, icon] for field_resources
 *   - 'related'   : list of related platform titles (matched by title below)
 *   - 'help_icons' / 'benefit_icons' : optional title=>icon hints used as
 *     fallback when guess() can't infer an icon.
 */
$data = [

  'MISA (Ministry of Investment Saudi Arabia)' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://misa.gov.sa', 'icon' => 'globe'],
      ['title' => 'Investor handbook (PDF)', 'url' => 'https://misa.gov.sa/en/investor-guide/', 'icon' => 'document'],
      ['title' => 'License services', 'url' => 'https://investsaudi.sa/en/services/licenses/', 'icon' => 'briefcase'],
      ['title' => 'Frequently asked questions', 'url' => 'https://misa.gov.sa/en/faq/', 'icon' => 'info'],
    ],
    'related' => ['Saudi Business Center (Meras)', 'ZATCA', 'Qiwa'],
  ],

  'Qiwa' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://qiwa.sa', 'icon' => 'globe'],
      ['title' => 'Employer services guide', 'url' => 'https://qiwa.sa/en/services', 'icon' => 'briefcase'],
      ['title' => 'Saudization & Nitaqat rules', 'url' => 'https://qiwa.sa/en/saudization', 'icon' => 'shield'],
      ['title' => 'Frequently asked questions', 'url' => 'https://qiwa.sa/en/help', 'icon' => 'info'],
    ],
    'related' => ['Mudad', 'Taqat', 'Saudi Business Center (Meras)'],
  ],

  'ZATCA' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://zatca.gov.sa', 'icon' => 'globe'],
      ['title' => 'e-Invoicing (FATOORA) portal', 'url' => 'https://zatca.gov.sa/en/E-Invoicing/SystemsDevelopers/Pages/default.aspx', 'icon' => 'document'],
      ['title' => 'Taxpayer e-services', 'url' => 'https://zatca.gov.sa/en/eServices/Pages/default.aspx', 'icon' => 'list_check'],
      ['title' => 'Knowledge centre & FAQs', 'url' => 'https://zatca.gov.sa/en/HelpCenter/Pages/default.aspx', 'icon' => 'info'],
    ],
    'related' => ['FASAH', 'Etimad', 'MISA (Ministry of Investment Saudi Arabia)'],
  ],

  'Balady' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://balady.gov.sa', 'icon' => 'globe'],
      ['title' => 'Activity classification guide', 'url' => 'https://balady.gov.sa/Services/Services', 'icon' => 'document'],
      ['title' => 'Building permits', 'url' => 'https://balady.gov.sa/Services/BuildingLicenses', 'icon' => 'building'],
      ['title' => 'Frequently asked questions', 'url' => 'https://balady.gov.sa/HelpCenter', 'icon' => 'info'],
    ],
    'related' => ['Saudi Business Center (Meras)', 'Najiz', 'MISA (Ministry of Investment Saudi Arabia)'],
  ],

  'Mudad' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://mudad.com.sa', 'icon' => 'globe'],
      ['title' => 'WPS compliance guide', 'url' => 'https://mudad.com.sa/services/wps', 'icon' => 'shield'],
      ['title' => 'Payroll automation services', 'url' => 'https://mudad.com.sa/services', 'icon' => 'cog'],
      ['title' => 'Frequently asked questions', 'url' => 'https://mudad.com.sa/help', 'icon' => 'info'],
    ],
    'related' => ['Qiwa', 'Taqat', 'Saudi Business Center (Meras)'],
    'help_extra' => [
      ['title' => 'Wage Protection compliance', 'body' => 'Submit WPS files in the format mandated by MHRSD and avoid non-compliance penalties.', 'icon' => 'shield'],
      ['title' => 'Payroll automation', 'body' => 'Calculate salaries, allowances, deductions and end-of-service awards in one workflow.', 'icon' => 'cog'],
      ['title' => 'Bank salary distribution', 'body' => 'Route net pay to employee accounts across participating local banks from a single portal.', 'icon' => 'briefcase'],
      ['title' => 'Self-service for employees', 'body' => 'Give workers visibility into payslips, contracts and leave balances through the Mudad app.', 'icon' => 'users'],
    ],
    'benefit_extra' => [
      ['title' => 'Monthly WPS reporting', 'body' => 'You need to submit timely wage protection files and stay green in Qiwa workforce reports.', 'icon' => 'list_check'],
      ['title' => 'Multi-bank payroll', 'body' => 'You pay employees across several Saudi banks and need one consolidated workflow.', 'icon' => 'building'],
      ['title' => 'Audit-ready payslips', 'body' => 'You want a defensible paper trail of every payroll cycle for inspections and audits.', 'icon' => 'document'],
      ['title' => 'EOS calculation', 'body' => 'You need a reliable end-of-service award calculator aligned with Saudi labour law.', 'icon' => 'check'],
    ],
    'process_extra' => [
      ['title' => 'Link your CR to Mudad', 'body' => 'Authenticate the establishment owner via Nafath and link the commercial registration.'],
      ['title' => 'Onboard employees & banks', 'body' => 'Import workers from Qiwa and map each to a bank account participating in WPS.'],
      ['title' => 'Run payroll monthly', 'body' => 'Generate the SIF file, submit to WPS, and distribute salaries within the legal window.'],
    ],
  ],

  'Etimad' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://etimad.sa', 'icon' => 'globe'],
      ['title' => 'Browse tenders', 'url' => 'https://tenders.etimad.sa', 'icon' => 'search'],
      ['title' => 'Supplier handbook', 'url' => 'https://etimad.sa/Help', 'icon' => 'document'],
      ['title' => 'Frequently asked questions', 'url' => 'https://etimad.sa/Help/FAQ', 'icon' => 'info'],
    ],
    'related' => ['ZATCA', 'MISA (Ministry of Investment Saudi Arabia)', 'FASAH'],
  ],

  'Saudi Business Center (Meras)' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://business.sa', 'icon' => 'globe'],
      ['title' => 'Setup a company', 'url' => 'https://business.sa/en/establishment-of-companies', 'icon' => 'briefcase'],
      ['title' => 'Reserve a trade name', 'url' => 'https://business.sa/en/trade-name-reservation', 'icon' => 'document'],
      ['title' => 'Help & FAQs', 'url' => 'https://business.sa/en/help', 'icon' => 'info'],
    ],
    'related' => ['MISA (Ministry of Investment Saudi Arabia)', 'Balady', 'ZATCA'],
  ],

  'Taqat' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://taqat.sa', 'icon' => 'globe'],
      ['title' => 'Job postings', 'url' => 'https://taqat.sa/jobs', 'icon' => 'search'],
      ['title' => 'Training & subsidies', 'url' => 'https://taqat.sa/programs', 'icon' => 'lightbulb'],
      ['title' => 'Frequently asked questions', 'url' => 'https://taqat.sa/help', 'icon' => 'info'],
    ],
    'related' => ['Qiwa', 'Mudad', 'Saudi Business Center (Meras)'],
  ],

  'FASAH' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://fasah.sa', 'icon' => 'globe'],
      ['title' => 'Trader handbook', 'url' => 'https://fasah.sa/en/help', 'icon' => 'document'],
      ['title' => 'Permit checklist', 'url' => 'https://fasah.sa/en/services', 'icon' => 'list_check'],
      ['title' => 'Frequently asked questions', 'url' => 'https://fasah.sa/en/help/FAQ', 'icon' => 'info'],
    ],
    'related' => ['ZATCA', 'SABER', 'Etimad'],
  ],

  'SABER' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://saber.sa', 'icon' => 'globe'],
      ['title' => 'Conformity certificate steps', 'url' => 'https://saber.sa/Help', 'icon' => 'shield'],
      ['title' => 'Technical regulations', 'url' => 'https://saber.sa/Regulations', 'icon' => 'document'],
      ['title' => 'Frequently asked questions', 'url' => 'https://saber.sa/Help/FAQ', 'icon' => 'info'],
    ],
    'related' => ['FASAH', 'ZATCA', 'Balady'],
  ],

  'Najiz' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://najiz.sa', 'icon' => 'globe'],
      ['title' => 'Legal services catalogue', 'url' => 'https://najiz.sa/applications/landing/about', 'icon' => 'document'],
      ['title' => 'Notary public services', 'url' => 'https://najiz.sa/applications/notary', 'icon' => 'shield'],
      ['title' => 'Help & FAQs', 'url' => 'https://najiz.sa/help', 'icon' => 'info'],
    ],
    'related' => ['Saudi Business Center (Meras)', 'Sejel Tijari', 'MISA (Ministry of Investment Saudi Arabia)'],
  ],

  'Sejel Tijari' => [
    'resources' => [
      ['title' => 'Official website', 'url' => 'https://sejel.mc.gov.sa', 'icon' => 'globe'],
      ['title' => 'Commercial registration inquiry', 'url' => 'https://sejel.mc.gov.sa/en/services/inquiry', 'icon' => 'search'],
      ['title' => 'User guide', 'url' => 'https://mc.gov.sa/en/eServices', 'icon' => 'document'],
      ['title' => 'Frequently asked questions', 'url' => 'https://mc.gov.sa/en/HelpCenter/Pages/FAQs.aspx', 'icon' => 'info'],
    ],
    'related' => ['Saudi Business Center (Meras)', 'Najiz', 'MISA (Ministry of Investment Saudi Arabia)'],
    'help_extra' => [
      ['title' => 'Commercial registration inquiry', 'body' => 'Look up any Saudi commercial registration to confirm the legal name, status and activities.', 'icon' => 'search'],
      ['title' => 'Activity verification', 'body' => 'Confirm which business activities a CR covers before signing supplier or partner contracts.', 'icon' => 'check'],
      ['title' => 'CR data export', 'body' => 'Pull structured CR information into your due-diligence and compliance workflows.', 'icon' => 'document'],
    ],
    'benefit_extra' => [
      ['title' => 'Due diligence on partners', 'body' => 'You need to verify a counter-party before signing or transferring funds.', 'icon' => 'shield'],
      ['title' => 'KYC & onboarding', 'body' => 'You collect verified business records for opening a corporate bank account or merchant facility.', 'icon' => 'users'],
      ['title' => 'Regulator-grade reporting', 'body' => 'You want the authoritative Ministry of Commerce reference for any audit or filing.', 'icon' => 'list_check'],
      ['title' => 'Pre-contract checks', 'body' => 'You confirm CR validity, status and authorised activities before issuing quotes or POs.', 'icon' => 'check'],
    ],
  ],

  'test' => [
    'skip' => TRUE,
  ],
];

/* -----------------------------------------------------------------------------
 * 2. Helpers.
 * ---------------------------------------------------------------------------- */

/**
 * Guess an icon key from a paragraph's title. Keep keys aligned with
 * field.storage.paragraph.field_icon allowed_values.
 */
function guess_icon(string $title): string {
  $t = mb_strtolower($title);
  $rules = [
    'shield'     => ['compliance', 'security', 'protect', 'audit', 'verify', 'verification', 'kyc'],
    'briefcase'  => ['license', 'licence', 'business setup', 'company', 'investor', 'commercial'],
    'document'   => ['document', 'contract', 'guide', 'form', 'invoice', 'declaration', 'filing', 'report'],
    'users'      => ['employee', 'workforce', 'recruit', 'hiring', 'team', 'workers', 'partner'],
    'check'      => ['eligibility', 'check', 'approval', 'validation'],
    'list_check' => ['steps', 'checklist', 'process', 'requirements', 'tasks'],
    'globe'      => ['portal', 'website', 'national', 'public'],
    'search'     => ['inquiry', 'search', 'lookup', 'find'],
    'cog'        => ['automation', 'integration', 'workflow', 'system'],
    'lightbulb'  => ['training', 'advisory', 'support', 'consult', 'guidance'],
    'chart'      => ['report', 'analytics', 'dashboard', 'kpi', 'tracking'],
    'building'   => ['municipal', 'permit', 'office', 'building', 'establishment'],
    'map_pin'    => ['address', 'location', 'region'],
    'star'       => ['premier', 'featured', 'priority'],
    'scale'      => ['legal', 'court', 'dispute', 'compliance'],
    'sparkles'   => ['benefit', 'incentive', 'reward'],
    'clock'      => ['fast', 'instant', 'quick', 'timely', 'speed'],
    'info'       => ['faq', 'help', 'support', 'about'],
    'target'     => ['saudization', 'nitaqat', 'goal', 'target'],
  ];
  foreach ($rules as $icon => $needles) {
    foreach ($needles as $needle) {
      if (str_contains($t, $needle)) {
        return $icon;
      }
    }
  }
  return 'briefcase';
}

/**
 * Ensure a paragraph entity has field_icon set. Returns TRUE if updated.
 */
function ensure_paragraph_icon(Paragraph $paragraph, ?string $fallback_title = null): bool {
  if (!$paragraph->hasField('field_icon')) {
    return FALSE;
  }
  if (!$paragraph->get('field_icon')->isEmpty()) {
    return FALSE;
  }
  $title = '';
  if ($paragraph->hasField('field_title') && !$paragraph->get('field_title')->isEmpty()) {
    $title = (string) $paragraph->get('field_title')->value;
  }
  if ($title === '' && $fallback_title) {
    $title = $fallback_title;
  }
  if ($title === '') {
    return FALSE;
  }
  $paragraph->set('field_icon', guess_icon($title));
  $paragraph->setNewRevision(TRUE);
  $paragraph->save();
  return TRUE;
}

/**
 * Create a `platform_resource` paragraph and return its [target_id, target_revision_id].
 */
function make_platform_resource(string $title, string $url, string $icon): array {
  $paragraph = Paragraph::create([
    'type' => 'platform_resource',
    'field_title' => $title,
    'field_link' => ['uri' => $url, 'title' => ''],
    'field_icon' => $icon,
  ]);
  $paragraph->setNewRevision(TRUE);
  $paragraph->save();
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

/**
 * Create a `help_item` paragraph (Key services card).
 */
function make_help_item(string $title, string $body, string $icon): array {
  $paragraph = Paragraph::create([
    'type' => 'help_item',
    'field_title' => $title,
    'field_body' => ['value' => $body, 'format' => 'basic_html'],
    'field_icon' => $icon,
  ]);
  $paragraph->setNewRevision(TRUE);
  $paragraph->save();
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

/**
 * Create a `benefit_item` paragraph (When you need this card).
 */
function make_benefit_item(string $title, string $body, string $icon): array {
  $paragraph = Paragraph::create([
    'type' => 'benefit_item',
    'field_title' => $title,
    'field_body' => ['value' => $body, 'format' => 'basic_html'],
    'field_icon' => $icon,
  ]);
  $paragraph->setNewRevision(TRUE);
  $paragraph->save();
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

/**
 * Create a `platform_process_step` paragraph (How it works step).
 */
function make_process_step(string $title, string $body): array {
  $paragraph = Paragraph::create([
    'type' => 'platform_process_step',
    'field_title' => $title,
    'field_body' => ['value' => $body, 'format' => 'basic_html'],
  ]);
  $paragraph->setNewRevision(TRUE);
  $paragraph->save();
  return [
    'target_id' => $paragraph->id(),
    'target_revision_id' => $paragraph->getRevisionId(),
  ];
}

/* -----------------------------------------------------------------------------
 * 3. Build a title => nid lookup so we can resolve "related" by title.
 * ---------------------------------------------------------------------------- */

$nid_by_title = [];
$nids = \Drupal::entityQuery('node')
  ->condition('type', 'platform')
  ->accessCheck(FALSE)
  ->execute();
foreach (Node::loadMultiple($nids) as $n) {
  $nid_by_title[mb_strtolower((string) $n->getTitle())] = (int) $n->id();
}

/* -----------------------------------------------------------------------------
 * 4. Apply per-platform updates.
 * ---------------------------------------------------------------------------- */

$summary = [];

foreach (Node::loadMultiple($nids) as $node) {
  $title = (string) $node->getTitle();
  $cfg = $data[$title] ?? null;
  if ($cfg === null || !empty($cfg['skip'])) {
    continue;
  }

  $icons_set = 0;
  $resources_set = 0;
  $related_set = 0;
  $help_added = 0;
  $benefit_added = 0;
  $process_added = 0;

  /* Icons on existing help_item paragraphs. */
  if ($node->hasField('field_how_we_help') && !$node->get('field_how_we_help')->isEmpty()) {
    foreach ($node->get('field_how_we_help') as $item) {
      $p = Paragraph::load($item->target_id);
      if ($p && ensure_paragraph_icon($p)) {
        $icons_set++;
      }
    }
  }

  /* Icons on existing benefit_item paragraphs. */
  if ($node->hasField('field_why_matters') && !$node->get('field_why_matters')->isEmpty()) {
    foreach ($node->get('field_why_matters') as $item) {
      $p = Paragraph::load($item->target_id);
      if ($p && ensure_paragraph_icon($p)) {
        $icons_set++;
      }
    }
  }

  /* Top up help_item if data provides 'help_extra' and existing count < 4. */
  if (!empty($cfg['help_extra'])) {
    $current = $node->hasField('field_how_we_help') ? $node->get('field_how_we_help')->count() : 0;
    $target = 4;
    if ($current < $target) {
      $needed = $target - $current;
      $to_add = array_slice($cfg['help_extra'], 0, $needed);
      $values = $node->hasField('field_how_we_help') ? $node->get('field_how_we_help')->getValue() : [];
      foreach ($to_add as $row) {
        $values[] = make_help_item($row['title'], $row['body'], $row['icon']);
        $help_added++;
      }
      $node->set('field_how_we_help', $values);
    }
  }

  /* Top up benefit_item similarly. */
  if (!empty($cfg['benefit_extra'])) {
    $current = $node->hasField('field_why_matters') ? $node->get('field_why_matters')->count() : 0;
    $target = 4;
    if ($current < $target) {
      $needed = $target - $current;
      $to_add = array_slice($cfg['benefit_extra'], 0, $needed);
      $values = $node->hasField('field_why_matters') ? $node->get('field_why_matters')->getValue() : [];
      foreach ($to_add as $row) {
        $values[] = make_benefit_item($row['title'], $row['body'], $row['icon']);
        $benefit_added++;
      }
      $node->set('field_why_matters', $values);
    }
  }

  /* Top up process_step if explicit override + currently empty. */
  if (!empty($cfg['process_extra'])) {
    $current = $node->hasField('field_process_steps') ? $node->get('field_process_steps')->count() : 0;
    if ($current === 0) {
      $values = [];
      foreach ($cfg['process_extra'] as $row) {
        $values[] = make_process_step($row['title'], $row['body']);
        $process_added++;
      }
      $node->set('field_process_steps', $values);
    }
  }

  /* Resources (only when empty so re-runs don't duplicate). */
  if (!empty($cfg['resources']) && $node->hasField('field_resources') && $node->get('field_resources')->isEmpty()) {
    $values = [];
    foreach ($cfg['resources'] as $r) {
      $values[] = make_platform_resource($r['title'], $r['url'], $r['icon']);
      $resources_set++;
    }
    $node->set('field_resources', $values);
  }

  /* Related platforms (only when empty). */
  if (!empty($cfg['related']) && $node->hasField('field_related_platforms') && $node->get('field_related_platforms')->isEmpty()) {
    $refs = [];
    foreach ($cfg['related'] as $rel_title) {
      $key = mb_strtolower($rel_title);
      if (isset($nid_by_title[$key]) && $nid_by_title[$key] !== (int) $node->id()) {
        $refs[] = ['target_id' => $nid_by_title[$key]];
        $related_set++;
      }
    }
    if ($refs) {
      $node->set('field_related_platforms', $refs);
    }
  }

  $node->save();

  $summary[] = sprintf(
    '%-44s | icons:%d | help+%d | why+%d | steps+%d | res:%d | rel:%d',
    $title,
    $icons_set,
    $help_added,
    $benefit_added,
    $process_added,
    $resources_set,
    $related_set
  );
}

echo PHP_EOL . str_repeat('-', 90) . PHP_EOL;
echo "Seed summary:" . PHP_EOL;
echo str_repeat('-', 90) . PHP_EOL;
foreach ($summary as $line) {
  echo $line . PHP_EOL;
}
echo str_repeat('-', 90) . PHP_EOL;
