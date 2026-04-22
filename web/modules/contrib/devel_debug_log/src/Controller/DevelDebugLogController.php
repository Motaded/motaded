<?php

namespace Drupal\devel_debug_log\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * The main controller for the module.
 */
class DevelDebugLogController extends ControllerBase {

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Serializer service.
   *
   * @var Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The DateFormatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The FormBuilder object.
   *
   * @var Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('date.formatter'),
      $container->get('serializer'),
      $container->get('form_builder')
    );
  }

  /**
   * DevelDebugLogController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database object.
   * @param \Drupal\Core\Datetime\DateFormatter $dateFormatter
   *   The dateFormatter object.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   The serializer object.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The formBuilder object.
   */
  public function __construct(Connection $database, DateFormatter $dateFormatter, Serializer $serializer, FormBuilderInterface $formBuilder) {
    $this->database = $database;
    $this->dateFormatter = $dateFormatter;
    $this->serializer = $serializer;
    $this->formBuilder = $formBuilder;
  }

  /**
   * Lists debug information.
   *
   * @return array
   *   Renderable array that contains a list of debug data.
   */
  public function listLogs() {
    $query = $this->database->select('devel_debug_log', 'm')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender');
    $results = $query->fields('m', ['timestamp', 'title', 'message'])
      ->orderBy('id', 'desc')
      ->execute();

    $rows = [];
    foreach ($results as $result) {
      $rows[] = [
        'title' => $result->title,
        'time' => $this->dateFormatter
          ->format($result->timestamp, 'short'),
        'message' => $result->message,
      ];
    }

    if (empty($rows)) {
      return [
        '#markup' => $this->t('No debug messages.'),
      ];
    }

    $build = [
      'messages' => [
        '#theme' => 'devel_debug_log_list',
        '#content' => $rows,
        '#delete_form' => $this->formBuilder->getForm('Drupal\devel_debug_log\Form\DevelDebugLogDeleteForm'),
      ],
      'pager' => [
        '#type' => 'pager',
      ],
    ];

    return $build;
  }

}
