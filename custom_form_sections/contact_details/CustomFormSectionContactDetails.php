<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../php/classes/CustomFormSectionBase.php';

class CustomFormSectionContactDetails extends CustomFormSectionBase
{
   public function getHtmlFilename() {return 'custom_form_section_contact_details.html'      ;}
   public function getJsFilenames()  {return array('custom_form_section_contact_details.js' );}
   public function getCssFilenames() {return array('custom_form_section_contact_details.css');}
   public function getInputIdAttributes()
   {
      return array
      (
         'contact-details|division'    ,
         'contact-details|first-name'  ,
         'contact-details|last-name'   ,
         'contact-details|line-manager',
         'contact-details|soeid'
      );
   }
}
?>
