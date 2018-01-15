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
  public static function get_filtered_data($args) {
    
    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_default = $config['worklog']['worklog_default'];
    $worklog_file_path = "$worklog_dir/$worklog_default";
    
    // parse and filter worklog
    $parsed = \WorklogCLI\WorklogData::get_data($worklog_file_path);
    $filtered = \WorklogCLI\WorklogFilter::filter_parsed($parsed,$args);
    
    // return filtered
    return $filtered;
    
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
  public static function op_dump() {
    
    // use current settings if something was left blank
    $config = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $worklog_dir = rtrim($config['worklog']['worklog_dir'],'/');
    $worklog_default = $config['worklog']['worklog_default'];
    $worklog_file_path = "$worklog_dir/$worklog_default";
    
    $parsed = \WorklogCLI\MDON::parse_file($worklog_file_path);
    
    print \WorklogCLI\Output::formatted_json($parsed);
    
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
    $rows = \WorklogCLI\WorklogSummary::summary_review($data,$args);
    $total = 0;
    $income = 0;
    foreach($rows as $row) {
      $total += $row['total'];
      $income += $row['$'];
    }
    $today = date('l F jS Y',strtotime($date));
    $output = $today." ($total hours) ($$income)\n";
    $output .=  str_repeat('=',strlen($today))."\n\n";
    
    // output
    $output .= \WorklogCLI\Output::whitespace_table($rows);
    print \WorklogCLI\Output::border_box($output);
    $options = \WorklogCLI\WorklogFilter::get_options($parsed,$args);
    print \WorklogCLI\Output::border_box($options);
    
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
  public static function op_invoicehtml($args) {
    
    // build summary`
    $data = CLI::get_filtered_data($args);
    $yaml_data = \WorklogCLI\WorklogSummary::summary_invoice($data,$args);
    
    // markdown twig template
    $saved = \WorklogCLI\JsonConfig::config_get('worklog-config');
    $twigfile = $saved['worklog']['invoice_template_md'];
    $twig = file_get_contents($twigfile);
    $vars = $yaml_data;
    $markdown = \WorklogCLI\Twig::process($twig,$vars)."\n";

    // output
    $output = \WorklogCLI\Output::markdown_html($markdown);
    $sections = explode("<hr/>",$output);
    
    // markdown html template
    $twigfile = $saved['worklog']['invoice_template_html'];
    $twig = file_get_contents($twigfile);
    $vars = array('sections'=>$sections);
    $html = \WorklogCLI\Twig::process($twig,$vars)."\n";
    $output =  \WorklogCLI\Output::formatted_html($html);

    print $output;
    
    
  }  
             
  public static function op_config() {
    
    // ask for app details
    $worklog_dir = readline("Enter path to worklogs directory (enter to skip): ");
    $worklog_default = readline("Enter default worklog (enter to skip): ");
    $invoice_template_md = readline("Enter default invoice md template (enter to skip): ");
    $invoice_template_html = readline("Enter default invoice html template (enter to skip): ");
        
    // use current settings if something was left blank
    $current = \WorklogCLI\JsonConfig::config_get('worklog-config');
    if (empty($worklog_dir) && !empty($current['worklog']['worklog_dir']))
      $worklog_dir = $current['worklog']['worklog_dir'];
    if (empty($worklog_default) && !empty($current['worklog']['worklog_default']))
      $worklog_default = $current['worklog']['worklog_default'];
    if (empty($invoice_template_md) && !empty($current['worklog']['invoice_template_md']))
      $invoice_template_md = $current['worklog']['invoice_template_md'];
    if (empty($invoice_template_html) && !empty($current['worklog']['invoice_template_html']))
      $invoice_template_html = $current['worklog']['invoice_template_html'];
    
    // save config file
    \WorklogCLI\JsonConfig::config_set('worklog-config',array(
      'worklog'=>array(
        'worklog_dir'=>$worklog_dir,
        'worklog_default'=>$worklog_default,
        'invoice_template_md'=>$invoice_template_md,
        'invoice_template_html'=>$invoice_template_html,
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
  
}