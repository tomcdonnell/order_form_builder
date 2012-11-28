<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_time.php"
*
* Project: Utilities.
*
* Purpose: Utilities relating to time.
*
* Author: Tom McDonnell 2008-01-07.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_time
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
   public static function parseSqlTimeString($timeStr)
   {
      assert('is_string($timeStr)');
      assert('strlen($timeStr) == 10');

      return array
      (
         'year'  => (int)(substr($timeStr,  0,  4)),
         'month' => (int)(substr($timeStr,  5,  2)),
         'day'   => (int)(substr($timeStr,  8,  2))
      );
   }

   /*
    *
    */
   public static function createSqlTimeStr($time)
   {
      assert('is_array($time)');

      // NOTE: The following tests are necessary to prevent SQL insertion attacks.
      if (!is_int($time['hour']) || !is_int($time['minute']) || !is_int($time['second']))
      {
         throw new Exception('Unexpected type(s) encountered.');
      }

      $h = $time['hour'  ];
      $m = $time['minute'];
      $s = $time['second'];

      return $h . ':' . (($m < 10)? '0': '') . $m . ':' . (($s < 10)? '0': '') . $s;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
