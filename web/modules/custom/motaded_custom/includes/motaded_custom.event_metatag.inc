<?php

/**
 * @file
 * Event nodes: Schema.org Event fields for Google rich results.
 */

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Maps field_event_format values to schema.org eventAttendanceMode URLs.
 */
function motaded_custom_event_attendance_mode_url(NodeInterface $node): string {
  $map = [
    'in_person' => 'https://schema.org/OfflineEventAttendanceMode',
    'online' => 'https://schema.org/OnlineEventAttendanceMode',
    'hybrid' => 'https://schema.org/MixedEventAttendanceMode',
  ];
  if ($node->hasField('field_event_format') && !$node->get('field_event_format')->isEmpty()) {
    $key = (string) $node->get('field_event_format')->value;
    if (isset($map[$key])) {
      return $map[$key];
    }
  }
  return $map['in_person'];
}

/**
 * Alters merged metatags for event nodes (dates, status, attendance mode).
 */
function motaded_custom_event_metatags_alter(array &$metatags, NodeInterface $node): void {
  if ($node->hasField('field_event_start') && !$node->get('field_event_start')->isEmpty()) {
    $start = $node->get('field_event_start')->date;
    if ($start !== NULL) {
      $metatags['schema_event_start_date'] = $start->format(\DateTimeInterface::ATOM);
    }
  }
  else {
    unset($metatags['schema_event_start_date']);
  }

  if ($node->hasField('field_event_end') && !$node->get('field_event_end')->isEmpty()) {
    $end = $node->get('field_event_end')->date;
    if ($end !== NULL) {
      $metatags['schema_event_end_date'] = $end->format(\DateTimeInterface::ATOM);
    }
  }
  else {
    unset($metatags['schema_event_end_date']);
  }

  $metatags['schema_event_event_attendance_mode'] = motaded_custom_event_attendance_mode_url($node);
  $metatags['schema_event_event_status'] = 'https://schema.org/EventScheduled';
}
