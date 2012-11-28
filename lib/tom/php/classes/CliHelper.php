<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "CliHelper.php"
*
* Project: Utilities.
*
* Purpose: Helper class for use with command-line scripts.
*
* Author: Tom McDonnell 2011-11-11.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/../utils/Utils_misc.php';

// Code Template for use with class CliHelper. /////////////////////////////////////////////////////
//
// require_once dirname(__FILE__) . '/../../../../lib/tom/php/classes/CliHelper.php';
// 
// error_reporting(-1);
// 
// $cliHelper = new CliHelper(__FILE__, '// Type script description here.');
// $config    = $cliHelper->validateArgvAndReturnConfigOrOutputUsageAndDie();
// 
// // Create database connection here.
// // Begin database transaction here.
//
// $cliHelper->performPostBeginTransactionDuties();
// 
// try
// {
//     // Perform database updates here.
// 
//     $cliHelper->performPreCommitDuties();
//
//     // Commit database transaction here.
// }
// catch (Exception $e)
// {
//     // Roll back database transaaction here.
//
//     $cliHelper->performPostRollbackDuties($e);
// }
//
// $cliHelper->performPostCommitDuties();
// 
////////////////////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class CliHelper
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * Usage example:
    *    $cliHelper = new CliHelper(__FILE__, array('--hypothetical', '--actual', '--actualCron'));
    */
   public function __construct($scriptFilename, $scriptDescription, $outputStream = null)
   {
      if ($outputStream === null)
      {
         // Note Regarding Output Streams
         // -----------------------------
         // Writing to the stream retuned by fopen('php://output', 'w') is equivalent to using the
         // 'echo' construct.  The option of specifying the output stream was introduced to solve a
         // curious problem whose cause was never found.  The problem was that if using the 'echo'
         // construct to output text, some scripts would pause during execution until a key was
         // pressed.  Changing the output stream to STDOUT fixed the problem so that the affected
         // scripts no longer paused.  The downside of using STDOUT is that output buffering will
         // no longer work.
         //    If the problem is encountered that a script pauses during execution until a key is
         // pressed, STDOUT should be provided to this class as the output stream, and all output
         // should be done using the $this->out() function.  The CliHelperOutputHelperAbstract
         // class may be helpful also.
         //
         // @see CliHelperOutputHelperAbstract.php
         //
         $outputStream = fopen('php://output', 'w');
      }

      $pos1 = strrpos($scriptFilename, '/' );
      $pos2 = strrpos($scriptFilename, '\\');
      $pos  = Utils_misc::switchAssign
      (
         (($pos1 === false)? '1': '0') . '-' . (($pos2 === false)? '1': '0'), array
         (
            '0-0' => (($pos1 > $pos2)? $pos1 + 1: $pos2 + 1),
            '0-1' => $pos1 + 1                              ,
            '1-0' => $pos2 + 1                              ,
            '1-1' => 0
         )
      );

      $this->_scriptDescription       = $scriptDescription;
      $this->_scriptFilenameMinusPath = substr($scriptFilename, $pos);
      $this->_out                     = $outputStream;
   }

   /*
    *
    */
   public function validateArgvAndReturnConfigOrOutputUsageAndDie()
   {
      global $argv;

      $usageString =
      (
         "\n" . str_repeat('-', 79) . "\n"             .
         "\n\"{$this->_scriptFilenameMinusPath}\"\n\n" .
         "Description\n"                               .
         "-----------\n"                               .
         "$this->_scriptDescription\n\n"               .
         "Usage\n"                                     .
         "-----\n"                                     .
         $this->_getExpectedArgumentsDescription()     .
         "\n" . str_repeat('-', 79) . "\n\n"
      );

      $this->out("\n");

      switch (count($argv))
      {
       case 1 : $this->out($usageString); die();
       case 2 : break;
       default: $this->out("Incorrect number of arguments.\n\n$usageString"); die();
      }

      list($argument, $emailAddresses) = self::_parseArgumentAndEmailAddresses($argv[1]);

      if (!array_key_exists($argument, $this->_DETAILS_BY_ARGUMENT))
      {
         $this->out("Incorrect argument '{$argv[1]}'.\n$usageString"); die();
      }

      $this->_configuration = $this->_DETAILS_BY_ARGUMENT[$argument]['configuration'];
      $this->_configuration['emailAddresses'] = $emailAddresses;

      if (!$this->_configuration['echoNormalOutput'])
      {
         ob_start();
      }

      switch ($argument)
      {
       case '--actualWithEmailOnException':
         $this->_configuration['emailAddresses'] = $emailAddresses;
         break;
       case '--hypothetical'          : // Fall through.
       case '--hypotheticalWithEmail:':
         $this->out($this->_getNoteRegardingHypotheticalMode() . "\n\n");
         break;
       case '--emailTest:':
         $this->out('Sending test email...');
         $success = mail
         (
            implode(',', $this->_configuration['emailAddresses']),
            'Test email from CliHelper'                          ,
            'This is a test email sent by the CliHelper.'        ,
            'From: cron@dpi.vic.gov.au'
         );
         $this->out((($success)? 'done.': 'failed to send.') . "\n");
         die();
         
       default:
         // Do nothing.
      }

      return $this->_configuration;
   }

   /*
    *
    */
   public function performPostBeginTransactionDuties()
   {
      $this->_scriptStartTime = microtime(true);
      $this->out("Script Start Time: " . date('Y-m-d H:i:s', $this->_scriptStartTime) . "\n");
      $this->out("Transaction begun.\n\n");
   }

   /*
    *
    */
   public function performPreCommitDuties()
   {
      if (!$this->_configuration['actuallyUpdateDb'])
      {
         throw new Exception
         (
            'No error actually occurred.  A bogus exeception was thrown in order to cause a' .
            ' rollback of all database changes because the script was run in hypothetical mode.'
         );
      }
   }

   /*
    *
    */
   public function performPostCommitDuties()
   {
      $this->out("Transaction committed.\n");
      $this->_outputScriptDurationSummary();

      if (!$this->_configuration['echoNormalOutput'])
      {
         ob_end_clean();
      }
   }

   /*
    *
    */
   public function performPostRollbackDuties(Exception $e)
   {
      $exceptionString =
      (
         "Error detected.  Transaction rolled back.\n\n" .
         $e->getMessage() . "\n" .
         $e->getTraceAsString()
      );

      $this->out("$exceptionString\n");
      $this->_outputScriptDurationSummary();

      if ($this->_configuration['sendEmailOnException'])
      {
         $this->out("An email will be sent to the following email addresses:\n   " );
         $this->out(implode(', ', $this->_configuration['emailAddresses']) . "\n\n");

         $obContents = ob_get_contents();
         $success    = mail
         (
            implode(',', $this->_configuration['emailAddresses'])                              ,
            "Exception caught during execution of cron job '{$this->_scriptFilenameMinusPath}'",
            (($obContents == '')? $this->_outputString: $obContents)                           ,
            'From: cron@dpi.vic.gov.au'
         );

         $this->out('Sending email...');
         $this->out((($success)? 'done.': 'failed to send.') . "\n\n");
      }

      // Die here to avoid execution continuing to the $this->performPostCommitDuties() function.
      die();
   }

   /*
    *
    */
   public function out($string)
   {
      $this->_outputString .= $string;
      fwrite($this->_out, $string);
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function _parseArgumentAndEmailAddresses($string)
   {
      $argumentsRequiringEmailAddresses = array
      (
         '--hypotheticalWithEmail:', '--actualWithEmailOnException:', '--emailTest:'
      );

      $parsedArgument       = null;
      $parsedEmailAddresses = array();

      foreach ($argumentsRequiringEmailAddresses as $argument)
      {
         $strlenArgument = strlen($argument);

         if (substr($string, 0, $strlenArgument) == $argument)
         {
            $parsedArgument       = $argument;
            $emailAddressesString = substr($string, $strlenArgument);
            $parsedEmailAddresses = explode(',', $emailAddressesString);
            break;
         }
      }

      if ($parsedArgument === null)
      {
         $parsedArgument = $string;
      }

      return array($parsedArgument, $parsedEmailAddresses);
   }

   /*
    *
    */
   private function _getExpectedArgumentsDescription()
   {
      // NOTE: Restrict output to 80 char max width to suit standard console.
      $usageString  = "php {$this->_scriptFilenameMinusPath} [argument]\n\n";
      $usageString .= "[argument] is one of the following (including the hyphens):\n\n";

      foreach ($this->_DETAILS_BY_ARGUMENT as $argument => $details)
      {
         $usageString .= " $argument\n   {$details['description']}\n\n";
      }

      return $usageString;
   }

   /*
    *
    */
   private function _getNoteRegardingHypotheticalMode()
   {
      // 80 char max width to suit standard console.
      return
      (
         "Note Regarding Hypothetical Mode\n"                                              .
         "--------------------------------\n"                                              .
         "The script is running in hypothetical mode.  All database changes referred to\n" .
         "in the output below will be rolled back before the script completes.  If the\n"  .
         "script is interrupted part way through, the changes will not be committed and\n" .
         "so will be rolled back by the database engine when the connection to the\n"      .
         "database closes."
      );
   }

   /*
    *
    */
   private function _outputScriptDurationSummary()
   {
      $scriptFinishTime = microtime(true);
      $scriptDuration   = $scriptFinishTime - $this->_scriptStartTime;
      $this->out("\nScript Finish Time: " . date('Y-m-d H:i:s', $scriptFinishTime) . "\n"  );
      $this->out('Script Duration   : '   . date('i:s'        , $scriptDuration  ) . "\n\n");
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   // NOTE: The variable name is capitalised because the variable is intended to be used as const.
   private $_DETAILS_BY_ARGUMENT = array
   (
      '--hypothetical' => array
      (
         'description'   => <<<STR
Same as --actual, but throw a bogus exception prior to committing database
   changes in order to force a rollback.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => false  ,
            'echoNormalOutput'     => true   ,
            'emailAddresses'       => array(),
            'sendEmailOnException' => false
         )
      ),
      '--hypotheticalWithEmail:' => array
      (
         'description'   => <<<STR
Same as --hypothetical, except when the bogus exception is caught, email the
   normal script output plus exception details.
   Provide email addresses as in --actualWithEmailOnException.
   Note: Nothing is output until the script finishes execution to allow what
   would have been output to be saved for the email using output bufferring.
   This option should be used to test what the email will look like in case of
   a real exception.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => false  ,
            'echoNormalOutput'     => false  ,
            'emailAddresses'       => array(), // To be updated by script.
            'sendEmailOnException' => true
         )
      ),
      '--actual' => array
      (
         'description'   => <<<STR
Actually update the database.  Echo a description of actions performed to
   the command line.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => true   ,
            'echoNormalOutput'     => true   ,
            'emailAddresses'       => array(),
            'sendEmailOnException' => false
         )
      ),
      '--actualCron' => array
      (
         'description'   => <<<STR
Same as --actual, but echo nothing to the command line unless an exception
   is caught.  If an exception is caught, echo everything that would have been
   echoed if --actual was used including details of the exception caught.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => true   ,
            'echoNormalOutput'     => false  ,
            'emailAddresses'       => array(),
            'sendEmailOnException' => false
         )
      ),
      '--actualWithEmailOnException:' => array
      (
         'description' => <<<STR
Same as --actual, but echo nothing to the command line.  If an exception is
   caught, email everything that would have been echoed if --actual was used
   including details of the exception caught.
   Provide email addresses as in the following example:
   '"--actualWithEmailOnException:tomcdonnell@gmail.com,charlie@brown.com"'.
   Note that the argument must be surrounded by quotes to prevent the operating
   system interpreting the full-stops as argument separators and an 'Incorrect
   number of arguments' error resulting.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => true   ,
            'echoNormalOutput'     => false  ,
            'emailAddresses'       => array(), // To be updated by script.
            'sendEmailOnException' => true
         )
      ),
      '--emailTest:' => array
      (
         'description'   => <<<STR
Do nothing except send one or more test emails.
   Provide email addresses as in --actualWithEmailOnException.
STR
         ,
         'configuration' => array
         (
            'actuallyUpdateDb'     => false  ,
            'echoNormalOutput'     => true   ,
            'emailAddresses'       => array(), // To be updated by script.
            'sendEmailOnException' => true
         )
      ),
   );

   private $_scriptFilenameMinusPath = null;
   private $_scriptStartTime         = null;
   private $_configuration           = null;
   private $_outputString            = '';
}

/*******************************************END*OF*FILE********************************************/
?>
