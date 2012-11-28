/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SelectorDate.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the SelectorDate object.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/*
 *
 */
function SelectorDate()
{
   var f = 'SelectorDate()';
   UTILS.checkArgs(f, arguments, []);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getSelectedYear  = function () {return ySelector.selectedIndex + minYear;};
   this.getSelectedMonth = function () {return mSelector.selectedIndex + 1;      };
   this.getSelectedDay   = function () {return dSelector.selectedIndex + 1;      };

   /*
    *
    */
   this.getSelectedDate = function ()
   {
      return d =
      {
         year : ySelector.selectedIndex + minYear,
         month: mSelector.selectedIndex + 1,
         day  : dSelector.selectedIndex + 1
      };
   };

   /*
    *
    */
   this.getSelectors = function ()
   {
      return s =
      {
         year : ySelector,
         month: mSelector,
         day  : dSelector
      };
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    * NOTE: Functions for setting year, month, and day separately are not
    *       provided because the resulting selected date may not exist.
    */
   this.setSelectedDate = function (y, m, d)
   {
      var f = 'SelectorDate.setSelectedDate()';
      UTILS.checkArgs(f, arguments, [Number, Number, Number]);

      if (UTILS.date.dateExists(y, m, d))
      {
         ySelector.selectedIndex = y - minYear;
         mSelector.selectedIndex = m - 1;
         dSelector.selectedIndex = d - 1;
      }
      else
      {
         throw new Exception
         (
            f, 'Invalid date.',
            '{year: ' + y + ', month: ' + m + ', day: ' + d + '}.'
         );
      }
   };

   /*
    *
    */
   this.setSelectedDateToMinimum = function ()
   {
      var f = 'SelectorDate.setSelectedDateToMinimum()';
      UTILS.checkArgs(f, arguments, []);

      ySelector.selectedIndex = 0;
      mSelector.selectedIndex = 0;
      dSelector.selectedIndex = 0;
   };

   /*
    *
    */
   this.setSelectedDateToMaximum = function ()
   {
      var f = 'SelectorDate.setSelectedDateToMaximum()';
      UTILS.checkArgs(f, arguments, []);

      ySelector.selectedIndex = ySelector.options.length - 1;
      mSelector.selectedIndex = mSelector.options.length - 1;
      dSelector.selectedIndex = dSelector.options.length - 1;
   };

   /*
    *
    */
   this.setSelectableYearRange = function (minY, maxY)
   {
      var f = 'SelectorDate.setSelectableYearRange()';
      UTILS.checkArgs(f, arguments, ['nullOrPositiveInt', 'nullOrPositiveInt']);
      UTILS.assert(f, 0, minY === null || maxY === null || minY > 0 && minY <= maxY);

      var prevSelectedYearIndex = ySelector.selectedIndex;
      var prevSelectedYear      = minYear + prevSelectedYearIndex;

      if (minY !== null) {minYear = minY;}
      if (maxY !== null) {maxYear = maxY;}

      initYearSelector(prevSelectedYear);
   };

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      var f = 'SelectorDate.setDisabled()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      ySelector.disabled = bool;
      mSelector.disabled = bool;
      dSelector.disabled = bool;
   };

   // Other public functions. -----------------------------------------------------------------//

   /*
    *
    */
   this.selectedDateEquals = function (y, m, d)
   {
      return bool =
      (
         0 == UTILS.date.compare
         (
            y, m, d,
            this.getSelectedYear(),
            this.getSelectedMonth(),
            this.getSelectedDay()
         )
      );
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Object listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function onChangeYear(e)
   {
      try
      {
         var f = 'SelectorDate.onChangeYear()';
         UTILS.checkArgs(f, arguments, [Object]);

         if (mSelector.selectedIndex == 1)
         {
            dSelector.options[28].disabled = !UTILS.date.isLeapYear
            (
               ySelector.selectedIndex + minYear
            );
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
   function onChangeMonth(e)
   {
      try
      {
         var f = 'SelectorDate.onChangeMonth()';
         UTILS.checkArgs(f, arguments, [Object]);

         var n = UTILS.date.getNDaysInMonth
         (
            ySelector.selectedIndex + minYear,
            mSelector.selectedIndex + 1
         );

         dSelector.options[28].disabled = n < 29;
         dSelector.options[29].disabled = n < 30;
         dSelector.options[30].disabled = n < 31;
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
      var f = 'SelectorDate.init()';
      UTILS.checkArgs(f, arguments, []);

      var today = new Date();

      maxYear     = today.getFullYear();
      minYear     = maxYear - 10;
      mSelectorJq = $(mSelector);
      dSelectorJq = $(dSelector);

      initYearSelector(null);

      for (var m = 1; m <= 12; ++m)
      {
         mSelectorJq.append(OPTION({value: m}, UTILS.date.getMonthAbbrev(m)));
      }

      for (var d = 1; d <= 31; ++d)
      {
         dSelectorJq.append(OPTION({value: d}, String(d)));
      }

      ySelector.selectedIndex = maxYear - minYear;
      mSelector.selectedIndex = today.getMonth();
      dSelector.selectedIndex = today.getDate() - 1;

      $(ySelector).change(onChangeYear );
      $(mSelector).change(onChangeMonth);
   }

   /*
    * Initialise the year selector for the year-range [minYear, maxYear].
    *
    * @param selectedYear {nullOrPositiveInt}
    *    Year to select if it falls within the new range.
    *    Specifying a year outside the new range does not cause an exception to
    *    allow the previously selected year to be passed when the year-range changes.
    */
   function initYearSelector(selectedYear)
   {
      var f = 'SelectorDate.initYearSelector()';
      UTILS.checkArgs(f, arguments, ['nullOrPositiveInt']);

      var ySelectorJq = $(ySelector);
      ySelectorJq.html('');

      for (var y = minYear; y <= maxYear; ++y)
      {
         ySelectorJq.append(OPTION({value: y}, String(y)));
      }

      if (selectedYear !== null && minYear <= selectedYear && selectedYear <= maxYear)
      {
         ySelector.selectedIndex = selectedYear - minYear;
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var maxYear;
   var minYear;

   var ySelector = SELECT({name: 'year' });
   var mSelector = SELECT({name: 'month'});
   var dSelector = SELECT({name: 'day'  });

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   init();
}

/*******************************************END*OF*FILE********************************************/
