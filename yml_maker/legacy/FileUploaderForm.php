<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\FileUploaderForm
 */
namespace Drupal\yml_maker\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\FileStorage;


/**
 * Form to save files path
 */
class FileUploaderForm extends FormBase {


  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'yml_maker_file_uploader';
  }


  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['infos'] = array(
      '#type' => 'label',
      '#value' => t("Enter the file name and path to the yml file. If it's already exist, it will be simply registered for this application, so you can manage it later. If the file doesn\'t already exist, it will also be created."),
    );

    $form['myfile'] = array(
      '#title' => $this->t('Upload your file'),
      '#type' => 'file',
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
      '#value' => t('Upload File'),
    );

    return $form;
  }
  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*
    $name = $form_state->getValue('file_name');
    $dbmanager = \Drupal::service('yml_maker.dbmanager');

    $check = $dbmanager->getOneFile($name)->fetchCol();
    //dump(!empty($dbmanager->getOneFile($name)->fetchCol()));

    if(!empty($check)){
      $form_state->setErrorByName('file', t('The file %file is already registered.', array('%file' => $name)));
    }
    */



    $file = $form_state->getValue('myfile');
    dump($file);
    $all_files = $this->getRequest()->files->get('files', []);
    if (!empty($all_files['myfile'])) {
      $file_upload = $all_files['myfile'];
      if ($file_upload->isValid()) {
        dump($file_upload);
        dump($file_upload->originalName);
        $form_state->setValue('myfile', $file_upload->getRealPath());
        $form_state->setValue('file_name', $file_upload->getOriginalName());

        //return;
      }
    }
    $file = $form_state->getValue('myfile');
    dump($file);

    $form_state->setErrorByName('test', t('Just for test'));

  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // TODO : Examiner ce code
    $validators = ['file_validate_extensions' => ['txt']];
    if ($file = file_save_upload('file', $validators, FALSE, 0)) {
      $data = file_get_contents($file->getFileUri());
      drupal_set_message($data);
    }

    $form_state->setErrorByName('test', t('Just for test'));

    $file = [];
    $file['file_name'] = $form_state->getValue('file_name');
    $file['file_path'] = $form_state->getValue('file_path');
    $file['file_description'] = $form_state->getValue('file_description');

    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $dbmanager->insertOneFile($file);

    drupal_set_message(t('Your File choice has been saved'));

  }

}