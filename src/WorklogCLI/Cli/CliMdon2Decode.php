<?php
namespace WorklogCLI\Cli;
use Lybrnth\Mdon;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliMdon2Decode {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get worklog file paths
    $worklog_file_paths = CLI::get_worklog_filepaths();
    
    // parse and filter worklog
    $dump = Mdon::decode2($worklog_file_paths);

    $output = Output::formatted_json($dump);
    CLI::out($output);

    
  
  }
  
}
