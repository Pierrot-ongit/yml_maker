<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\YMLMakerEditorForm
 */

namespace Drupal\yml_maker\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;



use Drupal\yml_maker\Service\BrowserService;
use Drupal\yml_maker\Service\FileReaderService;



class YMLMakerEditorForm extends FormBase {

  protected $browser;

  protected $fileReader;



  public function __construct(){
    $this->browser = \Drupal::service('yml_maker.browser');
    $this->fileReader = \Drupal::service('yml_maker.file_reader');
  }

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

    //TODO : Faut t'il ajouter deux paramétres dans le build pour directory et file ?

    $is_ajax = \Drupal::request()->isXmlHttpRequest();
    kint($is_ajax);

    $config = $this->config('yml_maker.settings');
    $directories_allowed = $config->get('directories_allowed');
    $ban_names = $config->get('ban_names');
    //kint($ban_names);
    kint($directories_allowed);
    //$files_founds = $this->browser->getYMLFiles('modules/custom/yml_maker/test');
    //kint($files_founds);

    //$file_content = $fileReader->loadFileContent($fid, $file);
    //dump($file_content);
    //kint($file_content);
/*
    $form['selection'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t("Selectionnez votre répertoire, ainsi que votre fichier."),
    );
    */
    $form['directories'] = array(
      '#title' => $this->t('Liste des répertoires'),
      '#type' => 'select',
      '#options' => $directories_allowed,
      '#ajax' => [
        'callback' => array($this, 'updateDirectories'),
        'wrapper' => 'files-selections-ajax-wrapper',
        'event' => 'select',
        'progress' => array(
          'type' => 'throbber',
          'message' => 'test throbber',
        ),
      ],
    );

    //$default_directories = $form_state->getValue('directories', $config_type);
    //$default = array_keys($directories_allowed)[0];
    //kint($default);
    $form['files'] = array(
      '#title' => $this->t('Liste des fichiers YML disponibles'),
      '#type' => 'select',
      '#options' => $this->browser->getYMLFiles(array_keys($directories_allowed)[0]),
      // Create wrapper for ajax.
      '#prefix' => '<div id="files-selections-ajax-wrapper">',
      '#suffix' => '</div>',
    );

    $form['launch'] = array(
      '#type' => 'submit',
      '#value' => t('Lancer l\'éditeur'),
      '#submit' => array('::buildEditor'),
      '#ajax' => array(
        'callback' => 'Drupal\config_translation\FormElement\DateFormat::ajaxSample',
        'wrapper' => 'editor-container-ajax-wrapper',
      ),
      '#weight' => 100,
    );



    /************* AJAX EXEMPLE ************/
    // Disable caching for the form
    $form['#cache'] = ['max-age' => 0];
    // Do not flatten nested form fields
    //$form['#tree'] = TRUE;

    $form['editor_container'] = array(
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
      $form['editor_container'][$delta] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('container-inline'),
        ),
        '#tree' => TRUE,
      );

      $form['editor_container'][$delta]['field1'] = array(
        '#type' => 'textfield',
        '#title' => t('Field 1 - ' . $delta),
        '#size' => 80,
      );
      

      $form['editor_container'][$delta]['remove_name'] = array(
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

    $form['editor_container']['add_name'] = array(
      '#type' => 'submit',
      '#value' => t('Add one more'),
      '#submit' => array('::YMLMakerEditorAddOne'),
      '#ajax' => array(
        'callback' => '::YMLMakerEditorAddOneCallback',
        'wrapper' => 'js-ajax-elements-wrapper',
      ),
      '#weight' => 99,
    );

    /************* FIN AJAX EXEMPLE ***********/

    $form['editor_container']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save this file.'),
      '#weight' => 100,
    );


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit Form
    $values = $form_state->getValues();
    $selection = $form_state->getValue('selection');
    kint($selection);
    $directory = $form_state->getValue(['selection','directories']);
    kint($directory);
    //die();
  }
  /***** FONCTIONS AJAX pour SELECTION *****/

  // TODO : Créer la fonction
  /**
   * Handles switching the directories selector.
   */
  public function updateDirectories(array &$form, FormStateInterface $form_state){
    /*
    $values = $form_state->getValues();
    kint($values);
    $directory = $form_state->getValue(['selection','directories']);
    kint($directory);
    //die();
    //$form['config_name']['#options'] = $this->findConfiguration($form_state->getValue('config_type'));
    */
    /*
    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('test fonction ajax'));
    return $response;

    */

    // We find the new value of the directories selector
    $directory = $form_state->getValue('directories');
    // We change the files selector options by finding the news files for this new directory.
    $form['selection']['files']['#options'] = $this->browser->getYMLFiles($directory);
    // We return the files selector element on the targeted wrapper.
    return $form['selection']['files'];


  }


  // TODO : Créer la fonction
 function buildEditor(array &$form, FormStateInterface $form_state){
   // Example for brevity only, inject the request_stack service and call
   // getCurrentRequest() on it to get the request object if possible.
   $request = \Drupal::request();
   $is_ajax = \Drupal::request()->isXmlHttpRequest();
   kint($is_ajax);
    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('test fonction ajax'));
   $form_state->setRebuild();
    return $response;
  }

  // TODO : Créer la fonction
  function buildEditorCallback(array &$form, FormStateInterface $form_state){
    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('test fonction ajax callback'));
    return $response;
  }


  /***** FONCTIONS AJAX pour EDITOR *******/
  // TODO : Refaire toutes les fonctions.

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
    return $form['editor_container'];
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
    return $form['editor_container'];
  }






}
