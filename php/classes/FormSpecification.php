<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
class FormSpecification
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function getFormSpecification()
   {
      return self::$_formSpecification;
   }

   /*
    *
    */
   public static function getSectionSpecification($sectionType)
   {
      if (!array_key_exists($sectionType, self::$_sectionSpecificationBySectionType))
      {
         throw new Exception("Unknown section type '$sectionType'.");
      }

      return self::$_sectionSpecificationBySectionType[$sectionType];
   }

   /*
    *
    */
   public static function getFieldSpecification($fieldType)
   {
      if (!array_key_exists($fieldType, self::$_fieldSpecificationByFieldType))
      {
         throw new Exception("Unknown field type '$fieldType'.");
      }

      return self::$_fieldSpecificationByFieldType[$fieldType];
   }

   /*
    *
    */
   public static function getSelectOptionSpecification()
   {
      return self::$_selectOptionSpecification;
   }

   /*
    *
    */
   public static function getDisplayConditionSpecification()
   {
      return self::$_displayConditionSpecification;
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private static $_formSpecification = array
   (
      'compulsoryTypeByKey' => array('sections' => 'array'),
      'optionalTypeByKey'   => array()
   );

   private static $_sectionSpecificationBySectionType = array
   (
      'custom' => array
      (
         'compulsoryTypeByKey' => array
         (
            'type'      => 'string', // Must be 'custom'.
            'nameShort' => 'string',
            'nameLong'  => 'string'
         ),
         'optionalTypeByKey' => array()
      ),
      'normal' => array
      (
         'compulsoryTypeByKey' => array
         (
            'type'      => 'string', // Must be 'normal'.
            'nameShort' => 'string',
            'nameLong'  => 'string',
            'fields'    => 'array'
         ),
         'optionalTypeByKey' => array()
      )
   );

   /*
    * Notes
    * -----
    *  * 'displayConditions'
    *      Determines under what conditions the field will be displayed.
    *      Field will be displayed if condition is true.
    *
    *  * 'classString'
    *      A string consisting of space separated CSS classes to add to the field element.
    *      Special Classes Known by the Client:
    *       * 'not-valid-selection'
    *         Use for select options if the select field should be considered incomplete when the
    *         option is selected.  Useful for instruction options.
    *       * 'optional-field'
    *         Use for input fields that are not compulsory.  Fields having this class will never be
    *         highlighted as being incomplete.
    *
    * Custom Form Sections
    * --------------------
    *  * The nameShort of the custom section should match the directory name in
    *    /custom_section_definitions that contains the files defining the custom section,
    *    with the following modification: dashes (-) in the custom section name short will be
    *    replaced with underscores '_' to get the directory name.
    */
   private static $_fieldSpecificationByFieldType = array
   (
      'paragraph' => array
      (
         'compulsoryTypeByKey' => array
         (
            'name' => 'string',
            'type' => 'string', // Must be 'paragraph'.
            'html' => 'string'
         ),
         'optionalTypeByKey' => array
         (
            'displayConditions' => 'array' , // Default: empty array.
            'classString'       => 'string'  // Default: empty string.
         )
      ),
      'select' => array
      (
         'compulsoryTypeByKey' => array
         (
            'name'                      => 'string',
            'defaultSelectedOptionText' => 'string', // Must match one of the options.
            'type'                      => 'string', // Must be 'select'.
            'questionHtml'              => 'string',
            'options'                   => 'array'   // See self::$_selectOptionSpecification below.
         ),
         'optionalTypeByKey' => array
         (
            'displayConditions' => 'array', // Default: empty array.
            'classString'       => 'string' // Default: empty string.
         )
      ),
      'text' => array
      (
         'compulsoryTypeByKey' => array
         (
            'name'         => 'string',
            'type'         => 'string', // Must be 'text'.
            'questionHtml' => 'string'
         ),
         'optionalTypeByKey' => array
         (
            'displayConditions' => 'array', // Default: empty array.
            'classString'       => 'string' // Default: empty string.
         )
      ),
      'textarea' => array
      (
         'compulsoryTypeByKey' => array
         (
            'name'         => 'string',
            'type'         => 'string', // Must be 'textarea'.
            'questionHtml' => 'string'
         ),
         'optionalTypeByKey' => array
         (
            'displayConditions' => 'array', // Default: empty array.
            'classString'       => 'string' // Default: empty string.
         )
      )
   );

   private static $_selectOptionSpecification = array
   (
      'compulsoryTypeByKey' => array
      (
         'text' => 'string'
      ),
      'optionalTypeByKey' => array
      (
         'itemNameShortsToAddIfSelected' => 'array', // Default: empty array.
         'classString'                   => 'string' // Default: empty string.
      )
   );

   private static $_displayConditionSpecification = array
   (
      'compulsoryTypeByKey' => array
      (
         'sectionNameShort' => 'string',
         'fieldName'        => 'string',
         'value'            => 'string'
      ),
      'optionalTypeByKey' => array()
   );
}
?>
