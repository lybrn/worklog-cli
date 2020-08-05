<?php 
namespace Lybrnth\Mdon;
class Output {

  public static function border_box($content) {

    // render output 
    $content = trim( Output::render($content) );
    
    // break content into lines
    $lines = explode("\n",$content);

    // find length of longest line
    $longest = 0;
    foreach($lines as $line) {
      if (mb_strlen($line) > $longest) 
        $longest = mb_strlen($line);
    }
    
    // print top dash line and top padding line
    $output = "+".str_repeat('-',$longest+4)."+\n";
    $output .= "|  ".str_repeat(' ',$longest)."  |\n";
    
    // print content lines
    foreach($lines as $line) {
      $output .= "|  ".$line.str_repeat(' ',$longest-mb_strlen($line))."  |\n";
    }
    
    // print bottom dash line and top padding line
    $output .= "|  ".str_repeat(' ',$longest)."  |\n";
    $output .= "+".str_repeat('-',$longest+4)."+\n";
    
    // return output string 
    return $output;
  }
  public static function render($content) {
    
    // if content is an array, implode keys and values into a content string
    if (is_array($content)) {
      foreach($content as $k=>$v) {
        if (is_array($v))
          $v = implode(', ',$v);
        if (!is_numeric($k))
          $content[$k] = "$k: $v";
      }
      $content = implode("\n",$content);
    }
    
    // remove white space from top and bottom of content
    $content = trim($content);
    
    return $content."\n";    
  }  
  
}
