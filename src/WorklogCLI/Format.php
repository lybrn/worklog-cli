<?php
namespace WorklogCLI;
class Format {

  public static function normalize_key($string) {
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    $normalized = preg_replace('/[^a-z0-9]/i','',$normalized);
    $normalized = strtolower($normalized);
    return $normalized;
  }
  public static function format_hours($hours) {
    $hours = number_format($hours,2);
    return $hours;
  }
  public static function format_cost($cost,$options=array()) {
    // $cost = ceil($cost * 100.0) / 100.0;
    if (!is_numeric($cost)) return '';
    $cost = number_format($cost,2);
    if (empty($options['comma'])) $cost = strtr($cost,array(','=>''));
    if (!empty($options['symbol'])) $cost = $options['symbol'].$cost;
    return $cost;
  }
}
