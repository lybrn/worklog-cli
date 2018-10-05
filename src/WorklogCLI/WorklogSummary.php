<?php
namespace WorklogCLI;
class WorklogSummary {

  public static function summary_days($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $day_line = $item['day_line_number'];
      $day_date = $item['date'];
      $day_text = $item['day_text'];
      $day_summary = array(
        'date'=>$day_date,
        'day'=>$day_text,
      );
      $sortkey = implode('-',$day_summary);
      $summary[ $sortkey ] = $day_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }
  public static function summary_day_lines($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $day_line = $item['day_line_number'];
      $day_date = $item['date'];
      $day_text = $item['day_text'];
      $day_summary = array(
        'date'=>$day_date,
        'day'=>$day_text,
        'line'=>$day_line,
      );
      $sortkey = implode('-',$day_summary);
      $summary[ $sortkey ] = $day_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }
  public static function summary_categories($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $category_line = $item['cat_line_number'];
      $category_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['client'] );;
      $category_text = $item['client'];
      $category_brackets = $item['category_brackets'];
      $category_summary = array(
        'key'=>$category_key,
        'text'=>$category_text,
        'brackets'=>$category_brackets,
      );
      $sortkey = $category_summary;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($category_summary['key']);
      $summary[ $sortkey ] = $category_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }        
  public static function summary_category_lines($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $category_line = $item['cat_line_number'];
      $category_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['client'] );;
      $category_text = $item['client'];
      $category_brackets = $item['category_brackets'];
      $category_summary = array(
        'key'=>$category_key,
        'text'=>$category_text,
        'brackets'=>$category_brackets,
        'line'=>$category_line
      );
      $sortkey = $category_summary;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($category_summary['key']);
      $summary[ $sortkey ] = $category_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }      
  public static function summary_tasks($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $task_line = $item['line_number'];
      $task_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['title'] );;
      $task_category = $item['client'];
      $task_text = $item['title'];
      $task_brackets = $item['title_brackets'];
      $task_summary = array(
        'key'=>$task_key,
        'text'=>$task_text,
//        'category'=>$task_category,
        'brackets'=>$task_brackets,
      );
      $sortkey = $task_summary;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($task_summary['key']);
      $summary[ $sortkey ] = $task_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }        
  public static function summary_task_names($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $task_line = $item['line_number'];
      $task_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['title'] );;
      $task_category = $item['client'];
      $task_text = $item['title'];
      $task_brackets = $item['title_brackets'];
      $task_summary = array(
        'key'=>$task_key,
        'text'=>$task_text,
  //        'category'=>$task_category,
        //'brackets'=>$task_brackets,
      );
      $sortkey = $task_summary;
      //$sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($task_summary['key']);
      $summary[ $sortkey ] = $task_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }           
  public static function summary_task_lines($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $task_line = $item['line_number'];
      $task_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['title'] );;
      $task_category = $item['client'];
      $task_text = $item['title'];
      $task_brackets = $item['title_brackets'];
      $task_summary = array(
        'key'=>$task_key,
        'text'=>$task_text,
        //'category'=>$task_category,
        'brackets'=>$task_brackets,
        'line'=>$task_line,
      );
      $sortkey = $task_summary;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($task_summary['key']);
      $summary[ $sortkey ] = $task_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }        
  public static function summary_entry_lines($parsed,$args=array()) {
                
    // info
    $summary = array();
    foreach($parsed as $item) {
      $task_line = $item['line_number'];
      $task_key = $projectkey = \WorklogCLI\Format::normalize_key( $item['title'] );;
      $task_text = $item['title'];
      $task_client = $item['client'];
      $task_project = $item['project'];
      $task_tasktype = $item['task'];
      $task_status = $item['status'];
      $task_brackets = $item['title_brackets'];
      $task_brackets = \WorklogCLI\WorklogData::brackets_remove_item($task_brackets,array(
        $task_project,
        $task_tasktype,
        $task_status
      ));
      $task_summary = array(
        'key'=>$task_key,
        'text'=>$task_text,
        'client'=>$task_client,
        'project'=>$task_project,
        'type'=>$task_tasktype,
        'status'=>$task_status,
        'brackets'=>$task_brackets,
        'line'=>$task_line,
      );
      $sortkey = $task_summary;
      $sortkey['brackets'] = implode('-',$sortkey['brackets']);
      $sortkey = implode('-',$sortkey);
      unset($task_summary['key']);
      if (!empty($task_summary['brackets'])) {
        $task_summary['brackets'] = '('.implode(', ',$task_summary['brackets']).')';
      }
      $summary[ $sortkey ] = $task_summary;
    }      
    ksort($summary,SORT_NATURAL);  
    return array_values($summary);
    
  }          
  public static function summary_invoice($parsed,$args=array()) {
                  
      $invoice = array(
        'pricing'=>array(),
        'timeline'=>array(),
        'client' => array(),
        'invoice' => array(),
        'projects' => array(),
      );
      
      // info
      $info = array();
      foreach($parsed as $item) {
        if (is_array($item['category_info'])) foreach($item['category_info'] as $k=>$v) {
          $info[$k] = $v;
        }
      }      
      $invoice['client'] = current(\WorklogCLI\WorklogData::get_clients_data($parsed));
      $invoice['invoice'] = current(\WorklogCLI\WorklogData::get_invoices_data($parsed));
      $invoice['pricing'] = \WorklogCLI\WorklogData::get_pricing_data($parsed);
      $invoice['projects'] = \WorklogCLI\WorklogData::get_grouped_data($parsed);
      $invoice['timeline'] = \WorklogCLI\WorklogData::get_timeline_data($parsed,$args);
      $invoice['entries'] = \WorklogCLI\WorklogData::get_entries_data($parsed,$args);
    
      return $invoice;
      
    }    
    public static function summary_totals($parsed,$args=array()) {
      $project_hours = array();
      $summary_lines = array();
      foreach($parsed as $item) {
        $projectkey = \WorklogCLI\Format::normalize_key( $item['project'] );
        $linekey = \WorklogCLI\Format::normalize_key( $projectkey.'-'.$item['title'] );
        if (empty($summary_lines[ $linekey ]['title'])) {
          $summary_lines[ $linekey ]['project'] = $item['project'];
          $summary_lines[ $linekey ]['title'] = $item['title'];
          $summary_lines[ $linekey ]['hours'] = \WorklogCLI\Format::format_hours($item['total']);
          $summary_lines[ $linekey ]['status'] = $item['status'];
          $summary_lines[ $linekey ]['sittings'] = 1;
        }
        else {
          $summary_lines[ $linekey ]['status'] = $item['status'];
          $summary_lines[ $linekey ]['hours'] += \WorklogCLI\Format::format_hours($item['total']);
          $summary_lines[ $linekey ]['sittings']++;
        }
        if (empty($project_hours[ $projectkey ])) {
          $project_hours[ $projectkey ] = \WorklogCLI\Format::format_hours($item['total']);          
        } else {
          $project_hours[ $projectkey ] += \WorklogCLI\Format::format_hours($item['total']);          
        }
        
      }
      
      $sorted_lines = array();
      foreach($summary_lines as $line_key=>$line) {
        $projectkey = \WorklogCLI\Format::normalize_key( $line['project'] );
        $key = implode('-',array(
          $project_hours[ $projectkey ],
          $line['project'],
          floor($line['hours']*100),
          $line_key,
        ));
        $sorted_lines[ $key ] = $line;
        $sorted_lines[ $key ]['project'] .= ' ('.$project_hours[ $projectkey ].')';
        $sorted_lines[ $key ]['hours'] = \WorklogCLI\Format::format_hours($sorted_lines[ $key ]['hours']);        
      }
      krsort($sorted_lines,SORT_NATURAL);
      
      $sorted_lines = array_values($sorted_lines);
      
      return $sorted_lines;
      
    }    
    public static function summary_review1($parsed,$args=array()) {
            
      $rows = array();
      foreach($parsed as $item) {
        $row = array();
        $row['start'] = date('H:i',strtotime($item['started_at']));
        $row['client'] = $item['client'];
        $row['task'] = $item['title'];
        $row['total'] = !empty($item['$']) ? '$'.\WorklogCLI\Format::format_cost($item['$']) : '';
        $row['project'] = $item['project'];
        $rows[] = $row;
      }
      return $rows;
      
    }
    public static function summary_review2($parsed,$args=array()) {
            
      $rows = array();
      foreach($parsed as $item) {
        $row = array();
        $row['start'] = date('Y-m-d H:i',strtotime($item['started_at']));
        $row['client'] = $item['client'];
        $row['client'] .= !empty($item['rate']) ? ' ($'.$item['rate'].')' : '';
        $row['task'] = $item['title'];
        $row['total'] = !empty($item['$']) ? '$'.\WorklogCLI\Format::format_cost($item['$']) : '';
        $row['total'] .= !empty($item['$']) ? ' ('.$item['hours'].' * '.$item['multiplier'].')' : ''; 
        $row['project'] = $item['project'];
        $row['project'] .= !empty($item['task']) ? ' ('.$item['task'].')' : '';
        $row['project'] = trim($item['project']);
        $row['line'] = $item['line_number'];
        $rows[] = $row;
      }
      return $rows;
      
    }    
    public static function summary_review($parsed,$args=array()) {
            
      $rows = array();
      foreach($parsed as $item) {
        $row = array();
        $row['start'] = date('Y-m-d H:i',strtotime($item['started_at']));
        $row['client'] = $item['client'];
        $row['title'] = $item['title'];
        $row['project'] = $item['project'];
        $row['task'] = $item['task'];
        $row['status'] = $item['status'];
        //$row['brackets'] = $item['brackets'];
        $row['hours'] = \WorklogCLI\Format::format_hours($item['hours']);
        $row['multiplier'] = $item['multiplier'];
        $row['total'] = \WorklogCLI\Format::format_hours($item['total']);
        $row['rate'] = $item['rate'];
        $row['$'] = $item['$'];
        $rows[] = $row;
      }
      return $rows;
      
    }
    public static function summary_billing($parsed,$args=array()) {
            
      $client_bills = array();
      foreach($parsed as $item) {
        $client_bill = @$client_bills[ $item['client'] ] ?: array(
          'client'=>$item['client'],
          'projects'=>array(),
          'sittings'=>0,
          'hours'=>0.0,
          'total'=>0.0,
        );
        if (!empty($item['total'] )) $client_bill['hours'] += \WorklogCLI\Format::format_hours($item['total']);
        if (!empty($item['$'])) $client_bill['total'] += \WorklogCLI\Format::format_cost($item['$']);
        $client_bill['sittings'] += 1;
        if (!empty($item['project'])) $client_bill['projects'][ $item['project'] ] = $item['project'];
        $client_bills[ $item['client'] ] = $client_bill;
      }
      foreach($client_bills as &$client_bill) {
        $client_bill['total'] = '$'.$client_bill['total'];
        $client_bill['sittings'] = $client_bill['sittings']; //.' sittings';
        $client_bill['projects'] = count($client_bill['projects']); //.' projects';
      }           
      return $client_bills;
      
    }    
    public static function summary_brackets($parsed,$args=array()) {
            
      $brackets = array();
      foreach($parsed as $item) {
        foreach($item['brackets'] as  $bracket) {
          $brackets[] = $bracket;
        }
      }
      $brackets = array_unique($brackets);
      sort($brackets); 
      return $brackets;
      
    }    

    public static function summary_times($parsed,$args=array()) {
            
      $rows = array();
      foreach($parsed as $item) {
        $row = array();
        $row['text'] = $item['title'];
        $row['client'] = $item['client'];
        $row['hours'] = \WorklogCLI\Format::format_hours($item['hours']);
        $row['time_brackets'] = $item['time_brackets'];
        $row['mult'] = $item['multiplier'];
        $row['total'] = \WorklogCLI\Format::format_hours($item['total']);
        $row['line'] = $item['line_number'];
        $rows[ $item['hours'].'-'.implode('-',$row) ] = $row;
      }
      krsort($rows,SORT_NATURAL);
      $rows = array_values($rows);
      return $rows;
      
    }
    public static function summary_category_info($parsed,$args=array()) {
      
      $info = array();
      foreach($parsed as $item) {
        foreach($item['category_info'] as $k=>$v) {
          $info[$k] = $v;
        }
      }      
      
      $rows = array();
      foreach($info as $k => $v) {
        $row = array();
        $row['key'] = $k;
        $row['value'] = $v;
        $rows[ $k ] = $row;
      }
      krsort($rows);
      $rows = array_values($rows);
      return $rows;
      
    }    
    public static function summary_notes($parsed,$args=array()) {
            
      $rows = array();
      foreach($parsed as $item) {
                
        $row = array();
        $row['line'] = $item['line_number'];
        $row['client'] = $item['client'];
        $row['note'] = '### '.$item['title'];
        $row['total'] = \WorklogCLI\Format::format_hours($item['total']);
        $rows[] = $row;
        
        foreach($item['notes'] as $note_text) {
          $row = array();
          $row['line'] = $item['line_number'];
          $row['client'] = $item['client'];
          $row['note'] = '+ '.$note_text;
          $row['total'] = \WorklogCLI\Format::format_hours($item['total']);
          $rows[] = $row;
        }

        $row = array();
        $row['line'] = $item['line_number'];
        $row['client'] = $item['client'];
        $row['note'] = ' ';
        $row['total'] = \WorklogCLI\Format::format_hours($item['total']);
        $rows[] = $row;
      }
      $rows = array_values($rows);
      return $rows;
      
    }   
    public static function summary_markdown($parsed,$args=array()) {
            
      $current_day = null;
      $current_client = null;
      
      $lines = array();
      foreach($parsed as $item) {
                
        if ($current_day != $item['day_text']) {
          $line = $item['day_text'];
          if (!empty($item['day_brackets'])) 
            $line .= ' ('.implode(') (',$item['day_brackets']).')';
          $lines[] = $line;
          $lines[] = str_repeat('=',strlen($item['day_text']));
          $lines[] = '';
          $current_day = $item['day_text'];
          $current_client = null;
        }

        if ($current_client != $item['client']) {
          $line = $item['client'];
          if (!empty($item['rate'])) 
            $line .= ' ($' . $item['rate'] . ')';
          $lines[] = $line;
          $lines[] = str_repeat('-',strlen($item['client']));
          $lines[] = '';
          $current_client = $item['client'];
        }

        $line = '### '.$item['title'];
        $matched_brackets = array();
        if (!empty($item['project'])) $matched_brackets[] = $item['project'];
        if (!empty($item['task'])) $matched_brackets[] = $item['task'];
        if (!empty($item['status'])) $matched_brackets[] = $item['status'];
        if (!empty($matched_brackets)) 
          $line .= ' ('.implode(' / ',$matched_brackets).')';        
        if (!empty($item['free_brackets'])) 
          $line .= ' ('.implode(' / ',$item['free_brackets']).')';
        if (!empty($item['total']) && (float) $item['total'] != 0) 
          $line .= ' (Hours: '.$item['total'].')';
        $line .= ' (Line: '.$item['line_number'].')';
 
        $lines[] = $line;
        $lines[] = '';
        
        foreach($item['notes'] as $note_text) {

          $lines[] = '+ '.$note_text;
        }

        $lines[] = ' ';
      }
      $lines = array_values($lines);
      return $lines;
      
    }        
    public static function summary_logexport($parsed,$args=array()) {
            
      $current_day = null;
      $current_client = null;
      $owner = null;
      
      foreach($args as $arg) 
        if (substr($arg,0,1)=='@') 
          $owner = $arg;
      
      $lines = array();
      foreach($parsed as $item) {
                
        if ($current_day != $item['day_text']) {
          $line = $item['day_text'];
          if (!empty($owner)) 
            $line .= ' ('.$owner.')';
          $lines[] = $line;
          $lines[] = str_repeat('=',strlen($item['day_text']));
          $lines[] = '';
          $current_day = $item['day_text'];
          $current_client = null;
        }

        if ($current_client != $item['client']) {
          $line = $item['client'];
          if (!empty($owner)) 
            $line .= ' ('.$owner.')';
          // if (!empty($item['rate'])) 
          //   $line .= ' ($' . $item['rate'] . ')';
          $lines[] = $line;
          $lines[] = str_repeat('-',strlen($item['client']));
          $lines[] = '';
          $current_client = $item['client'];
        }

        $line = '### '.$item['title'];
        $matched_brackets = array();
        if (!empty($item['project'])) $matched_brackets[] = $item['project'];
        if (!empty($item['task'])) $matched_brackets[] = $item['task'];
        if (!empty($item['status'])) $matched_brackets[] = $item['status'];
        if (!empty($matched_brackets)) 
          $line .= ' ('.implode(' / ',$matched_brackets).')';        
        if (!empty($item['total']) && (float) $item['total'] != 0) 
          $line .= ' (+'.$item['total'].'h)';
        if (!empty($owner)) 
          $line .= ' ('.$owner.')';


        $lines[] = $line;
        $lines[] = '';
        
        foreach($item['notes'] as $note_text) {

          $lines[] = '+ '.$note_text;
        }

        $lines[] = ' ';
      }
      $lines = array_values($lines);
      return $lines;
      
    }     
}
