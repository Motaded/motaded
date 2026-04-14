<?php

$path = DRUPAL_ROOT . '/reports/services_content_audit.csv';
$fp = fopen($path, 'w');
$headers = [
  'nid','langcode','title',
  'body','body_summary','target_audience',
  'service_duration','provided_languages','service_channels','service_cost','payment_options',
  'categories','beneficiaries','tags',
  'missing_body','missing_body_summary','missing_target_audience',
  'missing_service_duration','missing_provided_languages','missing_service_channels','missing_service_cost','missing_payment_options',
  'missing_categories','missing_beneficiaries','missing_tags'
];
fputcsv($fp, $headers);

$nids = \Drupal::entityQuery('node')
  ->accessCheck(FALSE)
  ->condition('type', 'page')
  ->condition('status', 1)
  ->condition('field_display_on_services.value', 1)
  ->sort('nid')
  ->execute();

$storage = \Drupal::entityTypeManager()->getStorage('node');
$nodes = $storage->loadMultiple($nids);

$isEmpty = static function ($value) {
  return trim((string) $value) === '';
};

$yesNo = static function (bool $missing) {
  return $missing ? 'Yes' : 'No';
};

$rows = 0;
foreach ($nodes as $node) {
  $entity = $node->hasTranslation('en') ? $node->getTranslation('en') : $node;

  $body = (string) ($entity->get('body')->value ?? '');
  $body_summary = (string) ($entity->get('body')->summary ?? '');
  $target_audience = (string) ($entity->get('field_target_audience')->value ?? '');
  $service_duration = (string) ($entity->get('field_service_duration')->value ?? '');
  $provided_languages = (string) ($entity->get('field_provided_languages')->value ?? '');
  $service_channels = (string) ($entity->get('field_service_channels')->value ?? '');
  $service_cost = (string) ($entity->get('field_service_cost')->value ?? '');
  $payment_options = (string) ($entity->get('field_payment_options')->value ?? '');

  $categories = [];
  foreach ($entity->get('field_taxonomy') as $item) {
    if ($item->entity) { $categories[] = $item->entity->label(); }
  }
  $beneficiaries = [];
  foreach ($entity->get('field_beneficiaries') as $item) {
    if ($item->entity) { $beneficiaries[] = $item->entity->label(); }
  }
  $tags = [];
  foreach ($entity->get('field_tags') as $item) {
    if ($item->entity) { $tags[] = $item->entity->label(); }
  }

  $row = [
    $entity->id(),
    $entity->language()->getId(),
    $entity->label(),
    preg_replace('/\s+/', ' ', trim(strip_tags($body))),
    preg_replace('/\s+/', ' ', trim(strip_tags($body_summary))),
    preg_replace('/\s+/', ' ', trim($target_audience)),
    trim($service_duration),
    trim($provided_languages),
    trim($service_channels),
    trim($service_cost),
    trim($payment_options),
    implode(' | ', array_unique($categories)),
    implode(' | ', array_unique($beneficiaries)),
    implode(' | ', array_unique($tags)),
    $yesNo($isEmpty($body)),
    $yesNo($isEmpty($body_summary)),
    $yesNo($isEmpty($target_audience)),
    $yesNo($isEmpty($service_duration)),
    $yesNo($isEmpty($provided_languages)),
    $yesNo($isEmpty($service_channels)),
    $yesNo($isEmpty($service_cost)),
    $yesNo($isEmpty($payment_options)),
    $yesNo(empty($categories)),
    $yesNo(empty($beneficiaries)),
    $yesNo(empty($tags)),
  ];

  fputcsv($fp, $row);
  $rows++;
}

fclose($fp);
print "Written: $path\n";
print "Rows: $rows\n";
