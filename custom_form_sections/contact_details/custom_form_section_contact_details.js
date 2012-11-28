/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

$(document).ready
(
   function (ev)
   {
      try
      {
         var f = 'custom_form_section_contact_details.js onReady()';
         UTILS.checkArgs(f, arguments, ['function']);

         new CustomFormSectionContactDetails();
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
function CustomFormSectionContactDetails()
{
   var f = 'CustomFormSectionContactDetails()';
   UTILS.checkArgs(f, arguments, []);

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function _onChangeSoeid(ev)
   {
      try
      {
         var f = 'CustomFormSectionContactDetails._onChangeSoeid()';
console.debug(f, 'e');
         UTILS.checkArgs(f, arguments, ['object']);

         var soeid = $('#contact-details\\\|soeid').val();

         $('#contact-details\\\|division'    ).val('');
         $('#contact-details\\\|first-name'  ).val('');
         $('#contact-details\\\|last-name'   ).val('');
         $('#contact-details\\\|line-manager').val('');

         $.ajax
         (
            {
                data    : {action: 'getContactDetailsFromSoeid', params: {soeid: soeid}},
                dataType: 'json'                                                        ,
                type    : 'POST'                                                        ,
                url     :
                (
                    Config.PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT +
                    '/custom_form_sections/contact_details/ajax.php'
                ),
                success: UTILS.ajax.createReceiveAjaxMessageFunction
                (
                    f, MiscUtils.displayFailureMessage,
                    {getContactDetailsFromSoeid: _fillContactDetailsInForm}
                )
            }
         );

         window.formUpdater.updateFormState();
console.debug(f, 'x');
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _fillContactDetailsInForm(contactDetails)
   {
      var f = 'CustomFormSectionContactDetails._fillContactDetailsInForm()';
      UTILS.checkArgs(f, arguments, ['object']);

      UTILS.validator.checkObject
      (
         contactDetails,
         {
            soeid      : 'string',
            firstName  : 'string',
            lastName   : 'string',
            lineManager: 'string',
            division   : 'string'
         }
      );

      $('#contact-details\\\|division'    ).val(contactDetails.division   );
      $('#contact-details\\\|first-name'  ).val(contactDetails.firstName  );
      $('#contact-details\\\|last-name'   ).val(contactDetails.lastName   );
      $('#contact-details\\\|line-manager').val(contactDetails.lineManager);
      $('#contact-details\\\|soeid'       ).val(contactDetails.soeid      );

      window.formUpdater.updateFormState();
   }

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   $('#contact-details\\\|soeid').change(_onChangeSoeid);
}
