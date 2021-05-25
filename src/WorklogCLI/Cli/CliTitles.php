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
class CliTitles {
  
  public static function cli($args) {
    
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $data = WorklogAugment::add_title_category_data($data);
    // $data = WorklogAugment::add_client_data($data);
    // 
    // $report = WorklogReports::report($data,[
    //   // 'line'     => 'title_category_first_line_number',
    //   'title'    => 'title_text_nobrackets',
    //   'sittings' => 'title_category_sittings',
    //   'category' => 'category_text_nobrackets',
    //   // 'client' => 'client_tight_name',
    // ]);
    // 
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );
    // 
    CLI::cli($args);
    
    // get titles
    $data = CLI::get_filtered_data();
    $titles = WorklogData::get_titles($data);
    
    // output
    $output = Output::whitespace_table($titles);
    CLI::out( $output );

  }
  
}
