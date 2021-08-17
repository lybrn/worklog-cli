<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\Output;
use WorklogCLI\Worklog2021\WorklogArgs;
use WorklogCLI\Worklog2021\WorklogNormalize;
use WorklogCLI\Worklog2021\WorklogEntries;
use WorklogCLI\Worklog2021\WorklogAugment;
use WorklogCLI\Worklog2021\WorklogFilter;
use WorklogCLI\JsonConfig;
class WorklogCli {

  public static $original_args = [];
  public static $args = [];

  public static function original_args() {
    return WorklogCli::$original_args;
  }
  public static function args() {
    return WorklogCli::$args;
  }
  public static function add_args($add = null) {
    if (is_string($add)) WorklogCli::$args[] = $add;
    if (is_array($add)) foreach($add as $arg) WorklogCli::$args[] = $arg;
    return WorklogCli::$args;
  }
  public static function remove_arg($arg_to_remove) {
    // if arg to remove is empty. return
    if (empty($arg_to_remove)) return;
    // make normalized version of arg to remove
    $arg_to_remove_normalized = WorklogNormalize::normalize_key($arg_to_remove);
    // loop through stored args
    foreach(WorklogCli::$args as $i=>$arg) {
      // if this arg is empty, continue
      if (empty($arg)) continue;
      // if this arg matches arg to remove, unset
      if ($arg==$arg_to_remove) unset(WorklogCli::$args[$i]);
      // get normalized copy of this arg
      $arg_normalized = WorklogNormalize::normalize_key($arg);
      // if either normalized version is empty, continue
      if (empty($arg_normalized)) continue;
      if (empty($arg_to_remove_normalized)) continue;
      // if normailize arg matches normalized arg to remove, unset
      if ($arg_normalized==$arg_to_remove_normalized) unset(WorklogCli::$args[$i]);
    }
  }
  
  public static function cli($args) {

    // set timezone
    date_default_timezone_set('America/Montreal');

    // set static args variable
    WorklogCli::$original_args = $args;
    WorklogCli::$args = $args;

    // get args for current invoice if there is one
    $invoice_args = WorklogArgs::get_invoice_filter_args();
    if (!empty($invoice_args)) {
      WorklogCli::remove_arg( WorklogArgs::get_invoice_number() );
      WorklogCli::add_args( $invoice_args );
    }
        
  }
  public static function showbox() {
    $box = in_array('--box',WorklogCli::$args);
    return $box; 
  }
  public static function nobox() {
    $nobox = in_array('--nobox',WorklogCli::$args);
    return $nobox; 
  }
  public static function out($output) {
    print WorklogCli::nobox() ? 
      Output::render($output) :
      Output::border_box($output);
  } 
  public static function get_filtered_data_2021($args=[]) {

    static $cached = [];
    $args = @$args ?: WorklogCli::$args;
    $args_key = md5(print_r($args,TRUE));
    if (empty($cached[$args_key])) {
      
      // get worklog file paths
      $worklog_file_paths = WorklogCli::get_worklog_filepaths();

      // parse and filter worklog
      $entries = WorklogEntries::entries($worklog_file_paths);
      $entries = WorklogAugment::add_filterable_data($entries);
      $filtered = WorklogFilter::filter_entries($entries,$args);
      // $filtered = WorklogFilter::filter_parsed($parsed,$args);

      $cached[$args_key] = $filtered;
      
    }
    // return filtered
    return $cached[$args_key];

  }    
  public static function get_worklog_filepaths() {
    
    static $worklog_file_paths = null;
    if (is_null($worklog_file_paths)) {
      
      // use current settings if something was left blank
      $config = JsonConfig::config_get('worklog-config');
      $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
      $worklog_file_paths = []; 

      // alt worklog paths
      foreach(WorklogCli::$args as $arg) {
        $alt_file_path = "$worklog_dir/$arg";
        if (is_file($alt_file_path)) {
          $worklog_file_paths[] = $alt_file_path;
        }
      }

      // if no worklog provided in args, use default worklog
      if (empty($worklog_file_paths)) {
        $worklog_default = $config['worklog']['worklog_default'];
        $worklog_file_paths[] = "$worklog_dir/$worklog_default";
      }
      
    }
    return $worklog_file_paths;
    
  }  
}
