<?php
namespace Lybrnth\LyPath\Tools;
use \Symfony\Component\Yaml\Yaml as SymfonyYaml;
class Yaml {
  
  public static function decode($string) {
    return SymfonyYaml::parse($string);
  }
  public static function encode($array) {
    return SymfonyYaml::dump($array,4,4);
  }

}
