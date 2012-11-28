<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/database.php';
require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_validator.php';

try
{
   Utils_validator::checkArray($_POST, array('action' => 'string', 'params' => 'array'));
   extract($_POST);

   switch ($action)
   {
    case 'getContactDetailsFromSoeid':
      Utils_validator::checkArray($params, array('soeid' => 'string'));
      $returnArray = getContactDetailsFromSoeid($pdoEx, $params['soeid']);
      break;

    default:
      throw new Exception("Unknown action '$action'.");
   }

   echo json_encode(array('action' => $action, 'success' => true, 'reply' => $returnArray));
}
catch (Exception $e)
{
   echo json_encode(array('action' => $action, 'success' => false, 'reply' => $e->getMessage()));
}

/*
 *
 */
function getContactDetailsFromSoeid(PdoExtended $pdoEx, $soeid)
{
   $rows = $pdoEx->selectRows
   (
      'SELECT
       division,
       firstName AS firstName,
       lastName AS lastName,
       soeid,
       supervisorName AS lineManager
       FROM user
       WHERE soeid=?',
      array($soeid)
   );

   switch (count($rows))
   {
    case 0 : throw new Exception("No user found matching SOE id '$soeid'.");
    case 1 : return $rows[0];
    default: throw new Exception("Multiple users found matching SOE id '$soeid'.");
   }
}
?>
