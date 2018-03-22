<?php
namespace WorklogCLI;
class MDON2 {

  public function parse_file($filepath) {

    // confirm file exists
    if (!is_file($filepath)) throw new \Exception("No such file: $filepath");

    // get file contents and parse
    $contents = file_get_contents($filepath);
    $parsed = MDON2::parse($contents);

    // return parsed contents
    return $parsed;

  }
  public function parse($contents) {

    //
    // [ depth, pos, style, text, [ items ]]
    //

    // break contents into lines
    $lines = explode("\n",$contents);
    // array that will hold selected rows
    $selected = array();
    // array that will hold all rows
    $rows = array();
    // for each line:
    foreach($lines as $i=>$line) {
      if ($depth = MDON2::get_depth($lines,$i,$line)) {
        $style = MDON2::get_style($lines,$i,$line);
        $matched = MDON2::get_matched($lines,$i,$line);
        $rows[] = array(
          'depth'=>$depth,
          'pos'=>count($rows),
          'style'=>$style,
          'text'=>trim(trim(trim($line),'+#')),
        );
      }
    }
    // use row data to build a tree
    $tree = array();
    // array that holds stack of parent rows
    $stack = array();
    // for each row
    foreach($rows as $r => $row) {
      // make to-add object
      if (empty($rows[$r]['toadd'])) {
        $rows[$r]['toadd'] = array($rows[$r]['style'],$rows[$r]['text']);
      }
      // if the stack is empty, add this row to the stack
      if (empty($stack)) {
        // add row to stack
        $stack[] = &$rows[$r]['toadd'];
        // add row to tree
        $tree[] = &$rows[$r]['toadd'];
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
        $stack[$top][2][] = &$rows[$r]['toadd'];
        // add row to stack
        $stack[] = &$rows[$r]['toadd'];
        //print "=".$row['depth']."/$top: ".$row['text']."\n";
      }
      // if this row is deeper than the last item of the stack,
      // then add this item to the top of the stack
      else if ($row['depth'] > count($stack)) {
        // leave all current items on the stack
        // get position of top stack item
        $top = count($stack)-1;
        // add row to below top stack item
        $stack[$top][2][] = &$rows[$r]['toadd'];
        // add row to stack

        $stack[] = &$rows[$r]['toadd'];
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
          $stack[$top][2][] = &$rows[$r]['toadd'];
        } else {
          // add row to tree
          $tree[] =  &$rows[$r]['toadd'];
        }
        // add row to stack
        $stack[] = &$rows[$r]['toadd'];
        //print "-".$row['depth']."/$top: ".$row['text']."\n";
      }
    }
    return $tree;
  }
  public function get_depth($lines,$i,$line) {
    // get the current line
    $line = trim($line);
    // get the next line
    $nextline = trim($lines[$i+1]);
    // h1 -- next line is "==="+
    if (preg_match('/^===+$/i',$nextline)) {
      return 1;
    }
    // h2 -- next line is "---"+
    if (preg_match('/^---+$/i',$nextline)) {
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
    // everything else -- no depth or meaning
    return FALSE;
  }
  public function get_style($lines,$i,$line) {
    // get the current line
    $line = trim($line);
    // get the next line
    $nextline = trim($lines[$i+1]);
    // h1 -- next line is "==="+
    if (preg_match('/^===+$/i',$nextline)) {
      return "===";
    }
    // h2 -- next line is "---"+
    if (preg_match('/^---+$/i',$nextline)) {
      return "---";
    }
    // h3 -- this line starts with "###"
    if (preg_match('/^###[^#]/i',$line)) {
      return "###";
    }
    // + item --- this line starts with "+"
    if (preg_match('/^\+[^\+\-]/i',$line)) {
      return "+";
    }
    // everything else -- no style or meaning
    return FALSE;
  }
  public function get_matched($lines,$i,$line) {
    // get the current line
    $line = trim($line);
    // get the next line
    $nextline = trim($lines[$i+1]);
    // h1 -- next line is "==="+
    if (preg_match('/^===+$/i',$nextline)) {
      return trim($line)."\n".trim($nextline);
    }
    // h2 -- next line is "---"+
    if (preg_match('/^---+$/i',$nextline)) {
      return trim($line)."\n".trim($nextline);
    }
    // h3 -- this line starts with "###"
    if (preg_match('/^###[^#]/i',$line)) {
      return trim($line);
    }
    // + item --- this line starts with "+"
    if (preg_match('/^\+[^\+\-]/i',$line)) {
      return trim($line);
    }
    // everything else -- no style or meaning
    return FALSE;
  }
}
