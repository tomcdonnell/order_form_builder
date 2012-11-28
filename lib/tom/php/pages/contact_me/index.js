/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "index.js"
*
* Project: Common pages - Contact Me.
*
* Purpose: Simple form validation
*
* Author: Tom McDonnell 2009-04-26.
*
\**************************************************************************************************/

// Globally executed code. /////////////////////////////////////////////////////////////////////////

window.addEventListener('load'  , onLoadWindow, false);

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function onLoadWindow(e)
{
   try
   {
      var f = 'onLoadWindow()';
      UTILS.checkArgs(f, arguments, [Event]);

      document.getElementById('submitButton').addEventListener('click', onClickSubmit, false);
   }
   catch (e)
   {
      UTILS.printExceptionToConsole(f, e);
   }
}

/*
 *
 */
function onClickSubmit(e)
{
   try
   {
      var f = 'onClickSubmit()';
      UTILS.checkArgs(f, arguments, [MouseEvent]);

      var inputs =
      {
         subject          : document.getElementById('subjectInput'          ),
         message          : document.getElementById('messageInput'          ),
         replyEmailAddress: document.getElementById('replyEmailAddressInput')
      };

      for each (var input in inputs)
      {
         if (input.value == '')
         {
            alert('The email will not be sent unless all fields contain text.');
            e.preventDefault();
            return;
         }
      }

      if (!UTILS.validator.checkEmailAddress(inputs.replyEmailAddress.value))
      {
         alert('The email will not be sent unless a valid return address is supplied.');
         e.preventDefault();
         return;
      }
   }
   catch (e)
   {
      UTILS.printExceptionToConsole(f, e);
   }
}

/*******************************************END*OF*FILE********************************************/
