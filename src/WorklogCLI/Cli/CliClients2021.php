<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliClients2021 {
  
  public static function cli($args) {      
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_timetracking_data($data);
    $data = WorklogAugment::add_effortcost_data($data);
        
    $report = WorklogReports::report($data,[
      'client' => 'client_tight_name',
      'short' => 'client_short_name',
      'fullname' => 'client_full_name',
      'cli' => 'client_cli_name',
      'sittings' => 'WorklogAugment::count',
      'effort' => 'timetracking_tracked_hours/WorklogAugment::sum',
      'total' => 'effortcost_total_cost/WorklogAugment::sum',
    ]);
    
    if (in_array('$',$args)) {
      $report = WorklogReports::filter($report,[
        'total' => true,
      ]);
    }
    
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
}
