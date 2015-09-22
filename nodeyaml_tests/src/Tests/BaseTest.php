<?php
/**
 * @file
 * Definition of Drupal\nodeyaml_test\Tests\BaseTest.
 */

namespace Drupal\nodeyaml_tests\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests for NodeYaml in node import/export.
 *
 * @group nodeyaml
 */
abstract class BaseTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Creates a new field and attached it to a content type.
   *
   * @param string $content_type
   *   The content type to attach the field to.
   * @param string $field_name
   *   The name of the field.
   * @param string $field_type
   *   The type of the field.
   * @param array $settings
   *   (optional) An array of field settings.
   * @param array $instance_settings
   *   (optional) An array of field instance settings.
   */
  protected function createField($content_type, $field_name, $field_type, array $settings = array(), array $instance_settings = array()) {
    $label = $field_name . '_' . $field_type . '_label';
    $edit = array(
      'new_storage_type' => $field_type,
      'label' => $label,
      'field_name' => $field_name,
    );


    /*    $edit = array(
          'fields[_add_new_field][label]' => $label,
          'fields[_add_new_field][field_name]' => $field_name,
          'fields[_add_new_field][type]' => $field_type,
        );*/
    $this->drupalPostForm("admin/structure/types/manage/$content_type/fields/add-field", $edit, 'Save and continue');

    // (Default) Configure the field.
    $this->drupalPostForm(NULL, $settings, 'Save field settings');
    $this->assertText('Updated field ' . $label . ' field settings.');

    // Field instance settings.
    $this->drupalPostForm(NULL, $instance_settings, 'Save settings');
    $this->assertText('Saved ' . $label . ' configuration.');
  }

  /**
   * Create a new vocabulary.
   *
   * @param string $name
   *   The name for vocabulary, it is used for both 'name' and 'vid'.
   * @param string $description
   *   (optional) A description for it.
   */
  protected function createVocabulary($name, $description = '') {
    $edit = array(
      'name' => $name,
      'vid' => $name,
      'description' => $description,
    );
    $this->drupalPostForm('admin/structure/taxonomy/add', $edit, 'Save');
  }

  /**
   * Define which content type the book module will use.
   *
   * @param string $type
   *   The content type to use in books.
   */
  protected function defineBookPages($type) {
    $settings = array(
      "book_allowed_types[{$type}]" => TRUE,
      'book_child_type' => $type,
    );
    $this->drupalPostForm('admin/structure/book/settings', $settings, 'Save configuration');
  }
}
