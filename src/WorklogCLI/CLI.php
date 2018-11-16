<?php
namespace WorklogCLI;
class CLI {

  private static $args = [];
  
  public static function args() {
    return CLI::$args;
  }
  public static function cli($argv) {

    // set timezone
    date_default_timezone_set('America/Montreal');

    // process arguments
    $args = array_slice($argv,1);
    $op = array_shift($args);

    // set static args variable
    CLI::$args = $args;

    // call method for operation if there is one
    $op_method = 'op_'.$op;
    if (method_exists(get_called_class(),$op_method)) {
      call_user_func_array(get_called_class().'::'.$op_method,array());
      return;
    }
    
    // if we get this far, show usage info
    CLI::op_usage();

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
    
    return $worklog_file_paths;
  }
  public static function get_template_paths() {
    
    // use current settings if something was left blank
    $config = JsonConfig::config_get('worklog-config');
    $template_dir = CLI::root().'/templates';
    
    $template_paths = []; 

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
  public static function get_filtered_data() {

    // get worklog file paths
    $worklog_file_paths = CLI::get_worklog_filepaths();

    // parse and filter worklog
    $parsed = WorklogData::get_data($worklog_file_paths);
    $filtered = WorklogFilter::filter_parsed($parsed,CLI::$args);

    // return filtered
    return $filtered;

  }
  public static function get_note_data($keys) {

    if (empty($keys)) return [];
    if (!is_array($keys)) $keys = [ $keys ];
    
    // get worklog file paths
    $worklog_file_paths = CLI::get_worklog_filepaths();

    // parse and filter worklog
    $notedata = WorklogData::get_note_data($worklog_file_paths);

    // normalized 
    $normalized = [];
    foreach($notedata as $k=>$v) {
      $k_normal = Format::normalize_key($k);
      $normalized[$k_normal] = $k;
    }
    
    // return data
    $return = [];
    foreach($keys as $key) {
      $key_normal = Format::normalize_key($key);
      if (!empty($normalized[$key_normal]))
        $return[ $normalized[$key_normal] ] = $notedata[ $normalized[$key_normal] ];
      
    }
    // return filtered
    return $return;

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
    $output = Output::formatted_json($data);
    print $output;

  }
  public static function op_data() {

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
  public static function op_categories() {

    // get categories
    $data = CLI::get_filtered_data();
    $categories = WorklogData::get_categories($data);

    // output categories
    $output = Output::whitespace_table($categories);
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

    // build summary`
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

    // build summary`
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
  public static function op_billing() {

    // build summary`
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::$args);
    $fromdate = strtotime(current($options['range']));
    $todate = strtotime(end($options['range']));
    $rows = WorklogSummary::summary_billing($data,CLI::$args);
    $income = 0;
    $hours = 0;
    foreach($data as $row) {
      $income += $row['$'];
      $hours += $row['total'];
    }
    $income = Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $output_title = $date_title." /// $hours /// \$$income\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_times() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_times($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_brackets() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_brackets($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_notes() {

    // build summary`
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
  public static function op_markdown() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_markdown($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_render() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_markdown($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  public static function op_catinfo() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_category_info($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
  }
  public static function op_notedata() {

    // get data
    $keys = CLI::$args;
    $data = CLI::get_note_data($keys);

    // output data
    $output = Output::formatted_stardot($data);
    CLI::out($output);

  }  
  public static function op_invoiceyaml() {

    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_invoice($data,CLI::$args);

    // output
    $output = Output::formatted_yaml($rows);
    CLI::out( $output );

  }
  public static function op_invoice() {

    // build summary`
    $data = CLI::get_filtered_data();
    $yaml_data = WorklogSummary::summary_invoice($data,CLI::$args);

    // twig
    $saved = JsonConfig::config_get('worklog-config');
    $twigfile = $saved['worklog']['invoice_template'];
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    print Twig::process($twig,$vars)."\n";

  }
  public static function op_logexport() {
    
    // build summary`
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_logexport($data,CLI::$args);

    // output
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );
    
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
  public static function op_invoiceexport() {

    // build summary`
    $data = CLI::get_filtered_data();
    $yaml_data = WorklogSummary::summary_invoice($data,CLI::$args);
    $invoice_number = $yaml_data['invoice']['number'];
    if (empty($invoice_number)) die('Invoice number is empty');

    ob_start();
    CLI::op_invoicehtml();
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
