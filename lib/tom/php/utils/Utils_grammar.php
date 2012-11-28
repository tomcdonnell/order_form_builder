<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_grammer.php"
*
* Project: Utilities.
*
* Purpose: Utilities relating to the creation of text strings with correct grammer from arrays.
*
* Author: Tom McDonnell 2008-03-22.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_grammar
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
    * Create and return a text string of the words in array '$wordsList', with correct grammer.
    *
    * Eg 1. $wordsList = ('one', 'two', 'three', 'four'),
    *       returns 'one, two, three, and four'.
    *
    * Eg 2. $wordsList = ('one', 'two'),
    *       returns 'one and two'.
    */
   public static function createCommaSeparatedList($words)
   {
      assert('is_array($words)');

      $count = count($words);

      switch ($count)
      {
       case 0: return '';
       case 1: return $words[0];
       case 2: return "{$words[0]} and {$words[1]}";
       default:
         $lastWord = $words[$count - 1];
         $words[$count - 1] = "and $lastWord";
         return implode(', ', $words);
      }
   }

   /*
    * @param $booleanArray {array}
    *    Array containing only boolean values as values.  Keys should be strings.
    *
    * @param $boolInvert {boolean}
    *    If true, then array value must be false for key to be included in list.
    *
    * Eg. Given array('snoop' => true, 'alfred' => false, 'kimba' => true, 'jimi' => true),
    *     returns "snoop, kimba, and jimi".
    */
   public static function convBooleanArrayToListOfKeys($booleanArray, $boolInvert = false)
   {
      assert('is_array($booleanArray)');
      assert('is_bool($boolInvert)');

      $selectedKeys = array();

      foreach ($booleanArray as $key => $bool)
      {
         if ($bool != $boolInvert)
         {
            $selectedKeys[] = $key;
         }
      }

      return self::createCommaSeparatedList($selectedKeys);
   }

   /*
    * Given an array containing only 0s and 1s, Create and return a text string
    * describing the indices of the array '$array' whose corresponding value is 1.
    * NOTE: For the purposes of this function, array indices start at 1.
    * Eg.  If $array = (0, 1, 1, 1, 1, 0, 1, 1, 0, 1)
    *      Function returns '2-5, 7, 8, and 10'
    */
   public static function convBooleanArrayToListOfIndices($array)
   {
      $count = count($array); // No. of elements in array.

      if ($count == 0)
      {
         return '';
      }

      $str = '';
      $inSeries = false;
      $n = 0;
      $nChecked = 0;
      while ($n < $count)
      {
         if ($array[$n])
         {
            // Element $n + 1 is checked.
            ++$nChecked;

            if ($inSeries)
              ++$n;
            else
            {
               // Element $n + 1 is not in the middle or end of a series (it may be at the start).

               if ($str != '')
                 $str .= ', ';

               $str .= $n + 1;

               if ($n <= $count - 3 && $array[$n + 1] && $array[$n + 2])
               {
                  // The next two elements are also checked (elements $n + 2 and $n + 3),
                  // so element $n + 1 is the start of a series.

                  $inSeries  = true;
                  $str      .= '-';
                  $nChecked += 2; // Increment nChecked (1st in series has already been counted).
                  $n        += 3; // Proceed to next unknown element.
               }
               else
                 // Element $n + 1 is not any part of a series.
                 ++$n;
            } 
         }
         else
         {
            // Element $n + 1 is not checked.

            if ($inSeries)
            {
               // The previous element (element $n) was the last of a series.
               $inSeries = false;
               $str .= $n;
            }
            ++$n;
         }
      }
      if ($inSeries)
        $str .= $n;

      // Insert the word 'and' into $str before the last element.
      // NOTE: If there are only two elements, no comma is required.
      //       Eg. "1 and 2" as opposed to "1, 2, and 3".
      for ($i = strlen($str) - 1; $i > 0 && $str[$i] != ','; --$i);
      if ($i > 0)
      {
         if ($nChecked > 2)
           // Insert the string ' and' after the last comma in $str.
           $str = substr_replace($str, ' and', $i + 1, 0);
         else
           // Replace the last comma in $str with the string ' and'.
           $str = substr_replace($str, ' and', $i, 1);
      }

      return $str;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
