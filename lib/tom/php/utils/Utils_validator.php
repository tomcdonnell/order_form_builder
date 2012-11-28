<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_validator.php"
*
* Project: Utilities.
*
* Purpose: Utilities concerning validation.
*
* Author: Tom McDonnell 2008-07-01.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

require_once dirname(__FILE__) . '/../contrib/EmailAddressValidator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 * All these functions perform a test, and throw an exception if the test fails.
 *
 * Note: Only use these functions where speed is unimportant.
 */
class Utils_validator
{
   // Public functions. -----------------------------------------------------------------------//

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
   public static function checkArray($array, $typeByRequiredKey, $typeByOptionalKey = array())
   {
      assert('is_array($array            )');
      assert('is_array($typeByRequiredKey)');
      assert('is_array($typeByOptionalKey)');

      $nKeys         = count($array            );
      $nKeysRequired = count($typeByRequiredKey);
      $nKeysOptional = count($typeByOptionalKey);
      $nKeysMax      = $nKeysRequired + $nKeysOptional;

      if ($nKeys < $nKeysRequired || $nKeys > $nKeysMax)
      {
         throw new Exception
         (
            "Incorrect number of keys in array ($nKeys).  " .
            "Expected number in range [$nKeysRequired, $nKeysMax]."
         );
      }

      self::_checkRequiredKeysAndTypes($array, $typeByRequiredKey);
      self::_checkOptionalKeysAndTypes($array, $typeByRequiredKey, $typeByOptionalKey);
   }

   /*
    *
    */
   public static function checkArrayAndSetDefaults
   (
      &$array, $typeByRequiredKey, $typeAndDefaultByOptionalKey = array()
   )
   {
      $typeByOptionalKey = array();

      foreach ($typeAndDefaultByOptionalKey as $key => $typeAndDefault)
      {
         if (!is_array($typeAndDefault) || count($typeAndDefault) != 2)
         {
            throw new Exception
            (
               'Type and default value for optional parameter must be two-element array.'
            );
         }

         $typeByOptionalKey[$key] = $typeAndDefault[0];
      }

      self::checkArray($array, $typeByRequiredKey, $typeByOptionalKey);

      foreach ($typeAndDefaultByOptionalKey as $key => $typeAndDefault)
      {
         if (!array_key_exists($key, $array))
         {
            $array[$key] = $typeAndDefault[1];
         }
      }
   }

