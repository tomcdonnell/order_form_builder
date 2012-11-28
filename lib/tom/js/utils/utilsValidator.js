/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "UtilsValidator.js"
*
* Project: Utilities.
*
* Purpose: Utilities concerning validation.
*
* Author: Tom McDonnell 2008-10-04.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for validation functions.
 */
UTILS.validator = {};

// Namespace 'UTILS.validator' functions. //////////////////////////////////////////////////////////

/*
 *
 */
UTILS.validator.checkObject = function (o, typeByRequiredKey, typeByOptionalKey)
{
   var f = 'UTILS.validator.checkObject()';
   UTILS.assert(f, 0, arguments.length == 2 || arguments.length == 3);
   UTILS.assert(f, 1, typeByRequiredKey.constructor == Object && o !== null);
   UTILS.assert(f, 2, arguments.length == 2 || typeByOptionalKey.constructor == Object);

   if (typeof typeByOptionalKey == 'undefined')
   {
      typeByOptionalKey = {};
   }

   // Check required keys and types.
   for (var key in typeByRequiredKey)
   {
      var type = typeByRequiredKey[key];

      if (typeof o[key] == 'undefined')
      {
         throw new Exception(f, "Required key '" + key + "' does not exist in object.", '');
      }

      try {UTILS.validator.checkType(o[key], type);}
      catch (e)
      {
         console.error(f, "Unexpected type for key '" + key + "'.", '');
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Check optional keys and types.
   var keysExtra = UTILS.object.diff_key(o, typeByRequiredKey);
   for (var i = 0, len = keysExtra.length; i < len; ++i)
   {
      var key = keysExtra[i];

      if (typeof typeByOptionalKey[key] == 'undefined')
      {
         throw new Exception(f, "Unexpected key '" + key + "' found.", '');
      }

      try {UTILS.validator.checkType(o[key], typeByOptionalKey[key]);}
      catch (e)
      {
         console.error(f, "Unexpected type for key '" + key + "'.", '');
         UTILS.printExceptionToConsole(f, e);
      }
   }
};

/*
 *
 */
UTILS.validator.checkObjectAndSetDefaults = function
(
   o, typeByRequiredKey, typeAndDefaultByOptionalKey
)
{
   var f = 'UTILS.validator.checkObjectAndSetDefaults()';
   UTILS.assert(f, 0, arguments.length == 2 || arguments.length == 3);
   UTILS.assert(f, 1, typeByRequiredKey.constructor == Object);
   UTILS.assert(f, 2, arguments.length == 2 || typeAndDefaultByOptionalKey.constructor == Object);

   var typeByOptionalKey = {};

   for (key in typeAndDefaultByOptionalKey)
   {
      var typeAndDefault = typeAndDefaultByOptionalKey[key];

      if (typeAndDefault.constructor != Array || typeAndDefault.length != 2)
      {
         throw new Exception
         (
            'Type and default value for optional parameter must be two-element array.'
         );
      }

      typeByOptionalKey[key] = typeAndDefault[0];
   }

   UTILS.validator.checkObject(o, typeByRequiredKey, typeByOptionalKey);

   for (key in typeAndDefaultByOptionalKey)
   {
      var typeAndDefault = typeAndDefaultByOptionalKey[key];

      if (typeof o[key] == 'undefined')
      {
         o[key] = typeAndDefault[1];
      }
   }

   return o;
};

/*
 *
 */
UTILS.validator.checkType = function (v, type)
{
   // NOTE: Must not call UTILS.checkArgs from here as UTILS.checkArgs calls this function
   var f = 'UTILS.validator.checkType()';
   UTILS.assert(f, 0, arguments.length == 2);
   UTILS.assert(f, 1, typeof v != 'undefined');
   UTILS.assert(f, 2, type.constructor == String);

   if (v === null && !(type == 'Defined' || type.substr(0, 4) == 'null'))
   {
      throw new Exception(f, "Expected non-null type '" + type + "'.  Received null.", '');
   }

   var b;

   switch (type)
   {
    // Special type for compatibility with UTILS.checkArgs().
    case 'Defined': b = (typeof v != 'undefined'); break;

    // Null.
    case 'null': b = v === null; break;

    // Basic types.
    case 'array'   : b = (v.constructor == Array                  ); break;
    case 'bool'    : b = (v.constructor == Boolean                ); break;
    case 'char'    : b = (v.constructor == String && v.length == 1); break;
    case 'float'   : b = (v.constructor == Number                 ); break;
    case 'function': b = (v.constructor == Function               ); break;
    case 'int'     : b = (v.constructor == Number && v %    1 == 0); break;
    case 'object'  : b = (v.constructor == Object                 ); break;
    case 'string'  : b = (v.constructor == String                 ); break;

    // Basic types with condition.
    case 'character'       : b = (v.constructor == String && v.length == 1       ); break;
    case 'nonEmptyString'  : b = (v.constructor == String && v.length > 0        ); break;
    case 'nonEmptyArray'   : b = (v.constructor == Array  && v.length > 0        ); break;
    case 'positiveInt'     : b = (v.constructor == Number && v >  0 && v % 1 == 0); break;
    case 'negativeInt'     : b = (v.constructor == Number && v <  0 && v % 1 == 0); break;
    case 'nonNegativeInt'  : b = (v.constructor == Number && v >= 0 && v % 1 == 0); break;
    case 'nonPositiveInt'  : b = (v.constructor == Number && v <= 0 && v % 1 == 0); break;
    case 'positiveFloat'   : b = (v.constructor == Number && v >  0              ); break;
    case 'negativeFloat'   : b = (v.constructor == Number && v <  0              ); break;
    case 'nonNegativeFloat': b = (v.constructor == Number && v >= 0              ); break;
    case 'nonPositiveFloat': b = (v.constructor == Number && v <= 0              ); break;

    // Basic types or null.
    case 'nullOrArray'     : b = (v === null || v.constructor == Array               ); break;
    case 'nullOrBool'      : b = (v === null || v.constructor == Boolean             ); break;
    case 'nullOrFloat'     : b = (v === null || v.constructor == Number              ); break;
    case 'nullOrFunction'  : b = (v === null || v.constructor == Function            ); break;
    case 'nullOrObject'    : b = (v === null || v.constructor == Object              ); break;
    case 'nullOrString'    : b = (v === null || v.constructor == String              ); break;
    case 'nullOrEvent'     : b = (v === null || v.constructor == Event               ); break;
    case 'nullOrMouseEvent': b = (v === null || v.constructor == MouseEvent          ); break;
    case 'nullOrInt'       : b = (v === null || v.constructor == Number && v % 1 == 0); break;

    // HTML elements or null.
    case 'nullOrHTMLCanvasElement': b = (v === null || v.constructor == HTMLCanvasElement); break;

    // Basic types with condition or null.
    case 'nullOrPositiveInt'     : b = (v===null || v.constructor==Number && v> 0 && v%1==0); break;
    case 'nullOrNegativeInt'     : b = (v===null || v.constructor==Number && v< 0 && v%1==0); break;
    case 'nullOrNonNegativeInt'  : b = (v===null || v.constructor==Number && v>=0 && v%1==0); break;
    case 'nullOrNonPositiveInt'  : b = (v===null || v.constructor==Number && v<=0 && v%1==0); break;
    case 'nullOrNonEmptyString'  : b = (v === null || v.constructor == String && v.length>0); break;
    case 'nullOrNonEmptyArray'   : b = (v === null || v.constructor == Array  && v.length>0); break;
    case 'nullOrPositiveFloat'   : b = (v === null || v.constructor == Number && v >  0    ); break;
    case 'nullOrNegativeFloat'   : b = (v === null || v.constructor == Number && v <  0    ); break;
    case 'nullOrNonNegativeFloat': b = (v === null || v.constructor == Number && v >= 0    ); break;
    case 'nullOrNonPositiveFloat': b = (v === null || v.constructor == Number && v <= 0    ); break;

    // Miscellaneous.
    case 'digitString':b = (v.constructor == String && /^\d+$/.test(v)); break;

    default: throw new Exception(f, "Unknown type '" + type + "'.", '');
   }

   if (!b)
   {
      throw new Exception
      (
         f,
         "Variable type check failed.  Expected '" + type + "', received '" + v +
         "' with constructor '" + v.constructor + "'.", ''
      );
   }
}

/*
 *
 */
UTILS.validator.checkIntRangeInclusive = function (v, min, max)
{
   UTILS.checkArgs(f, arguments, ['int', 'int', 'int']);
   UTILS.assert(f, 0, min <= max);

   if (v < min || v > max)
   {
      throw new Exception
      (
         f, 'Integer range check failed.', '(min: ' + min + ', value: ' + v + ', max: ' + max + ').'
      );
   }
}

/*
 *
 */
UTILS.validator.checkExactMatch = function (v, expectedValue)
{
   if (v !== expectedValue)
   {
      throw new Exception
      (
         f, 'Values supplied are not identical.', '(values and types must be same).'
      );
   }
}

/*
 *
 */
UTILS.validator.validateInteger = function (str)
{
   var f = 'UTILS.validator.validateInteger()';
   UTILS.checkArgs(f, arguments, [String]);

   var strOrFalse =
   (
      (isNaN(str) || str % 1 != 0)? false: UTILS.string.removeLeadingZeros(UTILS.string.trim(str))
   );

   return strOrFalse;
};

/*
 *
 */
UTILS.validator.validatePositiveInteger = function (str)
{
   var f = 'UTILS.validator.validatePositiveInteger()';
   UTILS.checkArgs(f, arguments, [String]);

   var vstr = UTILS.validator.validateInteger(str);

   return (vstr === false)? false: (vstr < 0)? false: vstr;
};

/*
 *
 */
UTILS.validator.checkMinLengthAndTextCharSet = function (str, minLength)
{
   var f = 'UTILS.validator.checkMinLengthAndTextCharSet()';
   UTILS.checkArgs(f, arguments, [String, Number]);
   UTILS.assert(f, 0, minLength >= 0);

   var tstr = UTILS.string.trim(str);

   if (tstr.length < minLength)
   {
      return false;
   }

   // Match any combination of alphabet characters
   // spaces and selected punctuation characters ("-", "'", "`").
   var regEx = new RegExp('^[a-zA-Z\-\'\` ]*$');

   return regEx.test(str);
};

/*
 *
 */
UTILS.validator.checkMinLengthAndExtendedTextCharSet = function (str, minLength)
{
   var f = 'UTILS.validator.checkMinLengthAndExtendedTextCharSet()';
   UTILS.checkArgs(f, arguments, [String, Number]);
   UTILS.assert(f, 0, minLength >= 0);

   var tstr = UTILS.string.trim(str);

   if (tstr.length < minLength)
   {
      return false;
   }

   // Match any combination of alphabet characters
   // spaces and selected punctuation characters ("-", "'", "`", ".", ",", ":", ";", "!").
   var regEx = new RegExp('^[a-zA-Z\-\'\`\.,:;! ]*$');

   return regEx.test(tstr);
};

/*
 *
 */
UTILS.validator.checkEmailAddress = function (str)
{
   var f = 'UTILS.validator.checkEmailAddress()';
   UTILS.checkArgs(f, arguments, [String]);

   var regEx = new RegExp
   (
      "^\\w+([\\.-]?\\w+)*@\\w+([\\.-]?\\w+)*\.(\\w{2}|" +
      "(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum))$"
   );

   return regEx.test(str);
};

/*******************************************END*OF*FILE********************************************/
