<?php
namespace Drupal\nodeyaml;

/**
 * This class contains some functions used in both Import and Export.
 */
abstract class BaseOperation {
  /**
   * An associative array with options from drush or form.
   *
   * @var array
   */
  protected $options = array();

  /**
   * Path where yaml files are.
   *
   * @var string
   */
  public $path = NULL;

  /**
   * Create a temporary directory and return its name.
   *
   * @param string $path
   *   Where create the directory, if not set, uses 'temporary://'.
   * @param string $prefix
   *   The prefix of directory, if not set uses an empty string.
   *
   * @return string
   *   The path to temporary directory.
   */
  public function createTemporaryDirectory($path = 'temporary://', $prefix = '') {
    $exists = FALSE;
    $name = '';
    while (!$exists) {
      $name = $path . '/' . $prefix . uniqid();
      drupal_mkdir($name);
      if (is_dir($name)) {
        $exists = TRUE;
      }
    }
    return $name;
  }

  /**
   * Create a zip file with all files from $this->path.
   *
   * @return string
   *   The name of the new zip file.
   */
  public function createZip() {
    $name = drupal_realpath(drupal_tempnam('temporary://', 'nodeyaml_zip_'));
    $zip = new Zip($name);
    $path = drupal_realpath($this->path);
    $files = $this->listFiles($path);
    foreach ($files as $file) {
      $file_inside = substr($file, strlen($path));
      $zip->add($file, $file_inside);
    }
    return $name;
  }

  /**
   * Extract all files from a zip file.
   *
   * @param string $zip_file
   *   The full path to zip file.
   * @param string $path
   *   The path where to extract the zip file, if is not set, will create one
   *   in temporary directory.
   *
   * @return string
   *   The path where the zip file is extracted.
   */
  public function extractZip($zip_file, $path = NULL) {
    if (!$path) {
      $path = $this->createTemporaryDirectory('temporary://', 'nodeyaml_');
    }
    $zip = new Zip($zip_file);
    $zip->extract($path);
    return $path;
  }

  /**
   * List all .yml files in a recursive way.
   *
   * @param $path
   *   The path to folder to list yaml files.
   *
   * @return array
   *   An array with full path+filename to each .yml file in $this->p5ath.
   */
  protected function listFiles($path) {
    $files = array();
    if (is_file($path)) {
      $files[] = $path;
    }
    elseif (is_dir($path)) {
      $all_files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
      $yaml_files = new \RegexIterator($all_files, '/\.yml$/');
      foreach ($yaml_files as $file) {
        /** @var \SplFileInfo $file */
        $files[] = $file->getPathname();
      }
    }
    return $files;
  }

  /**
   * Define the options used.
   *
   * @param array $options
   *   An array with the options. See the property $options of the class that
   *   extends this class with an explanations of each element in array.
   */
  public function setOptions(array $options = array()) {
    $this->options = $options;
  }
}
