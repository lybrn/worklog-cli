<?php
namespace WorklogCLI;
class WorklogFilter {

  public static function filter_parsed($parsed,$args) {
    $options = WorklogFilter::get_options($parsed,$args);
    if (empty($options)) return $parsed;
    $filtered = array();
    foreach($parsed as $item) {
      if (!is_null($options['category'])) {
        $client = WorklogFilter::normalize($item['client']);
        if ($client!=$options['category']) { continue; }
      }
      // if (!is_null($options['task'])) {
      //   $task = WorklogFilter::normalize($item['title']);
      //   if ($task!=$options['task']) continue;
      // }
      if (!is_null($options['bracket'])) {
        $found = false;
        foreach($item['brackets'] as $bracket) {
          $bracket = WorklogFilter::normalize($bracket);
          if ($bracket==$options['bracket']) { $found = true; break; }
        }
        if (!$found) { continue; }
      }
      if (!is_null($options['range'])) {
        if ($item['started_at'] < $options['range'][0]) continue;
        if ($item['started_at'] > $options['range'][1]) continue;
      }
      if (!empty($options['incomeonly'])) {
        if (empty($item['$'])) continue;
      }
      $filtered[] = $item;
    }
    return $filtered;
  }
  public static function get_options($parsed,$args) {
    
    // get option values
    $range = WorklogFilter::args_get_date_range($args);
    $category = WorklogFilter::args_get_category($parsed,$args);
    $bracket = WorklogFilter::args_get_bracket($parsed,$args);
    $task = WorklogFilter::args_get_task($parsed,$args);
    $incomeonly = WorklogFilter::args_get_income_flag($parsed,$args);
    // build options array
    $options = array();
    if (!is_null($range)) $options['range'] = $range;
    if (!is_null($category)) $options['category'] = $category;
    if (!is_null($bracket)) $options['bracket'] = $bracket;
    if (!is_null($incomeonly)) $options['incomeonly'] = $incomeonly;
    // return
    return $options;
    
  }  
  public static function arg_process_text_dates($arg) {
    $argnom = WorklogFilter::normalize($arg);
    $months = array(
      'january'    => '01',
      'jan'        => '01',
      'february'   => '02',
      'feb'        => '02',
      'march'      => '03',
      'mar'        => '03',
      'april'      => '04',
      'apr'        => '04',
      'may'        => '05',
      'may'        => '05',
      'june'       => '06',
      'jun'        => '06',
      'july'       => '07',
      'jul'        => '07',
      'august'     => '08',
      'aug'        => '08',
      'september'  => '09',
      'sep'        => '09',
      'october'    => '10',
      'oct'        => '10',
      'november'   => '11',
      'nov'        => '11',
      'december'   => '12',
      'dec'        => '12',
    );
    if (!empty($months[$argnom])) {
      return date('Y').'-'.$months[$argnom];
    }    
    $weekdays = array(
      'today'     => 'today',
      'yesterday' => 'yesterday',
      'monday'    => 'last Monday',
      'mon'       => 'last Monday',
      'tuesday'   => 'last Tuesday',
      'tue'       => 'last Tuesday',
      'wednesday' => 'last Wednesday',
      'wed'       => 'last Wednesday',
      'thursday'  => 'last Thursday',
      'thu'       => 'last Thursday',
      'friday'    => 'last Friday',
      'fri'       => 'last Friday',
      'saturday'  => 'last Saturday',
      'sat'       => 'last Saturday',
      'sunday'    => 'last Sunday',
      'sun'       => 'last Sunday',
    );
    if (!empty($weekdays[$argnom])) {
      return date('Y-m-d',strtotime($weekdays[$argnom]));
    }    
    return $arg;
  }
  public static function args_get_date_range($args) {
    $dates = array();
    foreach($args as $arg) {
      $arg = WorklogFilter::arg_process_text_dates($arg);
      if (preg_match('/^\d\d\d\d-\d\d$/',$arg)) {
        $firstofmonth = $arg.'-01';
        $lengthofmonth = date('t',strtotime($firstofmonth));
        $lasttofmonth = $arg.'-'.$lengthofmonth;
        $dates[] = $firstofmonth;
        $dates[] = $lasttofmonth;
        continue;
      }  
      else if (preg_match('/^\d\d\d\d-\d\d-\d\d$/',$arg)) {
        $dates[] = $arg;
      }
    } 
    $fromdate = null;
    $untildate = null;
    foreach($dates as $date) {
      $stamp = strtotime($date);
      if ($stamp==0) return;
      if (empty($fromdate) || $stamp < $fromdate) $fromdate = $stamp;
      if (empty($untildate) || $stamp > $untildate) $untildate = $stamp;
    }
    if (!empty($fromdate) && !empty($untildate)) {
      return array( 
        date('Y-m-d 00:00:00',$fromdate),
        date('Y-m-d 23:59:59',$untildate),
      );
    }
  }
  public static function args_get_month($args) {
    $month = WorklogFilter::args_get_numeric_month($args);
    if (!is_null($month)) return $month;
    $month = WorklogFilter::args_get_text_month($args);
    if (!is_null($month)) return $month;
  }
  public static function args_get_category($data,$args) {
    $category_list = WorklogFilter::category_list($data);
    foreach($args as $arg) {
      if (in_array($arg,$category_list)) {
        return $arg;
      }
    }      
  }
  public static function args_get_task($data,$args) {
    $task_list = WorklogFilter::tasks_list($data);
    foreach($args as $arg) {
      if (in_array($arg,$task_list)) {
        return $arg;
      }
    }      
  }
  public static function tasks_list($data) {
    $worklog_tasks = array();
    if (is_array($data)) foreach($data as $data => $item) {
      $task = @$item['title'] ?: '';
      $key = WorklogFilter::normalize($task);
      $worklog_tasks[ $key ] = $key;
    }
    asort($worklog_tasks);
    $rows = array_values($worklog_tasks);
    return $rows;  
  }      
  public static function args_get_bracket($data,$args) {
    $bracket_list = WorklogFilter::brackets_list($data);
    foreach($args as $arg) {
      if (in_array($arg,$bracket_list)) {
        return $arg;
      }
    }      
  }
  public static function args_get_income_flag($data,$args) {
    foreach($args as $arg) {
      if ($arg=='$') return TRUE;
    }      
    return null;
  }
  public static function brackets_list($data) {
    $worklog_brackets = array();
    if (is_array($data)) foreach($data as $data => $item) {
      $brackets = @$item['brackets'] ?: array();
      foreach($brackets as $bracket) {
        $key = WorklogFilter::normalize($bracket);
        $worklog_brackets[ $key  ] = $key;
      }
    }
    asort($worklog_brackets);
    $rows = array_values($worklog_brackets);
    return $rows;  
  }  
  public static function category_list($data) {
    $worklog_categories = array();
    if (is_array($data)) foreach($data as $data => $item) {
      $key = WorklogFilter::normalize($item['client']);
      $worklog_categories[ $key  ] = $key;
    }
    ksort($worklog_categories);
    $rows = array_values($worklog_categories);
    return $rows;    
  }
  public static function normalize($string) {
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    $normalized = preg_replace('/[^a-z0-9]/i','',$normalized);
    $normalized = strtolower($normalized);
    return $normalized;
  }


  
}
  
