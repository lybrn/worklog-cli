<?php
namespace Lybrnth\Mdon\Dev;
class Console {
  
    private static $flag = TRUE;
    
    public static function debug($set=null) {
      if (!is_null($set)) Console::$flag = (bool) $set;
      return Console::$flag;
    }
    public static function is_cli() {
      return (php_sapi_name() === 'cli');
    }
    public static function log($output="") {
      if (!Console::debug()) return;
      if (!Console::is_cli()) return;
      $caller = @next(debug_backtrace());
      $method = $caller['class'].'::'.$caller['function'];
      if (is_array($output) && empty($output)) $output = "";
      if (is_array($output)) $output = print_r($output,TRUE);
      print trim($method.': '.$output)."\n";
    }
    
}
