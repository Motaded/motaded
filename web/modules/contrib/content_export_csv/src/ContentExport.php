<?php

namespace Drupal\content_export_csv;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\link\LinkItemInterface;

/**
 * Content export service class.
 */
class ContentExport {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * Get available content types.
   *
   * @return array
   *   Returns an array of content types.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getContentTypes(): array {
    $contentTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    return $contentTypesList;
  }

  /**
   * Get nids based on node type.
   *
   * @param string $nodeType
   *   The node type.
   * @param int $status
   *   The status flag. 0 => unpublished, 1 => published.
   *
   * @return array
   *   Returns an array of nids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getNodeIds(string $nodeType, int $status = 1) {
    $entityQuery = $this->entityTypeManager->getStorage('node')->getQuery();
    $entityQuery->accessCheck(TRUE);
    $entityQuery->condition('status', $status);
    $entityQuery->condition('type', $nodeType);
    return $entityQuery->execute();
  }

  /**
   * Collects Node Data.
   *
   * @param array $entityIds
   *   The node ids.
   * @param array $fields
   *   The array of fields to use in export. If empty, all will be exported.
   * @param int $include_node_urls
   *   Flag for including Node Urls at the en d of the csv row.
   * @param int $strip_tags
   *   Flag for stripping tags.
   *
   * @return array
   *   Returns the array of node data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getNodeDataList(array $entityIds, array $fields = [], int $include_node_urls = 0, int $strip_tags = 1): array {
    $nodeData = $this->entityTypeManager->getStorage('node')->loadMultiple($entityIds);
    $nodeCsvData = [];
    foreach ($nodeData as $nodeDataEach) {
      $nodeCsvData[] = implode(',', $this->getNodeData($nodeDataEach, $fields, $include_node_urls, $strip_tags));
    }
    return $nodeCsvData;
  }

  /**
   * Gets the valid field list.
   *
   * @param string $nodeType
   *   The node type.
   *
   * @return array
   *   Returns array of fields.
   */
  public function getValidFieldList(string $nodeType): array {
    $nodeArticleFields = $this->entityFieldManager->getFieldDefinitions('node', $nodeType);
    $nodeFields = array_keys($nodeArticleFields);
    $unwantedFields = [
      'comment',
      'content_translation_source',
      'content_translation_outdated',
      'sticky',
      'revision_default',
      'revision_translation_affected',
      'revision_timestamp',
      'revision_uid',
      'revision_log',
      'vid',
      'uuid',
      'promote',
    ];

    foreach ($unwantedFields as $unwantedField) {
      $unwantedFieldKey = array_search($unwantedField, $nodeFields);
      if ($unwantedFieldKey !== FALSE) {
        unset($nodeFields[$unwantedFieldKey]);
      }
    }

    return $nodeFields;
  }

  /**
   * Gets Manipulated Node Data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node object.
   * @param array $fields
   *   The array of fields to use in export. If empty, all will be exported.
   * @param int $include_node_urls
   *   Flag for including Node Urls at the en d of the csv row.
   * @param int $strip_tags
   *   Flag for stripping tags.
   *
   * @return array
   *   Returns an array of node data.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getNodeData(EntityInterface $node, array $fields = [], int $include_node_urls = 0, int $strip_tags = 1): array {
    $nodeData = [];

    if (empty($fields)) {
      $nodeFields = $this->getValidFieldList($node->bundle());
    }
    else {
      $nodeFields = $fields;
    }

    foreach ($nodeFields as $nodeField) {
      if (isset($node->{$nodeField}->value)) {
        if ($strip_tags) {
          $nodeData[] = '"' . htmlspecialchars(strip_tags($this->getData($node, $nodeField, 'value')) ?? '') . '"';
        } 
        else {
          $nodeData[] = '"' . ($this->getData($node, $nodeField, 'value') ?? '') . '"';
        }
      }
      else {
        if ($node->{$nodeField}[0] instanceof LinkItemInterface) {
          if ($strip_tags) {
            $nodeData[] = '"' . htmlspecialchars(strip_tags($this->getData($node, $nodeField, 'uri') ?? '')) . '"';
          }
          else {
            $nodeData[] = '"' . ($this->getData($node, $nodeField, 'uri') ?? '') . '"';
          }
        }
        elseif (isset($node->{$nodeField}->target_id)) {
          if ($strip_tags) {
            $nodeData[] = '"' . htmlspecialchars(strip_tags($this->getData($node, $nodeField, 'target_id') ?? '')) . '"';
          }
          else {
            $nodeData[] = '"' . ($this->getData($node, $nodeField, 'target_id') ?? '') . '"';
          }
        }
        else {
          if ($strip_tags) {
            $nodeData[] = '"' . htmlspecialchars(strip_tags($this->getData($node, $nodeField, 'langcode') ?? '')) . '"';
          }
          else {
            $nodeData[] = '"' . ($this->getData($node, $nodeField, 'langcode') ?? '') . '"';
          }
        }
      }
    }

    if ($include_node_urls) {
      $nodeData[] = '"' . ($node->toUrl()->setAbsolute()->toString()) . '"';
    }

    return $nodeData;
  }

  /**
   * Get Node Data in CSV Format.
   *
   * @param string $nodeType
   *   The node type.
   * @param int $status
   *   The status flag. 0 => unpublished, 1 => published.
   * @param array $fields
   *   The array of fields to use in export. If empty, all will be exported.
   * @param int $include_node_urls
   *   Flag for including Node Urls at the en d of the csv row.
   * @param int $strip_tags
   *   Flag for stripping tags.
   *
   * @return array
   *   Returns an array of csv data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getNodeCsvData(string $nodeType, int $status = 1, array $fields = [], int $include_node_urls = 0, int $strip_tags = 1): array {
    $entityIds = $this->getNodeIds($nodeType, $status);
    return $this->getNodeDataList($entityIds, $fields, $include_node_urls, $strip_tags);
  }

  /**
   * Retrieve and concatenate specific values from an entity field.
   *
   * This function takes an entity object that implements the EntityInterface,
   * a field name on that entity, and an option or key to extract from each
   * value in the field. It then concatenates these extracted values using '|'
   * as the delimiter and returns the result as a string.
   *
   * @param EntityInterface $node
   *   The entity object from which data is to be extracted.
   * @param string $nodeField
   *   The name of the field on the entity from which values will be extracted.
   * @param string $option
   *   The key or option to extract from each value within the specified field.
   *
   * @return string
   *   A concatenated string of values extracted from the field, separated by '|'.
   */
  public function getData(EntityInterface $node, string $nodeField, string $option) {
    $result = '';
    $values = $node->get($nodeField)->getValue();
    foreach ($values as $value) {
      // Some field item arrays do not contain the requested key (e.g. langcode).
      if (!array_key_exists($option, $value) || $value[$option] === NULL) {
        continue;
      }
      $result .= $value[$option] . '|';
    }
    if ($result === '') {
      return '';
    }
    return substr_replace($result, "", -1);
  }

}
