<?php
/**
 * @file
 * Definition of Drupal\nodeyaml\Tests\BookTest.
 */

namespace Drupal\nodeyaml_tests\Tests;

/**
 * Tests for NodeYaml in book import/export.
 *
 * @group nodeyaml
 */
class BookTest extends BaseTest {

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
    'path',
    'nodeyaml',
    'nodeyaml_tests',
    'book',
    'link',
    'text',
    'taxonomy',
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
    // $this->drupalCreateContentType($settings);


    $this->checkPermissions(array(), TRUE);
    // Create a filter admin user.
    $permissions = array(
      'administer nodes',
      'access administration pages',
      //'create nodeyaml content',
      //'edit any nodeyaml content',
      'administer nodes',
      'administer site configuration',
      'add content to books',
      'administer book outlines',
      'create new books',
    );
    $this->user = $this->drupalCreateUser($permissions);

    // Log in with filter admin user.
    $this->drupalLogin($this->user);

    // Define the content type used in books.
    $this->defineBookPages('nodeyaml');
  }

  /**
   * Test for book import and export.
   */
  public function testBook() {
    debug('aqui');
    // Set the path with test yaml files to import.
    $this->config->set('import.path.node',
      drupal_get_path('module', 'nodeyaml') . '/src/Tests/BookTest/nodes');
    $this->config->set('import.path.book',
      drupal_get_path('module', 'nodeyaml') . '/src/Tests/BookTest/books');
    $this->config->set('export.path.node', '/tmp/nodeyaml/BookTest/nodes');
    $this->config->set('export.path.book', '/tmp/nodeyaml/BookTest/books');
    // Only use import/export for node and book.
    $this->config->set('import.types', array('node', 'book'));
    $this->config->set('export.types', array('node', 'book'));
    $this->config->save();

    // Test the node import.
    $this->drupalGet('admin/config/development/nodeyaml/import');
    $this->drupalPostForm(NULL, array(), 'Import');

    // Test the node fields.
    $this->drupalGet('node/1');
    $this->assertResponse(200, 'The path alias is correct on add.');
    $this->assertTitle('Test node. | Drupal', 'Title imported on add.');
    $this->assertLink('Test node 8.', 0, 'Link(title) imported on update.');

    $this->drupalGet('node/test/8');
  }
}
