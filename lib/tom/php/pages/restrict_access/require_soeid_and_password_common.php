<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "require_soeid_and_password_for_ajax_common.php"
*
* Project: Security.
*
* Purpose: Include one of the following files at the top of a web page or ajax script (before any
*          other 'include' or 'require' lines, and before any session directives) to restrict
*          access to that web page or ajax script to users with an SOE id and matching password.
*
*           * 'require_soeid_and_password.php' or
*           * 'require_soeid_and_password_for_ajax.php'
*
*          A blacklist and whitelist of users may also be provided in text files.  See below for
*          details.
*
*          Alternatively styled login and rejection pages may be used.  See below for details.
*
*          The reason that this page should be included before any other 'include' or 'require'
*          lines, and before any session directives, is that this page starts its own session,
*          and then closes it by calling session_write_close() if access is granted. (If access is
*          not granted, then the session will close and be written to when the script terminates).
*          If another session was started in the restricted file or in a file included from the
*          restricted file before the session for this file was started, then a 'session already
*          started' warning would result when this page attempted to start its session, the data to
*          be stored in the session for this page would instead be stored in the other session, and
*          so this page would not function correctly.
*
*          This script works by prompting the user for an SOE id and password if these are not
*          already stored in $_SESSION, and exiting with an 'Access Denied' message if the SOE id
*          and password are incorrect.  If the SOE id and password are correct, this script does
*          nothing (or acts so as to appear to do nothing), and so the execution of the page from
*          which this script was included (the restricted page) is allowed to continue just as if
*          this script had not been included.  Any parameters intended to be passed to the
*          restricted page are not interrupted by the display of the SOE id and password form,
*          because that form reads and passes on all contents of the $_GET and $_POST arrays.
*          (The behaviour described relating to $_GET and $_POST parameters is what was meant by
*          'acting so as to appear to do nothing').
*
*          This page will add a key 'soeAuthentication' to the $_SESSION array.  This key (and
*          associated value) should be left alone by the page whose access is to be restricted by
*          the inclusion of this script.
*
*          Example SOE id: The SOE id of Tom McDonnell is tm37.
*
* Author: Tom McDonnell 2010-06-23.
*
* -------------------------------------------------------------------------------------------------
*
*    Whitelists / Blacklists
*
*    This script will check the directory from which it was included for the presence of two text
*    files: 'soeid_whitelist.txt' and 'soeids_blacklist.txt'.  These files, if present, are
*    expected to contain a list of SOE ids, one per line.
*
*    The $_SESSION array can also be used to specify an SQL table to check for a whitelist or
*    blacklist.  See the code for details.
*
*    The authentication behaviour relating to the whitelist and blacklist is explained below.
*
*    * If neither whitelist nor blacklist is supplied,
*      Access granted only to users with correct password.
*
*    * If only whitelist is supplied,
*      Access granted only to users with correct password whose SOE ids are in whitelist.
*
*    * If only blacklist supplied,
*      Access granted only to users with correct password whose SOE ids are not in blacklist.
*
*    * If both whitelist and blacklist are supplied,
*      Access granted only to users with correct password whose SOE ids are in whitelist and
*      are not in blacklist.
*
* -------------------------------------------------------------------------------------------------
*
*    Using Alternatively Styled Login and Rejection Pages
*
*    If alternatively styled login and rejection pages are to be used, the following constants must
*    be defined.  The values should be strings that when eval'ed will cause the functions to run.
*
*     * LOGIN_PAGE_HEAD_HTML_FUNCTION
*     * LOGIN_PAGE_FOOT_HTML_FUNCTION
*     * LOGIN_REJECTION_PAGE_HTML_FUNCTION
*
*    See file 'LoginAndRejectionPageGenerator.php' for more details.
*
* Author: Tom McDonnell 2011-02-15.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../utils/Utils_validator.php';

// Defines. ////////////////////////////////////////////////////////////////////////////////////////

define('N_MINUTES_UNTIL_FORGET_AUTHENTICATION', 30);

// Settings. ///////////////////////////////////////////////////////////////////////////////////////

