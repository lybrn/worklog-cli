<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\Format;
class WorklogCheck {
  
  public static function require_unique($field_data,$rows) {

    $rows = WorklogAugment::join_unique($field_data,$rows);
    reset($rows);
    
    foreach($rows as &$row) {
      $sortkey = key($row);
      $fieldkey = $field_data['key'];
      $fieldvalue = $row[ $sortkey ][ $fieldkey ];
      
      if (is_array($fieldvalue)) {
        $is_unique = count($fieldvalue) == 1;
        if ($is_unique) {
          $row[ $sortkey ][ $fieldkey ] = '';
        } else {
          $row[ $sortkey ][ $fieldkey ] = 'Duplicate date';
        }  
      }
      
      
    }
    
    return $rows;
    
  }
  public static function require_timestamp($field_data,$rows) {

    foreach($rows as &$row) {
      $sortkey = key($row);
      $fieldkey = $field_data['key'];
      $fieldvalue = $row[ $sortkey ][ $fieldkey ];
      $is_timestamp = is_numeric($fieldvalue) &&  $fieldvalue > 1;        
      if ($is_timestamp) {
        $row[ $sortkey ][ $fieldkey ] = '';
      } else {
        $row[ $sortkey ][ $fieldkey ] = 'Invalid date';
      }        
    }

    return $rows;

  }  
 
}
  
