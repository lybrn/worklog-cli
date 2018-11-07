<?php
namespace WorklogCLI;
class MDON {

  public function parse_files($filepaths) {

    // array that stores file contents
    $contents = [];
    
    // loop through provided file paths
    foreach($filepaths as $filepath) {
      
      // confirm file exists
      if (!is_file($filepath)) throw new \Exception("No such file: $filepath");

      // get file contents
      $contents[] = file_get_contents($filepath);
      
    }

    // join file contents and parse
    $contents = implode("\n\n",$contents);
    $parsed = MDON::parse($contents);

    // return parsed contents
    return $parsed;

  }
  public function parse_file($filepath) {

    // confirm file exists
    if (!is_file($filepath)) throw new \Exception("No such file: $filepath");

    // get file contents and parse
    $contents = file_get_contents($filepath);
    $parsed = MDON::parse($contents);

    // return parsed contents
    return $parsed;

  }
  public function parse($contents) {

    // break contents into lines
    $lines = explode("\n",$contents);
    // array that will hold selected rows
    $selected = array();
    // array that will hold all rows
    $rows = array();
    // current depth
    $depth = 0;
    // current_style
    $current_style = null;
    $current_depth = 0;

    // for each line:
    foreach($lines as $i=>$line) {
      if ($depth = MDON::get_depth($lines,$i,$line,$current_style,$current_depth)) {
        $style = MDON::get_style($lines,$i,$line);
        $matched = MDON::get_matched($lines,$i,$line);
        $current_style = $style;
        $current_depth = $depth;
        $rows[] = array(
          'depth'=>$depth,
          'pos'=>count($rows),
          'style'=>$style,
          'line'=>$matched,
          'linenum'=>$i,
          'text'=>trim($line,'+#'),
        );
      }
    }

    // use row data to build a tree
    $tree = array();
    // array that holds stack of parent rows
    $stack = array();

    // for each row
    foreach($rows as $r => $row) {
      // if the stack is empty, add this row to the stack
      if (empty($stack)) {
        // add row to stack
        $stack[] = &$rows[$r];
        // add row to tree
        $tree[] = &$rows[$r];
        //print ".1/0: ".$row['text']."\n";
      }

      // if this row as at the same depth as the last item on the stack,
      // then pop off last item and replace with this one
      else if ($row['depth'] == count($stack)) {
        // pop last item off stack
        array_pop($stack);
        // get position of top stack item
        $top = count($stack)-1;
        // add row to below top stack item
        $stack[$top]['rows'][] = &$rows[$r];
        // add row to stack
        $stack[] = &$rows[$r];
        //print "=".$row['depth']."/$top: ".$row['text']."\n";
      }

      // if this row is deeper than the last item of the stack,
      // then add this item to the top of the stack
      else if ($row['depth'] > count($stack)) {
        // leave all current items on the stack
        // get position of top stack item
        $top = count($stack)-1;
        // add row to below top stack item
        $stack[$top]['rows'][] = &$rows[$r];
        // add row to stack
        $stack[] = &$rows[$r];
        //print "+".$row['depth']."/$top: ".$row['text']."\n";
      }

      // if this item is shallower than the last item on the stack,
      // then pop stack items to get to that depth-1, and then add this item
      else if ($row['depth'] < count($stack)) {
        // pop correct number of additional items off the stack
        while($row['depth'] <= count($stack)) { array_pop($stack); }
        // add row to below top stack item
        if (!empty($stack)) {
          // get position of top stack item
          $top = count($stack)-1;
          // add row to top of stack
          $stack[$top]['rows'][] = &$rows[$r];
        } else {
          // add row to tree
          $tree[] =  &$rows[$r];
        }

        // add row to stack
        $stack[] = &$rows[$r];
        //print "-".$row['depth']."/$top: ".$row['text']."\n";
      }
    }

    // return tree
    return $tree;

  }
  public function get_depth($lines,$i,$line,$current_style,$current_depth) {

    // get the current line
    $line = $line;
    // get the next line
    $nextline = $lines[$i+1];

    // h1 -- next line is "==="+
    if (preg_match('/^===+\s*$/i',$nextline)) {
      return 1;
    }

    // h2 -- next line is "---"+
    if (preg_match('/^---+\s*$/i',$nextline)) {
      return 2;
    }

    // h3 -- this line starts with "###"
    if (preg_match('/^###[^#]/i',$line)) {
      return 3;
    }

    // + item --- this line starts with "+"
    if (preg_match('/^\+[^\+\-]/i',$line)) {
      return 4;
    }

    // - item --- this line starts with "-"
    if (preg_match('/^\-[^\+\-]/i',$line)) {
      return 4;
    }

    // * or . item --- this line starts with "*" or "."
    if (preg_match('/^[\*\.][^\+\-\*]/i',$line)) {
      if (in_array($current_style,array('*','.'))) {
        return $current_depth;
      } else {
        return $current_depth + 1;
      }
    }

    // everything else -- no depth or meaning
    return FALSE;

  }
  public function get_style($lines,$i,$line) {

    // get the current line
    $line = $line;
    // get the next line
    $nextline = $lines[$i+1];

    // h1 -- next line is "==="+
    if (preg_match('/^===+\s*$/i',$nextline)) {
      return "===";
    }

    // h2 -- next line is "---"+
    if (preg_match('/^---+\s*$/i',$nextline)) {
      return "---";
    }

    // h3 -- this line starts with "###"
    if (preg_match('/^###[^#]/i',$line)) {
      return "###";
    }

    // + item --- this line starts with "+"
    if (preg_match('/^\+[^\+\-\*]/i',$line)) {
      return "+";
    }

    // - item --- this line starts with "-"
    if (preg_match('/^\-[^\+\-\*]/i',$line)) {
      return "-";
    }

    // * item --- this line starts with "*"
    if (preg_match('/^\*[^\+\-\*]/i',$line)) {
      return "*";
    }

    // . item --- this line starts with "."
    if (preg_match('/^\.[^\+\-\*]/i',$line)) {
      return ".";
    }

    // everything else -- no style or meaning
    return FALSE;

  }
  public function get_matched($lines,$i,$line) {

    // get the current line
    $line = $line;
    // get the next line
    $nextline = $lines[$i+1];

    // h1 -- next line is "==="+
    if (preg_match('/^===+\s*$/i',$nextline)) {
      return trim($line)."\n".trim($nextline);
    }

    // h2 -- next line is "---"+
    if (preg_match('/^---+\s*$/i',$nextline)) {
      return trim($line)."\n".trim($nextline);
    }

    // h3 -- this line starts with "###"
    if (preg_match('/^###[^#]/i',$line)) {
      return trim($line);
    }

    // + item --- this line starts with "+"
    if (preg_match('/^\+[^\+\-\*]/i',$line)) {
      return trim($line);
    }
    
    // - item --- this line starts with "-"
    if (preg_match('/^\-[^\+\-\*]/i',$line)) {
      return trim($line);
    }

    // * item --- this line starts with "*"
    if (preg_match('/^\*[^\+\-\*]/i',$line)) {
      return trim($line);
    }

    // . item --- this line starts with "."
    if (preg_match('/^\.[^\+\-\*\.]/i',$line)) {
      return trim($line);
    }

    // everything else -- no style or meaning
    return FALSE;

  }
}
