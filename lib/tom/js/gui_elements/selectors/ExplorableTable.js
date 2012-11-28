/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "ExplorableTable.js"
*
* Project: General.
*
* Purpose: A GUI table allowing rows to be displayed in a heirarchy of categories.  Rows in each
*          category may be expanded and contracted.  At a global level (affecting rows in all
*          categories, columns may be resized, and rows sorted by any column, in any order.
*
* Author: Tom McDonnell 2010-07-26.
*
\**************************************************************************************************/

// Object definition. //////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function ExplorableTable(table, ajaxUrl, rowLinkUrl)
{
   var f = 'ExplorableTable()';
   // NOTE: 'Defined' is used below instead of HTMLTableElement to suit IE6.
   UTILS.checkArgs(f, arguments, ['Defined', String, String]);

   // Privileged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getTable = function () {return _domElements.table;};

   /*
    *
    */
   this.initRootCategoryRows = function (idCategoryRoot)
   {
      var f = 'ExplorableTable.initRootCategoryRows()';
      UTILS.checkArgs(f, arguments, [Number]);

      _state.idCategoryRoot        = idCategoryRoot;
      _domElements.tbody.innerHTML = '';

      _state.ajaxParams.data = {action: 'getColumnHeadings', idCategory: idCategoryRoot};
      $.ajax(_state.ajaxParams);
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _receiveAjaxMessage(msg, textStatus, jqXHR)
   {
      try
      {
         var f = 'ExplorableTable._receiveAjaxMessage()';
         UTILS.checkArgs(f, arguments, [Object, String, Object]);

         UTILS.validator.checkObject(msg, {action: 'string', reply: 'Defined'});

         switch (msg.action)
         {
          case 'getColumnHeadings':
            _state.trToAppendCategoryRowsAfter       = null;
            _state.columnHeadings                    = msg.reply;
            _guiElements.interactiveTableHeadingsRow = new InteractiveTableHeadingsRow(msg.reply);
            _state.ajaxParams.data =
            {
               action: 'getChildCategoriesInfo', idCategory:_state.idCategoryRoot
            };
            $.ajax(_state.ajaxParams);
            break;
          case 'getChildCategoriesInfo':
            _appendCategoryRowsAfterSavedRow(_createCategoryRowsFromCategoriesInfo(msg.reply));
            break;
          case 'getDataRowsForCategory':
            _appendDataRowsAfterSavedRow(_convertArraysOfStringsToTrsWithSeparators(msg.reply));
            break;
          default:
            throw new Exception(f, 'Unknown action "' + msg.action + '".', '');
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
   function _onClickExpandContractButton(e)
   {
      try
      {
         var f = 'ExplorableTable._onClickExpandContractButton()';
         UTILS.checkArgs(f, arguments, [Object]);

         var buttonClicked = e.target;
         var buttonTrs     = $(buttonClicked).parent().parent();
         console.assert(buttonTrs.length == 1);
         var buttonTr      = buttonTrs[0];
         var buttons       = _getButtonsForRow(buttonTrs[0]);
         var idCategory    = _getIdCategoryFromCategoryTrId($(buttonTr).attr('id'));
         var command       =
         (
            (($(buttonClicked).attr('value') == '+'            )? 'expand'  : 'contract') + '_' +
            (($(buttonClicked).hasClass('expandDataRowsButton'))? 'dataRows': 'category')
         );

         _removeChildDataAndCategoryRows(idCategory);

         switch (command)
         {
          case 'expand_dataRows':
            _setButtonAttributes(buttons.dataRows, 'nextClickContract');
            _setButtonAttributes(buttons.category, 'nextClickExpand'  );
            _state.trToAppendDataRowsAfter = buttonTr;
            _appendLoadingDataRowsRowAfterSavedRow();
            _state.ajaxParams.data = {action: 'getDataRowsForCategory', idCategory: idCategory};
            $.ajax(_state.ajaxParams);
            break;
          case 'contract_dataRows':
            _setButtonAttributes(buttons.dataRows, 'nextClickExpand');
            _setButtonAttributes(buttons.category, 'nextClickExpand');
            break;
          case 'expand_category':
            _setButtonAttributes(buttons.dataRows, 'nextClickExpand'  );
            _setButtonAttributes(buttons.category, 'nextClickContract');
            _state.trToAppendCategoryRowsAfter = buttonTr;
            _appendLoadingCategoryRowsRowAfterSavedRow();
            _state.ajaxParams.data = {action: 'getChildCategoriesInfo', idCategory: idCategory};
            $.ajax(_state.ajaxParams);
            break;
          case 'contract_category':
            _setButtonAttributes(buttons.dataRows, 'nextClickExpand');
            _setButtonAttributes(buttons.category, 'nextClickExpand');
            break;
          default:
            throw new Exception('Unexpected case for switch variable "' + switchVar + '".');
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
   function _onClickDataRow(e)
   {
      try
      {
         var f = 'ExplorableTable._onClickDataRow()';
         UTILS.checkArgs(f, arguments, [Object]);

         // Redirect
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Getters. --------------------------------------------------------------------------------//

   /*
    *
    */
   function _getButtonsForRow(tr)
   {
      var f = 'ExplorableTable.getButtonsForRow()';
      // NOTE: 'Defined' is used below instead of HTMLTableRowElement to suit IE6.
      UTILS.checkArgs(f, arguments, ['Defined']);

      var tds      = $(tr).children();
      var firstTd  = tds[0];
      var inputs   = $(firstTd).children('input');

      return o =
      {
         category: inputs[inputs.length - 2],
         dataRows: inputs[inputs.length - 1]
      }
   }

   /*
    * ASSUMPTION
    * ----------
    * Category row id attributes are assumed to be in format
    *    'categoryTr_<idCategory>_<idParentCategory>'.
    */
   function _getIdCategoryFromCategoryTrId(idAttribute)
   {
      var f = 'ExplorableTable._getIdCategoryFromCategoryTrId()';
      UTILS.checkArgs(f, arguments, [String]);

      var lastUnderscorePos = idAttribute.lastIndexOf('_');
      var idCategoryString  = idAttribute.substring(11, lastUnderscorePos);

      UTILS.validator.checkType(idCategoryString, 'digitString');

      return Number(idCategoryString);
   }

   /*
    * ASSUMPTION
    * ----------
    * Category row id attributes are assumed to be in format
    *    'categoryTr_<idCategory>_<idParentCategory>'.
    */
   function _getIdCategoryParentFromCategoryTrId(idAttribute)
   {
      var f = 'ExplorableTable._getIdCategoryParentFromCategoryTrId()';
      UTILS.checkArgs(f, arguments, [String]);

      var lastUnderscorePos      = idAttribute.indexOf('_', true);
      var idCategoryParentString = idAttribute.substr(lastUnderscorePos);

      UTILS.validator.checkType(idCategoryParentString, 'digitString');

      return Number(idCategoryParentString);
   }

   /*
    *
    */
   function _getIndentLevelOfRow(tr)
   {
      var f = 'ExplorableTable._getIndentLevelOfRow()';
      // NOTE: 'Defined' is used below instead of HTMLTableRowElement to suit IE6.
      UTILS.checkArgs(f, arguments, ['Defined']);

      var tds         = $(tr).children();
      console.assert(tds.length == 1);
      var td          = tds[0];
      var children    = $(tds).children();
      var indentLevel = 0;

      while ($(children[indentLevel]).hasClass('categoryIndentElement'))
      {
         ++indentLevel;
      }

      return indentLevel;
   }

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   function _setButtonAttributes(button, newState)
   {
      var f = 'ExplorableTable._setButtonAttributes()';
      UTILS.checkArgs(f, arguments, [HTMLInputElement, String]);

      if ($(button).attr('disabled'))
      {
         // Ignore disabled category buttons.
         return;
      }

      switch (newState)
      {
       case 'nextClickContract':
         $(button).attr('value', '-');
         $(button).removeClass('expand');
         $(button).addClass('contract');
         var partialTitle = 'Contract ';
         break;
       case 'nextClickExpand':
         $(button).attr('value', '+');
         $(button).removeClass('contract');
         $(button).addClass('expand');
         var partialTitle = 'Expand ';
         break;
       default:
         throw new Exception
         (
            f, 'Expected "nextClickExpand" or "nextClickContract".  Received "' + newState + '".'
         );
      }

      $(button).attr
      (
         'title', partialTitle +
         (($(button).hasClass('expandCategoryButton'))? 'category rows.': 'data rows.')
      );
   }

   // Initialisation functions. ---------------------------------------------------------------//

   /*
    *
    */
   function _init()
   {
      var f = 'ExplorableTable._init()';
      UTILS.checkArgs(f, arguments, []);

      $(table).addClass('ExplorableTable');
   }

   // Other private functions. ----------------------------------------------------------------//

   /*
    *
    */
   function _appendLoadingCategoryRowsRowAfterSavedRow()
   {
      var f = 'ExplorableTable._appendLoadingCategoryRowsRowAfterSavedRow()';
      UTILS.checkArgs(f, arguments, []);

      var tr = TR
      (
         {'class': 'loadingCategoryRowsTr'},
         TD({colspan: _state.columnHeadings.length * 2 - 1}, 'Loading...')
      );

      switch (_state.trToAppendCategoryRowsAfter === null)
      {
       case true : $(_domElements.tbody).append(tr)                     ; break;
       case false: $(tr).insertAfter(_state.trToAppendCategoryRowsAfter); break;
      }
   }

   /*
    *
    */
   function _appendLoadingDataRowsRowAfterSavedRow()
   {
      var f = 'ExplorableTable._appendLoadingDataRowsRowAfterSavedRow()';
      UTILS.checkArgs(f, arguments, []);

      var tr = TR
      (
         {'class': 'loadingDataRowsTr'},
         TD({colspan: _state.columnHeadings.length * 2 - 1}, 'Loading...')
      );

      $(tr).insertAfter(_state.trToAppendDataRowsAfter);
   }

   /*
    *
    */
   function _createCategoryRowsFromCategoriesInfo(categoriesInfo)
   {
      var f = 'ExplorableTable._createCategoryRowsFromCategoriesInfo()';
      UTILS.checkArgs(f, arguments, [Array]);

      var trs                    = [];
      var categoryParentTr       = _state.trToAppendCategoryRowsAfter;
      var idCategoryParentString =
      (
         (categoryParentTr === null)? '': _getIdCategoryFromCategoryTrId
         (
            $(categoryParentTr).attr('id')
         )
      );

      for (var i = 0; i < categoriesInfo.length; ++i)
      {
         var info = categoriesInfo[i];
         var td   = TD({colspan: _state.columnHeadings.length * 2 - 1});

         UTILS.validator.checkObject
         (
            info, {id: 'int', name: 'string', nChildCategories: 'int', nChildDataRows: 'int'}
         );

         if (info.nChildDataRows == 0)
         {
            continue;
         }

         if (idCategoryParentString != '')
         {
            _appendIndentElementsToRow(td, _getIndentLevelOfRow(categoryParentTr) + 1);
         }

         var bCat = INPUT({type: 'button', 'class': 'expandCategoryButton', disabled: 'disabled'});
         var bRow = INPUT({type: 'button', 'class': 'expandDataRowsButton', disabled: 'disabled'});
         $(bCat).attr('title', 'Expand category rows.');
         $(bRow).attr('title', 'Expand data rows.'    );
         $(td).append(bCat);
         $(td).append(bRow);

         if (info.nChildCategories > 0 && info.nChildDataRows > 10)
         {
            $(bCat).addClass('expand');
            $(bCat).attr('disabled', false);
            $(bCat).attr('value'   , '+'  );
            $(bCat).click(_onClickExpandContractButton);
         }

         if (info.nChildDataRows > 0)
         {
            $(bRow).addClass('expand');
            $(bRow).attr('disabled', false);
            $(bRow).attr('value'   , '+'  );
            $(bRow).click(_onClickExpandContractButton);
         }

         $(td).append(document.createTextNode(info.name + ' (' + info.nChildDataRows + ')'));
         trs.push(TR({id: 'categoryTr_' + info.id + '_' + idCategoryParentString}, td));
      }

      return trs;
   }

   /*
    *
    */
   function _appendCategoryRowsAfterSavedRow(trs)
   {
      var f = 'ExplorableTable._appendCategoryRowsAfterSavedRow()';
      UTILS.checkArgs(f, arguments, [Array]);

      var trToAppendCategoryRowsAfter = _state.trToAppendCategoryRowsAfter;

      if (trToAppendCategoryRowsAfter === null)
      {
         // Remove 'Loading...' row if present.
         var children = $(_domElements.tbody).children();
         if (children.length > 0 && $(children[0]).hasClass('loadingCategoryRowsTr'))
         {
            $(children[0]).remove();
         }

         // Append trs to empty tbody.
         for (var i = 0; i < trs.length; ++i)
         {
            var tr = trs[i];
            $(tr).addClass('categoryTr');
            console.assert($(tr).tagName == 'tr');
            $(_domElements.tbody).append(trs[i]);
         }
      }
      else
      {
         // Reverse array order so original order will be preserved after all rows are appended.
         trs = trs.reverse();

         // Remove 'Loading...' row if present.
         var nextTrs = $(_state.trToAppendCategoryRowsAfter).next();
         var nextTr  = (nextTrs.length > 0)? nextTrs[0]: null;
         if ($(nextTr).hasClass('loadingCategoryRowsTr'))
         {
            $(nextTr).remove();
         }

         // Append trs after saved row.
         for (var i = 0; i < trs.length; ++i)
         {
            var tr = trs[i];
            $(tr).addClass('categoryTr');
            console.assert($(tr).tagName == 'tr');
            $(tr).insertAfter(_state.trToAppendCategoryRowsAfter);
         }
      }

      _state.trToAppendCategoryRowsAfter = null;
   }

   /*
    *
    */
   function _appendDataRowsAfterSavedRow(trs)
   {
      var f = 'ExplorableTable._appendDataRowsAfterSavedRow()';
      UTILS.checkArgs(f, arguments, [Array]);

      var trToAppendDataRowsAfter = _state.trToAppendDataRowsAfter;

      // Remove 'Loading...' row if present.
      var nextTrs = $(trToAppendDataRowsAfter).next();
      var nextTr  = (nextTrs.length > 0)? nextTrs[0]: null;
      if ($(nextTr).hasClass('loadingDataRowsTr'))
      {
         $(nextTr).remove();
      }

      // Add column headings row to trs.
      var idCategory  = _getIdCategoryFromCategoryTrId($(trToAppendDataRowsAfter).attr('id'));
      var headingsTrs = $(_guiElements.interactiveTableHeadingsRow.getTr()).clone(true);
      trs.unshift(headingsTrs[0]);

      // Reverse array order so original order will be preserved after all rows are appended.
      trs = trs.reverse();

      for (var i = 0; i < trs.length; ++i)
      {
         var tr = trs[i];
         $(tr).addClass('dataTr_' + idCategory);
         console.assert($(tr).tagName == 'tr');
         $(tr).insertAfter(trToAppendDataRowsAfter);
      }

      _state.trToAppendDataRowsAfter = null;
   }

   /*
    *
    */
   function _convertArraysOfStringsToTrsWithSeparators(arraysOfStrings)
   {
      var f = 'ExplorableTable._convertArraysOfStringsToTrsWithSeparators()';
      UTILS.checkArgs(f, arguments, [Array]);

      var trs             = [];
      var nColumnHeadings = _state.columnHeadings.length;
      var separatorTh     = TH
      (
         {'class': 'columnHeadingSeparator', style: 'width: ' + _COLUMN_SEPARATOR_WIDTH + 'px;'}, ''
      );

      for (var i = 0; i < arraysOfStrings.length; ++i)
      {
         var tds            = [];
         var arrayOfStrings = arraysOfStrings[i];

         for (var j = 0; j < arrayOfStrings.length; ++j)
         {
            tds.push(TD(arrayOfStrings[j]));
         }

         var tr = UTILS.DOM.implode(separatorTh, tds, TR(), true);

         $(tr).click(_onClickDataRow());

         trs.push(tr);
      }

      return trs;
   }

   /*
    *
    */
   function _removeChildDataAndCategoryRows(idCategoryParent)
   {
      var f = 'ExplorableTable._removeChildDataAndCategoryRows()';
      UTILS.checkArgs(f, arguments, [Number]);

      var childCategoryTrs = $('[id^=categoryTr_][id$=_' + idCategoryParent + ']');

      // Remove data rows for parent category.
      $('[class=dataTr_' + idCategoryParent + ']').remove();

      // Remove data rows for child categories.
      for (var i = 0; i < childCategoryTrs.length; ++i)
      {
         var categoryTr = childCategoryTrs[i];
         var idCategory = _getIdCategoryFromCategoryTrId($(categoryTr).attr('id'));
         _removeChildDataAndCategoryRows(idCategory);
      }

      // Remove child category rows.
      childCategoryTrs.remove();
   }

   /*
    *
    */
   function _appendIndentElementsToRow(td, indentLevel)
   {
      var f = 'ExplorableTable._appendIndentElementsToRow()';
      // NOTE: 'Defined' is used below instead of HTMLTableRowElement to suit IE6.
      UTILS.checkArgs(f, arguments, ['Defined', Number]);

      var indentElement = INPUT
      (
         {
            'class' : 'categoryIndentElement',
            disabled: 'disabled'             ,
            style   : 'visibility: hidden;'  ,
            type    : 'button'
         }
      );

      for (var i = 0; i < indentLevel; ++i)
      {
         $(td).append($(indentElement).clone());
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var self = this;

   var _domElements =
   {
      table: table,
      tbody: $(table).children()[1]  // NOTE: Must be no whitespace between <table> and <tbody>.
   };

   var _guiElements =
   {
      interactiveTableHeadingsRow: null // To be created when the headings info arrives via ajax.
   };

   var _config =
   {
      rowFullHeight: null
   };

   var _state =
   {
      idCategoryRoot             : null,
      columnHeadings             : null,
      trToAppendDataRowsAfter    : null,
      trToAppendCategoryRowsAfter: null,
      mouseDragInfo              : {}  ,
      ajaxParams                 :
      {
         dataType: 'json'             ,
         success : _receiveAjaxMessage,
         type    : 'POST'             ,
         url     : ajaxUrl
      }
   };

   // Private constants. ////////////////////////////////////////////////////////////////////////

   const _COLUMN_SEPARATOR_WIDTH         = 1;
   const _ID_CATEGORY_BY_APPROVAL_STATUS = 1;

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
