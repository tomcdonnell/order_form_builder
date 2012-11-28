<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "forget_soeid_and_password.php"
*
* Project: Security.
*
* Purpose: Include this file at the top of any web page to clear any SOE authentication information
*          stored in the relevant $_SESSION by the 'require_soeid_and_password.php' script.
*
* Author: Tom McDonnell 2010-06-23.
*
\**************************************************************************************************/

// Settings. ///////////////////////////////////////////////////////////////////////////////////////

// Passing -1 will show every possible error.
// (see tip at http://www.php.net/manual/en/function.error-reporting.php).
error_reporting(-1);

session_start();
unset($_SESSION['soeAuthentication']);

if (array_key_exists('redirectUrl', $_GET)) {
   header("Location: {$_GET['redirectUrl']}");
   exit(0);
}

// HTML code. //////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
 <head><title>SOE Details Deleted</title></head>
 <body>
  <h1>SOE Details Deleted</h1>
  <p>The SOE id and password you previously provided have now been deleted.</p>
  <p>
   Do not be alarmed, this just means that the next time you attempt to access a
   restricted page, you will be prompted to provide your SOE id and password again.
  </p>
 </body>
</html>
<?php
/*******************************************END*OF*FILE********************************************/
?>
