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
  public static function extract_merged($entries,$field) {
    
    $extracted = array();
    
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      
      if (!array_key_exists($field,$entry)) continue;
      
      $field_value = $entry[$field];
      $field_value_is_array = is_array($field_value);
      $field_value_is_scalar = is_scalar($field_value);
      
      if ($field_value_is_scalar) {
        $extracted[] = $field_value;
      } 
      else if ($field_value_is_array) {
        $extracted = array_merge($extracted,$field_value);
      }
      
    }
    
    return $extracted;

  }
  public static function reduce($entries,$fields) {
    
    $keep_columns = [];
    $parent_columns = [];
    foreach($fields as $field) {
      $parent_key = $field['parent'];
      $field_key = $field['column'];
      if (!empty($parent_key)) $parent_columns[$parent_key] = $parent_key;
      if (!empty($parent_key)) $keep_columns[$parent_key] = $parent_key;
      if (!empty($field_key)) $keep_columns[$field_key] = $field_key;
    }
    
    $filtered = [];
    foreach($entries as $i=>$entry) {
      $filtered_row = [];
      foreach($keep_columns as $keep_column) {
        $filtered_row[$keep_column] = $entry[$keep_column];
      }
      $filtered[$i]=$filtered_row;
    }
    
    foreach($parent_columns as $parent_column) {
      $filtered = WorklogReports::flatten($filtered,$parent_column);
    }
    return $filtered;
    
  }
  public static function flatten($entries,$subkey) {
    $flattened = [];
    
    foreach($entries as $entry) {
      $sublist = @$entry[$subkey] ?: [];
      if (!empty($sublist) && is_array($sublist)) {
        foreach($sublist as $subitem) {
          $row = $entry;
          unset($row[$subkey]);
          foreach($subitem as $k => $v) {
            if (!array_key_exists($k,$row) || is_null($row[$k]) || $row[$k]=='') 
              $row[$k] = $v;
          }
          $flattened[] = $row;
        }
      } else {
        $row = $entry; 
        unset($row[$subkey]);
        $flattened[] = $row;
      } 
    }
    
    return $flattened;
  }
  public static function report($entries,$fields) {

    $fields = WorklogReports::fields($fields);
    
    $entries = WorklogReports::reduce($entries,$fields);
    
    $rows = array();
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      $sortvalues = [];
      $row = [];
      foreach($fields as $field) {
        $report_key = $field['as'];
        $field_key = $field['column'];
        $report_value = $entry[ $field_key ];
        if (is_array($report_value)) $report_value = implode(' ',$report_value);
        $row[$report_key] = $report_value;
        if (empty($field['callable'])) {
          $sortvalues[] = $report_value;
        }
      }
      $sortkey = implode('-',$sortvalues);
      $rows[] = [ $sortkey => $row ];
    }
    
    foreach($fields as $field) {
      if (!empty($field['callable'])) {
        $field_callable = '\\WorklogCLI\\Worklog2021\\'.$field['callable'];
        $rows = call_user_func_array($field_callable,[$field,$rows]);
      }
    }
    
    $return = [];
    
    foreach($rows as $row) {
      $rowdata = reset($row);
      $sortkey = key($row);
      $return[$sortkey] = $rowdata;
    }

    uksort($return, "strnatcmp");
    
    $return = array_values($return);
    
    return $return;

  }    
  public static function fields($fields) {
    
    if (is_string($fields)) $fields = [ $fields ];
    
    $return = [];
    foreach($fields as $key => $value) {
      $field = [];
      $key_is_numeric = is_numeric($key);
      $key_is_nonempty_string = is_string($key) && ($key!='');;
      $value_is_numeric = is_numeric($value);
      $value_is_nonempty_string = is_string($value) && ($value!='');
      if ($key_is_numeric && $value_is_nonempty_string) {
        $field['column'] = $value;
        $field['as'] = $value;
      }
      if (!$key_is_numeric && $key_is_nonempty_string && $value_is_nonempty_string) {
        $field['column'] = $value;
        $field['as'] = $key;
      }
      // look for 'callable'
      if (!empty($field['column'])) {
        $last_slash_item = end(explode('/',$field['column']));
        $last_is_callable = is_callable('\\WorklogCLI\\Worklog2021\\'.$last_slash_item);
        $last_has_double_colon = strpos($last_slash_item,'::')!==FALSE;
        if ($last_has_double_colon && $last_is_callable) {
          $field['callable'] = $last_slash_item;
          $field['column'] = explode('/',$field['column']);
          array_pop($field['column']);
          $field['column'] = implode('/',$field['column']);
        }
      }
      // look for 'parent'
      if (!empty($field['column'])) {
        if(strpos($field['column'],'/')!==FALSE) {
          $slashbroken = explode('/',$field['column']);
          $field['parent'] = reset($slashbroken);
          $field['column'] = end($slashbroken);
        }
      }
      // return
      if (!empty($field)) {
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

  
