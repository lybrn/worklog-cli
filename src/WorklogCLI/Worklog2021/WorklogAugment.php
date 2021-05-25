<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
use WorklogCLI\CLI;
use WorklogCLI\Format;
class WorklogAugment {
  
  public static function add_augmented_data($entries) {
    
    foreach($entries as &$entry) {
      
      // day 
      $day_timestamp = @strtotime($entry['day_text_nobrackets']);
      $day_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['day_brackets_all']);
      $day_brackets_remaining = $entry['day_brackets_all'];
      $day_brackets_remaining = WorklogParsing::brackets_remove_item($day_brackets_remaining,[ $day_brackets_yyyymmdd ]);
      
      $entry['day_timestamp'] = $day_timestamp;
      $entry['day_brackets_yyyymmdd'] = $day_yyyymmdd;
      $entry['day_brackets_remaining'] = $day_brackets_remaining;
      
      // category
      $category_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['category_brackets_all']);
      $category_brackets_remaining = $entry['category_brackets_all'];
      $category_brackets_remaining = WorklogParsing::brackets_remove_item($category_brackets_remaining,[ $category_brackets_yyyymmdd ]);
      $entry['category_brackets_yyyymmdd'] = $category_brackets_yyyymmdd;
      $entry['category_brackets_remaining'] = $category_brackets_remaining;

