<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_sqlRelationships.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to relationships between SQL tables.
*
* Author: Tom McDonnell 2010-07-03.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../database/DatabaseConnection.php';

foreach
(
   glob
   (
      dirname(__FILE__) . '/../classes/sql_table_relationships_finder/specific_naming_conventions/*'
   )
   as $file
)
{
   require_once $file;
}

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_sqlRelationships
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
   public static function getInfoObjForTable
   (
      DatabaseConnection $dbc, $tableName, $namingConventionName
   )
   {
      $className = "SqlTableRelationshipsFinderNc$namingConventionName";

      if (!ctype_alnum($className))
      {
         throw new Exception
         (
            'Illegal character found in naming convention name.' .
            '  Will not eval string containing non-alphanumeric characters.'
         );
      }

      if (!class_exists($className))
      {
         throw new Exception("Unknown class name '$className'.");
      }

      return eval("return new $className(\$dbc, \$tableName);");
   }
}

/*******************************************END*OF*FILE********************************************/
?>
