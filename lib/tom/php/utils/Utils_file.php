<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_file.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to files.
*
* Author: Tom McDonnell 2010-06-23.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_file
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   /*
    * NOTE: Not very efficient.  Useful for small files though.
    */
   public static function fileContainsLine($filename, $line)
   {
      $fileAsString = file_get_contents($filename);

      if ($fileAsString === false)
      {
         throw new Exception("Error while opening file '$filename'.");
      }

      $lines = explode("\n", $fileAsString);

      // Discard empty line.
      array_pop($lines);

      return in_array($line, $lines);
   }

   /*
    *
    */
   public static function removePathFromDir($dir)
   {
      for ($i = strlen($dir) - 1; $i > 0; --$i)
      {
         if ($dir[$i] == '/')
         {
            return substr($dir, $i + 1);
         }
      }

      return $dir;
   }

   /*
    *
    */
   public static function getLines($filename)
   {
      $fileAsString = file_get_contents($filename);
      $lines        = explode("\n", $fileAsString);

      array_pop($lines); // Exclude empty string.

      return $lines;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
