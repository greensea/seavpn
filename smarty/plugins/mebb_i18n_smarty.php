<?php
/**
 * ------------------------------------------------------------------------- *
 * This library is free software; you can redistribute it and/or             *
 * modify it under the terms of the GNU Lesser General Public                *
 * License as published by the Free Software Foundation; either              *
 * version 2.1 of the License, or (at your option) any later version.        *
 *                                                                           *
 * This library is distributed in the hope that it will be useful,           *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of            *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU         *
 * Lesser General Public License for more details.                           *
 *                                                                           *
 * You should have received a copy of the GNU Lesser General Public          *
 * License along with this library; if not, write to the Free Software       *
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA *
 * ------------------------------------------------------------------------- *
 *
 * @package mebb-lib-i18n-smarty
 * @author Karlheinz Toni <karlheinz.toni@gmail.com>
 * @link http://code.google.com/p/smarty-gettext/
 * @copyright 2012 Karlheinz Toni
 */
namespace mebb\lib\i18n\smarty;

function compile($smarty, $files = null, &$info = array()){
  if(!$files) $files = $smarty->getTemplateDir();
  if(!is_array($files)) $files = array($files);

  $info['errors'] = array();

  $extension = (defined('MEBB_TEMPLATE_EXTENSION')?MEBB_TEMPLATE_EXTENSION:'tpl');
  $files_to_compile = array();
  $compiled = array();

  foreach($files as &$file){
    if($file = realpath($file)){
      if(is_dir($file)){
        $tmp = \mebb\lib\functions\glob_recursive($file.DIRECTORY_SEPARATOR.'*.'.$extension);
        foreach($tmp as $file){
          $files_to_compile[] = $file;
        }
      }else if(is_file($file)){
        $files_to_compile[] = $file;
      }
    }
  }
  foreach($files_to_compile as &$file){
    try{
      $template = $smarty->createTemplate($file, $smarty);
      if($template->mustCompile()){
        $source = $template->compiler->compileTemplate($template);  
      }else{
        $source = file_get_contents($file);
      }
      $compiled[] = array(
        'source' => $source,
        'file_original' => $file,
        'file_compiled' => $template->compiled->filepath
      );
    }catch(\Exception $e){
      $info['errors'][] = array(
        'exception' => $e,
        'file' => $file,
        'message' => $e->getMessage()
      );
      if(defined('MEBB_IGNORE_ERRORS')){
        if(!MEBB_IGNORE_ERRORS){
          throw $e;
        }
      }
    }
  }
  return $compiled;
}

function save($smarty, $compiled_templates, $file = null, $empty_file = true){
  if(!$file) $file = tempnam(sys_get_temp_dir(), 'i18n_'); 
  if($empty_file && file_exists($file)) unlink($file);

  $handle = fopen($file, "w");
  foreach($compiled_templates as $source){
    fwrite($handle, $source['source']);
  }
  fclose($handle);

  return $file;
}

function save_individual($smarty, $compiled_templates, $directory = null){
  if(!$directory) $directory = sys_get_temp_dir();
  $directory = rtrim(rtrim($directory, '/'),'\\').DIRECTORY_SEPARATOR.'i18n_'.rand(pow(10,6),pow(10,7)).DIRECTORY_SEPARATOR;
  if(!file_exists($directory)) mkdir($directory, 0777, true);
  $template_directories = $smarty->getTemplateDir();

  foreach($template_directories as &$template_directory){
    $template_directory = realpath($template_directory); 
  }

  foreach($compiled_templates as $source){
    $file = $source['file_original']; 
    foreach($template_directories as $template_directory){
      $file = str_replace($template_directory, '', $file);
    }
    $file = ltrim($file, DIRECTORY_SEPARATOR);
    $file = str_replace(DIRECTORY_SEPARATOR, '-', $file); 
    file_put_contents($directory.$file, $source['source']);
  }

  return $directory;
}

if (!function_exists('\\mebb\lib\\functions\\glob_recursive')){ 
  if(file_exists(BASE_PATH_LIB.'mebb/mebb_functions_glob_recursive.php')){
    include_once BASE_PATH_LIB.'mebb/mebb_functions_glob_recursive.php';
  }
}

?>
