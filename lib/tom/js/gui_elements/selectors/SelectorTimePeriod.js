/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "SelectorTimePeriod.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the SelectorTimePeriod object.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/*
 *
 */
function SelectorTimePeriod()
{
   var f = 'SelectorTimePeriod()';
   UTILS.checkArgs(f, arguments, []);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.getSelectedPeriod = function ()
   {
      var p =
      {
         start : sTimeSelector.getSelectedTime(),
         finish: fTimeSelector.getSelectedTime()
      };

      return p;
   };

   /*
    *
    */
   this.getSelectors = function ()
   {
      var s =
      {
         start : sTimeSelector.getSelectors(),
         finish: fTimeSelector.getSelectors()
      };

      return s;
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setSelectedPeriod = function (sH, sM, fH, fM)
   {
      var f = 'SelectorTimePeriod.setSelectedPeriod()';
      UTILS.checkArgs(f, arguments, [Number, Number, Number, Number]);

      if (UTILS.time.compare(sH, sM, 0, fH, fM, 0) > 0)
      {
         throw new Exception(f, 'Attempted to set invalid period.', '');
      }

      sTimeSelector.setSelectedTime(sH, sM);
      fTimeSelector.setSelectedTime(fH, fM);
   };

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      sTimeSelector.setDisabled(bool);
      fTimeSelector.setDisabled(bool);
   };

   // Other public functions. -----------------------------------------------------------------//

   /*
    *
    */
   this.selectedPeriodEquals = function (sH, sM, fH, fM)
   {
      var bool =
      (
         sTimeSelector.selectedTimeEquals(sH, sM) &&
         fTimeSelector.selectedTimeEquals(fH, fM)
      );

      return bool;
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function onChangeStime(e)
   {
      try
      {
         var f = 'SelectorTimePeriod.onChangeStime()';
         UTILS.checkArgs(f, arguments, [Object]);

         var sTime = sTimeSelector.getSelectedTime();
         var fTime = fTimeSelector.getSelectedTime();

         // If the start time is after the finish time...
         if ((sTime.hour > fTime.hour) || (sTime.hour == fTime.hour && sTime.minute > fTime.minute))
         {
            // Set the finish time to equal the start time.
            fTimeSelector.setSelectedTime(sTime.hour, sTime.minute);
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function onChangeFtime()
   {
      try
      {
         var f = 'SelectorTimePeriod.onChangeFtime()';
         UTILS.checkArgs(f, arguments, [Object]);

         var sTime = sTimeSelector.getSelectedTime();
         var fTime = fTimeSelector.getSelectedTime();

         // If the start time is after the finish time...
         if ((fTime.hour > sTime.hour) || (sTime.hour == fTime.hour && fTime.minute > sTime.minute))
         {
            // Set the start time to equal the finish time.
            sTimeSelector.setSelectedTime(fTime.hour, fTime.minute);
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function init()
   {
      var f = 'SelectorTimePeriod.init()';
      UTILS.checkArgs(f, arguments, []);

      var sSelectors = sTimeSelector.getSelectors();
      var fSelectors = fTimeSelector.getSelectors();

      sSelectors.hour.change(onChangeStime);
      fSelectors.hour.change(onChangeFtime);
      sSelectors.minute.change(onChangeStime);
      fSelectors.minute.change(onChangeFtime);
   }

   // Public variables. /////////////////////////////////////////////////////////////////////////

   var sTimeSelector = new SelectorTime();
   var fTimeSelector = new SelectorTime();

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   init();
}

/*******************************************END*OF*FILE********************************************/
