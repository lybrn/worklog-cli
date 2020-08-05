<?php
namespace Lybrnth\LyPath\Tools;
class Json {
  
  public static function decode($string) {
    return json_decode($string);
  }
  public static function encode($array) {
    return json_encode($array,JSON_PRETTY_PRINT);
  }

}
