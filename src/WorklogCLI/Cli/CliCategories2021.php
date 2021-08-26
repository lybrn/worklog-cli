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
class CliCategories2021 {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $report = WorklogReports::report($data,[
      'category' => 'category_text_nobrackets',
    ]);
    
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );
    
  }
  
}
