<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SqlTableRelationshipFinder.php"
*
* Project: SQL Table Relationships Finder.
*
* Purpose: Abstract class to be extended for SqlTableRelationshipFinder<X> classes.
*
* Author: Tom McDonnell 2010-06-26.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../database/DatabaseManager.php';
require_once dirname(__FILE__) . '/../../utils/Utils_database.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
abstract class SqlTableRelationshipsFinder
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * Build an array defining the relationships between the given table and other tables within the
    * same database.
    */
   public function __construct(DatabaseConnection $dbc, $tableName)
   {
      if (!Utils_database::tableExistsInDatabase($dbc, $tableName))
      {
         throw new Exception("Table `$tableName` not found.");
      }

      $dbName = $dbc->getDatabaseName();

      $this->dbc                       = $dbc;
      $this->tableName                 = $tableName;
      $this->columnHeadingsByTableName = Utils_dbSchema::getColumnHeadingsByTableName($dbName);
      $this->columnHeadings            = $this->columnHeadingsByTableName[$tableName];
      $this->relationshipsInfo         = $this->getRelationshipsInfo();
   }

   /*
    *
    */
   public function getAsArray()
   {
      return $this->relationshipsInfo;
   }

   /*
    *
    */
   public function getNLinks()
   {
      $nLinks = 0;

      foreach ($this->relationshipsInfo as $linkType => $relationshipInfo)
      {
         $nLinks += count($relationshipInfo);
      }

      return $nLinks;
   }

   /*
    * Eg. For 'directLinksFromTableByTableLinkCol', desire 'tableLinkCol', have 'linkedTableName'.
    *
    *     $tableLinkCol = getMissingLink
    *     (
    *        'directLinksFromTableByTableLinkCol',
    *        'tableLinkCol'                      ,
    *        'linkedTableName'                   ,
    *        $linkedTableName
    *     );
    */
   public function getMissingLink($linkType, $desiredLinkName, $suppliedLinkName, $suppliedLink)
   {
      $keyName = Utils_misc::switchAssign
      (
         $linkType, array
         (
            'directLinksFromTableByTableLinkCol'       => 'tableLinkCol'   ,
            'directLinksToTableByLinkedTableName'      => 'linkedTableName',
            'indirectLinksViaLinkTableByLinkTableName' => 'linkTableName'
         )
      );

      $relationshipInfo      = $this->relationshipsInfo[$linkType];
      $suppliedLinkNameIsKey = ($suppliedLinkName == $keyName)? '1': '0';
      $desiredLinkNameIsKey  = ($desiredLinkName  == $keyName)? '1': '0';

      switch ("$suppliedLinkNameIsKey-$desiredLinkNameIsKey")
      {
       case '0-0':
         foreach ($relationshipInfo as $tableLinkCol => $linkInfo)
         {
            if ($linkInfo[$suppliedLinkName] == $suppliedLink)
            {
               $missingLink = $linkInfo[$desiredLinkName];
               break (2);
            }
         }
         throw new Exception('Missing link not found.');
         break;
       case '0-1':
         foreach ($relationshipInfo as $tableLinkCol => $linkInfo)
         {
            if ($linkInfo[$suppliedLinkName] == $suppliedLink)
            {
               $missingLink = $tableLinkCol;
               break (2);
            }
         }
         throw new Exception('Missing link not found.');
         break;
       case '1-0':
         $missingLink = $relationshipInfo[$suppliedLink][$desiredLinkName];
         break;
       case '1-1':
         throw new Exception();
      }

      return $missingLink;
   }

   /*
    * Example of use:
    *
    *   $links = $sqlRelationshipsFinderObj->getMissingLinks
    *   (
    *      'directLinksFromTableByTableLinkCol', 'tableLinkCol', array
    *      (
    *         'tableLinkCol'       => $tableLinkCol,
    *         'linkedTableName'    => null         ,
    *         'linkedTableLinkCol' => null
    *      )
    *   );
    *
    *   Return value is the $links array supplied, with null values replaced with desired values.
    */
   public function getMissingLinks($linkType, $suppliedLinkName, $links)
   {
      if (!array_key_exists($suppliedLinkName, $links) || $links[$suppliedLinkName] === null)
      {
         throw new Exception('This function has been used incorrectly.');
      }

      foreach ($links as $linkName => &$linkValue)
      {
         if ($linkValue !== null)
         {
            continue;
         }

         $linkValue = $this->getMissingLink
         (
            $linkType, $linkName, $suppliedLinkName, $links[$suppliedLinkName]
         );
      }

      return $links;
   }

   // Protected functions. //////////////////////////////////////////////////////////////////////

   /*
    * For one-to-one links.  Return an array in the following format.
    *
    * array
    * (
    *    <string tableLinkCol> => array
    *    (
    *       'linkedTableName'    => <string>,
    *       'linkedTableLinkCol' => <string>
    *    ),
    *    ...
    * );
    */
   abstract protected function getDirectLinksFromTableByTableLinkCol();

   /*
    * For one-to-many links.  Return an array in the following format.
    *
    * array
    * (
    *    <string linkedTableName> => array
    *    (
    *       'tableLinkCol'       => <string>,
    *       'linkedTableLinkCol' => <string>
    *    ),
    *    ...
    * );
    */
   abstract protected function getDirectLinksToTableByLinkedTableName();

   /*
    * For many-to-many links.  Return an array in the following format.
    *
    * array
    * (
    *    <string linkTableName> => array
    *    (
    *       'linkedTableLinkCol' => <string>,
    *       'linkedTableName'    => <string>,
    *       'linkTableLinkColA'  => <string>,
    *       'linkTableLinkColB'  => <string>,
    *       'tableLinkCol'       => <string>
    *    ),
    *    ...
    * );
    */
   abstract protected function getIndirectLinksViaLinkTableByLinkTableName();

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function getRelationshipsInfo()
   {
      return array
      (
         'directLinksFromTableByTableLinkCol' =>
         (
            $this->getDirectLinksFromTableByTableLinkCol()
         ),
         'directLinksToTableByLinkedTableName' =>
         (
            $this->getDirectLinksToTableByLinkedTableName()
         ),
         'indirectLinksViaLinkTableByLinkTableName' =>
         (
            $this->getIndirectLinksViaLinkTableByLinkTableName()
         )
      );
   }

   // Protected variables. //////////////////////////////////////////////////////////////////////

   protected $dbc                       = null;
   protected $tableName                 = null;
   protected $columnHeadings            = null;
   protected $columnHeadingsByTableName = null;
   protected $relationshipsInfo         = null;
}

/*******************************************END*OF*FILE********************************************/
?>
