<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliTitles {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get titles
    $data = CLI::get_filtered_data();
    $titles = WorklogData::get_titles($data);

    // output
    $output = Output::whitespace_table($titles);
    CLI::out( $output );

  }
  
}
