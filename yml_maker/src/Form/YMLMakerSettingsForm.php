<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\YMLMakerSettingsForm
 */
namespace Drupal\yml_maker\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Manage Settings for the YML Maker module
 */
class YMLMakerSettingsForm extends ConfigFormBase {

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'yml_maker_admin_settings';
  }

  /**
   * @inheritDoc
   */
  protected function getEditableConfigNames() {
    return [
      'yml_maker.settings',
    ];
  }

  public function buildForm(array $form, FormStateInterface $form_state){
    $config = $this->config('yml_maker.settings');
    $directories_already_allowed = $config->get('directories_allowed');
    $ban_names = $config->get('ban_names');
    kint($ban_names);
   kint($directories_already_allowed);

    $form['#cache'] = ['max-age' => 0];
    $form['#tree'] = TRUE;

    $form['directories_allowed_container'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t("The list of path that are allowed to use with the YML Maker Form."),
      '#description' => t("Separate path by a virgule."),
      '#prefix' => '<div id="directories-allowed-ajax-wrapper">',
      '#suffix' => '</div>',
    );

    if ($form_state->get('directories_allowed_deltas') == '') {
      $form_state->set('directories_allowed_deltas', range(0, 3));
    }


    $allowed_field_count = $form_state->get('directories_allowed_deltas');
    kint($allowed_field_count);
    foreach ($allowed_field_count as $delta) {
      $form['directories_allowed_container'][$delta] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('container-inline'),
        ),
        '#tree' => TRUE,
      );

      $form['directories_allowed_container'][$delta]['directory_name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name - ' . $delta),
        '#size' => 25,
      );

      //https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21PathElement.php/class/PathElement/8.2.x

      $form['directories_allowed_container'][$delta]['directory_path'] = array(
        '#type' => 'path',
        '#title' => t('Path - ' . $delta),
        '#size' => 80,
      );

      $form['directories_allowed_container'][$delta]['directories_allowed_remove'] = array(
        '#type' => 'submit',
        '#value' => t('-'),
        '#submit' => array('::YMLMakerAllowedRemove'),
        '#ajax' => array(
          'callback' => '::YMLMakerAllowedRemoveCallback',
          'wrapper' => 'directories-allowed-ajax-wrapper',
        ),
        '#weight' => -50,
        '#attributes' => array(
          'class' => array('button-small'),
        ),
        '#name' => 'directories_allowed_remove_' . $delta,
      );
    }

    $form['directories_allowed_container']['directories_allowed_add'] = array(
      '#type' => 'submit',
      '#value' => t('Add one more directory'),
      '#submit' => array('::YMLMakerAllowedAddOne'),
      '#ajax' => array(
        'callback' => '::YMLMakerAllowedAddOneCallback',
        'wrapper' => 'directories-allowed-ajax-wrapper',
      ),
      '#weight' => 100,
    );
    
    /*************************************/
    $form['ban_names_container'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t("Mots bannis comme nom de fichier YML"),
      '#description' => t("Vous pouvez écrire une liste de mots qui seront bannis et ne pourront être utilisés dans le nom des fichiers. Cette restriction ne vaut que pour l'utilisation de l'interface YML Maker."),
      '#prefix' => '<div id="ban-names-ajax-wrapper">',
      '#suffix' => '</div>',
    );


    /**
     * On initialise le compter.
     */
    if ($form_state->get('field_ban_deltas') == '') {
      $form_state->set('field_ban_deltas', range(0, 2));
    }

    $field_ban_count = $form_state->get('field_ban_deltas');
    kint($field_ban_count);
    foreach ($field_ban_count as $delta) {
      $form['ban_names_container'][$delta] = array(
        '#type' => 'container',
        '#attributes' => array(
          'class' => array('container-inline'),
        ),
        '#tree' => TRUE,
      );

      $form['ban_names_container'][$delta]['ban'] = array(
        '#type' => 'textfield',
        '#title' => t('Mot Bannie - ' . $delta),
        '#size' => 25,
      );


      $form['ban_names_container'][$delta]['ban_remove'] = array(
        '#type' => 'submit',
        '#value' => t('-'),
        '#submit' => array('::YMLMakerBanRemove'),
        '#ajax' => array(
          'callback' => '::YMLMakerBanRemoveCallback',
          'wrapper' => 'ban-names-ajax-wrapper',
        ),
        '#weight' => -50,
        '#attributes' => array(
          'class' => array('button-small'),
        ),
        '#name' => 'ban_remove_' . $delta,
      );
    }

    $form['ban_names_container']['ban_add'] = array(
      '#type' => 'submit',
      '#value' => t('Add one more word'),
      '#submit' => array('::YMLMakerBanAddOne'),
      '#ajax' => array(
        'callback' => '::YMLMakerBanAddOneCallback',
        'wrapper' => 'ban-names-ajax-wrapper',
      ),
      '#weight' => 100,
    );

    return parent::buildForm($form, $form_state);

  }

  /************* DEBUT DU AJAX pour les ban *********/

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerBanRemove(array &$form, FormStateInterface $form_state) {
    // Get the triggering item
    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];

    // Store our form state
    $field_deltas_array = $form_state->get('ban_field_deltas');

    // Find the key of the item we need to remove
    $key_to_remove = array_search($delta_remove, $field_deltas_array);

    // Remove our triggered element
    unset($field_deltas_array[$key_to_remove]);

    // Rebuild the field deltas values
    $form_state->set('ban_field_deltas', $field_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages('Le champ répertoire -' . $key_to_remove . ' a été supprimé.');
  }

  /**
   * Le callback du remove. Il est lié au wrapper sélectionné par le bouton remove.
   */
  function YMLMakerBanRemoveCallback(array &$form, FormStateInterface $form_state) {
    return $form['ban_names_container'];
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerBanAddOne(array &$form, FormStateInterface $form_state) {

    // Stock le counter delta à partir de la form state.
    $field_deltas_array = $form_state->get('ban_field_deltas');

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
    $form_state->set('ban_field_deltas', $field_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages('Le champ Mot Bannie  a été ajouté.');
  }

  /**
   * Le callback du remove. Il est lié au wrapper sélectionné par le bouton remove.
   */
  function YMLMakerBanAddOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['ban_names_container'];
  }


  /************* FIN DU AJAX pour les bans *********/
  
  /************* DEBUT DU AJAX pour les directories *********/
  
  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerAllowedRemove(array &$form, FormStateInterface $form_state) {
    // Get the triggering item
    $delta_remove = $form_state->getTriggeringElement()['#parents'][1];

    // Store our form state
    $directories_allowed_deltas_array = $form_state->get('directories_allowed_deltas');

    // Find the key of the item we need to remove
    $key_to_remove = array_search($delta_remove, $directories_allowed_deltas_array);

    // Remove our triggered element
    unset($directories_allowed_deltas_array[$key_to_remove]);

    // Rebuild the field deltas values
    $form_state->set('directories_allowed_deltas', $directories_allowed_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages('Le champ répertoire -' . $key_to_remove . ' a été supprimé.');
  }

  /**
   * Le callback du remove. Il est lié au wrapper sélectionné par le bouton remove.
   */
  function YMLMakerAllowedRemoveCallback(array &$form, FormStateInterface $form_state) {
    return $form['directories_allowed_container'];
  }
  

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  function YMLMakerAllowedAddOne(array &$form, FormStateInterface $form_state) {

    // Stock le counter delta à partir de la form state.
    $directories_allowed_deltas_array = $form_state->get('directories_allowed_deltas');

    // check to see if there is more than one item in our array
    if (count($directories_allowed_deltas_array) > 0) {
      // Add a new element to our array and set it to our highest value plus one
      $directories_allowed_deltas_array[] = max($directories_allowed_deltas_array) + 1;
    }
    else {
      // Set the new array element to 0
      $directories_allowed_deltas_array[] = 0;
    }

    // Rebuild the field deltas values
    $form_state->set('directories_allowed_deltas', $directories_allowed_deltas_array);

    // Rebuild the form
    $form_state->setRebuild();

    // Return any messages set
    drupal_get_messages();
  }

  /**
   * Le callback du remove. Il est lié au wrapper sélectionné par le bouton remove.
   */
  function YMLMakerAllowedAddOneCallback(array &$form, FormStateInterface $form_state) {
    return $form['directories_allowed_container'];
  }
  
  
  
  
  /************* FIN DU AJAX pour les directories *********/
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Submit Form
    $values = $form_state->getValues();
    $directories_values = [];
    $ban_values = [];

    foreach ($values['directories_allowed_container'] as $key => $value) {
      if(is_array($value) && !empty($value['directory_name'] && !empty($value['directory_path']))){
        $directories_values[$value['directory_path']] = $value['directory_name'];
        //$directories_values[$key]['directory_path'] = $value['directory_path'];
      }
    }


    foreach ($values['ban_names_container'] as $key => $value) {
      if(is_array($value) && !empty($value['ban'])){
        $ban_values[$key]['ban'] = $value['ban'];
      }
    }

    \Drupal::configFactory()->getEditable('yml_maker.settings')
      // Set the submitted configuration setting
      ->set('directories_allowed', $directories_values)

      ->set('ban_names',$ban_values)
      ->save();

    drupal_set_message(t('Les options de configuration pour YML MAKER ont été sauvegardées.'));
    parent::submitForm($form, $form_state);


  }

}