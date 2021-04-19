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
      $k_normalized = Format::normalize_key($k,$keep,$reduceto);
      $array_normalized[ $k_normalized ] = $v;
      if (is_array($v) && $recursive) 
        $array_normalized[ $k_normalized ] = Format::normalize_array_keys($v,$keep,$reduceto,$recursive);
    }
    return $array_normalized;
  }
  
}
