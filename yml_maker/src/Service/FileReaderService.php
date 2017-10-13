<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Service\FileReaderService
 */

namespace Drupal\yml_maker\Service;

use Drupal\Core\File;



class FileReaderService {

  public $fid;
  public $file_infos;
  public $file;
  public $fullNamePath;
  public $newFile;
  public $path;

  const YML_EXT = '.yml';
  const BASE_ROOT = '/home/pierre/www';


  /**
   * Constructor
   */
  public function __construct() { }

  public function assembleNamePath($files_infos){
    $assembler = self::BASE_ROOT.$GLOBALS['base_path'].$files_infos['file_path'].$files_infos['file_name'].self::YML_EXT;
      return $assembler;
  }

  public function loadFileContent($fid, $files_infos){
    $this->fid = $fid;
    $this->file_infos = $files_infos;

    $this->fullNamePath = $this->assembleNamePath($files_infos);
    $file_raw = file($this->fullNamePath);

    $file = [];
    foreach ($file_raw as $key => $line) {
      $line = rtrim($line);
      $file[$key] = [
        'old_line' => $line,
        'new_line' => ltrim($line),
        'weight' => $key,
        'indentSize' => strlen($line) - strlen(ltrim($line)),
        //'lid' => $key + 1,
      ];
    }

    //$this->prepareFileContent();
    $this->file = $file;
    return $this->file;
  }

   // TODO : LEGACY
  public function prepareFileContent(){
    $file = $this->file;
    if(empty($file)){
      return;
    }

    foreach ($file as $key=>$line){
      $line = rtrim($line);
      $file[$key] = array(
        'old_line' => $line,
        'new_line' => ltrim($line),
        'weight' => $key,
        'identSize' => strlen($line)-strlen(ltrim($line)),
        //'lid' => $key + 1,
      );
/*
      $array_to_search = array_slice($file, 0, $key);
      $array_to_search = array_reverse($array_to_search);
      $parent_line = $this->searchForPlid($file[$key]['identSize'],$array_to_search);

      if(is_array($parent_line)){
        $plid = $parent_line['lid'];
        $file[$key]['plid'] = $plid;
      }
      else{
        $file[$key]['plid'] = 1;
      }

      if($this->isValideLine($file[$key]['new_line']) === FALSE){
        $file[$key]['isComment'] = TRUE;
        $file[$key]['plid'] = $file[$key-1]['lid'];
        $file[$key]['identSize'] = $file[$key-1]['identSize'] +2;
        //dump('INTERCEPTION EMPTY LINE OR COMMENT LINE');
        //dump($file[$key]);
      }
*/
    }
    $this->file = $file;
  }

  // TODO : LEGACY
  public function searchForPlid($indent, $array) {

    foreach ($array as $key => $val) {
      if($indent > $val['identSize'] && !$val['isComment']){
        return $val;
      }
    }
    return NULL;
  }

  // TODO : LEGACY
  public function getParentLine($plid){
    $file = $this->file;
    if(empty($file)){
      return NULL;
    }

    foreach ($file as $key => $val) {
      if ($val['lid'] === $plid) {
        return $val;
      }
    }
    return NULL;
  }

  // TODO : LEGACY
  /**
   * Function checking if the line given is empty or is a comment line
   *
   * @param $line
   */
  public function isValideLine($line){
    //TODO : REGEX sur # et check if empty
    // && !preg_match("/^\#/",$line)
    if(empty($line)){
        return FALSE;
      }
      else{
        return TRUE;
      }
  }


  public function writeFile($values){


    // TODO : On récupère les valeurs du formulaire mise en forme de tableau,
    // TODO :

    $indent = '  ';
    $file = array();
    foreach ($values as $key=>$value){
      $indentSize = $value[$key]['indentSize'];
      $lid = $value[$key]['lid'];
      $weight = $value[$key]['weight'];

      // http://php.net/manual/en/function.str-pad.php
      //str_pad('Alien', 10, "-=", STR_PAD_LEFT);  // produces "-=-=-Alien"
      $pad_length = strlen($file[$lid]['text']) + $file[$lid]['identSize'];
      $file[$lid]['text'] = str_pad($value[$key]['text'], $pad_length, "  ", STR_PAD_LEFT);
      $file[$lid]['text'] =  $file[$lid]['text']."\n";

    }

    // TODO : faire une énorme string qu'on append au fur et à mesure de la boucle, ou bien la construire en dehors de la 1ère boucle ?
    // TODO : Cela aurait plus de sens, et plus facilement manipulable et découpable en deux fonctions distances si on ne fait que de construire les lignes de textes sans les assembler directement.

    $texte_file = "";
    foreach ($file as $lid=>$line){
      $texte_file .= $line['text'];
    }


    // TODO : A faire en dernier

    //$file = fopen($this->file, "w"); // Pas prete.
    //fwrite($file, $texte_file);
    //fclose($file);
  }



}