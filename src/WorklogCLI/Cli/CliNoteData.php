<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliNoteData {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get data
    $keys = CLI::$original_args;
    $data = CLI::get_note_data_by_keys($keys);
    
    // output data
    $output = Output::formatted_stardot($data);
    CLI::out($output);

  }
  
}
