<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SqlTableRelationshipFinderNC1.php" ('NC1' for 'Naming Convention 1)
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
*          * Primary Keys
*            The primary key of each table is named `id`.
*
*          * One-to-One Links
*            For a given table, a column named `id<OtherTableName>` implies a one-to-one link
*            between that table and the table named `<otherTableName>` (with first letter in
*            lowercase).
*
*          * One-to-Many Links
*            A column named `id<TableName>` in another table implies a one-to-many link between
*            the table named `<tableName>` (with first letter in lowercase), and the other table.
*
*          * Many-to-Many Links
*            A table named `link_<tableName1>_<tableName2>` implies a many-to-many link between
*            tables `<tableName1>` and `<tableName2>`.  The `link_<tableName1>_<tableName2>`
*            table should have a column named `id<TableName1>` and a column named `id<TableName2>`.
*
* Author: Tom McDonnell 2010-06-26.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../../database/DatabaseManager.php';
require_once dirname(__FILE__) . '/../../../utils/Utils_database.php';
require_once dirname(__FILE__) . '/../SqlTableRelationshipsFinder.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 * 'NC1' for 'Naming Convention 1'.
 */
class SqlTableRelationshipsFinderNcTom extends SqlTableRelationshipsFinder
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * Build an array defining the relationships between the given table and other tables within the
    * same database.
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
         if ($tableLinkCol == 'id' || substr($tableLinkCol, 0, 2) != 'id')
         {
            continue;
         }

         $linkedTableName    = substr($tableLinkCol, 2);
         $linkedTableName[0] = strtolower($linkedTableName[0]);

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
    * Return search info for tables having a column 'id<$this->tableName>'.
    */
   protected function getDirectLinksToTableByLinkedTableName()
   {
      $linkedTableLinkCol    = "id{$this->tableName}";
      $linkedTableLinkCol[2] = strtoupper($linkedTableLinkCol[2]);

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
      $linkTableLinkColA    = "id{$this->tableName}";
      $linkTableLinkColA[2] = strtoupper($linkTableLinkColA[2]);
      $linkTableNames       = $this->getLinkTableNames();
      $indirectLinksViaLinkTableByLinkTableName = array();

      foreach ($linkTableNames as $linkTableName)
      {
         $linkTableColumnHeadings = $this->columnHeadingsByTableName[$linkTableName];

         if (in_array($linkTableLinkColA, $linkTableColumnHeadings))
         {
            $linkTableLinkColB = $this->findOtherLinkColumnName($linkTableName, $linkTableLinkColA);
            $linkedTableName    = substr($linkTableLinkColB, 2);
            $linkedTableName[2] = strtolower($linkedTableLinkCol[2]);

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
    *
    */
   private function getLinkTableNames()
   {
      $linkTableNames = array();

      foreach ($this->columnHeadingsByTableName as $tableName => $columnHeadings)
      {
         if (substr($tableName, 0, 4) == 'link')
         {
            $linkTableNames[] = $tableName;
         }
      }

      return $linkTableNames;
   }
}

/*******************************************END*OF*FILE********************************************/
?>
