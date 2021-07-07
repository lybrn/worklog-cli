<?php
namespace WorklogCLI\Worklog2021;
use WorklogCLI\CLI;
use WorklogCLI\Exception;
class WorklogLookup {

  public static function get_client_from_list($list) {
        
    if (empty($list) || !is_array($list)) return null;
    
    $possible_clients = [];
    
    foreach($list as $item) {
      
      if (empty($item) || !is_string($item)) continue;
      
      $client_data = WorklogLookup::get_client_details_array($item);

      if (empty($client_data) || !is_array($client_data)) continue; 
      
      if (empty($client_data['client_full_name'])) continue;

      $possible_clients[ $client_data['client_full_name'] ] = $item;
    
    }
    
    if (count($possible_clients) > 1) {
      throw new Exception("More than one possible client found: ".implode(",",$possible_clients));
    }
    
    $client = reset($possible_clients);
    
    return $client;
    
  }
  public static function get_client_details_array($client_name) {
    
    if (empty($client_name) || !is_string($client_name)) return null;
    
    $client_key = 'Client-'.$client_name;
    
    $client_data = current( WorklogDB::db( $client_key ) );

    if (empty($client_data) || !is_array($client_data)) return null;

    $client_data_normalized = [];
    
    foreach($client_data as $key=>$value) {
      $normalized_key = WorklogNormalize::normalize_key($key,'- ','_');
      $client_data_normalized[ $normalized_key ] = $value;
    }      
    
    if (empty($client_data_normalized) || !is_array($client_data_normalized)) return null;
    
    if (empty($client_data_normalized['client_name'])) {
      if (!empty($client_data_normalized['client_short_name'])) $client_data_normalized['client_name'] = $client_data_normalized['client_short_name'];
      else if (!empty($client_data_normalized['client_full_name'])) $client_data_normalized['client_name'] = $client_data_normalized['client_full_name'];
      else if (!empty($client_data_normalized['client_tight_name'])) $client_data_normalized['client_name'] = $client_data_normalized['client_tight_name'];
      else if (!empty($client_data_normalized['client_cli_name'])) $client_data_normalized['client_name'] = $client_data_normalized['client_cli_name'];
    }
    
    return $client_data_normalized;
    
  }
  public static function get_client_projects_list($client_name) {

    if (empty($client_name) || !is_string($client_name)) return null;
    
    $client_projects_key = 'Projects-'.$client_name;
    
    $client_projects_list = current( WorklogDB::db( $client_projects_key ) );

    if (empty($client_projects_list) || !is_array($client_projects_list)) return null;
        
    return $client_projects_list;
    
  }
  public static function get_client_project_from_list($client_name,$list) {
    
    if (empty($client_name) || !is_string($client_name)) return null;
    
    if (empty($list) || !is_array($list)) return null;
    
    $possible_client_projects = [];

    $client_project_list = WorklogLookup::get_client_projects_list($client_name);
    
    if (empty($client_project_list) || !is_array($client_project_list)) return null;
    
    foreach($list as $item) {
      
      if (empty($item) || !is_string($item)) continue;
      
      $item_normalized = WorklogNormalize::normalize_key($item);
      
      foreach($client_project_list as $project) {
        
        if (empty($project) || !is_string($project)) continue;
        
        $project_normalized = WorklogNormalize::normalize_key($project);
        
        if ($item_normalized==$project_normalized) {
            $possible_client_projects[$item] = $item;
        }
      
      }

    }
    
    if (count($possible_client_projects) > 1) {
      throw new Exception("More than one possible project found: ".implode(",",$possible_client_projects));
    }
    
    $client_project = reset($possible_client_projects);
    
    return $client_project;
    
  }

}
