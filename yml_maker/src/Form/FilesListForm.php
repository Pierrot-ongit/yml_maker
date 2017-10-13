<?php
/**
 * @file
 * Contains \Drupal\rsvplist\Form\FilesListForm
 */
namespace Drupal\yml_maker\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Form to manage the list of the YML registered by the application
 */
class FilesListForm extends FormBase {

  private $file; // TODO : A conserver ?


  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'yml_maker_builder';
  }



  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // List of files already saved in the DB.
    $select = Database::getConnection()->select('yml_maker', 'y');
    $select->addField('y', 'file');
    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $options = [];
    foreach ($entries as $key => $entry){
      array_push($options,$entry['file']);
    }

    $form['file_select'] = [
      '#type' => 'select',
      '#title' => t('Select element'),
      '#options' => $options,
    ];

    $form['submit'] =  array(
      '#type' => 'submit',
      '#value' => t('Choose File'),
    );
    $form['file_fieldset'] = array(
      '#type' => 'fieldset',
      '#title' => t('File Structure'),
    );
    $form['file_structure'] = array(
      '#type' => 'html',
      '#title' => t('File Structure'),
      '#value' => '',
    );


    return $form;
  }
  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {



    drupal_set_message(t('Your File choice has been saved'));

  }





}