<?php
/**
 * @file
 * Contains \Drupal\nodeyaml\Controller\DownloadZip.
 */

namespace Drupal\nodeyaml\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\nodeyaml\Export;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Export the content as a zip file.
 */
class ExportZip extends ControllerBase {
  /**
   * {@inheritdoc}
   */
  public function content() {
    /** @var \Drupal\Core\Config\Config config */
    $config = \Drupal::config('nodeyaml.settings');
    $options = array(
      'drush' => FALSE,
      'types' => $config->get('export.types'),
      'output' => 'zip',
    );
    $export = new Export();
    $export->overwrite = TRUE;
    $export->setOptions($options);
    $export->process();

    $response = new BinaryFileResponse(drupal_realpath($export->getZipName()));
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'export.zip');
    $response->deleteFileAfterSend(TRUE);
    return $response;
  }
}
