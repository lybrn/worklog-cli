<?php
namespace WorklogCLI;
class Format {

  public static function normalize_key($string,$keep='') {
    if (!is_string($string)) $string = "$string";
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    $normalized = preg_replace('/[^a-z0-9'.preg_quote($keep).']/i','',$normalized);
    $normalized = strtolower($normalized);
    return $normalized;
  }
  public static function normalize_array_keys($array) {
    if (!is_array($array)) return null;
    $array_normalized = [];
    foreach($array as $k=>$v) {
      $k_normalized = Format::normalize_key($k);
      $array_normalized[ $k_normalized ] = $v;
    }
    return $array_normalized;
  }
  public static function array_shape_rows($rows,$type_keys) {
    
    // break srting on | if this is a string
    if (is_string($type_keys)) 
      $type_keys = explode('|',$type_keys);
  
    // build key value type array
    $type = [];
    if (is_array($type_keys)) foreach($type_keys as $k=>$v) {
      if (is_numeric($k) && is_string($v) && !empty($v)) {
        $v_normalized = Format::normalize_key($v);
        $type[ $v_normalized ] = $v;
      } else if (!is_numeric($k) && !empty($v)){
        $k_normalized = Format::normalize_key($k);
        $type[ $k_normalized ] = $v;
      }
    }
    
    // shape 
    $rows_shaped = [];
    foreach($rows as $row) {
      $row_shaped = [];
      $row_normalized = Format::normalize_array_keys($row);      
      foreach($type as $type_key_normalized => $type_key_value) {
        $row_shaped[ $type_key_value ] = array_key_exists($type_key_normalized,$row_normalized) ?
          $row_normalized[ $type_key_normalized ] : 
          null;
      }
      $rows_shaped[] = $row_shaped;
    }
    
    return $rows_shaped;
  }  
  public static function array_keys_remove_prefix($array,$prefixes) {
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
    }
    return $array_unprefixed;
  }  
  public static function format_hours($hours) {
    $hours = number_format($hours,2);
    return $hours;
  }
  public static function format_cost($cost,$options=array()) {
    // $cost = ceil($cost * 100.0) / 100.0;
    $cost = trim($cost,'$ ');
    if (!is_numeric($cost)) return '';
    $cost = number_format($cost,2);
    if (empty($options['comma'])) $cost = strtr($cost,array(','=>''));
    if (!empty($options['symbol'])) $cost = $options['symbol'].$cost;
    return $cost;
  }
}
