<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\NodeYamlExportNode.
 */

namespace Drupal\nodeyaml;

use Drupal\Node\Entity\Node;
use Drupal\nodeyaml\Format\Yaml;

/**
 * Export the content of a single node to yaml file.
 */
class ExportNode {
  /**
   * The node to export.
   *
   * @var \Drupal\Node\Entity\Node
   */
  public $node;

  /**
   * The name of file to save the node.
   *
   * @var string
   */
  public $filename;

  /**
   * When overwrite the file if it already exists.
   *
   * @var bool
   */
  public $overwrite;

  /**
   * The current content of node as yaml file.
   *
   * @var string
   */
  public $yaml;

  /**
   * The error messages.
   *
   * Store all error messages while importing the node, like the fields that we
   * do not support yet.
   *
   * @var array
   */
  public $error = array();

  private $array = array();

  /**
   * Constructor.
   *
   * @param \Drupal\Node\Entity\Node $node
   *   The node to export.
   * @param string $filename
   *   The name of file to save the node.
   * @param bool $overwrite
   *   When overwrite the node if the file already exists.
   */
  public function __construct(Node $node, $filename = '', $overwrite = FALSE) {
    $this->node = $node;
    $this->filename = $filename;
    $this->overwrite = $overwrite;
  }

  /**
   * Generate the yaml for the node.
   */
  public function process() {
    $this->array = array();
    // Handle the node complex fields.
    // Know_Types is an array with fields that we know how to handle.
    $know_types = array(
      'link',
      'taxonomy_term_reference',
      'text_with_summary',
    );
    $fields = $this->node->getFieldDefinitions();
    foreach ($fields as $field) {
      if (get_class($field) == 'Drupal\Core\Field\BaseFieldDefinition') {
        // Make phpstorm happy and know the class for autoconplete.
        /** @var \Drupal\Core\Field\BaseFieldDefinition $base */
        $base = $field;

        // Path is one exception in how to handle.
        if ($base->getName() == 'path') {
          $this->process_path();
        }
        // Those fields are updated each time the node changes, not export it.
        elseif ($base->getName() == 'changed' ||
          $base->getName() == 'nid' ||
          $base->getName() == 'vid'
        ) {
        }
        // Handle all other base fields.
        else {
          $value = $this->node->{$base->getName()}->getString();
          $this->array[$base->getName()] = $value;
        }
      }
      else {
        if (in_array($field->get('field_type'), $know_types)) {
          $function = 'process_' . $field->get('field_type');
          $this->$function($field->get('field_name'));
        }
        else {
          $error = "Unknow Field:\nName: " . $field->get('field_name');
          $error .= "\nType: " . $field->get('field_type');
          $error .= "\nClass: " . get_class($field);
          $this->error[] = $error;
        }
      }
    }
  }

  /**
   * Generate yaml for link fields.
   *
   * @param string $field
   *   The name of the field.
   */
  public function process_link($field) {
    $values = $this->node->{$field}->getValue();
    foreach ($values as $value) {
      $this->array[$field][] = $value;
    }
  }

  /**
   * This generates the yaml for path.
   *
   * @todo Get the path from path in node object, not from looking for it from
   *   node/nid.
   */
  public function process_path() {
    $this->array['path'] = array();
    $this->array['path']['alias'] = \Drupal::service('path.alias_manager')
      ->getAliasByPath('/node/' . $this->node->nid->value);
  }


  /**
   * Handle taxonomy field.
   *
   * We use the term name, not id, so the term is preserved on import, but links
   * will be broken unless you use something like pathauto(recomended).
   *
   * @param string $field
   *   The name of field with taxonomy terms.
   */
  public function process_taxonomy_term_reference($field) {
    /** \Drupal\taxonomy\Plugin\Field\FieldType\TaxonomyTermReferenceItem[] */
    $tids = array();
    foreach ($this->node->{$field} as $tag) {
      if (isset($tag->getValue()['target_id'])) {
        $tids[] = $tag->getValue()['target_id'];
      }
    }
    $terms = entity_load_multiple('taxonomy_term', $tids);

    $this->array[$field] = array();
    foreach ($terms as $term) {
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $this->array[$field][] = $term->getName();
    }
  }

  /**
   * This process fields text_with_summary.
   *
   * This process the body and other fields like it.
   *
   * @param string $field
   *   The name of field.
   */
  public function process_text_with_summary($field) {
    $this->array[$field] = array();
    foreach ($this->node->{$field} as $body) {
      $this->array[$field][] = array(
        'format' => $body->format,
        'summary' => $body->summary,
        'value' => $body->value,
      );
    }
  }

  /**
   * Save the current yaml content to file.
   */
  public function save() {
    if (!file_exists($this->filename) || $this->overwrite) {
      file_put_contents($this->filename, Yaml::arrayToContent($this->array));
    }
  }
}
