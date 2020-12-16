<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
use WorklogCLI\JsonConfig;
class CliConfig {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
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
  
}
