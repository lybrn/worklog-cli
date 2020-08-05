<?php
namespace Lybrnth\Cli;
class Config {
  
  public static function config($config_file_path) {
    
    # if (empty($config_file_path))
    #   $first_trace = @end(debug_backtrace());
    #   $script_dir = dirname($first_trace['file']);
    #   $config_file_path = $script_dir.'/cli.yaml';
    # }

    if (!file_exists($config_file_path))
      throw new Exception("Config file not found: ".$config_file_path);
    
    $yaml = file_get_contents($config_file_path);
    $data = Yaml::decode($yaml);
        
    if (!is_array($data))
      throw new Exception("Could not decode config file: ".$config_file_path);

    if (!array_key_exists('cli',$data))
      throw new Exception("Key 'cli' not found in config file: ".$config_file_path);
      
    $cli = @$data['cli'] ?: null;
    if (!is_array($cli) || empty($cli))
      throw new Exception("No cli commands listed in config file: ".$config_file_path);
    
    return $cli;
  }
  
  
}
