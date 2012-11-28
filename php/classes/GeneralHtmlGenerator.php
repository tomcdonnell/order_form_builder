<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_html.php';

/*
 *
 */
class GeneralHtmlGenerator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct ()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function echoHtmlHeaderIncludingOpenBodyTag
   (
      Array $jsFilenames = array(), Array $cssFilenames = array()
   )
   {
      // Start output buffering so that if an exception is thrown during generation of
      // the page HTML, a 'Location' header may be used to redirect to the error page.
      ob_start();
?>
<!DOCTYPE html>
<html>
 <head>
  <meta http-equiv='content-type' content='text/html; charset=utf-8'>
  <meta charset='utf-8'>
  <meta name='author' content='Tom McDonnell'>
  <meta name='description' content='iPhone, iPad, Nokia Application Form'>
  <meta name='keywords' content='Staff,phone,ipad,iphone,nokia,application,form'>
<?php
      Utils_html::echoHtmlScriptAndLinkTagsForJsAndCssFiles($jsFilenames, $cssFilenames, ' ');
?>
  <title>DPI iPhone, iPad, Nokia Application Form - DPI Victoria</title>
 </head>
 <body>
<?php
      return '  '; // Return the indentation string to be used for subsequent HTML lines.
   }

   /*
    *
    */
   public static function echoHtmlFooterIncludingCloseBodyTag()
   {
?>
 </body>
</html>
<?php
      // Flush the output buffer now that the page has been
      // output without causing an exception to be thrown.
      ob_flush();
   }
}
?>
