/**************************************************************************************************\
*
* Filename: "movable_dividers.js"
*
* Project: General layouts.
*
* Purpose: Functions and variables for implementing movable dividers
*          on a web page without resorting to the use of frames.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/**
 * Three frame layout with movable dividers.
 *
 * --------------------------------
 * | topLeftFrame M topRightFrame |
 * |              M               |
 * |              M               |
 * MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM
 * | bottomFrame                  |
 * |                              |
 * |                              |
 * --------------------------------
 */
function ThreeFrameLayoutWithMovableDividers(framesContainerDiv)
{
   var f = 'ThreeFrameLayoutWithMovableDividers()';
   UTILS.checkArgs(f, arguments, [HTMLDivElement]);

   // Priviliged Functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getContainerDiv     = function () {return framesContainerDiv;};
   this.getTopLeftFrameDiv  = function () {return tlDiv;             };
   this.getTopRightFrameDiv = function () {return trDiv;             };
   this.getBottomFrameDiv   = function () {return b_Div;             };

   this.subscribeChildResizeFunction = function (funct)
   {
      var f = 'MovableDividers.subscribeChildResizeFunction()';
      UTILS.checkArgs(f, arguments, [Function]);

      resizeSubscriberFunctions.push(funct);
   };

   // Private Functions. ////////////////////////////////////////////////////////////////////////

   // Object handlers. -------------------------------------------------------------------------//

   /**
    * NOTE: This function is not private so that it can be used as a subscriber function to events
    *       of type 'resize' of a parent element.  For example, this object (MovableDividers) could
    *       be used in the top-left frame of another MovableDividers object.  It would then need to
    *       be able to resize in response to a movement of the dividers of the parent object. 
    */
   this.onResize = function ()
   {
      try
      {
         var f = 'MovableDividers.onResize()';
         UTILS.assert(f, 0, arguments.length <= 1); // Expect arguments to be [Object] or [].

         // Calculate the height and width of the screen area devoted to the three frames.
         var fullH = UTILS.DOM.getDimensionInPixels(framesContainerDiv, 'height');
         var fullW = UTILS.DOM.getDimensionInPixels(framesContainerDiv, 'width' );

         // Calculate the h-divider height, v-divider width, and the divisible height/width.
         // The divisible height is the height to be divided between the top and bottom frames.
         // The top frame is divided into the top-left and top-right frames.  The divisible
         // width is the width to be divided between the top-left and top-right frames.
         // (If the divisible height/width is odd, dividing it in two will result
         //  in non-integer values.  Avoid non-integer values by decrementing
         //  the divisible height/width, and incrementing the divider height/width).
         h_DivH = IDEAL_H_DIVIDER_HEIGHT;
         v_DivW = IDEAL_V_DIVIDER_WIDTH;
         divisibleH = fullH - h_DivH;
         divisibleW = fullW - v_DivW;
         if (UTILS.math.isOdd(divisibleH)) {divisibleH--; h_DivH++;}
         if (UTILS.math.isOdd(divisibleW)) {divisibleW--; v_DivW++;}

         // Remember the top div height.
         t_DivH = divisibleH / 2;

         // Set the height style property of selected elements.
         var t_DivHstr = t_DivH + 'px';
         var h_DivHstr = h_DivH + 'px';
         tlDiv.style.height = t_DivHstr;
         v_Div.style.height = t_DivHstr;
         trDiv.style.height = t_DivHstr;
         h_Div.style.height = h_DivHstr;
         b_Div.style.height = t_DivHstr;

         // Set the width style property of selected elements.
         var t_DivWstr = fullW  + 'px';
         var tlDivW    = divisibleW / 2;
         var tlDivWstr = tlDivW + 'px';
         var v_DivWstr = v_DivW + 'px';
         tlDiv.style.width = tlDivWstr;
         v_Div.style.width = v_DivWstr;
         trDiv.style.width = tlDivWstr;
         h_Div.style.width = t_DivWstr;
         b_Div.style.width = t_DivWstr;

         // NOTE: The properties set above are the only ones
         //       that will be changed in the event handlers.

         // Remember half dimensions and middle coordinates of the dividers.
         v_DivHalfW = Math.round(v_DivW / 2);
         h_DivHalfH = Math.round(h_DivH / 2);
         v_DivMX = tlDivW + v_DivHalfW;
         h_DivMY = t_DivH + h_DivHalfH;

         // Remember limits for dragging.
         var w = MIN_FRAME_WIDTH  + v_DivHalfW;
         var h = MIN_FRAME_HEIGHT + h_DivHalfH;
         minDragX =  w;
         maxDragX = -w + fullW;
         minDragY =  h;
         maxDragY = -h + fullH;

         // Remember the top and left coordinates of the frame container div.
         framesContainerDivLeft = UTILS.DOM.removePXsuffix(framesContainerDiv.style.left);
         framesContainerDivTop  = UTILS.DOM.removePXsuffix(framesContainerDiv.style.top );
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function onMouseDown(e)
   {
      try
      {
         var f = 'MovableDividers.onMouseDown()';
         UTILS.checkArgs(f, arguments, [Object]);

         $(window).mousemove(onMouseMove);
         $(window).mouseout(onMouseOut);
         $(window).mouseup(onMouseUp);

         revealGhosts();

         switch (e.target)
         {
          case v_Div:
            vDividerIsBeingDragged = true;
            break;
          case h_Div:
            hDividerIsBeingDragged   = true;
            hDividerWasGrabbedOnLeft = (e.clientX < v_DivMX);
            break;
          default:
            throw new Exception(f, 'Unexpected e.target', '');
            break;
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
   function onMouseMove(e)
   {
      try
      {
         var f = 'BottomDrawers.onMouseMove()';
         UTILS.checkArgs(f, arguments, [Object]);

         if (vDividerIsBeingDragged)
         {
            // Move the v-divider ghost.
            var x = e.clientX - framesContainerDivLeft;
            if (x < minDragX) x = minDragX;
            if (x > maxDragX) x = maxDragX;
            ghostOne.style.left = x - v_DivHalfW + 'px';

            // Start dragging the h-divider if necessary.
            if (!hDividerIsBeingDragged)
            {
               hDividerIsBeingDragged = (e.clientY > h_DivMY);
            }
         }

         if (hDividerIsBeingDragged)
         {
            // Move the h-divider ghost and change the height of the v-divider ghost.
            var y = e.clientY - framesContainerDivTop;
            if (y < minDragY) y = minDragY;
            if (y > maxDragY) y = maxDragY;
            var tStr = y - h_DivHalfH + 'px';
            ghostTwo.style.top    = tStr;
            ghostOne.style.height = tStr;

            // Start dragging the v-divider if necessary.
            if (!vDividerIsBeingDragged)
            {
               if (hDividerWasGrabbedOnLeft) vDividerIsBeingDragged = (e.clientX > v_DivMX);
               else                          vDividerIsBeingDragged = (e.clientX < v_DivMX);
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
   function onMouseOut(e)
   {
      try
      {
         var f = 'MovableDividers.onMouseOut()';
         UTILS.checkArgs(f, arguments, [Object]);

         if (e.relatedTarget == null || e.relatedTarget == HTML_DOM_ELEMENT)
         {
            onMouseUp(e);
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
   function onMouseUp(e)
   {
      try
      {
         var f = 'MovableDividers.onMouseUp()';
         UTILS.checkArgs(f, arguments, [Object]);

         // Remove event listeners.
         window.removeObjectListener('mousemove', onMouseMove, false);
         window.removeObjectListener('mouseout' , onMouseOut , false);
         window.removeObjectListener('mouseup'  , onMouseUp  , false);

         if (vDividerIsBeingDragged)
         {
            vDividerIsBeingDragged = false;

            // Set the required widths.
            var tlDivW = UTILS.DOM.removePXsuffix(ghostOne.style.left);
            var trDivW = divisibleW - tlDivW                          ;
            tlDiv.style.width = tlDivW + 'px';
            trDiv.style.width = trDivW + 'px';

            // Remember the x coordinate of the middle of the v-divider.
            v_DivMX = tlDivW + v_DivHalfW;
         }

         if (hDividerIsBeingDragged)
         {
            hDividerIsBeingDragged = false;

            // Remember the new height of the top frame.
            t_DivH = UTILS.DOM.removePXsuffix(ghostTwo.style.top);

            // Set the required heights.
            var t_DivHstr = t_DivH              + 'px';
            var b_DivHstr = divisibleH - t_DivH + 'px';
            tlDiv.style.height = t_DivHstr;
            v_Div.style.height = t_DivHstr;
            trDiv.style.height = t_DivHstr;
            b_Div.style.height = b_DivHstr;

            // Remember the y coordinate of the middle of the h-divider.
            h_DivMY = t_DivH + h_DivHalfH;
         }

         hideGhosts();


         // For each subscribers resize function...
         for (var i = 0, len = resizeSubscriberFunctions.length; i < len; ++i)
         {
            // Call the function.
            resizeSubscriberFunctions[i](e);
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Other functions. ------------------------------------------------------------------------//

   /**
    * Set the position and dimensions of ghostOne and ghostTwo to match
    * the v-divider and h-divider respectively, then reveal both ghosts.
    */
   function revealGhosts()
   {
      // Set the position and dimensions of ghostOne to match those of the v-divider.
      ghostOne.style.height = v_Div.style.height;
      ghostOne.style.width  = v_Div.style.width;
      ghostOne.style.bottom = 'auto';
      ghostOne.style.right  = 'auto';
      ghostOne.style.top    = '0px';
      ghostOne.style.left   = tlDiv.style.width;

      // Set the position and dimensions of ghostTwo to match those of the h-divider.
      ghostTwo.style.height = h_Div.style.height;
      ghostTwo.style.width  = h_Div.style.width;
      ghostTwo.style.bottom = 'auto';
      ghostTwo.style.right  = 'auto';
      ghostTwo.style.left   = '0px';
      ghostTwo.style.top    = tlDiv.style.height;

      // Reveal the ghost divs
      ghostOne.style.visibility = 'visible';
      ghostTwo.style.visibility = 'visible';
   }

   /**
    * Hide both ghosts.
    */
   function hideGhosts()
   {
      ghostOne.style.visibility = 'hidden';
      ghostTwo.style.visibility = 'hidden';
   }

   /**
    * Initialise the movable dividers.
    */
   function init()
   {
      var f = 'MovableDividers.init()';
      UTILS.checkArgs(f, arguments, []);

      $(framesContainerDiv).append(tlDiv);
      $(framesContainerDiv).append(v_Div);
      $(framesContainerDiv).append(trDiv);
      $(framesContainerDiv).append(DIV({style: 'clear: both;'}));
      $(framesContainerDiv).append(h_Div);
      $(framesContainerDiv).append(b_Div);
      $(framesContainerDiv).append(ghostOne);
      $(framesContainerDiv).append(ghostTwo);

      self.onResize();

      $(h_Div).mousedown(onMouseDown);
      $(v_Div).mousedown(onMouseDown);

      $(window).resize(self.onResize);
   };

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var hDividerIsBeingDragged = false;
   var vDividerIsBeingDragged = false;

   // Boolean.  Whether the h-divider was grabbed on the left or right of the v-divider.
   var hDividerWasGrabbedOnLeft;

   // Divisible width and height (see this.onResize()).
   var divisibleW;
   var divisibleH;

   var h_DivH; // Actual horizontal divider height (See this.onResize()).
   var v_DivW; // Actual vertical   divider width  (See this.onResize()).

   // Divider half dimensions.
   var v_DivHalfW;
   var h_DivHalfH;

   // Divider middle coordinates.
   var v_DivMX;
   var h_DivMY;

   var t_DivH; // Set on mouseup event, so valid only when dividers are not being dragged.

   // Minimum and maximum drag coordinates.
   var minDragX, minDragY;
   var maxDragX, maxDragY;

   // The distance from the top and left of the browser window to the frames container div.
   var framesContainerDivLeft;
   var framesContainerDivTop;

   var resizeSubscriberFunctions = [];

   var self = this;

   // DOM elements. ---------------------------------------------------------------------------//

   // Frame divs.
   var tlDiv = DIV({'class': 'tlFrame', style: 'float: left; overflow: auto;'});
   var trDiv = DIV({'class': 'trFrame', style: 'float: left; overflow: auto;'});
   var b_Div = DIV({'class': 'b_Frame', style: 'float: left; overflow: auto;'});

   // Divider divs.
   // The v_Div separates the top-left and top-right frames.
   // The h_Div separates the top frames (left and right) from the bottom frame.
   var v_Div = DIV({'class': 'vDivider', style: 'float: left;'});
   var h_Div = DIV({'class': 'hDivider'                       });

   // Ghosts (ghostOne used for v-divider, ghostTwo used for h-divider).
   var ghostOne = DIV({'class': 'ghostOne', style: 'visibility: hidden; position: absolute;'});
   var ghostTwo = DIV({'class': 'ghostTwo', style: 'visibility: hidden; position: absolute;'});

   // Private constants. ////////////////////////////////////////////////////////////////////////

   const HTML_DOM_ELEMENT = document.getElementsByTagName('html')[0];

   const MIN_SCROLL_BAR_DIMENSION = 45; // Units: Pixels.

   const MIN_FRAME_HEIGHT = MIN_SCROLL_BAR_DIMENSION;
   const MIN_FRAME_WIDTH  = MIN_SCROLL_BAR_DIMENSION;

   const IDEAL_H_DIVIDER_HEIGHT = 10; // Units: Pixels.
   const IDEAL_V_DIVIDER_WIDTH  = 10; // Units: Pixels.

   // Inititialisation code. ////////////////////////////////////////////////////////////////////

   init();
}

/*******************************************END*OF*FILE********************************************/
