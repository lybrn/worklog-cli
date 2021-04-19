<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
use WorklogCLI\Format;  
class WorklogParsing {
  
  public static function brackets_match_item($brackets,$items) {
    if (empty($brackets) || empty($items)) return null;
    if (!is_array($brackets)) $brackets = [ $brackets ];
    if (!is_array($items)) $items = [ $items ];
    $match = null;
    foreach($brackets as $bracket) {
      foreach($items as $item) {
        $bracketkey = Format::normalize_key($bracket);
        $itemkey = Format::normalize_key($item);
        if ($bracketkey == $itemkey) {
          $match = $item;
          break;
        }
      }
    }
    return $match;
  }
  public static function brackets_remove_item($brackets,$items) {
    $brackets_without_items = $brackets;
    if (is_array($brackets)) foreach($brackets as $b=>$bracket) {
      foreach($items as $item) {
        $bracketkey = Format::normalize_key($bracket);
        $itemkey = Format::normalize_key($item);
        if ($bracketkey == $itemkey) {
          unset($brackets_without_items[$b]);
        }
      }
    }
    return @array_values($brackets_without_items);
  }
  public static function line_get_brackets($text) {

    $matches = array();
    preg_match_all('/\(+([^\)]+)\)+/i',$text,$matches);
    $return = @is_array($matches[1]) ? $matches[1] : array();
    return $return;

  }
  public static function line_get_precolon($text) {

    $matches = array();
    preg_match_all('/^[^a-zA-Z0-9:]*([a-zA-Z0-9][^:]+):.*$/i',$text,$matches);
    $return = @is_array($matches[1]) ? $matches[1] : array();
    return $return;

  }  
  public static function brackets_get_multiplier($brackets) {

    foreach($brackets as $bracket) {
      $is_starred = substr($bracket,0,1)=='*';
      $is_numeric = is_numeric(substr($bracket,1));
      if ($is_starred && $is_numeric) return substr($bracket,1);
    }
    return null;

  }
  public static function brackets_get_rate($brackets) {

    foreach($brackets as $bracket) {
      $is_dollarsigned = substr($bracket,0,1)=='$';
      $is_numeric = is_numeric(substr($bracket,1));
      if ($is_dollarsigned && $is_numeric) return substr($bracket,1);
    }
    return null;

  }
  public static function brackets_get_cost($brackets) {

    if (!is_array($brackets) && !empty($brackets)) 
      $brackets = [ $brackets ];
      
    foreach($brackets as $bracket) {
      $is_dollarsigned_or_negative = substr($bracket,0,1)=='$' || substr($bracket,0,2)=='-$';
      $bracket_cleaned = strtr($bracket,['$'=>'',','=>'']);
      $is_numeric = is_numeric($bracket_cleaned);
      if ($is_dollarsigned_or_negative && $is_numeric) return $bracket_cleaned;
    }
    return null;

  }
  public static function brackets_get_yyyymmdd($brackets) {

    if (!is_array($brackets) && !empty($brackets)) 
      $brackets = [ $brackets ];
      
    foreach($brackets as $bracket) {
      $bracket = trim($bracket);
      $is_yyyymmdd = preg_match('/\d\d\d\d-\d\d-\d\d/',$bracket);
      $can_covert_to_timestamp = strtotime($bracket) != FALSE;
      if ($is_yyyymmdd && $can_covert_to_timestamp) return $bracket;
    }
    return null;

  }
  public static function brackets_get_time($brackets) {
    
    $time_brackets = [];
    
    foreach($brackets as $bracket_text) {
      // get offset
      
      $time_offset = WorklogParsing::time_get_offset($bracket_text);
      if (!empty($time_offset)) { 
        
        // process +/-##m/h time format (+10m) (-1h)
        
        $time_brackets[] = $bracket_text; 
        continue; 
      }
      
      if (is_string($bracket_text) && !is_numeric($bracket_text)) {
        
        // process ##:##am/pm time format (10:20am)

        $has_plus_or_minus = preg_match('/[\+\-]/i',$bracket_text);
        $am = preg_match('/a/i',$bracket_text);
        $pm = preg_match('/p/i',$bracket_text);
        $colon = preg_match('/:/i',$bracket_text);
        
        if ($has_plus_or_minus) continue;
        if (!$am && !$pm && !($am && $pm)) continue;
        if (!$colon) continue;
        
        $bracket_text_adjusted = preg_replace('/[^0-9\:]+/i','',$bracket_text);
        if (empty($bracket_text_adjusted)) continue;
        if ($am) $bracket_text_adjusted .= 'am';
        else if ($pm) $bracket_text_adjusted .= 'pm';
        
        $test_timestamp = strtotime( date('Y-m-d').' '.$bracket_text_adjusted );
        if (!empty($test_timestamp)) {
          $time_brackets[] = $bracket_text;     
        }
        
        continue;

      }
    }  

    return current($time_brackets);
  }
  public static function timetool_filter_since($compare,$compare_since) {

    // compare
    if (empty($compare)) return false;
    if (!is_numeric($compare)) $compare = strtotime($compare);
    if (empty($compare)) return false;
    // compare_since
    if (empty($compare_since)) return false;
    if (!is_numeric($compare_since)) $compare_since = strtotime($compare_since);
    if (empty($compare_since)) return false;
    // print_r(array(
    //   $compare,
    //   $compare_since,
    //   ( $compare > $compare_since )
    // ));
    // return
    return ( $compare >= $compare_since );

  }
  public static function timetool_filter_until($compare,$compare_until) {

    // compare
    if (empty($compare)) return false;
    if (!is_numeric($compare)) $compare = strtotime($compare);
    if (empty($compare)) return false;
    // compare_until
    if (empty($compare_until)) return false;
    if (!is_numeric($compare_until)) $compare_until = strtotime($compare_until);
    if (empty($compare_until)) return false;
    // print_r(array(
    //   $compare,
    //   $compare_until,
    //   ( $compare > $compare_until )
    // ));
    // return
    return ( $compare <= $compare_until );

  }
  public static function timetool_filter_compare($compare,$compare_to) {

    // compare
    if (empty($compare)) return false;
    $compare = strtolower($compare);
    $compare = preg_replace('/[^a-z]+/','',$compare);
    if (empty($compare)) return false;
    // compare_to
    if (empty($compare_to)) return false;
    $compare_to = strtolower($compare_to);
    $compare_to = preg_replace('/[^a-z]+/','',$compare_to);
    if (empty($compare_to)) return false;
    // return
    return ( $compare == $compare_to );

  }
  public static function time_get_offset($time) {

    $offset = 0;
    if (preg_match('/[\+\-]/i',$time)) {
      $hours = preg_match('/h/i',$time);
      $mins = preg_match('/m/i',$time);
      if (!$hours && !$mins && !($hours && $mins)) return null;
      if (preg_match('/[\+]/i',$time)) {
        $offset = preg_replace('/[^0-9\.]+/i','',$time);
        if (!is_numeric($offset)) $offset = 0;
        $offset = $hours ? floor( $offset * 60 * 60 ) : floor( $offset * 60 );
      }
      else if (preg_match('/^\s*[\-]/i',$time)) {
        $offset = preg_replace('/[^0-9\.]+/i','',$time);
        if (!is_numeric($offset)) $offset = 0;
        $offset = $hours ?
          floor( $offset * 60 * 60 ) :
          floor( $offset * 60 );

        $offset *= -1;
      }
    }
    return $offset;

  }
  public static function time_get_timepoint($time) {  
    // we are not using 1970-01-01 because we want 0 to still mean false
    $timepoint = strtotime('1970-01-02 '.$time.' UTC');
    if (empty($timepoint)) return FALSE;
    return $timepoint;
  }
  public static function line_clean_brackets($text) {

    $text = preg_replace("/\s*\(+[^\(]+\)+/i","",$text);
    $text = trim($text);
    $text = trim($text,'.');
    $text = trim($text);
    return $text;

  }
  public static function timepoints_get_starttime_and_endtime($timepoints) {
    $plus24hours = 24*60*60;
    $starttime = 0;
    $endtime = 0;
    foreach($timepoints as $timepoint) {
      if (empty($timepoint)) $continue;
      if (empty($starttime)) $starttime = $timepoint;
      if (empty($endtime)) $endtime = $timepoint;
      $timepoint_nextday = $timepoint + $plus24hours;
      if ($timepoint > $endtime) $endtime = $timepoint;
      if ($timepoint < $starttime) {
          $distance_from_starttime = abs($starttime - $timepoint);
          $distance_from_endtime = abs($endtime - $timepoint_nextday);
          if ($distance_from_endtime < $distance_from_starttime) {
            if ($timepoint_nextday > $endtime) $endtime = $timepoint_nextday;
          } else  {
            $starttime = $timepoint;
          }
      }
    }
    
    return [
      'starttime' => $starttime,
      'endtime' => $endtime,
    ];
  }
  
  
}
