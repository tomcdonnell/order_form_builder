/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

$(document).ready
(
   function (ev)
   {
      try
      {
         var f = 'custom_form_section_charge_codes.js onReady()';
         UTILS.checkArgs(f, arguments, ['function']);

         new CustomFormSectionChargeCodes();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }
);

/*
 *
 */
function CustomFormSectionChargeCodes()
{
   var f = 'CustomFormSectionChargeCodes()';
   UTILS.checkArgs(f, arguments, []);

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function _onClickAddButton(ev)
   {
      try
      {
         var f = 'CustomFormSectionChargeCodes._onClickAddButton()';
         UTILS.checkArgs(f, arguments, ['object']);
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _addChargeCodesLi(chargeCodeSetHeading)
   {
      var f = 'CustomFormSectionChargeCodes._addChargeCodesLi()';
      UTILS.checkArgs(f, arguments, ['string']);

      $('#custom-form-section-charge-codes-ul').append
      (
         LI
         (
            {'class': 'charge-codes-set-li'},
            SPAN(chargeCodeSetHeading)      ,
            INPUT({type: 'text'}), '/'      ,
            INPUT({type: 'text'}), '/'      ,
            INPUT({type: 'text'}), '/'      ,
            INPUT({type: 'text'}), '/'      ,
            INPUT({type: 'text'})
         )
      );
   }

   /*
    *
    */
   function _init()
   {
      var f = 'CustomFormSectionChargeCodes._init()';
      UTILS.checkArgs(f, arguments, []);

      $('#charge-codes\\\|add-button').click(_onClickAddButton);

      //_addChargeCodesLi('Phone and Accessories');
   }

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}
