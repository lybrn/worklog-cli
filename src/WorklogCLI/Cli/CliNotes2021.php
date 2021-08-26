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
class CliNotes2021 {
  
  public static function cli($args) {
  
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $data = WorklogAugment::add_augmented_data($data);
    $data = WorklogAugment::add_date_data($data);
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_timetracking_data($data);
    $data = WorklogAugment::add_effortcost_data($data);
    $data = WorklogAugment::add_billingcost_data($data);
    $data = WorklogAugment::add_render_data($data);
    
    //$data = WorklogAugment::add_notes_data($data);
    //$data = self::add_cli_totals_data($data);
    
    $report = WorklogReports::report($data,[
      'line' => 'rendered/render_line_number',
      'date' => 'day_timestamp_ymd',
      'client' => 'client_name',
      'text' => 'rendered/render_text',
      'mult' => 'billingcost_multiplier',
      'total' => 'billingcost_tracked_hours',    
    ]);    
    
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
  
}
