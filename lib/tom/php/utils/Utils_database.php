<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_database.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to database operations.
*
* Author: Tom McDonnell 2008-06-29.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../database/DatabaseManager.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_database
{
   // Public functions. -----------------------------------------------------------------------//

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   // EXISTS queries. -------------------------------------------------------------------------//

   /*
    * @return {boolean}
    */
   public static function tableExistsInDatabase(DatabaseConnection $dbc, $tableName)
   {
      assert('is_string($tableName)');

      $rows = $dbc->query
      (
         'SHOW TABLES
          LIKE "' . mysql_real_escape_string($tableName) . '"'
      );

      return (count($rows) > 0);
   }

   /*
    * @return {boolean}
    */
   public static function rowExistsInTable(DatabaseConnection $dbc, $tableName, $whereDetails)
   {
      assert('is_string($tableName)'    );
      assert('is_array($whereDetails)'  );
      assert('count($whereDetails) >= 1');

      $keys   = array_keys($whereDetails);
      $values = array_values($whereDetails);

      $rows = $dbc->query
      (
         'SELECT EXISTS
          (
             SELECT *
             FROM `' . mysql_real_escape_string($tableName) . '`
             WHERE `' . implode('`=? AND `', $keys) . '`=?
          ) AS `exists`',
         $values
      );

      assert('count($rows) == 1');

      return ($rows[0]['exists'] == '1');
   }

   // SHOW queries. ---------------------------------------------------------------------------//

   /*
    *
    */
   public static function getTableNames(DatabaseConnection $dbc)
   {
      $rows = $dbc->queryNumAssoc('SHOW TABLES');

      $tableNames = array();
      foreach ($rows as $row)
      {
         $tableNames[] = $row[0];
      }

      return $tableNames;
   }

   /*
    *
    */
   public static function getDatabaseNames(DatabaseConnection $dbc)
   {
      $rows = $dbc->queryNumAssoc('SHOW DATABASES');

      $databaseNames = array();
      foreach ($rows as $row)
      {
         $databaseNames[] = $row[0];
      }

      return $databaseNames;
   }

   // SELECT queries. -------------------------------------------------------------------------//

   /*
    *
    */
   public static function getRowCountByTableName(DatabaseConnection $dbc, $tableNames = null)
   {
      $rowCountByTableName = array();

      if ($tableNames === null)
      {
         $tableNames = self::getTableNames($dbc);
      }

      foreach ($tableNames as $tableName)
      {
         $rows = $dbc->query
         (
            'SELECT COUNT(*) AS `count`
             FROM `' . mysql_real_escape_string($tableName) . '`'
         );

         if (count($rows) != 1)
         {
            throw new Exception('Unexpected row count.');
         }

         $rowCountByTableName[$tableName] = $rows[0]['count'];
      }

      return $rowCountByTableName;
   }

   /*
    * Return rows obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    */
   public static function getRowsFromTable
   (
      DatabaseConnection $dbc, $tableName, $whereDetails = array()
   )
   {
      assert('is_string($tableName)'  );
      assert('is_array($whereDetails)');

      $conditions = self::getSqlConditionsFromWhereDetails($whereDetails);

      $rows = $dbc->query
      (
         'SELECT *
          FROM `' . mysql_real_escape_string($tableName) . '`
          WHERE ' . implode("\nAND ", $conditions)
      );

      return $rows;
   }

   /*
    * Return a single field from each of the rows obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    */
   public static function getColFromTable
   (
      DatabaseConnection $dbc, $colName, $tableName, $whereDetails = array(), $boolCastToInt = false
   )
   {
      assert('is_string($colName  )'  );
      assert('is_string($tableName)'  );
      assert('is_array($whereDetails)');
      assert('is_bool($boolCastToInt)');

      $conditions = self::getSqlConditionsFromWhereDetails($whereDetails);

      $rows = $dbc->query
      (
         'SELECT `' . mysql_real_escape_string($colName  ) . '`
          FROM `'   . mysql_real_escape_string($tableName) . '`
          WHERE ' . implode("\nAND ", $conditions)
      );

      $values = array();
      foreach ($rows as $row)
      {
         $value    = $row[$colName];
         $values[] = ($boolCastToInt)? (int)$value: $value;
      }

      return $values;
   }

   /*
    * Return a single row obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    *
    * If the query returns more or less than one row, an exception is thrown.
    */
   public static function getRowFromTable(DatabaseConnection $dbc, $tableName, $whereDetails)
   {
      assert('is_string($tableName)'  );
      assert('is_array($whereDetails)');

      $rows = self::getRowsFromTable($dbc, $tableName, $whereDetails);

      switch (count($rows))
      {
       case 0:
         throw new Exception('Zero rows returned by query.  One row was expected.');
       case 1:
         return $rows[0];
       default:
         throw new Exception
         (
            'Multiple (' . count($rows) . ') rows returned by query.  One row was expected.'
         );
      }
   }

   /*
    * Return a single field obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    *
    * If the query returns more or less than one row, an exception is thrown.
    */
   public static function getFieldFromRowOfTable
   (
      DatabaseConnection $dbc, $fieldName, $tableName, $whereDetails
   )
   {
      assert('is_string($fieldName)'  );
      assert('is_string($tableName)'  );
      assert('is_array($whereDetails)');

      $conditions = self::getSqlConditionsFromWhereDetails($whereDetails);

      $rows = $dbc->query
      (
         'SELECT `' . mysql_real_escape_string($fieldName) . '`
          FROM `' . mysql_real_escape_string($tableName) . '`
          WHERE ' . implode("\nAND ", $conditions)
      );

      switch (count($rows))
      {
       case 0:
         throw new Exception('Zero rows returned by query.  One row was expected.');
       case 1:
         return $rows[0][$fieldName];
       default:
         throw new Exception
         (
            'Multiple (' . count($rows) . ') rows returned by query.  One row was expected.'
         );
      }
   }

   /*
    *
    */
   public static function getMaxFieldFromTable
   (
      DatabaseConnection $dbc, $fieldName, $tableName, $whereDetails = array()
   )
   {
      return self::getAggregateQueryResult
      (
         $dbc, $fieldName, $tableName, 'MAX', $whereDetails
      );
   }

   /*
    *
    */
   public static function getMinFieldFromTable
   (
      DatabaseConnection $dbc, $fieldName, $tableName, $whereDetails = array()
   )
   {
      return self::getAggregateQueryResult
      (
         $dbc, $fieldName, $tableName, 'MIN', $whereDetails
      );
   }

   // UPDATE queries. -------------------------------------------------------------------------//

   /*
    * Return rows obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    */
   public static function updateRowsInTable
   (
      DatabaseConnection $dbc, $tableName, $setDetails, $whereDetails
   )
   {
      assert('is_string($tableName)'  );
      assert('is_array($setDetails  )');
      assert('is_array($whereDetails)');

      $setDirectives = self::getSqlConditionsFromWhereDetails($setDetails  );
      $conditions    = self::getSqlConditionsFromWhereDetails($whereDetails);

      $rows = $dbc->query
      (
         'UPDATE `' . mysql_real_escape_string($tableName) . '`
          SET ' . implode(",\n", $setDirectives) . '
          WHERE ' . implode("\nAND ", $conditions)
      );

      return $dbc->getNAffectedRows();
   }

   // INSERT queries. -------------------------------------------------------------------------//

   /*
    *
    */
   public static function insertRowIntoTable(DatabaseConnection $dbc, $tableName, $row)
   {
      assert('is_string($tableName)');
      assert('is_array($row)'       );

      $escapedRow = array();
      foreach ($row as $key => $value)
      {
         // Only keys need be escaped here since values are escaped in class DatabaseConnection.
         $escapedRow[mysql_real_escape_string($key)] = $value;
      }

      $dbc->query
      (
         'INSERT INTO `' . mysql_real_escape_string($tableName) . '`
          (`' . implode('`, `', array_keys(         $escapedRow)      ) . '`)
          VALUES
          ('  . implode(', '  , array_fill(0, count($escapedRow), '?')) .  ')',
         array_values($escapedRow)
      );

      if ($dbc->getNAffectedRows() != 1)
      {
         throw new Exception('Unexpected nAffectedRows.');
      }

      return $dbc->getLastInsertId();
   }

   /*
    *
    */
   public static function getSqlConditionsFromWhereDetails($whereDetails)
   {
      assert('is_array($whereDetails)');

      $sqlConditions = array();

      foreach ($whereDetails as $colName => $value)
      {
         assert('is_string($colName)');

         $sqlConditions[] =
         (
            '`'  . mysql_real_escape_string($colName) . '`=' .
            '\'' . mysql_real_escape_string($value  ) . '\''
         );
      }

      if (count($sqlConditions) == 0)
      {
         $sqlConditions[] = '1';
      }

      return $sqlConditions;
   }

   /*
    * Return an aggregate result field obtained from a simple SELECT query
    * where WHERE conditions are of form:
    *   colname1=value1 AND colname2=value2 AND ...
    *
    * If the query returns more or less than one row, an exception is thrown.
    */
   public static function getAggregateQueryResult
   (
      DatabaseConnection $dbc, $fieldName, $tableName, $aggregateFunction, $whereDetails = array()
   )
   {
      assert('is_string($fieldName)'        );
      assert('is_string($tableName)'        );
      assert('is_string($aggregateFunction)');
      assert('is_array($whereDetails)'      );

      $conditions = self::getSqlConditionsFromWhereDetails($whereDetails);

      $rows = $dbc->query
      (
         "SELECT $aggregateFunction(`" . mysql_real_escape_string($fieldName) . '`) AS `result`
          FROM `' . mysql_real_escape_string($tableName) . '`
          WHERE ' . implode("\nAND ", $conditions)
      );

      if (count($rows) != 1)
      {
         throw new Exception('Expected 1 row in query result.  Received ' . count($rows) . '.');
      }

      return $rows[0]['result'];
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function getSqlSetDirectivesFromSetDetails($setDetails)
   {
      assert('is_array($setDetails)');

      $sqlSetDirectives = array();

      foreach ($setDetails as $colName => $value)
      {
         assert('is_string($colName)');
         assert('is_string($value  )');

         $sqlSetDirectives[] =
         (
            '`'  . mysql_real_escape_string($colName) . '`=' .
            '\'' . mysql_real_escape_string($value  ) . '\''
         );
      }

      return $sqlSetDirectives;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
