<?php
namespace WorklogCLI\Cli;
use WorklogCLI\CLI;
use WorklogCLI\WorklogFilter;
use WorklogCLI\WorklogSummary;
use WorklogCLI\WorklogData;
use WorklogCLI\Format;
use WorklogCLI\Output;
class CliInvoice2Export {
  
  public static function cli($args) {
    
    CLI::cli($args);
    
    // build summary
    $yaml_data = WorklogSummary::summary_invoice2();
    $invoice_number = $yaml_data['invoice']['number'];
    if (empty($invoice_number)) die('Invoice number is empty');
    
    ob_start();
    CLI::op_invoice2html();
    $output = ob_get_clean();
    
    file_put_contents("$invoice_number.html",$output);

  }
  
}
