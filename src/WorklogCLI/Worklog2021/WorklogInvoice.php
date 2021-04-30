<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\CLI;
class WorklogInvoice {

    public static function invoice_data() {
                
      $data = CLI::get_filtered_data_2021();
      
      $data = WorklogAugment::add_title_category_data($data);
      $data = WorklogAugment::add_date_data($data);      
      $data = WorklogAugment::add_client_data($data);      
      $data = WorklogAugment::add_client_project_data($data);
      $data = WorklogAugment::add_timetracking_data($data);
      
      $invoice['clients'] = WorklogReports::report($data,[
        'fullname' => 'client_full_name',
        'shortname' => 'client_short_name',
        'tightname' => 'client_tight_name',
        'cliname' => 'client_cli_name',
        'address' => 'client_address',
        'rate' => 'client_rate',
        'contactfirstname' => 'client_first_name',
        'contactlastname' => 'client_last_name',
        'contactname' => 'client_name',
        'contactemail' => 'client_email',
        'taxname' => 'client_tax_name',
        'taxpercent' => 'client_tax_percent',
        'terms' => 'client_terms',
        'note' => 'client_note',
      ]);
      
      $invoice['projects'] = WorklogReports::report($data,[
        'client' => 'client_short_name',
        'project' => 'client_project_name',
        'number' => 'client_project_number',
        'sittings' => 'client_project_sittings',
        'line' => 'client_project_first_line_number',
        'key' => 'client_project_key',
      ]);
      
      $invoice['entries'] = WorklogReports::report($data,[
        'hours' => 'timetracking_tracked_hours',
        'title' => 'title_text_nobrackets',
        'project' => 'client_project_name',
        'client' => 'client_short_name',
      ]);
      
      // $entry['started_at'] = $item['started_at'];
      // $entry['date'] = date('Y-m-d',strtotime($item['started_at']));
      // $entry['month'] = date('Y-m',strtotime($item['started_at']));
      // $entry['project'] = $project;
      // $entry['task'] = $task;
      // $entry['title'] = $title;
      // $entry['notes'] = $notes;
      // $entry['rate'] = $rate;
      // $entry['total'] = $total;
      // 
      // $entry['hours'] = $hours;
      
      //$invoice_data = CLI::get_invoice_data();
      
      // $invoice = array(
      //   'invoice' => array(),
      //   'client' => array(),
      //   'worker'=>array(),
      //   'entries' => array(),
      //   'pricing'=>array(),
      //   'timeline'=>array(),
      //   'projects' => array(),
      // );
      
      // info
      // $invoice['invoice'] = $invoice_data;
      // $invoice['client'] =  WorklogInvoice::invoice_client_data($invoice['invoice']['client']);
      // $invoice['worker'] =  WorklogInvoice::invoice_worker_data($invoice['invoice']['worker']);
      
      // $range = explode(' ',$invoice['invoice']['range']);
      // 
      // $filter_args = CLI::args();
      // $filter_args[] = '$';
      // 
      // $parsed = CLI::get_filtered_data( $filter_args );
      // $invoice['entries'] = WorklogData::get_entries_data2($parsed,$filter_args);
      // $invoice['pricing'] = WorklogData::get_pricing_data2($parsed);
      // $invoice['timeline'] = WorklogData::get_timeline_data($parsed,$filter_args);
      // $invoice['projects'] = WorklogData::get_grouped_data2($parsed);
          
      return $invoice;
      
  }
  public static function invoice_client_data($client) {
    
    $client_key = 'Client-'.$client;
    $client_data = current( CLI::get_note_data_by_keys( $client_key ) );
    $client_data = Format::normalize_array_keys( $client_data );
    $client_data = Format::array_keys_remove_prefix( $client_data, 'client' );
    return $client_data;
    
  }
  public static function invoice_worker_data($worker) {
        
    $worker_key = 'Worker-'.$worker;
    $worker_data = current( CLI::get_note_data_by_keys( $worker_key ) );
    $worker_data = Format::normalize_array_keys( $worker_data );
    $worker_data = Format::array_keys_remove_prefix( $worker_data, 'worker' );
    return $worker_data;
    
  }
  
}
