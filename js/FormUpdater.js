/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 * Event driven code for forms built by the FormBuilder (/js/FormBuilder.js).
 */
function FormUpdater(parentDomElement, form)
{
   var f = 'FormUpdater()';
   UTILS.checkArgs(f, arguments, ['Defined', 'object']);

   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   this.attachEventListenersAndInitialiseState = function ()
   {
      var f = 'FormUpdater.attachEventListenersAndInitialiseState()';
      UTILS.checkArgs(f, arguments, []);

      _initLiDisplayConditionsByFieldId();

      var parentDomElementJq = $(parentDomElement);

      parentDomElementJq.find('input'   ).change(_onChangeFieldElementState);
      parentDomElementJq.find('select'  ).change(_onChangeFieldElementState);
      parentDomElementJq.find('textarea').change(_onChangeFieldElementState);

      this.updateFormState();
   };

   /*
    *
    */
   this.updateFormState = function ()
   {
      var f = 'FormUpdater.updateFormState()';
      UTILS.checkArgs(f, arguments, []);

      // NOTE: Hide or show must be done before the highlighting.
      _hideOrShowLisAccordingToDisplayConditions();
      _highlightIncompleteFieldsAndSetDisabledForSubmitButton();

      if (_boolAuditMode)
      {
         $('#main-form :input').attr('disabled', true);
      }
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onChangeFieldElementState(ev)
   {
      try
      {
         var f = 'FormUpdater._onChangeFieldElementState()';
         UTILS.checkArgs(f, arguments, ['object']);

         _self.updateFormState();
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
   function _hideOrShowLisAccordingToDisplayConditions()
   {
      var f = 'FormUpdater._hideOrShowLisAccordingToDisplayConditions()';
      UTILS.checkArgs(f, arguments, []);

      // Note Regarding JQuery and DOM Element Selection
      // -----------------------------------------------
      // Document.getElementById() is used rather than jQuery for DOM element selection in this
      // function because fieldId may contain a pipe character that would need to be escaped if
      // the jQuery method $('#' + fieldId) was used.

      for (var fieldId in _liDisplayConditionsByFieldId)
      {
         var liId                 = fieldId + '-li';
         var li                   = document.getElementById(liId);
         var liJq                 = $(li);
         var boolConditionsAreMet = true;
         var displayConditions    = _liDisplayConditionsByFieldId[fieldId];

         if (displayConditions !== null)
         {
            for (var i = 0, len = displayConditions.length; i < len; ++i)
            {
               var dc               = displayConditions[i];
               var conditionFieldId = dc.sectionNameShort + '|' + dc.fieldName;
               var conditionField   = document.getElementById(conditionFieldId);

               if ($(conditionField).val() != dc.value)
               {
                  boolConditionsAreMet = false;
                  break;
               }
            }
         }

         if (boolConditionsAreMet)
         {
            liJq.css('display', 'list-item');
            _setDisabledForAllChildInputElements(liJq, false);
            // NOTE: If _boolAuditMode is set, all input elements
            //       elements will be disabled in updateFormState().
         }
         else
         {
            liJq.css('display', 'none');
            _setDisabledForAllChildInputElements(liJq, true);
         }
      }
   }

   /*
    *
    */
   function _highlightIncompleteFieldsAndSetDisabledForSubmitButton()
   {
      var f = 'FormUpdater._highlightIncompleteFieldsAndSetDisabledForSubmitButton()';
      UTILS.checkArgs(f, arguments, []);

      var inputTagNames      = ['input', 'select', 'textarea'];
      var nFieldsHighlighted = 0;

      for (var i = 0, len = inputTagNames.length; i < len; ++i)
      {
         var inputsJq = $(inputTagNames[i]);

         for (var j = 0, len = inputsJq.length; j < len; ++j)
         {
            var input = inputsJq[j];

            if (!UTILS.DOM.elementIsDisplayed(input))
            {
               continue;
            }

            if (_addOrRemoveIncompleteFieldClassForField(input))
            {
               ++nFieldsHighlighted;
            }
         }
      }

      $('#save-and-submit-button'        )[0].disabled = (nFieldsHighlighted > 0);
      $('#save-without-submitting-button')[0].disabled =
      (
         $('#contact-details\\|soeid'     ).val() == '' &&
         $('#contact-details\\|first-name').val() == '' &&
         $('#contact-details\\|last-name' ).val() == '' &&
         $('#contact-details\\|division'  ).val() == ''
      );
   }

   /*
    * @field
    *    A select, input, or textarea DOM element.
    *
    * @return
    *    True if as post-condition field has class 'incomplete-field', false otherwise.
    */
   function _addOrRemoveIncompleteFieldClassForField(field)
   {
      var f = 'FormUpdater._addOrRemoveIncompleteFieldClassForField()';
      UTILS.checkArgs(f, arguments, ['Defined']);

      var fieldJq = $(field);

      if (fieldJq.hasClass('optional-field'))
      {
         return false;
      }

      fieldJq.removeClass('incomplete-field');

      var inputTagName = field.tagName.toLowerCase();

      switch (inputTagName)
      {
       case 'input':
         var inputType = fieldJq.attr('type');
         switch (inputType)
         {
          case 'text'    : var boolFieldIsCompleted = (fieldJq.val() != ''); break;
          case 'submit'  : var boolFieldIsCompleted = true                 ; break;
          case 'checkbox':
            // Checkboxes must be treated specially because styling checkboxes is problematic.
            // Add or remove the class to/from the grandparent element instead.
            if (field.checked)
            {
               fieldJq.parent().parent().removeClass('incomplete-field');
               return false;
            }
            fieldJq.parent().parent().addClass('incomplete-field');
            return true;
            
          default:
            throw new Exception(f, 'Unknown input type "' + inputType + '".', '');
         }
         break;

       case 'textarea':
         var boolFieldIsCompleted = (fieldJq.val() != '');
         break;

       case 'select':
         var selectedOptionJq     = fieldJq.find('option:selected');
         var boolFieldIsCompleted = (!selectedOptionJq.hasClass('not-valid-selection'));
         break;

       default:
         throw new Exception(f, 'Unknown input tag name "' + inputTagName + '".', '');
      }

      if (!boolFieldIsCompleted)
      {
         $(field).addClass('incomplete-field');
         return true;
      }

      return false;
   }

   /*
    * The purpose of this function is to ensure that values for
    * inputs that are hidden do not appear in the $_POSTed form data.
    */
   function _setDisabledForAllChildInputElements(parentElementJq, boolDisabled)
   {
      var f = 'FormUpdater._setDisabledForAllChildInputElements()';
      UTILS.checkArgs(f, arguments, ['Defined', 'bool']);

      var children = parentElementJq.children();

      for (var i = 0, len = children.length; i < len; ++i)
      {
         var child = children[i];

         switch (child.tagName.toLowerCase())
         {
          case 'input'   :
          case 'select'  :
          case 'textarea':
            child.disabled = boolDisabled;
            break;
          default:
            // Do nothing.
         }
      }
   }

   /*
    *
    */
   function _initLiDisplayConditionsByFieldId()
   {
      var f = 'FormUpdater._initLiDisplayConditionsByFieldId()';
      UTILS.checkArgs(f, arguments, []);

      var sds = form.sections;

      for (var i = 0, lenI = sds.length; i < lenI; ++i)
      {
         var sd = sds[i];

         if (sd.type == 'custom')
         {
            continue;
         }

         var sectionNameShort = sd.nameShort;
         var fds              = sd.fields;

         for (var j = 0, lenJ = fds.length; j < lenJ; ++j)
         {
            var fd      = fds[j];
            var fieldId = FormUtils.getFieldIdAttributeFromSectionNameFieldName
            (
               sectionNameShort, fd.name
            );

            _liDisplayConditionsByFieldId[fieldId] =
            (
               (fd.displayConditions === undefined)? null: fd.displayConditions
            );
         }
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var _self                         = this;
   var _liDisplayConditionsByFieldId = {};
   var _boolAuditMode                = (document.URL.indexOf('auditMode=1') != -1);
}
