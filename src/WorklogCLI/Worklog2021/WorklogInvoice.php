<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\CLI;
class WorklogInvoice {

    public static function invoice_data() {
                
      $data = WorklogCli::get_filtered_data_2021();
      $data = WorklogAugment::add_invoicefilter_data($data);
      
      $data = WorklogAugment::add_title_category_data($data);
      $data = WorklogAugment::add_date_data($data);      
      $data = WorklogAugment::add_client_data($data);      
      $data = WorklogAugment::add_client_project_data($data);
      $data = WorklogAugment::add_worker_data($data);      
      $data = WorklogAugment::add_timetracking_data($data);
      $data = WorklogAugment::add_effortcost_data($data);
      $data = WorklogAugment::add_billingcost_data($data);
      
      // matches
      $invoice['invoice'] = WorklogReports::rows($data,[
        'seq' => 'invoicefilter_seq',
        'number' => 'invoicefilter_number',
        'client' => 'invoicefilter_client',
        'worker' => 'invoicefilter_worker',
        'period' => 'invoicefilter_period',
        'range' => 'invoicefilter_range',
        'worktype' => 'invoicefilter_work_type',
        'date' => 'invoicefilter_date',
        'due' => 'invoicefilter_due',
      ]);
        
      // matches
      $invoice['client'] = WorklogReports::rows($data,[
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
      
      $invoice['projects'] = WorklogReports::rows($data,[
        'name' => 'client_project_name',
        'key' => 'client_project_key',
        'hours' => 'timetracking_tracked_hours/WorklogAugment::sum',
        'rate' => 'client_rate',
        'project-rate' => 'client_project_rate',
        'subtotal' => 'billingcost_total_cost/WorklogAugment::sum',        
        'tasks' => '',
        // 'number' => 'client_project_number',
        // 'sittings' => 'client_project_sittings',
        // 'line' => 'client_project_first_line_number',
      ]);
      
      $invoice['titles'] = WorklogReports::rows($data,[
        'title' => 'title_text_nobrackets',
        'project' => 'client_project_name',
        'project_key' => 'client_project_key',
        'status' => '',        
        'hours' => 'timetracking_tracked_hours/WorklogAugment::sum',
        'sittings' => 'client_project_sittings',
        'rate' => 'client_rate',
        'project-rate' => 'client_project_rate',
        'subtotal' => 'billingcost_total_cost/WorklogAugment::sum',        
      ]);
      
      if (!empty($invoice['projects'])) foreach($invoice['projects'] as &$project) {
        
        // if (empty($project['key'])) {
        //   print_r($invoice['projects']);
        //   throw new Exception("No key found in.");
        // }
        
        $project['tasks'] = WorklogReports::filter($invoice['titles'],[
          'project_key' => $project['key'],
        ]);

      }

      // matches
      // HARDCODED: "Lindsay Bernath" hardcoded WorklogAugment
      // Get it to use worker from invoice instead!
      $invoice['worker'] = WorklogReports::rows($data,[
        'fullname' => 'worker_full_name',
        'fullnametight' => 'worker_full_name_tight',
        'initials' => 'worker_initials',
        'email' => 'worker_email',
        'phone' => 'worker_phone',
        'address' => 'worker_address',
        'taxname' => 'worker_tax_name',
        'taxpercent' => 'worker_tax_percent',
        'taxaccount' => 'worker_tax_account',
        'payableby' => 'worker_payable_by',
        'taxname' => 'worker_tax_name',
        'taxpercent' => 'worker_tax_percent',
      ]);
      
      $invoice['entries'] = WorklogReports::report($data,[
        'hours' => 'timetracking_tracked_hours',
        'title' => 'title_text_nobrackets',
        'project' => 'client_project_name',
        'client' => 'client_short_name',
      ]);
      
      $invoice['timeline'] = WorklogReports::rows($data,[
        'hours' => 'timetracking_tracked_hours/WorklogAugment::sum',
        'days' => 'day_timestamp/WorklogAugment::date_range_days',
        'weeks' => 'day_timestamp/WorklogAugment::date_range_weeks',
        'hperday' => 'hours/days/WorklogAugment::divide',
        'hperweek' => 'hours/weeks/WorklogAugment::divide',
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
      
      //$invoice_data = WorklogCli::get_invoice_data();
      
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
      // $filter_args = WorklogCli::args();
      // $filter_args[] = '$';
      // 
      // $parsed = WorklogCli::get_filtered_data( $filter_args );
      // $invoice['entries'] = WorklogData::get_entries_data2($parsed,$filter_args);
      // $invoice['pricing'] = WorklogData::get_pricing_data2($parsed);
      // $invoice['timeline'] = WorklogData::get_timeline_data($parsed,$filter_args);
      // $invoice['projects'] = WorklogData::get_grouped_data2($parsed);
          
      return $invoice;
      
  }
  public static function invoice_client_data($client) {
    
    $client_key = 'Client-'.$client;
    $client_data = current( WorklogDb::db( $client_key ) );
    $client_data = Format::normalize_array_keys( $client_data );
    $client_data = Format::array_keys_remove_prefix( $client_data, 'client' );
    return $client_data;
    
  }
  public static function invoice_worker_data($worker) {
        
    $worker_key = 'Worker-'.$worker;
    $worker_data = current( WorklogDb::db( $worker_key ) );
    $worker_data = Format::normalize_array_keys( $worker_data );
    $worker_data = Format::array_keys_remove_prefix( $worker_data, 'worker' );
    return $worker_data;
    
  }
  
}
