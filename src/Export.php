<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\NodeYamlExport.
 */

namespace Drupal\nodeyaml;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Export the content of all nodes.
 */
class Export extends BaseOperation {
  /**
   * When overwrite if the file already exists.
   *
   * @var string
   */
  public $overwrite = FALSE;

  /**
   * If we create a zip file in process, this holds the file name.
   *
   * @var string
   */
  protected $zipName = '';

  /**
   * An associative array with options from drush or form.
   *
   * @var array
   *   An associative array with:
   *   - debug: show debug output.
   *   - drush: we are using drush, not web.
   *   - output: zip to output as zip file.
   *   - output-file: the file name to output(exemple: export.zip).
   *   - types: an array of types of entities to export, current node,book,all.
   */
  protected $options = array();

  /**
   * Call the export function for each type in options or for all if not set.
   */
  public function process() {
    // If we will create a zip file with the content to export, create a
    // temporary folder and save all the export to it.
    if (isset($this->options['output']) && $this->options['output'] == 'zip') {
      $this->path = $this->createTemporaryDirectory('temporary://', 'nodeyaml_');
    }

    // If the entity type do export is not set, defaults to all.
    if ($this->options['types'] === NULL) {
      $this->options['types'][] = 'all';
    }
    // Process Nodes.
    if (in_array('node', $this->options['types']) || in_array('all', $this->options['types'])) {
      $this->exportNodes();
    }
    // Process Books.
    if (in_array('book', $this->options['types']) || in_array('all', $this->options['types'])) {
      $this->exportBooks();
    }

    // Create the zip file if necessary and move it to correct place/file.
    if (isset($this->options['output']) && $this->options['output'] == 'zip') {
      $this->zipName = $this->createZip();
      // There is a file name to output, use it
      if (isset($this->options['output-file'])) {
        $output_file = $this->options['output-file'];
        rename($this->zipName, $output_file);
      }
      // There is not a file name, create one in current path.
      elseif ($this->options['drush']) {
        $output_file = getcwd() . '/' . basename($this->zipName) . '.zip';
        rename($this->zipName, $output_file);
      }
    }
  }

  /**
   * Export all books.
   */
  public function exportBooks() {
    $books = \Drupal::service('book.manager')->getAllBooks();
    if ($books) {
      foreach ($books as $book) {
        $export = new ExportBook();
        $export->setOptions($this->options);
        $export->process($book['nid']);
      }
    }
  }

  /**
   * Export all nodes.
   */
  public function exportNodes() {
    /** @var \Drupal\Core\Config\Config $config */
    $config = \Drupal::config('nodeyaml.settings');

    if (!$this->path) {
      $path = $config->get('export.path.node');
    }
    else {
      $path = $this->path . '/nodes';
    }

    // Create the directory if it do not exist.
    if (!is_dir($path)) {
      drupal_mkdir($path, NULL, TRUE);
    }

    /** @var \Drupal\Core\Entity\Query\QueryInterface $query */
    $query = \Drupal::entityQuery('node');
    $items = 10;
    $start = 0;
    $continue = TRUE;
    while ($continue) {
      $query->range($start, $items);
      $result = $query->execute();
      if ($result) {
        $nodes = entity_load_multiple('node', array_keys($result));
        foreach ($nodes as $node) {
          $export = new ExportNode($node, $path . '/' . $node->uuid->value . '.yml', $this->overwrite);
          $export->process();
          $export->save();
        }
      }
      else {
        $continue = FALSE;
      }
      $start = $start + $items;
    }
  }

  /**
   * Return the name of zip file created in process.
   *
   * @return string
   *   The name of zip file created in process.
   */
  public function getZipName() {
    return $this->zipName;
  }
}
