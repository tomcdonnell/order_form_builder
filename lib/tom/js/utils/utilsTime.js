/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "utilsTime.js"
*
* Project: Utilities.
*
* Purpose: Functions relating to time.
*
* Author: Tom McDonnell 2008-12-06
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for time utilities.
 */
UTILS.time = {};

// Namespace 'UTILS.time' functions. ///////////////////////////////////////////////////////////////

/*
 * If A < B return -1
 * If A = B return  0
 * If A > B return +1
 *
 * NOTE: Casts to number are done to avoid problems when numeric strings are compared.
 *       Eg. ('7' < '11') will evaluate to false.
 */
UTILS.time.compare = function (hA, mA, sA, hB, mB, sB)
{
   var f = 'UTILS.time.compare()';
   UTILS.checkArgs(f, arguments, [Number, Number, Number, Number, Number, Number]);

   return c =
   (
      (hA == hB)?
      (
         (mA == mB)?
         (
            (sA == sB)?
            0:
            ((Number(sA) > Number(sB))? 1: -1)
         ):
         ((Number(mA) > Number(mB))? 1: -1)
      ):
      ((Number(hA) > Number(hB))? 1: -1)
   );
};

/*******************************************END*OF*FILE********************************************/
