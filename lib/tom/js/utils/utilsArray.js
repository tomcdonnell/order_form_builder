/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "utilsArray.js"
*
* Project: Utilities.
*
* Purpose: Utilities concerning arrays.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for array utilities.
 */
UTILS.array = {};

// Namespace 'UTILS.array' functions. //////////////////////////////////////////////////////////////

/*
 *
 */
UTILS.array.compare = function (a, b)
{
   var f = 'UTILS.array.compare ()';
   UTILS.checkArgs(f, arguments, [Array, Array]);

   var len = a.length;
   if (b.length != len)
   {
      return false;
   }

   var type;
   for (var i = 0; i < len; ++i)
   {
      type = a[i].constructor;

      if (b[i].constructor == type)
      {
         switch (type)
         {
          case Number: // Fall through.
          case String: if (                     a[i] != b[i] ) return false; break;
          case Array : if (!UTILS.array.compare(a[i],   b[i])) return false; break;

          default:
            throw new Exception
            (
               f, 'Cannot compare objects of type ' + type + '.',
               'Reason: A normal comparison between two object references will return true only ' +
               'if the two references are to the same object.  If you wish to determine whether ' +
               'two object references point to different objects that are equivalent by some ' +
               'measure, then a specialised function must be used.'
            );
         }
      }
   }

   return true;
};

/*
 *
 */
UTILS.array.print2dArrayToConsole = function (a)
{
   var f = 'UTILS.array.print2dArrayToConsole()';
   UTILS.checkArgs(f, arguments, [Array]);

   console.info('+ 2d Array:');

   var r, c, len;
   for (r = 0, len = a.length; r < len; ++r)
   {
      console.info('| ', a[r]);
   }

   console.info('+');
};

/**
 * If array 'a' contains element 'e'
 *   return the index of the first occurrence of 'e' in 'a'.
 * Else
 *   return null.
 *
 * @param a {Array}
 * @param e {Defined}
 */
UTILS.array.findIndexOfElement = function (a, e)
{
   var f = 'UTILS.array.hasElement()';
   UTILS.checkArgs(f, arguments, [Array, 'Defined']);

   for (var i = 0, len = a.length; i < len; ++i)
   {
      if (a[i].constructor == e.constructor)
      {
         switch (e.constructor)
         {
          case String: // Fall through.
          case Number:
            if (a[i] == e) {return i;}
            break;
          case Array:
            if (UTILS.array.compare(a[i], e)) {return i;}
            break;
          default:
            throw new Exception
            (
               f, 'Cannot compare objects of type ' + e.constructor + '.',
               'Reason: A normal comparison between two object references will return true only ' +
               'if the two references are to the same object.  If you wish to determine whether ' +
               'two object references point to different objects that are equivalent by some ' +
               'measure, then a specialised function must be used.'
            );
         }
      }
   }

   return null;
};

/**
 * If array 'a' contains element 'e'
 *   return true.
 * Else
 *   return false.
 *
 * @param a {Array}
 * @param e {Defined}
 */
UTILS.array.hasElement = function (a, e)
{
   return (UTILS.array.findIndexOfElement(a, e) !== null);
};

/*
 *
 */
UTILS.array.clone = function (a)
{
   var b = new Array();

   for (var i = 0, len = a.length; i < len; ++i)
   {
      var e = a[i];

      b[i] =
      (
         (typeof e.clone != 'undefined')? e.clone():
         (
            (typeof e.cloneNode != 'undefined')? e.cloneNode(true):
            (
               (e.constructor == Array)? UTILS.array.clone(e): e
            )
         )
      );
   }

   return b;
}

/*
 *
 */
UTILS.array.merge = function (a, b)
{
   for (var i = 0, len = b.length; i < len; ++i)
   {
      a.push(b[i]);
   }

   return a;
};

/*
 *
 */
UTILS.array.unique = function (a)
{
   var b = [];

   for (var i = 0; i < a.length; ++i)
   {
      var e = a[i];

      if (!UTILS.array.hasElement(b, e))
      {
         b.push(e);
      }
   }

   return b;
};

/*******************************************END*OF*FILE********************************************/
