<?php
namespace Lybrnth\Config\Format;
class Json {
  public static function json_to_array($json_string) {
    return json_decode($json_string,TRUE);
  }
  public static function array_to_json($associative_array) {
    return json_encode($associative_array,JSON_PRETTY_PRINT);
  }
}
