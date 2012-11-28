<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_security.php"
*
* Project: Utilities.
*
* Purpose: Utilities pertaining to database schema.
*
* Author: Tom McDonnell 2010-06-17.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/Utils_validator.php';
require_once dirname(__FILE__) . '/Utils_misc.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_security
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   /*
    *
    */
   public static function dpiLdapUserCheck($params)
   {
      Utils_validator::checkArrayAndSetDefaults
      (
         $params, array
         (
            'host'     => 'string',
            'password' => 'string',
            'port'     => 'int'   ,
            'username' => 'string'
         ),
         array
         (
            // Note Regarding Parameter 'basdDn'
            // ---------------------------------
            // Unsure of meaning of baseDn.  Directory Name?  See documentation for ldap_search().
            'logLoginFailuresFilename'  => array('nullOrString', null),
            'logLoginSuccessesFilename' => array('nullOrString', null),
            'baseDn'                    => array('string'      , ''  )
         )
      );
      extract($params);

      $username = strtolower($username);

      // IMPORTANT
      // ---------
      // If a valid SOE id and a blank password are supplied to the LDAP server, the
      // LDAP server will return a positive response regardless of the stored password.
      // Therefore blank passwords are rejected prior to contacting the LDAP server.
      if ($password == '') {return false;}

      // Note Regarding Error Suppression Using '@'
      // ------------------------------------------
      // Warning messages are suppressed from LDAP functions using the '@' prefix to prevent them
      // from being echoed.  The warnings are of no interest since the result of each LDAP function
      // is examined.  Using '@' to suppress warnings is recommended in the PHP documentation for
      // function ldap_errno().

      while (true)
      {
         $ldapFunctionName = 'ldap_connect';
         $ldapLinkId       = @ldap_connect($host, $port);
         if ($ldapLinkId === false) {break;}

         $ldapFunctionName = 'ldap_bind';
         $ldapBindResult   = @ldap_bind($ldapLinkId, $username, $password);
         if ($ldapBindResult === false) {break;}

         $ldapFunctionName = 'ldap_search';
         $searchResult     = @ldap_search($ldapLinkId, $baseDn, "uid=$username");
         if ($searchResult === false) {break;}

         $ldapFunctionName = 'ldap_first_entry';
         $ldapEntry        = @ldap_first_entry($ldapLinkId, $searchResult);
         if ($ldapEntry === false) {break;}

         $ldapFunctionName = 'ldap_get_attributes';
         $ldapAttributes   = @ldap_get_attributes($ldapLinkId, $ldapEntry);
         if ($ldapAttributes === false) {break;}

         $msg = 'Expected array key "cn" not present in LDAP entry.';
         if (!array_key_exists('cn', $ldapAttributes)) {throw new Exception($msg);}

         $msg = 'LDAP entry \'cn\' is not an array.';
         if (!is_array($ldapAttributes['cn'])) {throw new Exception($msg);}

         $msg = 'LDAP entry \'cn\' is an empty array.';
         if (count($ldapAttributes['cn']) == 0) {throw new Exception($msg);}

         // Success!
         $fullName = $ldapAttributes['cn'][0];
         if ($logLoginSuccessesFilename !== null)
         {
            self::_logLdapSuccess($username, $fullName, $logLoginSuccessesFilename);
         }
         ldap_close($ldapLinkId);
         return true;
      }

      // Failure.
      if ($logLoginFailuresFilename !== null)
      {
         self::_logLdapError($username, $ldapLinkId, $ldapFunctionName, $logLoginFailuresFilename);
      }
      ldap_close($ldapLinkId);
      return false;
   }

   /**
    * DPI LDAP user check
    *
    * Modified by Tom McDonnell from code written by Dale Maggee <dale.maggee@dpi.vic.gov.au>
    *
    * Function to authenticate a username (soe id) and password against
    *   DPI LDAP servers
    *
    * See code for parameter details.
    *
    * @return  variant  User's Name (e.g: "Dale Maggee") on success, FALSE on failure.
    *
    * NOTE:
    *   If this function returns false, you shouldn't just assume that means 'invalid password' -
    *   you should make your failure message include all possible failure reasons, like "Invalid
    *   username or password".  If your using filters, another failure reason is that filters
    *   didn't match.
    *
    * How This Works
    * --------------
    * The Method used for LDAP auth is slightly counter-intuitive, because our LDAP proxy doesn't
    * like letting you log in with a DN of your SOE ID (It wants a valid DN). So here's what we do:
    *
    * 1. Connect to LDAP server anonymously
    * 2. Search for a user with a given uid ( == $username )
    * 3. Get the DN from the info returned by (2)
    * 4. Attempt to connect to LDAP server using the DN from (3) and $password.
    *    Return false if this fails.
    * 5. Apply any given filters, returning false if there's no match.
    * 6. Return true.
    *
    * Filter Array Structure
    * -------------------------------------------------------------------------------------------
    * 
    * $filter = array(
    *   <level> => < filter | array(<filter>,<filter>) >,
    * )
    *
    * where <level> is an integer from 0-6 and <filter> is a regex.
    *
    * providing multiple <level>s in the same filter is an AND operation - all levels
    * need to be satisfied for a filter to match. OR-ing can be accomplished using regexs
    *
    * (These are perl regular expressions, as per PHP's preg_match() function)
    * 
    * Optionally, a 'filter' may specify multiple filters, thusly:
    *
    * $filters = array(
    *    array(
    *       <level> => <regex>,
    *       ...
    *    ),
    *    array(
    *       <level> => <regex>,
    *       ...
    *    )
    * )
    *
    * Specifying multiple filters like this is an OR operation -
    * if any one of the provided filters matches the person, it's considered a match.
    *
    * Filter Examples
    * -------------------------------------------------------------------------------------------
    *
    * // A single filter to check for SDU staff within DPI:
    * $SDU_Filter = array(
    *    0 => '/Dept of Primary Industries/i',
    *    1 => '/Service Delivery Unit/i',
    * );
    *
    * // Filter for SDU or FSV. There are 2 ways we could do this:
    * 
    * // Method 1 - Multiple filters:
    * $SDU_FSV_Filter = array(
    *    array(
    *       0 => '/Dept of Primary Industries/i',
    *       1 => '/Service Delivery Unit/i',
    *    ),
    *    array(
    *       0 => '/Dept of Primary Industries/i',
    *       1 => '/Farm Services victoria/i',
    *    )
    * );
    * 
    * // Method 2 - Using Regex 'or':
    * $SDU_Filter = array(
    *    0 => '/Dept of Primary Industries/i',
    *    1 => '/(Service Delivery Unit|Farm Services victoria)/i',
    * );
    *
    * Note use of '/i' modifier in regex - it's a good idea to make your filter case-insensitive.
    *
    * You shouldn't be too stringent in your filter terms, as the data in the LDAP directory is of
    * "questionable" quality. for example, trying to be clever and specifying: '/^Dept of Primary
    * Industries$/' (whole string match) would not work as expected, as there are some LDAP entries
    * which have: "Dept of Primary Industries; Dept of Primary Industries" as their "level0"
    * attribute. Don't ask.
    *
    * See http://en.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol.
    */
   public static function dpiLdapUserCheckImproved($params)
   {
      Utils_validator::checkArrayAndSetDefaults
      (
         $params, array
         (
            'host'     => 'string', // The user's SOE id.
            'password' => 'string',
            'port'     => 'int'   ,
            'username' => 'string'
         ),
         array
         (
            'info'                      => array('nullOrArray' , null),
            'filter'                    => array('nullOrArray' , null), // See big comment above.
            'logLoginFailuresFilename'  => array('nullOrString', null),
            'logLoginSuccessesFilename' => array('nullOrString', null),
            'baseDn'                    => array('string'      , ''  )
         )
      );
      extract($params);

      $username = strtolower($username);

      // IMPORTANT
      // ---------
      // If a valid SOE id and a blank password are supplied to the LDAP server, the
      // LDAP server will return a positive response regardless of the stored password.
      // Therefore blank passwords are rejected prior to contacting the LDAP server.
      if ($password == '') {return false;}

      // Note Regarding Error Suppression Using '@'
      // ------------------------------------------
      // Warning messages are suppressed from LDAP functions using the '@' prefix to prevent them
      // from being echoed.  The warnings are of no interest since the result of each LDAP function
      // is examined.  Using '@' to suppress warnings is recommended in the PHP documentation for
      // function ldap_errno().

      if (!function_exists('ldap_connect'))
      {
         throw new Exception('PHP LDAP extension not enabled.  Edit php.ini and restart Apache.');
      }

      while (true)
      {
         $ldapFunctionName = 'ldap_connect';
         $ldapLinkId       = @ldap_connect($host, $port);
         if ($ldapLinkId === false) {break;}

         $distinguishedName = self::_bindToLdapServerAnonymouslyAndGetDistinguishedName
         (
            $ldapLinkId, $baseDn, $username, $logLoginFailuresFilename
         );

         if ($distinguishedName === false)
         {
            ldap_close($ldapLinkId);
            self::_logMessage
            (
               "Could not get LDAP distinguished name for username '$username'.",
               $logLoginFailuresFilename
            );
            return false;
         }

         $ldapFunctionName = 'ldap_bind';
         $ldapBindResult   = @ldap_bind($ldapLinkId, $distinguishedName, $password);
         if ($ldapBindResult === false) {break;}

         $ldapFunctionName = 'ldap_search';
         $searchResult     = @ldap_search($ldapLinkId, $baseDn, "uid=$username");
         if ($searchResult === false) {break;}

         $ldapFunctionName = 'ldap_get_entries';
         $ldapEntries      = @ldap_get_entries($ldapLinkId, $searchResult);
         if ($ldapEntries === false) {break;}

         // Perform filter check if a filter was provided.
         if ($filter !== null)
         {
            if (!self::_filterLdap($filter, $ldapEntries[0]))
            {
               return false;
            }
         }

         switch ($ldapEntries['count'])
         {
          case 0:
            ldap_close($ldapLinkId);
            throw new Exception("No LDAP entries found for user '$username'.");

          case 1:
            // Expected case.  Do nothing.
            break;

          default:
            self::_logMessage
            (
               "Multiple LDAP entries found for user '$username'.  The first found was used.",
               $logLoginFailuresFilename
            );
         }

         $ldapEntry = $ldapEntries[0];

         if (!array_key_exists('cn', $ldapEntry))
         {
            ldap_close($ldapLinkId);
            throw new Exception('Expected array key "cn" not present in LDAP entry.');
         }

         if (!is_array($ldapEntry['cn']))
         {
            ldap_close($ldapLinkId);
            throw new Exception('LDAP entry \'cn\' is not an array.');
         }

         if (count($ldapEntry['cn']) == 0)
         {
            ldap_close($ldapLinkId);
            throw new Exception('LDAP entry \'cn\' is an empty array.');
         }

         // Success!
         $fullName = $ldapEntries[0]['cn'][0];
         if ($logLoginSuccessesFilename !== null)
         {
            self::_logLdapSuccess($username, $fullName, $logLoginSuccessesFilename);
         }
         ldap_close($ldapLinkId);
         return true;
      }

      // Failure.
      if ($logLoginFailuresFilename !== null)
      {
         self::_logLdapError($username, $ldapLinkId, $ldapFunctionName, $logLoginFailuresFilename);
      }
      ldap_close($ldapLinkId);
      return false;
   }

   // Private functions. ///////////////////////////////////////////////////////////////////////

   /**
    * Compares one or more filters with 'level' attributes of a directory entry,
    *
    * @param   array   $filter   The Filter to use
    * @param   array   $person   The LDAP directory entry
    *
    * @return boolean   true if the person matches the filter
    */
   private static function _filterLdap(array $filter, array $person)
   {
      if (is_array(reset($filter)))
      {
         // First element of $filter is an array - we've been given multiple 
         // filters, so we iterate through them all until we find a match
         $match = false;

         foreach ($filter as $f)
         {
            $match = self::_checkSingleFilter($f, $person);
            // Multiple filters are OR'd, thus if we have a match we can return immediately:
            if ($match)
            {
               return $match;
            }
         }
      }
      else
      {
         // Only one filter provided, check it:
         return self::_checkSingleFilter($filter, $person);
      }

      return $match;
   }

   /**
    * Compares a single filter with a directory entry. 
    *   You probably shouldn't be calling this - use self::_filterLdap() instead.
    * 
    * @param   array   $filter      Filter to check against
    * @param   array   $person      LDAP directory entry to check
    * 
    * @return   bool   True if the filter matches
    */
   private static function _checkSingleFilter(array $filter, array $person)
   {
      $match = false;

      foreach ($filter as $level => $regex)
      {
         if (isset($person["level$level"]))
         {
            if (preg_match($regex, $person["level$level"][0]))
            {
               $match = true;
            }
            else
            {
               $match = false;
               // Mismatch - stop looking
               break;
            }
         }
      }

      return $match;
   }

   /*
    * Bind anonymously to an LDAP server and attempt to
    * find a distinguished name matching a given username.
    *
    * If the distinguished name can be found, return it, otherwise log errors and return false.
    *
    * The distinguished name is important because the LDAP protocal standard guarantees that
    * it is unique.  See http://en.wikipedia.org/wiki/Lightweight_Directory_Access_Protocol.
    */
   private static function _bindToLdapServerAnonymouslyAndGetDistinguishedName
   (
      $ldapLinkId, $baseDn, $username, $logFilename = null
   )
   {
      while (true)
      {
         $ldapFunctionName = 'ldap_bind';
         $ldapBindResult   = @ldap_bind($ldapLinkId);
         if ($ldapBindResult === false) {break;}

         $ldapFunctionName = 'ldap_search';
         $searchResult     = @ldap_search($ldapLinkId, $baseDn, "uid=$username");
         if ($searchResult === false) {break;}

         $ldapFunctionName = 'ldap_get_entries';
         $ldapEntries      = @ldap_get_entries($ldapLinkId, $searchResult);
         if ($ldapEntries === false) {break;}

         if (!array_key_exists('count', $ldapEntries))
         {
            ldap_close($ldapLinkId);
            throw new Exception("Expected key 'count' not found' in \$ldapEntries array.");
         }

         switch ($ldapEntries['count'])
         {
          case 0:
            // Expected case for invalid username.
            return false;

          case 1:
            // Expected case.  Do nothing.
            break;

          default:
            self::_logMessage
            (
               "Multiple LDAP entries found for user '$username'.  The first found was used.",
               $logFilename
            );
         }

         $ldapEntry = $ldapEntries[0];

         if (!array_key_exists('dn', $ldapEntry))
         {
            ldap_close($ldapLinkId);
            throw new Exception("Expected key 'dn' not found in \$ldapEntry array.");
         }

         // Success!
         return $ldapEntry['dn'];
      }

      // Failure.
      if ($logFilename !== null)
      {
         if ($ldapFunctionName !== null)
         {
            self::_logLdapError($username, $ldapLinkId, $ldapFunctionName, $logFilename);
         }
      }
      return false;
   }

   /*
    *
    */
   private static function _logLdapSuccess($username, $fullName, $logFilename)
   {
      self::_logMessage
      (
         date('Y-m-d H:i:s') . " Username: '$username' fullName: '$fullName'", $logFilename
      );
   }

   /*
    *
    */
   private static function _logLdapError($username, $ldapLinkId, $ldapFunctionName, $logFilename)
   {
      self::_logMessage
      (
         date('Y-m-d H:i:s') .
         " LDAP function: '$ldapFunctionName'" .
         ' LDAP errno: \'' . ldap_errno($ldapLinkId) . "'" .
         ' LDAP error: \'' . ldap_error($ldapLinkId) . "'" .
         " Username: '$username'",
         $logFilename
      );
   }

   /*
    *
    */
   private static function _logMessage($message, $logFilename)
   {
      // The '@' is intended to suppress the warning message that may be printed depending on
      // error_reporting settings if the file fails to open.  Since the result of the call to
      // fopen is checked below, the warning can be disregarded.
      $logFileStream = @fopen($logFilename, 'ab');

      if ($logFileStream === false)
      {
         throw new Exception
         (
            "Log file '$logFilename' could not be opened.  Message '$message' could not be" .
            ' logged.  Ensure the path is correct, then check file permissions.'
         );
      }

      fwrite($logFileStream, "$message\n");
   }
}

/*******************************************END*OF*FILE********************************************/
?>
