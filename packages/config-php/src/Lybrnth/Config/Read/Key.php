<?php
namespace Lybrnth\Config\Read;
class Key {
  public static function read_key($namespace,$key) {
    
    $data = \Lybrnth\Config\Load\Load::get_namespace_data($namespace);
    $items = [];
    foreach($data as $config_key=>$config_value) {
      if (array_key_exists($key,$config_value)) {
        $items[] = $config_value;
      }
    } 
    
    return $data;
    
  }
}
  
