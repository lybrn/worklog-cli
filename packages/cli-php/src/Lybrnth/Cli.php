<?php
namespace Lybrnth;
use Lybrnth\Cli\Config;
use Lybrnth\Cli\Args;
use Lybrnth\Cli\Exception;
use Lybrnth\Cli\Output;
class Cli {
  
  public static function cli($config_file_path) {
    
    try {
      
      $config = Config::config($config_file_path);
      
      $args = Args::args();
      
      $op = array_shift($args);
      
      if (!array_key_exists($op,$config))
        throw new Exception("No such command: $op");

      $op_class = @$config[$op] ?: null;

      if (empty($op_class))
        throw new Exception("No class defined for command: $op");
    
      $op_method = $op_class.'::cli';
      
      if (!is_callable($op_method))
        throw new Exception("Method for '$op' not callable: ".$op_method);
        
      call_user_func($op_method,$args);
      
    } 
    catch(Exception $e) {
      print Output::border_box($e->getMessage());
    }
      
  }
  
}
