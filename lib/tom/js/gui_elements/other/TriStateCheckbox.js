/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "TriStateCheckbox.js"
*
* Project: General.
*
* Purpose: A checkbox with three states: Checked, Partially checked, and not checked.
*          To be used for display of selection hierarchies where it is useful to know that some,
*          all, or none of the options at descendent levels are selected, while looking only
*          at the ancestor option.
*
* Author: Tom McDonnell 2010-09-10.
*
\**************************************************************************************************/

// Object definition. //////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function TriStateCheckbox(span)
{
   var f = 'TriStateCheckbox()';
   UTILS.checkArgs(f, arguments, [HTMLSpanElement]);

   // Privileged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getSpan      = function () {return span                    ;};
   this.getImageSpan = function () {return _domElements.spans.image;};
   this.getState     = function () {return _state.checkedState     ;};

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setState = function (newCheckedState)
   {
      var f = 'TriStateCheckbox.setState()';
      UTILS.checkArgs(f, arguments, [String]);

      if (_state.checkedState === newCheckedState)
      {
         return;
      }

      var imageSpan = _domElements.spans.image;

      switch (_state.checkedState)
      {
       case 'checked'         : $(imageSpan).removeClass('checked'         ); break;
       case 'unchecked'       : $(imageSpan).removeClass('unchecked'       ); break;
       case 'partiallyChecked': $(imageSpan).removeClass('partiallyChecked'); break;
       default: throw new Exception('Unknown state string "' + newState + '".');
      }

      $(imageSpan).addClass(newCheckedState);

      _state.checkedState = newCheckedState;
   };

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      var f = 'TriStateCheckbox.setDisabled()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      if (_state.disabled === bool)
      {
         return;
      }

      if (_state.disabled)
      {
         $(span).removeClass('disabled');
      }

      _state.disabled = !_state.disabled;

      if (_state.disabled)
      {
         $(span).addClass('disabled');
      }
   };

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      var f = 'TriStateCheckbox.setDisabled()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      _state.disabled = bool;
   };

   // Other privileged functions. -------------------------------------------------------------//

   /*
    *
    */
   this.simulateClick = function ()
   {
      var f = 'TriStateCheckbox.simulateClick()';
      UTILS.checkArgs(f, arguments, []);

      // Note that state 'partiallyChecked' can only be
      // be set externally (by calling this.setState);

      if (!_state.disabled)
      {
         switch (_state.checkedState)
         {
          case 'checked'         : self.setState('unchecked'); break;
          case 'unchecked'       : self.setState('checked'  ); break;
          case 'partiallyChecked': self.setState('checked'  ); break;
          default: throw new Exception('Unknown state "' + _state_checkedState + '".');
         }
      }
   };

   /*
    * NOTE
    * ----
    * This function should only be run once the span has been attached to the DOM.
    * Only then will the parent element's height be set..
    */
   this.updateHeight = function ()
   {
      var f = 'TriStateCheckbox.updateHeight()';
      UTILS.checkArgs(f, arguments, []);

      var parentElements = $(span).parent();

      if (parentElements.length === 0 || parentElements[0] === null)
      {
         return;
      }

      var parentElement = parentElements[0];

      // If the parent element has been hidden, then its height
      // will be undefined and so the span height cannot be set.
      if ($(parentElement).css('display') == 'none')
      {
         return;
      }

      // The while loop is necessary because setting the height of the span element to the height of
      // the parentElement may cause the parentElement to be resized.  The parent element will be
      // resized in response to changing the height of the span if text in the parentElement has to
      // wrap to a new line to accommodate the new height of the span.
      while ($(parentElement).height() != $(span).height())
      {
         $(span).height($(parentElement).height());
      }
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onClickImageSpan(e)
   {
      try
      {
         var f = 'TriStateCheckbox._onClickImageSpan()';
         UTILS.checkArgs(f, arguments, [Object]);

         self.simulateClick();
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
      var f = 'TriStateCheckbox._init()';
      UTILS.checkArgs(f, arguments, []);

      var imageSpan = _domElements.spans.image;

      $(span).css('display', 'block');
      $(span).css('float'  , 'left' );
      $(span).addClass('triStateCheckbox');
      $(span).append(imageSpan);

      $(imageSpan).css('display', 'block');
      $(imageSpan).css('float'  , 'left' );
      $(imageSpan).addClass('unchecked');
      $(imageSpan).click(_onClickImageSpan);
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var self = this;

   var _domElements =
   {
      spans:
      {
         image: SPAN({'class': 'triStateCheckboxImage'})
      }
   };

   var _state = 
   {
      checkedState: 'unchecked',
      disabled    : false
   };

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
