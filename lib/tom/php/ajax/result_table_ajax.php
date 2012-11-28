<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "result_table_ajax.php"
*
* Project: General.
*
* Purpose: File to receive AJAX messages from the Javascript ResultTable object.
*
* See also: /lib/tom/js/gui_elements/other/ResultTable.js
*           /lib/tom/php/classes/ResultTableAjaxHelperAbstract.php
*
* Author: Tom McDonnell 2010-12-16.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../utils/Utils_validator.php';

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   Utils_validator::checkArray
   (
      $_GET, array
      (
         // The name of a class descendent from ResultTableAjaxHelperAbstract.
         // (defined at /lib/tom/php/classes/ResultTableAjaxHelperAbstract.php)
         'className' => 'string',

         // The path to the file in which class 'className' is defined (including the filename).
         // If a relative path is given, it should be relative to the file from which the Javascript
         // file that specifies this file as a destination for AJAX requests is included.
         'classPathAndFilename' => 'string'
      ),
      array('startSession' => 'string')
   );
   extract($_GET);

   // Include the file that defines the class specified in the $_GET parameters.
   require_once $classPathAndFilename;

   // Start a session if required.
   if (array_key_exists('startSession', $_GET) && $_GET['startSession'] == '1')
   {
      session_start();
   }

   $msg = json_decode(file_get_contents('php://input'), true);

   Utils_validator::checkArray($msg, array('action' => 'string', 'params' => 'array'));
   extract($msg);

   eval("\$ajaxHelper = new $className();");

   switch ($action)
   {
    case 'getData':
      $returnArray = $ajaxHelper->getData($db, $params);
      break;

    case 'updateRow':
      $returnArray = $ajaxHelper->callCustomAjaxResponderFunction($db, '_updateRow', $params);
      break;

    case 'insertRow':
      $returnArray = $ajaxHelper->callCustomAjaxResponderFunction($db, '_insertRow', $params);
      break;

    default:
      $returnArray = $ajaxHelper->callCustomAjaxResponderFunction($db, $action, $params);
   }

   // NOTE: The 'action' parameter in the reply message is set to 'updateRow' for all replies.
   echo json_encode(array('action' => 'updateRow', 'success' => true, 'reply' => $returnArray));
}
catch (Exception $e)
{
   $errorMessage = $e->getMessage();

   // TODO
   // ----
   // This is a hack specific to the TAAS Admin project and does not belong in library code.
   // Find another way to accomplish this in a generic fashion.
   if (strpos($errorMessage, 'Integrity constraint violation: 1062 Duplicate entry') !== false)
   {
      $errorMessage = 'Another item already exists with the same name (it may be inactivated)';
   }

   // NOTE: The 'action' parameter in the reply message is set to 'updateRow' for all replies.
   echo json_encode(array('action' => 'updateRow', 'success' => false, 'reply' => $errorMessage));
}

/*******************************************END*OF*FILE********************************************/
?>
