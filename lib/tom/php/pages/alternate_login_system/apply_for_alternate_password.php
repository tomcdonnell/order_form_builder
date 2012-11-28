<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "apply_for_alternate_password.php"
*
* Project: Common pages - Alternate Login System.
*
* Purpose: Display an 'Apply for Alternate Password' page
*          whose configuration depends on $_POSTed data.
*
* Author: Tom McDonnell 2011-08-01.
*
\**************************************************************************************************/

// Settings. ///////////////////////////////////////////////////////////////////////////////////////

session_start();

ini_set('display_errors'        , '1');
ini_set('display_startup_errors', '1');

// Passing -1 will show every possible error.
// (see tip at http://www.php.net/manual/en/function.error-reporting.php).
error_reporting(-1);

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../utils/Utils_validator.php';
require_once dirname(__FILE__) . '/../../utils/Utils_misc.php';

// Global variables. ///////////////////////////////////////////////////////////////////////////////

$filesJs  = array();
$filesCss = array();

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   Utils_validator::checkArray
   (
      $_POST, array
      (
         'backAnchorHrefPlusGetString'             => 'nonEmptyString',
         'backAnchorText'                          => 'nonEmptyString',
         'pageTitle'                               => 'nonEmptyString',
         'pageHeading'                             => 'nonEmptyString',
         'getEmailAddressFromUsernameRequirePath'  => 'nonEmptyString',
         'getEmailAddressFromUsernameClassName'    => 'nonEmptyString',
         'getEmailAddressFromUsernameFunctionName' => 'nonEmptyString',
         'passwordDatabaseName'                    => 'nonEmptyString',
         'passwordTableName'                       => 'nonEmptyString',
         'passwordColumnName'                      => 'nonEmptyString'
      ), array
      (
         'username' => 'nonEmptyString'
      )
   );
   extract($_POST);

   // TODO: Finish implementing.
}
catch (Exception $e)
{
   echo $e->getMessage();
}

// HTML. ///////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
 <head>
<?php
$unixTime = time();
foreach ($filesJs  as $file) {echo "  <script src='$file?$unixTime'></script>\n"        ;}
foreach ($filesCss as $file) {echo "  <link rel='stylesheet' href='$file?$unixTime'/>\n";}
?>
  <title><?php echo $pageTitle; ?></title>
 </head>
 <body>
  <a href='<?php echo $backAnchorHrefPlusGetString; ?>'/><?php echo $backAnchorText; ?></a>
  <h1><?php echo $pageHeading; ?></h1>
  <p>
   The authentication system used by PMO has a known bug that affects a small minority of users
   whose information stored in the Business Management System (BMS) has been updated in the recent
   past.  The purpose of this page is to provide an alternate login method for users of PMO who are
   affected by that bug.
  </p>
  <p>
   If you cannot log into PMO using your soeid (eg 'tm37') and the same password you use
   to successfully log into Lotus Notes, you can apply for an alternate password to use when
   logging into the PMO system using the form below.
  </p>
  <h2>Alternate Password Application Form</h2>
  <p>
   Please enter below your SOE id and a password to use for future logins into PMO.  You may use
   any password you like, including your existing Lotus Notes password.
  </p>
  <p>
   When you click 'Submit' below, an email containing a link back to PMO will be sent to the email
   address stored in BMS for the employee with the SOE id you entered.  Once you have confirmed
   your identity by clicking the link in the email, you will be able to log into PMO using the
   alternate password.
  </p>
  <form action='index.php' method='post'>
   <table>
    <tbody>
     <tr><th>Username</th><td><input type='text' name='username'/></td></tr>
     <tr><th>Password</th><td><input type='password' name='password'/></td></tr>
     <tr><th colspan='2'><input type='submit' value='Submit'/></th></tr>
    </tbody>
   </table>
  </form>
 </body>
</html>
<?php
/*******************************************END*OF*FILE********************************************/
?>