   /*
    *
    */
   public static function checkType($v, $type)
   {
      if (!is_string($type))
      {
         throw new Exception('Received non-string for $type.');
      }

      switch ($type)
      {
       // Basic types.
       case 'array'   : $b = is_array($v)   ; break;
       case 'bool'    : // Fall through.
       case 'boolean' : $b = is_bool($v)    ; break;
       case 'float'   : $b = is_float($v)   ; break;
       case 'int'     : $b = is_int($v)     ; break;
       case 'null'    : $b = is_null($v)    ; break;
       case 'numeric' : $b = is_numeric($v) ; break;
       case 'object'  : $b = is_object($v)  ; break;
       case 'resource': $b = is_resource($v); break;
       case 'scalar'  : $b = is_scalar($v)  ; break;
       case 'string'  : $b = is_string($v)  ; break;

       // Combinations of basic types.
       case 'arrayOrString': $b = (is_array($v) || is_string($v)); break;

       // C-type character checks.
       case 'ctype_alnum' : $b = ctype_alnum($v) ; break;
       case 'ctype_alpha' : $b = ctype_alpha($v) ; break;
       case 'ctype_cntrl' : $b = ctype_cntrl($v) ; break;
       case 'ctype_digit' : $b = ctype_digit($v) ; break;
       case 'ctype_graph' : $b = ctype_graph($v) ; break;
       case 'ctype_lower' : $b = ctype_lower($v) ; break;
       case 'ctype_print' : $b = ctype_print($v) ; break;
       case 'ctype_punct' : $b = ctype_punct($v) ; break;
       case 'ctype_space' : $b = ctype_space($v) ; break;
       case 'ctype_upper' : $b = ctype_upper($v) ; break;
       case 'ctype_xdigit': $b = ctype_xdigit($v); break;

       // Basic types with condition.
       case 'character'       : $b = (is_string($v) && strlen($v) == 1); break;
       case 'nonNegativeInt'  : $b = (is_int($v)    && $v         >= 0); break;
       case 'negativeInt'     : $b = (is_int($v)    && $v         <  0); break;
       case 'positiveInt'     : $b = (is_int($v)    && $v         >  0); break;
       case 'nonNegativeFloat': $b = (is_float($v)  && $v         >= 0); break;
       case 'nonPositiveFloat': $b = (is_float($v)  && $v         <= 0); break;
       case 'negativeFloat'   : $b = (is_float($v)  && $v         <  0); break;
       case 'positiveFloat'   : $b = (is_float($v)  && $v         >  0); break;
       case 'nonEmptyString'  : $b = (is_string($v) && strlen($v) >  0); break;
       case 'nonEmptyArray'   : $b = (is_array($v)  && count($v)  >  0); break;

       // Basic types or null.
       case 'nullOrArray'   : $b = (is_null($v) || is_array($v)   ); break;
       case 'nullOrBool'    : $b = (is_null($v) || is_bool($v)    ); break;
       case 'nullOrInt'     : $b = (is_null($v) || is_int($v)     ); break;
       case 'nullOrFloat'   : $b = (is_null($v) || is_float($v)   ); break;
       case 'nullOrNumeric' : $b = (is_null($v) || is_numeric($v) ); break;
       case 'nullOrObject'  : $b = (is_null($v) || is_object($v)  ); break;
       case 'nullOrResource': $b = (is_null($v) || is_resource($v)); break;
       case 'nullOrScalar'  : $b = (is_null($v) || is_scalar($v)  ); break;
       case 'nullOrString'  : $b = (is_null($v) || is_string($v)  ); break;

       // Basic types with condition or null.
       case 'nullOrCharacter'       : $b = (is_null($v) || is_string($v) && strlen($v) == 1); break;
       case 'nullOrNonEmptyString'  : $b = (is_null($v) || is_string($v) && strlen($v) >  0); break;
       case 'nullOrNonEmptyArray'   : $b = (is_null($v) || is_array($v)  && count($v)  >  0); break;
       case 'nullOrPositiveInt'     : $b = (is_null($v) || is_int($v)    && $v         >  0); break;
       case 'nullOrNegativeInt'     : $b = (is_null($v) || is_int($v)    && $v         <  0); break;
       case 'nullOrNonPositiveInt'  : $b = (is_null($v) || is_int($v)    && $v         <= 0); break;
       case 'nullOrNonNegativeInt'  : $b = (is_null($v) || is_int($v)    && $v         >= 0); break;
       case 'nullOrPositiveFloat'   : $b = (is_null($v) || is_float($v)  && $v         >  0); break;
       case 'nullOrNegativeFloat'   : $b = (is_null($v) || is_float($v)  && $v         <  0); break;
       case 'nullOrNonPositiveFloat': $b = (is_null($v) || is_float($v)  && $v         <= 0); break;
       case 'nullOrNonNegativeFloat': $b = (is_null($v) || is_float($v)  && $v         >= 0); break;

       // Date strings.
       case 'date_yyyy-mm-dd': $b = self::checkDateString($v, 'yyyy-mm-dd'); break;
       case 'date_yyyy/mm/dd': $b = self::checkDateString($v, 'yyyy/mm/dd'); break;
       case 'date_dd-mm-yyyy': $b = self::checkDateString($v, 'dd-mm-yyyy'); break;
       case 'date_dd/mm/yyyy': $b = self::checkDateString($v, 'dd/mm/yyyy'); break;

       // Date strings or null.
       case 'nullOrDate_yyyy-mm-dd':
         $b = (is_null($v) || self::checkDateString($v, 'yyyy-mm-dd')); break;
       case 'nullOrDate_yyyy/mm/dd':
         $b = (is_null($v) || self::checkDateString($v, 'yyyy/mm/dd')); break;
       case 'nullOrDate_dd-mm-yyyy':
         $b = (is_null($v) || self::checkDateString($v, 'dd-mm-yyyy')); break;
       case 'nullOrDate_dd/mm/yyyy':
         $b = (is_null($v) || self::checkDateString($v, 'dd/mm/yyyy')); break;

       // Date-time strings.
       case 'Y-m-d H:i:s': $b = self::checkDatetimeString($v, 'Y-m-d H:i:s'); break;

       // Catch all.
       default: $b = (gettype($v) == 'object')? (get_class($v) == $type): false;
      }

      if (!$b)
      {
         throw new Exception
         (
            "Variable type check failed.  Expected '$type', received " .
            (
               (gettype($v) == 'object')?
               'object of class \'' . get_class($v) . '\'.':
               'variable of type \'' . gettype($v) . '\'.'
            )
         );
      }
   }

