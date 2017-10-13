<?php


namespace Drupal\yml_maker\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FormTest extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mymodule_form_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $item_id = NULL) {

    // Disable caching for the form
    $form['#cache'] = ['max-age' => 0];

    // Do not flatten nested form fields
    $form['#tree'] = TRUE;

    $form['field_container'] = array(
      '#type' => 'container',
      '#weight' => 80,
      '#tree' => TRUE,
      // Set up the wrapper so that AJAX will be able to replace the fieldset.
      '#prefix' => '<div id="js-ajax-elements-wrapper">',
      '#suffix' => '</div>',
    );

    if ($form_state->get('field_deltas') == '') {
      $form_state->set('field_deltas', range(0, 3));
    }

    $field_count = $form_state->get('field_deltas');

    foreach ($field_count as $delta) {
      $form['field_container'][$delta] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('container-inline'),
        ),
        '#tree' => TRUE,
      );

      $form['field_container'][$delta]['field1'] = array(
        '#type' => 'textfield',
        '#title' => t('Field 1 - ' . $delta),
        '#size' => 10,
      );

      $form['field_container'][$delta]['field2'] = array(
        '#type' => 'textfield',
        '#title' => t('Field 2 - ' . $delta),
        '#size' => 10,
      );

      $form['field_container'][$delta]['remove_name'] = array(
        '#type' => 'submit',
        '#value' => t('-'),
        '#submit' => array('::mymoduleAjaxExampleAddMoreRemove'),
        '#ajax' => array(
          'callback' => '::mymoduleAjaxExampleAddMoreRemoveCallback',
          'wrapper' => 'js-ajax-elements-wrapper',
        ),
        '#weight' => -50,
        '#attributes' => array(
          'class' => array('button-small'),
        ),
        '#name' => 'remove_name_' . $delta,
      );
    }

    $form['field_container']['add_name'] = array(
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::mymoduleAjaxExampleAddMoreAddOne'),
      '#ajax' => array(
        'callback' => '::mymoduleAjaxExampleAddMoreAddOneCallback',
        'wrapper' => 'js-ajax-elements-wrapper',
      ),
      '#weight' => 100,
    );

    $form['other_field'] = array(
      '#type' => 'textfield',
      '#title' => t('Other field'),
    );

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function mymoduleAjaxExampleAddMoreRemove(array &$form, FormStateInterface $form_state) {
    // Get the triggering item
    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];

    // Store our form state
    $field_deltas_array = $form_state->get('field_deltas');

    // Find the key of the item we need to remove
    $key_to_remove = array_search($delta_remove, $field_deltas_array);

    // Remove our triggered element
    unset($field_deltas_array[$key_to_remove]);

    // Rebuild the field deltas values
    $form_state->set('field_deltas', $field_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  function mymoduleAjaxExampleAddMoreRemoveCallback(array &$form, FormStateInterface $form_state) {
    return $form['field_container'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function mymoduleAjaxExampleAddMoreAddOne(array &$form, FormStateInterface $form_state) {

    // Store our form state
    $field_deltas_array = $form_state->get('field_deltas');

    // check to see if there is more than one item in our array
    if (count($field_deltas_array) > 0) {
      // Add a new element to our array and set it to our highest value plus one
      $field_deltas_array[] = max($field_deltas_array) + 1;
    }
    else {
      // Set the new array element to 0
      $field_deltas_array[] = 0;
    }

    // Rebuild the field deltas values
    $form_state->set('field_deltas', $field_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  function mymoduleAjaxExampleAddMoreAddOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['field_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit Form
  }

}