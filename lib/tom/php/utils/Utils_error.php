<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_error.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to php errors and exceptions.
*
* Author: Tom McDonnell 2011
*
\**************************************************************************************************/

/*
 *
 */
class Utils_error
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function initErrorAndExceptionHandler($logFileName, $genericErrorPageUrl = null)
   {
      if (!is_string($logFileName))
      {
         echo 'Supplied log file name is not a string.';
         die();
      }

      if ($genericErrorPageUrl !== null && !is_string($genericErrorPageUrl))
      {
         echo 'Supplied error page URL is not null and is not a string.';
         die();
      }

      self::$_logFileName         = $logFileName;
      self::$_genericErrorPageUrl = $genericErrorPageUrl;

      set_error_handler(array(__CLASS__, 'errorHandlerConvertErrorToException'), E_ALL);
      set_exception_handler(array(__CLASS__, 'exceptionHandler'));
   }

   /*
    *
    */
   public static function logExceptionDetails($e, $messageToPrecedeErrorDetails = null)
   {
      if (self::$_logFileName === null)
      {
         echo "Attempted to log exception details when no log file name specified.\n";
         echo ' To specify a log file name call ';
         echo __CLASS__, '::initErrorAndExceptionHandler().';
         die();
      }

      self::logMessage
      (
         (($messageToPrecedeErrorDetails === null)? '': $messageToPrecedeErrorDetails) .
         $e->getMessage() . '|' . $e->getTraceAsString() . "|URL: {$_SERVER['PHP_SELF']}"
      );
   }

   /*
    *
    */
   public static function logMessage($message)
   {
      $file        = fopen(self::$_logFileName, 'a');
      $message     = date('Y-m-d H:i:s') . ' ' . str_replace("\n", '__|__', $message) . "\n";
      $returnValue = fwrite($file, $message);

      if ($returnValue === false)
      {
         die("Could not write error message to log file '" . self::$_logFileName . "'.");
      }
   }

   /*
    * Convert all errors into Exceptions.
    *
    * See http://au2.php.net/manual/en/function.set-error-handler.php
    *
    * Note that CodeIgniter by default uses its own custom error handler, set in CodeIgniter.php
    * (Iine 61).  This function is intended to replace the function set as the error_handler there,
    * and so that line has been commented out.
    *
    * Note Regarding Privacy
    * ----------------------
    * This function must be public so that it can be used
    * as an error handler but should be treated as private.
    */
   public static function errorHandlerConvertErrorToException
   (
      $errno, $errstr, $errfile, $errline, $errcontext
   )
   {
      // Note Regarding Errors Deliberately Suppressed Using '@'
      // -------------------------------------------------------
      // PHP will call this custom error handler if a function call generates an error despite
      // errors being suppressed on that function call using '@'.  The following conditional
      // return was added in order to prevent deliberately suppressed errors from appearing in
      // the error logs.
      //
      // See http://au2.php.net/manual/en/function.set-error-handler.php.
      if (error_reporting() == 0) {return;}

      if (self::$_exceptionAlreadyThrownAndCaught)
      {
         // Note Regarding Error Context
         // ----------------------------
         // The contents of the $errcontext variable is not dumped below because in for
         // some errors (eg. Doctrine errors) the $errcontext variable can be very large.
         self::logMessage("$errstr|$errfile|$errline");
         self::_redirectToErrorPageOrEchoGenericErrorMessageAndDie();
         die();
      }
      else
      {
         throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
      }
   }

   /*
    * Note Regarding Privacy
    * ----------------------
    * This function must be public so that it can be used as
    * an exception handler but should be treated as private.
    */
   public static function exceptionHandler($e)
   {
      self::$_exceptionAlreadyThrownAndCaught = true;
      self::logExceptionDetails($e);
      self::_redirectToErrorPageOrEchoGenericErrorMessageAndDie();
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _redirectToErrorPageOrEchoGenericErrorMessageAndDie()
   {
      if (self::$_genericErrorPageUrl !== null)
      {
         try
         {
            header('Location: ' . self::$_genericErrorPageUrl);
            die();
         }
         catch (Exception $e)
         {
            echo 'Attempted to redirect to generic error message page when headers already';
            echo " sent.  Use output bufferring to avoid seeing this message.\n";
         }
      }

      echo "A fatal error has occurred.  Check '", self::$_logFileName, "' for details.\n";
      die();
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private static $_exceptionAlreadyThrownAndCaught = false;
   private static $_logFileName                     = null;
   private static $_genericErrorPageUrl             = null;
}

/*******************************************END*OF*FILE********************************************/
?>
