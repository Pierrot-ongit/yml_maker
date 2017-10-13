<?php
/**
 * @file
 * Contains \Drupal\yml_maker\Service\FileReaderService
 */

namespace Drupal\yml_maker\Service;

use Drupal\Core\File;

class BrowserService {

  const YML_EXT = '.yml';
  const BASE_ROOT = '/home/pierre/www';

  public function __construct() {
  }

  public function assemblePath($path){
    $assembler = self::BASE_ROOT.$GLOBALS['base_path'].$path;
    return $assembler;
  }

  public function getYMLFiles($path){
    $path = $this->assemblePath($path);
    kint($path);
    if(scandir($path) == FALSE){
      return;
    }
    $yml_files = [];
    $list = scandir($path);
    foreach ($list as $key => $value) {
      if($this->checkFiles($value)){
        $yml_files[] = $value;
      }
    }
    return $yml_files;

  }

  public function checkFiles($file){
    // TODO : Check qu'il s'agit d'un fichier YML, et que le nom n'est pas bannie.
    if(!strpos($file, '.yml', -4 || is_dir($file))){
      return FALSE;
    }
    return TRUE;
  }



}
