<?php
namespace WorklogCLI\Cli;
use Lybrnth\Mdon;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliMdonDecode {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get worklog file paths
    $worklog_file_paths = CLI::get_worklog_filepaths();
    
    // parse and filter worklog
    $dump = MDON::decode_files_get_stack($worklog_file_paths);

    $output = Output::formatted_json($dump);
    CLI::out($output);

    
  
  }
  
}
