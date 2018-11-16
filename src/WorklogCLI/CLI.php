<?php
namespace WorklogCLI;
class CLI {

  public static function cli($argv) {

    // set timezone
    date_default_timezone_set('America/Montreal');

    // process arguments
    $args = array_slice($argv,1);
    $op = array_shift($args);

    // call method for operation if there is one
    $op_method = 'op_'.$op;
    if (method_exists(get_called_class(),$op_method)) {
      call_user_func_array(get_called_class().'::'.$op_method,array($args));
      return;
    }

    // if we get this far, show usage info
    CLI::op_usage($args);

  }
  public static function get_dump($args) {

    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_file_paths = []; 

    // alt worklog paths
    foreach($args as $arg) {
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

    // parse and filter worklog
    $dump = \WorklogCLI\MDON::parse_files($worklog_file_paths);

    // return filtered
    return $dump;

  }
  public static function get_filtered_data($args) {

    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_file_paths = []; 

    // alt worklog paths
    foreach($args as $arg) {
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

    // parse and filter worklog
    $parsed = \WorklogCLI\WorklogData::get_data($worklog_file_paths);
    $filtered = \WorklogCLI\WorklogFilter::filter_parsed($parsed,$args);

    // return filtered
    return $filtered;

  }
  public static function get_note_data($args) {

    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_file_paths = []; 

    // alt worklog paths
    foreach($args as $arg) {
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

    // parse and filter worklog
    $notedata = \WorklogCLI\WorklogData::get_note_data($worklog_file_paths);

    // normalized 
    $normalized = [];
    foreach($notedata as $k=>$v) {
      $k_normal = \WorklogCLI\Format::normalize_key($k);
      $normalized[$k_normal] = $k;
    }
    // return data
    $return = [];
    foreach($args as $arg) {
      $arg_normal = \WorklogCLI\Format::normalize_key($arg);
      if (!empty($normalized[$arg_normal]))
        $return[ $normalized[$arg_normal] ] = $notedata[ $normalized[$arg_normal] ];
      
    }
    // return filtered
    return $return;

  }  
  public static function op_usage($args) {

    // print usage info
    print "USAGE: worklog [op] [arg1] [arg2]\n";

    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_default = $config['worklog']['worklog_default'];
    $worklog_file_path = "$worklog_dir/$worklog_default";

    $parsed = \WorklogCLI\WorklogData::get_data($worklog_file_path);

    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);

    if (!empty($options)) {
      print \WorklogCLI\Output::border_box($options);
    }


  }
  public static function op_info() {

    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_default = $config['worklog']['worklog_default'];
    $worklog_file_path = "$worklog_dir/$worklog_default";

    // count lines
    $worklog_line_count = 0;
    $handle = fopen($worklog_file_path, "r");
    while(!feof($handle)){ $line = fgets($handle); $worklog_line_count++; }
    fclose($handle);

    // output
    print \WorklogCLI\Output::border_box(array(
      'Path' => $worklog_file_path,
      'Lines' => $worklog_line_count,
    ));

  }
  public static function op_options($args) {

    // output options found in parameters
    $data = CLI::get_filtered_data($args);
    $options = \WorklogCLI\WorklogFilter::get_options($data,$args);
    print \WorklogCLI\Output::border_box($options);

  }  
  public static function op_dump($args) {

    $dump = CLI::get_dump($args);

    print \WorklogCLI\Output::formatted_json($dump);

  }

  public static function op_data($args) {

    // get data
    $data = CLI::get_filtered_data($args);

    // output data
    $output = Output::formatted_json($data);
    print $output;
    //print Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($data,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_days($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_days($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_daylines($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_day_lines($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_cats($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_categories($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_catlines($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_category_lines($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_tasks($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_tasks($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_tasknames($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_task_names($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_tasklines($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_task_lines($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_entries($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_entry_lines($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_categories($args) {

    // get categories
    $data = CLI::get_filtered_data($args);
    $categories = \WorklogCLI\WorklogData::get_categories($data);

    // output categories
    $output = Output::whitespace_table($categories);
    print Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_totals($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_totals($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_titles($args) {

    // get titles
    $data = CLI::get_filtered_data($args);
    $titles = \WorklogCLI\WorklogData::get_titles($data);

    // output titles
    $output = Output::whitespace_table($titles);
    print Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_review($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $options = \WorklogCLI\WorklogFilter::get_options($data,$args);
    $fromdate = strtotime(current($options['range']));
    $todate = strtotime(end($options['range']));
    $rows = \WorklogCLI\WorklogSummary::summary_review1($data,$args);
    $income = 0;
    foreach($data as $row) {
      $income += $row['$'];
    }
    $income = \WorklogCLI\Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $output_title = $date_title." /// \$$income\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_review2($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $options = \WorklogCLI\WorklogFilter::get_options($data,$args);
    $fromdate = current($options['range']);
    $todate = end($options['range']);
    $rows = \WorklogCLI\WorklogSummary::summary_review2($data,$args);
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
    $total = \WorklogCLI\Format::format_hours($total);
    $income = \WorklogCLI\Format::format_cost($income);
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
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_billing($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $options = \WorklogCLI\WorklogFilter::get_options($data,$args);
    $fromdate = strtotime(current($options['range']));
    $todate = strtotime(end($options['range']));
    $rows = \WorklogCLI\WorklogSummary::summary_billing($data,$args);
    $income = 0;
    $hours = 0;
    foreach($data as $row) {
      $income += $row['$'];
      $hours += $row['total'];
    }
    $income = \WorklogCLI\Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $output_title = $date_title." /// $hours /// \$$income\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }
  public static function op_times($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_times($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_brackets($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_brackets($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_notes($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_notes($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_queued($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_queued($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);

  }  
  public static function op_markdown($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_markdown($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    //print $output;
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_render($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_markdown($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    //print $output;
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_catinfo($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_category_info($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    //print $output;
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_notedata($args) {

    // get data
    $data = CLI::get_note_data($args);

    // output data
    $output = Output::formatted_stardot($data);
    print \WorklogCLI\Output::border_box($output);

  }  
  public static function op_invoiceyaml($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_invoice($data,$args);

    // output
    $output = \WorklogCLI\Output::formatted_yaml($rows);
//    print $output;
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function op_invoice($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $yaml_data = \WorklogCLI\WorklogSummary::summary_invoice($data,$args);

    // twig
    $saved = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $twigfile = $saved['worklog']['invoice_template'];
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    print \WorklogCLI\Twig::process($twig,$vars)."\n";

  }
  public static function op_logexport($args) {
    
    // build summary`
    $data = CLI::get_filtered_data($args);
    $rows = \WorklogCLI\WorklogSummary::summary_logexport($data,$args);

    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    //print $output;
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);

  }
  public static function get_output_template($args) {
    
    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $template_dir = CLI::root().'/templates';
    
    $template_paths = []; 

    // alt template paths
    foreach($args as $arg) {
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
    
    return current($template_paths);

  }
  public static function op_invoicehtml($args) {

    // build summary
    $data = CLI::get_filtered_data($args);
    $yaml_data = \WorklogCLI\WorklogSummary::summary_invoice($data,$args);
    $saved = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $invoice_template_name = CLI::get_output_template($args); 

    // markdown twig template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md.twig';
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    $markdown = \WorklogCLI\Twig::process($twig,$vars)."\n";

    // output
    $output = \WorklogCLI\Output::markdown_html($markdown);
    $sections = explode("<hr/>",$output);

    // markdown html template
    $twigfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.html.twig';
    $twig = file_get_contents($twigfile);
    $vars = array('sections'=>$sections);
    $html = \WorklogCLI\Twig::process($twig,$vars)."\n";
    $output =  \WorklogCLI\Output::formatted_html($html);

    print $output;


  }
  public static function op_invoiceexport($args) {

    // build summary`
    $data = CLI::get_filtered_data($args);
    $yaml_data = \WorklogCLI\WorklogSummary::summary_invoice($data,$args);
    $invoice_number = $yaml_data['invoice']['number'];
    if (empty($invoice_number)) die('Invoice number is empty');

    ob_start();
    CLI::op_invoicehtml($args);
    $output = ob_get_clean();

    file_put_contents("$invoice_number.html",$output);

  }

  public static function op_config() {

    // ask for app details
    $worklog_dir = readline("Enter path to worklogs directory (enter to skip): ");
    $worklog_default = readline("Enter default worklog (enter to skip): ");
    $invoice_template_name = readline("Enter default invoice template name (enter to skip): ");

    // use current settings if something was left blank
    $current = \WorklogCLI\JsonConfig::config_get('worklog-config');
    if (empty($worklog_dir) && !empty($current['worklog']['worklog_dir']))
      $worklog_dir = $current['worklog']['worklog_dir'];
    if (empty($worklog_default) && !empty($current['worklog']['worklog_default']))
      $worklog_default = $current['worklog']['worklog_default'];
    if (empty($invoice_template_name) && !empty($current['worklog']['invoice_template_name']))
      $invoice_template_name = $current['worklog']['invoice_template_name'];

    // save config file
    \WorklogCLI\JsonConfig::config_set('worklog-config',array(
      'worklog'=>array(
        'worklog_dir'=>$worklog_dir,
        'worklog_default'=>$worklog_default,
        'invoice_template_name'=>$invoice_template_name,
      ),
    ));

    // display saved data
    $saved = \WorklogCLI\JsonConfig::config_get('worklog-config');
    print 'worklog-config.json: ';
    print_r($saved);

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
  public function root() {
    $script_location = $_SERVER['SCRIPT_FILENAME'];
    $script_location = realpath($script_location);
    $script_location = dirname($script_location);
    return $script_location;
  }

}
