<?php
/**
 * @file
 * Contains \Drupal\nodeyaml\NodeYamlExportForm.
 */

namespace Drupal\nodeyaml\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\nodeyaml\Export;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nodeyaml_export_settings';
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
    /**$form['entity'] = array(
     * '#type' => 'fieldset',
     * '#title' => t('Types of content entities to export.'),
     * '#description' => t('Define which content entities export.'),
     * '#collapsible' => TRUE,
     * '#collapsed' => FALSE,
     * );
     *
     * $types = array(
     * 'book' => t('Book'),
     * 'node' => t('Node'),
     * );
     *
     * $form['entity']['type'] = array(
     * '#title' => t('Entity types.'),
     * '#type' => 'checkboxes',
     * '#options' => $types,
     * );*/

    $form['export'] = array(
      '#type' => 'fieldset',
      '#title' => t('Export the content'),
      '#description' => t('Export the content with current settings'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    );

    $form['export']['output'] = array(
      '#type' => 'radios',
      '#title' => t('Select how to export'),
      '#description' => t('Select how you want to export the content.'),
      '#options' => array(
        'multiple' => t('Multiple files to export dir(@export_dir).', array('@export_dir' => 'public://nodeyaml')),
        'multiple_zip' => t('Multiple files to download as zip file.'),
      ),
      '#default_value' => 'multiple',
    );

    // Non-submitting button for exporting.
    $form['export']['export_nodes'] = array(
      '#type' => 'button',
      '#value' => t("Export"),
      '#executes_submit_callback' => TRUE,
      '#submit' => array('::exportContent'),
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
   * Submit function to export the content.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public function exportContent(array $form, FormStateInterface $form_state) {
    $output = $form_state->getValue('output');
    /** @var \Drupal\Core\Config\Config config */
    $config = $this->config('nodeyaml.settings');

    $options = array(
      'drush' => FALSE,
      'types' => $config->get('export.types'),
    );
    if ($output == 'multiple_zip') {
      $url = Url::fromRoute('nodeyaml.export_zip');
      $form_state->setRedirectUrl($url);
    }
    else {
      $export = new Export();
      $export->overwrite = TRUE;
      $export->setOptions($options);
      $export->process();
      drupal_set_message(t('Exported the content.'));
    }
  }
}
