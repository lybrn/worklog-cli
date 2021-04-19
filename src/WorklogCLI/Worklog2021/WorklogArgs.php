<?php
namespace WorklogCLI\Worklog2021;
class WorklogArgs {

  public static $original_args = [];
  public static $args = [];

  public static function original_args() {
    return WorklogArgs::$original_args;
  }
  public static function args() {
    return WorklogArgs::$args;
  }
  public static function add_args($add = null) {
    if (is_string($add)) WorklogArgs::$args[] = $add;
    if (is_array($add)) foreach($add as $arg) WorklogArgs::$args[] = $arg;
    return WorklogArgs::$args;
  }
  public static function remove_arg($arg_to_remove) {
    // if arg to remove is empty. return
    if (empty($arg_to_remove)) return;
    // make normalized version of arg to remove
    $arg_to_remove_normalized = Format::normalize_key($arg_to_remove);
    // loop through stored args
    foreach(WorklogArgs::$args as $i=>$arg) {
      // if this arg is empty, continue
      if (empty($arg)) continue;
      // if this arg matches arg to remove, unset
      if ($arg==$arg_to_remove) unset(WorklogArgs::$args[$i]);
      // get normalized copy of this arg
      $arg_normalized = Format::normalize_key($arg);
      // if either normalized version is empty, continue
      if (empty($arg_normalized)) continue;
      if (empty($arg_to_remove_normalized)) continue;
      // if normailize arg matches normalized arg to remove, unset
      if ($arg_normalized==$arg_to_remove_normalized) unset(WorklogArgs::$args[$i]);
    }
  }
  public static function get_filtered_data_2021($args=[]) {

    static $cached = [];
    $args = @$args ?: WorklogArgs::$args;
    $args_key = md5(print_r($args,TRUE));
    if (empty($cached[$args_key])) {
      
      // get worklog file paths
      $worklog_file_paths = WorklogArgs::get_worklog_filepaths();

      // parse and filter worklog
      $parsed = Worklog2021\WorklogEntries::entries($worklog_file_paths);
      $filtered = $parsed;
      // $filtered = WorklogFilter::filter_parsed($parsed,$args);

      $cached[$args_key] = $filtered;
      
    }
    // return filtered
    return $cached[$args_key];

  }  
  
}
