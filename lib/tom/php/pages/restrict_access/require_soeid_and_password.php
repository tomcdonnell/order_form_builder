<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "require_soeid_and_password.php"
*
* Project: Security.
*
* Purpose: Include this file at the top of any web page (before any other 'include' or 'require'
*          lines, and before any session directives) to restrict access to that web page to users
*          with an SOE id and matching password.
*
*          See comments in file 'require_soeid_and_password_common.php' for further details.
*
* Author: Tom McDonnell 2010-06-23.
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

   $previouslyAuthenticated =
   (
      array_key_exists('authenticatedSoeid', $_SESSION['soeAuthentication'])
   )? '1': '0';

   $detailsSuppliedViaPost =
   (
      array_key_exists('soeid'   , $_POST) &&
      array_key_exists('password', $_POST)
   )? '1': '0';

   $logLoginFailuresFilename =
   (
      (array_key_exists('logLoginFailuresFilename', $_SESSION['soeAuthentication']))?
      $_SESSION['soeAuthentication']['logLoginFailuresFilename']: null
   );

   $logLoginSuccessesFilename =
   (
      (array_key_exists('logLoginSuccessesFilename', $_SESSION['soeAuthentication']))?
      $_SESSION['soeAuthentication']['logLoginSuccessesFilename']: null
   );

   switch ("$previouslyAuthenticated-$detailsSuppliedViaPost")
   {
    case '0-0':
      LoginAndRejectionPageGenerator::echoLoginPageHtml
      (
         $whitelist, $blacklist, N_MINUTES_UNTIL_FORGET_AUTHENTICATION
      );
      exit(0);

    case '0-1':
      $soeid = dealWithCaseDetailsSuppliedViaPost(
         $whitelist, $blacklist, $logLoginFailuresFilename, $logLoginSuccessesFilename
      );
      break;

    case '1-0':
      $soeid = dealWithCasePreviouslyAuthenticated
      (
         $whitelist, $blacklist, $_SESSION['soeAuthentication']['authenticatedSoeid']
      );
      break;

    case '1-1':
      // This will occur if the user refreshes the requested page after having entered correct
      // SOE id and password.  Deal with in same way as for case 'details supplied via $_POST'.
      $soeid = dealWithCaseDetailsSuppliedViaPost(
         $whitelist, $blacklist, $logLoginFailuresFilename, $logLoginSuccessesFilename
      );
      break;

    default:
      throw new Exception('Unexpected case.');
   }

   // If execution reaches this point, access is granted. -------------------------------------//

   $_SESSION['soeAuthentication']['authenticatedSoeid'   ] = $soeid;
   $_SESSION['soeAuthentication']['sessionCreateTimeUnix'] = time();

   // Remove $_POSTed variables so as not to
   // interfere with operation of restricted script.
   unset($_POST['soeid'   ]);
   unset($_POST['password']);

   if (array_key_exists('onLoginSuccessFunction', $_SESSION['soeAuthentication']))
   {
      call_user_func($_SESSION['soeAuthentication']['onLoginSuccessFunction']);
   }

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

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function dealWithCaseDetailsSuppliedViaPost(
   $whitelist, $blacklist, $logLoginFailuresFilename = null, $logLoginSuccessesFilename = null
)
{
   if (!array_key_exists('soeid', $_POST) || !array_key_exists('password', $_POST))
   {
      throw new Exception('Required $_POST parameter(s) not set.');
   }

   // Note Regarding Case Sensitivity of SOE ids
   // ------------------------------------------
   // Differences in letter-case are deemed to be irrelevant in comparisons between SOE ids.
   // This was decided following complaints from some users who were evidently used to logging into
   // other applications using a capitalized SOE id.  Here the SOE id from the client is converted
   // to lowercase.  All comparisons henceforth are done in lowercase.
   $soeid       = strtolower($_POST['soeid']);
   $password    = $_POST['password'];
   $ldapSuccess = Utils_security::dpiLdapUserCheckImproved(
      array(
         'host'                      => 'dominoldap.internal.vic.gov.au',
         'logLoginFailuresFilename'  => $logLoginFailuresFilename       ,
         'logLoginSuccessesFilename' => $logLoginSuccessesFilename      ,
         'password'                  => $password                       ,
         'port'                      => 389                             ,
         'username'                  => $soeid
      )
   );

   if ($ldapSuccess)
   {
      $whitelistExists = ($whitelist === null)? '0': '1';
      $blacklistExists = ($blacklist === null)? '0': '1';

      switch ("$whitelistExists-$blacklistExists")
      {
       case '0-0':
         $accessGranted = true;
         $reason        = null;
         break;

       case '0-1':
         $accessGranted = (!in_array($soeid, $blacklist));
         $reason        = 'Your SOE id is in the list of SOE ids of user\'s to be denied access.';
         break;

       case '1-0':
         $accessGranted = (in_array($soeid, $whitelist));
         $reason        = 'Your SOE id is not in the list of authorised user\'s SOE ids.';
         break;

       case '1-1':
         $accessGranted = (in_array($soeid, $whitelist) && !in_array($soeid, $blacklist));
         $reason        =
         (
            'Your SOE id is either not on the list of authorised users,' .
            ' or is on the list of users to be denied access.'
         );
         break;

       default:
         throw new Exception('Unexpected case.');
      }
   }
   else
   {
      $accessGranted = false;
      $reason        =
      (
         'Your SOE id is invalid, or the password you supplied did not match your SOE id.'
      );
   }

   if (!$accessGranted)
   {
      // The user has been denied access for reasons given in $reason.
      LoginAndRejectionPageGenerator::echoRejectionHtml($reason);
      exit(0);
   }

   // The user has passed the LDAP check, and has also passed the
   // whitelist / blacklist checks.  Therefore access is granted.
   return $soeid;
}

/*******************************************END*OF*FILE********************************************/
?>
