<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
use WorklogCLI\Format;  
use WorklogCLI\Twig;  
class WorklogReports {
  
  public static function template($entries,$template_map) {

    $rendered = [];
    
    foreach($entries as $entry) {
      $row = [];
      foreach($entry as &$value) {
        if (is_array($value)) $value = implode(' ',$value);
      }
      foreach($template_map as $field=>$template) {
        if (is_numeric($field)) {
          $row[ $template ] = $entry[$template] ?: null;          
        } else {
          $row[ $field ] = Twig::process($template,$entry);          
        }
      }
      $rendered[] = $row;
    }  
    return $rendered; 
  }
    
    
  public static function filter($entries,$fields) {
    
    if (empty($entries)) return $entries;
    if (empty($fields)) return $entries;

    $filtered = [];
    
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      foreach($fields as $name=>$value) {

        if (!array_key_exists($name,$entry)) continue 2;
        if ($entry[$name]==='') continue 2;
        if ($entry[$name]===null) continue 2;
        if ($entry[$name]===false) continue 2;
        
        if (is_string($value)){
          if (is_string($entry[$name]) && $entry[$name]!=$value) continue 2;
          if (is_array($entry[$name]) && !in_array($value,$entry[$name])) continue 2;
          // print_r([
          //   'entry name' => $entry[$name],
          //   'filter value' => $value,
          // ]); 
          // die("!");
        }

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
  public static function rows($entries,$fields) {
    
    $report = WorklogReports::report($entries,$fields);
    
    foreach($report as $rowkey => &$row) {
      foreach($row as $fieldkey => &$fieldvalue) {
        if (count($fieldvalue) == 1) {
          $row[$fieldkey] = current($fieldvalue);
        } 
        else if (count($fieldvalue) > 1) {
          throw new Exception("More than one item for in '$fieldkey' in ".print_r($row,TRUE));
        }
      }
    }
    
    return $report;
    
  }
  public static function report($entries,$fields) {

    $fields = WorklogReports::fields($fields);
    
    //print_r($fields);
    // exit;
    // 
    $entries = WorklogReports::reduce($entries,$fields);
    
    $rows = array();
    if (is_array($entries)) foreach($entries as $index=>$entry) {
      $sortvalues = [];
      $row = [];
      foreach($fields as $field) {
        $report_key = $field['key'];
        $report_as = $field['as'];
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
    
    //print_r($rows);
    
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
    
    $returnrows = [];
    
    foreach($return as $row) {
      $returnrow = [];
      foreach($fields as $field) {
        
        if (empty($returnrow[ $field['as'] ]))
          $returnrow[ $field['as'] ] = [];
        
        if (is_null($row[ $field['key'] ]))
          continue;
        
        if ($row[ $field['key'] ]=='')
          continue;

        if (is_array($row[ $field['key'] ]) && empty($row[ $field['key'] ]))
          continue;

        if (!is_array($row[ $field['key'] ]))
          $row[ $field['key'] ] = [ $row[ $field['key'] ] ];
        
        $returnrow[ $field['as'] ] = array_merge($returnrow[ $field['as'] ],$row[ $field['key'] ]);
                
      }
      $returnrows[] = $returnrow;

    }
    //print_r($return);

    return $returnrows;

  }    
  public static function fields($fields) {
    
    if (is_string($fields)) $fields = [ $fields ];
    
    $return = [];
    $pos = 0;
    $colmap = [];
    foreach($fields as $key => $valueset) {
      if (empty($valueset)) continue;
      $field = [];
      $key_is_numeric = is_numeric($key);
      $key_is_nonempty_string = is_string($key) && ($key!='');;
      if (!is_array($valueset)) { $valueset = [ $valueset ]; }
      foreach($valueset as $vkey => $value) {
        $pos++;

        $vkey_is_numeric = is_numeric($key);
        $vkey_is_nonempty_string = is_string($key) && ($key!='');;
        $value_is_numeric = is_numeric($value);
        $value_is_nonempty_string = is_string($value) && ($value!='');
        
        $field['pos'] = $pos;
        
        if ($key_is_numeric && $value_is_nonempty_string) {
          $field['column'] = $value;
          $field['as'] = $value;
          $field['template'] = $value;
          $field['var'] = $value;
        }
        if (!$key_is_numeric && $key_is_nonempty_string && $value_is_nonempty_string) {
          $field['column'] = $value;
          $field['as'] = $key;
          $field['template'] = $key;
          $field['var'] = $key;
        }
        if ($vkey_is_nonempty_string) {
          $field['var'] = $vkey;          
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
          $field['key'] = implode('-',[ $field['as'], $field['var'],$field['pos'] ]);
          $colmap[ $field['as'] ] = $field['key'];
          $return[] = $field; 
        }
      }
    }
    
    foreach($return as &$field) {
      $field['colmap'] = $colmap;
      
    }
    return $return;
  }
  public static function sortcols($sortcols) {
    
    $normalized = [];
    foreach($sortcols as $k=>$v) {
      if (is_numeric($k)) {
        $col = [];
        $col['col'] = $v;
        $col['asc'] = TRUE;
      } else if (is_string($k)) {
        $col = [];
        $col['col'] = $k;
        $col['asc'] = (strtoupper($v)!='DESC');
      }
      $normalized[ $col['col'] ] = $col;
    }
    
    return $normalized;
    
  }
  public static function sort_old($report,$sortcols) {
  
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
    
    uksort($sortable, "strnatcmp");
        
    return array_values($sortable);
    
  }
  public static function sort($report,$sortcols) {
  
    $sortcols = WorklogReports::sortcols($sortcols);
    
    $sort_pos_by_column_value = [];
        
    foreach($report as $i=>$row) {
      foreach($sortcols as $sortcol) {
        $sortcolname = $sortcol['col'];
        $sortvalue = $row[$sortcolname];
        if (is_array($sortvalue)) $sortvalue = implode(' ',$row[$sortcolname]);
        if (!is_string($sortvalue)) $sortvalue = (string) $sortvalue;
        $sort_pos_by_column_value[$sortcolname][] = $sortvalue;
      }
    }
    
    // print_r($sortcols);
    
    foreach($sort_pos_by_column_value as $sortcolname => &$column_sort_pos) {
      $sortcolasc = $sortcols[ $sortcolname ]['asc'];
      $column_sort_pos = array_unique($column_sort_pos);
      $column_sort_pos = array_values($column_sort_pos);
      usort($column_sort_pos, "strnatcmp");
      if (!$sortcolasc) $column_sort_pos = array_reverse($column_sort_pos);
      $column_sort_pos = array_values($column_sort_pos);
      $column_sort_pos = array_flip($column_sort_pos);
    }
    
    // print_r($sort_pos_by_column_value);
    
    $sortable = [];
    foreach($report as $i=>$row) {

      $sortkey = [];
      foreach($sortcols as $sortcol) {
        $sortcolname = $sortcol['col'];
        $sortvalue = $row[$sortcolname];
        if (is_array($sortvalue)) $sortvalue = implode(' ',$row[$sortcolname]);
        if (!is_string($sortvalue)) $sortvalue = (string) $sortvalue;
        $sortkey[] = $sort_pos_by_column_value[$sortcolname][$sortvalue];
      }
      
      $sortkey[] = $i;
      $sortkey = implode('-',$sortkey);
      $sortable[ $sortkey ] = $row;
    }
    
    uksort($sortable, "strnatcmp");
    
    // print_r($sortable);
        
    return array_values($sortable);
    
  }
}

  
