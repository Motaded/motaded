<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;

$report_dir = DRUPAL_ROOT . '/reports';
if (!is_dir($report_dir)) {
  mkdir($report_dir, 0775, TRUE);
}

$timestamp = date('Ymd-His');
$report_path = $report_dir . "/malformed-link-fixes-{$timestamp}.csv";

$fp = fopen($report_path, 'wb');
fputcsv($fp, [
  'node_id',
  'langcode',
  'bundle',
  'title',
  'page_url',
  'field',
  'property',
  'delta',
  'old_href',
  'new_href',
]);

$storage = \Drupal::entityTypeManager()->getStorage('node');
$alias_manager = \Drupal::service('path_alias.manager');

$nids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->execute();

$chunks = array_chunk($nids, 50);

$changed_nodes = 0;
$changed_links = 0;

foreach ($chunks as $chunk) {
  /** @var \Drupal\node\NodeInterface[] $nodes */
  $nodes = $storage->loadMultiple($chunk);

  foreach ($nodes as $node) {
    if (!$node instanceof NodeInterface) {
      continue;
    }

    $node_changed = FALSE;

    foreach ($node->getTranslationLanguages(FALSE) as $langcode => $language) {
      $translation = $node->getTranslation($langcode);
      $field_definitions = $translation->getFieldDefinitions();
      $title = $translation->label() ?? '';

      $alias = $alias_manager->getAliasByPath('/node/' . $translation->id(), $langcode);
      $page_url = $alias;

      foreach ($field_definitions as $field_name => $definition) {
        if ($definition->isComputed()) {
          continue;
        }

        $field_type = $definition->getType();
        if (!in_array($field_type, ['text', 'text_long', 'text_with_summary', 'string', 'string_long'], TRUE)) {
          continue;
        }

        if (!$translation->hasField($field_name)) {
          continue;
        }

        $field = $translation->get($field_name);
        if ($field->isEmpty()) {
          continue;
        }

        foreach ($field as $delta => $item) {
          $properties = ['value'];
          if ($field_type === 'text_with_summary') {
            $properties[] = 'summary';
          }

          foreach ($properties as $property) {
            $original = (string) ($item->{$property} ?? '');
            if ($original === '' || stripos($original, 'href=') === FALSE) {
              continue;
            }

            $local_changes = [];
            $updated = preg_replace_callback(
              '/(href\\s*=\\s*)(["\\\'])([^"\\\']*)(\\2)/iu',
              static function (array $matches) use (&$local_changes): string {
                $prefix = $matches[1];
                $quote = $matches[2];
                $href_raw = $matches[3];

                $decoded = html_entity_decode($href_raw, ENT_QUOTES | ENT_HTML5);
                if (!preg_match('/\\s/u', $decoded)) {
                  return $matches[0];
                }

                if (!preg_match('/^\\s*(https?:|www\\.)/iu', $decoded)) {
                  return $matches[0];
                }

                $clean = preg_replace('/\\s+/u', '', $decoded);
                if ($clean === NULL || $clean === $decoded) {
                  return $matches[0];
                }

                if (stripos($clean, 'www.') === 0) {
                  $clean = 'https://' . $clean;
                }

                $local_changes[] = [
                  'old' => $decoded,
                  'new' => $clean,
                ];

                $escaped = htmlspecialchars($clean, ENT_QUOTES | ENT_HTML5);
                return $prefix . $quote . $escaped . $quote;
              },
              $original
            );

            if ($updated === NULL || $updated === $original || empty($local_changes)) {
              continue;
            }

            $item->{$property} = $updated;
            $node_changed = TRUE;

            foreach ($local_changes as $change) {
              ++$changed_links;
              fputcsv($fp, [
                (string) $translation->id(),
                $langcode,
                $translation->bundle(),
                $title,
                $page_url,
                $field_name,
                $property,
                (string) $delta,
                $change['old'],
                $change['new'],
              ]);
            }
          }
        }
      }
    }

    if ($node_changed) {
      $node->save();
      ++$changed_nodes;
    }
  }
}

fclose($fp);

print "REPORT_PATH={$report_path}\n";
print "CHANGED_NODES={$changed_nodes}\n";
print "CHANGED_LINKS={$changed_links}\n";
