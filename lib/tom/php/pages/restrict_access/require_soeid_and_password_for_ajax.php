<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "require_soeid_and_password_for_ajax.php"
*
* Project: Security.
*
* Purpose: Include this file at the top of any ajax script (before any other 'include' or 'require'
*          lines, and before any session directives) to restrict access to that ajax script to
*          users with an SOE id and matching password.
*
*          See comments in file 'require_soeid_and_password_common.php' for further details.
*
* Author: Tom McDonnell 2011-02-15.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/require_soeid_and_password_common.php';
require_once dirname(__FILE__) . '/classes/LoginAndRejectionPageGenerator.php';
require_once dirname(__FILE__) . '/classes/WhitelistBlacklistAccessor.php';
require_once dirname(__FILE__) . '/../../utils/Utils_security.php';

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   list($whitelist, $blacklist) = WhitelistBlacklistAccessor::getBothLists($db);

   $previouslyAuthenticated = array_key_exists
   (
      'authenticatedSoeid', $_SESSION['soeAuthentication']
   );

   if (!$previouslyAuthenticated)
   {
      echo json_encode
      (
         array
         (
            // NOTE: The client must know how to deal with this reply.
            'action'  => 'checkLogin',
            'success' => false       ,
            'reply'   => 'Your session has expired.  Refresh the page to log back in.'
         )
      );

      exit(0);
   }

   $soeid = dealWithCasePreviouslyAuthenticated
   (
      $whitelist, $blacklist, $_SESSION['soeAuthentication']['authenticatedSoeid']
   );

   // If execution reaches this point, access is granted. -------------------------------------//

   $_SESSION['soeAuthentication']['authenticatedSoeid'   ] = $soeid;
   $_SESSION['soeAuthentication']['sessionCreateTimeUnix'] = time();

   // Close the session so as to avoid the 'Session already started' warning that would otherwise
   // be displayed if the restricted page starts its own session.  The next call to session_start()
   // (before termination of this script plus the page that includes it) will just restart the same
   // session as is closed here.
   session_write_close();
}
catch (Exception $e)
{
   echo $e->getMessage();

   // IMPORTANT
   // ---------
   // This line must remain to ensure that an exception generated
   // above does not result in the security checks being skipped.
   exit(0);
}

/*******************************************END*OF*FILE********************************************/
?>
