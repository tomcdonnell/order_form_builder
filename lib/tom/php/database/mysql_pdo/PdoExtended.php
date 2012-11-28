<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "PdoExtended.php"
*
* Project: Database Utilities.
*
* Purpose: Class for using MySQL in PHP using PDO more conveniently.
*
* Author: Tom McDonnell 2012.
*
\**************************************************************************************************/

/*
 *
 */
class PdoExtended extends PDO
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * Usage:
    *    $pdoEx = new PdoExtended
    *    (
    *        'mysql:host=localhost;dbname=databasename', 'username', 'password'
    *    );
    */
   public function __construct($dsn, $username, $password, $driverOptions = null)
   {
      if ($driverOptions === null)
      {
         $driverOptions = array
         (
            PDO::ATTR_AUTOCOMMIT         => false            ,
            PDO::ATTR_ORACLE_NULLS       => PDO::NULL_NATURAL,
            PDO::ATTR_PERSISTENT         => true             ,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
         );
      }

      // Note Regarding Error Suppression
      // --------------------------------
      // The purpose of the error suppression '@' is to avoid a warning when the 'database server
      // has gone away'.  A new connection is reestablished in that case, and there is no need to
      // display the warning.
      @parent::__construct($dsn, $username, $password, $driverOptions);

      $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }

   /*
    *
    */
   public function quoteIdentifier($identifier)
   {
      $regex = '/^[A-Za-z0-9_ ]*$/';

      if (preg_match($regex, $identifier) == 0)
      {
         throw new Exception
         (
            // http://stackoverflow.com/questions/1542627/escaping-field-names-in-pdo-statements.
            "Identifier '$identifier' to be used in SQL query does not match regex '$regex'."
         );
      }

      return "`$identifier`";
   }

   /*
    *
    */
   public function rowExistsInTable($tableName, Array $whereValueByColumnName)
   {
      $whereConditions = array();

      foreach ($whereValueByColumnName as $columnName => $value)
      {
         $whereConditions[] = $this->quoteIdentifier($columnName) .
         (
             ($value === null)? ' IS NULL': '=' . $this->quote($value)
         );
      }

      $pdoStatement = $this->_prepareAndExecuteSqlQuery
      (
         'SELECT EXISTS
          (
             SELECT *
             FROM ' . $this->quoteIdentifier($tableName) . '
             WHERE ' . implode(' AND ', $whereConditions) . '
          ) AS `exists`',
         array()
      );

      $rows  = $pdoStatement->fetchAll();
      $nRows = count($rows);

      if (count($rows) != 1)
      {
         $this->_throwSqlQueryException
         (
            $sql, array(), "Unexpected number of rows ($nRows) returned by SQL query."
         );
      }

      return ($rows[0]['exists'] == '1');
   }

   /*
    * Usage:
    *
    * $pdoEx = new PdoExtended($dsn, $username, $password);
    * $rows  = $pdo->selectRows('SELECT id, name_first FROM user');
    * foreach ($rows as $row)
    * {
    *    echo $row['id'        ];
    *    echo $row['name_first'];
    * }
    */
   public function selectRows($sql, Array $params = array(), $pdoFetchConst = PDO::FETCH_ASSOC)
   {
      if (!in_array($pdoFetchConst, array(PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_OBJ)))
      {
         throw new Exception('Unexpected value for PDO::FETCH_... constant.');
      }

      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);

      return $pdoStatement->fetchAll($pdoFetchConst);
   }

   /*
    * Usage:
    *
    * $pdoEx = new PdoExtended($dsn, $username, $password);
    * $row   = $pdo->selectRow('SELECT * FROM user WHERE id=4');
    */
   public function selectRow($sql, Array $params = array())
   {
      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);
      $rows         = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

      if (count($rows) != 1)
      {
         // Justification for Throwing an Exception Here
         // --------------------------------------------
         // The most common use of this function is when the caller knows that a row having a
         // certain id exists because of referential integrity constraints.  In that case, if
         // zero or multiple rows are returned, then something serious has gone wrong and an
         // exception is appropriate.  In the less common case where the caller expects either
         // zero or one row to be returned, the caller would have to check the result anyway
         // to see whether zero or one row was returned.  Use $this->fetchAll() or
         // $this->selectRowOrNull() for the case where the row may not exist.
         $this->_throwSqlQueryException
         (
            $sql, $params, 'Expected one row from SQL query, zero or multiple rows returned.'
         );
      }

      return $rows[0];
   }

   /*
    * Usage:
    *
    * $pdoEx = new PdoExtended($dsn, $username, $password);
    * $row   = $pdo->selectRowOrNull('SELECT * FROM user WHERE id=4');
    *
    * if ($row === null)
    * {
    *    // Act.
    * }
    */
   public function selectRowOrNull($sql, Array $params = array())
   {
      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);
      $rows         = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

      switch (count($rows))
      {
       case 0:
         return null;
       case 1:
         return $rows[0];
       default:
         $this->_throwSqlQueryException
         (
            $sql, $params, 'Expected one or zero rows from SQL query, multiple rows returned.'
         );
      }
   }

   /*
    * Usage:
    *
    * $pdoEx = new PdoExtended($dsn, $username, $password);
    * $ids   = $pdo->selectColumn('SELECT id FROM user');
    * foreach ($ids as $id)
    * {
    *    echo $id;
    * }
    */
   public function selectColumn($sql, Array $params = array(), $boolConvertToInt = false)
   {
      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);
      $column       = array();

      while ($value = $pdoStatement->fetchColumn())
      {
         $column[] = ($boolConvertToInt)? (int)$value: $value;
      }

      return $column;
   }

   /*
    * Usage:
    *
    * $pdoEx         = new PdoExtended($dsn, $username, $password);
    * $nameFirstById = $pdo->selectIndexedColumn('SELECT id, name_first FROM user');
    * foreach ($nameFirstById as as $id => $nameFirst)
    * {
    *    echo $id;
    *    echo $nameFirst;
    * }
    */
   public function selectIndexedColumn($sql, Array $params = array(), $boolConvertToInt = false)
   {
      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);
      $valueByKey   = array();

      while ($row = $pdoStatement->fetch(PDO::FETCH_NUM))
      {
         if (count($row) != 2)
         {
            $this->_throwSqlQueryException
            (
               $sql, $params, "Each row is expected to contain exactly two elements."
            );
         }

         $valueByKey[$row[0]] = ($boolConvertToInt)? (int)$row[1]: $row[1];
      }

      return $valueByKey;
   }

   /*
    * Usage:
    *
    * $pdoEx     = new PdoExtended($dsn, $username, $password);
    * $nameFirst = $pdo->selectField('SELECT nameFirst FROM user WHERE id=4');
    */
   public function selectField($sql, Array $params = array(), $boolConvertToInt = false)
   {
      $pdoStatement = $this->_prepareAndExecuteSqlQuery($sql, $params);
      $field        = $pdoStatement->fetchColumn();

      if ($field === false)
      {
         $this->_throwSqlQueryException
         (
            $sql, $params, 'Expected one row from SQL query, zero rows returned.'
         );
      }

      if ($pdoStatement->fetchColumn())
      {
         $this->_throwSqlQueryException
         (
            $sql, $params, 'Expected one row from SQL query, multiple rows returned.'
         );
      }

      return $field;
   }

   /*
    * Usage:
    *
    * $pdoEx  = new PdoExtended($dsn, $username, $password);
    * $idUser = $pdo->insert('user', array('nameFirst' => 'Estella', 'nameLast' => 'Havisham'));
    *
    * To perform more complex inserts use $pdoEx->prepare() and $pdoEx->execute() manually.
    */
   public function insert($tableName, Array $valueByKey)
   {
      if (count($valueByKey) == 0)
      {
         throw new Exception('Empty $valueByKey array passed to $pdoEx->insert() function.');
      }

      $strings = array();

      foreach ($valueByKey as $key => $value)
      {
         $strings[] = "`$key`=?";
      }

      $sql          = "INSERT INTO `$tableName` SET\n" . implode(",\n", $strings);
      $pdoStatement = $this->prepare($sql);

      try
      {
         $success = $pdoStatement->execute(array_values($valueByKey));
      }
      catch (Exception $e)
      {
         // Catch eg. 'number of bound variables does not match' error and add more info to
         // exception.  Note that this error will only be caught if errors are converted to
         // exceptions.  See Utils_error::initErrorAndExceptionHandler().
         $this->_throwSqlQueryException($sql, $params, $e->getMessage());
      }

      if (!$success)
      {
         $this->_throwSqlQueryException($sql, $valueByKey, 'Insert query failed.');
      }

      return $this->lastInsertId();
   }

   /*
    * Usage:
    *
    * $pdoEx         = new PdoExtended($dsn, $username, $password);
    * $nRowsAffected = $pdo->update
    * (
    *    'user',                          // UPDATE `user`
    *    array('nameFirst' => 'Gargery'), // SET    `nameLast`='Gargery'
    *    array('nameLast'  => 'Biddy'  )  // WHERE  `nameFirst`='Biddy'
    * );
    *
    * To perform more complex updates use $pdoEx->prepare() and $pdoEx->execute() manually.
    */
   public function update($tableName, Array $updateValueByKey, Array $whereValueByKey = array())
   {
      if (count($updateValueByKey) == 0)
      {
         throw new Exception('Empty $valueByKey array passed to $pdoEx->insert() function.');
      }

      $strings    = array();
      $conditions = array();

      foreach ($updateValueByKey as $k => $v) {$strings[]    = "`$k`=" . $this->quote($v);}
      foreach ($whereValueByKey  as $k => $v) {$conditions[] = "`$k`=" . $this->quote($v);}

      $sql =
      (
         "UPDATE `$tableName` SET\n" . implode(",\n", $strings) .
         "\nWHERE " . implode(' AND ', $conditions)
      );

      // Function exec() is used here instead of execute because exec() returns the number
      // of rows affected while execute returns a boolean indicating success or failure.
      $nRowsAffected = $this->exec($sql);

      if ($nRowsAffected === false)
      {
         $this->_throwSqlQueryException($sql, array(), 'Update query failed.');
      }

      return $nRowsAffected;
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function _prepareAndExecuteSqlQuery($sql, Array $params)
   {
      $pdoStatement = $this->prepare($sql);

      if ($pdoStatement === false)
      {
         $this->_throwSqlQueryException($sql, $params, 'PDO->prepare() returned false.');
      }

      try
      {
         $pdoStatement->execute($params);
      }
      catch (Exception $e)
      {
         // Catch 'number of bound variables does not match' error and add more info to exception.
         // Note that this error will only be caught if errors are converted to exceptions.
         // See Utils_error::initErrorAndExceptionHandler().
         $this->_throwSqlQueryException($sql, $params, $e->getMessage());
      }

      return $pdoStatement;
   }

   /*
    *
    */
   public function _throwSqlQueryException($sql, Array $params, $message)
   {
      throw new Exception
      (
         "$message\nsql:\n" . var_export($sql, true) . "\nparams:\n" . var_export($params, true)
      );
   }
}

/*******************************************END*OF*FILE********************************************/
?>
