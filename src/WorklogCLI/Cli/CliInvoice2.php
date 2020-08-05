<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliInvoice2 {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
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
  
}
