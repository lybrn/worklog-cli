<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliBilling {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // build summary
    $data = CLI::get_filtered_data();
    $options = WorklogFilter::get_options($data,CLI::args());
    $fromdate = @strtotime(current($options['range']));
    $todate = @strtotime(end($options['range']));
    $rows = WorklogSummary::summary_billing($data,CLI::args());
    $income = 0;
    $hours = 0;
    foreach($data as $row) {
      $income += $row['$'];
      $started_at = strtotime($row['started_at']);
      if (empty($earliest) || $started_at < $earliest)
        $earliest = $started_at;
      if (empty($latest) || $started_at > $latest)
        $latest = $started_at;
      $hours += $row['total'];
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
    $output_title = $date_title." /// $hours /// \$$income -- $normalized_hours @ $80\n";
    $output_title .=  str_repeat('=',strlen($date_title))."\n\n";

    // output
    $output = $output_title;
    $output .= Output::whitespace_table($rows);
    CLI::out( $output );


  }
  
}
