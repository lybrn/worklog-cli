<?php
namespace WorklogCLI;
class WorklogData {

  public static function get_range_data($parsed,$args=array()) {
    
    $range = WorklogFilter::args_get_date_range($args);

    $return = [
      'range_start_date_tight' => null,
      'range_start_date_dashed' => null,
      'range_end_date_tight' => null,
      'range_end_date_dashed' => null,
      'range_end_month_tight' => null,
      'range_end_month_dashed' => null,
      'range_next_month_end_tight' => null,
      'range_next_month_end_dashed' => null,
    ];
    
    $fromdate = new \DateTime($range[0]);
    $fromdate->add(new \DateInterval('PT1S')); // add 1 second to 23:59:59
    $todate = new \DateTime($range[1]);
    $nextmonthdate = new \DateTime($range[1]);
    $nextmonthdate->add( new \DateInterval("P1M") );
    
    $return['range_start_date_tight'] =  $fromdate->format("Ymd");
    $return['range_start_date_dashed'] =  $fromdate->format("Y-m-d");
    $return['range_end_date_tight'] =  $todate->format("Ymd");
    $return['range_end_date_dashed'] =  $todate->format("Y-m-d");
    $return['range_end_month_tight'] =  $todate->format("Ym");
    $return['range_end_month_dashed'] =  $todate->format("Y-m");
    $return['range_end_month_text'] =  $todate->format("F Y");
    $return['range_next_month_end_tight'] =  $nextmonthdate->format("Ymt");
    $return['range_next_month_end_dashed'] =  $nextmonthdate->format("Y-m-t");
    
    return $return;
    
    // // // dont do this, daylight savings time makes it fail
    // // $days = $rangediff / (60.0 * 60.0 * 24.0);
    // $weeks = $days / 7.0;
    // $timeline = array(
    //   'hours'=>0.0,
    //   'days'=>$days,
    //   'weeks'=>Format::format_hours($weeks),
    //   'hperday'=>0.0,
    //   'hperweek'=>0.0,
    // );
    // foreach($parsed as $item) {
    //   $hours = Format::format_hours($item['total']);
    //   $timeline['hours'] += $hours;
    // }
    // $timeline['hours'] = Format::format_hours( $timeline['hours'] );
    // $timeline['hperday'] = Format::format_hours( $timeline['hours'] / $days );
    // $timeline['hperweek'] = Format::format_hours( $timeline['hours'] / $weeks );
    // return $timeline;
  }
  public static function get_entries_data2($parsed,$args=array()) {
    
    $entries = array();
    foreach($parsed as $k=>$item) {
      $rate = Format::format_cost($item['client-rate']);
      $hours = Format::format_hours($item['total']);
      $total = is_numeric($rate) ? Format::format_cost($hours * $rate) : 0;
      $title = $item['title'];
      $task = $item['client-task'];
      $project = $item['client-project'];
      $notes = implode(". ",$item['notes']).'.';
      $entry = array();
      $entry['started_at'] = $item['started_at'];
      $entry['date'] = date('Y-m-d',strtotime($item['started_at']));
      $entry['project'] = $project;
      $entry['task'] = $task;
      $entry['title'] = $title;
      $entry['notes'] = $notes;
      $entry['rate'] = $rate;
      $entry['total'] = $total;

      $entry['hours'] = $hours;
      $sortkey = strtotime($entry['started_at']).'-'.$k;
      $entries[ $sortkey ] = $entry;
    }
    ksort($entries);
    return array_values($entries);
  }
  public static function get_entries_data($parsed,$args=array()) {
    
    $entries = array();
    foreach($parsed as $k=>$item) {
      $rate = $item['rate'];
      $hours = Format::format_hours($item['total']);
      $total = Format::format_cost($hours * $rate);
      $title = $item['title'];
      $task = $item['task'];
      $notes = implode(". ",$item['notes']).'.';
      $entry = array();
      $entry['started_at'] = $item['started_at'];
      $entry['date'] = date('Y-m-d',strtotime($item['started_at']));
      $entry['project'] = $item['project'];
      $entry['task'] = $task;
      $entry['title'] = $title;
      $entry['notes'] = $notes;
      $entry['rate'] = $rate;
      $entry['total'] = $total;

      $entry['hours'] = $hours;
      $sortkey = strtotime($entry['started_at']).'-'.$k;
      $entries[ $sortkey ] = $entry;
    }
    ksort($entries);
    return array_values($entries);
  }
  public static function get_timeline_data($parsed,$args=array()) {
    $range = WorklogFilter::args_get_date_range($args);
    //$rangediff = (strtotime($range[1]) - strtotime($range[0])) + 1;
    $fromdate = new \DateTime($range[1]);
    $todate = new \DateTime($range[0]);
    $todate->add(new \DateInterval('PT1S')); // add 1 second to 23:59:59
    $days = $todate->diff($fromdate)->format("%a") + 1;
    // // dont do this, daylight savings time makes it fail
    // $days = $rangediff / (60.0 * 60.0 * 24.0);
    $weeks = $days / 7.0;
    $timeline = array(
      'hours'=>0.0,
      'days'=>$days,
      'weeks'=>Format::format_hours($weeks),
      'hperday'=>0.0,
      'hperweek'=>0.0,
    );
    foreach($parsed as $item) {
      $hours = Format::format_hours($item['total']);
      $timeline['hours'] += $hours;
    }
    $timeline['hours'] = Format::format_hours( $timeline['hours'] );
    $timeline['hperday'] = Format::format_hours( $timeline['hours'] / $days );
    $timeline['hperweek'] = Format::format_hours( $timeline['hours'] / $weeks );
    return $timeline;
  }
  public static function get_pricing_data2($parsed) {

    $pricing = array(
      'hours'=>0.0,
      'subtotal'=>0.0,
      'tax'=>0.0,
      'taxpercent'=>0.0,
      'total'=>0.0,
    );

    $firstitem = current($parsed);
    $client_rate = Format::format_cost( $firstitem['client-rate'] );
    $taxpercent = (float) trim( $firstitem['client-taxpercent'] ,'%') / 100.0;

    // calculate hours and subtotal
    foreach($parsed as $item) {
      if (!empty($item['hours'])) $pricing['hours'] += Format::format_hours( $item['hours_multiplied'] );
      if (!empty($item['subtotal'])) $pricing['subtotal'] += Format::format_cost( $item['subtotal'] );
    }

    // calculate tax and total
    $pricing['tax'] = Format::format_cost( $pricing['subtotal'] * $taxpercent );
    $pricing['total'] = Format::format_cost( $pricing['subtotal'] + $pricing['tax'] );

    // format values
    $pricing['hours'] = Format::format_hours( $pricing['hours'] );
    $pricing['subtotal'] = Format::format_cost( $pricing['subtotal'] , array('comma'=>TRUE) );
    $pricing['tax'] = Format::format_cost( $pricing['tax'] , array('comma'=>TRUE) );
    $pricing['taxpercent'] = Format::format_cost( $taxpercent );
    $pricing['total'] = Format::format_cost( $pricing['total'] , array('comma'=>TRUE) );

    return $pricing;
  }
  public static function get_pricing_data($parsed) {
    $clients = WorklogData::get_clients_data($parsed);
    $pricing = array(
      'hours'=>0.0,
      'subtotal'=>0.0,
      'tax'=>0.0,
      'total'=>0.0,
    );

    $firstitem = current($parsed);
    $clientkey = Format::normalize_key( $firstitem['client'] );
    $rate = (float) trim($clients[ $clientkey ]['rate'],'$');
    $taxpercent = (float) trim($clients[ $clientkey ]['taxpercent'],'%') / 100.0;

    foreach($parsed as $item) {
      $hours = $item['total'];
      $pricing['hours'] += Format::format_hours( $hours );
    }
    $pricing['hours'] = $pricing['hours'];
    $pricing['subtotal'] = Format::format_cost( $pricing['hours'] * $rate );
    $pricing['tax'] = Format::format_cost( $pricing['subtotal'] * $taxpercent );
    $pricing['total'] = Format::format_cost( $pricing['subtotal'] + $pricing['tax'] );

    $pricing['subtotal'] = Format::format_cost( $pricing['subtotal'] , array('comma'=>TRUE) );
    $pricing['tax'] = Format::format_cost( $pricing['tax'] , array('comma'=>TRUE) );
    $pricing['total'] = Format::format_cost( $pricing['total'] , array('comma'=>TRUE) );

    return $pricing;
  }
  public static function get_clients_data($parsed) {
    $clients = array();
    foreach($parsed as $item) {
      $clientkey = Format::normalize_key( $item['client'] );
      $clients[ $clientkey ] = array(
        'key' => $clientkey,
        'name' => $item['client'],
        'shortname' => $item['category_info']['clientshortname'],
        'fullname' => $item['category_info']['clientfullname'],
        'address' => $item['category_info']['clientaddress'],
        'contactname' => $item['category_info']['clientcontactname'],
        'contactemail' => $item['category_info']['clientcontactemail'],
        'rate' => Format::format_cost( trim($item['category_info']['clientrate'],'$') ),
        'taxpercent' => $item['category_info']['clienttaxpercent'],
        'taxname' => $item['category_info']['clienttaxname'],
        //'details' => $item['category_info'],
      );
    }
    return $clients;
  }
  public static function get_clients_data2($parsed) {
    $clients = array();
    foreach($parsed as $item) {
      $clientkey = Format::normalize_key( $item['client'] );      
      $client = current( CLI::get_note_data_by_keys( 'Client-'.$clientkey ) );
      if (empty($client)) continue;
      $client = Format::normalize_array_keys( $client );
      $client = Format::array_keys_remove_prefix( $client, 'client');
      $clients[ $clientkey ] = $client;
    }
    return $clients;
  }  
  public static function get_workers_data2($parsed) {
    $workers = CLI::get_note_data_by_keys( 'Worker-*' );
    foreach($workers as $workerkey => &$worker) {
      $worker = Format::normalize_array_keys( $worker );
      $worker = Format::array_keys_remove_prefix( $worker, 'worker');
      $workers[ $workerkey ] = $worker;
    }
    return $workers;
  }    
  public static function get_worker_data($parsed,$notedata,$args) {
    
    foreach(CLI::args() as $arg) {
      $key = Format::normalize_key( "Worker-".$arg );
      $worker_data = CLI::get_note_data($key);
      if (!empty($worker_data)) {
        $pairs = current($worker_data);
        $pairs = Format::normalize_array_keys($pairs);
        return $pairs;
        
      }
    }

  }  
  public static function get_invoices_data($parsed) {
    $invoices = array();
    foreach($parsed as $item) {
      $clientkey = Format::normalize_key( $item['client'] );
      $invoices[ $clientkey ] = array(
        'key' => $clientkey,
        'number' => $item['category_info']['invoicenumber'],
        'period' => $item['category_info']['invoiceperiod'],
        'date' => $item['category_info']['invoicedate'],
        'due' => $item['category_info']['invoicedue'],
        //'details' => $item['category_info'],
      );
    }
    return $invoices;
  }
  public static function get_projects_data2($parsed) {
    $projects = array();
    // projects
    foreach($parsed as $item) {
      $projectkey = Format::normalize_key( $item['client-project'] );
      $projects[$projectkey] = array(
        'name' => $item['client-project'],
        'key' => $projectkey,
        'hours' => Format::format_hours(0),
        'rate' => $item['rate'],
        'project-rate' => $item['project-rate'],
        'subtotal' => 0.0,
      );
    }
    // entries
    foreach($parsed as $item) {
      $projectkey = Format::normalize_key( $item['client-project'] );
      $projects[$projectkey]['hours'] = (float) $projects[$projectkey]['hours'] + (float) $item['total'];
      $projects[$projectkey]['hours'] = Format::format_hours($projects[$projectkey]['hours']);
      $projects[$projectkey]['subtotal'] = (float) $projects[$projectkey]['subtotal'] + (float) $item['subtotal'];
      $projects[$projectkey]['subtotal'] = Format::format_cost($projects[$projectkey]['subtotal']);
    }
    // sort
    $sorted = array();
    foreach($projects as $project) {
      $project['hours'] = Format::format_hours($project['hours']);
      $sortkey = Format::normalize_key(implode('-',array(
        $project['hours'],
        $project['name'],
      )),'-');
      $sorted[ $sortkey ] = $project;
    }
    krsort($sorted,SORT_NATURAL);    
    $sorted = array_values($sorted);
    // return
    return $sorted;

  }  
  public static function get_projects_data($parsed) {
    $projects = array();
    // projects
    foreach($parsed as $item) {
      $projectkey = Format::normalize_key( $item['project'] );
      $projects[$projectkey] = array(
        'name' => $item['project'],
        'key' => $projectkey,
        'hours' => Format::format_hours(0),
      );
    }
    // entries
    foreach($parsed as $item) {
      $projectkey = Format::normalize_key( $item['project'] );
      $projects[$projectkey]['hours'] = (float) $projects[$projectkey]['hours'] + (float) $item['total'];
      $projects[$projectkey]['hours'] = Format::format_hours($projects[$projectkey]['hours']);
    }
    // sort
    $sorted = array();
    foreach($projects as $project) {
      $project['hours'] = Format::format_hours($project['hours']);
      $sortkey = Format::normalize_key(implode('-',array(
        $project['hours'],
        $project['name'],
      )),'-');
      $sorted[ $sortkey ] = $project;
    }
    krsort($sorted,SORT_NATURAL);
    $sorted = array_values($sorted);
    // return
    return $sorted;

  }
  public static function get_tasks_data2($parsed) {
    $tasks = array();
    // tasks
    foreach($parsed as $item) {
      $taskkey = Format::normalize_key( $item['client-project'].'-'.$item['title'] );
      $tasks[ $taskkey ] = array(
        'title' => $item['title'],
        'project' => $item['client-project'],
        'status' => null,
        'hours' => 0,
        'sittings' => 0,
        'rate' => Format::format_cost($item['rate']),
        'subtotal' => 0.0,
      );
    }
    // hours and status
    foreach($parsed as $item) {
      $taskkey = Format::normalize_key( $item['client-project'].'-'.$item['title'] );
      $tasks[ $taskkey ]['status'] = $item['client-status'];
      $tasks[ $taskkey ]['hours'] += Format::format_hours($item['total']);
      $tasks[ $taskkey ]['subtotal'] += Format::format_cost($item['subtotal']);
      $tasks[ $taskkey ]['sittings'] += 1;
    }
    // sort
    $sorted = array();
    foreach($tasks as $task) {
      $task['hours'] = Format::format_hours( $task['hours'] );
      $sortkey = Format::normalize_key(implode('-',array(
        ($task['hours'] * 100.00) + 10000.00, // add 10k so even negative hours are positve when sorting
        ($task['subtotal'] * 100.00) + 1000000.00, // add 1000k so even negative hours are positve when sorting
        $task['title'],
      )),'-');
      $sorted[ $sortkey ] = $task;
    }
    krsort($sorted,SORT_NATURAL);
    $sorted = array_values($sorted);
    // return
    return $sorted;

  }  
  public static function get_tasks_data($parsed) {
    $tasks = array();
    // tasks
    foreach($parsed as $item) {
      $taskkey = Format::normalize_key( $item['project'].'-'.$item['title'] );
      $tasks[ $taskkey ] = array(
        'title' => $item['title'],
        'project' => $item['project'],
        'status' => null,
        'hours' => 0,
        'sittings' => 0,
      );
    }
    // hours and status
    foreach($parsed as $item) {
      $taskkey = Format::normalize_key( $item['project'].'-'.$item['title'] );
      $tasks[ $taskkey ]['status'] = $item['status'];
      $tasks[ $taskkey ]['hours'] += Format::format_hours($item['total']);
      $tasks[ $taskkey ]['sittings'] += 1;
    }
    // sort
    $sorted = array();
    foreach($tasks as $task) {
      $task['hours'] = Format::format_hours( $task['hours'] );
      $sortkey = Format::normalize_key(implode('-',array(
        ($task['hours'] * 100.00) + 10000.00, // add 10k so even negative hours are positve when sorting
        ($task['subtotal'] * 100.00) + 1000000.00, // add 1000k so even negative hours are positve when sorting
        $task['title'],
      )),'-');
      $sorted[ $sortkey ] = $task;
    }
    krsort($sorted,SORT_NATURAL);
    $sorted = array_values($sorted);
    // return
    return $sorted;

  }
  public static function get_grouped_data2($parsed) {

    $grouped_data = array();
    $projects = WorklogData::get_projects_data2($parsed);
    $tasks = WorklogData::get_tasks_data2($parsed);
    // projects
    foreach($projects as $project) {
      $projectkey = Format::normalize_key($project['name']);
      $grouped_data[ $projectkey ] = $project;
    }
    // tasks
    foreach($tasks as $task) {
      $projectkey = Format::normalize_key($task['project']);
      $grouped_data[ $projectkey ]['tasks'][] = $task;
    }
    return array_values($grouped_data);
  } 
  public static function get_grouped_data($parsed) {

    $grouped_data = array();
    $projects = WorklogData::get_projects_data($parsed);
    $tasks = WorklogData::get_tasks_data($parsed);
    // projects
    foreach($projects as $project) {
      $projectkey = Format::normalize_key($project['name']);
      $grouped_data[ $projectkey ] = $project;
    }
    // tasks
    foreach($tasks as $task) {
      $projectkey = Format::normalize_key($task['project']);
      $grouped_data[ $projectkey ]['tasks'][] = $task;
    }
    return array_values($grouped_data);
  }
  public static function get_categories($data) {

    $worklog_categories = array();
    foreach($data as $data=>$item) {
      $key = Format::normalize_key($item['client']);
      $cat = array();
      $cat['key'] = $key;
      $cat['name'] = $item['client'];
      $cat['brackets'] = $item['category_brackets'];
      $sortkey = $cat;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      $worklog_categories[ $sortkey ] = $cat;
    }
    ksort($worklog_categories);
    $rows = array_values($worklog_categories);
    return $rows;

  }
  public static function get_titles($data) {

    $worklog_titles = array();
    foreach($data as $data=>$item) {
      $cat = array();
      //$cat['client'] = $item['client'];
      $cat['title'] = $item['title'];;
      $worklog_categories[ implode('-',$cat) ]= $cat;
    }
    ksort($worklog_categories);
    $rows = array_values($worklog_categories);
    return $rows;

  }
  public static function get_note_data($filepaths,$options=array()) {
    
    $parsed = MDON::parse_files($filepaths);
    $notedata = array();
    
    foreach($parsed as $day) {
      if (is_array($day['rows'])) 
      foreach($day['rows'] as $category) {
        if (!empty($category['rows']))
        foreach($category['rows'] as $tasks) {
          if (!empty($tasks['rows']))
          foreach($tasks['rows'] as $notes) {
                
            if ($notes['style']=='*' || $notes['style']=='.') {
              $pair = explode(':',$notes['text'],2);
              $key = trim($pair[0]);
              if (!empty($notes['rows'])) {
                if (!is_array($notedata[ $key ]))
                  $notedata[ $key ] = [];
                foreach($notes['rows'] as $noterow) {
                  $subpair = explode(':',$noterow['text'],2);
                  $subkey = trim($subpair[0]);
                  $subvalue = trim($subpair[1]);
                  if (!empty($subvalue)) {
                    $notedata[ $key ][ $subkey ] = $subvalue;
                  } else if (!empty($subkey) && empty($noterow['rows'])) {
                    $notedata[ $key ][] = $subkey;
                  }
                  if (!empty($noterow['rows'])) {
                    if (!is_array($notedata[ $key ][ $subkey ]))
                      $notedata[ $key ][ $subkey ] = [];
                    foreach($noterow['rows'] as $subnoterow) {
                      $subsubpair = explode(':',$subnoterow['text'],2);
                      $subsubkey = trim($subsubpair[0]);
                      $subsubvalue = trim($subsubpair[1]);
                      if (!empty($subsubvalue)) {
                        $notedata[ $key ][ $subkey ][ $subsubkey ] = $subsubvalue;                    
                      } else if (!empty($subsubkey)) {
                        $notedata[ $key ][ $subkey ][] = $subsubkey;                    
                      }
                      //$notedata[ $subkey ][ $subsubkey ] = $subsubvalue;                    
                    }    
                  }
                }
                if (empty($notedata[ $key ])) unset($notedata[ $key ]);
              }
            }
          }
        }
      }
    }
    ksort($notedata);
    return $notedata;        
                        
  }  
  public static function get_data($filepaths,$options=array()) {

    $parsed = MDON::parse_files($filepaths);
    $output = array();
    $category_info = array();
    $all_categories = array();
    $all_titles = array();

    foreach($parsed as $r => $row) {
      $day_text = WorklogData::line_clean_brackets($row['text']);
      if (!empty($day_text)) $parsed[$r]['day_text'] = $day_text;
      $timestamp = @strtotime($day_text);
      if (!empty($timestamp)) $parsed[$r]['timestamp'] = $timestamp;
      $parsed[$r]['day_brackets'] = WorklogData::line_get_brackets($row['text']);
    }
    foreach($parsed as $day) {
      if (!empty($options['since']) && !WorklogData::time_filter_since($day['timestamp'],$options['since'])) {
        continue;
      }
      if (!empty($options['until']) && !WorklogData::time_filter_until($day['timestamp'],$options['until'])) {
        continue;
      }
      $day_line_number = $day['linenum'];
      if (!empty($day['rows'])) {
        if (is_array($day['rows'])) foreach($day['rows'] as $category) {
          $cat_line_number = $category['linenum'];
          $cleancategory = WorklogData::line_clean_brackets($category['text']);
          $all_categories[ $cleancategory ] = $cleancategory;
          if (!empty($options['category']) && !WorklogData::time_filter_compare($category['text'],$options['category'])) {
            continue;
          }
          if (!empty($category['rows'])) {
            //$lastinfo = null;
            foreach($category['rows'] as $tasks) {

              if ($tasks['style']=='*') {
                $info = explode(':',$tasks['text'],2);
                if (count($info)!=2) continue;
                $key = Format::normalize_key( trim($info[0],'*') );
                $value = trim($info[1]);
                $category_info[ $cleancategory ][ $key ] = ($value!='') ? $value : array();
                $lastinfo = &$category_info[ $cleancategory ][ $key ];
                continue;
              }
              if ($tasks['style']=='.') {
                if (is_array($lastinfo)) {
                  $lastinfo[] = trim(ltrim($tasks['text'],'.'));
                }
                continue;
              }
              $queued = array();
              $output_notes = array();
              $clean_notes = array();
              $time_brackets = array();
              $cost_brackets = array();
              if (!empty($tasks['rows'])) {
                foreach($tasks['rows'] as $notes) {
                  if ($notes['style']=='-') {
                    $queued_brackets = WorklogData::line_get_brackets($notes['text']);
                    $queued_brackets = WorklogData::array_explode_values('/',$queued_brackets);
                    $queued_category = WorklogData::brackets_match_item($queued_brackets,$all_categories);
                    $queued_titles = $queued_brackets;
                    unset($queued_titles[ array_search($queued_category,$queued_titles)]);
                    $queued_title = current($queued_titles);
                    // if (!empty($queued_category)) { print_r($all_titles); die("!"); }
                    $queued_text = $notes['text'];
                    $queued_text = WorklogData::line_clean_brackets($queued_text);
                    $queued_text = rtrim($queued_text,'- ');
                    if (!empty($queued_text)) $queued[] = [ 
                      'text' => $queued_text,
                      'category' => $queued_category,
                      'title' => $queued_title,
                      'brackets' => $queued_brackets,
                    ];
                  }
                  if ($notes['style']!='+') continue;
                  $output_notes[] = $notes['text'];
                  $note_skip = FALSE;
                  $note_brackets = WorklogData::line_get_brackets($notes['text']);
                  $note_line_number = $notes['linenum'];
                  foreach($note_brackets as $note_time) {
                    $note_time_offset = WorklogData::time_get_offset($note_time);
                    if (!empty($note_time_offset) && $note_time_offset < 0) { $note_skip = TRUE; }
                  }
                  $clean_note = WorklogData::line_clean_brackets($notes['text']);
                  if (!empty($clean_note) && !$note_skip) $clean_notes[ $note_line_number ] = $clean_note;
                }
              }
              $used = array();
              $tasktitle = trim($tasks['text'],'.');
              $tasknotes = implode(" ",$output_notes);
              $taskcleannote = !empty($clean_notes) ? implode(". ",$clean_notes).'.' : '';
              $taskline = $tasktitle . '. ' . $tasknotes;
              $entry_category = WorklogData::line_clean_brackets($category['text']);
              $category_brackets = WorklogData::line_get_brackets($category['text']);
              $category_projects = current( CLI::get_note_data_by_keys( 'Projects-'.$entry_category ) );
              $category_tasks = current( CLI::get_note_data_by_keys( 'Tasks-'.$entry_category ) );
              $category_statuses = current( CLI::get_note_data_by_keys( 'Statuses-'.$entry_category ) );
              $category_rate = current( CLI::get_note_data_by_keys( 'Client-'.$entry_category ) )['Client Rate'];
              $category_taxpercent = current( CLI::get_note_data_by_keys( 'Client-'.$entry_category ) )['Client Tax Percent'];
              $category_taxratio = (float) trim($category_taxpercent,'%') / 100.0;
              $category_multiplier = WorklogData::brackets_get_multiplier($category_brackets);
              if (empty($category_multiplier)) $category_multiplier = '1.0';

              $title_brackets = WorklogData::line_get_brackets($tasktitle);
              $title_multiplier = WorklogData::brackets_get_multiplier($title_brackets);
              if (empty($title_multiplier)) $title_multiplier = '1.0';
              $rate = WorklogData::brackets_get_rate($category_brackets);
              if (empty($rate) && !empty($category_rate)) $rate = $category_rate;
              $title_brackets = WorklogData::array_explode_values('/',$title_brackets);
              $title_fixedcost = WorklogData::brackets_get_cost($title_brackets);
              $task_brackets = WorklogData::line_get_brackets($taskline);
              $unknown_brackets = [];
              $task_line_number = $tasks['linenum'];
              $lowest = null;
              $highest = null;
              $offset = 0;
              $applied = array();
              foreach($task_brackets as $time) {
                // get offset
                $time_offset = WorklogData::time_get_offset($time);
                if (!empty($time_offset)) {
                  $offset += $time_offset;
                  $time_brackets[] = $time;
                  $used[] = $time;
                  continue;
                }
                $cost_bracket = WorklogData::brackets_get_cost($time);
                if (!empty($cost_bracket)) {
                  $cost_brackets[] = $cost_bracket;
                }
                // get timepoint
                $timepoint = WorklogData::time_get_timestamp_on_day($day['timestamp'],$time,$highest);
                if (!empty($timepoint)) {
                  if (empty($lowest)) $lowest = $timepoint;
                  if (empty($highest)) $highest = $timepoint;
                  if ($timepoint < $lowest) $lowest = $timepoint;
                  if ($timepoint > $highest) $highest = $timepoint;
                  $used[] = $time;
                  $time_brackets[] = $time;
                  continue;
                }
              }
              foreach($task_brackets as $k=>$v) {
                if (is_array($used) && !in_array($v,$used)) {
                  $task_brackets[$k] = '?'.$v.'?';
                  $unknown_brackets[] = $v;
                }
              }
              foreach($title_brackets as $k=>$v) {
                if (in_array($v,$used)) { unset($title_brackets[$k]); }
              }

              $all_brackets = array_merge(
                $day['day_brackets'],
                $category_brackets,
                $title_brackets
                //$task_brackets
              );
              $free_brackets = $all_brackets;

              $multiplier = $category_multiplier * $title_multiplier;
              
              $length = $highest - $lowest + $offset;
              $hours = Format::format_hours( round($length / "60.0" / "60.0",'2') );
              $hours_multiplied = Format::format_hours( $hours * $multiplier );
              $tasktitle = WorklogData::line_clean_brackets($tasktitle);
              $tasknote = WorklogData::line_clean_brackets($tasknote);
              $taskline = WorklogData::line_clean_brackets($taskline);
              
              $projects = @$category_info[ $cleancategory ]['projects'] ?: array();
              $taskproject = WorklogData::brackets_match_item($title_brackets,$projects);
              $tasks = @$category_info[ $cleancategory ]['tasks'] ?: array();
              $tasktask = WorklogData::brackets_match_item($title_brackets,$tasks);
              $statuses = @$category_info[ $cleancategory ]['statuses'] ?: array();
              $taskstatus = WorklogData::brackets_match_item($title_brackets,$statuses);
              // $taskfixedcost = WorklogData::brackets_get_cost($title_brackets);
                
              $free_brackets = WorklogData::brackets_remove_item($free_brackets,array(
                $taskproject,
                $tasktask,
                $taskstatus,
                $rate,
              ));
              

              $client_project =  WorklogData::brackets_match_item($all_brackets,$category_projects);
              $client_task =  WorklogData::brackets_match_item($all_brackets,$category_tasks);
              $client_status =  WorklogData::brackets_match_item($all_brackets,$category_statuses);
              $client_rate = Format::format_cost( $category_rate );
              $client_taxpercent = $category_taxpercent;
              $client_free_brackets = WorklogData::brackets_remove_item($all_brackets,array(
                $client_project,
                $client_task,
                $client_status,
                $client_rate,
              ));
              
              $client_project_details = current( CLI::get_note_data_by_keys( 'Project-'.$entry_category.'-'.$client_project ) );
              $client_project_details = Format::array_keys_remove_prefix( Format::normalize_array_keys( $client_project_details ),'project');
              $project_rate = null;
              if (!empty($client_project_details['name'])) $client_project = $client_project_details['name'];
              if (!empty($client_project_details['number'])) $client_project .= ' '.$client_project_details['number'];
              if (!empty($client_project_details['rate'])) $project_rate = Format::format_cost( $client_project_details['rate'] ); 
              
              
              
              if (!empty($category_projects)) { $taskproject = $client_project; }
              if (!empty($category_tasks)) $tasktask = $client_task;
              if (!empty($category_statuses)) $taskstatus = $client_status;
              if (!empty($category_rate)) $rate = $client_rate;
              if (!empty($project_rate)) $rate = $project_rate;
              
              $entry = array();
              $entry['day_text'] = $day['day_text'];
              $entry['stamp'] = $day['timestamp'];
              $entry['date'] = date('Y-m-d',$day['timestamp']);
              $entry['started_at'] = date('Y-m-d H:i:s', $lowest ?: $day['timestamp']);
              $entry['title'] = $tasktitle;
              $entry['note'] = $taskcleannote;
              $entry['notes'] = $clean_notes;
              $entry['queued'] = $queued;
              $entry['category_info'] = $category_info[ $cleancategory ];
              $entry['client'] = $entry_category;

              $entry['hours'] = $hours;
              $entry['multiplier'] = $multiplier; 
              $entry['category_multiplier'] = $category_multiplier; 
              $entry['title_multiplier'] = $title_multiplier; 
              $entry['hours_multiplied'] = $hours_multiplied;             
              $entry['rate'] = $rate;
              $entry['subtotal'] = is_numeric($rate) ? $hours_multiplied * $rate : 0;
              $entry['taxpercent'] = is_numeric($client_taxpercent) ? $client_taxpercent : 0;
              $entry['taxratio'] = is_numeric($category_taxratio) ? $category_taxratio : 0;
              $entry['tax'] = $entry['subtotal'] * $entry['taxratio'];
              
              $entry['project'] = $taskproject;
              $entry['client-project'] = $client_project;
              $entry['client-task'] = $client_task;
              $entry['client-status'] = $client_status;
              $entry['client-free-brackets'] = $client_free_brackets;
              $entry['client-rate'] = $client_rate;
              $entry['client-taxpercent'] = $client_taxpercent;
              $entry['client-$'] = is_numeric($client_rate) ? $hours_multiplied * $client_rate : 0;
              $entry['project-rate'] = $project_rate;
              $entry['task'] = $tasktask;
              $entry['task-fixedcost'] = $taskfixedcost;
              $entry['status'] = $taskstatus;
              $entry['project'] = $taskproject;
              $entry['total'] = $hours_multiplied;
              $entry['$'] = is_numeric($rate) ? $hours_multiplied * $rate : 0;
              if (!empty($cost_brackets))  {
                print_r($cost_brackets);
                $fixedcost = array_sum($cost_brackets);
                $entry['$'] = $fixedcost;
                $entry['subtotal'] = $fixedcost;
              }
              $entry['day_brackets'] = $day['day_brackets'];
              $entry['category_brackets'] = $category_brackets;
              $entry['time_brackets'] = $time_brackets;
              $entry['title_brackets'] = $title_brackets;
              $entry['task_brackets'] = $task_brackets;
              $entry['unknown_brackets'] = $unknown_brackets;
              $entry['brackets'] = $all_brackets;
              $entry['free_brackets'] = $free_brackets;
              $entry['day_line_number'] = $day_line_number;
              $entry['cat_line_number'] = $cat_line_number;
              $entry['line_number'] = $task_line_number;

              $all_titles[ $tasktitle ] = $tasktitle;

              $timestamp_key = date('Y-m-d',$day['timestamp']);
              if (!empty($options['bracket'])) {
                foreach($entry['brackets'] as $bracket) {
                  if (WorklogData::time_filter_compare($bracket,$options['bracket'])) {
                    $days[] = $entry;
                    // $days[ $timestamp_key ][ $entry_category ][] = $entry;
                    break;
                  }
                }
              } else {
                $days[] = $entry;
                // $days[ $timestamp_key ][ $entry_category ][] = $entry;
              }

            }
          }
        }
      }
    }
    return $days;

  }
  public static function brackets_match_item($brackets,$items) {
    if (empty($brackets) || empty($items)) return null;
    if (!is_array($brackets)) $brackets = [ $brackets ];
    if (!is_array($items)) $items = [ $items ];
    $match = null;
    foreach($brackets as $bracket) {
      foreach($items as $item) {
        $bracketkey = Format::normalize_key($bracket);
        $itemkey = Format::normalize_key($item);
        if ($bracketkey == $itemkey) {
          $match = $item;
          break;
        }
      }
    }
    return $match;
  }
  public static function brackets_remove_item($brackets,$items) {
    $brackets_without_items = $brackets;
    foreach($brackets as $b=>$bracket) {
      foreach($items as $item) {
        $bracketkey = Format::normalize_key($bracket);
        $itemkey = Format::normalize_key($item);
        if ($bracketkey == $itemkey) {
          unset($brackets_without_items[$b]);
        }
      }
    }
    return array_values($brackets_without_items);
  }
  public static function line_get_brackets($text) {

    $matches = array();
    preg_match_all('/\(([^\)]+)\)/i',$text,$matches);
    $return = @is_array($matches[1]) ? $matches[1] : array();
    return $return;

  }
  public static function brackets_get_multiplier($brackets) {

    foreach($brackets as $bracket) {
      $is_starred = substr($bracket,0,1)=='*';
      $is_numeric = is_numeric(substr($bracket,1));
      if ($is_starred && $is_numeric) return substr($bracket,1);
    }
    return null;

  }
  public static function brackets_get_rate($brackets) {

    foreach($brackets as $bracket) {
      $is_dollarsigned = substr($bracket,0,1)=='$';
      $is_numeric = is_numeric(substr($bracket,1));
      if ($is_dollarsigned && $is_numeric) return substr($bracket,1);
    }
    return null;

  }
  public static function brackets_get_cost($brackets) {

    if (!is_array($brackets) && !empty($brackets)) 
      $brackets = [ $brackets ];
      
    foreach($brackets as $bracket) {
      $is_dollarsigned_or_negative = substr($bracket,0,1)=='$' || substr($bracket,0,2)=='-$';
      $bracket_cleaned = strtr($bracket,['$'=>'',','=>'']);
      $is_numeric = is_numeric($bracket_cleaned);
      if ($is_dollarsigned_or_negative && $is_numeric) return $bracket_cleaned;
    }
    return null;

  }
  public static function timetool_filter_since($compare,$compare_since) {

    // compare
    if (empty($compare)) return false;
    if (!is_numeric($compare)) $compare = strtotime($compare);
    if (empty($compare)) return false;
    // compare_since
    if (empty($compare_since)) return false;
    if (!is_numeric($compare_since)) $compare_since = strtotime($compare_since);
    if (empty($compare_since)) return false;
    // print_r(array(
    //   $compare,
    //   $compare_since,
    //   ( $compare > $compare_since )
    // ));
    // return
    return ( $compare >= $compare_since );

  }
  public static function timetool_filter_until($compare,$compare_until) {

    // compare
    if (empty($compare)) return false;
    if (!is_numeric($compare)) $compare = strtotime($compare);
    if (empty($compare)) return false;
    // compare_until
    if (empty($compare_until)) return false;
    if (!is_numeric($compare_until)) $compare_until = strtotime($compare_until);
    if (empty($compare_until)) return false;
    // print_r(array(
    //   $compare,
    //   $compare_until,
    //   ( $compare > $compare_until )
    // ));
    // return
    return ( $compare <= $compare_until );

  }
  public static function timetool_filter_compare($compare,$compare_to) {

    // compare
    if (empty($compare)) return false;
    $compare = strtolower($compare);
    $compare = preg_replace('/[^a-z]+/','',$compare);
    if (empty($compare)) return false;
    // compare_to
    if (empty($compare_to)) return false;
    $compare_to = strtolower($compare_to);
    $compare_to = preg_replace('/[^a-z]+/','',$compare_to);
    if (empty($compare_to)) return false;
    // return
    return ( $compare == $compare_to );

  }
  public static function time_get_offset($time) {

    $offset = 0;
    if (preg_match('/[\+\-]/i',$time)) {
      $hours = preg_match('/h/i',$time);
      $mins = preg_match('/m/i',$time);
      if (!$hours && !$mins && !($hours && $mins)) return null;
      if (preg_match('/[\+]/i',$time)) {
        $offset = preg_replace('/[^0-9\.]+/i','',$time);
        if (!is_numeric($offset)) $offset = 0;
        $offset = $hours ? floor( $offset * 60 * 60 ) : floor( $offset * 60 );
      }
      else if (preg_match('/^\s*[\-]/i',$time)) {
        $offset = preg_replace('/[^0-9\.]+/i','',$time);
        if (!is_numeric($offset)) $offset = 0;
        $offset = $hours ?
          floor( $offset * 60 * 60 ) :
          floor( $offset * 60 );

        $offset *= -1;
      }
    }
    return $offset;

  }
  public static function line_clean_brackets($text) {

    $text = preg_replace("/\s*\([^\(]+\)/i","",$text);
    $text = trim($text);
    $text = trim($text,'.');
    $text = trim($text);
    return $text;

  }
  public static function array_explode_values($delimiter,$array) {

    $final = array();
    if (is_array($array)) foreach($array as $k=>$value) {
      foreach(explode($delimiter,$value) as $v) {
        $v = trim($v);
        if (!empty($v)) $final[] = $v;
      }
    }
    return $final;

  }
  public static function time_get_timestamp_on_day($day_timestamp,$time,$next_day_if_after_timestamp) {

    $timepoint = null;
    if (!preg_match('/[\+\-]/i',$time)) {
      $am = preg_match('/a/i',$time);
      $pm = preg_match('/p/i',$time);
      $colon = preg_match('/:/i',$time);
      if (!$am && !$pm && !($am && $pm)) return null;
      if (!$colon) return null;
      $timestr = preg_replace('/[^0-9\:]+/i','',$time);
      if (empty($timestr)) return null;
      if ($am) $timestr .= 'am';
      else if ($pm) $timestr .= 'pm';
      $timepoint = strtotime(date('Y-m-d',$day_timestamp).' '.$timestr);
      if ($timepoint < $next_day_if_after_timestamp) $timepoint += (24 * 60 * 60);
      if (empty($timepoint)) return null;
    }
    return $timepoint;

  }

}
