<?php
namespace WorklogCLI\Cli;
use Lybrnth\Mdon;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
use WorklogCLI\WorklogData;
class CliWorklogDecode {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get data
    CLI::get_filtered_data();
    
    $data = WorklogData::$storage;

    $output = Output::formatted_json($data);
    CLI::out($output);
  
  }
  
}
