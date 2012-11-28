<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_misc.php"
*
* Project: Utilities.
*
* Purpose: Miscellaneous utilities.
*
* Author: Tom McDonnell 2008-06-28.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_misc
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
   public static function debugMsg()
   {  
   }

   /*
    * 
    */
   public static function errorMsg()
   {
   }

   /*
    *
    */
   public static function arrayValueOrNull($key, $array)
   {
      return (array_key_exists($key, $array))? $array[$key]: null;
   }

   /*
    *
    */
   public static function arrayValueOrZero($key, $array)
   {
      return (array_key_exists($key, $array))? $array[$key]: 0;
   }

   /*
    *
    */
   public static function arrayValueOrBlank($key, $array)
   {
      return (array_key_exists($key, $array))? $array[$key]: '';
   }

   /*
    *
    */
   public static function arrayValueOrDefault($key, $array, $default, $boolCastToInt = false)
   {
      if (array_key_exists($key, $array))
      {
         switch ($boolCastToInt)
         {
          case true : return (int)$array[$key];
          case false: return      $array[$key];
         }
      }

      return $default;
   }

   /*
    * Usage example:
    *    $bestSellingCarModelName = Utils_misc::switchAssign
    *    (
    *       $carManufacturer, array
    *       (
    *          'ford'   => 'falcon',
    *          'holden' => 'commodore'
    *       )
    *    );
    *
    * The above code is equivalent to:
    *    switch ($carManufacturer)
    *    {
    *     case 'ford'  : $bestSellingCarModelName = 'falcon'   ; break;
    *     case 'holden': $bestSellingCarModelName = 'commodore'; break;
    *     default      : throw new Exception("Unknown car manufacturer '$carManufacturer'.");
    *    }
    *
    * @param $defaultOutputValue {any type}
    *   * This parameter is optional.  If it is not supplied, then there will be no output value
    *     for the default case specified and so an exception will be thrown in the default case.
    */
   public static function switchAssign(
      $inputValue, $outputValueByInputValue, $defaultOutputValue = null
   )
   {
      if (array_key_exists($inputValue, $outputValueByInputValue))
      {
         return $outputValueByInputValue[$inputValue];
      }

      // Note on Use of func_num_args() Below
      // ------------------------------------
      // Function func_num_args() is used below instead of checking if $defaultOutputValue is its
      // default value as set in this function's parameters list.  This is done so that the default
      // value of $defaultOutputValue may be used as an actual default output value and not just as
      // an indicator that an exception should be thrown in the default case.
      if (func_num_args() == 3)
      {
         return $defaultOutputValue;
      }

      throw new Exception
      (
         "Case '$inputValue' not handled in switchAssign and no default supplied."
      );
   }
}

/*******************************************END*OF*FILE********************************************/
?>
