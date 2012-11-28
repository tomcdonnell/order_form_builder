<?php
/*********************************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=119 go-=b
*
* Filename: "index.php"
*
* Project: Common pages - Contact Me.
*
* Purpose: Display an email contact form whose configuration depends on $_POSTed data.
*
* Author: Tom McDonnell 2009-04-25.
*
\*********************************************************************************************************************/

// Settings. //////////////////////////////////////////////////////////////////////////////////////////////////////////

session_start();

ini_set('display_errors'        , '1');
ini_set('display_startup_errors', '1');

// Passing -1 will show every possible error.
// (see tip at http://www.php.net/manual/en/function.error-reporting.php).
error_reporting(-1);

// Includes. //////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../../utils/Utils_html.php';
require_once dirname(__FILE__) . '/../../utils/Utils_misc.php';
require_once dirname(__FILE__) . '/../../utils/Utils_validator.php';

// Global variables. //////////////////////////////////////////////////////////////////////////////////////////////////

$filesJs  = array();
$filesCss = array();

// Globally executed code. ////////////////////////////////////////////////////////////////////////////////////////////

try
{
   if (count($_POST) > 0)
   {
      Utils_validator::checkArray
      (
         $_POST, array
         (
            'backAnchorHrefPlusGetString' => 'nonEmptyString',
            'backAnchorText'              => 'nonEmptyString',
            'pageTitle'                   => 'nonEmptyString',
            'pageHeading'                 => 'nonEmptyString',
            'emailDstName'                => 'nonEmptyString',
            'emailSrcName'                => 'nonEmptyString',
            'replyEmailAddress'           => 'nonEmptyString',
            'subject'                     => 'nonEmptyString',
            'message'                     => 'nonEmptyString'
         )
      );
      extract($_POST);

      $success = mail
      (
         getEmailAddressFromName($emailDstName), $subject, $message,
         (
            "From: $emailSrcName\r\n"          .
            "Reply-To: $replyEmailAddress\r\n" .
            'X-Mailer: PHP/' . phpversion()
         )
      );

      $pageType = ($success)? 'messageSent': 'messageNotSent';
   }
   else
   {
      Utils_validator::checkArrayAndSetDefaults
      (
         $_GET,
         array
         (
            'backAnchorHref' => 'nonEmptyString',
            'backAnchorText' => 'nonEmptyString',
            'emailDstName'   => 'nonEmptyString'
         ),
         array
         (
            'pageTitle'               => array('nonEmptyString'      , 'Contact Form'),
            'pageHeading'             => array('nonEmptyString'      , 'Contact Form'),
            'emailSrcName'            => array('nonEmptyString'      , 'Contact Form'),
            'styleSheet'              => array('nullOrNonEmptyString', null          ),
            'backAnchorHrefGetString' => array('string'              , ''            ),
            'subject'                 => array('nonEmptyString'      , 'No subject'  )
         )
      );

      if ($styleSheet !== null) {$filesCss[] = $_GET['styleSheet'];}

      $backAnchorHrefPlusGetString = "$backAnchorHref?$backAnchorHrefGetString";

      // This function is used here only to check that an
      // email address is known for the given emailDstName.
      getEmailAddressFromName($emailDstName);

      $filesJs  = array
      (
         'index.js'                  ,
         '../../../js/utils/utils.js',
         '../../../js/utils/utilsValidator.js'
      );
      $pageType = 'contactForm';
   }
}
catch (Exception $e)
{
   echo $e->getMessage();
   die;
}

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function getEmailAddressFromName($name)
{
   $emailAddressesByName = array
   (
      'tom'   => 'tomcdonnell@gmail.com',
      'luke'  => '',
      'shawn' => ''
   );

   if (!array_key_exists($name, $emailAddressesByName))
   {
      throw new Exception("Unknown email destination name '$name'.");
   }

   if ($emailAddressesByName[$name] == '')
   {
      throw new Exception(ucfirst($name) . ', you need to give Tom your email address.');
   }

   return $emailAddressesByName[$name];
}

// HTML. //////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
 <head>
<?php
Utils_html::echoHtmlLinkTagsForCssFiles($filesCss, '  ');
Utils_html::echoHtmlScriptTagsForJsFiles($filesJs, '  ');
?>
  <title><?php echo $pageTitle; ?></title>
 </head>
 <body>
  <a href='<?php echo $backAnchorHrefPlusGetString; ?>'/><?php echo $backAnchorText; ?></a>
  <h1><?php echo $pageHeading; ?></h1>
<?php
 switch ($pageType)
 {
  case 'contactForm':
?>
  <form action='index.php' method='POST'>
   <h4>Subject:</h4>
   <input type='text' name='subject' id='subjectInput' size='70'
    value='<?php echo Utils_html::escapeSingleQuotes($subject); ?>' type='hidden'/>
   <br />
   <h4>Message:</h4>
   <textarea rows='10' cols='70' name='message' id='messageInput'></textarea>
   <br />
   <h4>Your Email Address</h4>
   <input type='text' name='replyEmailAddress' id='replyEmailAddressInput' size='70'/>
   <br />
   <input type='hidden' name='backAnchorHrefPlusGetString'
    value='<?php echo Utils_html::escapeSingleQuotes($backAnchorHrefPlusGetString); ?>'/>
   <input type='hidden' name='backAnchorText' value='<?php echo Utils_html::escapeSingleQuotes($backAnchorText); ?>'/>
   <input type='hidden' name='pageTitle'      value='<?php echo Utils_html::escapeSingleQuotes($pageTitle     ); ?>'/>
   <input type='hidden' name='pageHeading'    value='<?php echo Utils_html::escapeSingleQuotes($pageHeading   ); ?>'/>
   <input type='hidden' name='emailDstName'   value='<?php echo Utils_html::escapeSingleQuotes($emailDstName  ); ?>'/>
   <input type='hidden' name='emailSrcName'   value='<?php echo Utils_html::escapeSingleQuotes($emailSrcName  ); ?>'/>
   <br />
   <input type='submit' value='Submit' id='submitButton'/>
  </form>
<?php
    break;
  case 'messageSent':
?>
   <p>Your message has been sent.</p>
<?php
    break;
  case 'messageNotSent':
?>
   <p>Your message could not be sent due to a server error.</p>
<?php
    break;
  default:
    throw new Exception("Unknown page type '$pageType'.");
 }
?>
 </body>
</html>
<?php
/*****************************************************END*OF*FILE*****************************************************/
?>
