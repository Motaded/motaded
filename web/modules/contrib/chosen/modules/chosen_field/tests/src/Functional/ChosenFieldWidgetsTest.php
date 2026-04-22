<?php

namespace Drupal\Tests\chosen_field\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\field\Functional\FieldTestBase;

/**
 * Test the Chosen widgets.
 *
 * @group Chosen
 */
class ChosenFieldWidgetsTest extends FieldTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'options',
    'entity_test',
    'taxonomy',
    'field_ui',
    'options_test',
    'chosen_field',
    'chosen_field_test',
  ];

  /**
   * A field with cardinality 1 to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $card1;

  /**
   * A field with cardinality 2 to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $card2;

  /**
   * Function used to setup before running the test.
   */
  protected function setUp(): void {
    parent::setUp();

    // Field storage with cardinality 1.
    $this->card1 = \Drupal::entityTypeManager()->getStorage('field_storage_config')->create([
      'field_name' => 'card1',
      'entity_type' => 'entity_test',
      'type' => 'list_integer',
      'cardinality' => 1,
      'settings' => [
        'allowed_values' => [
          // Make sure that 0 works as an option.
          0 => 'Zero',
          1 => 'One',
          // Make sure that option text is properly sanitized.
          2 => 'Some <script>dangerous</script> & unescaped <strong>markup</strong>',
          // Make sure that HTML entities in option text are not double-encoded.
          3 => 'Some HTML encoded markup with &lt; &amp; &gt;',
        ],
      ],
    ]);
    $this->card1->save();

    // Field storage with cardinality 2.
    $this->card2 = \Drupal::entityTypeManager()->getStorage('field_storage_config')->create([
      'field_name' => 'card2',
      'entity_type' => 'entity_test',
      'type' => 'list_integer',
      'cardinality' => 2,
      'settings' => [
        'allowed_values' => [
          // Make sure that 0 works as an option.
          0 => 'Zero',
          1 => 'One',
          // Make sure that option text is properly sanitized.
          2 => 'Some <script>dangerous</script> & unescaped <strong>markup</strong>',
        ],
      ],
    ]);
    $this->card2->save();

    // Create a web user.
    $this->drupalLogin($this->drupalCreateUser(['view test entity', 'administer entity_test content']));
  }

  /**
   * Tests the 'chosen_select' widget (single select).
   */
  public function testSelectListSingle() {
    // Create an instance of the 'single value' field.
    $instance = \Drupal::entityTypeManager()->getStorage('field_config')->create([
      'field_storage' => $this->card1,
      'bundle' => 'entity_test',
    ]);
    $instance->setRequired(TRUE);
    $instance->save();

    \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('entity_test.entity_test.default')
      ->setComponent($this->card1->getName(), [
        'type' => 'chosen_select',
      ])
      ->save();

    // Create an entity.
    $entity = \Drupal::entityTypeManager()->getStorage('entity_test')->create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();
    $entity_init = clone $entity;

    // Display form.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    // A required field without any value has a "none" option.
    $this->assertSession()->elementExists('xpath', '//select[@id="edit-card1"]//option[@value="_none" and text()="- Select a value -"]');

    // With no field data, nothing is selected.
    $options = ['_none', 0, 1, 2];
    $id = 'edit-card1';
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    $this->assertSession()->responseContains('Some dangerous &amp; unescaped markup');

    // Submit form: select invalid 'none' option.
    $edit = ['card1' => '_none'];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->responseContains((string) new FormattableMarkup('@title field is required.', ['@title' => $instance->getName()]));

    // Submit form: select first option.
    $edit = ['card1' => 0];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card1', [0]);

    // Display form: check that the right options are selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    // A required field with a value has no 'none' option.
    $this->assertSession()->elementNotExists('xpath', '//select[@id="edit-card1"]//option[@value="_none"]');

    $id = 'edit-card1';
    $option = 0;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    $options = [1, 2];
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    // Make the field non required.
    $instance->setRequired(FALSE);
    $instance->save();

    // Display form.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    // A non-required field has a 'none' option.
    $this->assertSession()->elementExists('xpath', '//select[@id="edit-card1"]//option[@value="_none" and text()="- None -"]');
    // Submit form: Unselect the option.
    $edit = ['card1' => '_none'];
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card1', []);

    // Test optgroups.
    $this->card1->setSetting('allowed_values', []);
    $this->card1->setSetting('allowed_values_function', 'chosen_field_test_options_allowed_values_callback');
    $this->card1->save();

    // Display form: with no field data, nothing is selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $options = [0, 1, 2];
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    $this->assertSession()->responseContains('Some dangerous &amp; unescaped markup');
    $this->assertSession()->responseContains('Group 1');

    // Submit form: select first option.
    $edit = ['card1' => 0];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card1', [0]);

    // Display form: check that the right options are selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $option = 0;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    $options = [1, 2];
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    // Submit form: Unselect the option.
    $edit = ['card1' => '_none'];
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card1', []);
  }

  /**
   * Tests the 'options_select' widget (multiple select).
   */
  public function testSelectListMultiple() {
    // Create an instance of the 'multiple values' field.
    $instance = \Drupal::entityTypeManager()->getStorage('field_config')->create([
      'field_storage' => $this->card2,
      'bundle' => 'entity_test',
    ]);
    $instance->save();

    \Drupal::entityTypeManager()->getStorage('entity_form_display')->load('entity_test.entity_test.default')
      ->setComponent($this->card2->getName(), [
        'type' => 'chosen_select',
      ])
      ->save();

    // Create an entity.
    $entity = \Drupal::entityTypeManager()->getStorage('entity_test')->create([
      'user_id' => 1,
      'name' => $this->randomMachineName(),
    ]);
    $entity->save();
    $entity_init = clone $entity;

    // Display form: with no field data, nothing is selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $options = [0, 1, 2];
    $id = 'edit-card2';
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    $this->assertSession()->responseContains('Some dangerous &amp; unescaped markup');

    // Submit form: select first and third options.
    $edit = ['card2[]' => [0 => 0, 2 => 2]];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card2', [0, 2]);

    // Display form: check that the right options are selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $id = 'edit-card2';
    $option = 0;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    $option = 1;
    $id = 'edit-card2';
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is not selected.";
    $this->assertEmpty($option_field->hasAttribute('selected'), $message);

    $id = 'edit-card2';
    $option = 2;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    // Submit form: select only first option.
    $edit = ['card2[]' => [0 => 0]];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card2', [0]);

    // Display form: check that the right options are selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $id = 'edit-card2';
    $option = 0;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    $options = [1, 2];
    $id = 'edit-card2';
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    // Submit form: select the three options while the field accepts only 2.
    $edit = ['card2[]' => [0 => 0, 1 => 1, 2 => 2]];
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('this field cannot hold more than 2 values');

    // Submit form: uncheck all options.
    $edit = ['card2[]' => []];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card2', []);

    // A required select list does not have an empty key.
    $instance->setRequired(TRUE);
    $instance->save();
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');
    $this->assertSession()->elementNotExists('xpath', '//select[@id="edit-card2"]//option[@value=""]');

    // We do not have to test that a required select list with one option is
    // auto-selected because the browser does it for us.
    // Test optgroups.
    // Use a callback function defining optgroups.
    $this->card2->setSetting('allowed_values', []);
    $this->card2->setSetting('allowed_values_function', 'chosen_field_test_options_allowed_values_callback');
    $this->card2->save();

    $instance->setRequired(FALSE);
    $instance->save();

    // Display form: with no field data, nothing is selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $options = [0, 1, 2];
    $id = 'edit-card2';
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }

    $this->assertSession()->responseContains('Some dangerous &amp; unescaped markup');
    $this->assertSession()->responseContains('Group 1');

    // Submit form: select first option.
    $edit = ['card2[]' => [0 => 0]];
    $this->submitForm($edit, 'Save');
    $this->assertFieldValues($entity_init, 'card2', [0]);

    // Display form: check that the right options are selected.
    $this->drupalGet('entity_test/manage/' . $entity->id() . '/edit');

    $id = 'edit-card2';
    $option = 0;
    $option_field = $this->assertSession()->optionExists($id, $option);
    $message = "Option $option for field $id is selected.";
    $this->assertNotEmpty($option_field->hasAttribute('selected'), $message);

    $options = [1, 2];
    $id = 'edit-card2';
    foreach ($options as $option) {
      $option_field = $this->assertSession()->optionExists($id, $option);
      $message = "Option $option for field $id is not selected.";
      $this->assertEmpty($option_field->hasAttribute('selected'), $message);
    }
  }

}
