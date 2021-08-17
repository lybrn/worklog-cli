<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\Worklog2021\WorklogNormalize;
use WorklogCLI\Worklog2021\WorklogArgs;
use Lybrnth\Mdon;
class WorklogDb {

  public static function db($key) {
    
    return WorklogDb::get_note_data_by_keys($key);
    
  }
  public static function get_note_data_by_keys($keys) {
    
    // note data
    $notedata = WorklogDb::get_note_data();

    // normalized
    static $normalized = [];
    if (empty($normalized)) {        
      // normalized 
      foreach($notedata as $k=>$v) {
        $k_normal = WorklogNormalize::normalize_key($k);
        $normalized[$k_normal] = $k;
      }
    } 

    static $cached = [];
    $args_key = md5(print_r(func_get_args(),TRUE));
    if (empty($cached[$args_key])) {
      
      if (empty($keys)) return [];
      if (!is_array($keys)) $keys = [ $keys ];
      
      // notedata keys
      $normalized_keys = array_keys($normalized);
      
      // return data
      $return = [];
      foreach($keys as $key) {
        $key_normal = WorklogNormalize::normalize_key($key,'*');
        if (preg_match('/\*/i',$key_normal)) {
          $pattern = '/^'.strtr($key_normal,[ '*' => '.*' ]).'$/';
          foreach($normalized_keys as $matchkey) {
            if (preg_match($pattern,$matchkey) && !empty($normalized[$matchkey])) {
              $return[ $normalized[$matchkey] ] = $notedata[ $normalized[$matchkey] ];
            }
          }
        }
        else if (!empty($normalized[$key_normal]))
          $return[ $normalized[$key_normal] ] = $notedata[ $normalized[$key_normal] ];
        
      }
    
      $cached[$args_key] = $return;
    }
    // return filtered
    return $cached[$args_key];

  }  
  public static function get_note_data() {
    
    static $notedata = null;
    if (is_null($notedata)) {
      
      // get worklog file paths
      $worklog_file_paths = WorklogCli::get_worklog_filepaths();

      // parse and filter worklog
      $notedata = WorklogDb::_get_note_data($worklog_file_paths);
    
    }
    // return
    return $notedata;
    
  }  
  public static function _get_note_data($filepaths,$options=array()) {
    
    $parsed = Mdon::decode_files($filepaths);
    $notedata = array();
    
    foreach($parsed as $day) {
      if (is_array($day['rows'])) 
      foreach($day['rows'] as $category) {
        if (!empty($category['rows']))
        foreach($category['rows'] as $tasks) {
          if (!empty($tasks['rows']))
          foreach($tasks['rows'] as $notes) {
                
            if ($notes['style']=='*' || $notes['style']=='.') {
              $pair = explode(':',$notes['text'],2);
              $key = trim($pair[0]);
              if (!empty($notes['rows'])) {
                if (!is_array($notedata[ $key ]))
                  $notedata[ $key ] = [];
                foreach($notes['rows'] as $noterow) {
                  $subpair = explode(':',$noterow['text'],2);
                  $subkey = trim($subpair[0]);
                  $subvalue = trim($subpair[1]);
                  if (!empty($subvalue)) {
                    $notedata[ $key ][ $subkey ] = $subvalue;
                  } else if (!empty($subkey) && empty($noterow['rows'])) {
                    $notedata[ $key ][] = $subkey;
                  }
                  if (!empty($noterow['rows'])) {
                    if (!is_array($notedata[ $key ][ $subkey ]))
                      $notedata[ $key ][ $subkey ] = [];
                    foreach($noterow['rows'] as $subnoterow) {
                      $subsubpair = explode(':',$subnoterow['text'],2);
                      $subsubkey = trim($subsubpair[0]);
                      $subsubvalue = trim($subsubpair[1]);
                      if (!empty($subsubvalue)) {
                        $notedata[ $key ][ $subkey ][ $subsubkey ] = $subsubvalue;                    
                      } else if (!empty($subsubkey)) {
                        $notedata[ $key ][ $subkey ][] = $subsubkey;                    
                      }
                      //$notedata[ $subkey ][ $subsubkey ] = $subsubvalue;                    
                    }    
                  }
                }
                if (empty($notedata[ $key ])) unset($notedata[ $key ]);
              }
            }
          }
        }
      }
    }
    ksort($notedata);
    return $notedata;        
                        
  }    
}
