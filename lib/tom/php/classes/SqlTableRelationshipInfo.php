<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SqlTableRelationshipInfo.php"
*
* Project: General.
*
* Purpose: A class defining the relationships that exist between a given SQL table and other tables
*          within the same database.
*
* Author: Tom McDonnell 2010-06-16.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../database/DatabaseManager.php';
require_once dirname(__FILE__) . '/../utils/Utils_database.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class SqlTableRelationshipInfo
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * Build an array defining the relationships between the given table and other tables within the
    * same database.
    */
   public function __construct(DatabaseConnection $dbc, $tableName)
   {
      $this->dbc = $dbc;

      if (!Utils_database::tableExistsInDatabase($this->dbc, $tableName))
      {
         throw new Exception("Table `$tableName` not found.");
      }

      $this->relationshipsInfo = $this->getRelationshipInfoForTable($tableName);
   }

   /*
    *
    */
   public function getAsArray()
   {
      return $this->relationshipsInfo;
   }

   /*
    * G
    */
   public function getLinkedTableNameFromTableLinkCol($tableLinkCol)
   {
      $infoByTableLinkCol = $this->relationshipsInfo['directLinksFromTableByTableLinkCol'];

      if (!array_key_exists($tableLinkCol, $infoByTableLinkCol))
      {
         throw new Exception("No linked table found for table link column name '$tableLinkCol'.");
      }

      return $infoByTableLinkCol[$tableLinkCol]['linkedTableName'];
   }

   /*
    *
    */
   public function getLinkedTableNameFromLinkTableName($linkTableName)
   {
      $infoByLinkTableName = $this->relationshipsInfo['indirectLinksViaLinkTableByLinkTableName'];

      if (!array_key_exists($linkTableName, $infoByLinkTableName))
      {
         throw new Exception("No linked table found for link table name '$linkTableName'.");
      }

      return $infoByLinkTableName[$linkTableName]['linkedTableName'];
   }

   /*
    *
    */
   public function getTableLinkColFromLinkTableName($linkTableName)
   {
      $infoByLinkTableName = $this->relationshipsInfo['indirectLinksViaLinkTableByLinkTableName'];

      if (!array_key_exists($linkTableName, $infoByLinkTableName))
      {
         throw new Exception("No linked table found for link table name '$linkTableName'.");
      }

      return $infoByLinkTableName[$linkTableName]['tableLinkCol'];
   }

   /*
    *
    */
   public function getLinkedTableLinkColFromLinkTableName($linkTableName)
   {
      $infoByLinkTableName = $this->relationshipsInfo['indirectLinksViaLinkTableByLinkTableName'];

      if (!array_key_exists($linkTableName, $infoByLinkTableName))
      {
         throw new Exception("No linked table found for link table name '$linkTableName'.");
      }

      return $infoByLinkTableName[$linkTableName]['linkedTableLinkCol'];
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function getRelationshipInfoForTable($tableName)
   {
      if (!array_key_exists($tableName, $this->relationshipInfoByTableName))
      {
         // TODO: Write the code that determines the relationships for a given table.
         //throw new Exception('Code not yet written.  Finish the class.');
         return array
         (
            'directLinksFromTableByTableLinkCol'       => array(),
            'directLinksToTableByLinkedTableName'      => array(),
            'indirectLinksViaLinkTableByLinkTableName' => array()
         );
      }

      return $this->relationshipInfoByTableName[$tableName];
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private $dbc = null;

   // TODO
   // ----
   // Remove this hard-coded array, and replace with code to generate from the SQL information
   // schema.
   private $relationshipInfoByTableName = array
   (
      'cases' => array
      (
         'directLinksFromTableByTableLinkCol' => array
         (
            'modified_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'assigned_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'account_id' => array
            (
               'linkedTableName'    => 'accounts',
               'linkedTableLinkCol' => 'id'
            )
         ),
         'directLinksToTableByLinkedTableName' => array
         (
            // NOTE
            // ----
            // Array structure will be
            //  * array('tableLinkCol' => <string>, 'linkedTableLinkedCol' => <string>)
            // Table link col will typically be 'id'.
         ),
         'indirectLinksViaLinkTableByLinkTableName' => array
         (
            'ev101_event_cases_c' => array
            (
               'linkedTableLinkCol' => 'id'                       ,
               'linkedTableName'    => 'ev101_event'              ,
               'linkTableLinkColA'  => 'ev101_even6843escases_idb',
               'linkTableLinkColB'  => 'ev101_evenfa9d1_event_ida',
               'tableLinkCol'       => 'id'
            )
         )
      ),
      'contacts' => array
      (
         'directLinksFromTableByTableLinkCol' => array
         (
            'modified_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'assigned_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'reports_to_id' => array
            (
               'linkedTableName'    => 'contacts',
               'linkedTableLinkCol' => 'id'
            ),
            'campaign_id' => array
            (
               'linkedTableName'    => 'campaigns',
               'linkedTableLinkCol' => 'id'
            )
         ),
         'directLinksToTableByLinkedTableName' => array
         (
            'leads' => array
            (
               'tableLinkCol'         => 'id',
               'linkedTableLinkedCol' => 'id_contact'
            ),
            'notes' => array
            (
               'tableLinkCol'         => 'id',
               'linkedTableLinkedCol' => 'id_contact'
            ),
            'tasks' => array
            (
               'tableLinkCol'         => 'id',
               'linkedTableLinkedCol' => 'id_contact'
            )
         ),
         'indirectLinksViaLinkTableByLinkTableName' => array
         (
            // Campaigns??
            'accounts_contacts' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'accounts'  ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'account_id',
               'tableLinkCol'       => 'id'
            ),
            'calls_contacts' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'calls'     ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'call_id'   ,
               'tableLinkCol'       => 'id'
            ),
            'contacts_bugs' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'bugs'      ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'call_id'   ,
               'tableLinkCol'       => 'id'
            ),
            'contacts_cases' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'cases'     ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'case_id'   ,
               'tableLinkCol'       => 'id'
            ),
            'meetings_contacts' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'meetings'  ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'meeting_id',
               'tableLinkCol'       => 'id'
            ),
            'opportunities_contacts' => array
            (
               'linkedTableLinkCol' => 'id'            ,
               'linkedTableName'    => 'opportunities' ,
               'linkTableLinkColA'  => 'contact_id'    ,
               'linkTableLinkColB'  => 'opportunity_id',
               'tableLinkCol'       => 'id'
            ),
            'projects_contacts' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'projects'  ,
               'linkTableLinkColA'  => 'contact_id',
               'linkTableLinkColB'  => 'project_id',
               'tableLinkCol'       => 'id'
            ),
            'ev101_event_contacts_c' => array
            (
               'linkedTableLinkCol' => 'id'                       ,
               'linkedTableName'    => 'ev101_event'              ,
               'linkTableLinkColA'  => 'ev101_even4222ontacts_idb',
               'linkTableLinkColB'  => 'ev101_even58eb1_event_ida',
               'tableLinkCol'       => 'id'
            )
         )
      ),
      'ev101_event' => array
      (
         'directLinksFromTableByTableLinkCol' => array
         (
            'modified_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'assigned_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'contact_id_c' => array
            (
               'linkedTableName'    => 'contacts',
               'linkedTableLinkCol' => 'id'
            )
         ),
         'directLinksToTableByLinkedTableName' => array
         (
         ),
         'indirectLinksViaLinkTableByLinkTableName' => array
         (
            'ev101_event_contacts_c' => array
            (
               'linkedTableLinkCol' => 'id'                       ,
               'linkedTableName'    => 'contacts'                 ,
               'linkTableLinkColA'  => 'ev101_even58eb1_event_ida',
               'linkTableLinkColB'  => 'ev101_even4222ontacts_idb',
               'tableLinkCol'       => 'id'
            ),
            'ev101_event_documents_c' => array
            (
               'linkedTableLinkCol' => 'id'                       ,
               'linkedTableName'    => 'documents'                ,
               'linkTableLinkColA'  => 'ev101_evenf0351_event_ida',
               'linkTableLinkColB'  => 'ev101_even3fcecuments_idb',
               'tableLinkCol'       => 'id'
            ),
            'ev101_event_cases_c' => array
            (
               'linkedTableLinkCol' => 'id'                       ,
               'linkedTableName'    => 'cases'                    ,
               'linkTableLinkColA'  => 'ev101_evenfa9d1_event_ida',
               'linkTableLinkColB'  => 'ev101_even6843escases_idb',
               'tableLinkCol'       => 'id'
            )
         )
      ),
      'accounts' => array
      (
         'directLinksFromTableByTableLinkCol' => array
         (
            'modified_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'assigned_user_id' => array
            (
               'linkedTableName'    => 'users',
               'linkedTableLinkCol' => 'id'
            ),
            'parent_id' => array
            (
               'linkedTableName'    => 'accounts',
               'linkedTableLinkCol' => 'id'
            ),
            'campaign_id' => array
            (
               'linkedTableName'    => 'campaigns',
               'linkedTableLinkCol' => 'id'
            )
         ),
         'directLinksToTableByLinkedTableName' => array
         (
            'cases' => array
            (
               'tableLinkCol'         => 'id',
               'linkedTableLinkedCol' => 'account_id'
            ),
            'leads' => array
            (
               'tableLinkCol'         => 'id',
               'linkedTableLinkedCol' => 'lead_id'
            )
         ),
         'indirectLinksViaLinkTableByLinkTableName' => array
         (
            'accounts_cases' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'cases'     ,
               'linkTableLinkColA'  => 'account_id',
               'linkTableLinkColB'  => 'case_id'   ,
               'tableLinkCol'       => 'id'
            ),
            'accounts_contacts' => array
            (
               'linkedTableLinkCol' => 'id'        ,
               'linkedTableName'    => 'contacts'  ,
               'linkTableLinkColA'  => 'account_id',
               'linkTableLinkColB'  => 'contact_id',
               'tableLinkCol'       => 'id'
            )
         )
      )
   );
}

/*******************************************END*OF*FILE********************************************/
?>
