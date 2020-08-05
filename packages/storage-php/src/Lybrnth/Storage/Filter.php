<?php
namespace Lybrnth\Storage;
class Filter {
  
  public static function filter($data,$match) {
    
    if (!is_array($data))
      throw new Exception("Data must be an array: ".$data);
      
    if (!is_array($match))
      throw new Exception("Match must be an array: ".$match);
      
    $filtered = [];
    foreach($data as $item) {
      $match_results = [];
      foreach($match as $match_key=>$match_value) {
        $match_results[$match_key] = FALSE;
        if (array_key_exists($match_key,$item)) 
          if ($item[$match_key] == $match_value)
            $match_results[$match_key] = TRUE;
      }
      if (in_array(TRUE,$match_results) && !in_array(FALSE,$match_results)) {
        $filtered[] = $item;
      }
    }

    return $filtered;
    
  }
  
}
