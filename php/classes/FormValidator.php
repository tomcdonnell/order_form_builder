<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/Config.php';
require_once dirname(__FILE__) . '/../../config/database.php';
require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_validator.php';
require_once dirname(__FILE__) . '/CustomFormSectionUtils.php';
require_once dirname(__FILE__) . '/FormSpecification.php';

/*
 * IMPORTANT
 * ---------
 * This class must not make use of functions in the FormBase class.  Those functions assume that
 * the form array has been validated, and the validation is done in this class.  If the FormBase
 * class was used, circular logic would result.
 */
class FormValidator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct(PdoExtended $pdoEx)
   {
      $this->_pdoEx = $pdoEx;
   }

   /*
    *
    */
   public function assertFormArrayIsValid(Array $form)
   {
      $this->_displayConditionsByFieldNameBySectionNameShort = array();

      $specification = FormSpecification::getFormSpecification();

      Utils_validator::checkArray
      (
         $form                                ,
         $specification['compulsoryTypeByKey'],
         $specification['optionalTypeByKey'  ]
      );

      $sections = $form['sections'];

      for ($i = 0, $len = count($sections); $i < $len; ++$i)
      {
         $section = $sections[$i];

         if (!is_array($section))
         {
            throw new Exception
            (
               "Incorrect type '" . gettype($section) . "' for section $i.  Expected array."
            );
         }

         try
         {
            $this->_assertSectionIsValid($section);
         }
         catch (Exception $e)
         {
            throw new Exception("Problem found in section $i\n" . $e->getMessage());
         }
      }

      $this->_fillFieldBySectionNameShortByFieldName($form);
      $this->_fillFieldNamesAsKeysBySectionNameShort($form);
      $this->_fillSectionBySectionNameShort($form);
      $this->_assertItemNameShortsExist($form);
      $this->_assertDisplayConditionsAreValid();
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function _assertSectionIsValid(Array $section)
   {
      $this->_assertSectionNameShortOrFieldNameIsValid($section['nameShort']);

      if (!array_key_exists('type', $section))
      {
         throw new Exception("Expected key 'type' not present in section.");
      }

      $type = $section['type'];

      switch ($type)
      {
       case 'custom': $this->_assertCustomSectionIsValid($section); break;
       case 'normal': $this->_assertNormalSectionIsValid($section); break;
       default: throw new Exception("Unknown section type '$type'.");
      }
   }

   /*
    *
    */
   private function _assertCustomSectionIsValid(Array $section)
   {
      $specification = FormSpecification::getSectionSpecification('custom');

      try
      {
         Utils_validator::checkArray
         (
            $section                             ,
            $specification['compulsoryTypeByKey'],
            $specification['optionalTypeByKey'  ]
         );

         $this->_assertCustomSectionInputIdAttributesAreValid($section['nameShort']);
      }
      catch (Exception $e)
      {
         throw new Exception(", (custom section).\n" . $e->getMessage());
      }
   }

   /*
    *
    */
   private function _assertNormalSectionIsValid(Array $section)
   {
      $specification = FormSpecification::getSectionSpecification('normal');

      try
      {
         Utils_validator::checkArray
         (
            $section                             ,
            $specification['compulsoryTypeByKey'],
            $specification['optionalTypeByKey'  ]
         );
      }
      catch (Exception $e)
      {
         throw new Exception(", (normal section).\n" . $e->getMessage());
      }

      $i                = 0;
      $sectionNameShort = $section['nameShort'];

      foreach ($section['fields'] as $field)
      {
         try
         {
            $this->_assertFieldIsValid($field);
         }
         catch (Exception $e)
         {
            throw new Exception(", field $i\n" . $e->getMessage());
         }

         $fieldName = $field['name'];

         if (array_key_exists('displayConditions', $field))
         {
            $this->_displayConditionsByFieldNameBySectionNameShort[$sectionNameShort][$fieldName] =
            (
               $field['displayConditions']
            );
         }

         ++$i;
      }
   }

   /*
    *
    */
   private function _assertFieldIsValid(Array $field)
   {
      Utils_validator::checkType($field, 'array');

      $fieldName = $field['name'];

      $this->_assertSectionNameShortOrFieldNameIsValid($fieldName);

      if (!array_key_exists('type', $field))
      {
         throw new Exception("Expected key 'type' not supplied for field '$fieldName'.");
      }

      $fieldType          = $field['type'];
      $fieldSpecification = FormSpecification::getFieldSpecification($fieldType);

      try
      {
         Utils_validator::checkArray
         (
            $field                                    ,
            $fieldSpecification['compulsoryTypeByKey'],
            $fieldSpecification['optionalTypeByKey'  ]
         );
      }
      catch (Exception $e)
      {
         throw new Exception(", of type '$fieldType'.\n" . $e->getMessage());
      }

      if ($fieldType == 'select')
      {
         $this->_assertSelectOptionsAreValid($field['options']);
      }
   }

   /*
    *
    */
   private function _assertSelectOptionsAreValid(Array $options)
   {
      $specification = FormSpecification::getSelectOptionSpecification();

      $i = 0;

      foreach ($options as $option)
      {
         try
         {
            Utils_validator::checkArray
            (
               $option                              ,
               $specification['compulsoryTypeByKey'],
               $specification['optionalTypeByKey'  ]
            );
         }
         catch (Exception $e)
         {
            throw new Exception(", option $i.\n" . $e->getMessage());
         }

         ++$i;
      }
   }

   /*
    *
    */
   private function _assertDisplayConditionsAreValid()
   {
      foreach
      (
         $this->_displayConditionsByFieldNameBySectionNameShort as
         $sectionNameShort => $displayConditionsByFieldName
      )
      {
         foreach ($displayConditionsByFieldName as $fieldName => $displayConditions)
         {
            foreach ($displayConditions as $displayCondition)
            {
               try
               {
                  $this->_assertDisplayConditionIsValid($displayCondition);
               }
               catch (Exception $e)
               {
                  throw new Exception
                  (
                     'Problem found in display condition for section' .
                     " '$sectionNameShort', field '$fieldName'.\n" . $e->getMessage()
                  );
               }
            }
         }
      }
   }

   /*
    *
    */
   private function _fillFieldNamesAsKeysBySectionNameShort(Array $form)
   {
      $this->_fieldNamesAsKeysBySectionNameShort = array();

      $sections = $form['sections'];

      for ($i = 0, $lenI = count($sections); $i < $lenI; ++$i)
      {
         $section          = $sections[$i];
         $sectionNameShort = $section['nameShort'];
         $type             = $section['type'     ];
         $fieldNamesAsKeys = array();

         switch ($type)
         {
          case 'custom':
            $fieldNamesAsKeys =
            (
               CustomFormSectionUtils::getFieldNamesAsKeysForCustomSection($sectionNameShort)
            );
            break;

          case 'normal':
            $fields = $section['fields'];

            for ($j = 0, $lenJ = count($fields); $j < $lenJ; ++$j)
            {
               $field = $fields[$j];
               $fieldNamesAsKeys[$field['name']] = null;
            }
            break;

          default:
            throw new Exception("Unknown section type '$type'.");
         }

         $this->_fieldNamesAsKeysBySectionNameShort[$sectionNameShort] = $fieldNamesAsKeys;
      }
   }

   /*
    *
    */
   private function _fillFieldBySectionNameShortByFieldName(Array $form)
   {
      $this->_fieldByFieldNameBySectionNameShort = array();

      $sections = $form['sections'];

      for ($i = 0, $lenI = count($sections); $i < $lenI; ++$i)
      {
         $section          = $sections[$i];
         $sectionNameShort = $section['nameShort'];
         $type             = $section['type'     ];

         switch ($type)
         {
          case 'custom':
            $fieldByFieldName = 'CUSTOM_SECTION';
            break;

          case 'normal':
            $fieldByFieldName = array();
            foreach ($section['fields'] as $field)
            {
               $fieldByFieldName[$field['name']] = $field;
            }
            break;

          default:
            throw new Exception("Unknown section type '$type'.");
         }

         $this->_fieldByFieldNameBySectionNameShort[$sectionNameShort] =
         (
            $fieldByFieldName
         );
      }
   }

   /*
    *
    */
   private function _fillSectionBySectionNameShort(Array $form)
   {
      $this->_sectionBySectionNameShort = array();

      $sections = $form['sections'];

      for ($i = 0, $lenI = count($sections); $i < $lenI; ++$i)
      {
         $section          = $sections[$i];
         $sectionNameShort = $section['nameShort'];
         $this->_sectionBySectionNameShort[$sectionNameShort] = $section;
      }
   }

   /*
    *
    */
   private function _assertDisplayConditionIsValid(Array $displayCondition)
   {
      $specification = FormSpecification::getDisplayConditionSpecification();

      Utils_validator::checkArray
      (
         $displayCondition                    ,
         $specification['compulsoryTypeByKey'],
         $specification['optionalTypeByKey'  ]
      );

      // Extract values checked above.
      $sectionNameShort = $displayCondition['sectionNameShort'];
      $fieldName        = $displayCondition['fieldName'       ];
      $value            = $displayCondition['value'           ];

      if (!array_key_exists($sectionNameShort,$this->_fieldByFieldNameBySectionNameShort))
      {
         throw new Exception("No section with name short '$sectionNameShort' exists.");
      }

      $fieldByFieldName = $this->_fieldByFieldNameBySectionNameShort[$sectionNameShort];

      if (!array_key_exists($fieldName, $fieldByFieldName))
      {
         throw new Exception
         (
            "No field with name '$fieldName' exists in the '$sectionNameShort' section."
         );
      }

      $field = $fieldByFieldName[$fieldName];

      if ($field['type'] == 'select')
      {
         $this->_assertOptionWithTextExistsInField($value, $field);
      }
   }

   /*
    *
    */
   private function _assertOptionWithTextExistsInField($text, Array $field)
   {
      foreach ($field['options'] as $option)
      {
         if ($option['text'] == $text)
         {
            return;
         }
      }

      throw new Exception("No option with text '$text' found.");
   }

   /*
    *
    */
   private function _assertCustomSectionInputIdAttributesAreValid($customSectionNameShort)
   {
      $inputIdAttributes = CustomFormSectionUtils::getInputIdAttributesForCustomSection
      (
         $customSectionNameShort
      );

      foreach ($inputIdAttributes as $idAttribute)
      {
         $tokens = explode('|', $idAttribute);

         if (count($tokens) != 2)
         {
            throw new Exception
            (
               "Unexpected number of tokens found in custom form section input id attribute." .
               "  Expected two pipe-separated tokens '\$sectionNameShort|\$fieldName'."
            );
         }

         foreach ($tokens as $token)
         {
            $this->_assertSectionNameShortOrFieldNameIsValid($token);
         }
      }
   }

   /*
    * Assert that the given string contains only lower-case alphabet and hyphen characters.
    */
   private function _assertSectionNameShortOrFieldNameIsValid($string)
   {
      $result = preg_match('/^[a-z-]+$/', $string);

      if ($result === false)
      {
         throw new Exception('Error occured during call to preg_match().');
      }

      if ($result !== 1)
      {
         throw new Exception
         (
            "'$string' is an invalid section name short or field name."         .
            '  Section name shorts and field names may only contain lower-case' .
            ' alphabet characters and hyphens, and must not begin or end with a hyphen.'
         );
      }
   }

   /*
    *
    */
   private function _assertItemNameShortsExist(Array $form)
   {
      $sections = $form['sections'];

      for ($i = 0, $lenI = count($sections); $i < $lenI; ++$i)
      {
         $section = $sections[$i];
         $type              = $section['type'];

         switch ($type)
         {
          case 'custom':
            // Do nothing.
            break;

          case 'normal':
            foreach ($section['fields'] as $field)
            {
               if ($field['type'] != 'select')
               {
                  continue;
               }

               foreach ($field['options'] as $option)
               {
                  if (!array_key_exists('itemNameShortsToAddIfSelected', $option))
                  {
                     continue;
                  }

                  foreach ($option['itemNameShortsToAddIfSelected'] as $itemNameShort)
                  {
                     if (!$this->_itemWithNameShortExists($itemNameShort))
                     {
                        throw new Exception
                        (
                           "Problem found in options list for field '{$field['name']}'" .
                           " in section '{$section['nameShort']}'.  No item having"     .
                           " name short '$itemNameShort' exists."
                        );
                     }
                  }
               }
            }
            break;

          default:
            throw new Exception("Unknown section type '$type'.");
         }
      }
   }

   /*
    *
    */
   private function _itemWithNameShortExists($itemNameShort)
   {
      return $this->_pdoEx->rowExistsInTable('item', array('nameShort' => $itemNameShort));
   }

   // Private variables. ///////////////////////////////////////////////////////////////////////

   private $_pdoEx                                          = null;
   private $_fieldNamesAsKeysBySectionNameShort             = null;
   private $_displayConditionsByFieldNameBySectionNameShort = null;
   private $_fieldByFieldNameBySectionNameShort             = null;
   private $_sectionBySectionNameShort                      = null;
}
?>
