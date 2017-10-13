<?php
/**
 * Created by PhpStorm.
 * User: pierre
 * Date: 10/10/17
 * Time: 23:21
 */

namespace Drupal\yml_maker\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
/**
 * Implements the ajax demo form controller.
 *
 * This example demonstrates using ajax callbacks to populate the options of a
 * color select element dynamically based on the value selected in another
 * select element in the form.
 *
 * @see \Drupal\Core\Form\FormBase
 * @see \Drupal\Core\Form\ConfigFormBase
 */
class AddAnotherItem extends FormBase {
  protected $number = 1;
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'add_another_item';
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#tree'] = TRUE;
    $form['container'] = [
      '#type'       => 'container',
      '#attributes' => ['id' => 'my-container'], // CHECK THIS ID
    ];
    for ($i = 1; $i <= $this->number; $i++) {
      $form['container']['name_' . $i] = [
        '#type'       => 'textfield',
        '#attributes' => ['placeholder' => $this->t('Name')],
        '#size'       => 20,
        '#required'   => TRUE,
      ];
    }
    // Disable caching on this form.
    $form_state->setCached(FALSE);
    $form['container']['actions'] = [
      '#type' => 'actions',
    ];
    /*
     * The #ajax attribute used in the temperature input element defines an ajax
     * callback that will invoke the colorCallback method on this form object.
     * Whenever the temperature element changes, it will invoke this callback
     * and replace the contents of the color_wrapper container with the results
     * of this method call.
     */
    $form['container']['actions']['add_item'] = [
      '#type'   => 'submit',
      '#value'  => $this->t('Add another name'),
      '#submit' => ['::MYMODULE_MYFORM_add_item'],
      '#ajax'   => [
        // Could also use [ $this, 'colorCallback'].
        'callback' => '::MYMODULE_MYFORM_ajax_callback',
        'wrapper'  => 'my-container', // CHECK THIS ID
      ],
    ];
    if ($this->number > 1) {
      $form['container']['actions']['remove_item'] = [
        '#type'                    => 'submit',
        '#value'                   => $this->t('Remove latest name'),
        '#submit'                  => ['::MYMODULE_MYFORM_remove_item'],
        // Since we are removing a name, don't validate until later.
        '#limit_validation_errors' => [],
        '#ajax'                    => [
          'callback' => '::MYMODULE_MYFORM_ajax_callback',
          'wrapper'  => 'my-container', // CHECK THIS ID
        ],
      ];
    }
    // $form['submit'] = array(
    //   '#type'  => 'submit',
    //   '#value' => $this->t('Submit'),
    // );
    return $form;
  }
  /**
   * Implements callback for Ajax event on color selection.
   *
   * @param array $form
   *   From render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of form.
   *
   * @return array
   *   Color selection section of the form.
   */
  public function MYMODULE_MYFORM_ajax_callback($form, $form_state) {
    return $form['container'];
  }
  public function MYMODULE_MYFORM_add_item(array &$form, FormStateInterface $form_state) {
    $this->number++;
    $form_state->setRebuild();
  }
  public function MYMODULE_MYFORM_remove_item(array &$form, FormStateInterface $form_state) {
    if ($this->number > 1) {
      $this->number--;
    }
    $form_state->setRebuild();
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
    // Final submit
  }
}