   /*
    *
    */
   public static function checkDateString($dateStr, $format = 'yyyy-mm-dd')
   {
      switch ($format)
      {
       case 'yyyy-mm-dd': $regEx = '/^(\d{4})-(\d{2})-(\d{2})$/'  ; $y = 1; $m = 2; $d = 3; break;
       case 'yyyy/mm/dd': $regEx = '/^(\d{4})\/(\d{2})\/(\d{2})$/'; $y = 1; $m = 2; $d = 3; break;
       case 'dd-mm-yyyy': $regEx = '/^(\d{2})-(\d{2})-(\d{4})$/'  ; $d = 1; $m = 2; $y = 3; break;
       case 'dd/mm/yyyy': $regEx = '/^(\d{2})\/(\d{2})\/(\d{4})$/'; $d = 1; $m = 2; $y = 3; break;
       default: throw new Exception("Unknown format string '$format'");
      }

      return
      (
         preg_match($regEx, $dateStr, $matches) &&
         (
            ($matches[$d] == '0000' && $matches[$m] == '00' && $matches[$y] == '00') ||
            checkdate($matches[$m], $matches[$d], $matches[$y])
         )
      );
   }

   /*
    *
    */
   public static function checkDatetimeString($datetimeStr, $format = 'Y-m-d H:i:s')
   {
      switch ($format)
      {
       case 'Y-m-d H:i:s':
         $regEx = '/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/';
         $y = 1; $m = 2; $d = 3; $h = 4; $i = 5; $s = 6;
         break;
       default:
         throw new Exception("Unknown format string '$format'");
      }

      return
      (
         (
            preg_match($regEx, $datetimeStr, $matches) &&
            checkdate($matches[$m], $matches[$d], $matches[$y]) &&
            (0 <= $matches[$h] && $matches[$h] <= 23) &&
            (0 <= $matches[$i] && $matches[$i] <= 59) &&
            (0 <= $matches[$s] && $matches[$s] <= 59)
         ) ||
         (
            // A zero date is allowed for debugging purposes
            // and for queries designed to include all.
            preg_match($regEx, $datetimeStr, $matches) &&
            $matches[$y] == 0 && $matches[$m] == 0 && $matches[$d] == 0 &&
            $matches[$h] == 0 && $matches[$i] == 0 && $matches[$s] == 0
         )
      );
   }

   /*
    *
    */
   public static function checkIntRangeInclusive($v, $min, $max)
   {
      if (!is_int($min) || !is_int($max) || $min > $max)
      {
         throw new Exception
         (
            "Invalid range given or not range parameters not integers." .
            " (min: $min, max: $max)."
         );
      }

      if (!is_int($v) || $v < $min || $v > $max)
      {
         throw new Exception
         (
            "Integer range check failed or value not integer." .
            " (min: $min, value: $v, max: $max)."
         );
      }
   }

   /*
    *
    */
   public static function checkFloatRangeInclusive($v, $min, $max)
   {
      if (!is_float($min) || !is_float($max) || $min > $max)
      {
         throw new Exception
         (
            "Invalid range given or not range parameters not floats." .
            " (min: $min, max: $max)."
         );
      }

      if (!is_float($v) || $v < $min || $v > $max)
      {
         throw new Exception
         (
            "Float range check failed or value not float." .
            " (min: $min, value: $v, max: $max)."
         );
      }
   }

   /*
    *
    */
   public static function checkExactMatch($v, $expectedValue)
   {
      if ($v !== $expectedValue)
      {
         throw new Exception
         (
            "Value '$v' is not identical to '$expectedValue' (values and types must be same)."
         );
      }
   }

   /*
    * @param $d {array}
    *    array
    *    (
    *       'year'  => <int>,
    *       'month' => <int>,
    *       'day'   => <int>
    *    );
    */
   public static function checkDateArray($d)
   {
      if
      (
         !array_key_exists('year' , $d) ||
         !array_key_exists('month', $d) ||
         !array_key_exists('day'  , $d) ||
         !checkDate($d['month'], $d['day'], $d['year']) // Note funny order: {m, d, y}.
      )
      {
         throw new Exception("Invalid date (y:{$d['year']}, m:{$d['month']}, d:{$d['day']}).");
      }
   }

