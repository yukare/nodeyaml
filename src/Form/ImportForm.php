<?php
/**
 * @file
 * Contains \Drupal\nodeyaml\NodeYamlImportForm.
 */

namespace Drupal\nodeyaml\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nodeyaml\Import;

class ImportForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nodeyaml_import_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nodeyaml.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['import'] = array(
      '#type' => 'fieldset',
      '#title' => t('Import the nodes'),
      '#description' => t('Import the nodes with current settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['import']['file'] = array(
      '#title' => t('File'),
      '#type' => 'file',
      '#description' => 'Import the content from a zip file instead of default location.',
      '#upload_location' => 'public://',
    );

    // Non-submitting button for exporting.
    $form['import']['import_nodes'] = array(
      '#type' => 'button',
      '#value' => t("Import"),
      '#executes_submit_callback' => TRUE,
      '#submit' => array('::importNodes'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Helper function for importing the nodes.
   */
  public function importNodes(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Config config */
    $config = \Drupal::config('nodeyaml.settings');
    $options = array(
      'drush' => FALSE,
      'types' => $config->get('export.types'),
    );
    $import = new Import();
    $import->setOptions($options);
    $import->process();
    drupal_set_message(t('Imported the nodes.'));
  }

}
