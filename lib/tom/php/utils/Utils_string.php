<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_string.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to strings.
*
* Author: Tom McDonnell 2010-06-17.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_string
{
   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public static function filterNonPrintable($string)
   {
      // Note on Error Reporting
      // -----------------------
      // The iconv function causes notice-level error messages in some circumstances.
      //
      // Eg. Notice: iconv(): Detected an incomplete multibyte character in input string in <file>
      //
      // Therefore E_NOTICE level errors are temporarily disabled.
      $oldErrorLevel = error_reporting();
      error_reporting($oldErrorLevel & ~E_NOTICE);

      $string = iconv('UTF-8', 'ASCII//IGNORE', $string);
      $string = str_replace("\n", '_{_NEWLINE_}_', $string);
      $string = preg_replace( '/[^[:print:]]/', '', $string);
      $string = str_replace('_{_NEWLINE_}_', "\n", $string);
      $string = trim($string);

      // Reset error reporting level to old value.
      error_reporting($oldErrorLevel);

      return $string;
   }

   /*
    * For use with array_map() function.
    */
   public static function encloseInThTags($string) {return "<th>$string</th>";}
   public static function encloseInTdTags($string) {return "<td>$string</td>";}

   /*
    * Eg.  When supplied  'This string contains a "quoted" word.',
    *      will return   "'This string contains a \"quoted\" word.'".
    */
   public static function escapeAndEnclose($string, $quote = '"')
   {
      if (strlen($quote) > 1)
      {
         throw new Exception("Quote string '$quote' contains more than one character.");
      }

      return $quote . str_replace("$quote", "\"$quote", $string) . $quote;
   }

   /*
    *
    */
   public static function countOccurrencesOfCharacter($string, $character)
   {
      $n = 0;

      for ($i = 0; $i < strlen($string); ++$i)
      {
         if ($string[$i] == $character)
         {
            ++$n;
         }
      }

      return $n;
   }

   /*
    *
    */
   public static function isAllUppercase($string)
   {
      for ($i = 0; $i < strlen($string); ++$i)
      {
         $c = $string[$i];

         if ($c != strtoupper($c))
         {
            return false;
         }
      }

      return true;
   }

   /*
    *
    */
   public static function replaceAllWhitespaceCharsWithSingleSpaces($string)
   {
      $string = preg_replace('/\s/', ' ', $string);
      return preg_replace('/  +/', ' ', $string);
   }
}

/*******************************************END*OF*FILE********************************************/
?>
