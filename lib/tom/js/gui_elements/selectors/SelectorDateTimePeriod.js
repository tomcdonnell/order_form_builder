/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SelectorDateTimePeriod.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the SelectorDateTimePeriod object.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/*
 *
 */
function SelectorDateTimePeriod()
{
   var f = 'SelectorDateTimePeriod()';
   UTILS.checkArgs(f, arguments, []);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.getSelectedPeriod = function ()
   {
      var f = 'SelectorDateTimePeriod.getSelectedPeriod()';
      UTILS.checkArgs(f, arguments, []);

      var sDate = sDateSelector.getSelectedDate();
      var sTime = sTimeSelector.getSelectedTime();
      var fDate = fDateSelector.getSelectedDate();
      var fTime = fTimeSelector.getSelectedTime();

      return p =
      {
         sYear  : sDate.year  ,
         sMonth : sDate.month ,
         sDay   : sDate.day   ,
         sHour  : sTime.hour  ,
         sMinute: sTime.minute,
         sSecond: sTime.second,
         fYear  : fDate.year  ,
         fMonth : fDate.month ,
         fDay   : fDate.day   ,
         fHour  : fTime.hour  ,
         fMinute: fTime.minute,
         fSecond: fTime.second
      };
   };

   /*
    *
    */
   this.getSelectors = function ()
   {
      var f = 'SelectorDateTimePeriod.getSelectors()';
      UTILS.checkArgs(f, arguments, []);

      var sDate = sDateSelector.getSelectors();
      var sTime = sTimeSelector.getSelectors();
      var fDate = fDateSelector.getSelectors();
      var fTime = fTimeSelector.getSelectors();

      return s =
      {
         sYear  : sDate.year  ,
         sMonth : sDate.month ,
         sDay   : sDate.day   ,
         sHour  : sTime.hour  ,
         sMinute: sTime.minute,
         sSecond: sTime.second,
         fYear  : fDate.year  ,
         fMonth : fDate.month ,
         fDay   : fDate.day   ,
         fHour  : fTime.hour  ,
         fMinute: fTime.minute,
         fSecond: fTime.second
      };
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setSelectedPeriod = function (sY, sM, sD, sH, sMin, sS, fY, fM, fD, fH, fMin, fS)
   {
      var f = 'SelectorDateTimePeriod.setSelectedPeriod()';
      UTILS.checkArgs
      (
         f, arguments,
         [
            Number, Number, Number, Number, Number, Number,
            Number, Number, Number, Number, Number, Number
         ]
      );

      var dateComparison = UTILS.date.compare(sY, sM, sD, fY, fM, fD);

      if (dateComparison > 0)
      {
         throw new Exception(f, 'Attempted to set invalid period (sDate > fDate).', '');
      }

      if (dateComparison == 0 && UTILS.time.compare(sH, sMin, sS, fH, fMin, fS) > 0)
      {
         throw new Exception(f, 'Attempted to set invalid period (dates equal, sTime > fTime).','');
      }

      sDateSelector.setSelectedDate(sY, sM  , sD);
      sTimeSelector.setSelectedTime(sH, sMin, sS);
      fDateSelector.setSelectedDate(fY, fM  , fD);
      fTimeSelector.setSelectedTime(fH, fMin, fS);
   };

   /*
    *
    */
   this.setSelectedPeriodToMaximum = function ()
   {
      var f = 'SelectorDateTimePeriod.setSelectedPeriodToMaximum()';
      UTILS.checkArgs(f, arguments, []);

      sDateSelector.setSelectedDateToMinimum();
      sTimeSelector.setSelectedTimeToMinimum();
      fDateSelector.setSelectedDateToMaximum();
      fTimeSelector.setSelectedTimeToMaximum();
   };

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      var f = 'SelectorDateTimePeriod.setDisabled()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      sDateSelector.setDisabled(bool);
      sTimeSelector.setDisabled(bool);
      fDateSelector.setDisabled(bool);
      fTimeSelector.setDisabled(bool);
   };

   /*
    *
    */
   this.setSelectableYearRange = function (minY, maxY)
   {
      var f = 'SelectorDateTimePeriod.setSelectableYearRange()';
      UTILS.checkArgs(f, arguments, ['nullOrPositiveInt', 'nullOrPositiveInt']);
      UTILS.assert(f, 0, minY === null || maxY === null || minY > 0 && minY <= maxY);

      sDateSelector.setSelectableYearRange(minY, maxY);
      fDateSelector.setSelectableYearRange(minY, maxY);
   };

   // Other public functions. -----------------------------------------------------------------//

   /*
    *
    */
   this.selectedPeriodEquals = function (sY, sM, sD, sH, sMin, sS, fY, fM, fD, fH, fMin, fS)
   {
      var f = 'SelectorDateTimePeriod.selectorPeriodEquals()';
      UTILS.checkArgs
      (
         f, arguments,
         [
            Number, Number, Number, Number, Number, Number,
            Number, Number, Number, Number, Number, Number
         ]
      );

      return bool =
      (
         sDateSelector.selectedDateEquals(sY, sM  , sD) &&
         sTimeSelector.selectedTimeEquals(sH, sMin, sS) &&
         fDateSelector.selectedDateEquals(fY, fM  , fD) &&
         fTimeSelector.selectedDateEquals(fH, fMin, sS)
      );
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function onChangeSdate(e)
   {
      try
      {
         var f = 'SelectorDateTimePeriod.onChangeSdate()';
         UTILS.checkArgs(f, arguments, [Object]);

         var sDate          = sDateSelector.getSelectedDate();
         var fDate          = fDateSelector.getSelectedDate();
         var dateComparison = UTILS.date.compare
         (
            sDate.year, sDate.month, sDate.day, fDate.year, fDate.month, fDate.day
         );

         // If the start date is after the finish date...
         if (dateComparison > 0)
         {
            // Set the finish date to equal the start date.
            fDateSelector.setSelectedDate(sDate.year, sDate.month, sDate.day);
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
   function onChangeFdate()
   {
      try
      {
         var f = 'SelectorDateTimePeriod.onChangeFdate()';
console.debug(f, 'e');
         UTILS.checkArgs(f, arguments, [Object]);

         var sDate          = sDateSelector.getSelectedDate();
         var fDate          = fDateSelector.getSelectedDate();
         var dateComparison = UTILS.date.compare
         (
            sDate.year, sDate.month, sDate.day, fDate.year, fDate.month, fDate.day
         );
console.debug(f, fDate.year, fDate.month, fDate.day, sDate.year, sDate.month, sDate.day);
console.debug(f, 'dateComparison: ', dateComparison);

         // If the start date is after the finish date...
         if (dateComparison > 0)
         {
            // Set the start date to equal the finish date.
            sDateSelector.setSelectedDate(fDate.year, fDate.month, fDate.day);
         }
console.debug(f, 'x');
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function onChangeStime(e)
   {
      try
      {
         var f = 'SelectorDateTimePeriod.onChangeStime()';
         UTILS.checkArgs(f, arguments, [Object]);

         var sDate = sDateSelector.getSelectedDate();
         var fDate = fDateSelector.getSelectedDate();
         var dateComparison = UTILS.date.compare
         (
            sDate.year, sDate.month, sDate.day, fDate.year, fDate.month, fDate.day
         );

         if (dateComparison != 0)
         {
            return;
         }

         var sTime          = sTimeSelector.getSelectedTime();
         var fTime          = fTimeSelector.getSelectedTime();
         var timeComparison = UTILS.time.compare
         (
            sTime.hour, sTime.minute, sTime.second, fTime.hour, fTime.minute, fTime.second
         );

         // If the start time is after the finish time...
         if (timeComparison > 0)
         {
            // Set the finish time to equal the start time.
            fTimeSelector.setSelectedTime(sTime.hour, sTime.minute, sTime.second);
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
         var f = 'SelectorDateTimePeriod.onChangeFtime()';
         UTILS.checkArgs(f, arguments, [Object]);

         var sDate          = sDateSelector.getSelectedDate();
         var fDate          = fDateSelector.getSelectedDate();
         var dateComparison = UTILS.date.compare
         (
            sDate.year, sDate.month, sDate.day, fDate.year, fDate.month, fDate.day
         );

         if (dateComparison != 0)
         {
            return;
         }

         var sTime          = sTimeSelector.getSelectedTime();
         var fTime          = fTimeSelector.getSelectedTime();
         var timeComparison = UTILS.time.compare
         (
            fTime.hour, fTime.minute, fTime.second, sTime.hour, sTime.minute, sTime.second
         );

         // If the start time is after the finish time...
         if (timeComparison > 0)
         {
            // Set the start time to equal the finish time.
            sTimeSelector.setSelectedTime(fTime.hour, fTime.minute, fTime.second);
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
      var f = 'SelectorDateTimePeriod.init()';
      UTILS.checkArgs(f, arguments, []);

      var sDate = sDateSelector.getSelectors();
      var sTime = sTimeSelector.getSelectors();
      var fDate = fDateSelector.getSelectors();
      var fTime = fTimeSelector.getSelectors();

      $(sDate.year  ).change(onChangeSdate);
      $(sDate.month ).change(onChangeSdate);
      $(sDate.day   ).change(onChangeSdate);
      $(sTime.hour  ).change(onChangeStime);
      $(sTime.minute).change(onChangeStime);
      $(sTime.second).change(onChangeStime);
      $(fDate.year  ).change(onChangeFdate);
      $(fDate.month ).change(onChangeFdate);
      $(fDate.day   ).change(onChangeFdate);
      $(fTime.hour  ).change(onChangeFtime);
      $(fTime.minute).change(onChangeFtime);
      $(fTime.second).change(onChangeFtime);

      $(sDate.year  ).attr('name', 'sYear'  );
      $(sDate.month ).attr('name', 'sMonth' );
      $(sDate.day   ).attr('name', 'sDay'   );
      $(sTime.hour  ).attr('name', 'sHour'  );
      $(sTime.minute).attr('name', 'sMinute');
      $(sTime.second).attr('name', 'sSecond');
      $(fDate.year  ).attr('name', 'fYear'  );
      $(fDate.month ).attr('name', 'fMonth' );
      $(fDate.day   ).attr('name', 'fDay'   );
      $(fTime.hour  ).attr('name', 'fHour'  );
      $(fTime.minute).attr('name', 'fMinute');
      $(fTime.second).attr('name', 'fSecond');

      fTimeSelector.setSelectedTime(23, 59, 59);
   }

   // Public variables. /////////////////////////////////////////////////////////////////////////

   var sDateSelector = new SelectorDate();
   var sTimeSelector = new SelectorTime();
   var fDateSelector = new SelectorDate();
   var fTimeSelector = new SelectorTime();

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   init();
}

/*******************************************END*OF*FILE********************************************/
