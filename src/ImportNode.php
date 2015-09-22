<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\NodeYamlImportNode.
 */

namespace Drupal\nodeyaml;

use Drupal\Node\Entity\Node;

/**
 * Import the content of a single node.
 */
class ImportNode {
  /**
   * When update the node if it already exists.
   *
   * @var bool
   */
  public $update = TRUE;

  /**
   * When add the uuid to node that do not have it.
   *
   * @var bool
   */
  public $addUuid = FALSE;

  /**
   * The node content from file as an array.
   *
   * @var array
   */
  protected $array = array();

  /**
   * The node to update.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node = NULL;

  /**
   * The error messages.
   *
   * Store all error messages while importing the node, like the fields that we
   * do not support yet.
   *
   * @var array
   */
  public $error = array();

  /**
   * Add the nodes for the file or folder in $path.
   *
   * If the node have an uuid, and the node exists, with update = true the
   * node is updated instead of inserting a new one.
   *
   * @param array $array
   *   The content of yaml file with the node to import.
   */
  public function process(array $array) {
    //debug($array);
    /** @var array $yaml */
    $this->array = $array;
    // Update the node if it exists.
    if ($this->update) {
      // Update only if the node have an uuid.
      if (isset($this->array['uuid'])) {
        $query = \Drupal::entityQuery('node')
          ->condition('uuid', $this->array['uuid'])
          ->execute();
        // There is a node with this uuid.
        if (count($query)) {
          $keys = array_keys($query);
          $this->node = Node::load($keys[0]);
          $this->nodeUpdate();
        }
        // There is not a node with this uuid, add it.
        else {
          $this->nodeAdd();
        }
      }
      // There is no uuid, just add the node.
      else {
        $this->nodeAdd();
      }
    }
    // Do not update, always create a new node.
    else {
      $this->nodeAdd();
    }
  }


  /**
   * Add a new node.
   */
  protected function nodeAdd() {
    //debug('nodeadd');
    $this->prepareNodeUpdate();

    /** @var \Drupal\node\Entity\Node $node */
    $node = entity_create('node', $this->array);
    $node->setNewRevision();
    $node->save();
  }

  /**
   * Update an existing node.
   */
  protected function nodeUpdate() {
    $this->prepareNodeUpdate();

    // Update the values.
    foreach ($this->array as $key => $value) {
      $this->node->set($key, $value);
    }
    $this->node->save();
  }

  /**
   * Prepare the node to update.
   */
  protected function prepareNodeUpdate() {
    // Do not set the value of nid.
    unset($this->array['nid']);

    // Fields types that need some process before updating the node.
    $prepare = array(
      'taxonomy_term_reference',
    );

    // Field type that do not need any process before updating the node, so
    // if a field type is not in $prepare and is not in this list, it is
    // a field that we do not know how to handle.
    $default = array(
      'text_with_summary',
      'link',
    );
    // $fields = $this->node->getFieldDefinitions();
    $manager = \Drupal::entityManager();
    $fields = $manager->getFieldDefinitions('node', $this->array['type']);
    foreach ($fields as $field) {
      if (get_class($field) == 'Drupal\Core\Field\BaseFieldDefinition') {
        // We may do a prepare step in one of base fields, if not we
        // can remove this latter.
      }
      else {
        if (in_array($field->get('field_type'), $prepare)) {
          $function = 'prepare_' . $field->get('field_type');
          $this->$function($field->get('field_name'));
        }
        elseif (!in_array($field->get('field_type'), $default)) {
          // Create an error message for a field that we do not know.
          $err = "Unknow Field:\nName: " . $field->get('field_name');
          $err .= "\nType: " . $field->get('field_type');
          $err .= "\nClass: " . get_class($field);
          $this->error[] = $err;
        }
      }
    }
  }

  /**
   * Prepare a taxonomy field to update.
   *
   * @param string $field
   *   The name of the field.
   */
  protected function prepare_taxonomy_term_reference($field) {
    /** @var \Drupal\Core\Entity\EntityManager $manager */
    $manager = \Drupal::entityManager();
    $fields = $manager->getFieldDefinitions('node', $this->array['type']);
    $vocabulary = $fields[$field]->getSettings()['allowed_values'][0]['vocabulary'];

    $values = array();
    if (is_array($this->array[$field])) {
      foreach ($this->array[$field] as $term) {
        $query = \Drupal::entityQuery('taxonomy_term')
          ->condition('name', $term, "=")
          ->execute();
        if (count($query)) {
          $query = array_keys($query);
          foreach ($query as $id) {
            $values[]['target_id'] = $id;
          }
        }
        else {
          $options = array(
            'name' => $term,
            'vid' => $vocabulary,
          );
          $new = entity_create('taxonomy_term', $options);
          $new->save();
          $values[]['target_id'] = $new->tid->value;
        }
      }
    }
    $this->array[$field] = $values;
  }
}
