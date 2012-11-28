<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "DatabaseConnection.php"
*
* Project: Common.
*
* Purpose: Class for using MySQL in PHP more conveniently.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../utils/Utils_misc.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class DatabaseConnection
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct($hostname, $username, $password, $databaseName)
   {
      $this->hostname     = $hostname;
      $this->username     = $username;
      $this->password     = $password;
      $this->databaseName = $databaseName;

      $this->connect();
   }

   // Getters. --------------------------------------------------------------------------------//

   public function getNAffectedRows() {return mysql_affected_rows($this->connection);}
   public function getLastInsertId()  {return mysql_insert_id($this->connection)    ;}
   public function getDatabaseName()  {return $this->databaseName                   ;}

   // Other public functions. -----------------------------------------------------------------//

   /*
    * This function proved useful when an object containing a DatabaseConnection was saved to
    * $_SESSION, then retrieved by another script.  The other script needed to call this function
    * because the connection had been closed.
    */
   public function connect()
   {
      if (!$this->connection = mysql_connect($this->hostname, $this->username, $this->password))
      {
         throw new Exception($this->getErrorString());
      }
 
      $this->selectDatabase($this->databaseName);
   }

   /*
    *
    */
   public function selectDatabase($databaseName)
   {
      if (!mysql_select_db($databaseName, $this->connection))
      {
         $errStr = $this->getErrorString();
         mysql_close($this->connection);
         throw new Exception($errStr);
      }

      $this->databaseName = $databaseName;
   }

   /*
    *
    */
   public function query
   (
      $sqlString, $arguments = array(), $boolEchoQueryForDebug = false, $boolNumericalAssoc = false
   )
   {
      assert('is_string($sqlString)'          );
      assert('is_array($arguments)'           );
      assert('is_bool($boolEchoQueryForDebug)');
      assert('is_bool($boolNumericalAssoc   )');

      // For each argument...
      foreach ($arguments as $i => &$argument)
      {
         switch (gettype($argument))
         {
          case 'array':
            // Run mysql_real_escape() on all array elements,
            // And convert array into a comma-separated string, enclosed by brackets.
            foreach ($argument as $j => $element)
            {
               $argument[$j] = '"' . mysql_real_escape_string($element) . '"';
            }
            $argument = '(' . implode(',', $argument) . ')';
            break;

          case 'integer':
            $argument = mysql_real_escape_string($argument);
            break;

          case 'string':
            $argument = '"' . mysql_real_escape_string($argument) . '"';
            break;

          case 'NULL':
            // Note that some substrings involving NULL in the SQL query are replaced below.
            $argument = 'NULL';
            break;

          default:
            throw new Exception('Illegal type \'' . gettype($argument) . '\' used as argument.');
         }
      }

      // Insert $arguments into $sql string.
      $sqlSubstrings = explode('?', $sqlString);
      $sqlString     = $sqlSubstrings[0];
      if (count($sqlSubstrings) != count($arguments) + 1)
      {
         throw new Exception('Incorrect number of arguments for SQL string.');
      }
      for ($i = 0; $i < count($arguments); ++$i)
      {
         $sqlString .= $arguments[$i] . $sqlSubstrings[$i + 1];
      }

      // Replace substrings involving NULL.
      $sqlString = str_replace('=NULL' , ' IS NULL'    , $sqlString);
      $sqlString = str_replace('<>NULL', ' IS NOT NULL', $sqlString);

      // Echo query for debugging purposes if requested.
      if ($boolEchoQueryForDebug)
      {
         echo $sqlString, "\n\n";
      }

      // Run the query.
      $this->resultSet = mysql_query($sqlString, $this->connection);

      if ($this->resultSet === false)
      {
         throw new Exception($this->getErrorString() . "\nSQL: $sqlString");
      }

      return ($this->resultSet === true)? true: $this->loadResult($boolNumericalAssoc);
   }

   /*
    *
    */
   public function queryNumAssoc($sqlString, $arguments = array(), $boolEchoQueryForDebug = false)
   {
      return self::query($sqlString, $arguments, $boolEchoQueryForDebug, true);
   }

   /*
    *
    */
   public function rollbackTransaction()
   {
      Utils_misc::debugMsg('Attempting transaction rollback...');

      if (!mysql_query('ROLLBACK WORK'))
      {
         Utls_misc::errorMsg
         (
            "Could not rollback transaction.\n" .
            "MySQL error number : '", mysql_errno(), "'\n" .
            "MySQL error message: '", mysql_error(), "'\n"
         );
      }
      else
      {
         Utils_misc::debugMsg('Transaction rolled back OK.');
      }
   }
         
   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function loadResult($boolNumericalAssoc)
   {
      assert('is_bool($boolNumericalAssoc)');

      if (mysql_affected_rows($this->connection) == 0)
      {
         return array();
      }

      $array = array();
      switch ($boolNumericalAssoc)
      {
       case true:
         while ($row = mysql_fetch_row($this->resultSet)) {$array[] = (array)$row;}
         break;
       case false:
         while ($row = mysql_fetch_assoc($this->resultSet)) {$array[] = (array)$row;}
         break;
      }

      mysql_free_result($this->resultSet);

      return $array;
   }

   /*
    *
    */
   private function getErrorString()
   {
      return
      (
         'MySQL error.' . "\n" .
         'Errno: ' . mysql_errno($this->connection) . "\n" .
         'Error: ' . mysql_error($this->connection) . "\n"
      );
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private $connection = '';
   private $database   = null;
}

/*******************************************END*OF*FILE********************************************/
?>
