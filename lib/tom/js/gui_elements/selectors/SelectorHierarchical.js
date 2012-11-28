/**************************************************************************************************\
*
* vim: ts=3 sw=3 et co=100 go-=b
*
* Filename: "SelectorHierarchical.js"
*
* Project: Common.
*
* Purpose: A series of dependent selectors.  Useful where selection hierarchies arise.
*
*          Eg. Country->State->CentreName->TeamName.
*
*          When a country is selected, an ajax request is made for the states of that country, and
*          when a state is selected, an ajax request is made for the centre names within that state
*          etc.
*
*          NOTE
*          ----
*          The first option in all selectors is assumed to be an instruction eg. 'Select state'.
*
* Author: Tom McDonnell 2010.
*
\**************************************************************************************************/

/*
 *
 */
function SelectorHierarchical(params)
{
   var f = 'SelectorHierarchical()';
   UTILS.checkArgs(f, arguments, [Object]);

   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    * This function should be called immediately after the SelectorHierarchical object has been
    * created.
    *
    * This function is not incorporated into the _init() function so that the
    * onReceiveOptionsSetFromServerFunction may refer to the SelectorHierarchical object if
    * necessary.  At the time the _init() function executes, the new SelectorHierarchical object
    * has not yet been returned.
    */
   this.startAutoSelectProcess = function ()
   {
      var f = 'SelectorHierarchical.startAutoSelectProcess()';
      UTILS.checkArgs(f, arguments, []);

      _state.ajaxParams.success = UTILS.ajax.createReceiveAjaxMessageFunction(
         'SelectorHierarchical', params.onAjaxFailureFunction, {
            getOptions: function (reply)
            {
               var f = 'SelectorHierarchical.onAjaxSuccessFunction()';
               UTILS.checkArgs(f, arguments, [Object]);

               var selector = _selectors[reply.selectorIndex];

               _replaceOptionsSetForSelector(reply.options, reply.selectorIndex);
               $(selector).attr('disabled', false);

               if (_state.onReceiveOptionsSetFromServerFunction !== null)
               {
                  _state.onReceiveOptionsSetFromServerFunction();
               }
            }
         }
      );

      $.ajax(_state.ajaxParams);
      _disableSelectorAndClearOptions(0, 'Loading...');
   }

   // Getters. --------------------------------------------------------------------------------//

   this.getSelectors = function () {return _selectors;};

   /*
    *
    */
   this.getSelectedValueBySelectorIndex = function ()
   {
      var f = 'SelectorHierarchical.getSelectedValueBySelectorIndex()';
      UTILS.checkArgs(f, arguments, []);

      var selectedValueBySelectorIndex = [];

      for (var selectorIndex = 0; selectorIndex < _selectors.length; ++selectorIndex)
      {
         var selector = _selectors[selectorIndex];
         selectedValueBySelectorIndex.push(selector[selector.selectedIndex].value);
      }

      return selectedValueBySelectorIndex;
   };

   /*
    * Selector indexes are assumed to be in order of least specific to most specific.
    */
   this.getMostSpecificSelectedValueAndSelectorIndex = function ()
   {
      var f = 'SelectorHierarchical.getMostSpecificSelectedValueAndSelectorIndex()';
      UTILS.checkArgs(f, arguments, []);

      for (var selectorIndex = 0; selectorIndex < _selectors.length; ++selectorIndex)
      {
         var selector = _selectors[selectorIndex];

         // If the selected option is the instruction option...
         if (selector.selectedIndex == 0)
         {
            break;
         }
      }

      if (selectorIndex == 0)
      {
         return o =
         {
            selectedValue: null,
            selectorIndex: null
         };
      }

      var previousSelectorIndex = selectorIndex - 1;
      var previousSelector      = _selectors[previousSelectorIndex];

      return o =
      {
         selectedValue: previousSelector.options[previousSelector.selectedIndex].value,
         selectorIndex: previousSelectorIndex
      };
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setOnReceiveOptionsSetFromServerFunction = function (nullOrFunction)
   {
      var f = 'SelectorHierarchical.setOnReceiveOptionsSetFromServerFunction()';
      UTILS.checkArgs(f, arguments, ['nullOrFunction']);

      _state.onReceiveOptionsSetFromServerFunction = nullOrFunction;
   };

   /*
    *
    */
   this.resetSelections = function (startSelectorIndex)
   {
      var f = 'SelectorHierarchical.resetSelections()';
      UTILS.checkArgs(f, arguments, ['nonNegativeInt']);

      var selector = _selectors[startSelectorIndex];

      if (selector.selectedIndex != 0)
      {
         selector.selectedIndex = 0;
         $(selector).change();
      }

      _state.valueToSelectWhenOptionsArriveBySelectorIndex = null;
   };

   /*
    *
    */
   this.selectOptionsFromValues = function (valueBySelectorIndex)
   {
      var f = 'SelectorHierarchical.selectOptionsFromValues()';
      UTILS.checkArgs(f, arguments, [Array]);

      if (valueBySelectorIndex.length < _selectors.length)
      {
         throw new Exception(f, 'Too few values supplied for selectors.', '');
      }

      var firstSelector = _selectors[0];
      var firstValue    = valueBySelectorIndex[0];

      if (firstValue === null)
      {
         // The caller has requested that the selected value be left unchanged.
         return;
      }

      UTILS.validator.checkType(firstValue, 'int');

      _state.valueToSelectWhenOptionsArriveBySelectorIndex = valueBySelectorIndex;

      if (_selectorHasOptionWithValue(firstSelector, String(firstValue)))
      {
         UTILS.DOM.selectOptionWithValue(firstSelector, String(firstValue));
      }
      else
      {
         // The options that the caller is trying to select may not have arrived from the server.
         // In this case select the first option.  The requested selection will be attempted again
         // when the next set of options arrives from the server.
         firstSelector.selectedIndex = 0;
      }

      $(firstSelector).change();
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onChangeSelector(e)
   {
      try
      {
         var f = 'SelectorHierarchical._onChangeSelector()';
         UTILS.checkArgs(f, arguments, [Object]);

         var nSelectors          = _selectors.length;
         var selector            = e.currentTarget;
         var selectorIndex       = _getSelectorIndexFromSelector(selector);
         var options             = selector.options;
         var selectedOptionIndex = selector.selectedIndex;
         var selectedOptionValue = selector.options[selectedOptionIndex].value;

         // If the selector is not the last selector...
         if (selectorIndex < _selectors.length - 1)
         {
            // If the selected option is the instruction option...
            if (selectedOptionValue == -1)
            {
               var lowestSelectorIndexToDisable = selectorIndex + 1;
            }
            else
            {
               var lowestSelectorIndexToDisable = selectorIndex + 2;

               _state.ajaxParams.data = JSON.stringify
               (
                  {
                     action: 'getOptions',
                     params:
                     {
                        selectorIndex: selectorIndex + 1,
                        parentId     : Number(selectedOptionValue)
                     }
                  }
               );

               $.ajax(_state.ajaxParams);
               _disableSelectorAndClearOptions(selectorIndex + 1, 'Loading...');
            }

            for (var i = lowestSelectorIndexToDisable; i < nSelectors; ++i)
            {
               _disableSelectorAndClearOptions(i, null);
            }
         }
         else
         {
            // Always call onFinishSelectionAndSubsequentAutoSelections() for the last selector.
            params.onFinishSelectionAndSubsequentAutoSelections(selectorIndex, selectedOptionValue);
         }

         if
         (
            selectedOptionValue                                   ==   -1 &&
            params.onFinishSelectionAndSubsequentAutoSelections  !== null
         )
         {
            params.onFinishSelectionAndSubsequentAutoSelections(selectorIndex, selectedOptionValue);
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Other private functions. ----------------------------------------------------------------//

   /*
    *
    */
   function _disableSelectorAndClearOptions(selectorIndex, selectedOptionText)
   {
      var f = 'SelectorHierarchical._disableSelectorAndClearOptions()';
      UTILS.checkArgs(f, arguments, ['nonNegativeInt', 'nullOrString']);

      if (selectedOptionText === null)
      {
         selectedOptionText = params.selectorsInfo[selectorIndex].disabledOptionText;
      }

      var selector = _selectors[selectorIndex];

      $(selector).html('');
      $(selector).attr('disabled', true);

      // NOTE
      // ----
      // Value -1 is used for the instruction option.
      // Value -2 is used for the disabled option.
      $(selector).append(OPTION({value: -2}, selectedOptionText));
   }

   /*
    *
    */
   function _replaceOptionsSetForSelector(options, selectorIndex)
   {
      var f = 'SelectorHierarchical._replaceOptionsSetForSelector()';
      UTILS.checkArgs(f, arguments, [Object, 'nonNegativeInt']);

      var nSelectors            = _selectors.length;
      var selector              = _selectors[selectorIndex];
      var previousSelectedIndex = selector.selectedIndex;
      $(selector).html('');

      for (var id in options)
      {
         $(selector).append(OPTION({value: id}, options[id]));
      }

      if (_state.valueToSelectWhenOptionsArriveBySelectorIndex !== null)
      {
         var valueToSelectAutomatically =
         (
            _state.valueToSelectWhenOptionsArriveBySelectorIndex[selectorIndex]
         );

         if (selectorIndex == _selectors.length - 1)
         {
            _state.valueToSelectWhenOptionsArriveBySelectorIndex = null;
         }
      }

      if (_state.autoSelectWhenOneOptionOnly && selector.options.length == 2)
      {
         // There is only one option to select (not including the instruction option).
         valueToSelectAutomatically = selector.options[1].value;
      }

      if (valueToSelectAutomatically !== null)
      {
         // Ensure valueToSelectAutomatically is String, not Int.
         valueToSelectAutomatically = String(valueToSelectAutomatically);

         if (_selectorHasOptionWithValue(selector, valueToSelectAutomatically))
         {
            UTILS.DOM.selectOptionWithValue(selector, valueToSelectAutomatically);
         }
         else
         {
            selector.selectedIndex = 0;
            params.onFinishSelectionAndSubsequentAutoSelections
            (
               selectorIndex, $(selector.options[0]).attr('value')
            );
         }
      }

      // If both the previous and the current selected options are the instruction option or the
      // disabled option, there is no need to trigger the onChange event.  Otherwise trigger it.
      if
      (
         (previousSelectedIndex != 0 || selector.selectedIndex != 0) ||
         (valueToSelectAutomatically === null && selectorIndex == _selectors.length - 1)
      )
      {
         $(selector).change();
      }
   }

   /*
    *
    */
   function _init()
   {
      var f = 'SelectorHierarchical._init()';
      UTILS.checkArgs(f, arguments, []);
      UTILS.validator.checkObject
      (
         params,
         {
            ajaxUrl                                     : 'string'        ,
            autoSelectWhenOneOptionOnly                 : 'bool'          ,
            onAjaxFailureFunction                       : 'function'      ,
            onFinishSelectionAndSubsequentAutoSelections: 'nullOrFunction',
            selectorsInfo                               : 'array'
         }
      );

      var selectorsInfo = params.selectorsInfo;

      for (var i = 0; i < selectorsInfo.length; ++i)
      {
         var selectorInfo = selectorsInfo[i];
         var selector     = SELECT
         (
            {name: 'selectorHierarchical_' + i, disabled: true},

            // NOTE
            // ----
            // Value -1 is used for the instruction option.
            // Value -2 is used for the disabled option.
            OPTION({value: -2}, selectorInfo.disabledOptionText)
         );

         UTILS.validator.checkObject(selectorInfo, {disabledOptionText: 'string'});

         $(selector).change(_onChangeSelector);
         _selectors.push(selector);
      }

      _state.autoSelectWhenOneOptionOnly = params.autoSelectWhenOneOptionOnly;
      _state.ajaxParams.data             = JSON.stringify
      (
         {
            action: 'getOptions',
            params: {selectorIndex: 0, parentId: null}
         }
      );
   }

   /*
    *
    */
   function _getSelectorIndexFromSelector(selector)
   {
      var f = 'SelectorHierarchical._getSelectorIndexFromSelector()';
      UTILS.checkArgs(f, arguments, [HTMLSelectElement]);

      var name            = $(selector).attr('name');
      var underscoreIndex = name.indexOf('_');
      var selectorIndex   = name.substr(underscoreIndex + 1);

      return Number(selectorIndex);
   }

   /*
    *
    */
   function _selectorHasOptionWithValue(selector, value)
   {
      var f = 'SelectorHierarchical._selectorHasOptionWithValue()';
      UTILS.checkArgs(f, arguments, [HTMLSelectElement, String]);

      var options = selector.options;

      for (var i = 0; i < options.length; ++i)
      {
         if (options[i].value == value)
         {
            return true;
         }
      }

      return false;
   }

   // Private variables. ----------------------------------------------------------------------//

   var self       = this;
   var _selectors = [];
   var _state     =
   {
      ajaxParams:
      {
         dataType: 'json'        ,
         type    : 'POST'        ,
         url     : params.ajaxUrl,
         success : null
      },
      autoSelectWhenOneOptionOnly                  : false,
      onReceiveOptionsSetFromServerFunction        : null ,
      valueToSelectWhenOptionsArriveBySelectorIndex: null
   };

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
