/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "SelectorTriStateCheckbox.js"
*
* Project: General.
*
* Purpose: A GUI object designed to work similarly to a multi-selector, but without the user having
*          to hold shift or control in order to make multiple selections.  A TriStateSelector is
*          displayed to the left of every option, to enable the SelectorTriStateCheckbox object
*          to be used with the SelectorColumnBasedHierarchical object.
*
*          Operation
*          ----------
*          Each option is a div containing a TriStateSelector span on the left, and text on the
*          right.  Options have six states, since the three checked-states of the TriStateCheckbox
*          ('checked', 'unchecked', and 'partiallyChecked') are independent of the two selected-
*          states of the option ('selected', and 'unselected').
*              Note that clicking on the TriStateSelector span will change the option's checked-
*          state, but will not change the option's selected-state.  Similarly, clicking on the part
*          of the option div not occupied by the TriStateSelector will change the selected-state
*          but will not change the checked-state.
*
*          Collapsible Option Ids
*          ----------------------
*          For an explanation of collapsible option ids, see the comments in the file
*          SelectorColumnBasedHierarchical.js
*
* Author: Tom McDonnell 2010-09-03.
*
\**************************************************************************************************/

// Object definition. //////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function SelectorTriStateCheckbox(mainDiv, optionsInfo)
{
   var f = 'SelectorTriStateCheckbox()';
   UTILS.checkArgs(f, arguments, [HTMLDivElement, Array]);

   // Privileged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getDiv = function () {return mainDiv;};

   /*
    *
    */
   this.getSelectedOptionIdsCollapsed = function ()
   {
      var f = 'SelectorTriStateCheckbox.getSelectedOptionIdsCollapsed()';
      UTILS.checkArgs(f, arguments, []);

      var selectedOptionIdsCollapsed = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         var optionInfo = optionsInfo[i];

         if (optionInfo.selected)
         {
            selectedOptionIdsCollapsed.push(optionInfo.optionIdCollapsed);
         }
      }

      return selectedOptionIdsCollapsed;
   };

   /*
    *
    */
   this.getFullyCheckedOptionIds = function ()
   {
      var f = 'SelectorTriStateCheckbox.getFullyCheckedOptionIds()';
      UTILS.checkArgs(f, arguments, []);

      var checkedOptionIds = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         var optionInfo = optionsInfo[i];

         if (optionInfo.checkedState == 'checked')
         {
            checkedOptionIds.push(optionInfo.optionId);
         }
      }

      return checkedOptionIds;
   };

   /*
    *
    */
   this.getPartiallyCheckedOptionIds = function ()
   {
      var f = 'SelectorTriStateCheckbox.getPartiallyCheckedOptionIds()';
      UTILS.checkArgs(f, arguments, []);

      var partiallyCheckedOptionIds = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         var optionInfo = optionsInfo[i];

         if (optionInfo.checkedState == 'partiallyChecked')
         {
            partiallyCheckedOptionIds.push(optionInfo.optionId);
         }
      }

      return partiallyCheckedOptionIds;
   };

   /*
    *
    */
   this.getCheckedStateFromOptionId = function (optionId)
   {
      var f = 'SelectorTriStateCheckbox.getCheckedStateFromOptionId()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex      = self.getOptionIndexFromOptionId(optionId);
      var triStateCheckbox = _guiElements.triStateCheckboxes[optionIndex];

      return triStateCheckbox.getState();
   };

   /*
    *
    */
   this.getCheckedStateFromOptionIdCollapsed = function (optionIdCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.getCheckedStateFromOptionIdCollapsed()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex      = self.getOptionIndexFromOptionIdCollapsed(optionIdCollapsed);
      var triStateCheckbox = _guiElements.triStateCheckboxes[optionIndex];

      return triStateCheckbox.getState();
   };

   /*
    *
    */
   this.getOptionIdsCollapsed = function ()
   {
      var f = 'SelectorTriStateCheckbox.getOptionIdsCollapsed()';
      UTILS.checkArgs(f, arguments, []);

      var optionIdsCollapsed = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         optionIdsCollapsed.push(optionsInfo[i].optionIdCollapsed);
      }

      return optionIdsCollapsed;
   };

   /*
    *
    */
   this.getOptionIds = function ()
   {
      var f = 'SelectorTriStateCheckbox.getOptionIds()';
      UTILS.checkArgs(f, arguments, []);

      var optionIds = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         optionIds.push(optionsInfo[i].optionId);
      }

      return optionIds;
   };

   /*
    *
    */
   this.getOptionTexts = function ()
   {
      var f = 'SelectorTriStateCheckbox.getOptionTexts()';
      UTILS.checkArgs(f, arguments, []);

      var optionTexts = [];

      for (var i = 0; i < optionsInfo.length; ++i)
      {
         optionTexts.push(optionsInfo[i].text);
      }

      return optionTexts;
   };

   /*
    *
    */
   this.getOptionIdFromOptionIndex = function (optionIndex)
   {
      var f = 'SelectorTriStateCheckbox.getOptionIdFromOptionIndex()';
      UTILS.checkArgs(f, arguments, [Number]);

      if (!(0 <= optionIndex && optionIndex < optionsInfo.length))
      {
         throw new Exception(f, 'Option index out of range.', 'optionIndex = ' + optionIndex);
      }

      var optionInfo = optionsInfo[optionIndex];

      return optionInfo.optionId;
   };

   /*
    *
    */
   this.getOptionIdCollapsedFromOptionIndex = function (optionIndex)
   {
      var f = 'SelectorTriStateCheckbox.getOptionIdCollapsedFromOptionIndex()';
      UTILS.checkArgs(f, arguments, [Number]);

      if (!(0 <= optionIndex && optionIndex < optionsInfo.length))
      {
         throw new Exception(f, 'Option index out of range.', 'optionIndex = ' + optionIndex);
      }

      var optionInfo = optionsInfo[optionIndex];

      return optionInfo.optionIdCollapsed;
   };

   /*
    *
    */
   this.getOptionIndexFromOptionIdCollapsed = function (optionIdCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.getOptionIndexFromOptionIdCollapsed()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex = _state.optionIndexByOptionIdCollapsed[optionIdCollapsed];

      if (optionIndex === undefined)
      {
         throw new Exception
         (
            f, 'No option index found for optionIdCollapsed "' + optionIdCollapsed + '".', ''
         );
      }

      return optionIndex;
   };

   /*
    *
    */
   this.getOptionIndexFromOptionId = function (optionId)
   {
      var f = 'SelectorTriStateCheckbox.getOptionIndexFromOptionId()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex = _state.optionIndexByOptionId[optionId];

      if (optionIndex === undefined)
      {
         throw new Exception(f, 'No option index found for optionId "' + optionId + '".', '');
      }

      return optionIndex;
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setCheckedStateForOptionAtIndex = function (optionIndex, newState)
   {
      var f = 'SelectorTriStateCheckbox.setCheckedStateForOptionAtIndex()';
      UTILS.checkArgs(f, arguments, [Number, String]);

      if (optionIndex < 0 || optionIndex >= optionsInfo.length)
      {
         throw new Exception('Option index "' + optionIndex + '" out of range.');
      }

      var optionInfo         = optionsInfo[optionIndex];
      var triStateCheckboxes = _guiElements.triStateCheckboxes;

      if (optionInfo.isHeading && newState.checkedState != 'unchecked')
      {
         throw new Exception
         (
            f, 'Attempted to set checked state of heading option to other than "unchecked".', ''
         );
      }

      triStateCheckboxes[optionIndex].setState(newState);
      optionInfo.checkedState = newState;
   };

   /*
    *
    */
   this.setSelectedStateForOptionAtIndex = function (optionIndex, boolSelected)
   {
      var f = 'SelectorTriStateCheckbox.setSelectedStateForOptionAtIndex()';
      UTILS.checkArgs(f, arguments, [Number, Boolean]);

      var optionInfo = optionsInfo[optionIndex];
      var optionDiv  = $(mainDiv).find('div')[optionIndex];

      if (!optionInfo.userSelectable)
      {
         throw new Exception('Attempted to set the selected state of a non-userSelectable option.');
      }

      switch (boolSelected)
      {
       case true : $(optionDiv).addClass('selected')   ; break;
       case false: $(optionDiv).removeClass('selected'); break;
      }

      optionInfo.selected = boolSelected;
   };

   /*
    *
    */
   this.setCheckedStates = function (optionIdsCollapsed, newState)
   {
      var f = 'SelectorTriStateCheckbox.setCheckedStates()';
      UTILS.checkArgs(f, arguments, [Array, String]);

      for (var i = 0; i < optionIdsCollapsed.length; ++i)
      {
         var optionIndex = self.getOptionIndexFromOptionIdCollapsed(optionIdsCollapsed[i]);
         var optionInfo  = optionsInfo[optionIndex];

         if (!optionInfo.isHeading)
         {
            self.setCheckedStateForOptionAtIndex(optionIndex, newState);
         }
      }
   };

   // Simple boolean functions. ---------------------------------------------------------------//

   /*
    *
    */
   this.optionIsSelectable = function (optionIdCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.optionIsSelectable()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex = self.getOptionIndexFromOptionIdCollapsed(optionIdCollapsed);
      var optionInfo  = optionsInfo[optionIndex];

      return optionInfo.userSelectable;
   };

   /*
    *
    */
   this.optionIsHeading = function (optionIdCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.optionIsHeading()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIndex = self.getOptionIndexFromOptionIdCollapsed(optionIdCollapsed);
      var optionInfo  = optionsInfo[optionIndex];

      return optionInfo.isHeading;
   };

   // Other privileged functions. -------------------------------------------------------------//

   /*
    *
    */
   this.addOptions = function (newOptionsInfo)
   {
      var f = 'SelectorTriStateCheckbox.addOptions()';
      UTILS.checkArgs(f, arguments, [Array]);

      _validateOptionsInfo(newOptionsInfo, true);

      for (var i = 0; i < newOptionsInfo.length; ++i)
      {
         var newOptionInfo  = newOptionsInfo[i];
         var newOptionDiv   = _createOptionDivMatchingOptionInfo(newOptionInfo);
         optionsInfo.push(newOptionInfo);
         var newOptionIndex = optionsInfo.length - 1;
         $(mainDiv).append(newOptionDiv);
         _state.optionIndexByOptionId[newOptionInfo.optionId] = newOptionIndex;
         _state.optionIndexByOptionIdCollapsed[newOptionInfo.optionIdCollapsed] = newOptionIndex;

         $(newOptionDiv).show();
      }
   };

   /*
    *
    */
   this.hideOptions = function (optionIdsCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.hideOptions()';
      UTILS.checkArgs(f, arguments, [Array]);

      var optionDivs = $(mainDiv).find('div');

      for (var i = 0; i < optionIdsCollapsed.length; ++i)
      {
         var optionIndex = self.getOptionIndexFromOptionIdCollapsed(optionIdsCollapsed[i]);

         if (optionsInfo[optionIndex].userSelectable)
         {
            self.setSelectedStateForOptionAtIndex(optionIndex, false);
         }

         $(optionDivs[optionIndex]).hide();
      }
   };

   /*
    *
    */
   this.revealHiddenOptions = function (optionIdsCollapsed)
   {
      var f = 'SelectorTriStateCheckbox.revealHiddenOptions()';
      UTILS.checkArgs(f, arguments, [Array]);

      var optionDivs = $(mainDiv).find('div');

      for (var i = 0; i < optionIdsCollapsed.length; ++i)
      {
         var optionIndex = self.getOptionIndexFromOptionIdCollapsed(optionIdsCollapsed[i]);
         var optionDiv   = optionDivs[optionIndex];

         if ($(optionDiv).css('display') != 'none')
         {
            throw new Exception
            (
               f, 'Attempted to reveal non-hidden option.',
               'optionIdCollapsed: "' + optionIdsCollapsed[i] + '"'
            );
         }

         $(optionDivs[optionIndex]).show();
      }
   };

   /*
    *
    */
   this.deleteAllOptionsAndClearState = function ()
   {
      var f = 'SelectorTriStateCheckbox.deleteAllOptionsAndClearState()';
      UTILS.checkArgs(f, arguments, []);

      optionsInfo = [];

      _init();
   };

   /*
    *
    */
   this.updateTriStateCheckboxHeights  = function ()
   {
      var f = 'SelectorTriStateCheckbox.updateTriStateCheckboxHeights()';
      UTILS.checkArgs(f, arguments, []);

      var triStateCheckboxes = _guiElements.triStateCheckboxes;

      for (var i = 0; i < triStateCheckboxes.length; ++i)
      {
         triStateCheckboxes[i].updateHeight();
      }
   };

   /*
    *
    */
   this.setDisabledForAllInputs = function (bool)
   {
      var f = 'SelectorTriStateCheckbox.setDisabledForAllInputs()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      var triStateCheckboxes = _guiElements.triStateCheckboxes;

      for (var i = 0; i < triStateCheckboxes.length; ++i)
      {
         var triStateCheckbox = triStateCheckboxes[i];
         var optionDiv        = $(triStateCheckbox.getSpan()).parent();

         // NOTE
         // ----
         // The disabled state of triStateCheckboxes
         // in nonUserCheckable options should never change.
         if (!$(optionDiv).hasClass('nonUserCheckable'))
         {
            triStateCheckboxes[i].setDisabled(bool);
         }
      }
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onClickOptionDiv(e)
   {
      try
      {
         var f = 'SelectorTriStateCheckbox._onClickOptionDiv()';
         UTILS.checkArgs(f, arguments, [Object]);

         // Note that e.target may be the span surrounding the tri-state checkbox image span.
         var optionDiv   = (e.target.tagName == 'SPAN')? $(e.target).parent()[0]: e.target;
         var optionIndex = UTILS.DOM.countPreviousSiblings(optionDiv);
         var optionInfo  = optionsInfo[optionIndex];

         self.setSelectedStateForOptionAtIndex(optionIndex, !optionInfo.selected);
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onClickTriStateCheckboxImageSpan(e)
   {
      try
      {
         var f = 'SelectorTriStateCheckbox._onClickTriStateCheckboxImageSpan()';
         UTILS.checkArgs(f, arguments, [Object]);

         var optionDiv        = $(e.target).parent().parent()[0];
         var optionIndex      = UTILS.DOM.countPreviousSiblings(optionDiv);
         var triStateCheckbox = _guiElements.triStateCheckboxes[optionIndex];

         optionsInfo[optionIndex].checkedState = triStateCheckbox.getState();

         // Ensure checked-state and selected-state are independent by preventing the click
         // event from propagating to the option div that encloses the TriStateCheckbox span.
         e.stopPropagation();
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
   function _init()
   {
      var f = 'SelectorTriStateCheckbox._init()';
      UTILS.checkArgs(f, arguments, []);

      $(mainDiv).empty();
      $(mainDiv).addClass('selectorTriStateCheckbox');
      $(mainDiv).css('overflow-x', 'hidden');
      $(mainDiv).css('overflow-y', 'scroll');

      _guiElements.triStateCheckboxes       = [];
      _state.optionIndexByOptionId          = {};
      _state.optionIndexByOptionIdCollapsed = {};

      self.addOptions(optionsInfo);
   }

   /*
    *
    */
   function _createOptionDivMatchingOptionInfo(optionInfo)
   {
      var f = 'SelectorTriStateCheckbox._createOptionDivMatchingOptionInfo()';
      UTILS.checkArgs(f, arguments, [Object]);

      var optionText = optionInfo.text;
      var optionDiv  = DIV({style: 'display: none'}); // To be revealed later.

      // NOTE
      // ----
      // A TriStateCheckbox must be added for each option so that it
      // can be retrieved based on the index of the option clicked.
      var triStateCheckbox = new TriStateCheckbox(SPAN());
      _guiElements.triStateCheckboxes.push(triStateCheckbox);

      if (optionInfo.isHeading)
      {
         $(optionDiv).addClass('heading'          );
         $(optionDiv).addClass('nonUserSelectable');
         $(optionDiv).addClass('nonUserCheckable' );
      }
      else
      {
         $(optionDiv).append(triStateCheckbox.getSpan());
         triStateCheckbox.setState(optionInfo.checkedState);
         $(triStateCheckbox.getImageSpan()).click(_onClickTriStateCheckboxImageSpan);

         switch
         (
            ((optionInfo.userSelectable)? '1': '0') + '-' +
            ((optionInfo.userCheckable )? '1': '0')
         )
         {
          case '0-0':
            $(optionDiv).addClass('nonUserSelectable');
            $(optionDiv).addClass('nonUserCheckable' );
            triStateCheckbox.setDisabled(true);
            break;
          case '0-1':
            $(optionDiv).addClass('nonUserSelectable');
            break;
          case '1-0':
            $(optionDiv).click(_onClickOptionDiv);
            $(optionDiv).addClass('nonUserCheckable');
            triStateCheckbox.setDisabled(true);
            break;
          case '1-1':
            $(optionDiv).click(_onClickOptionDiv);
            break;
          default:
            throw new Exception('Impossible case.');
         }
      }

      $(optionDiv).append(document.createTextNode(optionText));

      if (optionInfo.selected      ) {$(optionDiv).addClass('selected')           ;}
      if (optionInfo.title !== null) {$(optionDiv).attr('title', optionInfo.title);}

      return optionDiv;
   }

   // Other private functions. ----------------------------------------------------------------//

   /*
    * NOTE
    * ----
    * All options are validated together instead of validating each individually to enable
    * validation checks involving multiple options.  Uniqueness checks involve multiple options.
    *
    */
   function _validateOptionsInfo(newOptionsInfo, boolReplacingOldOptions)
   {
      var f = 'SelectorTriStateCheckbox._validateOptionsInfo()';
      UTILS.checkArgs(f, arguments, [Array, Boolean]);

      switch (boolReplacingOldOptions)
      {
       case true:
         var optionIdsCollapsed = [];
         var optionTexts        = [];
         break;
       case false:
         var optionIdsCollapsed = self.getOptionIdsCollapsed();
         var optionTexts        = self.getOptionTexts();
         break;
      }

      for (var i = 0; i < newOptionsInfo.length; ++i)
      {
         var newOptionInfo = newOptionsInfo[i];

         UTILS.validator.checkObject
         (
            newOptionInfo,
            {
               optionId         : 'string',
               optionIdCollapsed: 'string',
               text             : 'string'
            },
            {
               checkedState  : 'string',
               isHeading     : 'bool'  ,
               selected      : 'bool'  ,
               userCheckable : 'bool'  ,
               userSelectable: 'bool'  ,
               title         : 'string'
            }
         );

         // Set default values of optional parameters if required.
         if (newOptionInfo.checkedState   === undefined) {newOptionInfo.checkedState = 'unchecked';}
         if (newOptionInfo.isHeading      === undefined) {newOptionInfo.isHeading      = false    ;}
         if (newOptionInfo.selected       === undefined) {newOptionInfo.selected       = false    ;}
         if (newOptionInfo.userCheckable  === undefined) {newOptionInfo.userCheckable  = true     ;}
         if (newOptionInfo.userSelectable === undefined) {newOptionInfo.userSelectable = true     ;}
         if (newOptionInfo.title          === undefined) {newOptionInfo.title          = null     ;}

         if (newOptionInfo.isHeading && newOptionInfo.checkedState != 'unchecked')
         {
            throw new Exception
            (
               f,
               'Checked-state of non-heading option specified as being other than "unchecked".', ''
            );
         }

         var newOptionIdCollapsed = newOptionInfo.optionIdCollapsed;
         var newOptionText        = newOptionInfo.text;
         var duplicateDetails     =
         (
            'optionId: "' + newOptionText + '", text: "' + newOptionText + '", index: "' + i + '".'
         );

         if ($.inArray(newOptionIdCollapsed, optionIdsCollapsed) != -1)
         {
            throw new Exception(f, 'Duplicate optionId.', duplicateDetails);
         }

         if ($.inArray(newOptionText, optionTexts) != -1)
         {
            throw new Exception(f, 'Duplicate option text.', duplicateDetails);
         }

         // TODO
         // ----
         // Only userSelectable option texts are checked for uniqueness here so that an option may
         // have the same text as an option heading.  Ideally option headings should be confirmed
         // as unique also.
         optionIdsCollapsed.push(newOptionIdCollapsed);
         if (optionTexts.userSelectable)
         {
            optionTexts.push(newOptionText);
         }
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var self = this;

   var _guiElements =
   {
      triStateCheckboxes: null
   };

   var _state =
   {
      optionIndexByOptionId         : null,
      optionIndexByOptionIdCollapsed: null
   };

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
