/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "utils_math.js"
*
* Project: Utilities.
*
* Purpose: General purpose mathematical utilities.
*
* @author: Tom McDonnell 2007.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for math utilities.
 */
UTILS.math = {};

// Namespace 'UTILS.math' functions. ///////////////////////////////////////////////////////////////

/**
 * Test whether a number is an integer.
 *
 * @param x {Number}
 */
UTILS.math.isInt = function (x)
{
   var f = 'UTILS.math.isInt()';
   UTILS.checkArgs(f, arguments, [Number]);

   return x % 1 == 0;
};

/**
 * Test whether a number is even.
 *
 * @param x {Number}
 */
UTILS.math.isEven = function (x)
{
   var f = 'UTILS.math.isEven()';
   UTILS.checkArgs(f, arguments, [Number]);

   return x % 2 == 0;
};

/**
 * Test whether a number is odd.
 *
 * @param x {Number}
 */
UTILS.math.isOdd = function (x)
{
   var f = 'UTILS.math.isOdd()';
   UTILS.checkArgs(f, arguments, [Number]);

   return x % 1 == 0 && x % 2 != 0
};

/*******************************************END*OF*FILE********************************************/