      // title
      $title_text_precolon = WorklogParsing::line_get_precolon($entry['title_text_nobrackets']);
      $title_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['title_brackets_all']);
      $title_brackets_time = WorklogParsing::brackets_get_time($entry['title_brackets_all']);
      $title_brackets_multiplier = WorklogParsing::brackets_get_multiplier($entry['title_brackets_all']);
      $title_brackets_remaining = $entry['title_brackets_all'];
      $title_brackets_remaining = WorklogParsing::brackets_remove_item($title_brackets_remaining,[ $title_brackets_yyyymmdd ]);
      $title_brackets_remaining = WorklogParsing::brackets_remove_item($title_brackets_remaining,[ $title_brackets_time ]);
      $title_brackets_remaining = WorklogParsing::brackets_remove_item($title_brackets_remaining,[ $title_brackets_multiplier ]);
      $entry['title_text_precolon'] = $title_text_precolon;
      $entry['title_brackets_yyyymmdd'] = $title_brackets_yyyymmdd;
      $entry['title_brackets_time'] = $title_brackets_time;
      $entry['title_brackets_multiplier'] = $title_brackets_multiplier;
      $entry['title_brackets_remaining'] = $title_brackets_remaining;

      // note rows
      if (is_array($entry['note_rows'])) {
        foreach($entry['note_rows'] as &$note_row) {
          
          // note
          $note_brackets_time = WorklogParsing::brackets_get_time($note_row['note_brackets_all']);
          $note_brackets_remaining = $note_row['note_brackets_all'];
          $note_brackets_remaining = WorklogParsing::brackets_remove_item($note_brackets_remaining,[ $note_brackets_time ]);
          $note_row['note_brackets_time'] = $note_brackets_time;
          $note_row['note_brackets_remaining'] = $note_brackets_remaining;
          //ksort($note_row);

        }
      }
      
      $entry['entry_brackets_all'] = array_merge(
        $entry['day_brackets_all'],
        $entry['category_brackets_all'],
        $entry['title_text_precolon'],
        $entry['title_brackets_all'],
        $entry['note_brackets_all'],
      );

      //ksort($bare_entry);
      
    }
    

    return $entries;
    
  }
  public static function add_filterable_data($entries) {
    
    foreach($entries as &$entry) {
      
      // day 
      $day_timestamp = @strtotime($entry['day_text_nobrackets']);
      
      $entry['filter_day_timestamp'] = $day_timestamp;
      
      // tags
      $possible_tags = [];

      // category text
      if (!empty($entry['category_text_nobrackets']))        
        $possible_tags[] = $entry['category_text_nobrackets'];        

      // bracket tags
      if (is_array($entry['day_brackets_all']))
        $possible_tags = array_merge($possible_tags,$entry['day_brackets_all']);
      if (is_array($entry['category_brackets_all']))
        $possible_tags = array_merge($possible_tags,$entry['category_brackets_all']);
      if (is_array($entry['title_brackets_all']))        
        $possible_tags = array_merge($possible_tags,$entry['title_brackets_all']);
      if (is_array($entry['title_precolon']))        
        $possible_tags = array_merge($possible_tags,$entry['title_precolon']);
                  
      // client name tags
      $category = $entry['category_text_nobrackets'];
      $client_key = 'Client-'.$category;
      $client_data = current( CLI::get_note_data_by_keys( $client_key ) );
      $client_data_normalized = [];
      if (is_array($client_data)) foreach($client_data as $key=>$value) {
        $normalized_key = WorklogNormalize::normalize_key($key,'- ','_');
        $client_data_normalized[$normalized_key] = $value;
      }      
      if (!empty($client_data_normalized['client_full_name']))        
        $possible_tags[] = $client_data_normalized['client_full_name'];
      if (!empty($client_data_normalized['client_short_name']))        
        $possible_tags[] = $client_data_normalized['client_short_name'];
      if (!empty($client_data_normalized['client_tight_name']))        
        $possible_tags[] = $client_data_normalized['client_tight_name'];
      if (!empty($client_data_normalized['client_cli_name']))        
        $possible_tags[] = $client_data_normalized['client_cli_name'];

      $filter_tags_all = [];
      foreach($possible_tags as $possible_tag) {
        if (empty($possible_tag)) continue;
        if (!is_string($possible_tag)) continue;
        if (is_numeric($possible_tag)) continue;
        if (strtotime($possible_tag)) continue;
        $key = WorklogFilter::normalize($possible_tag);
        $filter_tags_all[ $key ] = $key;
      }            

      $entry['filter_tags_all'] = $filter_tags_all;

    }
    

    return $entries;
    
  }  
  public static function add_date_data($entries) {
    
    foreach($entries as &$entry) {
      
      // day 
      $day_timestamp = @strtotime($entry['day_text_nobrackets']);
      $day_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['day_brackets_all']);
      
      $entry['day_timestamp'] = $day_timestamp;
      $entry['day_brackets_yyyymmdd'] = $day_brackets_yyyymmdd;
      
      // category
      $category_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['category_brackets_all']);
      $entry['category_brackets_yyyymmdd'] = $category_brackets_yyyymmdd;

      // title
      $title_brackets_yyyymmdd = WorklogParsing::brackets_get_yyyymmdd($entry['title_brackets_all']);
      $entry['title_brackets_yyyymmdd'] = $title_brackets_yyyymmdd;
      
      $entry['entry_brackets_yyyymmdd'] = []; 
      if (!empty($day_brackets_yyyymmdd)) 
        $entry['entry_brackets_yyyymmdd'][] = $day_brackets_yyyymmdd;
      if (!empty($category_brackets_yyyymmdd)) 
        $entry['entry_brackets_yyyymmdd'][] = $category_brackets_yyyymmdd;
      if (!empty($title_brackets_yyyymmdd)) 
        $entry['entry_brackets_yyyymmdd'][] = $title_brackets_yyyymmdd;

    }
    
    return $entries;
    
  }  
  public static function add_title_category_data($entries) {
  
    $count_sittings = [];
    $count_titles = [];
    $first_line_number = [];
  
    foreach($entries as $entry) {
          
      $category_key = $entry['category_key'];
      $title_category_key = $entry['title_category_key'];

      if (!empty($title_category_key)) {    
        $count_sittings[$title_category_key]++;
        if (empty($first_line_number[$title_category_key]))
          $first_line_number[$title_category_key] = $entry['title_line_number'];
      }
      
      if (!empty($category_key) && !empty($title_category_key)) {        
        $count_titles[$category_key][$title_category_key] = $title_category_key;
      }
      
    }
    foreach($entries as &$entry) {
      
      $category_key = $entry['category_key'];
      if (!empty($category_key)) {
        $entry['category_sittings'] = @$count_sittings[$category_key] ?: null;
        $entry['category_titles'] = count($count_titles[$category_key]);
        $entry['category_first_line_number'] = @$first_line_number[$category_key] ?: null;
      }
      
      $title_category_key = $entry['title_category_key'];
      if (!empty($title_category_key)) {
        $entry['title_category_sittings'] = @$count_sittings[$title_category_key] ?: null;
        $entry['title_category_first_line_number'] = @$first_line_number[$title_category_key] ?: null;
        $entry['title_category_brackets_all'] = array_merge(
          $entry['category_brackets_all'] ?: [],
          $entry['title_brackets_all'] ?: []
        );
        $entry['title_category_tags_all'] = array_merge(
          $entry['category_tags_all'] ?: [],
          $entry['title_tags_all'] ?: []
        );        
      }

    }
    return $entries;
  }
  public static function add_client_data($entries) {
  
    foreach($entries as &$entry) {
      $category = $entry['category_text_nobrackets'];
      $client_key = 'Client-'.$category;
      $client_data = current( CLI::get_note_data_by_keys( $client_key ) );
      if (is_array($client_data)) foreach($client_data as $key=>$value) {
        $normalized_key = WorklogNormalize::normalize_key($key,'- ','_');
        $entry[$normalized_key] = $value;
      }      
      $client_key = WorklogNormalize::normalize_key($entry['client_full_name'],'-_','-');
      $entry['client_key'] = $client_key;
    }
    
    $count_sittings = [];
    $count_titles = [];
    
    foreach($entries as &$entry) {
      
      $client_key = $entry['client_key'];
      $title_key = $entry['title_key'];
      
      if (!empty($client_key)) {
        $count_sittings[$client_key]++;        
      }
      if (!empty($client_key) && !empty($title_key)) {
        $count_titles[$client_key][$title_key] = $title_key;
      }
    }
    
    foreach($entries as &$entry) {
      $client_key = $entry['client_key'];
      $title_key = $entry['title_key'];
      $entry['client_sittings'] = @$count_sittings[$client_key] ?: null;
      $entry['client_titles'] = @count($count_titles[$client_key]) ?: null;
    }

    return $entries;
  }   
  public static function add_client_timetracking_data($entries) {
    
    $count_tracked_hours = [];
    
    foreach($entries as &$entry) {
      
      $client_key = $entry['client_key'];
      if (empty($client_key)) continue;
    
      $entry_tracked_hours = $entry['timetracking_tracked_hours'];
      if (!is_numeric($entry_tracked_hours)) continue;
      
      $client_total_inited = isset($count_tracked_hours[$client_key]);
      if (!$client_total_inited) $count_tracked_hours[$client_key] = 0;
          
      $count_tracked_hours[$client_key] += $entry_tracked_hours;
      
    }
    
    foreach($entries as &$entry) {
      
      $client_key = $entry['client_key'];
      if (empty($client_key)) continue;

      $client_total_inited = isset($count_tracked_hours[$client_key]);
      
      if ($client_total_inited) {
        $entry['client_tracked_hours'] = $count_tracked_hours[$client_key];
      } 

    }
    
    return $entries;
    
  }
  public static function add_client_project_data($entries) {

    $project_lists_by_client = [];
    $project_data_by_client_project = [];

    foreach($entries as &$entry) {
      $entry['client_project_name'] = [];
      // $entry['client_project_rejected'] = [];
      $title_category_tags_all = $entry['title_category_tags_all'];
      $client_short_name = $entry['client_short_name'];
      $client_projects_notedata_key = 'Projects-'.$client_short_name;
      
      if (!array_key_exists($client_projects_notedata_key,$project_lists_by_client)) {
        $project_lists_by_client[ $client_projects_notedata_key ] = current( CLI::get_note_data_by_keys( $client_projects_notedata_key ) );
      }
      $client_projects_list = $project_lists_by_client[ $client_projects_notedata_key ];
            
      if (is_array($title_category_tags_all)) foreach($title_category_tags_all as $tag) {
        $tag_normalized = WorklogNormalize::normalize_key($tag);
        if (is_array($client_projects_list)) foreach($client_projects_list as $project) {
          $project_normalized = WorklogNormalize::normalize_key($project);
          if ($tag_normalized==$project_normalized) {
            
            $client_project_data_key = 'Project-'.$client_short_name.'-'.$project;            
            if (!array_key_exists($client_project_data_key,$project_data_by_client_project)) {
              $data_to_cache = current( CLI::get_note_data_by_keys( $client_project_data_key ) );;
              $data_to_cache = WorklogNormalize::normalize_array_keys($data_to_cache);
              $data_to_cache = WorklogNormalize::array_keys_remove_prefix($data_to_cache,'project');
              $project_data_by_client_project[ $client_project_data_key ] = $data_to_cache;
              // print_r([ $client_project_data_key => $data_to_cache ]);
            }                 
            $client_project_data = $project_data_by_client_project[ $client_project_data_key ];
            
            $entry['client_project_key'] = null;
            $entry['client_project_name'] = @$client_project_data['name'] ?: $project;
            $entry['client_project_number'] = @$client_project_data['number'] ?: null;
            $entry['client_project_client'] = @$client_project_data['client'] ?: null;
            $entry['client_project_distribute'] = @$client_project_data['distribute'] ?: null;
            $entry['client_project_title_key'] = null;

            $client_project_key = implode('-',[
              $entry['client_key'],
              $entry['client_project_name'],
            ]);
            $client_project_key = WorklogNormalize::normalize_key($client_project_key,'_- ','-');
            
            $entry['client_project_key'] = $client_project_key;
            
            $client_project_title_key = implode('-',[
              $entry['client_project_key'],
              $entry['title_key'],
            ]);
            $client_project_title_key = WorklogNormalize::normalize_key($client_project_title_key,'_- ','-');

            $entry['client_project_title_key'] = $client_project_title_key;
            
            $client_project_data = $project_data_by_client_project[ $client_project_data_key ];
            
          } else {
            // $entry['client_project_rejected'][$tag_normalized] = $tag;
          }
        }
      }
      
    }
    
    $count_sittings = [];
    $count_titles = [];
    
    foreach($entries as &$entry) {
      
      $client_project_key = $entry['client_project_key'];
      
      if (!empty($client_project_key)) {
        $count_sittings[$client_project_key]++;      
        if (empty($first_line_number[$client_project_key])) {
          $first_line_number[$client_project_key] = $entry['category_line_number'];
        }          
      }
      
      $client_project_title_key = $entry['client_project_title_key'];

      if (!empty($client_project_title_key)) {
        $count_sittings[$client_project_title_key]++;        
        if (empty($first_line_number[$client_project_title_key])) {
          $first_line_number[$client_project_title_key] = $entry['category_line_number'];
        }        
      }

      $title_key = $entry['title_key'];

      if (!empty($client_project_key) && !empty($title_key)) {
        $count_titles[$client_project_key][$title_key] = $title_key;
      }

    }
    
    foreach($entries as &$entry) {
      $client_project_key = $entry['client_project_key'];
      $client_project_title_key = $entry['client_project_key'];
      $title_key = $entry['title_key'];
      $entry['client_project_sittings'] = @$count_sittings[$client_project_key] ?: null;
      $entry['client_project_first_line_number'] = @$first_line_number[$client_project_key] ?: null;
      $entry['client_project_titles'] = @count($count_titles[$client_project_key]) ?: null;
      $entry['client_project_title_sittings'] = @$count_sittings[$client_project_title_key] ?: null;
      $entry['client_project_first_line_number'] = @$first_line_number[$client_project_title_key] ?: null;
    }

    return $entries;
  }    
  public static function add_client_project_timetracking_data($entries) {
    
    $count_tracked_hours = [];
    
    foreach($entries as &$entry) {
      
      $client_project_key = $entry['client_project_key'];
      if (empty($client_project_key)) continue;
    
      $entry_tracked_hours = $entry['timetracking_tracked_hours'];
      if (!is_numeric($entry_tracked_hours)) continue;
      
      $client_project_total_inited = isset($count_tracked_hours[$client_project_key]);
      if (!$client_project_total_inited) $count_tracked_hours[$client_project_key] = 0;
          
      $count_tracked_hours[$client_project_key] += $entry_tracked_hours;
      
    }
    
    foreach($entries as &$entry) {
      
      $client_project_key = $entry['client_project_key'];
      if (empty($client_project_key)) continue;

      $client_project_total_inited = isset($count_tracked_hours[$client_project_key]);
      
      if ($client_project_total_inited) {
        $entry['tracked_hours'] = $count_tracked_hours[$client_project_key];
      } 

    }
    
    return $entries;
    
  }
  public static function add_timetracking_data($entries) {
    
    foreach($entries as &$entry) {
      
        $brackets_to_check = [];        
        if (is_array($entry['title_brackets_all'])) $brackets_to_check = array_merge($brackets_to_check,$entry['title_brackets_all']);
        if (is_array($entry['note_brackets_all'])) $brackets_to_check = array_merge($brackets_to_check,$entry['note_brackets_all']);
        
        $timetracking_brackets_all = [];
        $timetracking_brackets_unknown = [];
        $timetracking_timepoints_all = [];
        $timetracking_starttime = null;
        $timetracking_endtime = null;
        $timetracking_offset = 0;

        foreach($brackets_to_check as $bracket_to_check) {
          $is_time_bracket = WorklogParsing::brackets_get_time([ $bracket_to_check ]);
          $timepoint = WorklogParsing::time_get_timepoint($bracket_to_check);
          if ($is_time_bracket) {
            $timetracking_brackets_all[] = $bracket_to_check;
            $time_offset =  WorklogParsing::time_get_offset($bracket_to_check);
            if ($time_offset) { 
              $timetracking_offset += $time_offset; 
            } else { 
              $timetracking_timepoints_all[] = $timepoint;
            }
          } else {
            $timetracking_brackets_unknown[] = $bracket_to_check;
          }
        }
        
        $start_and_end_times = WorklogParsing::timepoints_get_starttime_and_endtime($timetracking_timepoints_all);
        $timetracking_starttime = $start_and_end_times['starttime'];
        $timetracking_endtime = $start_and_end_times['endtime'];
        $timetracking_duration = $timetracking_endtime - $timetracking_starttime;
        $timetracking_tracked = $timetracking_duration + $timetracking_offset;
        $timetracking_first_timepoint = reset($timetracking_timepoints_all);
        $timetracking_last_timepoint = end($timetracking_timepoints_all);
        
        if ($timetracking_starttime != $timetracking_first_timepoint) {
          $entry['check_warnings'][] = 'Title timepoint is not the earliest';
        }
        if ($timetracking_endtime != $timetracking_last_timepoint && $timetracking_endtime != $timetracking_last_timepoint+(24*60*60)) {
          $entry['check_warnings'][] = 'Final timepoint is not the latest: '.$timetracking_endtime.' vs '.$timetracking_last_timepoint;
        }
        if ($timetracking_tracked < 0) {
          $entry['check_warnings'][] = 'Time tracked is negative: '.$timetracking_tracked;
        }
        if (count($timetracking_brackets_all)>1 && $timetracking_tracked == 0) {
          $entry['check_warnings'][] = 'Time tracked adds up to zero: '.implode(' ',$timetracking_brackets_all);
        }

        $entry['timetracking_brackets_all'] = $timetracking_brackets_all;
        $entry['timetracking_brackets_unknown'] = $timetracking_brackets_unknown;
        $entry['timetracking_timepoints_all'] = $timetracking_timepoints_all;
        $entry['timetracking_starttime'] = $timetracking_starttime;
        $entry['timetracking_endtime'] = $timetracking_endtime;
        $entry['timetracking_offset'] = $timetracking_offset;
        $entry['timetracking_tracked_time'] = $timetracking_tracked;
        $entry['timetracking_tracked_time_no_offset'] = $timetracking_duration;
        $entry['timetracking_tracked_hours'] = Format::format_seconds_to_hours($timetracking_tracked);
        
        
        // print_r($timetracking_brackets_all);
        
    }
    
    
    return $entries;
  }
  public static function add_effortcost_data($entries) {

    foreach($entries as &$entry) {
      
      $entry['effortcost_tracked_hours'] = @$entry['timetracking_tracked_hours'] ?: 0.0;
      $entry['effortcost_hourly_rate'] = @$entry['client_rate'] ?: 0.0;
      $entry['effortcost_total_cost'] = $entry['effortcost_tracked_hours'] * $entry['effortcost_hourly_rate'];
      $entry['effortcost_total_cost'] = Format::format_cost($entry['effortcost_total_cost']);
      
    }
    
    return $entries;
    
  }
  public static function add_billingcost_data($entries) {

    foreach($entries as &$entry) {
      
      $entry['billingcost_hourly_rate'] = @$entry['client_rate'] ?: 0.0;
      $entry['billingcost_multiplier'] = @$entry['title_brackets_multiplier'] ?: 1.0;
      $entry['billingcost_tracked_hours'] = @$entry['effortcost_tracked_hours'] * $entry['billingcost_multiplier'] ?: 0.0;
      $entry['billingcost_total_cost'] = $entry['billingcost_tracked_hours'] * $entry['billingcost_hourly_rate'];
      $entry['billingcost_total_cost'] = Format::format_cost($entry['billingcost_total_cost']);
      
    }
    
    return $entries;
    
  }
  public static function add_client_effortcost_data($entries) {
    
    $count_total_cost = [];
    $count_tracked_hours = [];
    $count_combined_multiplier = [];

    foreach($entries as &$entry) {
      
      //
      // Client
      //
            
      $client_key = @$entry['client_key'] ?: null;

      // client project effort total cost
      $effortcost_total_cost = $entry['effortcost_total_cost'];
      if (!empty($client_key) && is_numeric($effortcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_key] = 0;
            
        $count_total_cost[$client_key] += $effortcost_total_cost;
        
      }
        
      // client project effort tracked hours
      $effortcost_tracked_hours = $entry['effortcost_tracked_hours'];
      if (!empty($client_key) && is_numeric($effortcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_key] = 0;

        $count_tracked_hours[$client_key] += $effortcost_tracked_hours;
        
      }
      
      
      //
      // Client Project
      //
            
      $client_project_key = @$entry['client_project_key'] ?: null;

      // client project effort total cost
      $effortcost_total_cost = $entry['effortcost_total_cost'];
      if (!empty($client_project_key) && is_numeric($effortcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_project_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_project_key] = 0;
            
        $count_total_cost[$client_project_key] += $effortcost_total_cost;
        
      }
        
      // client project effort tracked hours
      $effortcost_tracked_hours = $entry['effortcost_tracked_hours'];
      if (!empty($client_project_key) && is_numeric($effortcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_project_key] = 0;

        $count_tracked_hours[$client_project_key] += $effortcost_tracked_hours;
        
      }
      
      //
      // Client Project Title
      //
      
      $client_project_title_key = $entry['client_project_title_key'] ?: null;

      // client project title effort total cost
      $effortcost_total_cost = $entry['effortcost_total_cost'];
      if (!empty($client_project_title_key) && is_numeric($effortcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_project_title_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_project_title_key] = 0;
            
        $count_total_cost[$client_project_title_key] += $effortcost_total_cost;
        
      }
        
      // client project title effort tracked hours
      $effortcost_tracked_hours = $entry['effortcost_tracked_hours'];
      if (!empty($client_project_title_key) && is_numeric($effortcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_title_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_project_title_key] = 0;

        $count_tracked_hours[$client_project_title_key] += $effortcost_tracked_hours;
        
      }
      
    }
    
    foreach($entries as &$entry) {
      
      //
      // Client
      //

      $client_key = @$entry['client_key'] ?: null;

      // client project effort total cost
      $count_total_cost_inited = isset($count_total_cost[$client_key]);      
      if ($count_total_cost_inited) {
        $entry['client_effort_total_cost'] = $count_total_cost[$client_key];
      } 

      // client project effort total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_effort_tracked_hours'] = $count_tracked_hours[$client_key];
      } 
      
      //
      // Client Project
      //

      $client_project_key = @$entry['client_project_key'] ?: null;

      // client project effort total cost
      $count_total_cost_inited = isset($count_total_cost[$client_project_key]);      
      if ($count_total_cost_inited) {
        $entry['client_project_effort_total_cost'] = $count_total_cost[$client_project_key];
      } 

      // client project effort total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_project_effort_tracked_hours'] = $count_tracked_hours[$client_project_key];
      } 

      //
      // Client Project Title
      //

      $client_project_title_key = @$entry['client_project_title_key'] ?: null;

      // client project effort total cost
      $count_total_cost_inited = isset($count_total_cost[$client_project_title_key]);      
      if ($count_total_cost_inited) {
        $entry['client_project_title_effort_total_cost'] = $count_total_cost[$client_project_title_key];
      } 

      // client project effort total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_title_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_project_title_effort_tracked_hours'] = $count_tracked_hours[$client_project_title_key];
      } 

    }
    
    return $entries;
    
  }  
  public static function add_client_billingcost_data($entries) {
    
    $count_total_cost = [];
    $count_tracked_hours = [];

    foreach($entries as &$entry) {
      
      //
      // Client
      //
            
      $client_key = @$entry['client_key'] ?: null;

      // client project effort total cost
      $billingcost_total_cost = $entry['billingcost_total_cost'];
      if (!empty($client_key) && is_numeric($billingcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_key] = 0;
            
        $count_total_cost[$client_key] += $billingcost_total_cost;
        
      }
        
      // client project effort tracked hours
      $billingcost_tracked_hours = $entry['billingcost_tracked_hours'];
      if (!empty($client_key) && is_numeric($billingcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_key] = 0;

        $count_tracked_hours[$client_key] += $billingcost_tracked_hours;
        
      }
      
      
      //
      // Client Project
      //
            
      $client_project_key = @$entry['client_project_key'] ?: null;

      // client project effort total cost
      $billingcost_total_cost = $entry['billingcost_total_cost'];
      if (!empty($client_project_key) && is_numeric($billingcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_project_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_project_key] = 0;
            
        $count_total_cost[$client_project_key] += $billingcost_total_cost;
        
      }
        
      // client project effort tracked hours
      $billingcost_tracked_hours = $entry['billingcost_tracked_hours'];
      if (!empty($client_project_key) && is_numeric($billingcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_project_key] = 0;

        $count_tracked_hours[$client_project_key] += $billingcost_tracked_hours;
        
      }
      
      //
      // Client Project Title
      //
      
      $client_project_title_key = $entry['client_project_title_key'] ?: null;

      // client project title effort total cost
      $billingcost_total_cost = $entry['billingcost_total_cost'];
      if (!empty($client_project_title_key) && is_numeric($billingcost_total_cost)) {
        
        $count_total_cost_inited = isset($count_total_cost[$client_project_title_key]);
        if (!$count_total_cost_inited) $count_total_cost[$client_project_title_key] = 0;
            
        $count_total_cost[$client_project_title_key] += $billingcost_total_cost;
        
      }
        
      // client project title effort tracked hours
      $billingcost_tracked_hours = $entry['billingcost_tracked_hours'];
      if (!empty($client_project_title_key) && is_numeric($billingcost_tracked_hours)) {

        $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_title_key]);
        if (!$count_tracked_hours_inited) $count_tracked_hours[$client_project_title_key] = 0;

        $count_tracked_hours[$client_project_title_key] += $billingcost_tracked_hours;
        
      }
      
    }
    
    foreach($entries as &$entry) {
      
      //
      // Client
      //

      $client_key = @$entry['client_key'] ?: null;

      // client project billing total cost
      $count_total_cost_inited = isset($count_total_cost[$client_key]);      
      if ($count_total_cost_inited) {
        $entry['client_billing_total_cost'] = $count_total_cost[$client_key];
      } 

      // client project billing total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_billing_tracked_hours'] = $count_tracked_hours[$client_key];
        $entry['client_billing_multiplier'] = $entry['client_billing_tracked_hours'] / $entry['client_effort_tracked_hours'];
      } 
      
      //
      // Client Project
      //

      $client_project_key = @$entry['client_project_key'] ?: null;

      // client project billing total cost
      $count_total_cost_inited = isset($count_total_cost[$client_project_key]);      
      if ($count_total_cost_inited) {
        $entry['client_project_billing_total_cost'] = $count_total_cost[$client_project_key];
      } 

      // client project billing total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_project_billing_tracked_hours'] = $count_tracked_hours[$client_project_key];
        $entry['client_project_billing_multiplier'] = $entry['client_project_billing_tracked_hours'] / $entry['client_project_effort_tracked_hours'];
      } 

      //
      // Client Project Title
      //

      $client_project_title_key = @$entry['client_project_title_key'] ?: null;

      // client project billing total cost
      $count_total_cost_inited = isset($count_total_cost[$client_project_title_key]);      
      if ($count_total_cost_inited) {
        $entry['client_project_title_billing_total_cost'] = $count_total_cost[$client_project_title_key];
      } 

      // client project billing total cost
      $count_tracked_hours_inited = isset($count_tracked_hours[$client_project_title_key]);      
      if ($count_tracked_hours_inited) {
        $entry['client_project_title_billing_tracked_hours'] = $count_tracked_hours[$client_project_title_key];
        $entry['client_project_title_billing_multiplier'] = $entry['client_project_title_billing_tracked_hours'] / $entry['client_project_effort_tracked_hours'];
      } 

    }
    
    return $entries;
    
  }    
  
}
