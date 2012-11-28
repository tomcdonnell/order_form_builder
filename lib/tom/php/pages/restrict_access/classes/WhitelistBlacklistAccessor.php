<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "WhitelistBlacklistAccessor.php"
*
* Project: Security.
*
* Purpose: Provide access to SOE id whitelists and blacklists.
*
* Author: Tom McDonnell 2011-02-15.
*
\**************************************************************************************************/

/*
 *
 */
class WhitelistBlacklistAccessor
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
   public static function getBothLists($db)
   {
      $soeAuthenticationArray = $_SESSION['soeAuthentication'];

      $whitelist =
      (
         (array_key_exists('soeidsWhitelistTableName', $soeAuthenticationArray))?
         self::_getSoeidsFromSqlTable($db, $soeAuthenticationArray['soeidsWhitelistTableName']):
         self::_getLinesAsArrayFromFile(self::WHITELIST_FILENAME)
      );

      $blacklist =
      (
         (array_key_exists('soeidsBlacklistTableName', $soeAuthenticationArray))?
         self::_getSoeidsFromSqlTable($db, $soeAuthenticationArray['soeidsBlacklistTableName']):
         self::_getLinesAsArrayFromFile(self::BLACKLIST_FILENAME)
      );

      return array($whitelist, $blacklist);
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _getSoeidsFromSqlTable($db, $tableName)
   {
      $soeids = $db->fetchCol
      (
         // NOTE: The presence of a `deleted` column as well as an `soeid` column is assumed.
         'SELECT soeid
          FROM ' . $db->quoteIdentifier($tableName) . '
          WHERE deleted="0"'
      );

      return $soeids;
   }

   /*
    *
    */
   private static function _getLinesAsArrayFromFile($filename)
   {
      if (!file_exists($filename))
      {
         return null;
      }

      $fileAsString = file_get_contents($filename);
      $lines        = explode("\n", $fileAsString);

      // Discard empty string.
      array_pop($lines);

      foreach ($lines as &$line)
      {
         $line = rtrim($line);
      }

      return $lines;
   }

   // Class constants. //////////////////////////////////////////////////////////////////////////

   const WHITELIST_FILENAME = 'soeids_whitelist.txt';
   const BLACKLIST_FILENAME = 'soeids_blacklist.txt';
}

/*******************************************END*OF*FILE********************************************/
?>
