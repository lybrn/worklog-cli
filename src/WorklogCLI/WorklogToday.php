<?php
namespace WorklogCLI;
class WorklogToday {

    public static function day_summary($worklog_file_path,$date=null,$args=array()) {
      
      $parsed = \WorklogCLI\WorklogData::get_data($worklog_file_path);
      $today = WorklogToday::args_get_date($args) ?: date('Y-m-d');
      
      $today_items = array();
      foreach($parsed as $item) {
        if ($item['date']!=$today) continue;
        $today_items[] = $item;
      }
      
      $rows = array();
      foreach($today_items as $item) {
        $row = array();
        $row['start'] = date('H:i',strtotime($item['started_at']));
        $row['client'] = $item['client'];
        $row['title'] = $item['title'];
        $row['hours'] = $item['hours'];
        $row['multiplier'] = $item['multiplier'];
        $row['total'] = $item['total'];
        $row['rate'] = $item['rate'];
        $row['$'] = $item['$'];
        $rows[] = $row;
      }
      return $rows;
      
    }
  
    public static function month_summary($worklog_file_path,$month=null,$args=array()) {
      
      $parsed = \WorklogCLI\WorklogData::get_data($worklog_file_path);
      $month = WorklogToday::args_get_month($args) ?: date('Y-m');
      $category = WorklogToday::args_get_category($parsed,$args) ?: null;
      
      $month_items = array();
      foreach($parsed as $item) {
        $item_month = date('Y-m',strtotime($item['date']));
        if ($item_month!=$month) continue;
        $month_items[] = $item;
      }
      
      $rows = array();
      foreach($month_items as $item) {
        if (!empty($category)) {
          $key = WorklogToday::normalize($item['client']);
          if ($key!=$category) continue;
        }
        $started_at = @strtotime($item['started_at']) ?:  strtotime($item['day_text']);
        $row = array();
        $row['start'] = date('H:i',strtotime($item['started_at']));
        $row['client'] = $item['client'];
        $row['title'] = $item['title'];
        $row['hours'] = $item['hours'];
        $row['multiplier'] = $item['multiplier'];
        $row['total'] = $item['total'];
        $row['rate'] = $item['rate'];
        $row['$'] = $item['$'];
        $rows[] = $row;
      }
      return $rows;
      
    }
    public static function args_get_date($args) {
      foreach($args as $arg) {
        if (strtotime($arg)!=0) {
          return date('Y-m-d',strtotime($arg));
        }
      }
    }
    public static function args_get_month($args) {
      $months = array(
        'january'    => '01',
        'february'   => '02',
        'march'      => '03',
        'april'      => '04',
        'may'        => '05',
        'june'       => '06',
        'july'       => '07',
        'august'     => '08',
        'september'  => '09',
        'october'    => '10',
        'november'   => '11',
        'december'   => '12',
      );
      foreach($args as $arg) {
        $arg = strtolower($arg);
        if (!empty($months[$arg])) {
          return date('Y').'-'.$months[$arg];
        }
      }
    }
    public static function args_get_category($data,$args) {
      $category_list = WorklogToday::category_list($data);
      foreach($args as $arg) {
        if (in_array($arg,$category_list)) {
          return $arg;
        }
      }      
    }
    public static function category_list($data) {
        
      $worklog_categories = array();
      if (is_array($data)) foreach($data as $data => $item) {
        $key = WorklogToday::normalize($item['client']);
        $cat = array();
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

// ///
// 
// DATE (Location)
// ===============
// 
// +-------+--------+---------------+------+
// | Start | Client | Sitting title | Time |
// | Start | Client | Sitting title | Time |
// | Start | Client | Sitting title | Time |
// | Start | Client | Sitting title | Time |
// +-------+--------+---------------+------+
// 
// Total: xx.xx hours
// 
// 
