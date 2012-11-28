<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../php/classes/CustomFormSectionBase.php';

class CustomFormSectionChargeCodes extends CustomFormSectionBase
{
   public function getCssFilenames()      {return array('custom_form_section_charge_codes.css');}
   public function getHtmlFilename()      {return 'custom_form_section_charge_codes.html'      ;}
   public function getInputIdAttributes() {return array()                                      ;}
   public function getJsFilenames()       {return array('custom_form_section_charge_codes.js' );}
}
?>
