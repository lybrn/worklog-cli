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
  public static function array_keys_remove_prefix($array,$prefix) {
    if (!is_array($array)) return null;
    $array_unprefixed = [];
    foreach($array as $k=>$v) {
      $k_unprefixed = preg_replace("/^".preg_quote($prefix)."/",'',$k);
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