session_start();

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   // Note Regarding $_SESSION Parameters
   // -----------------------------------
   // Even though this page is designed to be the first script included in the page whose access is
   // to be restricted, this script must not initialise the entire $_SESSION array.  The reason is
   // that $_SESSION data may be intended to persist beyond the lifetime of a script.  In general,
   // each script that is intended to function independently from other scripts making use of the
   // $_SESSION array should only modify variables inside an array accessible by a single $_SESSION
   // key.  The $_SESSION key chosen should be unique to the script.
   if (!array_key_exists('soeAuthentication', $_SESSION))
   {
      $_SESSION['soeAuthentication'] = array();
   }

// Temporary try...catch block while investigating a bug that only occurs for Prathe.
try
{
   Utils_validator::checkArray
   (
      $_SESSION['soeAuthentication'], array(), array
      (
         'authenticatedSoeid'        => 'string'       ,
         'onLoginSuccessFunction'    => 'arrayOrString',
         'sessionCreateTimeUnix'     => 'int'          ,
         'soeidsBlacklistTableName'  => 'string'       ,
         'soeidsWhitelistTableName'  => 'string'       ,
         'logLoginFailuresFilename'  => 'string'       ,
         'logLoginSuccessesFilename' => 'string'       ,

         // TODO
         // ----
         // Remove the line below.  All references in code to this key were removed on 2010-02-16.
         // The line below was left in the code so that users whose $_SESSION still inlcluded the
         // key would not see error messages.  The $_SESSION['soeAuthentication'] array is cleared
         // whenever the $_SESSION expires or the user visits the logout page.  Give it a couple of
         // days (after the code has been pushed live), then remove the line below.
         'echoLoginPageHtmlFunctions' => 'array'
      )
   );
}
catch (Exception $e)
{
   echo "Prathe, is it you?<br/>\n";
   echo "That damn pesky \$_SESSION related bug has resurfaced!<br/>\n";
   echo "Clear your \$_SESSION as a workaround.\n";
   echo $e;
}

   // Note Regarding $_GET and $_POST Parameters
   // ------------------------------------------
   // This script does not use $_GET parameters, but must not place restrictions on what $_GET
   // parameters may have been passed.  The reason is that this script is designed to be included
   // in arbitrary scripts that may make use of $_GET parameters.  Likewise, although this script
   // does use $_POST parameters, it must not let these interfere with any $_POSTed parameters used
   // by the restricted script.  Therefore no restrictions are placed on what can be passed to this
   // script by either the $_GET or the $_POST method, and the $_POSTed parameters used by this
   // script are unset once the user has been authenticated.

   if
   (
      array_key_exists('sessionCreateTimeUnix', $_SESSION['soeAuthentication']) &&
      (time() - (int)$_SESSION['soeAuthentication']['sessionCreateTimeUnix']) >
      N_MINUTES_UNTIL_FORGET_AUTHENTICATION * 60
   )
   {
      $_SESSION['soeAuthentication'] = array();
   }
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
function dealWithCasePreviouslyAuthenticated($whitelist, $blacklist, $soeid)
{
   $whitelistExists = ($whitelist === null)? '0': '1';
   $blacklistExists = ($blacklist === null)? '0': '1';

   switch ("$whitelistExists-$blacklistExists")
   {
    case '0-0': $granted = true; break;
    case '0-1': $granted = (!in_array($soeid, $blacklist)); break;
    case '1-0': $granted = ( in_array($soeid, $whitelist)); break;
    case '1-1': $granted = ( in_array($soeid, $whitelist) && !in_array($soeid, $blacklist)); break;
    default: throw new Exception('Unexpected case.');
   }

   if (!$granted)
   {
      // The user has been authenticated previously, but is either blacklisted, or is not on the
      // whitelist.  Rather than showing the user an 'Access Denied' message, allow the user to
      // re-enter their SOE id and password.  The user will be less confused this way.
      LoginAndRejectionPageGenerator::echoLoginPageHtml
      (
         $whitelist, $blacklist, N_MINUTES_UNTIL_FORGET_AUTHENTICATION
      );

      exit(0);
   }

   // The user has previously been authenticated, and has passed
   // the whitelist / blacklist checks.  Therefore access is granted.
   return $soeid;
}

/*******************************************END*OF*FILE********************************************/
?>
