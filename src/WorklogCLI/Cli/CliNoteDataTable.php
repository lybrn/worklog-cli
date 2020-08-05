<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliNoteDataTable {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // get data
    $keys = CLI::$original_args[0];
    $typekey = CLI::$original_args[1];
    $data = CLI::get_note_data_by_keys($keys);
    
    // shape data to type
    $typedata = current(CLI::get_note_data_by_keys($typekey));
    $data = Format::array_shape_rows($data,$typedata);
    
    // output data
    $output = Output::whitespace_table($data,TRUE);
    CLI::out($output);

  }
  
}
