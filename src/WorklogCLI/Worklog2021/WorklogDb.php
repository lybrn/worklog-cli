<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\CLI;
class WorklogDb {

  public static function db($key) {
    
    return CLI::get_note_data_by_keys($key);
    
  }

}
