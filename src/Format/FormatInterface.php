<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\Format\FormatInterface.
 */

namespace Drupal\nodeyaml\Format;

/**
 * Define the interface used by formats.
 */
interface FormatInterface {
  /**
   * Convert from content(string) to an array.
   *
   * This get the content from a string(The content of a file) and convert it
   * to an array with the values.
   *
   * @param string $content
   *   The content to convert.
   *
   * @return array
   *   An array with the content.
   */
  public static function contentToArray($content);

  /**
   * Convert the content from an array to a string in the format.
   *
   * This get the content as an array and convert it to a string, to store in
   * a file.
   *
   * @param array $array
   *   The array with the data to convert.
   *
   * @return string
   *   The string with the data convert to format.
   */
  public static function arrayToContent(array $array);
}
