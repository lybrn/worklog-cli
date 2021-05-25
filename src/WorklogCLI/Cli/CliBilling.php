<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\Format;
use WorklogCLI\Output;
use \WorklogCLI\Worklog2021\WorklogEntries;
use \WorklogCLI\Worklog2021\WorklogAugment;
use \WorklogCLI\Worklog2021\WorklogReports;
class CliBilling {
  
  public static function cli($args) {
    
    // CLI::cli($args);
    // 
    // $data = CLI::get_filtered_data_2021();
    // 
    // $data = WorklogAugment::add_client_data($data);
    // $data = WorklogAugment::add_title_category_data($data);
    // $data = WorklogAugment::add_client_project_data($data);
    // $data = WorklogAugment::add_timetracking_data($data);
    // $data = WorklogAugment::add_client_timetracking_data($data);
    // $data = WorklogAugment::add_client_project_timetracking_data($data);
    // $data = WorklogAugment::add_effortcost_data($data);
    // $data = WorklogAugment::add_client_effortcost_data($data);
    // $data = WorklogAugment::add_billingcost_data($data);
    // $data = WorklogAugment::add_client_billingcost_data($data);
    // //$data = self::add_cli_totals_data($data);
    // 
    // $report = WorklogReports::report($data,[
    //   'client' => 'client_tight_name',
    //   'effort' => 'client_tracked_hours',
    //   'sittings' => 'client_sittings',
    //   'titles' => 'client_titles',
    //   'mult' => 'client_billing_multiplier',
    //   'hours' => 'client_billing_tracked_hours',
    //   'total' => 'client_billing_total_cost',
    // ]);    
    // 
    // $output = Output::whitespace_table($report);
    // 
    // CLI::out( $output );

    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::args());
    $fromdate = @strtotime(current($options['range']));
    $todate = @strtotime(end($options['range']));
    $rows = WorklogSummary::summary_billing($data,CLI::args());
    $income = 0;
    $hours = 0;
    $effort = 0;
    foreach($data as $row) {
      $income += $row['$'];
      $started_at = strtotime($row['started_at']);
      if (empty($earliest) || $started_at < $earliest)
        $earliest = $started_at;
      if (empty($latest) || $started_at > $latest)
        $latest = $started_at;
      $hours += $row['total'];
      $effort += $row['hours'];
    }
    if (empty($fromdate)) $fromdate = $earliest;
    if (empty($todate)) $todate = $latest;
    
    $income = Format::format_cost($income);
    if ( date('Y-m-d',$fromdate) == date('Y-m-d',$todate) ) {
      $date_title = date('Y-m-d',$fromdate);
    } else {
      $date_title = date('Y-m-d',$fromdate)." to ".date('Y-m-d',$todate);
    }
    $normalized_hours = Format::format_hours($income / 80.0); 
    $output_title = $date_title." /// Effort: $effort /// \$$income: $normalized_hours @ $80\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";
    
    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows,TRUE);
    CLI::out( $output );


  }
  
}
