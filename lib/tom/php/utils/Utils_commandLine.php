<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_commandLine.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to command line scripts.
*
* Author: Tom McDonnell 2011-10-02.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/Utils_misc.php';

/*
 *
 */
class Utils_commandLine
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function getStandardUsageString($filename)
   {
      $pos1 = strrpos($filename, '/' );
      $pos2 = strrpos($filename, '\\');
      $pos  = Utils_misc::switchAssign
      (
         (($pos1 === false)? '1': '0') . '-' . (($pos2 === false)? '1': '0'), array
         (
            '0-0' => (($pos1 > $pos2)? $pos1 + 1: $pos2 + 1),
            '0-1' => $pos1 + 1                              ,
            '1-0' => $pos2 + 1                              ,
            '1-1' => 0
         )
      );

      $filenameMinusPath = substr($filename, $pos);

      return
      (
         // 80 char max width to suit standard console.
         "Usage: php $filenameMinusPath [arguments]\n" .
         "\n"                                          .
         "Arguments:\n"                                .
         " * -actual\n"                                .
         "   Actually update the database.\n"          .
         " * -hypothetical\n"                          .
         "   Describe the updates that would be performed if -actual was used.\n"
      );
   }

   /*
    *
    */
   public static function echoNoteRegardingHypotheticalMode()
   {
      // 80 char max width to suit standard console.
      echo "\nNote Regarding Hypothetical Mode\n";
      echo "--------------------------------\n";
      echo 'The script is running in hypothetical mode.  All database changes referred to';
      echo ' in the output below will be rolled back before the script completes.  The';
      echo ' changes will also be rolled back should the script be interrupted part way';
      echo " through.\n\n";
   }

   /*
    *
    */
   public static function getHypotheticalModeBogusExceptionDescription()
   {
      return
      (
         'No error actually occurred.  A bogus exeception was thrown in order to cause a rollback' .
         ' of all database changes because the script was run in hypothetical mode.'
      );
   }
}

/*******************************************END*OF*FILE********************************************/
?>
