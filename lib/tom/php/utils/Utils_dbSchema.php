<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_dbSchema.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to database schema.
*
* Author: Tom McDonnell 2010-06-17.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_dbSchema
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
    *
    */
   public static function getColumnHeadings($databaseName, $tableName)
   {
      $dbc = DatabaseManager::get('information_schema');

      $rows = $dbc->query
      (
         'SELECT COLUMN_NAME
          FROM COLUMNS
          WHERE TABLE_SCHEMA=?
          AND TABLE_NAME=?',
         array($databaseName, $tableName)
      );

      $columnNames = array();
      foreach ($rows as $row)
      {
         $columnNames[] = $row['COLUMN_NAME'];
      }

      return $columnNames;
   }

   /*
    *
    */
   public static function getTableNamesForDatabase($databaseName)
   {
      $dbc = DatabaseManager::get('information_schema');

      $rows = $dbc->query
      (
         'SELECT DISTINCT TABLE_NAME
          FROM COLUMNS
          WHERE TABLE_SCHEMA=?',
         array($databaseName)
      );

      $tableNames = array();
      foreach ($rows as $row)
      {
         $tableNames[] = $row['TABLE_NAME'];
      }

      return $tableNames;
   }

   /*
    *
    */
   public static function getColumnHeadingsByTableName($databaseName)
   {
      $dbc        = DatabaseManager::get('information_schema');
      $tableNames = self::getTableNamesForDatabase($databaseName);

      foreach ($tableNames as $tableName)
      {
         $columnHeadingsByTableName[$tableName] =
         (
            self::getColumnHeadings($databaseName, $tableName)
         );
      }

      return $columnHeadingsByTableName;
   }

   /*
    *
    */
   public static function tableHasColumn($databaseName, $tableName, $columnName)
   {
      $dbc = DatabaseManager::get('information_schema');

      $rows = $dbc->query
      (
         'SELECT EXISTS
          (
             SELECT *
             FROM COLUMNS
             WHERE TABLE_SCHEMA=?
             AND TABLE_NAME=?
             AND COLUMN_NAME=?
          ) AS `exists`',
         array($databaseName, $tableName, $columnName)
      );

      if (count($rows) != 1)
      {
         throw new Exception('Unexpected number of rows returned by query.');
      }

      return ($rows[0]['exists'] == '1');
   }
}

/*******************************************END*OF*FILE********************************************/
?>
