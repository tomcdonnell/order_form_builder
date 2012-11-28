/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "prevent_delete_key_from_acting_as_back_button.js"
*
* Project: On-load scipts.
*
* Purpose: For preventing the backspace key from acting as the back button,
*          and so prevent accidental data loss when a user is editing form a form.
*
* @author: Tom McDonnell 2012-01-25.
*
\**************************************************************************************************/

$(document).ready
(
   function (ev)
   {
      try
      {
         var f = 'prevent_delete_key_from_acting_as_back_button.js.onDocumentReady()';
         UTILS.checkArgs(f, arguments, [Function]);

         var BKSP = 8;

         $('input, textarea').keydown
         (
            function (ev) {if (UTILS.getKeyCodeForEvent(ev) == BKSP) {ev.stopPropagation();}}
         );

         $(document).keydown
         (
            function (ev) {if (UTILS.getKeyCodeForEvent(ev) == BKSP) {ev.preventDefault();}}
         );
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }
);

/*******************************************END*OF*FILE********************************************/
