<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\Format;
use WorklogCLI\Output;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliBilling2021 {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_title_category_data($data);
    $data = WorklogAugment::add_client_project_data($data);
    $data = WorklogAugment::add_timetracking_data($data);
    $data = WorklogAugment::add_client_timetracking_data($data);
    $data = WorklogAugment::add_client_project_timetracking_data($data);
    $data = WorklogAugment::add_effortcost_data($data);
    $data = WorklogAugment::add_client_effortcost_data($data);
    $data = WorklogAugment::add_billingcost_data($data);
    $data = WorklogAugment::add_client_billingcost_data($data);
    //$data = self::add_cli_totals_data($data);
    
    $report = WorklogReports::report($data,[
      'client' => 'client_name',
      'effort' => 'client_tracked_hours',
      'sittings' => 'WorklogAugment::count',
      'titles' => 'title_text_nobrackets/WorklogAugment::count_unique',
      'mult' => 'client_billing_multiplier',
      'hours' => 'billingcost_tracked_hours/WorklogAugment::sum',
      'total' => 'billingcost_total_cost/WorklogAugment::sum',
      'rate' => 'billingcost_hourly_rate/WorklogAugment::join_unique',
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
