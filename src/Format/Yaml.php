<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\Format\Yaml.
 */

namespace Drupal\nodeyaml\Format;

use Symfony\Component\Yaml\Yaml as YamlS;

/**
 * Converts an array to yaml format and vice-versa.
 */
class Yaml implements FormatInterface {
  /**
   * Convert an array to a string in yaml.
   *
   * @param array $array
   *   The array to convert.
   *
   * @return string
   *   A representation of the array in yaml format.
   */
  public static function arrayToContent(array $array) {
    $yaml = "";
    $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array), \RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $element) {

      $depth = $iterator->getDepth();

      $key = $iterator->key();
      $indent = str_repeat(' ', ($depth * 2));
      // Just want the value, not all the rest.
      $current = $element;
      if ($iterator->callHasChildren()) {
        if (is_numeric($key)) {
          $yaml .= "$indent" . "-\n";
        }
        else {
          $yaml .= "$indent" . "$key" . ":\n";
        }
      }
      else {
        // Handle values with multiple lines like body.
        if (strpos($current, "\n") !== FALSE) {
          $lines = explode("\n", $current);
          // Remove the last line if empty.
          if ($lines[count($lines) - 1] == '') {
            unset($lines[count($lines) - 1]);
          }
          $current = "|\n$indent  " . implode("\n$indent  ", $lines);
          $yaml .= "$indent" . "$key" . ": $current\n";
        }
        // Handle numeric keys.
        elseif (is_numeric($key)) {
          $yaml .= "$indent" . "- $current\n";
        }
        // All other keys.
        else {
          $yaml .= "$indent" . "$key" . ": $current\n";
        }
      }
    }
    return $yaml;
  }

  /**
   * Convert a string representation of an array in yaml to an array.
   *
   * @param string $content
   *   The string representation in yaml format.
   *
   * @return array
   *   The array represented by string.
   */
  public static function contentToArray($content) {
    /** @var array $yaml */
    $yaml = YamlS::parse($content);
    return $yaml;
  }
}
