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
    $count = [];
    $first_line_number = [];
    foreach($entries as $entry) {
      $title_category_key = [];
      $title_category_key[] = $entry['title_text_nobrackets'];
      $title_category_key[] = $entry['category_text_nobrackets'];
      $title_category_key = implode('-',$title_category_key);
      $count[$title_category_key]++;
      if (empty($first_line_number[$title_category_key]))
        $first_line_number[$title_category_key] = $entry['title_line_number'];
    }
    foreach($entries as &$entry) {
      $title_category_key = [];
      $title_category_key[] = $entry['title_text_nobrackets'];
      $title_category_key[] = $entry['category_text_nobrackets'];
      $title_category_key = implode('-',$title_category_key);
      $entry['title_category_sittings'] = @$count[$title_category_key] ?: null;
      $entry['title_category_first_line_number'] = @$first_line_number[$title_category_key] ?: null;
      $entry['title_category_brackets_all'] = array_merge(
          $entry['category_brackets_all'] ?: [],
          $entry['title_brackets_all'] ?: []);
      $entry['title_category_tags_all'] = array_merge(
        $entry['category_tags_all'] ?: [],
        $entry['title_tags_all'] ?: []
      );
    }
    return $entries;
  }
  public static function add_client_data($entries) {
  
    $clients = [];
    
    foreach($entries as &$entry) {
      $category = $entry['category_text_nobrackets'];
      $client_key = 'Client-'.$category;
      $client_data = current( CLI::get_note_data_by_keys( $client_key ) );
      if (is_array($client_data)) foreach($client_data as $key=>$value) {
        $normalized_key = WorklogNormalize::normalize_key($key,'- ','_');
        $entry[$normalized_key] = $value;
      }
    }

    return $entries;
  }   
  public static function add_client_project_data($entries) {

    $count = [];
    $first_line_number = [];
    $project_lists_by_client = [];
    $project_data_by_client_project = [];

    foreach($entries as &$entry) {
      $entry['client_project_name'] = [];
      // $entry['client_project_rejected'] = [];
      $title_category_tags_all = $entry['title_category_tags_all'];
      $client_short_name = $entry['client_short_name'];
      $client_projects_key = 'Projects-'.$client_short_name;
      
      if (!array_key_exists($client_projects_key,$project_lists_by_client)) {
        $project_lists_by_client[ $client_projects_key ] = current( CLI::get_note_data_by_keys( $client_projects_key ) );
      }
      $client_projects_list = $project_lists_by_client[ $client_projects_key ];
            
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
            
            $entry['client_project_name'] = @$client_project_data['name'] ?: $project;
            $entry['client_project_number'] = @$client_project_data['number'] ?: null;
            $entry['client_project_client'] = @$client_project_data['client'] ?: null;
            $entry['client_project_distribute'] = @$client_project_data['distribute'] ?: null;

            
            $client_project_data = $project_data_by_client_project[ $client_project_data_key ];
          } else {
            // $entry['client_project_rejected'][$tag_normalized] = $tag;
          }
        }
      }
      
    }
    
    
    
    foreach($entries as $entry) {
      $client_project_key = [];
      $client_project_key[] = @$entry['client_full_name'] ?: '-';
      $client_project_key[] = @$entry['client_project_name'] ?: '-';
      $client_project_key = trim( WorklogNormalize::normalize_key( implode('-',$client_project_key),'-' ),'-');
      if (empty($client_project_key))
      if (empty($client_project_key)) continue;
      $count[$client_project_key]++;
      if (empty($first_line_number[$client_project_key]))
        $first_line_number[$client_project_key] = $entry['category_line_number'];
    }

    foreach($entries as &$entry) {
      $client_project_key = [];
      $client_project_key[] = @$entry['client_full_name'] ?: '-';
      $client_project_key[] = @$entry['client_project_name'] ?: '-';
      $client_project_key = trim( WorklogNormalize::normalize_key( implode('-',$client_project_key),'-' ),'-');
      $entry['client_project_key'] = $client_project_key;
      $entry['client_project_sittings'] = @$count[$client_project_key] ?: null;
      $entry['client_project_first_line_number'] = @$first_line_number[$client_project_key] ?: null;
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

  
}
