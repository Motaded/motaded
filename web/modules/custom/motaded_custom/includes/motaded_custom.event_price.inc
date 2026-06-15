<?php

/**
 * @file
 * Event price type + amount: form UX, validation, presave, schema offers.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\schema_metatag\SchemaMetatagManager;

/**
 * Whether the event uses a paid ticket price.
 */
function motaded_custom_event_price_type_is_paid(NodeInterface $node): bool {
  return motaded_custom_event_price_type($node) === 'paid';
}

/**
 * Returns the price type value or empty string.
 */
function motaded_custom_event_price_type(NodeInterface $node): string {
  if (!$node->hasField('field_price_range') || $node->get('field_price_range')->isEmpty()) {
    return '';
  }
  return (string) $node->get('field_price_range')->value;
}

/**
 * Whether price is fixed at zero (Free or Invite only).
 */
function motaded_custom_event_price_type_is_zero_price(NodeInterface $node): bool {
  return in_array(motaded_custom_event_price_type($node), ['free', 'invite_only'], TRUE);
}

/**
 * Formatted ticket price for display (paid events only), e.g. "100 SAR".
 */
function motaded_custom_event_price_display(NodeInterface $node): string {
  if (!motaded_custom_event_price_type_is_paid($node)) {
    return '';
  }
  $raw = motaded_custom_event_price_raw_value($node);
  if ($raw === NULL || $raw === '' || !is_numeric($raw) || (float) $raw <= 0) {
    return '';
  }
  $formatted = rtrim(rtrim(number_format((float) $raw, 2, '.', ','), '0'), '.');
  return $formatted . ' ' . (string) t('SAR');
}

/**
 * Returns the stored ticket price, falling back to the default translation.
 */
function motaded_custom_event_price_raw_value(NodeInterface $node): ?string {
  if (!$node->hasField('field_event_price')) {
    return NULL;
  }
  if (!$node->get('field_event_price')->isEmpty()) {
    return trim((string) $node->get('field_event_price')->value);
  }
  $default = $node->getUntranslated();
  if ($default->id() !== $node->id() || $default->language()->getId() !== $node->language()->getId()) {
    if (!$default->get('field_event_price')->isEmpty()) {
      return trim((string) $default->get('field_event_price')->value);
    }
  }
  return NULL;
}

/**
 * Formats a decimal field value for schema.org Offer price (string, no currency).
 */
function motaded_custom_event_price_schema_string(?string $value): string {
  if ($value === NULL || $value === '') {
    return '0';
  }
  $number = (float) $value;
  if ($number <= 0) {
    return '0';
  }
  $formatted = number_format($number, 2, '.', '');
  return rtrim(rtrim($formatted, '0'), '.') ?: '0';
}

/**
 * Schema.org availability URL for the event offer.
 */
function motaded_custom_event_price_schema_availability(NodeInterface $node): string {
  if (function_exists('motaded_custom_event_is_past') && motaded_custom_event_is_past($node)) {
    return 'https://schema.org/SoldOut';
  }
  return 'https://schema.org/InStock';
}

/**
 * validFrom: node last-changed timestamp (UTC).
 */
function motaded_custom_event_price_schema_valid_from(NodeInterface $node): ?string {
  $changed = (int) $node->getChangedTime();
  if ($changed <= 0) {
    return NULL;
  }
  return (new \DateTimeImmutable('@' . $changed))
    ->setTimezone(new \DateTimeZone('UTC'))
    ->format('Y-m-d\TH:i:sP');
}

/**
 * Builds schema.org Offer array for Event JSON-LD, or NULL when type unset.
 *
 * Paid events without a positive price return NULL (no Offer).
 *
 * @return array<string, string>|null
 */
function motaded_custom_event_price_schema_offer(NodeInterface $node): ?array {
  $type = motaded_custom_event_price_type($node);
  if ($type === '') {
    return NULL;
  }

  if (motaded_custom_event_price_type_is_zero_price($node)) {
    $price = '0';
  }
  elseif (motaded_custom_event_price_type_is_paid($node)) {
    $raw = motaded_custom_event_price_raw_value($node);
    if ($raw === NULL || $raw === '' || !is_numeric($raw) || (float) $raw <= 0) {
      return NULL;
    }
    $price = motaded_custom_event_price_schema_string($raw);
  }
  else {
    return NULL;
  }

  try {
    $url = $node->toUrl('canonical', ['absolute' => TRUE])->toString();
  }
  catch (\Throwable) {
    $url = '';
  }

  $offer = [
    '@type' => 'Offer',
    'price' => $price,
    'priceCurrency' => 'SAR',
    'availability' => motaded_custom_event_price_schema_availability($node),
  ];
  if ($url !== '') {
    $offer['url'] = $url;
  }

  $valid_from = motaded_custom_event_price_schema_valid_from($node);
  if ($valid_from !== NULL) {
    $offer['validFrom'] = $valid_from;
  }

  return $offer;
}

