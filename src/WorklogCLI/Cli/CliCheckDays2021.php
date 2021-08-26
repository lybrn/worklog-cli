<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
use WorklogCLI\Check;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliCheckDays2021 {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $data = WorklogAugment::add_date_data($data);
            
    $report = WorklogReports::report($data,[
      'date' => 'day_timestamp_ymd',
      'day' => 'day_text_nobrackets',
    //  'error' => 'day_line_number/WorklogCheck::require_unique',
      'error' => [ 
        'day_line_number/WorklogCheck::require_unique',
        'day_timestamp/WorklogCheck::require_timestamp',
      ],
      'line' => 'day_line_number/WorklogAugment::join_unique',
    ]);

    $report = WorklogReports::filter($report,[
      'error' => true,
    ]);
        
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
}
