<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
class WorklogEntries {
  
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
      //ksort($bare_entry);
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
    }
    return $entries;
  }

  
  public static function entries($filepaths,$options=[]) {

    $parsed = Mdon::decode_files($filepaths);
    $bare_data = [];
    
    // day rows
    foreach($parsed as $day_row) {

      $bare_entry = [];
      
      $day_raw = trim($day_row['text']);
      $day_text = WorklogParsing::line_clean_brackets($day_row['text']);
      $day_brackets = WorklogParsing::line_get_brackets($day_row['text']); 
      $day_line_number = $day_row['linenum'];
      
      $bare_entry['day_text_raw'] = $day_raw;
      $bare_entry['day_text_nobrackets'] = $day_text;
      $bare_entry['day_brackets_all'] = $day_brackets;
      $bare_entry['day_line_number'] = $day_line_number;
      
      // category rows
      if (is_array($day_row['rows'])) {
        foreach($day_row['rows'] as $category_row) {
          
          $category_raw = trim($category_row['text']);
          $category_text = WorklogParsing::line_clean_brackets($category_row['text']);
          $category_brackets = WorklogParsing::line_get_brackets($category_row['text']);
          $category_line_number = $category_row['linenum'];
          
          $bare_entry['category_text_raw'] = $category_raw;
          $bare_entry['category_text_nobrackets'] = $category_text;
          $bare_entry['category_brackets_all'] = $category_brackets;
          $bare_entry['category_line_number'] = $category_line_number;
          
          // title rows
          if (is_array($category_row['rows'])) {
            foreach($category_row['rows'] as $title_row) {  
            
              $title_raw = trim($title_row['text']);
              $title_text = WorklogParsing::line_clean_brackets($title_row['text']);
              $title_brackets = WorklogParsing::line_get_brackets($title_row['text']);
              $title_line_number = $title_row['linenum'];
            
              $bare_entry['title_text_raw'] = $title_raw;
              $bare_entry['title_text_nobrackets'] = $title_text;
              $bare_entry['title_brackets_all'] = $title_brackets;
              $bare_entry['title_line_number'] = $title_line_number;

              $bare_entry_notes = [];
              $bare_entry_notes_brackets_all = [];
              
              // note rows
              if (is_array($title_row['rows'])) {
                foreach($title_row['rows'] as $note_row) {  

                  // only look at completed notes (lines that start with '+'')
                  if ($note_row['style'] != '+') {
                    continue; 
                  }
                  
                  $bare_entry_note = [];
                  
                  $note_raw = trim($note_row['text']);
                  $note_text = WorklogParsing::line_clean_brackets($note_row['text']);
                  $note_brackets = WorklogParsing::line_get_brackets($note_row['text']);
                  $note_line_number = $note_row['linenum'];

                  $bare_entry_note['note_text_raw'] = $note_raw;
                  $bare_entry_note['note_text_nobrackets'] = $note_text;
                  $bare_entry_note['note_brackets_all'] = $note_brackets;
                  $bare_entry_note['note_line_number'] = $note_line_number;

                  $bare_entry_notes[] = $bare_entry_note;
                  
                  if (is_array($note_brackets))
                    $bare_entry_notes_brackets_all = array_merge($bare_entry_notes_brackets_all,$note_brackets);
                }
              }
              
              $bare_entry['note_rows'] = $bare_entry_notes;
              $bare_entry['note_brackets_all'] = $bare_entry_notes_brackets_all;
              
            }
          }
          
              
        }
      }
        
      $bare_data[] = $bare_entry;
    }
    
    
    return $bare_data;
  }
   
}
