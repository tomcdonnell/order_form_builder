<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_array.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to arrays.
*
* Author: Tom McDonnell 2010-06-18.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_array
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
   public static function arraysAreEqual(Array $a, Array $b)
   {
      assert('is_array($a) && is_array($b)');

      if (count($a) != count($b))
      {
         return false;
      }

      $count   = count($a);
      $keysOfA = array_keys($a);

      for ($i = 0; $i < $count; ++$i)
      {
         $key = $keysOfA[$i];
         $ai  = $a[$key];
         $bi  = $b[$key];

         if (gettype($ai) != gettype($bi))
         {
            return false;
         }

         $valuesAreEqual = (is_array($ai))? self::arraysAreEqual($ai, $bi): ($ai == $bi);

         if (!$valuesAreEqual)
         {
            return false;
         }
      }

      return true;
   }

   /*
    *
    */
   public static function arraysAreEqualWhenSorted(Array $a, Array $b)
   {
      sort($a);
      sort($b);

      return self::arraysAreEqual($a, $b);
   }

   /*
    *
    */
   public static function rtrim(Array $a, $blankValue = '', $boolPreserveKeys = false)
   {
      assert('is_array($a)');

      for ($i = count($a) - 1; $i >= 0; --$i)
      {
         if ($a[$i] != $blankValue)
         {
            break;
         }
      }

      return array_slice($a, 0, $i, $boolPreserveKeys);
   }

   /*
    * @param $maxRowsForSingleColumn
    * @param $maxColumns
    *    See code for how table dimensions are affected by these two parameters.
    */
   public static function getStringsAsHtmlTable
   (
      Array $strings, $indent, $maxRowsForSingleColumn = 5, $maxColumns = 5,
      $className = 'tableColsSameNoHeading'
   )
   {
      if (count($strings) == 0)
      {
         return '';
      }

      $nStrings  = count($strings);
      $tableType =
      (
         ($nStrings <= $maxRowsForSingleColumn)? 'small':
         (
            ($nStrings <= $maxRowsForSingleColumn * $maxColumns)? 'medium': 'large'
         )
      );

      switch ($tableType)
      {
       case 'small':
         $nCols = 1;
         $nRows = $nStrings;
         break;
       case 'medium':
         $nRows = ceil(sqrt($nStrings));
         $nCols = $nRows;
         break;
       case 'large':
         $nCols = $maxColumns;
         $nRows = ceil($nStrings / $nCols);
         break;
       default:
         throw new Exception('Unexpected case.');
      }

      $twoDimStringsArray = self::fill2dArrayMaintainingColumnOrder($strings, $nRows, $nCols);

      $i     = &$indent;
      $html  = "$i<table class='$className'>\n";
      $html .= "$i <tbody>\n";

      foreach ($twoDimStringsArray as $strings)
      {
         $html .= "$i  <tr>";

         foreach ($strings as $string)
         {
            $html .= "<td>$string</td>";
         }

         $html .= "</tr>\n";
      }

      $html .= "$i </tbody>\n";
      $html .= "$i</table>\n";

      return $html;
   }

   /*
    * Given a two dimensional array having continuous integer keys starting at zero, the arrays
    * inside which also meet this restriction, return a new array which is the given array with
    * rows swapped with columns.
    */
   public static function transpose(Array $arrayIn)
   {
      if (count($arrayIn) == 0)
      {
         return array();
      }

      $firstRow = $arrayIn[0];
      $nRows    = count($arrayIn );
      $nCols    = count($firstRow);
      $arrayOut = array_fill(0, $nCols, array());

      for ($r = 0; $r < $nRows; ++$r)
      {
         for ($c = 0; $c < $nCols; ++$c)
         {
            $arrayOut[$c][$r] = $arrayIn[$r][$c];
         }
      }

      return $arrayOut;
   }

   /*
    * Return a new array containing for each shared key, the sum of the corresponding values.
    * If the value is an array, process recursively.
    *
    * If any value is not an integer and is not an array, or if the types of the values for the
    * same key do not match in the two arrays, throw an exception.
    *
    * Example:
    *    $array1 = array('a' => 1, 'b' => array('c' => 2, 'd' => 3          )          );
    *    $array2 = array('a' => 4, 'b' => array('c' => 5, 'd' => 6, 'e' => 7), 'f' => 8);
    *    $result = array('a' => 5, 'b' => array('c' => 7, 'e' => 9, 'e' => 7 , 'f' => 8);
    */
   public static function mergeSumRecursive(Array $array1, Array $array2)
   {
      $mergedArray      = array();
      $uniqueKeysAsKeys = array();

      foreach (array_keys($array1) as $key) {$uniqueKeysAsKeys[$key] = null;}
      foreach (array_keys($array2) as $key) {$uniqueKeysAsKeys[$key] = null;}

      foreach (array_keys($uniqueKeysAsKeys) as $key)
      {
         $value1 = (array_key_exists($key, $array1))? $array1[$key]: null;
         $value2 = (array_key_exists($key, $array2))? $array2[$key]: null;

         if ($value1 !== null && $value2 === null) {$mergedArray[$key] = $value1; continue;}
         if ($value2 !== null && $value1 === null) {$mergedArray[$key] = $value2; continue;}

         if (is_int($value1) && is_int($value2))
         {
            $mergedArray[$key] = $value1 + $value2;
            continue;
         }

         if (is_array($value1) && is_array($value2))
         {
            $mergedArray[$key] = self::mergeSumRecursive($value1, $value2);
            continue;
         }

         throw new Exception("Unexpected type or type mismatch for key '$key'.");
      }

      return $mergedArray;
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    * Eg. Given $array       = array('one', 'two', 'three', 'four', 'five');
    *           $nRows       = 2;
    *           $nCols       = 2;
    *           $fillerValue = '';
    *
    *     Return array
    *     (
    *        array('one'  , 'four'),
    *        array('two'  , 'five'),
    *        array('three', ''    )
    *     );
    *
    */
   private static function fill2dArrayMaintainingColumnOrder
   (
      Array $values, $nRows, $nCols, $fillerValue = ''
   )
   {
      $valuesArray = array();
      $nValues     = count($values);
      $n           = -1;

      if ($nValues > $nRows * $nCols)
      {
         throw new Exception('Too many values for given array dimensions.');
      }

      for ($c = 0; $c < $nCols; ++$c)
      {
         $valuesArray[$c] = array();

         for ($r = 0; $r < $nRows; ++$r)
         {
            $valuesArray[$c][$r] = (++$n < $nValues)? $values[$n]: $fillerValue;
         }
      }

      return self::transpose($valuesArray);
   }
}

/*******************************************END*OF*FILE********************************************/
?>