   /*
    * @param $d {array}
    *    array
    *    (
    *       'year'  => <int>,
    *       'month' => <int>,
    *       'day'   => <int>
    *    );
    */
   public static function checkTimeArrayNoSeconds($t)
   {
      if (!array_key_exists('hour', $t) || !array_key_exists('minute', $t))
      {
         throw new Exception("Missing key 'hour' or 'minute' in time array.");
      }

      $h = $t['hour'  ];
      $m = $t['minute'];

      if ($h < 0 || $h > 23 || $m < 0 || $m > 59)
      {
         throw new Exception("Invalid time (h: $h, m:$m).");
      }
   }

   /*
    *
    */
   public static function checkMinLengthAndTextCharSet($str, $minLength)
   {
      assert('is_string($str)'   );
      assert('is_int($minLength)');

      $str = trim($str);

      if (strlen($str) < $minLength)
      {
         return false;
      }

      // Match any combination of alphabet characters
      // spaces and selected punctuation characters ("-", "'", "`").
      $nMatches = preg_match("/^[a-zA-Z\-\'\` ]*$/", $str);

      if ($nMatches === false)
      {
         throw new Exception('An error occurred during running of preg_match().');
      }

      return ($nMatches > 0);
   }

   /*
    *
    */
   public static function checkMinLengthAndExtendedTextCharSet($str, $minLength)
   {
      assert('is_string($str)'   );
      assert('is_int($minLength)');

      $str = trim($str);

      if (strlen($str) < $minLength)
      {
         return false;
      }

      // Match any combination of alphabet characters
      // spaces and selected punctuation characters ("-", "'", "`", ".", ",", ":", ";", "!").
      $nMatches = preg_match('/^[a-zA-Z\-\'\`\.,:;! ]*$/', $str);

      if ($nMatches === false)
      {
         throw new Exception('An error occurred during running of preg_match().');
      }

      return ($nMatches > 0);
   }

   /*
    *
    */
   public static function checkEmailAddress($str)
   {
      assert('is_string($str)');

      $e = new EmailAddressValidator();

      return $e->check_email_address($str);
   }

   /*
    *
    */
   public static function checkStringIsDecimalFloat($str)
   {
      $nMatches = preg_match('/^[0-9]*\.?[0-9]*$/', $str);

      if ($nMatches === false)
      {
         throw new Exception('An error occurred during running of preg_match().');
      }

      return ($nMatches > 0);
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _checkRequiredKeysAndTypes($array, $typeByRequiredKey)
   {
      foreach ($typeByRequiredKey as $key => $type)
      {
         if (!array_key_exists($key, $array))
         {
            throw new Exception("Required key '$key' does not exist in array.");
         }

         $value = $array[$key];

         if (is_array($type))
         {
            $type = self::checkArrayAndSetDefaults
            (
               $type,
               array('typeByRequiredKey' => 'array'                ),
               array('typeByOptionalKey' => array('array', array()))
            );

            self::checkArray($value, $type['typeByRequiredKey'], $type['typeByOptionalKey']);
            continue;
         }

         try
         {
            self::checkType($value, $type);
         }
         catch (Exception $e)
         {
            throw new Exception("Type check failed for required key '$key'.\n" . $e->getMessage());
         }
      }
   }

   /*
    *
    */
   private static function _checkOptionalKeysAndTypes($array, $typeByRequiredKey,$typeByOptionalKey)
   {
      $arrayExtra = array_diff_key($array, $typeByRequiredKey);

      foreach (array_keys($arrayExtra) as $key)
      {
         if (!array_key_exists($key, $typeByOptionalKey))
         {
            throw new Exception("Unexpected key '$key' found.");
         }

         $type  = $typeByOptionalKey[$key];
         $value = $array[$key];

         if (is_array($type))
         {
            $type = self::checkArrayAndSetDefaults
            (
               $type,
               array('typeByRequiredKey' => 'array'                ),
               array('typeByOptionalKey' => array('array', array()))
            );

            self::checkArray($value, $type['typeByRequiredKey'], $type['typeByOptionalKey']);
            continue;
         }

         try
         {
            self::checkType($value, $type);
         }
         catch (Exception $e)
         {
            throw new Exception("Type check failed for optional key '$key'.\n" . $e->getMessage());
         }
      }
   }

}

/*******************************************END*OF*FILE********************************************/
?>
