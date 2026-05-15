<?php

declare(strict_types=1);

namespace Drupal\motaded_setup_cost\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\motaded_setup_cost\Entity\SetupCostProfile;

/**
 * Full profile editor: steps, line items, modifiers, monthly, timelines, packages.
 */
final class SetupCostProfileForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);
    /** @var \Drupal\motaded_setup_cost\Entity\SetupCostProfileInterface $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Administrative label'),
      '#default_value' => $entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [SetupCostProfile::class, 'load'],
        'source' => ['label'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $entity->status(),
    ];

    $form['tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-general',
    ];

    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'tabs',
    ];
    $form['general']['currency'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency code'),
      '#default_value' => $entity->get('currency') ?? 'SAR',
      '#size' => 8,
      '#required' => TRUE,
    ];
    $form['general']['webform_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Lead capture webform ID'),
      '#default_value' => $entity->get('webform_id') ?? '',
      '#description' => $this->t('Machine name of the Webform embedded after estimation (e.g. setup_cost_estimate_lead).'),
    ];
    $form['general']['global_setup_buffer_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Setup total buffer (min multiplier)'),
      '#default_value' => $entity->get('global_setup_buffer_min') ?? 1,
      '#step' => 0.01,
      '#min' => 0,
    ];
    $form['general']['global_setup_buffer_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Setup total buffer (max multiplier)'),
      '#default_value' => $entity->get('global_setup_buffer_max') ?? 1,
      '#step' => 0.01,
      '#min' => 0,
    ];
    $form['general']['last_updated'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last updated (display)'),
      '#default_value' => $entity->get('last_updated') ?? '',
    ];
    $form['general']['methodology_source'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Methodology / source line'),
      '#default_value' => $entity->get('methodology_source') ?? '',
    ];
    $form['general']['disclaimer'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Disclaimer'),
      '#default_value' => $entity->get('disclaimer') ?? '',
      '#rows' => 3,
    ];

    $form['steps_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Wizard steps & options'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $form['steps_section']['help'] = [
      '#markup' => '<p>' . $this->t('Step <code>id</code> is used as the answer key. Types: <code>cards</code>, <code>checkboxes</code>, <code>slider</code>.') . '</p>',
    ];
    $steps = $entity->getSteps();
    if ($steps === [] && $entity->isNew()) {
      $steps = [['id' => '', 'label' => '', 'type' => 'cards', 'weight' => 0, 'options' => []]];
    }
    $form['steps_section']['steps'] = $this->buildStepsTable($steps);

    $form['line_items_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Setup line items'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $lineItems = $entity->getLineItems();
    if ($lineItems === [] && $entity->isNew()) {
      $lineItems = [['id' => '', 'label' => '', 'base_min' => 0, 'base_max' => 0, 'weight' => 0]];
    }
    $form['line_items_section']['line_items'] = $this->buildLineItemsTable($lineItems);

    $form['modifiers_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Line item modifiers'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $form['modifiers_section']['help'] = [
      '#markup' => '<p>' . $this->t('Match <code>dimension</code> to a step id. Use <code>value</code> for exact match, <code>*</code> for any, or <code>value_min</code>/<code>value_max</code> for numeric (slider).') . '</p>',
    ];
    $modifiers = $entity->getModifiers();
    if ($modifiers === [] && $entity->isNew()) {
      $modifiers = [['line_item_id' => '', 'dimension' => '', 'value' => '', 'add_min' => 0, 'add_max' => 0]];
    }
    $form['modifiers_section']['modifiers'] = $this->buildModifiersTable($modifiers);

    $monthly = $entity->getMonthly();
    $form['monthly_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Monthly operations'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $form['monthly_section']['base_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Base monthly min'),
      '#default_value' => $monthly['base_min'] ?? 0,
      '#min' => 0,
    ];
    $form['monthly_section']['base_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Base monthly max'),
      '#default_value' => $monthly['base_max'] ?? 0,
      '#min' => 0,
    ];
    $form['monthly_section']['per_employee_min'] = [
      '#type' => 'number',
      '#title' => $this->t('Per employee min'),
      '#default_value' => $monthly['per_employee_min'] ?? 0,
      '#min' => 0,
    ];
    $form['monthly_section']['per_employee_max'] = [
      '#type' => 'number',
      '#title' => $this->t('Per employee max'),
      '#default_value' => $monthly['per_employee_max'] ?? 0,
      '#min' => 0,
    ];
    $officeRows = $monthly['office'] ?? [];
    if ($officeRows === []) {
      $officeRows = [['option_id' => '', 'add_min' => 0, 'add_max' => 0]];
    }
    $form['monthly_section']['office'] = $this->buildMonthlyOfficeTable($officeRows);
    $serviceRows = $monthly['service'] ?? [];
    if ($serviceRows === []) {
      $serviceRows = [['option_id' => '', 'add_min' => 0, 'add_max' => 0]];
    }
    $form['monthly_section']['service'] = $this->buildMonthlyServiceTable($serviceRows);

    $form['timelines_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Timelines'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $def = $entity->getTimelineDefault();
    $form['timelines_section']['default_min_weeks'] = [
      '#type' => 'number',
      '#title' => $this->t('Default min weeks'),
      '#default_value' => $def['min_weeks'],
      '#min' => 1,
    ];
    $form['timelines_section']['default_max_weeks'] = [
      '#type' => 'number',
      '#title' => $this->t('Default max weeks'),
      '#default_value' => $def['max_weeks'],
      '#min' => 1,
    ];
    $timelines = $entity->getTimelines();
    if ($timelines === [] && $entity->isNew()) {
      $timelines = [['label' => '', 'min_weeks' => 2, 'max_weeks' => 6, 'conditions' => []]];
    }
    $form['timelines_section']['rules'] = $this->buildTimelinesTable($timelines);

    $form['packages_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Recommended packages'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $packages = $entity->getPackages();
    if ($packages === [] && $entity->isNew()) {
      $packages = [['id' => '', 'label' => '', 'description' => '', 'min_points' => 0, 'rules' => []]];
    }
    $form['packages_section']['packages'] = $this->buildPackagesTable($packages);

    $ui = $entity->getUi();
    $form['ui_section'] = [
      '#type' => 'details',
      '#title' => $this->t('UI labels'),
      '#group' => 'tabs',
      '#tree' => TRUE,
    ];
    $uiFields = [
      'progress_label' => $this->t('Progress label'),
      'result_setup_label' => $this->t('Setup cost label'),
      'result_monthly_label' => $this->t('Monthly cost label'),
      'result_timeline_label' => $this->t('Timeline label'),
      'result_breakdown_title' => $this->t('Breakdown title'),
      'packages_title' => $this->t('Packages section title'),
      'lead_title' => $this->t('Lead form title'),
      'lead_description' => $this->t('Lead form description'),
      'cta_advisor_label' => $this->t('Advisor CTA label'),
      'cta_advisor_url' => $this->t('Advisor CTA URL'),
      'empty_selection_hint' => $this->t('Empty selection hint'),
    ];
    foreach ($uiFields as $key => $title) {
      $form['ui_section'][$key] = [
        '#type' => str_contains($key, 'description') ? 'textarea' : 'textfield',
        '#title' => $title,
        '#default_value' => $ui[$key] ?? '',
        '#rows' => str_contains($key, 'description') ? 2 : NULL,
      ];
    }

    return $form;
  }

  /**
   * @param array<int, array<string, mixed>> $steps
   */
  private function buildStepsTable(array $steps): array {
    $element = [
      '#type' => 'table',
      '#header' => [
        $this->t('Step ID'),
        $this->t('Label'),
        $this->t('Type'),
        $this->t('Weight'),
        $this->t('Slider min'),
        $this->t('Slider max'),
        $this->t('Options (id|label per line)'),
      ],
    ];
    $count = max(count($steps), 6);
    for ($i = 0; $i < $count; $i++) {
      $step = $steps[$i] ?? [];
      $optionsText = '';
      foreach ($step['options'] ?? [] as $opt) {
        $optionsText .= ($opt['id'] ?? '') . '|' . ($opt['label'] ?? '') . "\n";
      }
      $element[$i]['id'] = ['#type' => 'textfield', '#default_value' => $step['id'] ?? '', '#size' => 12];
      $element[$i]['label'] = ['#type' => 'textfield', '#default_value' => $step['label'] ?? '', '#size' => 24];
      $element[$i]['type'] = [
        '#type' => 'select',
        '#options' => ['cards' => 'cards', 'checkboxes' => 'checkboxes', 'slider' => 'slider'],
        '#default_value' => $step['type'] ?? 'cards',
      ];
      $element[$i]['weight'] = ['#type' => 'number', '#default_value' => $step['weight'] ?? $i, '#size' => 4];
      $element[$i]['slider_min'] = ['#type' => 'number', '#default_value' => $step['slider_min'] ?? 1, '#size' => 4];
      $element[$i]['slider_max'] = ['#type' => 'number', '#default_value' => $step['slider_max'] ?? 50, '#size' => 4];
      $element[$i]['options_raw'] = [
        '#type' => 'textarea',
        '#default_value' => trim($optionsText),
        '#rows' => 4,
      ];
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $items
   */
  private function buildLineItemsTable(array $items): array {
    $element = [
      '#type' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Base min'),
        $this->t('Base max'),
        $this->t('Weight'),
      ],
    ];
    $count = max(count($items), 8);
    for ($i = 0; $i < $count; $i++) {
      $row = $items[$i] ?? [];
      $element[$i]['id'] = ['#type' => 'textfield', '#default_value' => $row['id'] ?? '', '#size' => 14];
      $element[$i]['label'] = ['#type' => 'textfield', '#default_value' => $row['label'] ?? '', '#size' => 28];
      $element[$i]['base_min'] = ['#type' => 'number', '#default_value' => $row['base_min'] ?? 0, '#min' => 0];
      $element[$i]['base_max'] = ['#type' => 'number', '#default_value' => $row['base_max'] ?? 0, '#min' => 0];
      $element[$i]['weight'] = ['#type' => 'number', '#default_value' => $row['weight'] ?? $i, '#size' => 4];
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $modifiers
   */
  private function buildModifiersTable(array $modifiers): array {
    $element = [
      '#type' => 'table',
      '#header' => [
        $this->t('Line item'),
        $this->t('Dimension'),
        $this->t('Value'),
        $this->t('Val min'),
        $this->t('Val max'),
        $this->t('Add min'),
        $this->t('Add max'),
        $this->t('× min'),
        $this->t('× max'),
      ],
    ];
    $count = max(count($modifiers), 12);
    for ($i = 0; $i < $count; $i++) {
      $row = $modifiers[$i] ?? [];
      foreach (['line_item_id', 'dimension', 'value', 'value_min', 'value_max'] as $text) {
        $element[$i][$text] = ['#type' => 'textfield', '#default_value' => $row[$text] ?? '', '#size' => 10];
      }
      foreach (['add_min', 'add_max', 'multiply_min', 'multiply_max'] as $num) {
        $element[$i][$num] = ['#type' => 'number', '#default_value' => $row[$num] ?? 0, '#step' => 0.01];
      }
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   */
  private function buildMonthlyOfficeTable(array $rows): array {
    $element = [
      '#type' => 'table',
      '#header' => [$this->t('Office option ID'), $this->t('Add min'), $this->t('Add max')],
      '#title' => $this->t('Office type adjustments'),
    ];
    $count = max(count($rows), 4);
    for ($i = 0; $i < $count; $i++) {
      $row = $rows[$i] ?? [];
      $element[$i]['option_id'] = ['#type' => 'textfield', '#default_value' => $row['option_id'] ?? ''];
      $element[$i]['add_min'] = ['#type' => 'number', '#default_value' => $row['add_min'] ?? 0, '#min' => 0];
      $element[$i]['add_max'] = ['#type' => 'number', '#default_value' => $row['add_max'] ?? 0, '#min' => 0];
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   */
  private function buildMonthlyServiceTable(array $rows): array {
    $element = [
      '#type' => 'table',
      '#header' => [$this->t('Service option ID'), $this->t('Add min'), $this->t('Add max')],
      '#title' => $this->t('Selected service adjustments (monthly)'),
    ];
    $count = max(count($rows), 8);
    for ($i = 0; $i < $count; $i++) {
      $row = $rows[$i] ?? [];
      $element[$i]['option_id'] = ['#type' => 'textfield', '#default_value' => $row['option_id'] ?? ''];
      $element[$i]['add_min'] = ['#type' => 'number', '#default_value' => $row['add_min'] ?? 0, '#min' => 0];
      $element[$i]['add_max'] = ['#type' => 'number', '#default_value' => $row['add_max'] ?? 0, '#min' => 0];
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $timelines
   */
  private function buildTimelinesTable(array $timelines): array {
    $element = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('Min weeks'),
        $this->t('Max weeks'),
        $this->t('Conditions (dimension|value per line)'),
      ],
      '#title' => $this->t('Timeline rules (first match wins)'),
    ];
    $count = max(count($timelines), 6);
    for ($i = 0; $i < $count; $i++) {
      $rule = $timelines[$i] ?? [];
      $condText = '';
      foreach ($rule['conditions'] ?? [] as $c) {
        $condText .= ($c['dimension'] ?? '') . '|' . ($c['value'] ?? '') . "\n";
      }
      $element[$i]['label'] = ['#type' => 'textfield', '#default_value' => $rule['label'] ?? ''];
      $element[$i]['min_weeks'] = ['#type' => 'number', '#default_value' => $rule['min_weeks'] ?? 2, '#min' => 1];
      $element[$i]['max_weeks'] = ['#type' => 'number', '#default_value' => $rule['max_weeks'] ?? 6, '#min' => 1];
      $element[$i]['conditions'] = [
        '#type' => 'textarea',
        '#default_value' => trim($condText),
        '#rows' => 3,
      ];
    }
    return $element;
  }

  /**
   * @param array<int, array<string, mixed>> $packages
   */
  private function buildPackagesTable(array $packages): array {
    $element = [
      '#type' => 'table',
      '#header' => [
        $this->t('ID'),
        $this->t('Label'),
        $this->t('Min points'),
        $this->t('Description'),
        $this->t('Scoring rules (dimension|value|points per line)'),
      ],
    ];
    $count = max(count($packages), 3);
    for ($i = 0; $i < $count; $i++) {
      $pkg = $packages[$i] ?? [];
      $rulesText = '';
      foreach ($pkg['rules'] ?? [] as $r) {
        $rulesText .= ($r['dimension'] ?? '') . '|' . ($r['value'] ?? '') . '|' . ($r['points'] ?? 0) . "\n";
      }
      $element[$i]['id'] = ['#type' => 'textfield', '#default_value' => $pkg['id'] ?? ''];
      $element[$i]['label'] = ['#type' => 'textfield', '#default_value' => $pkg['label'] ?? ''];
      $element[$i]['min_points'] = ['#type' => 'number', '#default_value' => $pkg['min_points'] ?? 0];
      $element[$i]['description'] = ['#type' => 'textarea', '#default_value' => $pkg['description'] ?? '', '#rows' => 2];
      $element[$i]['rules'] = ['#type' => 'textarea', '#default_value' => trim($rulesText), '#rows' => 4];
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    /** @var \Drupal\motaded_setup_cost\Entity\SetupCostProfile $entity */
    $entity = $this->entity;
    $entity->set('label', $form_state->getValue('label'));
    $entity->set('status', (bool) $form_state->getValue('status'));
    $entity->set('currency', $form_state->getValue('currency'));
    $entity->set('webform_id', $form_state->getValue('webform_id'));
    $entity->set('global_setup_buffer_min', (float) $form_state->getValue('global_setup_buffer_min'));
    $entity->set('global_setup_buffer_max', (float) $form_state->getValue('global_setup_buffer_max'));
    $entity->set('last_updated', $form_state->getValue('last_updated'));
    $entity->set('methodology_source', $form_state->getValue('methodology_source'));
    $entity->set('disclaimer', $form_state->getValue('disclaimer'));

    $stepsSection = $form_state->getValue('steps_section');
    $entity->set('steps', $this->parseSteps($stepsSection['steps'] ?? []));

    $lineSection = $form_state->getValue('line_items_section');
    $entity->set('line_items', $this->parseLineItems($lineSection['line_items'] ?? []));

    $modSection = $form_state->getValue('modifiers_section');
    $entity->set('modifiers', $this->parseModifiers($modSection['modifiers'] ?? []));

    $monthlySection = $form_state->getValue('monthly_section');
    $entity->set('monthly', [
      'base_min' => (float) ($monthlySection['base_min'] ?? 0),
      'base_max' => (float) ($monthlySection['base_max'] ?? 0),
      'per_employee_min' => (float) ($monthlySection['per_employee_min'] ?? 0),
      'per_employee_max' => (float) ($monthlySection['per_employee_max'] ?? 0),
      'office' => $this->parseMonthlyRows($monthlySection['office'] ?? []),
      'service' => $this->parseMonthlyRows($monthlySection['service'] ?? []),
    ]);

    $timelineSection = $form_state->getValue('timelines_section');
    $entity->set('timeline_default', [
      'min_weeks' => (int) ($timelineSection['default_min_weeks'] ?? 2),
      'max_weeks' => (int) ($timelineSection['default_max_weeks'] ?? 6),
    ]);
    $entity->set('timelines', $this->parseTimelines($timelineSection['rules'] ?? []));

    $pkgSection = $form_state->getValue('packages_section');
    $entity->set('packages', $this->parsePackages($pkgSection['packages'] ?? []));

    $uiSection = $form_state->getValue('ui_section') ?? [];
    $entity->set('ui', array_filter($uiSection, static fn($v) => $v !== '' && $v !== NULL));

    $status = $entity->save();
    $this->messenger()->addStatus($this->t('Profile %label saved.', ['%label' => $entity->label()]));
    $form_state->setRedirectUrl($entity->toUrl('collection'));
    return $status;
  }

  /**
   * @param array<int, array<int, array<string, mixed>>> $tableRows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parseSteps(array $tableRows): array {
    $out = [];
    foreach ($tableRows as $row) {
      $id = trim((string) ($row['id'] ?? ''));
      if ($id === '') {
        continue;
      }
      $options = [];
      foreach (preg_split('/\R/', (string) ($row['options_raw'] ?? '')) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }
        $parts = explode('|', $line, 2);
        $optId = trim($parts[0]);
        if ($optId === '') {
          continue;
        }
        $options[] = [
          'id' => $optId,
          'label' => trim($parts[1] ?? $optId),
          'weight' => count($options),
        ];
      }
      $out[] = [
        'id' => $id,
        'label' => (string) ($row['label'] ?? $id),
        'type' => (string) ($row['type'] ?? 'cards') ?: 'cards',
        'weight' => (int) ($row['weight'] ?? 0),
        'slider_min' => (int) ($row['slider_min'] ?? 1),
        'slider_max' => (int) ($row['slider_max'] ?? 50),
        'slider_step' => 1,
        'options' => $options,
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parseLineItems(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
      $id = trim((string) ($row['id'] ?? ''));
      if ($id === '') {
        continue;
      }
      $out[] = [
        'id' => $id,
        'label' => (string) ($row['label'] ?? $id),
        'base_min' => (float) ($row['base_min'] ?? 0),
        'base_max' => (float) ($row['base_max'] ?? 0),
        'weight' => (int) ($row['weight'] ?? 0),
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parseModifiers(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
      if (trim((string) ($row['line_item_id'] ?? '')) === '') {
        continue;
      }
      $out[] = [
        'line_item_id' => trim((string) $row['line_item_id']),
        'dimension' => trim((string) ($row['dimension'] ?? '')),
        'value' => trim((string) ($row['value'] ?? '')),
        'value_min' => trim((string) ($row['value_min'] ?? '')),
        'value_max' => trim((string) ($row['value_max'] ?? '')),
        'add_min' => (float) ($row['add_min'] ?? 0),
        'add_max' => (float) ($row['add_max'] ?? 0),
        'multiply_min' => (float) ($row['multiply_min'] ?? 0),
        'multiply_max' => (float) ($row['multiply_max'] ?? 0),
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parseMonthlyRows(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
      $id = trim((string) ($row['option_id'] ?? ''));
      if ($id === '') {
        continue;
      }
      $out[] = [
        'option_id' => $id,
        'add_min' => (float) ($row['add_min'] ?? 0),
        'add_max' => (float) ($row['add_max'] ?? 0),
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parseTimelines(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
      if (trim((string) ($row['label'] ?? '')) === '' && empty($row['conditions'])) {
        continue;
      }
      $conditions = [];
      foreach (preg_split('/\R/', (string) ($row['conditions'] ?? '')) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }
        $parts = explode('|', $line, 2);
        $conditions[] = [
          'dimension' => trim($parts[0]),
          'value' => trim($parts[1] ?? ''),
        ];
      }
      if ($conditions === [] && trim((string) ($row['label'] ?? '')) === '') {
        continue;
      }
      $out[] = [
        'label' => (string) ($row['label'] ?? ''),
        'min_weeks' => (int) ($row['min_weeks'] ?? 2),
        'max_weeks' => (int) ($row['max_weeks'] ?? 6),
        'conditions' => $conditions,
      ];
    }
    return $out;
  }

  /**
   * @param array<int, array<string, mixed>> $rows
   *
   * @return array<int, array<string, mixed>>
   */
  private function parsePackages(array $rows): array {
    $out = [];
    foreach ($rows as $row) {
      $id = trim((string) ($row['id'] ?? ''));
      if ($id === '') {
        continue;
      }
      $rules = [];
      foreach (preg_split('/\R/', (string) ($row['rules'] ?? '')) ?: [] as $line) {
        $line = trim($line);
        if ($line === '') {
          continue;
        }
        $parts = explode('|', $line, 3);
        $rules[] = [
          'dimension' => trim($parts[0]),
          'value' => trim($parts[1] ?? ''),
          'points' => (int) ($parts[2] ?? 0),
        ];
      }
      $out[] = [
        'id' => $id,
        'label' => (string) ($row['label'] ?? $id),
        'description' => (string) ($row['description'] ?? ''),
        'min_points' => (int) ($row['min_points'] ?? 0),
        'rules' => $rules,
      ];
    }
    usort($out, static fn($a, $b) => ($a['min_points'] <=> $b['min_points']));
    return $out;
  }

}
