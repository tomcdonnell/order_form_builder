/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "Tabs.js"
*
* Project: GUI layouts.
*
* Purpose: Definition of the Tabs object.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/*
 * Usage:
 *    var tabs = new Tabs
 *    (
 *       [
 *          [DIV('heading1'), DIV('content1')],
 *          ...
 *       ]
 *    );
 *
 *    $(document.body).append(tabs.getContainerDiv());
 */
function Tabs(divPairs)
{
   var f = 'Tabs()';
   UTILS.checkArgs(f, arguments, [Array]);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getContainerDiv       = function () {return containerDiv;      };
   this.getSelectedHeadingDiv = function () {return selectedHeadingDiv;};

   this.getSelectedContentDiv = function ()
   {
      return contentsDiv.childNodes[$(selectedHeadingDiv).index()];
   };

   this.getTabDiv = function (tabNo)
   {
      var f = 'UTILS.getTabDiv()';
      UTILS.checkArgs(f, arguments, [Number]);
      UTILS.assert(f, 0, 0 <= tabNo && tabNo < contentsDiv.childNodes.length);

      return contentsDiv.childNodes[tabNo];
   };

   // Other public functions. -----------------------------------------------------------------//

   /*
    * @param funct {Function}
    *    Function to be called when the selected tab changes.
    *    The function should expect two arguments:
    *      The new selected tab heading div, and the previous selected tab heading div.
    */
   this.setOnChangeFunction = function (funct)
   {
      var f = 'Tabs.setOnChangeFunction()';
      UTILS.checkArgs(f, arguments, [Function]);

      onChangeFunction = funct;
   };

   /*
    *
    */
   this.add = function (headingDiv, contentDiv)
   {
      var f = 'Tabs.add()';
      UTILS.checkArgs(f, arguments, [HTMLDivElement, HTMLDivElement]);

      headingDiv.style.display = 'inline-block';
      contentDiv.style.display = 'none';

      $(headingsDiv).append(headingDiv);
      $(contentsDiv).append(contentDiv);
      $(headingDiv ).click(onClickHeading);

      if (selectedHeadingDiv === null)
      {
         setSelected(headingDiv, true);
      }
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function onClickHeading(e)
   {
      try
      {
         var f = 'Tabs.onClickHeading()';
         UTILS.checkArgs(f, arguments, [Object]);

         if (e.currentTarget != selectedHeadingDiv)
         {
            var oldSelected = selectedHeadingDiv;
            var newSelected = e.currentTarget;

            setSelected(oldSelected, false);
            setSelected(newSelected, true );

            if (onChangeFunction !== null)
            {
               onChangeFunction(newSelected, oldSelected);
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
   function setSelected(headingDiv, bool)
   {
      var f = 'Tabs.setSelected()';
      UTILS.checkArgs(f, arguments, [HTMLDivElement, Boolean]);
      UTILS.assert(f, 0, headingDiv.parentNode == headingsDiv);

      var tabNo      = UTILS.DOM.countPreviousSiblings(headingDiv);
      var contentDiv = contentsDiv.childNodes[tabNo];

      switch (bool)
      {
       case true:
         headingDiv.setAttribute('class', 'selected');
         contentDiv.setAttribute('class', 'selected');
         contentDiv.style.display = 'block';
         selectedHeadingDiv = headingDiv;
         break;
       case false:
         headingDiv.removeAttribute('class');
         contentDiv.removeAttribute('class');
         contentDiv.style.display = 'none';
         break;
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var selectedHeadingDiv = null;
   var headingsDiv        = DIV({'class': 'tabHeadings'});
   var contentsDiv        = DIV({'class': 'tabContents'});
   var containerDiv       = DIV
   (
      {'class': 'tabsContainer'}, headingsDiv, DIV({style: 'clear: both;'}), contentsDiv
   );

   var onChangeFunction = null;

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   for (var i = 0, len = divPairs.length; i < len; ++i)
   {
      UTILS.assert(f, 0, divPairs[i].constructor == Array);
      UTILS.assert(f, 1, divPairs[i].length      ==     2);

      this.add(divPairs[i][0], divPairs[i][1]);
   }
}

/*******************************************END*OF*FILE********************************************/
