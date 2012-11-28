<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../php/classes/CustomFormSectionBase.php';

/*
 *
 */
class CustomFormSectionPhoneApprovers extends CustomFormSectionBase
{
   public function getHtmlFilename() {return 'custom_form_section_phone_approvers.html';}
   public function getJsFilenames()  {return array();}
   public function getCssFilenames() {return array();}
   public function getInputIdAttributes()
   {
      return array
      (
         'phone-approvers|line-manager',
         'phone-approvers|executive-manager'
      );
   }
}
?>
