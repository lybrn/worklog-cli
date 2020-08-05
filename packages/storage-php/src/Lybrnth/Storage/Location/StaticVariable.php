<?php
namespace Lybrnth\Storage\Location;
class StaticVariable {

  private static $stack = [];
  
  public static function write($data) {

    array_push(StaticVariable::$stack,$data);
    
  }
  public static function read() {
    
    return StaticVariable::$stack;
    
  }

}
