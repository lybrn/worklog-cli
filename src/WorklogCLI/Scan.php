<?php
namespace WorklogCLI;

class Scan {
  
  // scans all directories and returns items based on options
  public static function scan($root,$paths=array(),$options=array()) {

    // require that search paths is array or string
    if (!is_array($paths) && !is_string($paths))
      throw new Exception("Provided paths list is not string or array: $paths");

    // convert search paths to array if it's a string
    if (is_string($paths))
      $paths = array($paths);

    // initialize array that will hold paths to return
    $return_items = array();

    // loop through folders and find tpls
    foreach($paths as $path) {

      $search_path = $root.'/'.$path;
      
      // require that this path exists
      if (!is_dir($search_path))
        throw new Exception("Provided path does not exist: $search_path");

      // initialize array to hold list of child directories
      $search_path_child_dirs = array();

      // open the search path direcory
      $search_path_open = opendir($search_path);

      // loop through files and create theme entries
      while ($itemname = readdir($search_path_open)) {

        // if this item name starts with a dot, continue
        if (substr($itemname,0,1)=='.') continue;

        // path to item
        $itempath = $search_path.'/'.$itemname;
        
        $return = [];
        
        // add file to return items if option set 
        if (!empty($options['return_files']) && is_file($itempath)) 
          $return[] = TRUE;

        // add dir to return items if option set 
        if (!empty($options['return_dirs']) && is_dir($itempath)) 
          $return[] = TRUE;

        // add dir to return items if option set 
        if (!empty($options['return_types'])) {
          $dotbroken = explode('.',$itemname);
          $extension = end($dotbroken);
          $return[] = ( 
            count($dotbroken) > 1 &&
            in_array($extension,$options['return_types'])
          );          
        } 
          
        // add dir to return items if option set 
        if (!empty($options['return_kit_items'])) {
          $kit_basename = basename($path);
          $return[] = (
            !empty($kit_basename) && 
            strpos($itemname,$kit_basename.'.')===0
          );
        }
        
        // if return was flagged
        if (!empty($return) && !in_array(FALSE,$return))
          $return_items[] = trim($path .'/'.$itemname,'/');    

        // if is a directory, add to list of subdirecories
        if (is_dir($itempath)) {
          $search_path_child_dirs[] = $path .'/'.$itemname;          
        }    
                  
        
      }

      // if any child direcories were found and recursive is true, recurse
      if (!empty($search_path_child_dirs) && !empty($options['recursive'])) {

        // find return paths within child directories (recursion)
        $search_paths_found_within_subpaths = self::scan($root,$search_path_child_dirs,$options);

        // add paths found within child directoris to list of return paths
        $return_items = array_merge($return_items, $search_paths_found_within_subpaths);

      } 

      // close open directory
      closedir($search_path_open);

    }

    // return all paths found
    return $return_items;
  }    
      
}
