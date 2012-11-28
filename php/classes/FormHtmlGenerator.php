<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/Config.php';
require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_html.php';
require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_misc.php';
require_once dirname(__FILE__) . '/CustomFormSectionUtils.php';

/*
 * Note Regarding Validation of the Supplied Form 
 * ---------------------------------------------------------
 * No validation of the supplied form is done in this file because the JSON defining the form (in
 * '/form_definitions' is assumed to already have been validated using the FormValidator class.
 */
class FormHtmlGenerator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct ()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function echoFormHtml
   (
      $formTypeNameShort, $idForm, Array $form,
      Array $postArray, $postArrayPrevRevisionOrNull, $indent
   )
   {
      echo self::_getFormHtml
      (
         $formTypeNameShort, $idForm, $form,
         $postArray, $postArrayPrevRevisionOrNull, $indent
      );
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _getFormHtml
   (
      $formTypeNameShort, $idForm, Array $form,
      Array $postArray, $postArrayPrevRevisionOrNull, $indent
   )
   {
      $i             = &$indent; // Abbreviation.
      $html          = '';
      $formActionUrl =
      (
         'http://' .
         Config::DOMAIN_NAME . Config::PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT .
         '/php/save_form.php' . (($idForm === null)? '': "?idForm=$idForm")
      );

      $html .= "$i<form id='main-form' enctype='multipart/form-data' method='post'";
      $html .=   " action='" . Utils_html::escapeSingleQuotes($formActionUrl) . "'>\n";
      $html .= "$i <input type='hidden' name='formTypeNameShort' value='";
      $html .= Utils_html::escapeSingleQuotes($formTypeNameShort);
      $html .=    "'>\n";
      $html .= "$i <div class='form-div'>\n";
      $html .= "$i  <div id='failure-message-div'><p></p></div>\n";
      $html .= "$i  <h1 class='form-title'>";
      $html .=      "DPI " . htmlentities(ucfirst($formTypeNameShort)) . ' Order Form';
      $html .=     "</h1>\n";
      $html .= "$i  <div>";
      $html .=      "<span class='red'>";
      $html .=       "Compulsory uncompleted fields are highlighted in red.";
      $html .=      '</span>';
      $html .=     "</div>\n";

      foreach ($form['sections'] as $section)
      {
         $html .= self::_getFormSectionHtml($section,
         $postArray, $postArrayPrevRevisionOrNull, "$i  ");
      }

      $html .= "$i  <fieldset class='submit-fieldset'>\n";
      $html .= "$i   <input type='submit'";
      $html .=      " id='save-without-submitting-button' name='save-without-submitting-button'";
      $html .=      " value='Save Form Without Submitting' title='This button will be enabled";
      $html .=      " when all compulsory fields have been completed.' disabled='disabled'>\n";
      $html .= "$i   <input type='submit'";
      $html .=      " id='save-and-submit-button' name='save-and-submit-button'";
      $html .=      " value='Save and Submit Form' title='This button will be enabled when all";
      $html .=      " compulsory fields have been completed.' disabled='disabled'>\n";
      $html .= "$i  </fieldset>\n";
      $html .= "$i </div>\n";
      $html .= "$i</form>\n";

      return $html;
   }

   /*
    *
    */
   private static function _getFormSectionHtml
   (
      Array $section, Array $postArray, $postArrayPrevRevisionOrNull, $indent
   )
   {
      $i = &$indent; // Abbreviation.

      if ($section['type'] == 'custom')
      {
         return CustomFormSectionUtils::getHtml($section, $postArray, $indent);
      }

      $sectionNameShort = $section['nameShort'];
      $fieldsetClass    = FormUtils::getFieldsetCssClassFromSectionName($sectionNameShort);

      $html  = "$i<fieldset class='" . Utils_html::escapeSingleQuotes($fieldsetClass) . "'>\n";
      $html .= "$i <legend>" . htmlentities($section['nameLong']) . "</legend>\n";
      $html .= "$i <ul>\n";

      foreach ($section['fields'] as $field)
      {
         $fieldType = $field['type'];
         $fieldId   = FormUtils::getFieldIdAttributeFromSectionNameFieldName
         (
            $sectionNameShort, $field['name']
         );

         $fieldValue = (array_key_exists($fieldId, $postArray))? $postArray[$fieldId]: null;
         $fieldValuePrevRevision =
         (
            ($postArrayPrevRevisionOrNull === null)? null:
            (
               (array_key_exists($fieldId, $postArrayPrevRevisionOrNull))?
               $postArrayPrevRevisionOrNull[$fieldId]: null
            )
         );

         $classString =
         (
            ($fieldValuePrevRevision !== null && $fieldValue != $fieldValuePrevRevision)?
            "class='value-changed-since-previous-revision' ": ''
         );

         $html       .= "$i  <li id='" . Utils_html::escapeSingleQuotes("$fieldId-li") . "'";
         $html       .=    " ${classString}style='display: none'>\n";
         $indentParam = "$i   ";

         switch ($fieldType)
         {
          case 'select':
            $html .= self::_getLabelHtml($fieldId, $field, $indentParam);
            $html .= self::_getSelectHtml($fieldId, $field, $fieldValue, $indentParam);
            break;

          case 'text':
            $html .= self::_getLabelHtml($fieldId, $field, $indentParam);
            $html .= self::_getTextInputHtml($fieldId, $fieldValue, $indentParam);
            break;

          case 'textarea':
            $html .= self::_getLabelHtml($fieldId, $field, $indentParam);
            $html .= self::_getTextareaHtml($fieldId, $fieldValue, $indentParam);
            break;

          case 'paragraph':
            $html .= self::_getParagraphHtml($fieldId, $field['html'], $indentParam);
            break;

          default:
            throw new Exception("Unknown field type '$fieldType'.");
         }

         $html .= "$i  </li>\n";
      }

      $html .= "$i </ul>\n";
      $html .= "$i</fieldset>\n";

      return $html;
   }

   /*
    *
    */
   private static function _getLabelHtml($fieldId, Array $field, $indent)
   {
      $html  = "$indent<label for='" . Utils_html::escapeSingleQuotes($fieldId) . "'>";
      $html .=            $field['questionHtml'];
      $html .= "</label>\n";
      return $html;
   }

   /*
    *
    */
   private static function _getSelectHtml($fieldId, Array $field, $fieldValue, $indent)
   {
      $fieldClass = Utils_misc::arrayValueOrBlank('classString', $field);

      $i     = &$indent; // Abbreviation.
      $html  = "$i<select id='" . Utils_html::escapeSingleQuotes($fieldId) . "'";
      $html .=   " name='"      . Utils_html::escapeSingleQuotes($fieldId) . "'";
      $html .=
      (
         ($fieldClass != '')?
         " class='" . Utils_html::escapeSingleQuotes($fieldClass) . "'>\n": ">\n"
      );

      foreach ($field['options'] as $option)
      {
         $optionText              = $option['text'];
         $escapedAttributesString =
         (
            (array_key_exists('classString', $option))?
            " class='" . Utils_html::escapeSingleQuotes($option['classString']) . "'": ''
         );

         if ($fieldValue === $optionText)
         {
            $escapedAttributesString .= " selected='selected'";
         }

         $html .= "$i <option$escapedAttributesString>";
         $html .=      Utils_html::escapeSingleQuotes($optionText);
         $html .=    "</option>\n";
      }

      $html .= "$i</select>\n";

      return $html;
   }

   /*
    *
    */
   private static function _getTextInputHtml($fieldId, $fieldValue, $indent)
   {
      return "$indent<input type='text'" .
      (
         " id='"   . Utils_html::escapeSingleQuotes($fieldId) . "'" .
         " name='" . Utils_html::escapeSingleQuotes($fieldId) . "'" .
         (
            ($fieldValue !== null)?
            " value='" . Utils_html::escapeSingleQuotes($fieldValue) . "'": ''
         ) .
         "/>\n"
      );
   }

   /*
    *
    */
   private static function _getTextareaHtml($fieldId, $fieldValue, $indent)
   {
      return "$indent<textarea cols='35' rows='4'" .
      (
         " id='"   . Utils_html::escapeSingleQuotes($fieldId) . "'"  .
         " name='" . Utils_html::escapeSingleQuotes($fieldId) . "'>" .
         (
            ($fieldValue !== null)? htmlentities($fieldValue): ''
         ) .
         "</textarea>\n"
      );
   }

   /*
    *
    */
   private static function _getParagraphHtml($fieldId, $html, $indent)
   {
      return "$indent<p" .
      (
         " id='" . Utils_html::escapeSingleQuotes($fieldId) . "'>$html</p>\n"
      );
   }
}
?>
