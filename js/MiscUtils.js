/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
var MiscUtils =
{
   /*
    *
    */
   displayFailureMessage: function (message, boolRemoveAfterDelay)
   {
      var f = 'MiscUtils.displayFailureMessage';
      UTILS.checkArgs(f, arguments, ['string', 'bool']);

      var failureMessageDiv = $('#failure-message-div');
      var failureMessageP   = $(failureMessageDiv).find('p')[0];
  
      $(failureMessageP  ).html(message);
      $(failureMessageDiv).show();
  
      if (boolRemoveAfterDelay)
      {
         $(failureMessageDiv).addClass('transient');
         window.setTimeout
         (
            MiscUtils.onExpireFailureMessageDisplayPeriod,
            Config.FAILURE_MESSAGE_TIMEOUT_MS
         );
      }
      else
      {
         $(failureMessageDiv).removeClass('transient');
      }
   },

   /*
    *
    */
   onExpireFailureMessageDisplayPeriod: function ()
   {
      try
      {
         var f = 'MiscUtils.onExpireFailureMessageDisplayPeriod(e)';
         // In Firefox this function will be called with a single Number parameter,
         // but in IE this function will be called with no parameters.

         var failureMessageDivJq = $('#failure-message-div');
         
         if (failureMessageDivJq.hasClass('transient')) {failureMessageDivJq.fadeOut();}
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }
};
