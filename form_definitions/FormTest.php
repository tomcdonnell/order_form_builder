<?php

require_once dirname(__FILE__) . '/../php/classes/FormBase.php';

class FormTest extends FormBase
{
   public function getAsPhpArray()
   {
      return array
      (
         'sections' => 
  array (
    'section' => 
    array (
      0 => 
      array (
        'type' => 'custom',
        'nameShort' => 'contact-details',
        'nameLong' => 'Contact Details',
      ),
      1 => 
      array (
        'type' => 'custom',
        'nameShort' => 'contact-details',
        'nameLong' => 'Contact Details',
      ),
      2 => 
      array (
        'type' => 'normal',
        'nameShort' => 'request-information',
        'nameLong' => 'Request Information',
        'fields' => 
        array (
          'field' => 
          array (
            0 => 
            array (
              'type' => 'select',
              'name' => 'new-phone-will-replace-existing-phone',
              'questionHtml' => 'Is this replacing an existing DPI mobile?',
              'defaultSelectedOptionText' => 'Select...',
              'options' => 
              array (
                'option' => 
                array (
                  0 => 
                  array (
                    'text' => 'Select...',
                    'classString' => 'not-valid-selection',
                  ),
                  1 => 
                  array (
                    'text' => 'No',
                  ),
                  2 => 
                  array (
                    'text' => 'Yes',
                  ),
                ),
              ),
            ),
            1 => 
            array (
              'type' => 'text',
              'name' => 'existing-phone-number',
              'questionHtml' => 'Existing mobile telephone number:',
              'displayConditions' => 
              array (
                'displayCondition' => 
                array (
                  'sectionNameShort' => 'request-information',
                  'fieldName' => 'new-phone-will-replace-existing-phone',
                  'value' => 'Yes',
                ),
              ),
            ),
            2 => 
            array (
              'type' => 'select',
              'name' => 'existing-phone-fate',
              'questionHtml' => 'The phone being replaced:',
              'defaultSelectedOptionText' => 'Select...',
              'options' => 
              array (
                'option' => 
                array (
                  0 => 
                  array (
                    'text' => 'Select...',
                    'classString' => 'not-valid-selection',
                  ),
                  1 => 
                  array (
                    'text' => 'Was lost/stolen',
                  ),
                  2 => 
                  array (
                    'text' => 'it is not functioning and will be returned',
                  ),
                  3 => 
                  array (
                    'text' => 'Is being upgraded and will be returned',
                  ),
                  4 => 
                  array (
                    'text' => 'Is being transferred',
                  ),
                ),
              ),
              'displayConditions' => 
              array (
                'displayCondition' => 
                array (
                  'sectionNameShort' => 'request-information',
                  'fieldName' => 'new-phone-will-replace-existing-phone',
                  'value' => 'Yes',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
   )
  );
}
}
?>
