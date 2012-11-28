<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../php/classes/CustomFormSectionBase.php';

class CustomFormSectionAgreement extends CustomFormSectionBase
{
   public function getCssFilenames()      {return array('custom_form_section_agreement.css');}
   public function getHtmlFilename()      {return 'custom_form_section_agreement.html';}
   public function getInputIdAttributes() {return array('agreement|agreement');}
   public function getJsFilenames()       {return array();}
}
?>
