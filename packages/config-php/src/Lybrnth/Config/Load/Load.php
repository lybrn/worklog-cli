<?php
namespace Lybrnth\Config\Load;
class Load {
  
  static $static_config_file_contents = [];
  static $static_config_file_data = [];
  static $static_namespace_data = [];
  
  public static function load_namespace($namespace) {
    
    Load::load_namespace_at_path($namespace,'');
    
    Load::rebuild_config_data_for_namespace($namespace);
    
    print_r(array_keys(Load::$static_config_file_contents));  
    print_r(array_keys(Load::$static_config_file_data));  
        
    return Load::get_namespace_data($namespace);
    
  }
  public static function get_namespace_data($namespace) {
    
    $data = @Load::$static_namespace_data[$namespace] ?: [];
    return array_values($data);
      
  }
  private static function rebuild_config_data_for_namespace($namespace) {
    
    $config_files = Load::$static_config_file_data;
    
    foreach($config_files as $config_file_path => $config_file_data) {
      
      $config_file_parts = explode('.',strtr($config_file_path,'/','.'));

      $namespace_data = @$config_file_data[$namespace] ?: null;

      if (empty($namespace_data))
        $namespace_data = @$config_file_data['config'][$namespace] ?: null;

      if (!empty($namespace_data)) 
        Load::$static_namespace_data[$namespace][$config_file_path] = $namespace_data;
    
    }
  }
  private static function load_namespace_at_path($namespace,$load_path) {
    
    print "-- Path: $load_path --\n";
    
    $possible_formats = [
      'json',
      'yaml'
    ];

    $possible_names = [
      "config",
      "$namespace",
      "$namespace.config",
      "config.$namespace",
    ];
    
    // load files
    foreach($possible_names as $possible_name) {      
      foreach($possible_formats as $possible_format) {
      
        $possible_filename = empty($load_path) ?
          "$possible_name.$possible_format" :
          "$load_path/$possible_name.$possible_format";
                      
        print "File: $possible_filename\n";
        
        if (is_file($possible_filename)) {
      
          $config_file_data = Load::parse_config_file_data($possible_filename);
      
        }
          
      }
    }
    
    // load sub directions
    foreach($possible_names as $possible_name) {
    
      // load dirs
      $possible_dirname = empty($load_path) ? 
        "$possible_name" : 
        "$load_path/$possible_name";

      print "Dir: $possible_dirname\n";
      
      if (is_dir($possible_dirname)) {
        
        Load::load_namespace_at_path($namespace,$possible_dirname);
      
      }        
        
    }
    
    print "----\n";
    
  }
  private static function load_config_file_contents($config_file_path) {
    
    $cached = @Load::$static_config_file_contents[$config_file_path] ?: null;
    
    if (empty($cached)) {
    
      $config_file_contents = file_get_contents($config_file_path);
    
      Load::$static_config_file_contents[$config_file_path] = $config_file_contents;
    
      $cached = Load::$static_config_file_contents[$config_file_path];
    
    }
    
    return $cached;
    
  }
  private static function parse_config_file_data($config_file_path) {
    
    $cached = @Load::$static_config_file_data[$config_file_path] ?: null;
    
    if (empty($cached)) {
            
      $config_file_contents = Load::load_config_file_contents($config_file_path);
      
      $format = @end(explode('.',$config_file_path));
      
      if ($format=='json') {
      
        $config_file_data = \Lybrnth\Config\Format\Json::json_to_array($config_file_contents);
      
      }
      
      else if ($format=='yaml') {
      
        $config_file_data = \Lybrnth\Config\Format\Yaml::yaml_to_array($config_file_contents);
      
      }
      
      else {
      
        throw new Config/Exception("Format not supported: {$format}");
      
      }

      Load::$static_config_file_data[$config_file_path] = $config_file_data;
      
      $cached = Load::$static_config_file_data[$config_file_path];
      
    }
    
    return $cached;    
    
  }
  
}
