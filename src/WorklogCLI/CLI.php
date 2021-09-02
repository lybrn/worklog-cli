<?php
namespace WorklogCLI;
class CLI {

  public static $original_args = [];
  public static $args = [];
  
  public static function original_args() {
    return CLI::$original_args;
  }
  public static function args() {
    return CLI::$args;
  }
  public static function add_args($add = null) {
    if (is_string($add)) CLI::$args[] = $add;
    if (is_array($add)) foreach($add as $arg) CLI::$args[] = $arg;
    return CLI::$args;
  }
  public static function remove_arg($arg_to_remove) {
    // if arg to remove is empty. return
    if (empty($arg_to_remove)) return;
    // make normalized version of arg to remove
    $arg_to_remove_normalized = Format::normalize_key($arg_to_remove);
    // loop through stored args
    foreach(CLI::$args as $i=>$arg) {
      // if this arg is empty, continue
      if (empty($arg)) continue;
      // if this arg matches arg to remove, unset
      if ($arg==$arg_to_remove) unset(CLI::$args[$i]);
      // get normalized copy of this arg
      $arg_normalized = Format::normalize_key($arg);
      // if either normalized version is empty, continue
      if (empty($arg_normalized)) continue;
      if (empty($arg_to_remove_normalized)) continue;
      // if normailize arg matches normalized arg to remove, unset
      if ($arg_normalized==$arg_to_remove_normalized) unset(CLI::$args[$i]);
    }
  }
  public static function cli($args) {

    // set timezone
    date_default_timezone_set('America/Montreal');

    // process arguments
    //$args = array_slice($argv,1);
    //$op = array_shift($args);

    // set static args variable
    CLI::$original_args = $args;
    CLI::$args = $args;

    // get args for current invoice if there is one
    $invoice_args = CLI::get_invoice_filter_args();
    if (!empty($invoice_args)) {
      CLI::remove_arg( CLI::get_invoice_number() );
      CLI::add_args( $invoice_args );
    }
    
    // // call method for operation if there is one
    // $op_method = 'op_'.strtr($op,'-','_');
    // if (method_exists(get_called_class(),$op_method)) {
    //   try {
    //     call_user_func_array(get_called_class().'::'.$op_method,array());
    //   } catch(Exception $e) {
    //     print Output::border_box( $e->getMessage() );
    //   }
    //   return;
    // }
    
    // if we get this far, show usage info
    // CLI::op_usage();

  }
  public static function showbox() {
    $box = in_array('--box',CLI::$args);
    return $box; 
  }
  public static function nobox() {
    $nobox = in_array('--nobox',CLI::$args);
    return $nobox; 
  }
  public static function out($output) {
    print CLI::nobox() ? 
      Output::render($output) :
      Output::border_box($output);
  } 
  public static function get_worklog_filepaths() {
    
    static $worklog_file_paths = null;
    if (is_null($worklog_file_paths)) {
      
      // use current settings if something was left blank
      $config = JsonConfig::config_get('worklog-config');
      $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
      $worklog_file_paths = []; 

      // alt worklog paths
      foreach(CLI::$args as $arg) {
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
  public static function get_template_paths() {
    
    static $template_paths = null;
    if (is_null($template_paths)) {

      // use current settings if something was left blank
      $config = JsonConfig::config_get('worklog-config');
      $template_dir = CLI::root().'/templates';
    
      // alt template paths
      foreach(CLI::$args as $arg) {
        $alt_template_path = "$template_dir/$arg";
        if (is_dir($alt_template_path)) {
          $template_paths[] = $arg;
        }
      }

      // if no template provided in args, use default worklog
      if (empty($template_paths)) {
        $template_default = $config['worklog']['invoice_template_name'];
        $template_paths[] = $template_default;
      }
      
    }
    return $template_paths;

  }  
  public static function get_dump() {

    // get worklog file paths
    $worklog_file_paths = CLI::get_worklog_filepaths();
    
    // parse and filter worklog
    $dump = MDON::parse_files($worklog_file_paths);

    // return filtered
    return $dump;

  }
  public static function get_filtered_data($args=[]) {

    static $cached = [];
    $args = @$args ?: CLI::$args;
    $args_key = md5(print_r($args,TRUE));
    if (empty($cached[$args_key])) {
      
      // get worklog file paths
      $worklog_file_paths = CLI::get_worklog_filepaths();

      // parse and filter worklog
      $parsed = WorklogData::get_data($worklog_file_paths);
      $filtered = WorklogFilter::filter_parsed($parsed,$args);

      $cached[$args_key] = $filtered;
      
    }
    // return filtered
    return $cached[$args_key];

  }
  public static function get_filtered_data_2021($args=[]) {

    static $cached = [];
    $args = @$args ?: CLI::$args;
    $args_key = md5(print_r($args,TRUE));
    if (empty($cached[$args_key])) {
      
      // get worklog file paths
      $worklog_file_paths = CLI::get_worklog_filepaths();

      // parse and filter worklog
      $entries = Worklog2021\WorklogEntries::entries($worklog_file_paths);
      $entries = Worklog2021\WorklogAugment::add_filterable_data($entries);
      $filtered = Worklog2021\WorklogFilter::filter_entries($entries,$args);
      // $filtered = WorklogFilter::filter_parsed($parsed,$args);

      $cached[$args_key] = $filtered;
      
    }
    // return filtered
    return $cached[$args_key];

  }  
  public static function get_note_data() {
    
    static $notedata = null;
    if (is_null($notedata)) {
      
      // get worklog file paths
      $worklog_file_paths = CLI::get_worklog_filepaths();

      // parse and filter worklog
      $notedata = WorklogData::get_note_data($worklog_file_paths);
    
    }
    // return
    return $notedata;
    
  }
  public static function get_note_data_by_keys($keys) {
    
    // note data
    $notedata = CLI::get_note_data();

    // normalized
    static $normalized = [];
    if (empty($normalized)) {        
      // normalized 
      foreach($notedata as $k=>$v) {
        $k_normal = Format::normalize_key($k);
        $normalized[$k_normal] = $k;
      }
    } 

    static $cached = [];
    $args_key = md5(print_r(func_get_args(),TRUE));
    if (empty($cached[$args_key])) {
      
      if (empty($keys)) return [];
      if (!is_array($keys)) $keys = [ $keys ];
      
      // notedata keys
      $normalized_keys = array_keys($normalized);
      
      // return data
      $return = [];
      foreach($keys as $key) {
        $key_normal = Format::normalize_key($key,'*');
        if (preg_match('/\*/i',$key_normal)) {
          $pattern = '/^'.strtr($key_normal,[ '*' => '.*' ]).'$/';
          foreach($normalized_keys as $matchkey) {
            if (preg_match($pattern,$matchkey) && !empty($normalized[$matchkey])) {
              $return[ $normalized[$matchkey] ] = $notedata[ $normalized[$matchkey] ];
            }
          }
        }
        else if (!empty($normalized[$key_normal]))
          $return[ $normalized[$key_normal] ] = $notedata[ $normalized[$key_normal] ];
        
      }
    
      $cached[$args_key] = $return;
    }
    // return filtered
    return $cached[$args_key];

  }  
  public static function get_invoice_number() {

    static $cached_invoice_number = null;
    if (!empty($cached_invoice_number)) return $cached_invoice_number;
    
    // loop through each arg
    foreach(CLI::args() as $key) {
      // normalize key
      $key = Format::normalize_key($key);
      // get any data for this key
      $data = CLI::get_note_data_by_keys($key);
      // if no data found, continue
      if (empty($data)) continue;
      // format array keys on data found
      $data = Format::normalize_array_keys(current($data));
      $data = Format::array_keys_remove_prefix($data,['invoice','work']);
      // get the invoice nmber if there is one
      $data_invoice_number = Format::normalize_key($data['number']) ?: null;
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
    $invoice_number = CLI::get_invoice_number();
    // if no data found, continue
    if (empty($invoice_number)) return null;
    // get any data for this key
    $data = CLI::get_note_data_by_keys($invoice_number);
    // if no data found, continue
    if (empty($data)) return null;
    // format array keys on data found
    $data = Format::normalize_array_keys(current($data));    
    $data = Format::array_keys_remove_prefix($data,[ 'invoice','work' ]);
    // return data
    return $data;
  }
  public static function get_invoice_filter_args() {  
    // get invoice data 
    $invoice_data = CLI::get_invoice_data();
    // build array of filder arguments from invoice data
    $filter_args = [];
    // add client or category
    if (!empty($invoice_data['category'])) 
      $filter_args[] = Format::normalize_key($invoice_data['category']);
    else if (!empty($invoice_data['filter'])) {
      $invoice_filter_args = explode(' ',trim($invoice_data['filter'],' '));
      foreach($invoice_filter_args as $invoice_filter_arg)
        $filter_args[] = Format::normalize_key($invoice_filter_arg);
    }
    else if (!empty($invoice_data['client']))
      $filter_args[] = Format::normalize_key($invoice_data['client']);
    if (!empty($invoice_data['project'])) {
      $projects = explode(' ',$invoice_data['project']);
      foreach($projects as $project) {
        $is_negative = substr($project,0,1)=='-';
        $project = Format::normalize_key($project);
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
  public static function op_args() {
  
    $args = [];
    $args['original_args'] = CLI::$original_args;
    $args['filter_args'] = CLI::$args;
    
    $output = Output::formatted_stardot($args);
    CLI::out($args);
    
  }
  public static function op_usage() {

    // print usage info
    CLI::out("USAGE: worklog [op] [arg1] [arg2]");
    
  }
  public static function op_info() {

    // use current settings if something was left blank
    $worklog_file_paths = CLI::get_worklog_filepaths();

    // info
    $info = [];
    
    // count lines
    foreach($worklog_file_paths as $worklog_file_path) {

      $filename = basename($worklog_file_path);
      $worklog_line_count = 0;
      $handle = fopen($worklog_file_path, "r");
      while(!feof($handle)){ $line = fgets($handle); $worklog_line_count++; }
      fclose($handle);
      $info[ $filename ] = [
        'Path' => $worklog_file_path,
        'Lines' => $worklog_line_count,
      ];
    }

    // output
    $output = Output::formatted_stardot($info);
    CLI::out( $output );

  }
  public static function op_options() {

    // output options found in parameters
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    CLI::out( $options );

  }  
  public static function op_dump() {

    // get dump
    $dump = CLI::get_dump();

    // output data
    $output = Output::formatted_json($dump);
    print $output;

  }
  public static function op_logdata() {

    // get data
    $data = CLI::get_filtered_data();

    // output data
    $output = Output::formatted_json($data);
    print $output;

  }
  public static function op_days() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_days($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_check_days() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = Check::check_days($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }  
  public static function op_daylines() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_day_lines($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_cats() {
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_categories($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_catlines() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_category_lines($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_tasks() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_tasks($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_tasknames() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_task_names($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_tasklines() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_task_lines($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_entries() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_entry_lines($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_statuses() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_statuses($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }  
  public static function op_inbox() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_inbox($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }    
  public static function op_categories() {

    // get categories
    $data = CLI::get_filtered_data();
    $categories = WorklogData::get_categories($data);

    // output categories
    $output = Output::whitespace_table($categories);
    CLI::out( $output );

  }
  public static function op_ytd() {
    
    // get filtered data
    $data = CLI::get_filtered_data();
    // get options
    $options = WorklogFilter::get_options($data,CLI::$args);  
    $fromdate = strtotime(current($options['range']));
    $todate = strtotime(end($options['range']));
    $today = strtotime(date("Y-m-d",time()));
    $now = time();
    $day_in_seconds = 60 * 60 * 24;
    // calculate date differnce
    $period_not_done_yet = $today < $todate;
    $period_days = ($todate-$fromdate) / $day_in_seconds ;
    $period_days = round($period_days,2);
    $ellapsed_days = $period_not_done_yet ? ($today-$fromdate) / $day_in_seconds  : $period_days;
    $ellapsed_days = round($ellapsed_days,2);
    $remaining_days = $period_not_done_yet ? ($todate-$today) / $day_in_seconds : 0;  
    $remaining_days = round($remaining_days,2);
    // default report data 
    $report_data = [];
    $report_data['rate'] = 80.00;
    $report_data['goal'] = 60000.00;
    $report_data['effort'] = 0.0;
    $report_data['billable'] = 0.0;
    $report_data['total'] = 0.0;
    $report_data['multiplier'] = 1.0; 
    $report_data['period_goal'] = $report_data['goal'];
    $report_data['period_days'] = $period_days;
    $report_data['period_weeks'] = $period_days / 7.0; 
    $report_data['ellapsed_days'] = $ellapsed_days;
    $report_data['ellapsed_weeks'] = $ellapsed_days / 7.0;
    $report_data['remainnig_days'] = $remaining_days;
    $report_data['remainnig_weeks'] = $remaining_days / 7.0;
    // loop through rows and build data
    foreach($data as $row) {
      if (is_numeric($row['$']))          $report_data['total'] += $row['$'];
      if (is_numeric($row['hours']))      $report_data['effort'] += $row['hours'];
      if (is_numeric($row['$']))          $report_data['billable'] += $row['$'] / $report_data['rate']; 
    } 
    if ($report_data['effort'] > 0) 
      $report_data['multiplier'] = $report_data['billable'] / $report_data['effort'];
    $report_data['period_goal'] = $report_data['goal'] / 365.00 * $report_data['period_days'];
    $report_data['target_goal'] = $report_data['period_goal'] - $report_data['total'];
    $report_data['target_billable'] = $report_data['target_goal'] /  $report_data['rate'];
    $report_data['target_billable_weekly'] = $report_data['target_billable'] / $report_data['remainnig_weeks']; 
    $report_data['target_effort'] = $report_data['target_billable'] / $report_data['multiplier'];
    $report_data['target_effort_weekly'] = $report_data['target_effort'] / $report_data['remainnig_weeks']; 
    
    $print_data = [];

    // output
    $output = $report_data;
    CLI::out( $output );
    
  }
  public static function op_totals() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_totals($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_titles() {

    // get titles
    $data = CLI::get_filtered_data();
    $titles = WorklogData::get_titles($data);

    // output
    $output = Output::whitespace_table($titles);
    CLI::out( $output );

  }
  public static function op_review() {

    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    $fromdate = strtotime(current($options['range']));
    $todate = strtotime(end($options['range']));
    $rows = WorklogSummary::summary_review1($data,CLI::$args);
    $income = 0;
    foreach($data as $row) {
      $income += $row['$'];
    }
    $income = Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $output_title = $date_title." /// \$$income\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );
    
  }
  public static function op_review2() {

    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    $fromdate = current($options['range']);
    $todate = end($options['range']);
    $rows = WorklogSummary::summary_review2($data,CLI::$args);
    $total = 0.0;
    $mult = 0.0;
    $income = 0.0;
    $count = 0;
    foreach($data as $row) {
      $total += $row['hours'];
      $mult += $row['multiplier'];
      $income += $row['$'];
      if (!empty($row['$'])) $count++;
    }
    $total = Format::format_hours($total);
    $income = Format::format_cost($income);
    $mult = number_format($mult / $count,1);
    if ($fromdate==$todate) {
      $date_title = date('Y-m-d',strtotime($fromdate));
    } else {
      $date_title = date('Y-m-d',strtotime($fromdate))." to ".date('Y-m-d',strtotime($todate));
    }
    $output_title = $date_title." /// \$$income ($total * $mult)\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );


  }
  public static function op_sittings() {

    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    $fromdate = current($options['range']);
    $todate = end($options['range']);
    $rows = WorklogSummary::summary_sittings($data,CLI::$args);
    $total = 0.0;
    $mult = 0.0;
    $income = 0.0;
    $count = 0;
    foreach($data as $row) {
      $total += $row['hours'];
      $mult += $row['multiplier'];
      $income += $row['$'];
      if (!empty($row['$'])) $count++;
    }
    $total = Format::format_hours($total);
    $income = Format::format_cost($income);
    $mult = number_format($mult / $count,1);
    if ($fromdate==$todate) {
      $date_title = date('Y-m-d',strtotime($fromdate));
    } else {
      $date_title = date('Y-m-d',strtotime($fromdate))." to ".date('Y-m-d',strtotime($todate));
    }
    $output_title = $date_title." /// \$$income ($total * $mult)\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );


  }  
  public static function op_billing() {

    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    $fromdate = @strtotime(current($options['range']));
    $todate = @strtotime(end($options['range']));
    $rows = WorklogSummary::summary_billing($data,CLI::$args);
    $income = 0;
    $hours = 0;
    foreach($data as $row) {
      $income += $row['$'];
      $started_at = strtotime($row['started_at']);
      if (empty($earliest) || $started_at < $earliest)
        $earliest = $started_at;
      if (empty($latest) || $started_at > $latest)
        $latest = $started_at;
      $hours += $row['total'];
    }
    if (empty($fromdate)) $fromdate = $earliest;
    if (empty($todate)) $todate = $latest;
    
    $income = Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $normalized_hours = Format::format_hours($income / 80.0); 
    $output_title = $date_title." /// $hours /// \$$income -- $normalized_hours @ $80\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_times() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_times($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_brackets() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_brackets($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_notes() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_notes($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_queued() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_queued($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }  
  public static function op_queued_stardot() {

    // build summary
    $data = CLI::get_filtered_data();
    $grouped = WorklogSummary::summary_queued_by_title($data,CLI::$args);

    // output
    $output = Output::formatted_stardot($grouped);
    CLI::out( $output );

  }    
  public static function op_markdown() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_markdown($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_render() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_markdown($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_catinfo() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_category_info($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
  }
  public static function op_notedatadump() {

    // get data
    $data = CLI::get_note_data();

    // output data
    $output = Output::formatted_json($data);
    print $output;

  }    
  public static function op_notedata() {

    // get data
    $keys = CLI::$original_args;
    $data = CLI::get_note_data_by_keys($keys);
    

    // output data
    $output = Output::formatted_stardot($data);
    CLI::out($output);

  }  
  public static function op_notedatatable() {

    // get data
    $keys = CLI::$original_args[0];
    $typekey = CLI::$original_args[1];
    $data = CLI::get_note_data_by_keys($keys);
    
    // shape data to type
    $typedata = current(CLI::get_note_data_by_keys($typekey));
    $data = Format::array_shape_rows($data,$typedata);
    
    // output data
    $output = Output::whitespace_table($data,TRUE);
    CLI::out($output);

  }  
  public static function get_note_data_normalized($key,$assignkey=null) {
    $data = CLI::get_note_data_by_keys($key);
    if (empty($data))
      throw new Exception("No data for key: $key");
    
    // if (!empty($assignkey))  
    //   foreach($data as $k=>$v) { $data[$k][$assignkey] = $k; }

    $assignkey = Format::normalize_key($assignkey,'-_ ./','-');
    $data = Format::normalize_array_keys($data,'-_ ./','-',TRUE);
    
    if ($assignkey) {
      $data = Format::array_keys_remove_prefix($data,$assignkey.'-',TRUE);
      $data = array_values($data);
      $data = [ $assignkey => current($data) ];
    }
    return $data;
  }
  public static function op_data() {

    // get data
    $assignkey = null;
    $keys = CLI::$original_args[0];
    if (strpos($keys,':')!==FALSE) {
      $assignkey = current(explode(':',$keys));
      $keys = end(explode(':',$keys));
    }
    
    $data = CLI::get_note_data_normalized($keys,$assignkey);
    $data = Format::array_flatten($data);
    //$data = Format::array_extrude($data);
    print_r($data);
    
    // // shape data to type
    // $typedata = current(CLI::get_note_data_by_keys($typekey));
    // $data = Format::array_shape_rows($data,$typedata);    
    // // output data
    // $output = Output::whitespace_table($data,TRUE);
    // CLI::out($output);
    
  }
  
  public static function op_invoiceyaml() {

    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_invoice($data,CLI::$args);

    // output
    $output = Output::formatted_yaml($rows);
    CLI::out( $output );

  }
  public static function op_invoice() {

    // build summary
    $data = CLI::get_filtered_data();
    $yaml_data = WorklogSummary::summary_invoice($data,CLI::$args);

    // twig
    $saved = JsonConfig::config_get('worklog-config');
    $twigfile = $saved['worklog']['invoice_template'];
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    print Twig::process($twig,$vars)."\n";

  }
  public static function op_invoice2() {

    $section = CLI::$args[0] ?: null;
    
    // build summary
    $yaml_data = WorklogSummary::summary_invoice2();
    $subset = [];
    foreach(CLI::$args as $arg) {
      if (array_key_exists($arg, $yaml_data)) 
        $subset[$arg] = $yaml_data[$arg];  
    }
    if (!empty($subset)) $yaml_data = $subset;
    $output = Output::formatted_yaml($yaml_data);
    CLI::out( $output );

  }
  public static function op_logexport() {
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_logexport($data,CLI::$args);

    // output
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );
    
  }
  public static function op_invoice2html() {

    // build summary
    $invoice_data = CLI::get_invoice_data();
    $yaml_data = WorklogSummary::summary_invoice2();
    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    // markdown twig template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md.twig';
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    $markdown = Twig::process($twig,$vars)."\n";

    // output
    $output = Output::markdown_html($markdown);
    $sections = explode("<hr/>",$output);
    
    // get file contents
    $scan_dir = 'templates/'.$invoice_template_name;
    $scanned = Scan::scan(CLI::root(),$scan_dir,['return_files'=>TRUE]);
    $file_contents = [];
    foreach($scanned as $file) {
      $key = basename($file);
      $contents = file_get_contents(CLI::root().'/'.$file);
      $file_contents[$key] = $contents;
    }

    // markdown html template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.html.twig';
    $twig = file_get_contents($twigfile);
    $vars = array('sections'=>$sections);
    $vars['files'] = $file_contents;
    $html = Twig::process($twig,$vars)."\n";
    $output =  Output::formatted_html($html);

    print $output;

  }  
  public static function op_invoicehtml() {

    // build summary
    $data = CLI::get_filtered_data();
    $yaml_data = WorklogSummary::summary_invoice($data,CLI::$args);
    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = current( CLI::get_template_paths() ); 

    // markdown twig template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md.twig';
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    $markdown = Twig::process($twig,$vars)."\n";

    // output
    $output = Output::markdown_html($markdown);
    $sections = explode("<hr/>",$output);

    // markdown html template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.html.twig';
    $twig = file_get_contents($twigfile);
    $vars = array('sections'=>$sections);
    $html = Twig::process($twig,$vars)."\n";
    $output =  Output::formatted_html($html);

    print $output;


  }
  public static function op_rangedata() {

    $data = CLI::get_filtered_data();
    $data =  WorklogSummary::summary_rangedata($data,CLI::$args);
    print_r($data);

  }        
  public static function op_template_md() {

    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    $markdownfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md';
    $markdowncontent = file_get_contents($markdownfile);
        
    print $markdowncontent;
    
  }    
  public static function op_template_data() {

    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    $yamlfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.yaml';
    $yamlcontent = file_get_contents($yamlfile);  
    $yamldata = YAML::decode($yamlcontent);
    $yamldata = Format::array_flatten($yamldata);
    $yamldata = Format::array_extrude($yamldata);
    
    print_r($yamldata);

  }      
  public static function op_template_maketwig() {

    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    $markdownfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md';
    $markdowncontent = file_get_contents($markdownfile);
    $yamlfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.yaml';
    $yamlcontent = file_get_contents($yamlfile);  
    $yamldata = YAML::decode($yamlcontent);

    $twig = Format::maketwig($markdowncontent,$yamldata);
    print_r($twig);

  }    
  public static function op_template_out() {

    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    $markdownfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md';
    $markdowncontent = file_get_contents($markdownfile);
    $yamlfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.yaml';
    $yamlcontent = file_get_contents($yamlfile);  
    $yamldata = YAML::decode($yamlcontent);

    $twig = Format::maketwig($markdowncontent,$yamldata);
    $data = CLI::get_filtered_data();
    $rangedata =  WorklogSummary::summary_rangedata($data,CLI::$args);
    $rangedata = Format::array_flatten($rangedata);
    $rangedata = Format::array_extrude($rangedata);
    
    print Twig::process($twig,$rangedata)."\n";
    
    //print_r($twig);

  }          
  public static function op_invoiceexport() {

    // build summary
    $data = CLI::get_filtered_data();
    $yaml_data = WorklogSummary::summary_invoice($data,CLI::$args);
    $invoice_number = $yaml_data['invoice']['number'];
    if (empty($invoice_number)) die('Invoice number is empty');

    ob_start();
    CLI::op_invoicehtml();
    $output = ob_get_clean();

    file_put_contents("$invoice_number.html",$output);

  }
  public static function op_invoice2export() {

    // build summary
    $yaml_data = WorklogSummary::summary_invoice2();
    $invoice_number = $yaml_data['invoice']['number'];
    if (empty($invoice_number)) die('Invoice number is empty');
    
    ob_start();
    CLI::op_invoice2html();
    $output = ob_get_clean();
    
    file_put_contents("$invoice_number.html",$output);

  }  
  public static function op_config() {

    // ask for app details
    $worklog_dir = readline("Enter path to worklogs directory (enter to skip): ");
    $worklog_default = readline("Enter default worklog (enter to skip): ");
    $invoice_template_name = readline("Enter default invoice template name (enter to skip): ");

    // use current settings if something was left blank
    $current = JsonConfig::config_get('worklog-config');
    if (empty($worklog_dir) && !empty($current['worklog']['worklog_dir']))
      $worklog_dir = $current['worklog']['worklog_dir'];
    if (empty($worklog_default) && !empty($current['worklog']['worklog_default']))
      $worklog_default = $current['worklog']['worklog_default'];
    if (empty($invoice_template_name) && !empty($current['worklog']['invoice_template_name']))
      $invoice_template_name = $current['worklog']['invoice_template_name'];

    // save config file
    JsonConfig::config_set('worklog-config',array(
      'worklog'=>array(
        'worklog_dir'=>$worklog_dir,
        'worklog_default'=>$worklog_default,
        'invoice_template_name'=>$invoice_template_name,
      ),
    ));

    // display saved data
    $saved = JsonConfig::config_get('worklog-config');
    print 'worklog-config.json: ';
    print_r($saved);

  }
  // public static function args_get_date() {
  //   foreach(CLI::$args as $arg) {
  //     if (strtotime($arg)!=0) {
  //       return date('Y-m-d',strtotime($arg));
  //     }
  //   }
  // }
  // public static function args_get_month() {
  //   $months = array(
  //     'january'    => '01',
  //     'february'   => '02',
  //     'march'      => '03',
  //     'april'      => '04',
  //     'may'        => '05',
  //     'june'       => '06',
  //     'july'       => '07',
  //     'august'     => '08',
  //     'september'  => '09',
  //     'october'    => '10',
  //     'november'   => '11',
  //     'december'   => '12',
  //   );
  //   foreach(CLI::$args as $arg) {
  //     $arg = strtolower($arg);
  //     if (!empty($months[$arg])) {
  //       return date('Y').'-'.$months[$arg];
  //     }
  //   }
  // }
  public function root() {
    $script_location = $_SERVER['SCRIPT_FILENAME'];
    $script_location = realpath($script_location);
    $script_location = dirname($script_location);
    return $script_location;
  }

}
