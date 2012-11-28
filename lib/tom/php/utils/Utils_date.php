<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_date.php"
*
* Project: Utilities.
*
* Purpose: Utilities relating to dates.
*
* Author: Tom McDonnell 2008-01-07.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/Utils_validator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_date
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
   public static function getMonthOneLetterAbbrev($month)
   {
      switch ($month)
      {
       case  1: return 'J';
       case  2: return 'F';
       case  3: return 'M';
       case  4: return 'A';
       case  5: return 'M';
       case  6: return 'J';
       case  7: return 'J';
       case  8: return 'A';
       case  9: return 'S';
       case 10: return 'O';
       case 11: return 'N';
       case 12: return 'D';
       default: throw new Exception("Invalid month number '$monthNo'.");
      }
   }

   /*
    *
    */
   public static function getMonthThreeLetterAbbrev($month)
   {
      switch ($month)
      {
       case  1: return 'Jan';
       case  2: return 'Feb';
       case  3: return 'Mar';
       case  4: return 'Apr';
       case  5: return 'May';
       case  6: return 'Jun';
       case  7: return 'Jul';
       case  8: return 'Aug';
       case  9: return 'Sep';
       case 10: return 'Oct';
       case 11: return 'Nov';
       case 12: return 'Dec';
       default: throw new Exception("Invalid month number '$monthNo'.");
      }
   }

   /*
    *
    */
   public static function getMonthName($monthNo)
   {
      switch ($monthNo)
      {
       case  1: return 'January';
       case  2: return 'February';
       case  3: return 'March';
       case  4: return 'April';
       case  5: return 'May';
       case  6: return 'June';
       case  7: return 'July';
       case  8: return 'August';
       case  9: return 'September';
       case 10: return 'October';
       case 11: return 'November';
       case 12: return 'December';
       default: throw new Exception("Invalid month number '$monthNo'.");
      }
   }

   /*
    *
    */
   public static function parseSqlDateString($dateStr)
   {
      assert('is_string($dateStr)'   );
      assert('strlen($dateStr) == 10');

      return array
      (
         'year'  => (int)(substr($dateStr,  0,  4)),
         'month' => (int)(substr($dateStr,  5,  2)),
         'day'   => (int)(substr($dateStr,  8,  2))
      );
   }

   /*
    *
    */
   public static function createSqlDateStr($date)
   {
      assert('is_array($date)');

      // NOTE: The following tests are necessary to prevent SQL insertion attacks.
      if (!is_int($date['year']) || !is_int($date['month']) || !is_int($date['day']))
      {
         throw new Exception('Unexpected type(s) encountered.');
      }

      $y = $date['year' ];
      $m = $date['month'];
      $d = $date['day'  ];

      if ($y < 1000 || $y > 9999)
      {
         throw new Exception('Year must be four digits.');
      }

      return $y . '-' . (($m < 10)? '0': '') . $m . '-' . (($d < 10)? '0': '') . $d;
   }

   /*
    * This function is necessary despite the existence of function strtotime().
    *
    * It is used for now because strtotime cannot differentiate between strings in format
    * 'dd-mm-yyyy' and those in format 'mm-dd-yyyy' where dd is <= 12.
    *
    * For a list of formats understood by function strtotime(),
    * see http://au.php.net/manual/en/datetime.formats.date.php.
    */
   public static function getTimestampFromDateString
   (
      $dateStr, $format = 'yyyy-mm-dd', $hours = 0, $minutes = 0, $seconds = 0
   )
   {
      switch ($format)
      {
       case 'yyyy-mm-dd': $regEx = '/^(\d{4})-(\d{2})-(\d{2})$/'  ; $y = 1; $m = 2; $d = 3; break;
       case 'yyyy/mm/dd': $regEx = '/^(\d{4})\/(\d{2})\/(\d{2})$/'; $y = 1; $m = 2; $d = 3; break;
       case 'dd-mm-yyyy': $regEx = '/^(\d{2})-(\d{2})-(\d{4})$/'  ; $d = 1; $m = 2; $y = 3; break;
       case 'dd/mm/yyyy': $regEx = '/^(\d{2})\/(\d{2})\/(\d{4})$/'; $d = 1; $m = 2; $y = 3; break;
       default: throw new Exception("Unknown format string '$format'");
      }

      if (preg_match($regEx, $dateStr, $matches))
      {
         $y = $matches[$y];
         $m = $matches[$m];
         $d = $matches[$d];

         if (checkdate($m, $d, $y))
         {
            return mktime($hours, $minutes, $seconds, $m, $d, $y);
         }
      }

      return false;
   }

   /*
    *
    */
   public static function getDurationInSecondsOfUnixTimePeriodOverlap
   (
      $s1Unix, $f1Unix, $s2Unix, $f2Unix
   )
   {
      if ($s1Unix > $f1Unix || $s2Unix > $f2Unix)
      {
         throw new Exception('Supplied start time greater than supplied finish time.');
      }

      // If period 1 and period 2 do not overlap...
      if ($f2Unix <= $s1Unix || $s2Unix >= $f1Unix) {return 0;}

      // If period 1 starts   within period 2...
      if ($s2Unix <= $s1Unix) {return ($f2Unix - $s1Unix);}

      // If period 1 finishes within period 2...
      if ($f2Unix >= $f1Unix) {return ($f1Unix - $s2Unix);}

      // Else period 2 is entirely within period 1.
      return ($f2Unix - $s2Unix);
   }

   /*
    *
    */
   public static function convertFormat($dateSrc, $formatSrc, $formatDst)
   {
       if ($formatSrc != 'dd/mm/yyyy')
       {
           throw new Exception("Unknown date format source string '$formatSrc'.");
       }

       if ($formatDst != 'yyyy-mm-dd')
       {
           throw new Exception("Unknown date format destination string '$formatDst'.");
       }

       if (!Utils_validator::checkDateString($dateSrc, $formatSrc))
       {
           return false;
       }

       $d = substr($dateSrc, 0, 2);
       $m = substr($dateSrc, 3, 2);
       $y = substr($dateSrc, 6   );

       return "$y-$m-$d";
   }

   /*
    * If A < B return -1
    * If A = B return  0
    * If A > B return +1
    *
    * NOTE: Integers should be supplied to avoid problems when numeric strings are compared.
    *       Eg. ('7' < '11') will evaluate to false.
    */
   public static function compare($yA, $mA, $dA, $yB, $mB, $dB)
   {
      foreach (array($yA, $mA, $dA, $yB, $mB, $dB) as $v)
      {
         if (!is_int($v))
         {
            throw new Exception("Expected integer.  Received '$v' (" . gettype($v) . ').');
         }
      }

      return
      (
         ($yA == $yB)?
         (
            ($mA == $mB)?
            (
               ($dA == $dB)?
               0:
               (($dA > $dB)? 1: -1)
            ):
            (($mA > $mB)? 1: -1)
         ):
         (($yA > $yB)? 1: -1)
      );
   }
}

/*******************************************END*OF*FILE********************************************/
?>
