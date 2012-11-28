<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_mysql.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to MySQL.
*
* Author: Tom McDonnell 2012-05-06.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_mysql
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
    * For table names and column names in MySQL queries.
    */
   public static function escapeAndQuoteIdentifier($identifierString)
   {
      return str_replace('`', '``', $identifierString);
   }
}

/*******************************************END*OF*FILE********************************************/
?>
