<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\ExportBook.
 */

namespace Drupal\nodeyaml;

/**
 * Export an entire book.
 */
class ExportBook {
  /**
   * An associative array with options from drush or form.
   *
   * @var array
   */
  protected $options = array();

  /**
   * The content of the exported book in yaml format.
   *
   * @var string
   */
  protected $yaml;

  /**
   * Export the book.
   *
   * Generates an yaml representation of the book that can by imported latter.
   *
   * @param int $book
   *   The node id(nid) from the first page on book.
   */
  public function process($book) {
    $yaml = "";
    $book_node = entity_load('node', $book);

    $tree = \Drupal::service('book.manager')->bookSubtreeData($book_node->book);
    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($tree), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $element) {
      // We need only 'link' key from array, ignore the rest.
      $key = $iterator->key();
      if ($key != 'link') {
        continue;
      }
      $depth = $iterator->getDepth();

      $indent = str_repeat(' ', ($depth * 2) - 0);

      // Start the book.
      if ($depth > 1) {
        $yaml .= "\n$indent" . '-';
      }
      else {
        $yaml = "book\n  -";
      }

      $node = entity_load('node', $element['nid']);
      $yaml .= "\n$indent" . '  uuid: ' . $node->uuid->value;
      $yaml .= "\n$indent" . '  title: ' . $node->title->value;
      $yaml .= "\n$indent" . '  url: ' . \Drupal::service('path.alias_manager')
          ->getAliasByPath('node/' . $node->nid->value);
      $yaml .= "\n$indent" . '  weight: ' . $element['weight'];
      $yaml .= "\n$indent" . '  children:';
    }
    $this->yaml = $yaml;
  }

  /**
   * Define the options used.
   *
   * @param array $options
   *   An array with the options. See the property $options of this class with
   *   an explanations of each element in array.
   */
  public function setOptions(array $options) {
    $this->options = $options;
  }
}
