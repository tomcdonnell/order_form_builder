<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_misc.php"
*
* Project: Utilities.
*
* Purpose: Miscellaneous utilities.
*
* Author: Tom McDonnell 2008-06-28.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/Utils_validator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_csv
{
   // Public functions. -----------------------------------------------------------------------//

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   /*
    *
    */
   public static function writeRowsToCsvFile
   (
      $file, $rows, $doubleQuoteReplacement = '\"', $boolSortRowByColumnHeading = false
   )
   {
      foreach ($rows as $row)
      {
         if ($boolSortRowByColumnHeading)
         {
            ksort($row);
         }

         $escapedRow = array();

         foreach ($row as $key => $value)
         {
            $escapedRow[] = str_replace('"', $doubleQuoteReplacement, $value);
         }

         $success = fwrite($file, '"' . implode('","', $escapedRow) . "\"\n");

         if ($success === false)
         {
            throw new Exception('Error on call to fwrite.');
         }
      }
   }

   /*
    *
    */
   public static function addBlanksForMissingFields($partialRow, $fullSetOfColumnHeadingsAsKeys)
   {
      foreach (array_keys($partialRow) as $key)
      {
         if (!array_key_exists($key, $fullSetOfColumnHeadingsAsKeys))
         {
            throw new Exception("Unknown key '$key' found in contacts row.");
         }
      }

      foreach (array_keys($fullSetOfColumnHeadingsAsKeys) as $key)
      {
         if (!array_key_exists($key, $partialRow))
         {
            $partialRow[$key] = '';
         }
      }

      return $partialRow;
   }

   /*
    *
    */
   public static function cleanUpRows(&$rows, $options = array())
   {
      self::checkOptionsArrayAndSetDefaults($options);

      foreach ($rows as &$row)
      {
         self::cleanUpRow($row, $options, false);
      }
   }

   /*
    *
    */
   public static function cleanUpRow(&$row, $options = array(), $checkOptionsArray = true)
   {
      if ($checkOptionsArray)
      {
         self::checkOptionsArrayAndSetDefaults($options);
      }

      foreach ($row as $key => &$value)
      {
         self::cleanUpValue($value, $options, false);
      }
   }

   /*
    *
    */
   public static function cleanUpValue(&$value, $options = array(), $checkOptionsArray = true)
   {
      if ($checkOptionsArray)
      {
         self::checkOptionsArrayAndSetDefaults($options);
      }

      // NOTE: Trim first, so that for example 'removeTrailingCommas' works as intended.
      if ($options['trim'])
      {
         $value = trim($value);
      }

      if ($options['removeCarriageReturns'])
      {
         $value = str_replace("\r", '', $value);
      }

      if ($options['removeTrailingCommas'])
      {
         $value = preg_replace('/,$/', '', $value);
      }

      if ($options['replaceMultipleSpacesWithSingle'])
      {
         $value = preg_replace('/\s+/', ' ', $value);
      }

      if ($options['replaceMultipleNewlinesWithDouble'])
      {
         $value = preg_replace('/\n\n+/', "\n\n", $value);
      }
   }

   // Private functions. ///////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function checkOptionsArrayAndSetDefaults(&$options)
   {
      Utils_validator::checkArrayAndSetDefaults
      (
         $options, array(), array
         (
            'removeCarriageReturns'             => array('bool', true),
            'removeTrailingCommas'              => array('bool', true),
            'replaceMultipleNewlinesWithDouble' => array('bool', true),
            'replaceMultipleSpacesWithSingle'   => array('bool', true),
            'trim'                              => array('bool', true)
         )
      );
   }
}

/*******************************************END*OF*FILE********************************************/
?>
