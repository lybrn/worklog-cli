<?php
namespace Lybrnth\Config;
class CLI extends \NDPToolsCLI\CLI {
  //
  // Usage: cli ly-config-load [namespace] 
  //
  public static function op_ly_config_load($args) {

    try {
      $namespace = @$args[0] ?: null;
      $data = Load\Load::load_namespace($namespace);
      $output = $data;
      print Format\Yaml::array_to_yaml($output)."\n";
    }
    catch(\Exception $e) {
      print \NDPToolsCLI\Output::border_box($e->getMessage());
    }

  }  
  //
  // Usage: cli ly-config-read [namespace] [key] 
  //
  public static function op_ly_config_read($args) {

    try {
      $namespace = @$args[0] ?: null;
      $key = @$args[1] ?: null;
      
      $data = Read\Key::read_key($namespace,$key);
      $output = $data;
      print Format\Yaml::array_to_yaml($output)."\n";
    }
    catch(\Exception $e) {
      print \NDPToolsCLI\Output::border_box($e->getMessage());
    }

  }    
}
