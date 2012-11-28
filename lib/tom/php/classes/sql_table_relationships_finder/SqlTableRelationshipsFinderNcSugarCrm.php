<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SqlTableRelationshipFinderNC2.php" ('NC2' for 'Naming Convention 2)
*
* Project: SQL Table Relationships Finder.
*
* Purpose: A class for finding the relationships that exist between a given SQL table and other
*          tables within the same database.  This class assumes the following table naming
*          convention has been used.
*
*          Naming Convention
*          -----------------
*
*          NOTE: This naming convention was designed to match that used by SugarCRM.
*
*          * Primary Keys
*            The primary key of each table is named `id`.
*
*          * One-to-One Links
*            For a given table, a column named `<otherTableName>_id` implies a one-to-one link
*            between that table and the table named `<otherTableName>`.
*
*          * One-to-Many Links
*            A column named `<TableName>_id` in another table implies a one-to-many link between
*            the table named `<tableName>`, and the other table.
*
*          * Many-to-Many Links
*            A table named `<tableName1>_<tableName2>` implies a many-to-many link between tables
*            `<tableName1>` and `<tableName2>`.  The `link_<tableName1>_<tableName2>` table should
*            have a column named `<tableName1>_id` and a column named `<tableName2>_id`.
*
* Author: Tom McDonnell 2010-06-26.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../database/DatabaseManager.php';
require_once dirname(__FILE__) . '/../../utils/Utils_database.php';
require_once dirname(__FILE__) . '/SqlTableRelationshipsFinder.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 * 'NC1' for 'Naming Convention 1'.
 */
class SqlTableRelationshipsFinderNcSugarCrm extends SqlTableRelationshipsFinder
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct(DatabaseConnection $dbc, $tableName)
   {
      parent::__construct($dbc, $tableName);
   }

   // Protected functions. //////////////////////////////////////////////////////////////////////

   /*
    * Return link info for tables linked to by a column of $this->tableName.
    */
   protected function getDirectLinksFromTableByTableLinkCol()
   {
      $directLinksFromTableByTableLinkCol = array();

      foreach ($this->columnHeadings as $tableLinkCol)
      {
         if (substr($tableLinkCol, strlen($tableLinkCol) - 3) != '_id')
         {
            continue;
         }

         $linkedTableName = substr($tableLinkCol, 0, strlen($tableLinkCol) - 3) . 's';

         if (array_key_exists($linkedTableName, $this->columnHeadingsByTableName))
         {
            $linkedTableColumnHeadings = $this->columnHeadingsByTableName[$linkedTableName];

            if (in_array('id', $linkedTableColumnHeadings))
            {
               $directLinksFromTableByTableLinkCol[$tableLinkCol] = array
               (
                  'linkedTableName'    => $linkedTableName,
                  'linkedTableLinkCol' => 'id'
               );
            }
         }
      }

      return $directLinksFromTableByTableLinkCol;
   }

   /*
    * Return search info for tables having a column '<$this->tableNameSingular>_id'.
    */
   protected function getDirectLinksToTableByLinkedTableName()
   {
      $tableNameSingular                   = $this->getTableNameSingular($this->tableName);
      $linkedTableLinkCol                  = "{$tableNameSingular}_id";
      $directLinksToTableByLinkedTableName = array();

      foreach ($this->columnHeadingsByTableName as $linkedTableName => $linkedTableColumnHeadings)
      {
         if (in_array($linkedTableLinkCol, $linkedTableColumnHeadings))
         {
            $directLinksToTableByLinkedTableName[$linkedTableName] = array
            (
               'tableLinkCol'       => 'id',
               'linkedTableLinkCol' => $linkedTableLinkCol
            );
         }
      }

      return $directLinksToTableByLinkedTableName;
   }

   /*
    * Return search info for tables linked via link tables to $this->table.
    */
   protected function getIndirectLinksViaLinkTableByLinkTableName()
   {
      $linkTableLinkColA = "{$this->tableName}_id";
      $linkTableNames    = $this->getLinkTableNames();

      $indirectLinksViaLinkTableByLinkTableName = array();

      foreach ($linkTableNames as $linkTableName)
      {
         $linkTableColumnHeadings = $this->columnHeadingsByTableName[$linkTableName];

         if (in_array($linkTableLinkColA, $linkTableColumnHeadings))
         {
            $linkTableLinkColB = $this->findOtherLinkColumnName($linkTableName, $linkTableLinkColA);
            $linkedTableName   = substr($linkTableLinkColB, 0, strlen($linkTableLinkColB) - 3);

            $indirectLinksViaLinkTableByLinkTableName[$linkTableName] = array
            (
               'linkedTableLinkCol' => 'id'              ,
               'linkedTableName'    => $linkedTableName  ,
               'linkTableLinkColA'  => $linkTableLinkColA,
               'linkTableLinkColB'  => $linkTableLinkColB,
               'tableLinkCol'       => 'id'
            );
         }
      }

      return $indirectLinksViaLinkTableByLinkTableName;
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    * Get singular version of table name by removing trailing 's' if present.
    */
   private function getTableNameSingular($tableName)
   {
      $strlen = strlen($tableName);

      return
      (
         (substr($tableName, $strlen - 1) == 's')? substr($tableName, 0, $strlen - 1): $tableName
      );
   }

   /*
    *
    */
   private function getLinkTableNames()
   {
      $tableNames       = array_keys($this->columnHeadingsByTableName);
      $tableNamesAsKeys = array_fill_keys($tableNames, null);
      $linkTableNames   = array();

      foreach ($tableNames as $tableName)
      {
         $offset = 0;
         $strlen = strlen($tableName);

         while (($strpos = strpos($tableName, '_', $offset)))
         {
            $offset            = $strpos + 1;
            $tableCandidateOne = substr($tableName, 0, $strpos );
            $tableCandidateTwo = substr($tableName, $strpos + 1);

            if
            (
               array_key_exists($tableCandidateOne, $tableNamesAsKeys) &&
               array_key_exists($tableCandidateTwo, $tableNamesAsKeys)
            )
            {
               $linkTableNames[] = $tableName;
            }
         }
      }

      return $linkTableNames;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
