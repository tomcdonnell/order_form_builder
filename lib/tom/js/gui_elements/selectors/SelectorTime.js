/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "SelectorTime.js"
*
* Project: GUI elements.
*
* Purpose: Definition of the SelectorTime object.
*
* Author: Tom McDonnell 2007.
*
\**************************************************************************************************/

/*
 *
 */
function SelectorTime()
{
   var f = 'SelectorTime()';
   UTILS.checkArgs(f, arguments, []);

   // Priviliged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.getSelectedTime = function ()
   {
      return t =
      {
         hour  : hSelector.selectedIndex,
         minute: mSelector.selectedIndex,
         second: sSelector.selectedIndex
      };
   };

   /*
    *
    */
   this.getSelectors = function ()
   {
      return s =
      {
         hour  : hSelector,
         minute: mSelector,
         second: sSelector
      };
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setSelectedTime = function (h, m, s)
   {
      var f = 'SelectorTime.setSelectedTime()';
      UTILS.checkArgs(f, arguments, [Number, Number, Number]);
      UTILS.assert(f, 0, 0 <= h && h < 24);
      UTILS.assert(f, 1, 0 <= m && m < 60);
      UTILS.assert(f, 2, 0 <= s && s < 60);

      hSelector.selectedIndex = h;
      mSelector.selectedIndex = m;
      sSelector.selectedIndex = s;
   };

   // Other public functions. -----------------------------------------------------------------//

   /*
    *
    */
   this.selectedTimeEquals = function (h, m, s)
   {
      return bool =
      (
         0 == UTILS.time.compare
         (
            h, m, s,
            hSelector.selectedIndex,
            mSelector.selectedIndex,
            sSelector.selectedIndex
         )
      );
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function init()
   {
      var f = 'SelectorTime.init()';
      UTILS.checkArgs(f, arguments, []);

      for (var h = 0; h < 24; ++h)
      {
         $(hSelector).append(OPTION(((h < 10)? '0': '') + String(h)));
      }

      for (var m = 0; m < 60; ++m)
      {
         $(mSelector).append(OPTION(((m < 10)? '0': '') + String(m)));
         $(sSelector).append(OPTION(((m < 10)? '0': '') + String(m)));
      }
   }

   /*
    *
    */
   this.setDisabled = function (bool)
   {
      var f = 'SelectorTime.setDisabled()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      hSelector.disabled = bool;
      mSelector.disabled = bool;
      sSelector.disabled = bool;
   };

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var hSelector = SELECT({name: 'hour'  });
   var mSelector = SELECT({name: 'minute'});
   var sSelector = SELECT({name: 'second'});

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   init();
}

/*******************************************END*OF*FILE********************************************/
