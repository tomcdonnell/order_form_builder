/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "SelectorCalendar.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the SelectorCalendar object.
*
* Author: Tom McDonnell 2007-12-24.
*
\**************************************************************************************************/

/**
 * @param onChangeSelectedDateFunction
 *    Should accept (selectedY, selectedM, selectedD, selectorCalendarTableElem) as parameters.
 *
 * @param onClickCloseButtonFunction
 *    Useful if SelectorCalendar is to be used as a popup.
 *    Supply null if not using SelectorCalendar as popup, so the close button will not be displayed.
 */
function SelectorCalendar(initialDate, onChangeSelectedDateFunction, onClickCloseButtonFunction)
{
   var f = 'SelectorCalendar()';
   UTILS.checkArgs(f, arguments, [Date, Function, 'nullOrFunction']);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getTable         = function () {return _table        ;};
   this.getSelectedYear  = function () {return _selectedYear ;};
   this.getSelectedMonth = function () {return _selectedMonth;};
   this.getSelectedDay   = function () {return _selectedDay  ;};
   this.getSelectedDate  = function ()
   {
      return d =
      {
         year : _selectedYear ,
         month: _selectedMonth,
         day  : _selectedDay
      };
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setOnChangeSelectedDateFunction = function (newFunction)
   {
      var f = 'SelectorCalendar.setOnChangeSelectedDateFunction()';
      UTILS.checkArgs(f, arguments, [Function]);

      onChangeSelectedDateFunction = newFunction;
   };

   /*
    * Note that functions for setting year, month, and day separately
    * are not provided because the resulting selected date may not exist.
    */
   this.setSelectedDate = function (y, m, d)
   {
      var f = 'SelectorCalendar.setSelectedDate()';
      UTILS.checkArgs(f, arguments, [Number, Number, Number]);

      if (UTILS.date.dateExists(y, m, d))
      {
         _selectedYear  = y;
         _selectedMonth = m;
         _selectedDay   = d;

         _removeDayTableRows();
         _appendDayTableRows();
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

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onClickIncrementOrDecrementYear(ev)
   {
      try
      {
         var f = 'SelectorCalendar._onClickIncrementOrDecrementYear()';
         UTILS.checkArgs(f, arguments, [Object]);

         var button = ev.currentTarget;

         switch ($(button).attr('name'))
         {
          case 'yDecrement': --_visibleYear; break;
          case 'yIncrement': ++_visibleYear; break;
          default: throw new Exception('Unexpected value for button name.');
         }

         _removeDayTableRows();
         _visibleYearSquare.innerHTML = String(_visibleYear);
         _appendDayTableRows();
         _enableOrDisableReturnToSelectedDateButton();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onClickIncrementOrDecrementMonth(ev)
   {
      try
      {
         var f = 'SelectorCalendar._onClickIncrementOrDecrementMonth()';
         UTILS.checkArgs(f, arguments, [Object]);

         var button = ev.currentTarget;

         switch ($(button).attr('name'))
         {
          case 'mDecrement':
            if (_visibleMonth > 1) {--_visibleMonth;}
            else
            {
               --_visibleYear;
               _visibleYearSquare.innerHTML = String(_visibleYear);
               _visibleMonth = 12;
            }
            break;

          case 'mIncrement':
            if (_visibleMonth < 12) {++_visibleMonth;}
            else
            {
               ++_visibleYear;
               _visibleYearSquare.innerHTML = String(_visibleYear);
               _visibleMonth = 1;
            }
            break;

          default:
            throw new Exception('Unexpected value for button name.');
         }

         _removeDayTableRows();
         _visibleMonthSquare.innerHTML = UTILS.date.getMonthAbbrev(_visibleMonth);
         _appendDayTableRows();
         _enableOrDisableReturnToSelectedDateButton();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onClickReturnToSelectedDateButton(ev)
   {
      try
      {
         var f = 'SelectorCalendar._onClickReturnToSelectedDateButton()';
         UTILS.checkArgs(f, arguments, [Object]);

         _visibleYear  = _selectedYear;
         _visibleMonth = _selectedMonth;

         _removeDayTableRows();
         _visibleYearSquare.innerHTML  = String(_visibleYear);
         _visibleMonthSquare.innerHTML = String(UTILS.date.getMonthAbbrev(_visibleMonth));
         _appendDayTableRows();

         _enableOrDisableReturnToSelectedDateButton();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onClickDaySquare(ev)
   {
      try
      {
         var f = 'SelectorCalendar._onClickDaySquare()';
         UTILS.checkArgs(f, arguments, [Object]);

         _selectedDaySquare.removeAttribute('class');
         _selectedDaySquare = ev.currentTarget;
         _selectedDaySquare.setAttribute('class', 'selected');
         _selectedDay   = Number(_selectedDaySquare.innerHTML);
         _selectedMonth = _visibleMonth;
         _selectedYear  = _visibleYear;

         _enableOrDisableReturnToSelectedDateButton();

         // SetTimeout is used here so that the selected day square
         // will be highlighted prior to the calendar popup being closed.
         window.setTimeout
         (
            // The anonymous function below is created rather than passing
            // onChangeSelectedDateFunction directly to the setTimeout function because setTimeout
            // in IE does not allow parameters to be passed to the callback.
            function ()
            {
               onChangeSelectedDateFunction(_selectedYear, _selectedMonth, _selectedDay, _table);
            },
            100
         );
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Initialisation functions. ---------------------------------------------------------------//

   /*
    *
    */
   function _init(date)
   {
      var f = 'SelectorCalendar._init()';
      UTILS.checkArgs(f, arguments, [Date]);

      if (onClickCloseButtonFunction === null)
      {
         $(_closeTdAsButton).parent().css('display', 'none');
      }

      _appendDayTableRows();

      $(_closeTdAsButton ).click(onClickCloseButtonFunction       );
      $(_yDecrementButton).click(_onClickIncrementOrDecrementYear );
      $(_yIncrementButton).click(_onClickIncrementOrDecrementYear );
      $(_mDecrementButton).click(_onClickIncrementOrDecrementMonth);
      $(_mIncrementButton).click(_onClickIncrementOrDecrementMonth);
      $(_returnToSelectedDateButton).click(_onClickReturnToSelectedDateButton);

      _enableOrDisableReturnToSelectedDateButton();
   }

   /*
    *
    */
   function _enableOrDisableReturnToSelectedDateButton()
   {
      var f = 'SelectorCalendar._enableOrDisableReturnToSelectedDateButton()';
      UTILS.checkArgs(f, arguments, []);

      _returnToSelectedDateButton.disabled =
      (
         _visibleYear  == _selectedYear &&
         _visibleMonth == _selectedMonth
      );
   }

   /*
    * Append to the main table:
    *   A TR element for each week of the {_visibleMonth, _visibleYear}
    *   combination, containing a TD element for each day of the month.
    */
   function _appendDayTableRows()
   {
      var f = 'SelectorCalendar._appendDayTableRows()';
      UTILS.checkArgs(f, arguments, []);

      // Range of weekDay: [0, 6].
      // NOTE: JS month days start at 0, so decrement by 1.
      var weekDay = UTILS.date.getFirstDayOfMonth(_visibleYear, _visibleMonth);
      var weekNo  = UTILS.date.getFirstWeekOfMonth(_visibleYear, _visibleMonth);
      var weekRow = TR(TD({'class': 'weekNumber'}, String(++weekNo)));

      // Fill blank day squares (before beginning of month).
      var nDaysInPrevMonth =
      (
         (month = 1)? 31: UTILS.date.getNDaysInMonth(_visibleYear, _visibleMonth - 1)
      );
      var d = nDaysInPrevMonth - weekDay - 1;
      for (var wd = 0; wd < weekDay; ++wd)
      {
         $(weekRow).append(TD({'class': 'prevMonth'}, String(++d)));
      }

      // Fill month day squares.
      var nDaysInMonth      = UTILS.date.getNDaysInMonth(_visibleYear, _visibleMonth);
      var nWeekRowsAppended = 0;
      for (var d = 1; d <= nDaysInMonth; ++d)
      {
         daySquare = TD(String(d));
         $(daySquare).click(_onClickDaySquare);

         if (_visibleYear == _selectedYear && _visibleMonth == _selectedMonth && d == _selectedDay)
         {
            daySquare.setAttribute('class', 'selected');
            _selectedDaySquare = daySquare;
         }

         $(weekRow).append(daySquare);

         if (++weekDay == 7)
         {
            weekDay = 0;
            $(_tbody).append(weekRow);
            ++nWeekRowsAppended;
            weekRow = TR(TD({'class': 'weekNumber'}, String(++weekNo)));
         }
      }

      // Fill blank day squares (after end of month).
      var d = 0; 
      for (var wd = weekDay; wd < 7; ++wd)
      {
         $(weekRow).append(TD({'class': 'nextMonth'}, String(++d)));
      }

      $(_tbody).append(weekRow);
      ++nWeekRowsAppended;

      // If only four week rows have been appended...
      if (nWeekRowsAppended == 5)
      {
         // Append a fifth week row so that five weeks are displayed for all months.
         weekRow = TR(TD({'class': 'weekNumber'}, String(++weekNo)));
         for (var wd = 0; wd < 7; ++wd)
         {
            $(weekRow).append(TD({'class': 'nextMonth'}, String(++d)));
         }
         $(_tbody).append(weekRow);
      }
   }

   /*
    * Remove the rows of the table that contain the day squares (all but the top three rows).
    */
   function _removeDayTableRows()
   {
      var f = 'SelectorCalendar._removeDayTableRows()';
      UTILS.checkArgs(f, arguments, []);

      var rows  = _tbody.childNodes;
      var nRows = rows.length;

      for (r = 3; r < nRows; ++r)
      {
         $(rows[3]).remove();
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   // The date currently selected (not necessarily currently displayed).
   var _selectedYear  = initialDate.getFullYear();
   var _selectedMonth = initialDate.getMonth() + 1;
   var _selectedDay   = initialDate.getDate();

   // The year and month currently displayed.
   var _visibleYear   = _selectedYear;
   var _visibleMonth  = _selectedMonth;

   var _selectedDaySquare = null;

   var _visibleYearSquare  = TH({'class': 'year' }, String(_selectedYear));
   var _visibleMonthSquare = TH({'class': 'month'}, UTILS.date.getMonthAbbrev(_selectedMonth));

   var _mDecrementButton = INPUT({type: 'button', name: 'mDecrement' , value: '<'});
   var _mIncrementButton = INPUT({type: 'button', name: 'mIncrement' , value: '>'});
   var _yDecrementButton = INPUT({type: 'button', name: 'yDecrement' , value: '<'});
   var _yIncrementButton = INPUT({type: 'button', name: 'yIncrement' , value: '>'});

   var _closeTdAsButton = TD({'class': 'closeButton', title: 'cancel'}, 'X');

   var _returnToSelectedDateButton = INPUT
   (
      {type: 'button', value: '@', title: 'return to selected date'}
   );

   var _weekDayAttributes = {'class': 'day', width: String(100 / 8) + '%'};

   var _tbody = TBODY
   (
      // NOTE: IE6 requires that 'colspan' be camelCased to 'colSpan'.
      TR(TH({colSpan: 7}, 'Select a date'), _closeTdAsButton),
      TR
      (
         // NOTE: IE6 requires that 'rowspan' be camelCased to 'rowSpan'.
         TH({'class': 'week'  , rowSpan: 2}, 'Week', BR(), 'No.')                ,
         TH({'class': 'year' }, _yDecrementButton          ), _visibleYearSquare ,
         TH({'class': 'year' }, _yIncrementButton          )                     ,
         TH(                    _returnToSelectedDateButton)                     ,
         TH({'class': 'month'}, _mDecrementButton          ), _visibleMonthSquare,
         TH({'class': 'month'}, _mIncrementButton          )
      ),
      TR
      (
         TH(_weekDayAttributes, 'Sun'),
         TH(_weekDayAttributes, 'Mon'),
         TH(_weekDayAttributes, 'Tue'),
         TH(_weekDayAttributes, 'Wed'),
         TH(_weekDayAttributes, 'Thu'),
         TH(_weekDayAttributes, 'Fri'),
         TH(_weekDayAttributes, 'Sat')
      )
   );

   var _table = TABLE({'class': 'calendar'}, _tbody);

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init(initialDate);
}

/*******************************************END*OF*FILE********************************************/
