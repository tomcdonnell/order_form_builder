<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/Config.php';
require_once dirname(__FILE__) . '/../../config/database.php';
require_once dirname(__FILE__) . '/CustomFormSectionUtils.php';
require_once dirname(__FILE__) . '/FormValidator.php';

/*
 *
 */
abstract class FormBase
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct(PdoExtended $pdoEx)
   {
      try
      {
         // Note Regarding Validation
         // -------------------------
         // Since validation of the form array is done here in the constructor, the form array is
         // assumed to be valid everywhere else (meaning existence of compulsory keys need not be
         // checked).
         $formValidator = new FormValidator($pdoEx);
         $formValidator->assertFormArrayIsValid($this->getAsPhpArray());
      }
      catch (Exception $e)
      {
         die($e->getMessage() . '|' . $e->getTraceAsString());
      }
   }

   /*
    *
    */
   public abstract function getAsPhpArray();

   /*
    *
    */
   public function getAsJsonString()
   {
      return json_encode($this->getAsPhpArray());
   }

   /*
    *
    */
   public function getAllCustomSectionNames()
   {
      static $customSectionNames = null;

      if ($customSectionNames === null)
      {
         // Note Regarding Uniqueness of Custom Section Names
         // -------------------------------------------------
         // That all custom section names in the form are unique
         // has already been checked in the FormValidator.

         $form               = $this->getAsPhpArray();
         $customSectionNames = array();

         foreach ($form['sections'] as $section)
         {
            if ($section['type'] == 'custom')
            {
               $customSectionNames[] = $section['nameShort'];
            }
         }
      }

      return $customSectionNames;
   }

   /*
    *
    */
   public function getSectionMatchingNameShort($sectionNameShort)
   {
      static $sectionByNameShort = null;

      if ($sectionByNameShort === null)
      {
         $form     = $this->getAsPhpArray();
         $sections = $form['sections'];

         foreach ($sections as $section)
         {
            $sectionByNameShort[$section['nameShort']] = $section;
         }
      }

      if (!array_key_exists($sectionNameShort, $sectionByNameShort))
      {
         throw new Exception("No section found matching name short '$sectionNameShort'.");
      }

      return $sectionByNameShort[$sectionNameShort];
   }

   /*
    *
    */
   public function getFieldMatchingNameFromNormalSection
   (
      $section, $fieldName
   )
   {
      if ($section['type'] != 'normal')
      {
         throw new Exception('Non-normal section supplied.');
      }

      $fields = $section['fields'];

      foreach ($fields as $field)
      {
         if ($field['name'] == $fieldName)
         {
            return $field;
         }
      }

      throw new Exception("Could not find field matching field id attribute '$fieldIdAttribute'.");
   }
}
?>
