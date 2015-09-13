<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\Tests\NodeTest.
 */

namespace Drupal\nodeyaml_tests\Tests;

/**
 * Tests for NodeYaml in node import/export.
 *
 * @group nodeyaml
 */
class NodeTest extends BaseTest {

  /**
   * Set to TRUE to strict check all configuration saved.
   *
   * While the drupal core it do not suport it, must keep at false.
   *
   * @see \Drupal\Core\Config\Testing\ConfigSchemaChecker
   *
   * @var bool
   */
  protected $strictConfigSchema = FALSE;

  /**
   * A user for tests.
   */
  protected $user;

  /**
   * Object with configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * List of modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'field',
    'field_ui',
    'text',
    'taxonomy',
    'link',
    'path',
    'nodeyaml',
    'nodeyaml_tests',
  );

  /**
   * Code run before each and every test method.
   */
  public function setUp() {
    parent::setUp();
    $this->resetAll();

    // Create object with configuration.
    $this->config = \Drupal::configFactory()->getEditable('nodeyaml.settings');

    // Create a content type, as we will create nodes on test.
    $settings = array(
      // Override default type (a random name).
      'type' => 'nodeyaml',
      'name' => 'NodeYaml Content',
    );
    //$this->drupalCreateContentType($settings);
    $this->checkPermissions(array(), TRUE);
    // Create a filter admin user.
    $permissions = array(
      'administer nodes',
      'access administration pages',
      'administer nodes',
      'administer site configuration',
      'administer node fields',
      'administer taxonomy',
    );
    $this->user = $this->drupalCreateUser($permissions);

    // Log in with admin user.
    $this->drupalLogin($this->user);
  }

  /**
   * Test the node import.
   *
   * This test import a node, test if the values of node are correct,
   * export the node and test if the exported file is equal to imported file.
   */
  public function testImport() {
    // Name of yaml file.
    $name = '/5a5ae82b-3322-4662-8030-4800de9dc89f.yml';

    // Set the path with test yaml files to import.
    $this->config->set('import.path.node',
      drupal_get_path('module', 'nodeyaml_tests') . '/src/Tests/NodeTest/nodes');
    $this->config->set('export.path.node', '/tmp/nodeyaml/NodeTest/nodes');
    // Only use import/export for node.
    $this->config->set('import.types', array('node'));
    $this->config->set('export.types', array('node'));
    $this->config->save();

    // Test the node import.
    $this->drupalGet('admin/config/development/nodeyaml/import');
    $this->drupalPostForm(NULL, array(), 'Import');

    // Test the node fields.
    $this->drupalGet('node/test');
    $this->assertResponse(200, 'The path alias is correct on add.');
    $this->assertTitle('Test node. | Drupal', 'Title imported on add.');
    $this->assertText('Test node body.', 'Body field imported on add.');
    // $this->assertText('Taxonomy Term', 'Taxonomy (field_tags) imported on add.');
    $this->assertLinkByHref('http://www.example.com', 0, 'Link(url) imported on add.');
    $this->assertLink('Example.com', 0, 'Link(title) imported on add.');

    // Test the node export.
    $this->drupalGet('admin/config/development/nodeyaml/export');
    $this->drupalPostForm(NULL, array(), 'Export');

    // Everything again, this time we test the node update.
    // Test the node import.
    $this->drupalGet('admin/config/development/nodeyaml/import');
    $this->drupalPostForm(NULL, array(), 'Import');

    // Test the node fields.
    $this->drupalGet('node/test');
    $this->assertResponse(200, 'The path alias is correct on update.');
    $this->assertTitle('Test node. | Drupal', 'Title imported on update.');
    $this->assertText('Test node body.', 'Body field imported on update.');
    $this->assertText('Taxonomy Term', 'Taxonomy (field_tags) imported on update.');
    $this->assertLinkByHref('http://www.example.com', 0, 'Link(url) imported on update.');
    $this->assertLink('Example.com', 0, 'Link(title) imported on update.');

    // Test the node export.
    $this->drupalGet('admin/config/development/nodeyaml/export');
    $this->drupalPostForm(NULL, array(), 'Export');

    // Test if the imported file and the exported have the same content.
    $import = file_get_contents($this->config->get('import.path.node') . $name);
    $export = file_get_contents($this->config->get('export.path.node') . $name);
    $this->assert($export == $import,
      'Both import and export files have the same contents on update.');
  }
}
