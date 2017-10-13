<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\DeleteForm
 */
namespace Drupal\yml_maker\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Render\Element;

/**
 * Class DeleteForm
 *
 * @package Drupal\yml_maker\Form


yml_maker.delete_form:
path: '/admin/structure/yml_maker/delete/{fid}'
defaults:
_form: '\Drupal\yml_maker\Form\DeleteForm'
_title: 'DeleteForm'
requirements:
_permission: 'access yml_maker'
 */

class DeleteForm extends ConfirmFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yml_maker_delete_form';
  }
  
  public $fid;
  public $name;
  
  public function getQuestion() {
    return t('Do you want to delete %name?', array('%name' => $this->name));
  }
  public function getCancelUrl() {
    return new Url('yml_maker.list');
  }
  public function getDescription() {
    return t('Only do this if you are sure!');
  }
  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete it!');
  }
  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fid = NULL) {
    $this->fid = $fid;
    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $name = $dbmanager->getOneFileByID($fid);
    if(empty($name)){
      $form_state->setErrorByName('Id', t('No file found for this Id'));
    }
    $this->name = $name['file_name'];

    return parent::buildForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // TODO Implemente service
    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $dbmanager->delFile($this->fid);


    drupal_set_message("succesfully deleted");
    $form_state->setRedirect('yml_maker.list');
  }
}