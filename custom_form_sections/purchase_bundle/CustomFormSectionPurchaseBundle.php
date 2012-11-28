<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../php/classes/CustomFormSectionBase.php';

class CustomFormSectionPurchaseBundle extends CustomFormSectionBase
{
   public function getCssFilenames()      {return array('custom_form_section_purchase_bundle.css');}
   public function getHtmlFilename()      {return 'custom_form_section_purchase_bundle.html';}
   public function getInputIdAttributes() {return array();}
   public function getJsFilenames()       {return array('custom_form_section_purchase_bundle.js');}
}
?>
