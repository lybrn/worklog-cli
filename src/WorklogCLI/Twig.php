<?php 
namespace WorklogCLI;
class Twig {

  public static function process($content,$vars = array()) {
    // load twig
    $twig = new \Twig_Environment( new \Twig_Loader_String() );
    // process twig
    try { 
      $content = @$twig->render('{% autoescape false %}'.$content.'{% endautoescape %}', $vars); 
    } 
    catch(Exception $e) { }
    // return content
    return $content;
  }
  
}
