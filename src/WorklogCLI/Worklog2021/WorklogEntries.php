<?php
namespace WorklogCLI\Worklog2021;
use Lybrnth\Mdon;
class WorklogEntries {
  
  public static function entries($filepaths,$options=[]) {

    $parsed = Mdon::decode_files($filepaths);
    $bare_data = [];
    
    // day rows
    foreach($parsed as $day_row) {

      $bare_entry = [];
      
      $day_raw = trim($day_row['text']);
      $day_text = WorklogParsing::line_clean_brackets($day_row['text']);
      $day_key = WorklogNormalize::normalize_key($day_text,'_- ','-');
      $day_precolon = WorklogParsing::line_get_precolon($day_row['text']) ?: []; 
      $day_brackets = WorklogParsing::line_get_brackets($day_row['text']) ?: []; 
      $day_line_number = $day_row['linenum'];
      
      $bare_entry['day_key'] = $day_key;
      $bare_entry['day_text_raw'] = $day_raw;
      $bare_entry['day_text_nobrackets'] = $day_text;
      $bare_entry['day_precolon'] = $day_precolon;
      $bare_entry['day_brackets_all'] = $day_brackets;
      $bare_entry['day_tags_all'] = array_merge($day_brackets, $day_precolon);
      $bare_entry['day_line_number'] = $day_line_number;
      
      // category rows
      if (is_array($day_row['rows'])) {
        foreach($day_row['rows'] as $category_row) {
          
          $category_raw = trim($category_row['text']);
          $category_text = WorklogParsing::line_clean_brackets($category_row['text']);
          $category_key = WorklogNormalize::normalize_key($category_text,'_- ','-');
          $category_precolon = WorklogParsing::line_get_precolon($category_row['text']) ?: [];
          $category_brackets = WorklogParsing::line_get_brackets($category_row['text']) ?: [];
          $category_line_number = $category_row['linenum'];
          
          $bare_entry['category_key'] = $category_key;
          $bare_entry['category_text_raw'] = $category_raw;
          $bare_entry['category_text_nobrackets'] = $category_text;
          $bare_entry['category_precolon'] = $category_precolon;
          $bare_entry['category_brackets_all'] = $category_brackets;
          $bare_entry['category_tags_all'] = array_merge($category_brackets, $category_precolon);
          $bare_entry['category_line_number'] = $category_line_number;
          
          // title rows
          if (is_array($category_row['rows'])) {
            foreach($category_row['rows'] as $title_row) {  
            
              $title_raw = trim($title_row['text']);
              $title_text = WorklogParsing::line_clean_brackets($title_row['text']);
              $title_key = WorklogNormalize::normalize_key($title_text,'_- ','-');
              $title_precolon = WorklogParsing::line_get_precolon($title_text) ?: [];
              $title_brackets = WorklogParsing::line_get_brackets($title_row['text']) ?: [];
              $title_line_number = $title_row['linenum'];
            
              $bare_entry['title_key'] = $title_key;
              $bare_entry['title_text_raw'] = $title_raw;
              $bare_entry['title_text_nobrackets'] = $title_text;
              $bare_entry['title_precolon'] = @$title_precolon ?: [];              
              $bare_entry['title_brackets_all'] = $title_brackets;
              $bare_entry['title_tags_all'] = array_merge($title_brackets, $title_precolon);
              $bare_entry['title_line_number'] = $title_line_number;

              $title_category_key = implode('-',[ $category_key, $title_key ]);
              $title_category_key = WorklogNormalize::normalize_key($title_category_key,'_- ','-');
              
              $bare_entry['title_category_key'] = $title_category_key;
                
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
                  $note_key = WorklogNormalize::normalize_key($note_text,'_- ','-');
                  $note_brackets = WorklogParsing::line_get_brackets($note_row['text']);
                  $note_line_number = $note_row['linenum'];

                  $bare_entry_note['note_key'] = $note_raw;
                  $bare_entry_note['note_text_raw'] = $note_key;
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
              
              $bare_data[] = $bare_entry;
              
            }
          }
              
        }
      }
        
    }
    
    
    return $bare_data;
  }
   
}
