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
class CliTotals {
  
  public static function cli($args) {
  
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $data = WorklogAugment::add_client_data($data);
    // $data = WorklogAugment::add_title_category_data($data);
    // $data = WorklogAugment::add_client_project_data($data);
    // $data = WorklogAugment::add_timetracking_data($data);
    // $data = WorklogAugment::add_client_project_timetracking_data($data);
    // $data = WorklogAugment::add_effortcost_data($data);
    // $data = WorklogAugment::add_client_effortcost_data($data);
    // $data = self::add_cli_totals_data($data);
    // 
    // $report = WorklogReports::report($data,[
    //   // 'client' => 'client_tight_name',
    //   'category' => 'category_text_nobrackets',
    //   'project' => 'client_project_name',
    //   'project totals' => 'cli_totals_project_summary',
    //   // 'project_hours' => 'client_project_effort_tracked_hours',
    //   // 'project_total' => 'client_project_effort_total_cost',
    //   'title'    => 'title_text_nobrackets',
    //   'title totals'    => 'cli_totals_title_summary',
    //   // 'title'    => 'title_text_nobrackets',
    //   // 'title_hours' => 'client_project_title_effort_tracked_hours',
    //   // 'title_total' => 'client_project_title_effort_total_cost',
    //   'status' => null,
    //   'sittings' => 'title_category_sittings',
    // ]);    
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );
    
    
    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $rows = WorklogSummary::summary_totals($data,CLI::$args);
    
    // output
    $output = Output::whitespace_table($rows);
    CLI::out( $output );
    


  }
  public static function add_cli_totals_data($entries) {
  
    foreach($entries as &$entry) {
  
      $formatted_hours = Format::format_hours($entry['client_project_effort_tracked_hours']);
      $formatted_cost = Format::format_cost($entry['client_project_effort_total_cost']);
  
      $entry['cli_totals_project_summary'] = [];
      // $entry['cli_totals_project_summary'][] = $entry['client_project_name'];
      $entry['cli_totals_project_summary'][] = $formatted_hours.'h';
      $entry['cli_totals_project_summary'][] = '$'.$formatted_cost;
      $entry['cli_totals_project_summary'] = implode(' / ',$entry['cli_totals_project_summary']);
  
      $formatted_hours = Format::format_hours($entry['client_project_title_effort_tracked_hours']);
      $formatted_cost = Format::format_cost($entry['client_project_title_effort_total_cost']);

      $entry['cli_totals_title_summary'] = [];
      // $entry['cli_totals_title_summary'][] = $entry['title_text_nobrackets'];
      $entry['cli_totals_title_summary'][] = $formatted_hours.'h';
      $entry['cli_totals_title_summary'][] = '$'.$formatted_cost;
      $entry['cli_totals_title_summary'] = implode(' / ',$entry['cli_totals_title_summary']);
  
    }
  
    return $entries;
  
  }
  
}
