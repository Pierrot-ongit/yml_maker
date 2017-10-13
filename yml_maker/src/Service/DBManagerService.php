<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Service\DBManagerService
 */

namespace Drupal\yml_maker\Service;


use Drupal\Core\Database\Database;


/**
 * Defines a service for managing registered YAML files for the application.
 */
class DBManagerService {

  protected $db;

  /**
   * Constructor
   */
  public function __construct() {
      //$this->db = \Drupal::service('database');
  }


  public function getListFiles(){
    $select = Database::getConnection()->select('yml_maker', 'y');
    //$select->addField('y', 'file_name');
    $select->fields('y', ['id', 'file_name', 'file_path', 'file_description']);
    $entries = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    return $entries;

  }


  /**
 * Get one file infos
 *
 */
  public function getFileByName($name) {

    $select = Database::getConnection()->select('yml_maker', 'y');
    $select->condition('file_name', $name);
    $select->addField('y', 'file_name');
    $results = $select->execute()->fetchAssoc();
    return $results;
  }

  /**
   * Get one file infos
   *
   */
  public function getOneFileByID($id) {

    $select = Database::getConnection()->select('yml_maker', 'y');
    $select->condition('id', $id);
    //$select->addField('y', 'file_name');
    $select->fields('y', ['id', 'file_name', 'file_path', 'file_description']);
    $results = $select->execute()->fetchAssoc();
    return $results;
  }

  /**
   * Get one file infos
   *
   */
  public function insertOneFile($file) {

    $description = '';
    $name = $file['file_name'];
    $path = $file['file_path'];

    if(!empty($file['file_description'])){
      $description = $file['file_description'];
    }


    $query = \Drupal::database()->insert('yml_maker')
      ->fields([
        'file_name' => $file['file_name'],
        'file_path' => $file['file_path'],
        'file_description' => $description,
      ])
      ->execute();

  }

  /**
   * Get one file infos
   *
   */
  public function updateOneFile($name, $file) {

    $description = '';
    if(!empty($file['file_description'])){
      $description = $file['file_description'];
    }

    $update = Database::getConnection()->update('yml_maker');
    $update->condition('file_name', $name);
    $update->fields([
      'file_name' => $file['file_name'],
      'file_path' => $file['file_path'],
      'file_description' => $description,
    ]);
    $update->execute();
  }

  /**
   * Deletes one file by his name.
   *
   */
  public function delFile($id){
    $delete = Database::getConnection()->delete('yml_maker');
    $delete->condition('id', $id);
    $delete->execute();
  }


}