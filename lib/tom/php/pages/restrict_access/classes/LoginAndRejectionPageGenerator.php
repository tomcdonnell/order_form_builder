<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "LoginAndRejectionPageGenerator.php"
*
* Project: Security.
*
* Purpose: Generate simple HTML for login / logout pages.
*
* Author: Tom McDonnell 2011-02-15.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../../utils/Utils_htmlForm.php';
require_once dirname(__FILE__) . '/../../../utils/Utils_misc.php';
require_once dirname(__FILE__) . '/../../../utils/Utils_validator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class LoginAndRejectionPageGenerator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This page is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function echoRejectionHtml($reason)
   {
      if (self::_expectedConstantsAreDefined())
      {
         $escapedReason = str_replace("'", "\'", $reason);
         eval('call_user_func(' . LOGIN_REJECTION_PAGE_HTML_FUNCTION . ", '$escapedReason');");
      }
      else
      {
         echo "<!DOCTYPE html PUBLIC\n";
         echo " '-//W3C//DTD XHTML 1.0 Strict//EN'\n";
         echo " 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n";
         echo "<html xmlns='http://www.w3.org/1999/xhtml'>\n";
         echo " <head>\n";
         echo "  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>\n";
         echo "  <title>Access Denied</title>\n";
         echo " </head>\n";
         echo " <body>\n";
         echo "  <h1>Access Denied</h1>\n";
         echo "  <p>$reason</p>\n";
         echo " </body>\n";
         echo "</html>\n";
      }

      exit(0);
   }

   /*
    *
    */
   public static function echoLoginPageHtml($whitelist, $blacklist, $nMinutesRememberLoginDetails)
   {
      if (self::_expectedConstantsAreDefined())
      {
         eval('$indent = call_user_func(' . LOGIN_PAGE_HEAD_HTML_FUNCTION . ');');
         self::_echoLoginPageFormHtml($indent);
         eval('call_user_func(' . LOGIN_PAGE_FOOT_HTML_FUNCTION . ');');
      }
      else
      {
         $indent = self::_echoLoginPageDefaultHeadHtml
         (
            $whitelist, $blacklist, $nMinutesRememberLoginDetails
         );

         self::_echoLoginPageFormHtml($indent);
         self::_echoLoginPageDefaultFootHtml();
      }
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _expectedConstantsAreDefined()
   {
      $expectedConstantNames = array(
         'LOGIN_PAGE_HEAD_HTML_FUNCTION',
         'LOGIN_PAGE_FOOT_HTML_FUNCTION',
         'LOGIN_REJECTION_PAGE_HTML_FUNCTION'
      );

      foreach ($expectedConstantNames as $constantName) {
         if (!defined($constantName)) {
            return false;
         }
      }

      return true;
   }

   /*
    *
    */
   private static function _echoLoginPageDefaultHeadHtml(
      $whitelist, $blacklist, $nMinutesRememberLoginDetails
   )
   {
      $whitelistExists = ($whitelist === null)? '0': '1';
      $blacklistExists = ($blacklist === null)? '0': '1';

      $restrictionMsg = Utils_misc::switchAssign
      (
         "$whitelistExists-$blacklistExists", array
         (
            '0-0' => 'Access to the page you requested is restricted.', 
            '0-1' =>
            (
               'Access to the page you requested is restricted to users whose names do not appear' .
               ' on a list of users to be denied access.'
            ),
            '1-0' =>
            (
               'Access to the page you requested is restricted to users whose names appear on a' .
               ' list of authorised users.'
            ),
            '1-1' =>
            (
               'Access to the page you requested is restricted to users whose names appear on a' .
               ' list of authorised users, and do not appear on a list of users to be denied'    .
               ' access.'
            )
         )
      );

      echo "<!DOCTYPE html PUBLIC\n";
      echo " '-//W3C//DTD XHTML 1.0 Strict//EN'\n";
      echo " 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>\n";
      echo "<html>\n";
      echo " <head>\n";
      echo "  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>\n";
      echo "  <title>Restricted Access</title>\n";
      echo " </head>\n";
      echo " <body>\n";
      echo "  <h1>Restricted Access</h1>\n";
      echo "  <p>$restrictionMsg</p>\n";
      echo "  <p>\n";
      echo "   If you are authorised is view the page, you should have been given an SOE id and\n";
      echo "   password.\n";
      echo "  </p>\n";
      echo "  <p>Enter both, then click 'Submit' to continue.</p>\n";
      echo "  <p>\n";
      echo "   If upon clicking 'Submit' you are granted access, your SOE id and password will\n";
      echo "   be remembered for $nMinutesRememberLoginDetails minutes.<br/>\n";
      echo "   The time to forget will be reset to $nMinutesRememberLoginDetails minutes each\n";
      echo "   time you view a restricted page.<br/>\n";
      echo "   This will prevent you from having to repeatedly re-enter your SOE id and\n";
      echo "   password when viewing restricted pages.\n";
      echo "  </p>\n";

      return '  ';
   }

   /*
    *
    */
   private static function _echoLoginPageFormHtml($indent)
   {
      // The URL to which the SOE id and password are $_POSTed must include the original
      // $_GET string so that this script does not affect the operation of the restricted page.
      $postUrl = $_SERVER['PHP_SELF'] . Utils_htmlForm::createGetStringFromArray($_GET);

      $i = &$indent; // Abbreviation.

      echo "$i<form action='$postUrl' method='post'>\n";

      // The parameters that are $_POSTed from this script must include those originally
      // $_POSTed so that this script does not affect the operation of the restricted page.
      Utils_htmlForm::echoArrayAsHiddenInputs($_POST, "$i ");

      echo "$i <table class='soeidLoginFormTable'>\n";
      echo "$i  <tbody>\n";
      echo "$i   <tr><td colspan='2'>Enter your Lotus Notes login details</td></tr>\n";
      echo "$i   <tr><td>SOE id</td><td><input type='text' name='soeid'/></td></tr>\n";
      echo "$i   <tr><td>Password</td><td><input type='password' name='password'/></td></tr>\n";
      echo "$i   <tr>";
      echo       "<td colspan='2'>";

      // Note Regarding Display of Buttons
      // ---------------------------------
      // The 'value' attribute of the button below is intentionally left blank so that a
      // background image for the button can be added.  In Firefox, styling the text as
      // transparent would would allow the background image method to work, but not in IE.
      echo        "<input class='submitButton' type='submit' value=''/>";
      echo       "</td>";
      echo      "</tr>\n";
      echo "$i  </tbody>\n";
      echo "$i </table>\n";
      echo "$i</form>\n";
   }

   /*
    *
    */
   private static function _echoLoginPageDefaultFootHtml()
   {
      echo " </body>\n";
      echo "</html>\n";
   }
}

/*******************************************END*OF*FILE********************************************/
?>
