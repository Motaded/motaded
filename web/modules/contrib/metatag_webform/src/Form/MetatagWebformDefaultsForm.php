<?php

namespace Drupal\metatag_webform\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\metatag\Entity\MetatagDefaults;
use Drupal\metatag\Form\MetatagDefaultsForm;
use Drupal\metatag\MetatagDefaultsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides default metatag form overrides.
 */
class MetatagWebformDefaultsForm extends MetatagDefaultsForm {

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container): self {
    $instance = parent::create($container);

    $instance->routeMatch = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->entity = $instance->getMetatagDefaults();

    return $instance;
  }

  /**
   * Get the `metatag_defaults` entity to build the form.
   *
   * @return \Drupal\metatag\MetatagDefaultsInterface
   *   The `metatag_defaults` object.
   */
  public function getMetatagDefaults(): MetatagDefaultsInterface {
    $webform_id = $this->routeMatch->getRawParameter('webform');

    if (is_null($webform_id)) {
      return MetatagDefaults::create();
    }

    /** @var \Drupal\metatag\MetatagDefaultsInterface $metatag_defaults */
    $metatag_defaults = $this->entityTypeManager->getStorage('metatag_defaults')->load(implode('.', [
      'webform', $webform_id,
    ]));

    if (is_null($metatag_defaults)) {
      return MetatagDefaults::create();
    }

    return $metatag_defaults;
  }

  /**
   * {@inheritDoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    // We don't need to choose metatag type.
    if (array_key_exists('id', $form)) {
      $form['id']['#access'] = FALSE;
    }

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Proceed default flow for non-webform metatags.
    if (!$this->routeMatch->getRouteName() == 'metatag_webform.webform_metatag_settings') {
      return parent::save($form, $form_state);
    }

    /** @var \Drupal\metatag\MetatagDefaultsInterface $metatag_defaults */
    $metatag_defaults = $this->entity;
    $metatag_defaults->setStatus($form_state->getValue('status'));

    // Retrieve the webform entity.
    /** @var \Drupal\webform\WebformInterface $webform */
    $webform = $this->routeMatch->getParameter('webform');

    // Set entity label.
    if ($metatag_defaults->isNew()) {
      $metatag_defaults->set('label', implode(': ', [
        'Webform', $webform->label(),
      ]));
    }

    // Set entity tags.
    $tags = $this->metatagPluginManager->getDefinitions();
    $tag_values = [];

    $user_input = $form_state->getUserInput();

    foreach ($tags as $tag_id => $tag_definition) {
      if (array_key_exists($tag_id, $user_input)) {
        $tag = $this->metatagPluginManager->createInstance($tag_id);
        $tag->setValue($user_input[$tag_id]);

        if (!empty($tag->value())) {
          $tag_values[$tag_id] = $tag->value();
        }
      }
    }

    ksort($tag_values);
    $metatag_defaults->set('tags', $tag_values);

    // Save the entity.
    $metatag_defaults->set('id', implode('.', [
      'webform', $webform->id(),
    ]));

    $status = $metatag_defaults->save();

    $this->messenger()->addStatus($this->t('Webform metatags saved.'));

    return $status;
  }

}