/**
 * Alters the event node form: radios for price type, conditional price field.
 */
function motaded_custom_event_price_form_alter(array &$form, FormStateInterface $form_state): void {
  if (empty($form['field_price_range']) || empty($form['field_event_price'])) {
    return;
  }

  $form['field_price_range']['#weight'] = 19;
  $form['field_price_range']['widget']['#type'] = 'radios';

  $form['field_event_price']['#weight'] = 20;
  $form['field_event_price']['#states'] = [
    'visible' => [
      ':input[name="field_price_range"]' => ['value' => 'paid'],
    ],
    'required' => [
      ':input[name="field_price_range"]' => ['value' => 'paid'],
    ],
  ];

  $form['#validate'][] = 'motaded_custom_event_price_validate';
}

/**
 * Validates price type and paid price amount.
 */
function motaded_custom_event_price_validate(array &$form, FormStateInterface $form_state): void {
  $type_values = $form_state->getValue('field_price_range');
  $type = is_array($type_values) ? (string) ($type_values[0]['value'] ?? '') : (string) $type_values;
  if ($type === '') {
    $form_state->setErrorByName('field_price_range', t('Price type is required.'));
    return;
  }
  if ($type !== 'paid') {
    return;
  }

  $price_values = $form_state->getValue('field_event_price');
  $raw = is_array($price_values) ? (string) ($price_values[0]['value'] ?? '') : (string) $price_values;
  $raw = trim($raw);
  if ($raw === '') {
    $form_state->setErrorByName('field_event_price', t('Price is required when Price type is Paid.'));
    return;
  }
  if (!is_numeric($raw) || (float) $raw <= 0) {
    $form_state->setErrorByName('field_event_price', t('Enter a price greater than 0 SAR for paid events.'));
  }
}

/**
 * Normalizes price on save: free/invite only → 0, paid → numeric value.
 */
function motaded_custom_event_price_presave(NodeInterface $node): void {
  if (!$node->hasField('field_event_price')) {
    return;
  }

  if (motaded_custom_event_price_type_is_zero_price($node)) {
    $node->set('field_event_price', '0');
    return;
  }

  if (!motaded_custom_event_price_type_is_paid($node)) {
    $node->set('field_event_price', NULL);
  }
}

/**
 * Injects Event schema offers from price fields.
 */
function motaded_custom_event_price_metatags_alter(array &$metatags, NodeInterface $node): void {
  $offer = motaded_custom_event_price_schema_offer($node);
  if ($offer === NULL) {
    unset($metatags['schema_event_offers']);
    return;
  }
  // schema_metatag arrayTrim() treats PHP empty("0") as empty and drops price.
  if (($offer['price'] ?? '') === '0') {
    $offer['price'] = '0.00';
  }
  $metatags['schema_event_offers'] = SchemaMetatagManager::serialize($offer);
}

/**
 * Replaces Event offers in final JSON-LD (preserves price "0" for free events).
 */
function motaded_custom_event_page_jsonld_alter(array &$html_head): void {
  $node = \Drupal::routeMatch()->getParameter('node');
  if (!$node instanceof NodeInterface || $node->bundle() !== 'event') {
    return;
  }

  $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
  if ($node->hasTranslation($langcode)) {
    $node = $node->getTranslation($langcode);
  }

  $offer = motaded_custom_event_price_schema_offer($node);

  foreach ($html_head as &$attachment) {
    $element = is_array($attachment) && isset($attachment[0]) ? $attachment[0] : $attachment;
    $value = $element['#value'] ?? NULL;
    if (!is_string($value) || $value === '') {
      continue;
    }
    $data = json_decode($value, TRUE);
    if (!is_array($data) || empty($data['@graph']) || !is_array($data['@graph'])) {
      continue;
    }

    $changed = FALSE;
    foreach ($data['@graph'] as &$item) {
      if (!is_array($item) || ($item['@type'] ?? '') !== 'Event') {
        continue;
      }
      if ($offer === NULL) {
        if (array_key_exists('offers', $item)) {
          unset($item['offers']);
          $changed = TRUE;
        }
        continue;
      }
      $item['offers'] = $offer;
      $changed = TRUE;
    }
    unset($item);

    if (!$changed) {
      continue;
    }

    $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (isset($attachment[0])) {
      $attachment[0]['#value'] = $encoded;
    }
    else {
      $attachment['#value'] = $encoded;
    }
  }
}
