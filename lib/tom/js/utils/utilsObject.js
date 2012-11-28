/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "utilsObject.js"
*
* Project: Utilities.
*
* Purpose: Utilities concerning objects.
*
* Author: Tom McDonnell 2008-12-07.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for object utilities.
 */
UTILS.object = {};

// Namespace 'UTILS.object' functions. /////////////////////////////////////////////////////////////

/*
 * Return an array containing the keys that are present in 'a' but not present in 'b'.
 */
UTILS.object.diff_key = function (a, b)
{
   var f = 'UTILS.object.diff_key()';
   UTILS.checkArgs(f, arguments, [Object, Object]);

   var keys = [];

   for (var key in a)
   {
      if (typeof b[key] == 'undefined')
      {
         keys.push(key);
      }
   }

   return keys;
};

/**
 * Compare each member of two objects.  If all are equal return true, else false.
 *
 * @param a {Object}
 * @param b {Object}
 */
UTILS.object.equals = function (a, b)
{
   var f = 'UTILS.object.equals()';
   UTILS.checkArgs(f, arguments, [Object, Object]);

   function membersOfFirstAreFoundInSecond(a, b)
   {
      for (key in a)
      {
         if (a[key] != b[key])
         {
            return false
         }
      }

      return true;
   }

   var objectsAreEqual =
   (
      membersOfFirstAreFoundInSecond(a, b) &&
      membersOfFirstAreFoundInSecond(b, a)
   );

   return objectsAreEqual;
};

/*
 * Intended to be functionally the same as the PHP function array_keys, but since
 * Javascript arrays do not have keys, it belongs in UTILS.object not UTILS.array.
 *
 * @param o {Object}
 */
UTILS.object.keys = function (o)
{
   var f = 'UTILS.object.keys()';
   UTILS.checkArgs(f, arguments, [Object]);

   var keys = [];

   for (var k in o)
   {
      keys.push(k);
   }

   return keys;
};

/*
 * Intended to be functionally the same as the PHP function array_values, but since
 * Javascript arrays do not have keys, it belongs in UTILS.object not UTILS.array.
 *
 * @param o {Object}
 */
UTILS.object.values = function (o)
{
   var f = 'UTILS.object.values()';
   UTILS.checkArgs(f, arguments, [Object]);

   var values = [];

   for (var k in o)
   {
      values.push(o[k]);
   }

   return values;
};

/*
 *
 */
UTILS.object.printToConsole = function (o)
{
   var f = 'UTILS.object.printToConsole()';
   UTILS.checkArgs(f, arguments, [Object]);

   console.group('object');

   for (var key in o)
   {
      console.debug(key, ': ', o[key]);
   }

   console.groupEnd();
};

/*
 *
 */
UTILS.object.length = function (o)
{
   var f = 'UTILS.object.length()';
   UTILS.checkArgs(f, arguments, [Object]);

   var n = 0;

   for (var key in o)
   {
      ++n;
   }

   return n;
};

/*
 *
 */
UTILS.object.valueOrZero = function (o, k)
{
   return (typeof o[k] == 'undefined')? 0: o[k];
};

/*
 *
 */
UTILS.object.valueOrBlank = function (o, k)
{
   return (typeof o[k] == 'undefined')? '': o[k];
};

/*
 *
 */
UTILS.object.valueOrDefault = function (o, k, d)
{
   return (typeof o[k] == 'undefined')? d: o[k];
};

/*******************************************END*OF*FILE********************************************/
