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
      $data = WorklogAugment::add_render_data($data);
      $data = WorklogAugment::add_effortcost_data($data);
      $data = WorklogAugment::add_billingcost_data($data);
      
      // matches
      $invoice['invoice'] = current(WorklogReports::rows($data,[
        'seq' => 'invoicefilter_seq',
        'number' => 'invoicefilter_number',
        'client' => 'invoicefilter_client',
        'worker' => 'invoicefilter_worker',
        'period' => 'invoicefilter_period',
        'range' => 'invoicefilter_range',
        'worktype' => 'invoicefilter_work_type',
        'date' => 'invoicefilter_date',
        'due' => 'invoicefilter_due',
      ]));
      
      $invoice['pricing'] = current(WorklogReports::rows($data,[
        'hours' => 'billingcost_tracked_hours/WorklogAugment::sum',
        'subtotal' => 'billingcost_subtotal_cost/WorklogAugment::sum',
        'tax' => 'billingcost_tax_cost/WorklogAugment::sum',
        'taxpercent' => 'billingcost_tax_ratio',
        'total' => 'billingcost_invoicetotal_cost/WorklogAugment::sum',
      ]));
        
      $invoice['timeline'] = current(WorklogReports::rows($data,[
        'hours' => 'billingcost_tracked_hours/WorklogAugment::sum',
        'days' => 'day_timestamp/WorklogAugment::date_range_days',
        'weeks' => 'day_timestamp/WorklogAugment::date_range_weeks',
        'hperday' => 'hours/days/WorklogAugment::divide',
        'hperweek' => 'hours/weeks/WorklogAugment::divide',
      ]));
      
      // matches
      $invoice['client'] = current(WorklogReports::rows($data,[
        'fullname' => 'client_full_name',
        'shortname' => 'client_short_name',
        'tightname' => 'client_tight_name',
        'cliname' => 'client_cli_name',
        'address' => 'client_address',
        'rate' => 'client_rate',
        'contactfirstname' => 'client_contact_firstname',
        'contactlastname' => 'client_contact_lastname',
        'contactname' => 'client_contact_name',
        'contactemail' => 'client_contact_email',
        'taxname' => 'client_tax_name',
        'taxpercent' => 'client_tax_percent',
        'terms' => 'client_terms',
        'note' => 'client_note',
      ]));
      
      // matches
      // HARDCODED: "Lindsay Bernath" hardcoded in WorklogAugment
      // Get it to use worker from invoice instead!
      $invoice['worker'] = current(WorklogReports::rows($data,[
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
      ]));
      
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
      
      $titles = WorklogReports::rows($data,[
        'title' => 'title_text_nobrackets',
        'project' => 'client_project_name',
        'status' => 'status',
        'hours' => 'timetracking_tracked_hours/WorklogAugment::sum',
        'sittings' => 'client_project_sittings',
        'rate' => 'client_rate',
        'subtotal' => 'billingcost_total_cost/WorklogAugment::sum',        
        'project-rate' => 'client_project_rate',
        'project-key' => 'client_project_key',
      ]);
      
      if (!empty($invoice['projects'])) foreach($invoice['projects'] as &$project) {
        
        // if (empty($project['key'])) {
        //   print_r($invoice['projects']);
        //   throw new Exception("No key found in.");
        // }
        
        $project_tasks = $titles;
        $project_tasks = WorklogReports::filter($project_tasks,[
          'project-key' => $project['key'],
        ]);
        $project_tasks = WorklogReports::sort($project_tasks,[
          'subtotal' => 'DESC',
        ]);
        
        $project['tasks'] = $project_tasks;

      }
      
      $invoice['entries'] = WorklogReports::rows($data,[
        'started_at' => 'timetracking_starttime_ymdhi',
        'date' => 'day_timestamp_ymd',
        'month' => 'day_timestamp_ym',
        'project' => 'client_project_name',
        'task' => 'task',
        'title' => 'title_text_nobrackets',
        'notes' => 'note_text_paragraph',
        'rate' => 'client_rate',
        'total' => 'billingcost_invoicetotal_cost',
        'hours' => 'billingcost_tracked_hours',
      ]);
      
      $invoice['entries'] = WorklogReports::sort($invoice['entries'],[
        'started_at' => 'ASC',
      ]);

      

      
    



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
