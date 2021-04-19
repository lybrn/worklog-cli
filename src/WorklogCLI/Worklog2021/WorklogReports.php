<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
use WorklogCLI\Format;  
class WorklogReports {
  
  public static function filter($entries,$fields) {
    
    $filtered = [];
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      foreach($fields as $name=>$value) {
        if (!array_key_exists($name,$entry)) continue 2;
        if ($entry[$name]=='') continue 2;
        if ($entry[$name]==null) continue 2;
      }
      $filtered[] = $entry;
    }
    return $filtered;
  }
  public static function report($entries,$fields) {

    $fields = WorklogReports::fields($fields);
    
    $rows = array();
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      $row = [];
      foreach($fields as $field) {
        $report_key = $field['as'];
        $field_key = $field['column'];
        $report_value = $entry[ $field_key ];
        if (is_array($report_value)) $report_value = implode(' ',$report_value);
        $row[$report_key] = $report_value;
      }
      $sortkey = implode('-',$row);
      $rows[ $sortkey ] = $row;
    }
    
    ksort($rows);
    
    $rows = array_values($rows);
    
    
    return $rows;

  }    
  public static function fields($fields) {
    $return = [];
    foreach($fields as $key => $value) {
      $key_is_numeric = is_numeric($key);
      $key_is_nonempty_string = is_string($key) && ($key!='');;
      $value_is_numeric = is_numeric($value);
      $value_is_nonempty_string = is_string($value) && ($value!='');
      if ($key_is_numeric && $value_is_nonempty_string) {
        $field = [];
        $field['column'] = $value;
        $field['as'] = $value;
        $return[] = $field;
      }
      if (!$key_is_numeric && $key_is_nonempty_string && $value_is_nonempty_string) {
        $field = [];
        $field['column'] = $value;
        $field['as'] = $key;
        $return[] = $field;
      }
    }
    return $return;
  }
  public static function sort($report,$sortcols) {
  
    $sortable = [];
    foreach($report as $i=>$row) {
      $sortkey = [];
      foreach($sortcols as $sortcol) {
        $sortkey[] = $row[$sortcol];
      }
      $sortkey[] = $i;
      $sortkey = implode('-',$sortkey);
      $sortable[ $sortkey ] = $row;
    }
    
    krsort($sortable);
    
    return array_values($sortable);
    
  }
}

  
