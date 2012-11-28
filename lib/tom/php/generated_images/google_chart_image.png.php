<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "google_chart_image.png.php"
*
* Project: Generated images.
*
* Purpose: Generate a PNG image using the Google Chart API (http://code.google.com/apis/chart).
*
* Author: Tom McDonnell 2011-03-15.
*
\**************************************************************************************************/

// Settings. ///////////////////////////////////////////////////////////////////////////////////////

session_start();

// Defines. ////////////////////////////////////////////////////////////////////////////////////////

define('URL', 'https://chart.googleapis.com/chart?chid=' . md5(uniqid(rand(), true)));

// Globally excecuted code. ////////////////////////////////////////////////////////////////////////

try
{
   // Send the request, and print out the returned bytes.
   $context = stream_context_create
   (
      array
      (
         'http' => array
         (
            'method'  => 'POST'                   ,
            'header'  => 'content-type: image/png',
            'content' => http_build_query($_SESSION['googleChartParams'], '', '&')
         )
      )
   );

   $file = fopen(URL, 'r', false, $context);

   if ($file === false)
   {
      throw new Exception('Could not open file "' . URL . '".');
   }

   fpassthru($file);
}
catch (Exception $e)
{
   echo $e->getMessage();
}

/*******************************************END*OF*FILE********************************************/
?>
