<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\Worklog2021\WorklogCli;
use WorklogCLI\Worklog2021\WorklogInvoice;
use WorklogCLI\Worklog2021\WorklogArgs;
use WorklogCLI\Output;
class CliInvoiceData {
  
  public static function cli($args) {
    
    WorklogCli::cli($args);
    
    $section = WorklogCli::$args[0] ?: null;

    // build summary
    $yaml_data = WorklogInvoice::invoice_data();
    $subset = [];
    foreach(WorklogCli::$args as $arg) {
      if (array_key_exists($arg, $yaml_data)) 
        $subset[$arg] = $yaml_data[$arg];  
    }
    if (!empty($subset)) $yaml_data = $subset;
    $output = Output::formatted_yaml($yaml_data);
    WorklogCli::out( $output );

  }
  
}
