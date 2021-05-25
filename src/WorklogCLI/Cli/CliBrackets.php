<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Worklog2021\WorklogReports;
use WorklogCLI\Worklog2021\WorklogAugment;
use WorklogCLI\Output;
class CliBrackets {
  
  public static function cli($args) {
    
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // $data = WorklogAugment::add_title_category_data($data);
    // $data = WorklogAugment::add_timetracking_data($data);
    // $data = WorklogAugment::add_date_data($data);
    // 
    // $report = WorklogReports::extract_merged($data,'title_category_tags_all');
    // $timetracking = WorklogReports::extract_merged($data,'timetracking_brackets_all'); 
    // $yyyymmdd = WorklogReports::extract_merged($data,'entry_brackets_yyyymmdd'); 
    // $report = array_unique($report);
    // $report = array_diff($report,$timetracking);
    // $report = array_diff($report,$yyyymmdd);
    // natsort($report);
    // 
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );
    
    
    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_brackets($data,CLI::$args);
    
    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
    
  }
  
}
