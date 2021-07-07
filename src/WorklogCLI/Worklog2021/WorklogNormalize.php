<?php
namespace WorklogCLI\Worklog2021;
class WorklogNormalize {

  public static function normalize_key($string,$keep='',$reduceto=FALSE) {
    if (!is_string($string)) $string = "$string";
    $keep = preg_quote($keep);
    $keep = strtr($keep,['/'=>'\/']);
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    // remove all extra chars excluding keep chars
    $normalized = preg_replace('/[^a-z0-9'.$keep.']/i','',$normalized);
    // turn all keep chars into '-'
    if (!empty($reduceto)) $normalized = preg_replace('/[^a-z0-9]+/i',$reduceto,$normalized);
    // remove leading and trailing dashes
    $normalized = trim($normalized,'-');
    $normalized = strtolower($normalized);
    return $normalized;
  }
  
  public static function normalize_array_keys($array,$keep='',$reduceto=FALSE,$recursive=FALSE) {
    if (!is_array($array)) return null;
    $array_normalized = [];
    foreach($array as $k=>$v) {
      $k_normalized = WorklogNormalize::normalize_key($k,$keep,$reduceto);
      $array_normalized[ $k_normalized ] = $v;
      if (is_array($v) && $recursive) 
        $array_normalized[ $k_normalized ] = WorklogNormalize::normalize_array_keys($v,$keep,$reduceto,$recursive);
    }
    return $array_normalized;
  }
  public static function array_keys_remove_prefix($array,$prefixes,$recursive=FALSE) {
    if (!is_array($array)) return null;
    if (empty($prefixes)) return $array;
    if (!is_array($prefixes)) $prefixes = [ $prefixes ];
    $array_unprefixed = [];
    foreach($array as $k=>$v) {
      $k_unprefixed = $k;
      $previous = $k_unprefixed;
      foreach($prefixes as $prefix) {
        $k_unprefixed = preg_replace("/^".preg_quote($prefix)."/",'',$k_unprefixed);
        if ($k_unprefixed!=$previous) break; // prevent removing more than on prefix
        $previous = $k_unprefixed;
      }
      $array_unprefixed[ $k_unprefixed ] = $v;
      if (is_array($v) && $recursive) 
        $array_unprefixed[ $k_unprefixed ] = WorklogNormalize::array_keys_remove_prefix($v,$prefixes,$recursive);
      
    }
    return $array_unprefixed;
  }  
  public static function array_keys_add_prefix($array,$prefix,$recursive=FALSE) {
    if (!is_array($array)) return null;
    if (empty($prefix)) return $array;
    if (!is_string($prefix)) return $array;
    $array_prefixed = [];
    $prefix = trim(trim($prefix),'_-');
    foreach($array as $k=>$v) {
      $k_prefixed = $k;
      $k_prefixed = preg_replace("/^".preg_quote($prefix)."/",'',$k_prefixed);
      $k_prefixed = $prefix.'_'.$k_prefixed;
      $array_prefixed[ $k_prefixed ] = $v;      
    }
    return $array_prefixed;
  }    
}
