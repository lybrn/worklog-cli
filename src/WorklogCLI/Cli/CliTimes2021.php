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
class CliTimes2021 {
  
  public static function cli($args) {

    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_title_category_data($data);
    $data = WorklogAugment::add_timetracking_data($data);
    $data = WorklogAugment::add_client_project_data($data);
    $data = WorklogAugment::add_effortcost_data($data);
    $data = WorklogAugment::add_billingcost_data($data);
    
    $report = WorklogReports::report($data,[
      // 'line'     => 'title_category_first_line_number',
      'text'    => 'title_text_nobrackets',
      'client' => 'client_short_name',
      // 'client' => 'client_tight_name',
      'project' => 'client_project_name',
      'hours' => 'effortcost_tracked_hours',
      'time_brackets' => 'timetracking_brackets_all',
      // 'timepointes' => 'timetracking_timepoints_all',
      '???' => 'timetracking_brackets_unknown',
      'mult' => 'billingcost_multiplier',
      'total' => 'billingcost_tracked_hours',
      // 'offset' => 'timetracking_offset',
      // 'starttime' => 'timetracking_starttime',
      // 'endtime' => 'timetracking_endtime',
      // 'total-no' => 'timetracking_tracked_time_no_offset',
      // 'total-wo' => 'timetracking_tracked_time',
      'line' => 'title_line_number',
    ]);
    
    $report = WorklogReports::sort($report,[
      'hours' => 'DESC',
      'line' => 'ASC',
    ]);
    
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
}
