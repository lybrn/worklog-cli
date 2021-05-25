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
class CliCategories {
  
  public static function cli($args) {
    
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $report = WorklogReports::report($data,[
    //   'category' => 'category_text_nobrackets',
    // ]);
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );
    // 
    
    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_categories($data,CLI::$args);

    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
    
  }
  
}
