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
class CliTitles2021 {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    $data = CLI::get_filtered_data_2021();
    
    $data = WorklogAugment::add_title_category_data($data);
    $data = WorklogAugment::add_client_data($data);
    $data = WorklogAugment::add_client_project_data($data);
    
    $report = WorklogReports::report($data,[      
      'title'    => 'title_text_nobrackets',
      'sittings' => 'title_category_sittings',
      'project' => 'client_project_name',
      'line'     => 'title_category_first_line_number',
      // 'client' => 'client_tight_name',
    ]);
    
    
    $output = Output::whitespace_table($report);
    
    CLI::out( $output );

  }
  
}
