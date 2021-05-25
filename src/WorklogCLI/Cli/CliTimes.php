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
class CliTimes {
  
  public static function cli($args) {

    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $data = WorklogAugment::add_client_data($data);
    // $data = WorklogAugment::add_title_category_data($data);
    // $data = WorklogAugment::add_timetracking_data($data);
    // $data = WorklogAugment::add_client_project_data($data);
    // 
    // $report = WorklogReports::report($data,[
    //   // 'line'     => 'title_category_first_line_number',
    //   'title'    => 'title_text_nobrackets',
    //   'category' => 'category_text_nobrackets',
    //   // 'client' => 'client_tight_name',
    //   'project' => null,
    //   'hours' => 'timetracking_tracked_hours',
    //   'times' => 'timetracking_brackets_all',
    //   // 'timepointes' => 'timetracking_timepoints_all',
    //   '???' => 'timetracking_brackets_unknown',
    //   'mult' => null,
    //   'total' => null,
    //   // 'offset' => 'timetracking_offset',
    //   // 'starttime' => 'timetracking_starttime',
    //   // 'endtime' => 'timetracking_endtime',
    //   // 'total-no' => 'timetracking_tracked_time_no_offset',
    //   // 'total-wo' => 'timetracking_tracked_time',
    //   'line' => 'title_line_number',
    // ]);
    // 
    // $report = WorklogReports::sort($report,[ 'hours' ]);
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );

    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_times($data,CLI::$args);
    
    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
    

  }
  
}
