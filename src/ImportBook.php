<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\NodeYamlBook.
 */

namespace Drupal\nodeyaml;

use Drupal\Node\Entity\Node;

/**
 * Import The content of a book.
 */
class ImportBook extends Book {
  /**
   * Create the book from definition.
   *
   * @var array $array
   *   The content of yaml file with information about the book as an array.
   */
  public function process(array $array) {
    self::addNodes($array['book'], NULL);
  }

  /**
   * Add the nodes to book.
   *
   * @param array $children
   *   An array of nodes.
   * @param Node $parent
   *   The parent node.
   */
  public static function addNodes(array $children, Node $parent = NULL) {
    /** @var  \Drupal\Node\Entity\Node $previous */
    $previous = NULL;
    foreach ($children as $child) {
      $node = self::getNodeFromUuid($child['uuid']);
      // No parent, so this is the main page.
      if ($parent == NULL) {
        if (!isset($node->book)) {
          self::createBook($node);
        }
        $parent = $node;
        $previous = $node;
      }
      else {
        self::addBookPage($node, $parent, $previous);
        $previous = $node;
      }
      if (isset($child['children'])) {
        self::addNodes($child['children'], $node);
      }
    }
  }

  /**
   * Get a node from its uuid.
   *
   * @param string $uuid
   *   The uuid of the node.
   *
   * @return \Drupal\node\Entity\Node|NULL
   *   Return the node from the uuid or NULL if one is not found.
   */
  public static function getNodeFromUuid($uuid) {
    $node = NULL;
    $query = \Drupal::entityQuery('node')
      ->condition('uuid', $uuid)
      ->execute();
    // There is a node with this uuid.
    if (count($query)) {
      $keys = array_keys($query);
      $node = Node::load($keys[0]);
    }
    return $node;
  }

  /**
   * Get a node from its path alias.
   *
   * @param string $path
   *   The path alias of the node.
   *
   * @return \Drupal\node\Entity\Node|NULL
   *   Return the node from the path or NULL if one is not found.
   */
  public static function getNodeFromPath($path) {
    $result = db_query("SELECT source, pid FROM {url_alias} WHERE alias = :alias ORDER BY pid DESC LIMIT 1 OFFSET 0",
      array(':alias' => $path));
    if ($result) {
      $result = $result->fetchAssoc();
      $pieces = explode('/', $result['source']);
      $nid = $pieces[1];
      /** @var \Drupal\node\Entity\Node $node */
      $node = Node::load($nid);
    }
    else {
      $node = NULL;
    }
    return $node;
  }
}
