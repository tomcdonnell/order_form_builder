/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "FormSelectionsSaver.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the FormSelectionsSaver object.
*
* Author: Tom McDonnell 2012-06-13.
*
\**************************************************************************************************/

/*
 *
 */
function FormSelectionsSaver(params)
{
   var f = 'FormSelectionsSaver()';
   UTILS.checkArgs(f, arguments, [Object]);
   UTILS.validator.checkObject
   (
      params,
      {
         allowDeleteDefault     : 'bool'        ,
         allowOverwriteDefault  : 'bool'        ,
         allowPublicSaveDelete  : 'bool'        ,
         loadOptionsAjaxUrl     : 'string'      ,
         loadSelectionSetAjaxUrl: 'string'      ,
         saveAjaxUrl            : 'string'      ,
         removeAjaxUrl          : 'string'      ,
         formIdAttribute        : 'string'      ,
         classesString          : 'string'      ,
         textHeading            : 'nullOrString',
         textSelectInstruction  : 'nullOrString',
         textShowSaveButton     : 'nullOrString',
         textDeleteButton       : 'nullOrString'
      }
   );

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getTable = function () {return _domElements.table;};

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function _init()
   {
      var f = 'FormSelectionsSaver._init()';
      UTILS.checkArgs(f, arguments, []);

      var buttons = _inputs.buttons;

      $(buttons.cancelSave  ).click(_onClickCancelSave);
      $(buttons.remove      ).click(_onClickRemove    );
      $(buttons.save        ).click(_onClickSave      );
      $(buttons.showSave    ).click(_onClickShowSave  );
      $(_inputs.loadSelector).change(_onChangeLoadSelector);
      _addEventListenerToAllFormInputElements(_onChangeFormElement);

      if (!params.allowPublicSaveDelete)
      {
         var saveCheckbox = _inputs.saveCheckbox;
         saveCheckbox.disabled = true;
         saveCheckbox.checked  = false;
         $(saveCheckbox).attr('title', 'Public configurations may only be saved by HR-operatives.');
      }

      buttons.remove.disabled   = true;
      buttons.showSave.disabled = true;

      _domElements.table = TABLE
      (
         {id: 'formSelectionsSaverTable', 'class': params.classesString},
         _domElements.thead, _domElements.tbody
      );

      $.ajax
      (
         {
            dataType: 'json'                                                  ,
            success : _onSupplyOptions                                        ,
            type    : 'POST'                                                  ,
            url     : params.loadOptionsAjaxUrl + '/' + params.formIdAttribute,
            data    : JSON.stringify
            (
               // Note Regarding Initial Selection
               // --------------------------------
               // The current form selections are supplied so that the server can check which of
               // the options if any matches the current form selections and so should be selected
               // initially.
               {currentSerializedFormSelections: _getCurrentFormSelectionsSerialized()}
            )
         }
      );
   }

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onChangeLoadSelector()
   {
      try
      {
         var f = 'FormSelectionsSaver._onClickChangeLoadSelector()';
         UTILS.checkArgs(f, arguments, [Object]);

         var buttons         = _inputs.buttons;
         var formSelectionId = $(_inputs.loadSelector).val();

         // If the instruction option is selected...
         if (formSelectionId == -1)
         {
            _clearFormSelections();
            return;
         }

         $.ajax
         (
            {
               dataType: 'json'               ,
               success : _onSupplySelectionSet,
               type    : 'POST'               ,
               url     : params.loadSelectionSetAjaxUrl + '/' + formSelectionId
            }
         );

         $(buttons.save  ).disabled = true;
         $(buttons.remove).disabled = true;
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onClickRemove(ev)
   {
      try
      {
         var f = 'FormSelectionsSaver._onClickRemove()';
         UTILS.checkArgs(f, arguments, [Object]);

         var loadSelector       = _inputs.loadSelector;
         var selectedOption     = loadSelector.options[loadSelector.selectedIndex];
         var selectedOptionText = $(selectedOption).text();
         var optGroupElement    = $(selectedOption).parent();
         var optGroupLabel      = $(optGroupElement).attr('label');

         if (optGroupLabel == 'Public')
         {
            if (!params.allowDeleteDefault && selectedOptionText == 'Default')
            {
               alert('You are not allowed to delete the default configuration.');
               return false;
            }

            if (!params.allowPublicSaveDelete)
            {
               alert('You are not allowed to delete public configurations.');
               return false;
            }
         }

         var confirmMessage =
         (
            (
               (optGroupLabel != 'Public')? '':
               "Caution!  '" + selectedOptionText + "' is public and so may be relied on by" +
               " other users.\n\n" +
               'If you choose to delete a public option, an equivalent private option will be\n' +
               'saved for every user who has edited the public option.\n\n'
            ) +
            "Are you sure you want to delete '" + selectedOptionText + "'?"
         );

         if (confirm(confirmMessage))
         {
            $.ajax
            (
               {
                  dataType: 'json'          ,
                  success : _onSupplyOptions,
                  type    : 'POST'          ,
                  url     : params.removeAjaxUrl + '/' + $(selectedOption).attr('value')
               }
            );
         }
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onClickSave(ev)
   {
      try
      {
         var f = 'FormSelectionsSaver._onClickSave()';
         UTILS.checkArgs(f, arguments, [Object]);

         if (!params.allowOverwriteDefault && $(_inputs.saveTextbox).attr('value') == 'Default')
         {
            alert("Please choose a name other than 'Default'.");
            return false;
         }

         $.ajax
         (
            {
               dataType: 'json'            ,
               success : _onSupplyOptions  ,
               type    : 'POST'            ,
               url     : params.saveAjaxUrl,
               data    : JSON.stringify
               (
                  {
                     formIdAttribute         : params.formIdAttribute              ,
                     isPublic                : _inputs.saveCheckbox.checked        ,
                     name                    : $(_inputs.saveTextbox).attr('value'),
                     serializedFormSelections: _getCurrentFormSelectionsSerialized()
                  }
               )
            }
         );
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onClickShowSave(ev)
   {
      try
      {
         var f = 'FormSelectionsSaver._onClickShowSave()';
         UTILS.checkArgs(f, arguments, [Object]);

         var loadSelector           = _inputs.loadSelector;
         var selectedOption         = $(loadSelector.options[loadSelector.selectedIndex]);
         var selectedOptionValue    = $(selectedOption).attr('value');
         var selectedOptionOptGroup = $(selectedOption).parent();

         _inputs.saveCheckbox.checked = ($(selectedOptionOptGroup).attr('label') == 'Public');
         $(_inputs.saveTextbox).attr
         (
            'value', ((selectedOptionValue == '-1')? '': $(selectedOption).text())
         );

         if (!params.allowPublicSaveDelete)
         {
            _inputs.saveCheckbox.checked = false;
         }

         $('#bigButtonsTr').hide();
         $('#loadTr'      ).hide();
         $('#saveFormTr'  ).show();
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onClickCancelSave(ev)
   {
      try
      {
         var f = 'FormSelectionsSaver._onClickCancelSave()';
         UTILS.checkArgs(f, arguments, [Object]);

         $('#bigButtonsTr').show();
         $('#loadTr'      ).show();
         $('#saveFormTr'  ).hide();
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onChangeFormElement(ev)
   {
      try
      {
         var f = 'FormSelectionsSaver._onChangeFormElement()';
         UTILS.checkArgs(f, arguments, [Object]);

         var buttons = _inputs.buttons;

         buttons.showSave.disabled = false;
         buttons.remove.disabled   = true;
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onSupplyOptions(data, textStatus, jqXhr)
   {
      try
      {
         var f = 'FormSelectionsSaver._onSupplyOptions()';
         UTILS.checkArgs(f, arguments, [Object, String, Object]);

         if (data.permissionErrorMessage != undefined)
         {
            alert(data.permissionErrorMessage);
            return;
         }

         UTILS.validator.checkObject
         (
            data,
            {
               optionIdToSelectInitially  : 'nullOrPositiveInt',
               optionNameByIdByPrivacyType: 'object'
            },
            {
               permissionErrorMessage: 'string'
            }
         );

         var boolOptionSelected = false;
         var loadSelectorJq     = $(_inputs.loadSelector);
         var optGroupPrivate    = OPTGROUP({label: 'Private'});
         var optGroupPublic     = OPTGROUP({label: 'Public' });
         var privateNameById    = data.optionNameByIdByPrivacyType['private'];
         var publicNameById     = data.optionNameByIdByPrivacyType['public' ];
         var selectInstruction  =
         (
            (params.textSelectInstruction === null)?
            'Select saved form selections set': params.textSelectInstruction
         );

         switch (data.optionIdToSelectInitially === null)
         {
          case true:
            var optionIdToSelectInitiallyWasSuppliedAsNull = true;
            data.optionIdToSelectInitially = _getDefaultOptionId();
            break;
          case false:
            var optionIdToSelectInitiallyWasSuppliedAsNull = false;
         }

         loadSelectorJq.html(OPTION({value: '-1'}, selectInstruction));
         loadSelectorJq.append(optGroupPublic );
         loadSelectorJq.append(optGroupPrivate);

         for (var id in publicNameById)
         {
            var attributes = {value: id};

            if (id == data.optionIdToSelectInitially)
            {
               attributes.selected = 'selected';
               boolOptionSelected  = true;
            }

            $(optGroupPublic).append(OPTION(attributes, publicNameById[id]));
         }

         for (var id in privateNameById)
         {
            var attributes = {value: id};
            if (id == data.optionIdToSelectInitially)
            {
               attributes.selected = 'selected';
               boolOptionSelected  = true;
            }

            $(optGroupPrivate).append(OPTION(attributes, privateNameById[id]));
         }

         $('#bigButtonsTr').show();
         $('#loadTr'      ).show();
         $('#saveFormTr'  ).hide();

         var buttons = _inputs.buttons;
         buttons.remove.disabled   = !boolOptionSelected;
         buttons.showSave.disabled = true;

         if (optionIdToSelectInitiallyWasSuppliedAsNull || !boolOptionSelected)
         {
            $(_inputs.loadSelector).change();
         }
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   /*
    *
    */
   function _onSupplySelectionSet(obj, textStatus, jqXhr)
   {
      try
      {
         var f = 'FormSelectionsSaver._onSupplySelectionSet()';
         UTILS.checkArgs(f, arguments, [Object, String, Object]);

         UTILS.validator.checkObject(obj, {serializedFormSelections: 'string'});

         // See Note Regarding URI Encoding in function _onClickSave().
         var serializedFormSelections = decodeURIComponent(obj.serializedFormSelections);
         var formSelections           = serializedFormSelections.split('&');

         _clearFormSelections();

         if (formSelections == '')
         {
            return;
         }

         for (var i = 0; i < formSelections.length; ++i)
         {
            var formSelection = formSelections[i];
            var nameAndValue  = formSelection.split('=');
            var name          = nameAndValue[0];
            var value         = nameAndValue[1];
            var domElement    = _getDomElementMatchingNameOrNull(name);

            if (domElement === null)
            {
               // Note Regarding Lack of Exception Thrown Here
               // --------------------------------------------
               // No exception is thrown here because of the liklihood that the list of available
               // columns to export changing without saved export configurations being updated.
               continue;
            }

            if (domElement.tagName.toLowerCase() == 'input')
            {
               if (domElement.disabled)
               {
                  // Note Regarding Disabled Inputs
                  // ------------------------------
                  // The value of disabled inputs should not be changed, because the FormSelector
                  // object is designed only to allow users to automate what they would otherwise
                  // do manually.  Since users are unable to change the value of disabled inputs,
                  // neither should the FormSelector object be able to.
                  continue;
               }

               var inputType = $(domElement).attr('type');

               switch (inputType)
               {
                case 'checkbox': domElement.checked = (value == 'on'); break;
                case 'text'    : $(domElement).attr('value', value)  ; break;
                case 'radio'   :
                  throw new Exception
                  (
                     f, 'No code to handle radio buttons yet written.  Write it.', ''
                  );

                case 'button': // Fall through.
                case 'submit':
                  // No form value is saved for these input types.  Therefore do nothing.
                  break;

                case 'hidden':
                  // Note Regarding Hidden Inputs
                  // ----------------------------
                  // Hidden inputs are always ignored by the FormSelectionsSaver object.
                  break;

                default:
                  throw new Exception(f, 'Unexpected input type "' + inputType + '".');
               }
            }
         }

         var buttons = _inputs.buttons;
         buttons.remove.disabled   = false;
         buttons.showSave.disabled = true;
      }
      catch (e)
      {
         console.error(f, e);
      }
   }

   // Other private functions. ----------------------------------------------------------------//

   /*
    *
    */
   function _getDefaultOptionId()
   {
      var f = 'FormSelectionsSaver._getDefaultOptionId()';
      UTILS.checkArgs(f, arguments, []);

      var options = _inputs.loadSelector.options;

      for (var i = 0; i < options.length; ++i)
      {
         var optionJq = $(options[i]);
         if (optionJq.text() == 'Default')
         {
            return Number(optionJq.attr('value'));
         }
      }

      return null;
   }

   /*
    *
    */
   function _getCurrentFormSelectionsSerialized()
   {
      var f = 'FormSelectionsSaver._getCurrentFormSelectionsSerialized()';
      UTILS.checkArgs(f, arguments, []);

      // Note Regarding URI Encoding
      // ---------------------------
      // Function encodeURIComponent() is used below to get around a CodeIgniter security feature.
      // If the serialized string is sent without being encoded, a 'Disallowed Key Characters'
      // error will result due to presence of ampersand ('&') characters.
      return encodeURIComponent($('#' + params.formIdAttribute).serialize());
   }

   /*
    *
    */
   function _clearFormSelections()
   {
      var f = 'FormSelectionsSaver._clearFormSelections()';
      UTILS.checkArgs(f, arguments, []);

      var inputs    = $('form#' + params.formIdAttribute + ' input'   );
      var selectors = $('form#' + params.formIdAttribute + ' selector');
      var textareas = $('form#' + params.formIdAttribute + ' textarea');

      for (var i = 0; i < inputs.length; ++i)
      {
         var input     = inputs[i];
         var inputType = $(input).attr('type');

         if (input.disabled)
         {
            // See Note Regarding Disabled Inputs above.
            continue;
         }

         switch (inputType)
         {
          case 'text'    : $(input).value('')   ; break;
          case 'checkbox': input.checked = false; break;
          case 'radio'   : input.checked = false; break;

          case 'button': // Fall through.
          case 'submit':
            // No form value is saved for these input types.  Therefore do nothing.
            break;

          case 'hidden':
            // See Note Regarding Submits and Hidden Inputs above.
            break;

          default:
            throw new Exception(f, 'Unexpected input type "' + inputType + '".', '');
         }
      }
   }

   /*
    *
    */
   function _getDomElementMatchingNameOrNull(name)
   {
      var f = 'FormSelectionsSaver._getDomElementMatchingNameOrNull()';
      UTILS.checkArgs(f, arguments, [String]);

      var domElements = $('#' + params.formIdAttribute + ' [name="' + name + '"]');

      switch (domElements.length)
      {
       case 0:
         return null;

       case 1:
         return domElements[0];

       default:
         throw new Exception
         (
            f, 'Multiple dom elements (' + domElements.length +
            ') found matching name "' + name + '".', ''
         );
      }
   }

   /*
    *
    */
   function _addEventListenerToAllFormInputElements(func)
   {
      var f = 'FormSelectionsSaver._addEventListenerToAllFormElements()';
      UTILS.checkArgs(f, arguments, [Function]);

      $('#' + params.formIdAttribute + ' select'  ).change(func);
      $('#' + params.formIdAttribute + ' textarea').change(func);

      var inputsJq = $('#' + params.formIdAttribute + ' input');

      for (var i = 0; i < inputsJq.length; ++i)
      {
         var input     = inputsJq[i];
         var inputType = $(input).attr('type');

         switch (inputType)
         {
          case 'checkbox': // Fall through.
          case 'radio'   : // Fall through.
          case 'text'    :
            $(input).change(func);
            break;

          case 'button': // Fall through.
          case 'submit':
            // No form value is saved for these input types.  Therefore do nothing.
            break;

          case 'hidden':
            // See Note Regarding Hidden Inputs above.
            break;

          default:
            throw new Exception
            (
               'Unsupported input type "' + inputType +
               '".  Add a case for "'     + inputType + '".  It\'s dead easy.'
            );
         }
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   // HTML elements. --------------------------------------------------------------------------//

   var _inputs =
   {
      buttons:
      {
         cancelSave: INPUT({type: 'button', value: 'Cancel'}),
         save      : INPUT({type: 'button', value: 'Save'  }),
         remove: INPUT
         (
            {
               type : 'button',
               value:
               (
                  (params.textDeleteButton === null)?
                  'Delete\nLoaded Form\nSelections': params.textDeleteButton
               )
            }
         ),
         showSave: INPUT
         (
            {
               type : 'button',
               value:
               (
                  (params.textShowSaveButton === null)?
                  'Save\nForm\nSelections': params.textShowSaveButton
               )
            }
         )
      },
      loadSelector: SELECT(OPTION('Load Saved Form Selections')),
      saveTextbox : INPUT({type: 'textbox' , title: 'type the name of the search to be saved'}),
      saveCheckbox: INPUT
      (
         {
            type : 'checkbox',
            title: 'Check to save for use by all users.  Uncheck to save for your use only.'
         }
      )
   };

   var _domElements =
   {
      table: null,
      thead: THEAD
      (
         TR
         (
            TH
            (
               // Note Regarding IE6
               // ------------------
               // IE6 requires that 'colspan' and 'rowspan'
               // be camelcased to 'colSpan' and 'rowSpan'.
               {colSpan: 2},
               ((params.textHeading === null)? 'Save / Load Form Selections': params.textHeading)
            )
         )
      ),
      tbody: TBODY
      (
         TR({id: 'loadTr'}, TD({colSpan: 2}, _inputs.loadSelector)),
         TR
         (
            {id: 'bigButtonsTr'}                                ,
            TD({style: 'width: 50%;'}, _inputs.buttons.showSave),
            TD(                        _inputs.buttons.remove  )
         ),
         TR
         (
            {id: 'saveFormTr', style: 'display: none;'},
            TD
            (
               {colSpan: 2},
               TABLE
               (
                  {style: 'width: 100%'},
                  TBODY
                  (
                     TR(TD({style: 'width: 50%'}, 'Save name'), TD(_inputs.saveTextbox       )),
                     TR(TD('Save for use by others'          ), TD(_inputs.saveCheckbox      )),
                     TR(TD(_inputs.buttons.save              ), TD(_inputs.buttons.cancelSave))
                  )
               )
            )
         )
      )
   };

   // Other variables. ------------------------------------------------------------------------//

   var self = this;

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
