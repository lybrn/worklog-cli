<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\Format;
use WorklogCLI\Output;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliCheck2021 {
  
  public static function cli($args) {      
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
        
    $data = WorklogAugment::add_date_data($data);
    $data = WorklogAugment::add_timetracking_data($data);
    $data = WorklogAugment::add_title_category_data($data);
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_client_project_data($data);
    
    $report = WorklogReports::report($data,[
      'date' => 'day_timestamp_ymd',
      'client' => 'client_name/WorklogAugment::join_unique',
      'warning' => 'check_warnings/warning_text',
      'culprit' => 'check_warnings/warning_culprit',
      'line' => 'check_warnings/warning_line_number/WorklogAugment::join_unique',
    ]);
    
    $report = WorklogReports::filter($report,[
      'warning' => true,
    ]);

    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
}
