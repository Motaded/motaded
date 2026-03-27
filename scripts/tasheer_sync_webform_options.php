#!/usr/bin/env php
<?php

/**
 * @file
 * Generates webform.webform_options.*.yml from tasheer-analysis/drupal-webform-data.json.
 *
 * Usage (project root): php scripts/tasheer_sync_webform_options.php
 *
 * Commit the updated YAML under config/sync. Then: ddev drush cim -y && ddev drush cr
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_readable($autoload)) {
  fwrite(STDERR, "Run composer install. Missing: {$autoload}\n");
  exit(1);
}
require $autoload;

use Symfony\Component\Yaml\Yaml;

$jsonPath = $root . '/tasheer-analysis/drupal-webform-data.json';
$syncDir = $root . '/config/sync';

if (!is_readable($jsonPath)) {
  fwrite(STDERR, "File not found: {$jsonPath}\n");
  exit(1);
}

$raw = file_get_contents($jsonPath);
$data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

$fields = $data['fields'] ?? [];
$byKey = [];
foreach ($fields as $field) {
  if (!empty($field['key'])) {
    $byKey[$field['key']] = $field;
  }
}

$sets = [
  'tasheer_nationality' => [
    'uuid' => 'a1a1a1a1-1111-4111-8111-111111111101',
    'label' => 'Tasheer — Select Nationality',
    'category' => 'Tasheer',
    'options' => optionsToMap($byKey['select_nationality']['options'] ?? []),
  ],
  'tasheer_visa_type' => [
    'uuid' => 'a1a1a1a1-1111-4111-8111-111111111102',
    'label' => 'Tasheer — Visa Type',
    'category' => 'Tasheer',
    'options' => optionsToMap($byKey['visa_type']['options'] ?? []),
  ],
  'tasheer_number_of_entries' => [
    'uuid' => 'a1a1a1a1-1111-4111-8111-111111111103',
    'label' => 'Tasheer — Number of Entries',
    'category' => 'Tasheer',
    'options' => optionsToMap($byKey['number_of_entries']['options'] ?? []),
  ],
  'tasheer_visa_validity' => [
    'uuid' => 'a1a1a1a1-1111-4111-8111-111111111104',
    'label' => 'Tasheer — Visa Validity',
    'category' => 'Tasheer',
    'options' => optionsToMap($byKey['visa_validity']['options'] ?? []),
  ],
];

foreach ($sets as $id => $meta) {
  $optionsYaml = Yaml::dump($meta['options'], 4, 2);
  $optionsYaml = rtrim($optionsYaml) . "\n";

  $labelEsc = str_replace("'", "''", $meta['label']);
  $catEsc = str_replace("'", "''", $meta['category']);

  $file = "uuid: {$meta['uuid']}\n";
  $file .= "langcode: en\n";
  $file .= "status: true\n";
  $file .= "dependencies:\n";
  $file .= "  enforced:\n";
  $file .= "    module:\n";
  $file .= "      - webform\n";
  $file .= "id: {$id}\n";
  $file .= "label: '{$labelEsc}'\n";
  $file .= "category: '{$catEsc}'\n";
  $file .= "likert: false\n";
  $file .= "options: |\n";
  $file .= indentBlock($optionsYaml);

  $path = $syncDir . '/webform.webform_options.' . $id . '.yml';
  if (file_put_contents($path, $file) === FALSE) {
    fwrite(STDERR, "Failed to write {$path}\n");
    exit(1);
  }
  fwrite(STDOUT, "Wrote {$path}\n");
}

// Write motaded_custom config with condition mappings.
$motadedConfig = [
  'webform_id' => 'tasheer_visa_appointment',
  'json_path' => 'tasheer-analysis/drupal-webform-data.json',
  'options' => [
    'nationality' => 'tasheer_nationality',
    'visa_type' => 'tasheer_visa_type',
    'number_of_entries' => 'tasheer_number_of_entries',
    'visa_validity' => 'tasheer_visa_validity',
  ],
  'conditions' => [
    'visa_type_by_nationality' => (array) ($data['conditions']['visaTypeByNationality'] ?? []),
    'entries_by_nat_visa_type' => (array) ($data['conditions']['entriesByNatVisaType'] ?? []),
    'validity_by_nat_visa_type_entry' => (array) ($data['conditions']['validityByNatVisaTypeEntry'] ?? []),
  ],
  'messages' => [
    'no_options' => 'No options available',
  ],
];
$motadedPath = $syncDir . '/motaded_custom.tasheer_visa.yml';
if (file_put_contents($motadedPath, Yaml::dump($motadedConfig, 8, 2)) === FALSE) {
  fwrite(STDERR, "Failed to write {$motadedPath}\n");
  exit(1);
}
fwrite(STDOUT, "Wrote {$motadedPath}\n");

/**
 * @param array<int, array{value: string, text: string}> $options
 *
 * @return array<string, string>
 */
function optionsToMap(array $options): array {
  $map = [];
  foreach ($options as $opt) {
    if (!isset($opt['value'], $opt['text'])) {
      continue;
    }
    $map[(string) $opt['value']] = (string) $opt['text'];
  }
  return $map;
}

/**
 * @param string $yaml
 *   Inner YAML (key: value lines).
 */
function indentBlock(string $yaml): string {
  $lines = explode("\n", rtrim($yaml));
  $out = [];
  foreach ($lines as $line) {
    $out[] = $line === '' ? '' : '  ' . $line;
  }
  return implode("\n", $out) . "\n";
}
