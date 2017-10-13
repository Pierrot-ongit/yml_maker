<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Form\BuilderForm
 */

namespace Drupal\yml_maker\legacy;

use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;


/**
 * Form to manage content of a YML File.
 */
class LegacyBuilderForm extends FormBase {

  public $fid;
  public $name;
  public $path;

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'yml_maker_builder_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fid = NULL) {

    $this->fid = $fid;
    $dbmanager = \Drupal::service('yml_maker.dbmanager');
    $file = $dbmanager->getOneFileByID($fid);
    //Redirect if the file is not found
    if(empty($file)){
      drupal_set_message($this->t('No file found for this ID.'), 'error');
      return $this->redirect('yml_maker.list')->send();
    }

    $this->name = $file['file_name'];
    $this->path = $file['file_path'];
    $fileReader = \Drupal::service('yml_maker.file_reader');
    $file_content = $fileReader->loadFileContent($fid, $file);
    //dump($file_content);
    //kint($file_content);

    /**** AJAX PLAYFIELD ***/
    $form['test-ajax'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => 'test-ajax',
        'id' => 'test-ajax',
      ),
    );
    $triggerElement = $form_state->getTriggeringElement();

    if ($triggerElement) {
      $form['test-ajax']['#markup'] = $this->t('triggered');
    }


    /***** FIN AJAX PLAYFIELD ***/

    $form['infos'] = array(
      '#markup' => t("You can modifie your YML file here."),
    );


    // TODO : UN BON GROS IF en fonction de l'url.

    $form['file_name'] = array(
      '#title' => t('File Name'),
      '#type' => 'textfield',
      '#size' => 45,
      '#description' => t("Enter the name for the yml file."),
      '#required' => TRUE,
      '#default_value' => $this->name,
    );


    $form['file_path'] = array(
      '#title' => t('File Path'),
      '#type' => 'textfield',
      '#size' => 85,
      '#description' => t("Enter the path to the yml file."),
      '#required' => TRUE,
      '#default_value' => $this->path,
    );



    // Call to a fonction to build the draggable table
    $this->editBuilderTable($file_content, $form);
    // TODO : FIN DU IF.


    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save changes'),
    );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

    /*
    $user_input = $form_state->getUserInput();
    kint($user_input);
    kint($form_state->getValues());

    if (isset($user_input['mytable'])) {
      $order = array_flip(array_keys($user_input['mytable']));
      //dump($user_input['mytable']);
      kint($order);

    }
    $format_value = \Drupal\Component\Utility\NestedArray::getValue(
      $form_state->getValues(),
      $form_state->getTriggeringElement()['#array_parents']
    );
    kint($form_state->getTriggeringElement());

    $input = $form_state->getUserInput();

        if (isset($input['op']) && $input['op'] === 'Add one line') {
          //$form_state->setErrorByName('test', t('Find user input button op'));
          //The button whose value is 'Button Value' is clicked
          //\Drupal\Core\Form\drupal_set_message('TEST Button add one line');
          dump($input);
        }

    //die("\n<br/>DIE");
    //$form_state->setErrorByName('test', t('Just for test'));
*/
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Change to adapt to this form
    // Save elements in the same order as defined in post rather than the form.
    // This ensures parents are updated before their children, preventing orphans.
    $user_input = $form_state->getUserInput();

    if (isset($user_input['table'])) {
      $order = array_flip(array_keys($user_input['table']));
      $form['table'] = array_merge($order, $form['table']);

      foreach (Element::children($form['table']) as $key) {
        if ($form['table'][$key]['#item']) {
          $row = $form['table'][$key];
          $values = $form_state->getValue(['table', $key]);

          // Update menu item if moved.
          if ($row['parent']['pid']['#default_value'] != $values['pid'] || $row['weight']['#default_value'] != $values['weight']) {
            $link = $this->bookManager->loadBookLink($values['nid'], FALSE);
            $link['weight'] = $values['weight'];
            $link['pid'] = $values['pid'];
            $this->bookManager->saveBookLink($link, FALSE);
          }

          // Update the title if changed.
          if ($row['title']['#default_value'] != $values['title']) {
            $node = $this->nodeStorage->load($values['nid']);
            $node->revision_log = $this->t('Title changed from %original to %current.', ['%original' => $node->label(), '%current' => $values['title']]);
            $node->title = $values['title'];
            $node->book['link_title'] = $values['title'];
            $node->setNewRevision();
            $node->save();
            $this->logger('content')->notice('book: updated %title.', ['%title' => $node->label(), 'link' => $node->link($this->t('View'))]);
          }
        }
      }
    }

    drupal_set_message($this->t('Updated book %title.', ['%title' => $form['#node']->label()]));

  }

  public function editBuilderTable(array $file_content,array &$form) {
      //dump($file_content);
      $size = count($file_content);
      $fileReader = \Drupal::service('yml_maker.file_reader');


    $form['mytable'] = array(
      '#type' => 'table',
      '#header' => [
        $this->t('Lines'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => t('This file have not lines yet.'),

      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically prepended.
      // if there is none, an HTML ID is auto-generated.

      '#tabledrag' => [
      // La config table pour organiser la weight
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'line-weight',
      ],
      ],
    );

    // FORM TREE
    $form['mytable']['#tree'] = TRUE;

    // Build the table rows and columns.
    // The first nested level in the render array forms the table row, on which you
    // likely want to set #attributes and #weight.
    // Each child element on the second level represents a table column cell in the
    // respective table row, which are render elements on their own. For single
    // output elements, use the table cell itself for the render element. If a cell
    // should contain multiple elements, simply use nested sub-keys to build the
    // render element structure for drupal_render() as you would everywhere else.


    foreach ($file_content as $key => $line) {
      // TableDrag: Mark the table row as draggable.
      $form['mytable'][$key]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['mytable'][$key]['#weight'] = $line['weight'];


      // $parent_line = $fileReader->getParentLine($line['plid']); TODO : inutile pour l'instant
      if (isset($line['identSize']) && $line['identSize'] > 1) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $line['identSize'],
        ];
      }

      // Column with the text.
      $form['mytable'][$key]['text'] = array(
        '#prefix' => !empty($indentation) ? \Drupal::service('renderer')->render($indentation) : '',
        '#type' => 'textfield',
        '#default_value' => $line['new_line'],
        '#ajax' => [
          'callback' => array($this, 'testAjax'),
          'event' => 'change',
          'progress' => array(
            'type' => 'throbber',
            'message' => t('Ajax on textfield...'),
          ),
        ],
        '#suffix' => '<span class="test-message"></span>',
      );


      // TableDrag: Weight column element.
      // NOTE: The tabledrag javascript puts the drag handles inside the first column,
      // then hides the weight column. This means that tabledrag handle will not show
      // if the weight element will be in the first column so place it further as in this example.


      $form['mytable'][$key]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $line['weight'],
        '#delta' => $size,
        //'#parents' => array('mytable', $key, 'weight'),
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('line-weight')),
      );



      $form['mytable'][$key]['actions'] = [
        '#type' => 'actions',
        '#attributes' => array(
          'style'=>'margin: 0;'
        ),
      ];

      $form['mytable'][$key]['actions']['add_line'] = [
        '#type'   => 'button',
        '#value'  => $this->t('Add one line'),
        '#name' => 'add_line_'.$key,
        //'#submit' => [ $this, 'addLine'],
        '#ajax'   => [
          'callback' => [$this, 'addLine'],
          'event' => 'mousedown',
          'prevent' => 'click',
          //'wrapper'  => '#mytable', // CHECK THIS ID
        ],
      ];
      $form['mytable'][$key]['actions']['remove_line'] = [
        '#type'   => 'button',
        '#value'  => $this->t('Remove this Line'),
        '#name' => 'remove_line_'.$key,
        '#submit' => ['::removeLine'],
        // Since we are removing a name, don't validate until later.
        '#limit_validation_errors' => [],
        '#ajax'   => [
          'callback' => [$this, 'removeLine'],
          //'wrapper'  => 'mytable', // CHECK THIS ID
        ],
      ];




    }


  }

  public function testAjax(array &$form, FormStateInterface $form_state){

    $text = '<span>TEST AJAX ON TEXTFIELD CHANGE.</span>';
    //kint($text);
    $response = new AjaxResponse();

    //Parameters
    //array $array: The array from which to get the value.
   // array $parents: An array of parent keys of the value, starting with the outermost key.
    // On récupére la valeur de l'élément qui a été trigger.
    $format_value = \Drupal\Component\Utility\NestedArray::getValue(
      $form_state->getValues(),
      $form_state->getTriggeringElement()['#array_parents']
    );

    //$test_value = $form_state->getValues();
    //$test_value = '<pre>'.$test_value.'</pre>';

    $this->ajax_dump($format_value);

      //drupal_set_message('TEST AJAX');
    //$response->addCommand(new AlertCommand('TEST'));
    $css = ['border' => '10px solid red'];
    $message = $this->t($text);
    $response->addCommand(new CssCommand('#test-ajax', $css));
    $response->addCommand(new HtmlCommand('#test-ajax', $format_value));
    return $response;

  }

  public function addLine(array &$form, FormStateInterface $form_state) {
    $format_value = \Drupal\Component\Utility\NestedArray::getValue(
      $form_state->getValues(),
      $form_state->getTriggeringElement()['#array_parents']);
    $input = $form_state->getUserInput();
    //$test_values = '<pre>'.$input.'</pre>';

    //kint($input);
     $this->ajax_dump($input);
    //die("\n<br/>DIE");

    $response = new AjaxResponse();

    return $response;

  }

  public function removeLine(array &$form, FormStateInterface $form_state) {

    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('TEST REMOVE LINE'));
    //$response->addCommand(new RemoveCommand();
    $response->addCommand(new ReplaceCommand(
      '#edit-date-format-suffix',
      '<small id="edit-date-format-suffix">' . $format . '</small>'));
    //$form_state->drupal_set_message('TEST ADD LINE');
    return $response;

  }

  function ajax_dump($item)
  {
    die('' . print_r($item, TRUE) . '');
  }


  /**
   * @param array $file_content
   * @param array $form
   * LEGACY !
   * Ancienne construction de table, en méthode drag and drop sur la depth en plus de la weight.
   * Quasi impossible et marche mal.
   */
  public function oldbuilderTable(array $file_content,array &$form) {
    //dump($file_content);
    $size = count($file_content);
    $fileReader = \Drupal::service('yml_maker.file_reader');


    $form['mytable'] = array(
      '#type' => 'table',
      '#header' => [
        $this->t('Lines'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => t('This file have not lines yet.'),

      // TableDrag: Each array value is a list of callback arguments for
      // drupal_add_tabledrag(). The #id of the table is automatically prepended.
      // if there is none, an HTML ID is auto-generated.

      '#tabledrag' => [
        // La table pour organiser la depth
        [
          'action' => 'match',
          'relationship' => 'parent',
          'group' => 'line-pid',
          'subgroup' => 'line-pid',
          'source' => 'line-nid',
          'hidden' => TRUE,
          'limit' => $size,
        ],
        // La config table pour organiser la weight
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'line-weight',
        ],
      ],
    );

    // FORM TREE
    $form['mytable']['#tree'] = TRUE;

    // Build the table rows and columns.
    // The first nested level in the render array forms the table row, on which you
    // likely want to set #attributes and #weight.
    // Each child element on the second level represents a table column cell in the
    // respective table row, which are render elements on their own. For single
    // output elements, use the table cell itself for the render element. If a cell
    // should contain multiple elements, simply use nested sub-keys to build the
    // render element structure for drupal_render() as you would everywhere else.


    foreach ($file_content as $key => $line) {
      // TableDrag: Mark the table row as draggable.
      $form['mytable'][$key]['#attributes']['class'][] = 'draggable';
      // TableDrag: Sort the table row according to its existing/configured weight.
      $form['mytable'][$key]['#weight'] = $line['weight'];


      // $parent_line = $fileReader->getParentLine($line['plid']); TODO : inutile pour l'instant
      if (isset($line['identSize']) && $line['identSize'] > 1) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $line['identSize'],
        ];
      }

      // Column with the text.
      $form['mytable'][$key]['text'] = array(
        '#prefix' => !empty($indentation) ? \Drupal::service('renderer')->render($indentation) : '',
        '#type' => 'textfield',
        '#default_value' => $line['new_line'],
        '#ajax' => [
          'callback' => array($this, 'testAjax'),
          'event' => 'change',
          'progress' => array(
            'type' => 'throbber',
            'message' => t('Ajax on textfield...'),
          ),
        ],
        '#suffix' => '<span class="test-message"></span>',
      );


      // TableDrag: Weight column element.
      // NOTE: The tabledrag javascript puts the drag handles inside the first column,
      // then hides the weight column. This means that tabledrag handle will not show
      // if the weight element will be in the first column so place it further as in this example.


      $form['mytable'][$key]['weight'] = array(
        '#type' => 'weight',
        '#title' => t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $line['weight'],
        '#delta' => $size,
        //'#parents' => array('mytable', $key, 'weight'),
        // Classify the weight element for #tabledrag.
        '#attributes' => array('class' => array('line-weight')),
      );


      $form['mytable'][$key]['actions'] = [
        '#type' => 'actions',
        '#attributes' => array(
          'style'=>'margin: 0;'
        ),
      ];

      $form['mytable'][$key]['actions']['add_line'] = [
        '#type'   => 'button',
        '#value'  => $this->t('Add one line'),
        '#name' => 'add_line_'.$key,
        //'#submit' => [ $this, 'addLine'],
        '#ajax'   => [
          'callback' => [$this, 'addLine'],
          'event' => 'mousedown',
          'prevent' => 'click',
          //'wrapper'  => '#mytable', // CHECK THIS ID
        ],
      ];
      $form['mytable'][$key]['actions']['remove_line'] = [
        '#type'   => 'button',
        '#value'  => $this->t('Remove this Line'),
        '#name' => 'remove_line_'.$key,
        '#submit' => ['::removeLine'],
        // Since we are removing a name, don't validate until later.
        '#limit_validation_errors' => [],
        '#ajax'   => [
          'callback' => [$this, 'removeLine'],
          //'wrapper'  => 'mytable', // CHECK THIS ID
        ],
      ];


      $form['mytable'][$key]['parent']['lid'] = [
        '#parents' => ['mytable', $key, 'lid'],
        '#type' => 'hidden',
        '#value' => $line['lid'],
        '#attributes' => [
          'class' => ['line-id'],
        ],
      ];

      $form['mytable'][$key]['parent']['plid'] = [
        '#parents' => ['mytable', $key, 'plid'],
        '#type' => 'hidden',
        '#default_value' => $line['plid'],
        '#attributes' => [
          'class' => ['line-plid'],
        ],
      ];
    }


  }


}