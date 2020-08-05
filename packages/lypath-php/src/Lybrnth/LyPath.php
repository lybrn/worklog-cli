<?php
namespace Lybrnth;
class LyPath {
  
  public static function lypath() {
    
    
  }
  public static function package_root() {
    $dir = __DIR__;
    $package = dirname(dirname($dir));
    return $package;
  }
    
}
