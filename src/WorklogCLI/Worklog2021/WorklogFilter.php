<?php
namespace WorklogCLI\Worklog2021;
class WorklogFilter {

  public static function filter_entries($entries,$args) {
    
    $options = WorklogFilter::get_options($entries,$args);
    
    if (empty($options)) return $entries;    
    
    $filtered = array();
  
    foreach($entries as $item) {
      
      // date range filter
      if (!is_null($options['range'])) {
        if ($item['filter_day_timestamp'] < strtotime($options['range'][0])) { continue; }
        if ($item['filter_day_timestamp'] > strtotime($options['range'][1])) { continue; }
      }
  
      // tag filter
      if (!is_null($options['tags'])) {
        foreach($options['tags'] as $filter_tag)
          if (!in_array($filter_tag,$item['filter_tags_all'])) { continue 2; }
      }
      
      $filtered[] = $item;
      
    }
    
    return $filtered;
    
    // 
    // $include_tags = [];
    // $exclude_tags = [];
    // if (!is_null($options['tags'])) {
    //   foreach($options['tags'] as $tag) {
    //     if (substr($tag,0,1)=='-') {
    //       $exclude_tags[] = substr($tag,1);
    //     } else {
    //       $include_tags[] = $tag;
    //     }
    //   }
    // }
    // 
    // foreach($entries as $item) {
    //   if (!is_null($options['categories'])) {
    //     $client = WorklogFilter::normalize($item['client']);
    //     if (!in_array($client,$options['categories'])) { continue; }
    //   }
    //   if (!empty($include_tags)) {
    //     $include = false;
    //     foreach($item['tags'] as $tag) {
    //       $tag = WorklogFilter::normalize($tag);
    //       if (in_array($tag,$include_tags)) { $include = true; break; }
    //     }
    //     if (!$include) { continue; }
    //   }
    //   if (!empty($exclude_tags)) {
    //     $exclude = false;
    //     foreach($item['tags'] as $tag) {
    //       $tag = WorklogFilter::normalize($tag);
    //       if (in_array($tag,$exclude_tags)) { $exclude = true; break; }
    //     }
    //     if ($exclude) { continue; }
    //   }
    //   if (!empty($options['incomeonly'])) {
    //     if (empty($item['$']) && empty($item['client-$'])) { continue; }
    //   }
    //   $filtered[] = $item;
    // }
    // return $filtered;
    
  }
  public static function get_options($entries,$args) {
    
    // get option values
    $range = WorklogFilter::args_get_date_range($args);
    $tags = WorklogFilter::args_get_tags($entries,$args);
    $incomeonly = WorklogFilter::args_get_income_flag($entries,$args);
    // build options array
    $options = array();
    if (!is_null($range)) $options['range'] = $range;
    if (!empty($tags)) $options['tags'] = $tags;
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
  public static function args_get_tags($data,$args) {
    $tag_list = WorklogFilter::tags_list($data);
    $tags = [];
    foreach($args as $arg) {
      $is_negative = substr($arg,0,1)=='-';
      $arg = WorklogFilter::normalize($arg);
      if (in_array($arg,$tag_list)) {
        $tags[] = $is_negative ? '-'.$arg : $arg;
      }
    }   
    return $tags;   
  }
  public static function args_get_income_flag($data,$args) {
    foreach($args as $arg) {
      if ($arg=='$') return TRUE;
    }      
    return null;
  }
  public static function tags_list($data) {
    $worklog_tags = array();
    if (is_array($data)) foreach($data as $data => $item) {
      $tags = @$item['filter_tags_all'] ?: [];
      foreach($tags as $tag) {
        if (strtotime($tag)) continue;
        $key = WorklogFilter::normalize($tag);
        $worklog_tags[ $key ] = $key;
      }
    }
    asort($worklog_tags);
    $rows = array_values($worklog_tags);
    return $rows;  
  }    
  public static function normalize($string) {
    $normalized = preg_replace('/\([^\)]*\)/i','',$string);
    $normalized = preg_replace('/[^a-z0-9]/i','',$normalized);
    $normalized = strtolower($normalized);
    return $normalized;
  }
  
}
  
