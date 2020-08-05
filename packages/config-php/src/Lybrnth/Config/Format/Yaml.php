<?php
namespace Lybrnth\Config\Format;
use \Symfony\Component\Yaml\Yaml as SymfonyYaml;
class Yaml {
  public static function yaml_to_array($yaml_string) {
    return SymfonyYaml::parse($yaml_string);
  }
  public static function array_to_yaml($associative_array) {
    return SymfonyYaml::dump($associative_array);
  }
}
