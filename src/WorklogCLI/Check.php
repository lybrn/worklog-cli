<?php 
namespace WorklogCLI;
class Check {
  
  public static function check_days($parsed,$args=array()) {
    
    // errors
    $errors = [];
    
    // vars
    $last_day_time = null;
    $last_day_year = null;
    $todays_day_time = strtotime(date('Y-m-d'));
    
    $dates = [];
    foreach($parsed as $item) {
      $day_line = $item['day_line_number'];
      $day_date = $item['date'];
      $day_text = $item['day_text'];
      $day_time = strtotime($day_text);
      $day_year = date('Y',$day_time);
      $dates[ $day_line ] = $item;
    }
      
    // info
    foreach($dates as $item) {
      
      $day_line = $item['day_line_number'];
      $day_date = $item['date'];
      $day_text = $item['day_text'];
      $day_text_no_weekday = implode(' ',array_slice(explode(' ',$day_text),1));
      $day_text_no_weekday_time = strtotime($day_text_no_weekday);
      $day_correct_weekday = date('l',$day_text_no_weekday_time);
      $day_time = strtotime($day_text);
      $day_year = date('Y',$day_time);
      $day_correct_format = date('l F jS Y',$day_time);
      
      // No timestamp parseable
      if ($day_time==0) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "No timestamp parseable",
        'Line' => $day_line
      ];

      // Duplicate date
      if (!is_null($last_day_time)) if ($day_time == $last_day_time) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "Duplicate date, same as last",
        'Line' => $day_line
      ];
      
      // Not in order
      if (!is_null($last_day_time)) if ($day_time < $last_day_time) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "Not in order",
        'Line' => $day_line
      ];
      
      // Date is in the future
      if ($day_time > $todays_day_time) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "Date is in the future",
        'Line' => $day_line
      ];

      // In a different year
      if (!is_null($last_day_year)) if ($day_year != $last_day_year) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "In a different year than $last_day_year",
        'Line' => $day_line
      ];
      
      if ($day_text_no_weekday_time != $day_time) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "Incorrect weekday: $day_correct_weekday",
        'Line' => $day_line
      ];

      // Incorrect format
      if ($day_text_no_weekday_time == $day_time) if ($day_text != $day_correct_format) $errors[] = [
        'Day' => $day_text,
        'Date' => $day_date,
        'Error' => "Incorrect format: $day_correct_format",
        'Line' => $day_line
      ];

      
      if (is_null($last_day_year))
        $last_day_year = $day_year;

      $last_day_time = $day_time;

    }      
    return array_values($errors);
    
  }
    
}
