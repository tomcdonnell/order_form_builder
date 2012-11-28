/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "utilsTable.js"
*
* Project: GUI elements.
*
* Purpose: Utilities used in files in the /result/ directory.
*
* Author: Tom McDonnell 2008-05-18.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for utilities pertaining to HTML TABLE elements.
 */
UTILS.table = {};

// Namespace 'UTILS.table' functions. //////////////////////////////////////////////////////////////

/*
 * Build an HTML table cell element (TR or TD) containing text nodes separated by BR elements.
 * The BR elements are spliced into the string wherever '\n' characters are found.
 *
 * Eg. Given:
 *        arguments = ['h', 'Three\nline\nstring', {'class': 'heading'}],
 *     Returns
 *        TH({'class': 'heading'}, 'Three', BR(), 'line', BR(), 'string')
 *
 * @param hORd {String}
 *    'h' or 'd'.  Determines whether a TD or TH element is returned.
 *
 * @param nullOrStr {'nullOrString'}
 *    The string to splice.  Null is allowed since often values in
 *    rows returned from SQL querys contain nulls.  See example above.
 *
 * @param attributes {Object}
 *    Attributes object for the TH or TD element to be returned.
 */
UTILS.table.buildTCellWithBRs = function (hORd, nullOrStr, attributes)
{
   var f = 'UTILS.table.buildTCellWithBRs()';
   UTILS.checkArgs(f, arguments, [String, 'nullOrString', Object]);

   var cell;

   switch (hORd)
   {
    case 'h': cell = TH(attributes); break;
    case 'd': cell = TD(attributes); break;
    default: throw new Exception(f, "Expected 'h' or 'd'.  Received '" + hORd + "'.", '');
   }

   var lines = (nullOrStr === null)? []: nullOrStr.split('\n');

   for (var i = 0, len = lines.length; i < len; ++i)
   {
      $(cell).append(document.createTextNode(lines[i]));

      if (i + 1 < len)
      {
         $(cell).append(BR());
      }
   }

   return cell;
}

/*******************************************END*OF*FILE********************************************/
