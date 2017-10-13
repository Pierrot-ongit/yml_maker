<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\FileSaverForm
 */
namespace Drupal\yml_maker\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\FileStorage;


/**
 * Form to save files path
 */
class FileSaverForm extends FormBase {


  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'yml_maker_file_saver';
  }



  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['infos'] = array(
      '#markup' => t("Enter the file name and path to the yml file. If it's already exist, it will be simply registered for this application, so you can manage it later. If the file doesn't already exist, it will also be created."),
    );


    $form['file_name'] = array(
      '#title' => t('File Name'),
      '#type' => 'textfield',
      '#size' => 45,
      '#description' => t("Enter the name for the yml file."),
      '#required' => TRUE,
    );


    $form['file_path'] = array(
      '#title' => t('File Path'),
      '#type' => 'textfield',
      '#size' => 85,
      '#description' => t("Enter the path to the yml file."),
      '#required' => TRUE,
    );

    $form['file_description'] = array(
      '#title' => t('File Description'),
      '#type' => 'textfield',
      '#size' => 85,
      '#description' => t("Enter the file description to the yml file."),
      '#required' => FALSE,
    );


    $form['submit'] =  array(
      '#type' => 'submit',
      '#value' => t('Save File'),
    );


    // $dbmanager = \Drupal::service('yml_maker.dbmanager');
    //$test_select = $dbmanager->getListFiles();
    //dump($test_select);

    return $form;
  }
  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $name = $form_state->getValue('file_name');
    $dbmanager = \Drupal::service('yml_maker.dbmanager');

    $check = $dbmanager->getFileByName($name);
    //dump(!empty($dbmanager->getOneFile($name)->fetchCol()));

    if(!empty($check)){
      $form_state->setErrorByName('file', t('The file %file is already registered.', array('%file' => $name)));
    }

    //$form_state->setErrorByName('test', t('Just for test'));
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $file = [];
    $file['file_name'] = $form_state->getValue('file_name');
    $file['file_path'] = $form_state->getValue('file_path');
    $file['file_description'] = $form_state->getValue('file_description');

    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $dbmanager->insertOneFile($file);


    drupal_set_message(t('Your File choice has been saved'));

  }





}