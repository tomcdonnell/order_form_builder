<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "assert_cookies_enabled.php"
*
* Project: Restrict Access.
*
* Purpose: Display 'Cookies must be enabled to proceed' message if cookies are not enabled.
*          Otherwise act so as to appear to do nothing.  This means perform the check, but pass on
*          any $_GET and $_POST parameters during each redirect.
*
* Author: Tom McDonnell 2010-07-03.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../utils/Utils_html.php';
require_once dirname(__FILE__) . '/../../utils/Utils_htmlForm.php';

// Settings. ///////////////////////////////////////////////////////////////////////////////////////

// Passing -1 will show every possible error.
// (see tip at http://www.php.net/manual/en/function.error-reporting.php).
error_reporting(-1);

// Global variables. ///////////////////////////////////////////////////////////////////////////////

$TEST_COOKIE_TTL_SECONDS = 120;
$COOKIES_EXPLANATION_URL =
(
   'http://www.google.com/support/accounts/bin/answer.py?answer=61416&hl=en&ctx=ch_ServiceLoginAuth'
);

// Globally executed code. /////////////////////////////////////////////////////////////////////////

try
{
   $cookieSet   = (array_key_exists('testCookieSet', $_GET   ))? '1': '0';
   $cookieFound = (array_key_exists('testCookie'   , $_COOKIE))? '1': '0';

   switch ("$cookieSet-$cookieFound")
   {
    case '0-1':
      // If page is loaded without $_GET string (?testCookie=1) after cookie has been set and
      // before cookie has expired, re-set cookie and force proper procedure to be followed.
      // Fall through.
    case '0-0':
      // Normal case when page first loaded.
      // Set test cookie.
      setcookie('testCookie', '1', time() + $TEST_COOKIE_TTL_SECONDS);
      Utils_htmlForm::redirectToUrlIncludingGetAndPostParams
      (
         "{$_SERVER['PHP_SELF']}?testCookieSet=1"
      );
      exit(0);

    case '1-0':
      // Normal case after immediate redirect if cookies not enabled.
      // Abnormal case if page is loaded with $_GET string (?testCookie=1) after cookie has expired.
      // Unset $_GET['testCookieSet'] so that the $_GET string constructed by the function below
      // will not include that variable.
      // Note that a refresh at this point will not force re-check because URL is unchanged.
      unset($_GET['testCookieSet']);
      displayEnableCookiesMessagePageAndExit($_SERVER['PHP_SELF']);
      exit(0);

    case '1-1':
      // Normal case after immediate redirect if cookies
      // enabled, or after user reads message and enables cookies.
      // Do nothing but allow script to continue.
      break;

    default:
      throw new Exception('Unexpected case.');
   }

   // If execution reaches this point, cookies are enabled so execution may continue. ---------//

   // Unset $_GET['testCookieSet'] so $_GET array in page requiring that cookies be enabled is
   // unaffected by this script.  Note that the URL as displayed by the browser will be unchanged.
   unset($_GET['testCookieSet']);
}
catch (Exception $e)
{
   echo $e->getMessage();

   // IMPORTANT
   // ---------
   // This line must remain to ensure that an exception generated
   // above does not result in the page that included this script being displayed.
   exit(0);
}

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 * The parameters passed to this page both by the $_GET and $_POST methods must be passed on to the
 * page for which cookies must be enabled so as not to interfere with the operation of that page.
 */
function displayEnableCookiesMessagePageAndExit($redirectUrl)
{
   global $COOKIES_EXPLANATION_URL;

   $postUrl = $redirectUrl . Utils_htmlForm::createGetStringFromArray($_GET, '?');
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
 <head><title>Cookies Not Enabled</title></head>
 <body>
  <h1>Cookies Must Be Enabled To Proceed</h1>
  <p>The requested page requires that cookies be enabled in your web browser.</p>
  <p>
   Instructions on how to enable cookies in your web browser may be found
   <a href='<?php echo Utils_html::escapeSingleQuotes($COOKIES_EXPLANATION_URL); ?>'>here</a>.
  </p>
  <p>Enable cookies in your web browser then click 'Proceed' below.</p>
  <p>
   Note that refreshing this page even after enabling cookies will not have the desired effect.
  </p>
  <form action='<?php echo Utils_html::escapeSingleQuotes($postUrl); ?>' method='POST'>
<?php
   Utils_htmlForm::echoArrayAsHiddenInputs($_POST, '   ');
?>
   <input type='submit' value='Proceed'/>
  </form>
 </body>
</html>
<?php
   exit(0);
}

/*******************************************END*OF*FILE********************************************/
?>
