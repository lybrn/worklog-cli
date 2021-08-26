<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliNotes {
  
  public static function cli($args) {

    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_notes($data,CLI::$args);
    
    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  
  
}
