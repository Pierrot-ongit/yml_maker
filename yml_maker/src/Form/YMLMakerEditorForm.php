<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\YMLMakerEditorForm
 */

namespace Drupal\yml_maker\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class YMLMakerEditorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'yml_maker_editor_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('yml_maker.settings');
    $directories_allowed = $config->get('directories_allowed');
    $ban_names = $config->get('ban_names');
    kint($ban_names);
    kint($directories_allowed);
    $fileReader = \Drupal::service('yml_maker.file_reader');
    $browser = \Drupal::service('yml_maker.browser');
    $files_founds = $browser->getYMLFiles(key($directories_allowed['/modules/custom/yml_maker/test']));
    kint($files_founds);

    //$file_content = $fileReader->loadFileContent($fid, $file);
    //dump($file_content);
    //kint($file_content);

    $form['selection'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t("Selectionnez votre répertoire, ainsi que votre fichier."),
    );
    $form['selection']['directories'] = array(
      '#type' => 'select',
      '#options' => $directories_allowed,

    );
    $form['selection']['files'] = array(
      '#type' => 'select',
      '#options' => [
        'test file' => 'test file test test'
      ],
    );
    $form['selection']['launch'] = array(
      '#type' => 'submit',
      '#value' => t('Lancer l\'éditeur'),
      '#submit' => array('::YMLMakerAllowedAddOne'),
      '#ajax' => array(
        'callback' => '::YMLMakerAllowedAddOneCallback',
        'wrapper' => 'directories-allowed-ajax-wrapper',
      ),
      '#weight' => 100,
    );



    /************* AJAX EXEMPLE ************/
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
        '#size' => 80,
      );
      

      $form['field_container'][$delta]['remove_name'] = array(
        '#type' => 'submit',
        '#value' => t('-'),
        '#submit' => array('::YMLMakerEditorRemove'),
        '#ajax' => array(
          'callback' => '::YMLMakerEditorRemoveCallback',
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
      '#submit' => array('::YMLMakerEditorAddOne'),
      '#ajax' => array(
        'callback' => '::YMLMakerEditorAddOneCallback',
        'wrapper' => 'js-ajax-elements-wrapper',
      ),
      '#weight' => 100,
    );

    /************* FIN AJAX EXEMPLE ***********/

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerEditorRemove(array &$form, FormStateInterface $form_state) {
    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];

    $field_deltas_array = $form_state->get('field_deltas');

    $key_to_remove = array_search($delta_remove, $field_deltas_array);

    unset($field_deltas_array[$key_to_remove]);

    $form_state->set('field_deltas', $field_deltas_array);
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
  function YMLMakerEditorRemoveCallback(array &$form, FormStateInterface $form_state) {
    return $form['field_container'];
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerEditorAddOne(array &$form, FormStateInterface $form_state) {

    $field_deltas_array = $form_state->get('field_deltas');
    if (count($field_deltas_array) > 0) {
      $field_deltas_array[] = max($field_deltas_array) + 1;
    }
    else {
      $field_deltas_array[] = 0;
    }

    $form_state->set('field_deltas', $field_deltas_array);
    $form_state->setRebuild();

    drupal_get_messages();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  function YMLMakerEditorAddOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['field_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit Form
  }

}