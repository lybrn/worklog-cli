<?php
namespace Lybrnth\LyPath\Tools;
class XmlToArray {
  
  public static function decode($xml_string) {

    // normalize xml string
    $xml_string = strtr($xml_string,array('<![CDATA['=>'',']]>'=>''));
    $xml_string = strtr($xml_string,array('&'=>'&amp;'));
    $xml_string = strtr($xml_string,array('&amp;amp;'=>'&amp;','&amp;lt;'=>'&lt;','&amp;gt;'=>'&gt;'));
    $xml_string = "<root>".$xml_string."</root>";
    // parse xml string into an object
    $xml_obj = simplexml_load_string($xml_string) ?: null;
    print_r($xml_obj);
    if (empty($xml_obj)) return null;
    // turn all objects into associative arrays by running obj through json encode and decode
    $xml_json = json_encode($xml_obj);
    $xml_array = json_decode($xml_json,TRUE);
    // return xml array
    return $xml_array;
  
  }

}
