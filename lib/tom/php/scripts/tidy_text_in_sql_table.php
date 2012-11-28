<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "tidy_text_in_sql_table.php"
*
* Project: Scripts.
*
* Purpose: Trim all fields of a given SQL table in a given SQL database.
*
* Author: Tom McDonnell 2010-05-06.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../../common/php/utils/Utils_database.php';

// Global variables. ///////////////////////////////////////////////////////////////////////////////

$TABLE_NAME = '20100506_farm_services';

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   DatabaseManager::addMany
   (
      array
      (
         array
         (
            'name'     => 'sugar_stakeholders',
            'host'     => 'localhost'         ,
            'user'     => 'root'              ,
            'password' => 'igaiasma'          ,
            'database' => 'sugar_stakeholders'
         )
      )
   );

   $dbc = DatabaseManager::get('sugar_stakeholders');

   echo 'Getting ids from table...';
   $ids = Utils_database::getColFromTable($dbc, 'id', $TABLE_NAME);
   echo 'done.  Got ', count($ids), " ids.\n";

   echo 'Trimming all fields...';
   $nRowsUpdated = 0;
   $i            = 0;
   foreach ($ids as $id)
   {
      if (++$i % 100 == 0) {echo '.';}

      $row = Utils_database::getRowFromTable($dbc, $TABLE_NAME, array('id' => $id));

      foreach ($row as $key => $value)
      {
         $value     = str_replace("\t", ' ', $value);
         $value     = str_replace("\n", ' ', $value);
         $value     = removeNonAlphaNumPunctCharsLeavingSpaces($value, $key);
         $value     = trim($value);
         $row[$key] = $value;
      }

      $nRowsUpdated +=
      (
         Utils_database::updateRowsInTable($dbc, $TABLE_NAME, $row, array('id' => $id))
      );
   }
   echo "done.\n$nRowsUpdated rows were updated.\n";

}
catch (Exception $e)
{
   echo $e->getMessage();
}

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function removeNonAlphaNumPunctCharsLeavingSpaces($value, $key)
{
   $newValue = '';

   for ($i = 0; $i < strlen($value); ++$i)
   {
      $char = $value[$i];

      if (ctype_alnum($char) || ctype_punct($char) || $char == ' ')
      {
         $newValue .= $char;
      }
   }

   return $newValue;
}

/*******************************************END*OF*FILE********************************************/
?>
