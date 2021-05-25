<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliNotes {
  
  public static function cli($args) {
  
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $data = WorklogAugment::add_client_data($data);
    // $data = WorklogAugment::add_timetracking_data($data);
    // $data = WorklogAugment::add_effortcost_data($data);
    // $data = WorklogAugment::add_billingcost_data($data);
    // 
    // //$data = WorklogAugment::add_notes_data($data);
    // //$data = self::add_cli_totals_data($data);
    // 
    // $report = WorklogReports::report($data,[
    //   'line' => 'note_rows/note_line_number',
    //   'client' => 'client_tight_name',
    //   'title' => 'title_text_nobrackets',
    //   'note' => 'note_rows/note_text_nobrackets',
    //   'mult' => 'billingcost_multiplier',
    //   'total' => 'billingcost_total_cost',    
    // ]);    
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );
      
    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_notes($data,CLI::$args);
    
    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );

  }
  
  
}
