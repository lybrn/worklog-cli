<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\JsonConfig;
use WorklogCLI\YAML;
use WorklogCLI\Twig;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliTemplateOut {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    $saved = JsonConfig::config_get('worklog-config');
    $invoice_template_name = $invoice_data['template'] ?: current( CLI::get_template_paths() ); 
    
    $markdownfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.md';
    $markdowncontent = file_get_contents($markdownfile);
    $yamlfile = CLI::root().'/templates/'.$invoice_template_name.'/'.$invoice_template_name.'.yaml';
    $yamlcontent = file_get_contents($yamlfile);  
    $yamldata = YAML::decode($yamlcontent);

    $twig = Format::maketwig($markdowncontent,$yamldata);
    $data = CLI::get_filtered_data();
    $rangedata =  WorklogSummary::summary_rangedata($data,CLI::$args);
    $rangedata = Format::array_flatten($rangedata);
    $rangedata = Format::array_extrude($rangedata);
    
    print Twig::process($twig,$rangedata)."\n";
    
  }
  
}
