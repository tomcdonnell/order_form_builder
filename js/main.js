/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

$(document).ready
(
   function (ev)
   {
      try
      {
         var f = 'main.js onReady()';
         UTILS.checkArgs(f, arguments, ['function']);

         window.formUpdater = new FormUpdater(document.body, window.FORM_DEFINITION);
         window.formUpdater.attachEventListenersAndInitialiseState();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }
);
