<?php
namespace WorklogCLI;
class GenericObj {
  
  // Generic = 
  // [ position, key, value ]
  
  function make_generic($assoc) {
    $generic = array();
    $count = 0;
    foreach($assoc as $k=>$v) {
      $pos = $count;
      $key = $k;
      $value = is_array($value) ? $v : array($v);
      $value = GenericObj::make_generic($value);
      $count++;
      $generic[] = array($pos,$key,$value);
    }
    return $generic;
  }
  
  function make_assoc($generic) {
    $assoc = array();
    foreach($generic as $obj) {
      $pos = $obj[0];
      $key = $obj[1];
      $value = $obj[2];
      $assoc[$pos] = array()
    }    
  }
  
}

. Name: Tim
. Age: 5