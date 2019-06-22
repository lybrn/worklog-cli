<?php 
namespace WorklogCLI;
class Output {
  
  public function markdown_html($markdown) {

    // turn markdown into html
    $parsedown = new \Parsedown();
    $output = $parsedown->text($markdown);
    // format html
    $doc = new \DomDocument('1.0');
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;
    $doc->loadXML('<html>'.$output.'</html>');
    $output = $doc->saveXML();
    $output = trim(strtr($output,array(
      '<?xml version="1.0"?>'=>'',
      '<html>'=>'',
      '</html>'=>'',
    )));
    $output = preg_replace('/^  /m','',$output);
    return $output;
        
  }
  public static function formatted_html($html) {
    
    // format html
    $doc = new \DomDocument('1.0');
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;
    $doc->loadXML($html);
    $output = $doc->saveXML();
    $output = trim(strtr($output,array(
      '<?xml version="1.0"?>'=>'<!DOCTYPE html>',
    )));
    $output = preg_replace('/^  /m','',$output);
    return $output;
  
  
  }  
  public function formatted_json($data) {
    
    // turn array into pretty printed json
    $output = json_encode($data,JSON_PRETTY_PRINT);
    return $output;
    
  }
  public function formatted_stardot($data) {
    
    // turn array into rendered stardot format
    $output = \WorklogCLI\StarDot::render($data);
    return $output;
    
  }  
  public function formatted_yaml($data) {
    
    // turn array into pretty printed json
    $output = \WorklogCLI\YAML::encode($data);
    return $output;
    
  }
  public function border_box($content) {

    // render output 
    $content = trim( Output::render($content) );
    
    // break content into lines
    $lines = explode("\n",$content);

    // find length of longest line
    $longest = 0;
    foreach($lines as $line) {
      if (mb_strlen($line) > $longest) 
        $longest = mb_strlen($line);
    }
    
    // print top dash line and top padding line
    $output = "+".str_repeat('-',$longest+4)."+\n";
    $output .= "|  ".str_repeat(' ',$longest)."  |\n";
    
    // print content lines
    foreach($lines as $line) {
      $output .= "|  ".$line.str_repeat(' ',$longest-mb_strlen($line))."  |\n";
    }
    
    // print bottom dash line and top padding line
    $output .= "|  ".str_repeat(' ',$longest)."  |\n";
    $output .= "+".str_repeat('-',$longest+4)."+\n";
    
    // return output string 
    return $output;
  }
  public function render($content) {
    
    // if content is an array, implode keys and values into a content string
    if (is_array($content)) {
      foreach($content as $k=>$v) {
        if (is_array($v))
          $v = implode(', ',$v);
        if (!is_numeric($k))
          $content[$k] = "$k: $v";
      }
      $content = implode("\n",$content);
    }
    
    // remove white space from top and bottom of content
    $content = trim($content);
    
    return $content."\n";    
  }
  public function whitespace_table($rows,$include_totals_row=false,$sort_key=null) {

    // check if this is a single column of values
    $is_one_column = !@is_array(current($rows)) && @is_numeric(key($rows));
    
    // if this is one colomn, turn each value into a row
    if ($is_one_column) { 
      $values = $rows; 
      foreach($values as $i=>$value) {
        $rows[$i] = array($value);
      }
    }
    
    // check if rows uses numeric keys
    $uses_nonnumeric_keys = !empty($rows) && !@is_numeric(key(current($rows)));
    
    // build array of all keys
    $all_keys = [];
    if (is_array($rows)) foreach($rows as $cols) {
      if (is_array($cols)) foreach($cols as $colk=>$colv) {
        //if (is_numeric($colk) && !empty($colv)) $colk=$colv;   
        //if (empty($all_keys[$colk])) $all_keys[$colk] = $colk;
        $all_keys[$colk] = $colk;
        if (strpos($colk,'^')!==false) {
          $sort_key = $colk;
        }
        
      }
    }  
    
    // sort rows
    if (!empty($all_keys[$sort_key])) {
      $sorted = [];
      foreach($rows as $i => $row) {
        $sortable_key = $row[$sort_key] ?: '-';
        $sortable_key.= '-'.$i;
        $sorted[$sortable_key] = $row;
      }
      ksort($sorted);
      $rows = $sorted;
    }
    
    // build and add header row if nonnumeric keys
    if ($uses_nonnumeric_keys) { 
      $header_row = array();
      $divider_row = array();
      $keys = $all_keys; 
      if (is_array($keys)) foreach($keys as $k=>$key) {
        $header_row[$k] = strtoupper($key);
        $divider_row[$k] = str_repeat('-',mb_strlen($key));
      }
      array_unshift($rows,$divider_row);
      array_unshift($rows,$header_row);
    }
        
    // build add totals row
    if ($include_totals_row) {
      $totals = [];
      $decimals = [];
      $totals_row = array();
      $divider_row = array();
      if (is_array($rows)) foreach($rows as $cols) { 
        foreach($all_keys as $key) {
          $value = array_key_exists($key, $cols) ? $cols[$key] : '';
          if (is_numeric($value)) {  
            $totals[$key] += $value;
            $dotbroken = explode('.',$value);
            $value_decimals = count($dotbroken)==2 ? strlen(end(explode('.',$value))) : 0;
            if (empty($decimals[$key]) || $value_decimals > $decimals[$key]) 
              $decimals[$key] = $value_decimals;
            
          }
        }
      }
      if (!empty($totals)) {
        $keys = $all_keys; 
        $first_key = current($all_keys);
        if (is_array($keys)) foreach($keys as $k=>$key) {
          $total = number_format((float) $totals[$k], $decimals[$key], '.', '');
          $totals_row[$k] = @$total ?: '';
          if ($k == $first_key && empty($total)) {
            $totals_row[ $k ] = 'TOTALS:';
          }

          $divider_row[$k] = $totals_row[$k]=='' ? 
            str_repeat(' ',mb_strlen($totals_row[$k])) :
            str_repeat('-',mb_strlen($totals_row[$k]));
        }
        array_push($rows,$divider_row);
        array_push($rows,$totals_row);
      }
    }
    
    // determine largest size for each column
    $column_sizes = array();
    if (is_array($rows)) foreach($rows as $cols) { 
      if (is_array($all_keys)) foreach($all_keys as $key) {
        $value = array_key_exists($key, $cols) ? $cols[$key] : '';
        if (is_array($value)) $value = implode(', ',$value);
        if (empty($column_sizes[$key]) || mb_strlen($value) > $column_sizes[$key]) {
          $column_sizes[$key] = mb_strlen($value);
        }
      }
    }
    
    // create lines from rows of column values
    $lines = array();
    if (is_array($rows)) foreach($rows as $cols) {
      $line = array();
      if (is_array($all_keys)) foreach($all_keys as $key) {
        $value = array_key_exists($key, $cols) ? $cols[$key] : '-';
        if (is_array($value)) $value = implode(', ',$value);
        $column_size = $column_sizes[$key];
        $value = str_pad($value,$column_size," ");
        $line[] = $value;
      }
      
      $line = implode("   ",$line);
      $lines[] = $line;
    }
    
    // implode into output and return
    $output = implode("\n",$lines)."\n";
    return $output;
    
  }
  
  
}
