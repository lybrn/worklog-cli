<?php
namespace Lybrnth\LyPath\Cli;
use Lybrnth\Lypath;
use Lybrnth\Lypath\Tools\Json;
use Lybrnth\Lypath\Tools\Yaml;
use Lybrnth\Lypath\Tools\XmlToArray;
class CliLyPath {
  
  public static function cli($args) {
  
    $package_root = LyPath::package_root();
    
    $php_file = require $package_root.'/parse/house.php';
    $xml_file = file_get_contents($package_root.'/parse/house.xml');
    $xml_array = XmlToArray::decode($xml_file);
    
    print "LYPATH: $package_root\n";
    print_r($xml_array);

  }
  
}
