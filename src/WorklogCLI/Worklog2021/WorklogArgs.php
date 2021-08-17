<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\Worklog2021\WorklogCli;
use WorklogCLI\Worklog2021\WorklogEntries;
use WorklogCLI\Worklog2021\WorklogDb;
use WorklogCLI\Worklog2021\WorklogNormalize;
class WorklogArgs {


  public static function get_filtered_data_2021($args=[]) {

    static $cached = [];
    $args = @$args ?: WorklogCli::$args;
    $args_key = md5(print_r($args,TRUE));
    if (empty($cached[$args_key])) {
      
      // get worklog file paths
      $worklog_file_paths = WorklogCli::get_worklog_filepaths();

      // parse and filter worklog
      $parsed = WorklogEntries::entries($worklog_file_paths);
      $filtered = $parsed;
      // $filtered = WorklogFilter::filter_parsed($parsed,$args);

      $cached[$args_key] = $filtered;
      
    }
    // return filtered
    return $cached[$args_key];

  }  
  public static function get_invoice_number() {

    static $cached_invoice_number = null;
    if (!empty($cached_invoice_number)) return $cached_invoice_number;
    
    // loop through each arg
    foreach(WorklogCli::args() as $key) {
      // normalize key
      $key = WorklogNormalize::normalize_key($key);
      // get any data for this key
      $data = WorklogDb::db($key);
      // if no data found, continue
      if (empty($data)) continue;
      // format array keys on data found
      $data = WorklogNormalize::normalize_array_keys(current($data));
      $data = WorklogNormalize::array_keys_remove_prefix($data,['invoice','work']);
      // get the invoice nmber if there is one
      $data_invoice_number = WorklogNormalize::normalize_key($data['number']) ?: null;
      // if no invoice number found, continue
      if (empty($data_invoice_number)) continue;
      // if arg key matches the invoice dumber, return the invoice number
      if ($key==$data_invoice_number) {
        $cached_invoice_number = $data_invoice_number;
        return $cached_invoice_number;
      }
    }
    // if we got this far, no invoice number has been found
    return null;
    
  }     
  public static function get_invoice_data() {  
    // get invoice number
    $invoice_number = WorklogArgs::get_invoice_number();
    // if no data found, continue
    if (empty($invoice_number)) return null;
    // get any data for this key
    $data = WorklogDb::db($invoice_number);
    // if no data found, continue
    if (empty($data)) return null;
    // format array keys on data found
    $data = WorklogNormalize::normalize_array_keys(current($data));    
    $data = WorklogNormalize::array_keys_remove_prefix($data,[ 'invoice','work' ]);
    // return data
    return $data;
  }
  public static function get_invoice_filter_args() {  
    // get invoice data 
    $invoice_data = WorklogArgs::get_invoice_data();
    // build array of filder arguments from invoice data
    $filter_args = [];
    // add client or category
    if (!empty($invoice_data['category'])) 
      $filter_args[] = WorklogNormalize::normalize_key($invoice_data['category']);
    else if (!empty($invoice_data['client'])) 
      $filter_args[] = WorklogNormalize::normalize_key($invoice_data['client']);
    if (!empty($invoice_data['project'])) {
      $projects = explode(' ',$invoice_data['project']);
      foreach($projects as $project) {
        $is_negative = substr($project,0,1)=='-';
        $project = WorklogNormalize::normalize_key($project);
        $filter_args[] = $is_negative ? '-'.$project : $project;
      }
    }
    // add range
    $range = explode(' ',$invoice_data['range']);
    foreach($range as $range_point) 
      if (!empty($range_point)) $filter_args[] = $range_point;
    // return filter arguments
    return $filter_args;
  }
    
}
