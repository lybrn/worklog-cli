<?php
namespace Lybrnth;
use \Lybrnth\Storage\Location\StaticVariable;
use \Lybrnth\Storage\Filter;
class Storage {
  
  public static function write($data) {
    
    StaticVariable::write($data);
    
  }
  public static function read($match) {
    
    $data = StaticVariable::read();
    $filtered = Filter::filter($data,$match);
    return $filtered;
    
  }
}
