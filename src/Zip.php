<?php

namespace Drupal\nodeyaml;

class Zip extends \Drupal\Core\Archiver\Zip {

  /**
   * Implement the add function with the ability to not use the full filepath.
   *
   * Based on example at:
   * http://24b6.net/2012/12/15/considering-drupals-archiver-interface
   *
   * @param string $file_path
   *   Full path to file.
   * @param string|bool $basename
   *   The path to file inside zip file. FALSE will keep the
   *   $file_path(default), TRUE will use the filename without any path, any
   *   string will be the filename.
   *
   * @return $this
   */
  public function add($file_path, $basename = FALSE) {
    if ($basename) {
      if (!is_string($basename)) {
        $basename = basename($file_path);
      }
      $this->zip->addFile($file_path, $basename);
    }
    else {
      $this->zip->addFile($file_path);
    }
    return $this;
  }
}
