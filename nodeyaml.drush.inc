<?php
/**
 * @file
 * Drush hook and callback functions.
 */

use Drupal\nodeyaml\Export;

/**
 * Implements hook_drush_command().
 */
function nodeyaml_drush_command() {
  $items['export-nodes'] = array(
    'description' => 'Export all nodes from the site as yaml.',
    'aliases' => array('ne'),
    'options' => array(
      // 'content-type' => 'Export only the content type.',
      // 'path' => 'Export to a given path.',
      'dbg' => array(
        'description' => 'Show debug output.',
        'default' => FALSE,
      ),
      'output' => array(
        'description' => 'Output format to use: zip for a compressed zip file.',
        'default' => NULL,
      ),
      'types' => array(
        'description' => 'Types of entities to export: node,book,all.',
        'default' => 'all',
      ),
    ),
  );

  $items['import-nodes'] = array(
    'description' => 'Import yaml files as nodes for the site.',
    'aliases' => array('ni'),
  );

  return $items;
}

/**
 * Callback for the export-nodes command.
 */
function drush_nodeyaml_export_nodes() {
  $export = new Export();
  $items = nodeyaml_drush_command();
  $options = array();
  foreach ($items['export-nodes']['options'] as $key => $value) {
    $options[$key] = drush_get_option($key, $value['default']);
  }
  $options['types'] = explode(',', $options['types']);
  $export->setOptions($options);
  $export->process();
}

/**
 * Callback for the import-nodes command.
 */
function drush_nodeyaml_import_nodes() {

}
