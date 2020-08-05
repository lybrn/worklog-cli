<?php
namespace Lybrnth;
use Lybrnth\Mdon\Exception;
use Lybrnth\Mdon\Output;
use Lybrnth\Mdon\Parse;
use Lybrnth\Mdon\Parse2;
class Mdon {
  
  public static function decode2($filepaths) {
    $filepath = current($filepaths);
    $markdown = file_get_contents($filepath);
    return Parse2::decode($markdown);
  }

  public static function decode_files_get_stack($filepaths) {
    Parse::parse_files($filepaths);
    return Parse::$storage  ;
  }
  public static function decode_files($filepaths) {
    return Parse::parse_files($filepaths);
  }
  public static function decode_file($filepaths) {
    return Parse::parse_file($filepaths);
  }
  public static function decode_string($markdown) {
    return Parse::parse($filepaths);
  }
  
}
