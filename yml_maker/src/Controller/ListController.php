<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Controller\ListController.
 */
namespace Drupal\yml_maker\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Render\Element\Dropbutton;

/**
 * Controller for RSVP List List
 */
class ListController extends ControllerBase {


  /**
   * Creates the List page.
   *
   * @return array
   *  Render array for List output.
   */
  public function build() {
    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $list = $dbmanager->getListFiles();

    $content = array();
    $class_action = array('class' => array('button button-action button--primary',));

    $content['actions'] = array('#type' => 'actions',);
    $content['actions']['#attributes'] = array('class' => array('action-links',));
    $content['actions']['saver'] = array(
      '#type' => 'link',
      '#title' => t('Save a new file'),
      '#url' => Url::fromRoute('yml_maker.file_saver'),
      '#attributes' => $class_action,
    );

    $content['actions']['uploader'] = array(
      '#type' => 'link',
      '#url' => Url::fromRoute('yml_maker.file_uploader'),
      '#title' => t('Upload a new file'),
      '#attributes' => $class_action,
    );

    $content['message'] = array(
      '#markup' => $this->t('Below is a list of all the YML Registered by the YML Maker Module.'),
    );

    $headers = array(
      t('Name'),
      t('Path'),
      t('Description'),
      t('Actions'),
      t('Actions'),
    );
    $rows = array();
    foreach ($list as $key => $value) {

      $id = $value['id'];
      unset($value['id']);

      $edit = Url::fromRoute('yml_maker.builder',['fid' => $id]);
      $delete = Url::fromRoute('yml_maker.delete_form',['fid' => $id]);

      // Sanitize each entry.
      $rows[$key] = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', $value);
      $rows[$key]['edit'] = \Drupal::l('Edit', $edit);
      $rows[$key]['delete'] = \Drupal::l('Delete', $delete);
      //dump($rows[$key]);
    }


    $content['table'] = array(
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    );
    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    // TODO : Find if this work
    //$content['#attached']['library'][] = "menu_ui/drupal.menu_ui.adminforms";


    return $content;
  }


}
