<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\Import.
 */

namespace Drupal\nodeyaml;

use Drupal\nodeyaml\Format\Yaml;

/**
 * Import the nodes from yaml files.
 */
class Import extends BaseOperation {
  /**
   * When update the node if it already exists.
   *
   * @var bool
   */
  public $update = TRUE;

  /**
   * Process the import.
   */
  public function process() {
    if (isset($this->options['input-file'])) {
      $this->path = $this->extractZip($this->options['input-file']);
    }

    if ($this->options['types'] === NULL) {
      $this->options['types'][] = 'all';
    }
    if (in_array('node', $this->options['types']) || in_array('all', $this->options['types'])) {
      $this->importNodes();
    }
    if (in_array('book', $this->options['types']) || in_array('all', $this->options['types'])) {
      $this->importBooks();
    }
  }

  /**
   * Add books.
   */
  public function importBooks() {
    $book = new ImportBook();
    /** @var \Drupal\Core\Config\Config $config */

    $config = \Drupal::config('nodeyaml.settings');

    if (!$this->path) {
      $path = $config->get('import.path.book');
    }
    else {
      $path = $this->path . "/books";
    }

    /** @var array $files */
    $files = $this->listFiles($path);
    foreach ($files as $file) {
      $content = file_get_contents($file);
      $array = Yaml::contentToArray($content);
      $book->process($array);
    }

  }

  /**
   * Add the nodes for the file or folder in $path.
   *
   * If the node have an uuid, and the node exists, with update = true the
   * node is updated instead of inserting a new one.
   */
  public function importNodes() {
    $import = new ImportNode();
    $import->update = $this->update;

    /** @var \Drupal\Core\Config\Config $config */
    $config = \Drupal::config('nodeyaml.settings');

    if ($this->path === NULL) {
      $path = $config->get('import.path.node');
    }
    else {
      $path = $this->path . "/nodes";
    }

    debug($path);
    /** @var array $files */
    $files = $this->listFiles($path);
    debug($files);
    foreach ($files as $file) {
      $content = file_get_contents($file);
      $array = Yaml::contentToArray($content);
      $import->process($array);
    }
  }
}
