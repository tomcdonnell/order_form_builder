<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/CustomFormSectionUtils.php';
require_once dirname(__FILE__) . '/FormBase.php';

/*
 *
 */
class PostArrayValidator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct(FormBase $form)
   {
      $this->form = $form;
   }

   /*
    *
    */
   public function assertPostArrayKeysAndValuesAreAsExpected(PdoExtended $pdoEx, Array $postArray)
   {
      $submitButtonName =
      (
         $this->_assertPostArrayKeysAreAsExpectedAndReturnSubmitButtonName($postArray)
      );

      $this->_assertPostArrayValuesAreAsExpected
      (
         $pdoEx, $postArray, $submitButtonName
      );
   }

   /*
    *
    */
   public static function getExtraPostArrayKeysNotToBeSaved()
   {
      return array
      (
         'formTypeNameShort'             ,
         'save-without-submitting-button',
         'save-and-submit-button'
      );
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function _assertPostArrayKeysAreAsExpectedAndReturnSubmitButtonName(Array $postArray)
   {
      $expectedPostKeysInfo = $this->_getExpectedPostKeysInfo();

      if (!array_key_exists('formTypeNameShort', $postArray))
      {
         throw new Exception("Expected key 'formTypeNameShort' not found in \$_POST array.");
      }

      $boolSws = array_key_exists('save-without-submitting-button', $postArray);
      $boolSas = array_key_exists('save-and-submit-button'        , $postArray);

      switch ((($boolSws)? '1': '0') . '-' . (($boolSas)? '1': '0'))
      {
       case '0-1': $submitButtonName = 'save-and-submit-button'        ; break;
       case '1-0': $submitButtonName = 'save-without-submitting-button'; break;
       case '0-0': throw new Exception('Neither expected submit key found in $_POST array.');
       case '1-1': throw new Exception('Both expected submit keys found in $_POST array.'  );
       default   : throw new Exception('Impossible case.'                                  );
      }

      // Check that compulsory keys are present.
      foreach ($expectedPostKeysInfo['compulsoryKeys'] as $key)
      {
         if (!array_key_exists($key, $postArray))
         {
            throw new Exception
            (
               "Compulsory key '$key' not present in \$_POST array." .
               '__|__' . var_export($postArray           , true)     .
               '__|__' . var_export($expectedPostKeysInfo, true)
            );
         }
      }

      $this->_assertConditionallyCompulsoryKeysArePresentIfConditionsMet
      (
         $expectedPostKeysInfo, $postArray
      );

      // Check that all other keys are expected optional keys.
      $extraKeys = array_keys
      (
         array_diff_key
         (
            $postArray                                                    ,
            $expectedPostKeysInfo['conditionsByConditionalCompulsoryKey'] ,
            array_fill_keys($expectedPostKeysInfo['compulsoryKeys'], null),
            array_fill_keys($expectedPostKeysInfo['optionalKeys'  ], null),
            array
            (
               // Expected extra keys.
               'formTypeNameShort'              => null, // To identify the form type.
               'save-and-submit-button'         => null, // -\_ Submit
               'save-without-submitting-button' => null  // -/  Buttons.
            )
         )
      );

      if (count($extraKeys) > 0)
      {
         throw new Exception
         (
            "Unexpected extra keys found in \$_POST array: '" . implode("','", $extraKeys)
         );
      }

      return $submitButtonName;
   }

   /*
    *
    */
   private function _assertConditionallyCompulsoryKeysArePresentIfConditionsMet
   (
      Array $expectedPostKeysInfo, Array $postArray
   )
   {
      foreach ($expectedPostKeysInfo['conditionsByConditionalCompulsoryKey'] as $key => $conditions)
      {
         // Key $key should exist in $postArray if and only if all conditions are true.
         $boolAllConditionsAreTrue = true;
         $boolArrayKeyExists       = array_key_exists($key, $postArray);

         foreach ($conditions as $condition)
         {
            $conditionInputName = "{$condition['sectionNameShort']}|{$condition['fieldName']}";

            if
            (
               !array_key_exists($conditionInputName, $postArray) ||
               $postArray[$conditionInputName] != $condition['value']
            )
            {
               $boolAllConditionsAreTrue = false;
            }
         }

         switch ((($boolAllConditionsAreTrue)? '1': '0') . '-' . (($boolArrayKeyExists)? '1': '0'))
         {
          case '0-0': // Fall through.
          case '1-1':
            // No problem.
            break;
          case '0-1':
            throw new Exception
            (
               "Conditional compulsory key '$key' exists in \$_POST despite not all" .
               " display conditions being true."                                     .
               '__|__' . var_export($postArray           , true)                     .
               '__|__' . var_export($expectedPostKeysInfo, true)
            );
          case '1-0':
            throw new Exception
            (
               "Conditional compulsory key '$key' does not exist in \$_POST despite all" .
               " display conditions being true."                                         .
               '__|__' . var_export($postArray           , true)                         .
               '__|__' . var_export($expectedPostKeysInfo, true)
            );
          default:
            throw new Exception('Impossible case.');
         }
      }
   }

   /*
    *
    */
   private function _assertPostArrayValuesAreAsExpected
   (
      PdoExtended $pdoEx, Array $postArray, $submitButtonName
   )
   {
      if
      (
         !$pdoEx->rowExistsInTable
         (
            'form_type', array('nameShort' => $postArray['formTypeNameShort'])
         )
      )
      {
         throw new Exception
         (
            "No form_type found matching form type name short '{$postArray['formTypeNameShort']}'."
         );
      }

      foreach ($postArray as $key => $value)
      {
         if (in_array($key, self::getExtraPostArrayKeysNotToBeSaved()))
         {
            continue;
         }

         list($sectionNameShort, $fieldName) = explode('|', $key);

         $section = $this->form->getSectionMatchingNameShort($sectionNameShort);

         if ($section['type'] == 'custom')
         {
            $fieldIdAttributes =
            (
               CustomFormSectionUtils::getInputIdAttributesForCustomSection($sectionNameShort)
            );

            if (!in_array($key, $fieldIdAttributes))
            {
               throw new Exception("Unexpected key '$key' found in $_POST array.");
            }

            continue;
         }

         $field = $this->form->getFieldMatchingNameFromNormalSection($section, $fieldName);

         switch ($field['type'])
         {
          case 'select':
            $optionValues = FormUtils::getOptionValuesFromSelectField
            (
               $field, ($submitButtonName == 'save-and-submit')
            );
            if (!in_array($value, $optionValues))
            {
               throw new Exception
               (
                  "Unexpected or not-valid option value '$value'" .
                  " found for select field with id attribute '$key'."
               );
            }
            break;
          case 'text'    : // Fall through.
          case 'textarea':
            // Text and textarea inputs are unrestricted.  No need for validation.
            break;
          default:
            throw new Exception("Unknown field type '{$field['type']}'.");
         }
      }
   }

   /*
    *
    */
   private function _getExpectedPostKeysInfo()
   {
      $form                                 = $this->form->getAsPhpArray();
      $compulsoryKeysAsKeys                 = array();
      $optionalKeysAsKeys                   = array();
      $conditionsByConditionalCompulsoryKey = array();

      foreach ($form['sections'] as $section)
      {
         $sectionType = $section['type'];

         switch ($sectionType)
         {
          case 'normal':
            foreach ($this->_getCompulsoryPostKeys($form) as $key)
            {
               $compulsoryKeysAsKeys[$key] = null;
            }
            $conditionsByConditionalCompulsoryKey = array_merge
            (
               $conditionsByConditionalCompulsoryKey,
               $this->_getConditionByConditionalCompulsoryPostKey($form)
            );
            break;

          case 'custom':
            $sectionNameShort = $section['nameShort'];
            $keys = CustomFormSectionUtils::getInputIdAttributesForCustomSection($sectionNameShort);
            foreach ($keys as $key)
            {
               $optionalKeysAsKeys[$key] = null;
            }
            break;

          default:
            throw new Exception("Unknown section type '$sectionType'.");
         }
      }

      return array
      (
         'compulsoryKeys'                       => array_keys($compulsoryKeysAsKeys),
         'optionalKeys'                         => array_keys($optionalKeysAsKeys  ),
         'conditionsByConditionalCompulsoryKey' => $conditionsByConditionalCompulsoryKey
      );
   }

   /*
    *
    */
   private function _getCompulsoryPostKeys(Array $form)
   {
      $compulsoryKeys = array();

      foreach ($form['sections'] as $section)
      {
         if ($section['type'] == 'custom')
         {
            continue;
         }

         foreach ($section['fields'] as $field)
         {
            if ($field['type'] == 'paragraph')
            {
               continue;
            }

            if
            (
               !array_key_exists('displayConditions', $field) ||
               count($field['displayConditions']) == 0
            )
            {
               $fieldIdAttribute = "{$section['nameShort']}|{$field['name']}";
               $compulsoryKeys[] = $fieldIdAttribute;
            }
         }
      }

      return $compulsoryKeys;
   }

   /*
    *
    */
   private function _getConditionByConditionalCompulsoryPostKey(Array $form)
   {
      $conditionByKey = array();

      foreach ($form['sections'] as $section)
      {
         if ($section['type'] == 'custom')
         {
            continue;
         }

         foreach ($section['fields'] as $field)
         {
            if ($field['type'] == 'paragraph')
            {
               continue;
            }

            if
            (
               array_key_exists('displayConditions', $field) &&
               count($field['displayConditions']) > 0
            )
            {
               $fieldIdAttribute = "{$section['nameShort']}|{$field['name']}";
               $conditionByKey[$fieldIdAttribute] = $field['displayConditions'];
            }
         }
      }

      return $conditionByKey;
   }
}
?>
