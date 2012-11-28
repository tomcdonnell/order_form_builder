/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "utilsAjax.js"
*
* Project: Utilities.
*
* Purpose: Utilities concerning the Document Object Model (DOM).
*
* Dependencies: jQuery.
*
* Author: Tom McDonnell 2011-02-16.
*
\**************************************************************************************************/

// Namespace 'UTILS' variables. ////////////////////////////////////////////////////////////////////

/**
 * Namespace for AJAX utilities.
 */
UTILS.ajax = {};

// Namespace 'UTILS.ajax' functions. ///////////////////////////////////////////////////////////////

/*
 * @param onFailureFunction {Function}
 *   Function prototype: function (message, boolRemoveAfterShortDelay);
 */
UTILS.ajax.createReceiveAjaxMessageFunction = function
(
   objectName, onFailureFunction, responseFunctionByAction
)
{
   var f = 'UTILS.ajax.createReceiveAjaxMessageFunction()';
   UTILS.checkArgs(f, arguments, [String, Function, Object]);

   return function (msg, textStatus, jqXHR)
   {
      try
      {
         var f = objectName + '._receiveAjaxMessage()';
         UTILS.checkArgs(f, arguments, [Object, String, Object]);
         UTILS.validator.checkObject(msg, {action: 'string', success: 'bool', reply: 'Defined'});

         var action  = msg.action;
         var reply   = msg.reply;
         var success = msg.success;

         if (action == 'checkLogin' && success === false)
         {
            onFailureFunction(reply, false);
            return;
         }

         if (typeof responseFunctionByAction[action] == 'undefined')
         {
            throw new Exception
            (
               f, 'No response function defined for action "' + action + '".', ''
            );
         }

         if (!success)
         {
            if (reply.constructor !== String)
            {
               throw new Exception
               (
                  f, 'The type of msg.reply should be a string if msg.success is false.', ''
               );
            }

            onFailureFunction(reply, true);
            return;
         }

         responseFunctionByAction[action](reply);
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   };
};

/*******************************************END*OF*FILE********************************************/
