<?php
namespace Lybrnth\Cli;
class Args {
  
  public static function args() {
    $args = $_SERVER['argv'];
    array_shift($args);
    return $args;
  }
  
}
