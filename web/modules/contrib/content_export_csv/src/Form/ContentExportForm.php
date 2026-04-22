<?php

namespace Drupal\content_export_csv\Form;

use Drupal\content_export_csv\ContentExport;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Content export form.
 */
class ContentExportForm extends FormBase {

  /**
   * The content export service.
   *
   * @var \Drupal\content_export_csv\ContentExport
   */
  protected $contentExport;

  /**
   * The constructor.
   *
   * @param \Drupal\content_export_csv\ContentExport $content_export
   *   The content export service.
   */
  public function __construct(ContentExport $content_export) {
    $this->contentExport = $content_export;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_export_csv.export')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_export_csv_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['content_export'] = [
      '#title' => $this->t('Export options'),
      '#type' => 'fieldset',
    ];

    $form['content_export']['content_type'] = [
      '#title' => $this->t('Content type'),
      '#description' => $this->t('Filter nodes by specific content type.'),
      '#type' => 'select',
      '#empty_option' => $this->t('- Select -'),
      '#options' => $this->contentExport->getContentTypes(),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::ajaxUpdateContentFields',
        'wrapper' => 'content-fields-wrapper',
      ],
    ];

    $form['content_export']['fields_wrapper'] = [
      '#type' => 'container',
      '#id' => 'content-fields-wrapper',
    ];

    $nodeType = $form_state->getValue('content_type');
    if (!empty($nodeType)) {
      $validFields = $this->contentExport->getValidFieldList($nodeType);
      $fieldOptions = [];
      foreach ($validFields as $field) {
        $fieldOptions[$field] = $field;
      }
      $form['content_export']['fields_wrapper']['fields'] = [
        '#title' => $this->t('Content fields'),
        '#description' => $this->t('Select which field(s) you want to export.'),
        '#type' => 'checkboxes',
        '#multiple' => TRUE,
        '#size' => 20,
        '#options' => $fieldOptions,
      ];
    }

    $form['content_export']['status'] = [
      '#title' => $this->t('Content status'),
      '#description' => $this->t('Filter nodes that are published or unpublished.'),
      '#type' => 'select',
      '#empty_option' => $this->t('- Select -'),
      '#options' => [
        0 => $this->t('Unpublished'),
        1 => $this->t('Published'),
      ],
      '#default_value' => 1,
    ];

    $form['content_export']['include_node_urls'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include Node Urls at the end of the exported CSV row.'),
      '#default_value' => 0,
    ];

    $form['content_export']['strip_tags'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Strip HTML tags for fields.'),
      '#default_value' => 1,
    ];

    $form['content_export']['export'] = [
      '#value' => $this->t('Export content'),
      '#type' => 'submit',
    ];

    return $form;
  }

  /**
   * Ajax callback that updates available content fields.
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function ajaxUpdateContentFields(array &$form, FormStateInterface $form_state, Request $request) {
    return $form['content_export']['fields_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nodeType = $form_state->getValue('content_type');
    $status = $form_state->getValue('status');
    $fields = $form_state->getValue('fields');
    $include_node_urls = $form_state->getValue('include_node_urls');
    $strip_tags = $form_state->getValue('strip_tags');

    $private_path = PrivateStream::basepath();
    $public_path = PublicStream::basepath();
    $file_base = ($private_path) ? $private_path : $public_path;
    $filename = 'content_export' . time() . '.csv';
    $filepath = $file_base . '/' . $filename;
    $csvFile = fopen($filepath, "w");

    if (fopen($filepath, "w") === false) {
      $this->messenger()->addError($this->t("Failed to open stream: No such file or directory @filepath found.", ['@filepath' => $file_base]));
      return FALSE;
    }

    if (!empty($fields)) {
      $fieldNames = implode(',', $fields);
    }
    else {
      $fieldNames = implode(',', $this->contentExport->getValidFieldList($nodeType));
    }

    // Include Node Urls at the end of the exported CSV row.
    if ($include_node_urls) {
      $fieldNames .= ',url';
    }

    // Clear empty fields to prevent errors.
    $fields = array_filter($fields, function ($e) {
      return (gettype($e) === 'string');
    });

    if ($csvFile) {
      $csvData = $this->contentExport->getNodeCsvData($nodeType, $status, $fields, $include_node_urls, $strip_tags);
      fwrite($csvFile, $fieldNames . "\n");
      foreach ($csvData as $csvDataRow) {
        fwrite($csvFile, $csvDataRow . "\n");
      }
      fclose($csvFile);

      header('Content-Type: text/csv');
      header('Content-Disposition: attachment; filename="' . basename($filepath) . '";');
      header('Content-Length: ' . filesize($filepath));
      readfile($filepath);
      unlink($filepath);
      exit;
    }
  }

}
