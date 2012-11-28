<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "DatabaseManager.php"
*
* Project: Common.
*
* Purpose: Class for using MySQL in PHP more conveniently.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/DatabaseConnection.php';
require_once dirname(__FILE__) . '/../utils/Utils_validator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class DatabaseManager
{
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   // Static functions. /////////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   /*
    *
    */
   public static function get($name)
   {
      if (array_key_exists($name, DatabaseManager::$connections))
      {
         return DatabaseManager::$connections[$name];
      }
      else
      {
         throw new Exception('Attempted to get non-existent connection "' . $name . '".');
      }
   }

   // Other functions. ------------------------------------------------------------------------//

   /*
    *
    */
   public static function addMany($connectionsDetails)
   {
      foreach ($connectionsDetails as $connectionDetails)
      {
         self::add($connectionDetails);
      }
   }

   /*
    *
    */
   public static function add($connectionDetails)
   {
      Utils_validator::checkArray
      (
         $connectionDetails, array
         (
            'name'     => 'string',
            'host'     => 'string',
            'user'     => 'string',
            'password' => 'string',
            'database' => 'string'
         )
      );
      extract($connectionDetails);

      if (!array_key_exists($name, DatabaseManager::$connections))
      {
         DatabaseManager::$connections[$name] =
         (
            new DatabaseConnection($host, $user, $password, $database)
         );
      }
      else
      {
         throw new Exception('Attempted to overwrite existing connection "' . $name . '".');
      }
   }

   /*
    * 
    */
   public static function remove($name)
   {
      if (array_key_exists($name, DatabaseManager::$connections))
      {
         @mysql_close(DatabaseManager::$connections[$name]);

         unset(DatabaseManager::$connections[$name]);
      }
      else
      {
         throw new Exception('Attempted to remove non-existent connection "' . $name . '".');
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private static $connections = array();
}

/*******************************************END*OF*FILE********************************************/
?>
