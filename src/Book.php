<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\NodeYamlBook.
 */

namespace Drupal\nodeyaml;

use Drupal\Node\Entity\Node;

/**
 * Basic functions to deal with books.
 */
class Book {
  /**
   * Create a new book.
   *
   * Use the given node as root item to create a new book.
   *
   * @param \Drupal\Node\Entity\Node $node
   *   The main(first) node on the new book.
   */
  public static function createBook(Node $node) {
    $node->book['bid'] = 'new';
    $node->book['plid'] = 0;
    $node->save();
  }

  /**
   * Add a node as a book page.
   *
   * @param Node $node
   *   The node to add.
   * @param Node $parent
   *   The parent node.
   * @param Node $previous
   *   The previous node. If this is set, the new page will be put next to it
   *   in book structure.
   */
  public static function addBookPage(Node $node, Node $parent, Node $previous = NULL) {
    if ($previous == NULL) {
      $weight = -50;
    }
    else {
      $weight = $previous->book['weight'] + 1;
    }
    $node->book['bid'] = $parent->book['bid'];
    $node->book['pid'] = $parent->book['nid'];

    $node->save();
  }

}
