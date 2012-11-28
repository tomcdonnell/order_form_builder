/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "InteractiveTableHeadingsRow.js"
*
* Project: General.
*
* Purpose: Object containing functions implementing a table headings row with variable width fields.
*          The width of the fields may be changed by dragging the separator between two fields.
*
* Author: Tom McDonnell 2010-10-16.
*
\**************************************************************************************************/

// Object definition. //////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function InteractiveTableHeadingsRow(columnHeadings)
{
   var f = 'InteractiveTableHeadingsRow()';
   UTILS.checkArgs(f, arguments, [Array]);

   // Privileged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getTr = function () {return _domElements.tr;};

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setColumnHeadings = function (newColumnHeadings)
   {
      var f = 'InteractiveTableHeadingsRow.setColumnHeadings()';
      UTILS.checkArgs(f, arguments, [Array]);

      columnHeadings = newColumnHeadings;

      _init();
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onDoubleClickColumnHeadingsSeparatorTh(e)
   {
      try
      {
         var f = 'InteractiveTableHeadingsRow._onDoubleClickColumnHeadingsSeparatorTh()';
         UTILS.checkArgs(f, arguments, [Object]);

         var ths = $($(e.target).parent()).children();

         for (var i = 0; i < ths.length; ++i)
         {
            var th = ths[i];

            if (!$(th).hasClass('columnHeadingSeparator'))
            {
               $(th).css('width', 'auto');
            }
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
   function _onMouseDownColumnHeadingsSeparatorTh(e)
   {
      try
      {
         var f = 'InteractiveTableHeadingsRow._onMouseDownColumnHeadingsSeparatorTh()';
         UTILS.checkArgs(f, arguments, [Object]);

         _updateMouseDragInfo(e);

         $(document.body).mousemove(_onMouseMove);
         $(document.body).mouseup(_onMouseUp);
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onMouseMove(e)
   {
      try
      {
         var f = 'InteractiveTableHeadingsRow._onMouseMove()';
         UTILS.checkArgs(f, arguments, [Object]);

         var pageX         = e.pageX;
         var mouseDragInfo = _state.mouseDragInfo;

         if (pageX < mouseDragInfo.dragLimitL) {pageX = mouseDragInfo.dragLimitL;}
         if (pageX > mouseDragInfo.dragLimitR) {pageX = mouseDragInfo.dragLimitR;}

         var relativeX = pageX - mouseDragInfo.startPageX;

         $(mouseDragInfo.lTh).width(mouseDragInfo.lThOuterWidth + relativeX);
         $(mouseDragInfo.rTh).width(mouseDragInfo.rThOuterWidth - relativeX);
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onMouseUp(e)
   {
      try
      {
         var f = 'InteractiveTableHeadingsRow._onMouseUp()';
         UTILS.checkArgs(f, arguments, [Object]);

         $(document.body).unbind('mousemove', _onMouseMove);

         _clearMouseDragInfo();
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
      var f = 'InteractiveTableHeadingsRow._init()';
      UTILS.checkArgs(f, arguments, []);

      _domElements.tr.innerHTML = '';

      var ths             = [];
      var nColumnHeadings = columnHeadings.length;
      var separatorTh     = TH
      (
         {
            'class': 'columnHeadingSeparator',
            style  : 'width: ' + _COLUMN_SEPARATOR_WIDTH + 'px;'
         }, '|'
      );

      $(separatorTh).dblclick(_onDoubleClickColumnHeadingsSeparatorTh);
      $(separatorTh).mousedown(_onMouseDownColumnHeadingsSeparatorTh);

      for (var i = 0; i < nColumnHeadings; ++i)
      {
         ths.push(TH(columnHeadings[i]));
      }

      _domElements.tr = UTILS.DOM.implode(separatorTh, ths, _domElements.tr, true);
   }

   // Other private functions. ----------------------------------------------------------------//

   /*
    *
    */
   function _clearMouseDragInfo()
   {
      var f = 'InteractiveTableHeadingsRow._clearMouseDragInfo()';
      UTILS.checkArgs(f, arguments, []);

      _state.mouseDragInfo =
      {
         draggedTh    : null,
         lTh          : null,
         rTh          : null,
         startDragX   : null,
         dragLimitL   : null,
         dragLimitR   : null,
         lThOuterWidth: null,
         rThOuterWidth: null
      };
   }

   /*
    *
    */
   function _updateMouseDragInfo(e)
   {
      var f = 'InteractiveTableHeadingsRow._updateMouseDragInfo()';
      UTILS.checkArgs(f, arguments, [Object]);

      var draggedTh = e.target;
      var lThs      = $(draggedTh).prev();
      var rThs      = $(draggedTh).next();
      var tables    = $(draggedTh).parent().parent().parent();

      console.assert(lThs.length  == 1);
      console.assert(rThs.length == 1);
      console.assert(tables.length   == 1);

      var lTh          = lThs[0];
      var rTh          = rThs[0];
      var table        = tables[0];
      var tableOffsetL = $(table).offset().left;

      _state.mouseDragInfo =
      {
         draggedTh    : draggedTh                           ,
         lTh          : lTh                                 ,
         rTh          : rTh                                 ,
         startPageX   : e.pageX                             ,
         dragLimitL   : tableOffsetL                        ,
         dragLimitR   : tableOffsetL + $(table).outerWidth(),
         lThOuterWidth: $(lTh).outerWidth()                 ,
         rThOuterWidth: $(rTh).outerWidth()
      };
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var self = this;

   var _domElements =
   {
      tr: TR()
   };

   var _state =
   {
      mouseDragInfo: {}
   };

   // Private constants. ////////////////////////////////////////////////////////////////////////

   const _COLUMN_SEPARATOR_WIDTH         = 1;
   const _ID_CATEGORY_BY_APPROVAL_STATUS = 1;

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
