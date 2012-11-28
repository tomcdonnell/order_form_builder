<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/Config.php';
require_once dirname(__FILE__) . '/CustomFormSectionBase.php';
require_once dirname(__FILE__) . '/FormUtils.php';
require_once dirname(__FILE__) . '/FormValidator.php';

/*
 * Include all custom class .php files.
 */
foreach (glob(dirname(__FILE__) . '/../../custom_form_sections/*') as $dirname)
{
   if (!is_dir($dirname))
   {
      throw new Exception("The '/custom_form_sections' directory should contain only directories.");
   }

   foreach (glob("$dirname/*") as $filename)
   {
      if (substr($filename, strlen($filename) - 4, 4) != '.php')
      {
         continue;
      }

      $lastSlashPos = strrpos($filename, '/');

      if (substr($filename, $lastSlashPos + 1, strlen('CustomFormSection')) == 'CustomFormSection')
      {
         require_once $filename;
      }
   }
}

/*
 *
 */
class CustomFormSectionUtils
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
   public static function getHtml($customSection, $postArray, $indent)
   {
      $customSectionNameShort = $customSection['nameShort'];
      $className = self::_getClassNameFromCustomFormSectionNameShort($customSectionNameShort);

      if (!class_exists($className))
      {
         throw new Exception("Custom section class '$className' not found.");
      }

      eval("\$sectionObj = new $className();"             );
      eval("\$filename = \$sectionObj->getHtmlFilename();");

      if (!is_string($filename))
      {
         throw new Exception("Function {$className}->getHtmlFilename() did not return a string.");
      }

      $customSectionDirectoryName = self::_getDirectoryNameFromNameShort($customSectionNameShort);
      $filenameIncludingPath      = Config::PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT .
      (
         "/custom_form_sections/$customSectionDirectoryName/$filename"
      );

      if (!file_exists($filenameIncludingPath))
      {
         throw new Exception("Custom section HTML file '$filenameIncludingPath' not found.");
      }

      $html = trim(file_get_contents($filenameIncludingPath));
      $html = self::_addInitialInputValuesFromPostArrayToHtml
      (
         $customSectionNameShort, $postArray, $html
      );

      $lines = explode("\n", $html);

      foreach ($lines as &$line)
      {
         $line = "$indent$line\n";
      }

      return implode('', $lines);
   }

   /*
    *
    */
   public static function getJsAndCssFilenames($customSectionNameShortShorts)
   {
      $filenamesJs  = array();
      $filenamesCss = array();

      foreach ($customSectionNameShortShorts as $customSectionNameShort)
      {
         $className    = self::_getClassNameFromCustomFormSectionNameShort($customSectionNameShort);
         $directoryName= self::_getDirectoryNameFromNameShort($customSectionNameShort);

         if (!class_exists($className))
         {
            throw new Exception("Custom section class '$className' not found.");
         }

         eval("\$sectionObj   = new $className();"               );
         eval("\$jsFilenames  = \$sectionObj->getJsFilenames();" );
         eval("\$cssFilenames = \$sectionObj->getCssFilenames();");

         if (!is_array($jsFilenames))
         {
            throw new Exception
            ("Function {$className}->getJsFilenames() did not return an array.");
         }

         if (!is_array($cssFilenames))
         {
            throw new Exception
            ("Function {$className}->getCssFilenames() did not return an array.");
         }

         foreach ($jsFilenames as &$jsFilename)
         {
            $jsFilename = "custom_form_sections/$directoryName/$jsFilename";
         }

         foreach ($cssFilenames as &$cssFilename)
         {
            $cssFilename = "custom_form_sections/$directoryName/$cssFilename";
         }

         $filenamesJs  = array_merge($filenamesJs , $jsFilenames );
         $filenamesCss = array_merge($filenamesCss, $cssFilenames);
      }

      return array
      (
         'js'  => $filenamesJs,
         'css' => $filenamesCss
      );
   }

   /*
    * Terminology: fieldNames and inputIdAttributes
    * ---------------------------------------------
    * 'fieldName':
    *    The value corresponding to the key 'name' in a field within a form.
    * 'inputIdAttribute':
    *    The id attibute of the input corresponding to a field in the HTML that is generated from
    *    the form.  It will be of the form: "{$section['nameShort']}|$field['name']}".
    */
   public static function getInputIdAttributesForCustomSection($customSectionNameShort)
   {
      $className = self::_getClassNameFromCustomFormSectionNameShort($customSectionNameShort);

      if (!class_exists($className))
      {
         throw new Exception("Custom section class '$className' not found.");
      }

      eval("\$sectionObj        = new $className();"                    );
      eval("\$inputIdAttributes = \$sectionObj->getInputIdAttributes();");

      return $inputIdAttributes;
   }

   /*
    * See comment headed 'Terminology: fieldNames and inputIdAttributes' above.
    */
   public static function getFieldNamesAsKeysForCustomSection($customSectionNameShort)
   {
      $inputIdAttributes = self::getInputIdAttributesForCustomSection($customSectionNameShort);
      $fieldNames        = array();

      foreach ($inputIdAttributes as $idAttribute)
      {
         $fieldNames[] = substr($idAttribute, strpos($idAttribute, '|') + 1);
      }

      $nFieldNames = count($fieldNames);

      return
      (
         ($nFieldNames > 0)? array_combine($fieldNames, array_fill(0, $nFieldNames, null)): array()
      );
   }

   // Private functions. ///////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _getSectionHeading($customSectionNameShort)
   {
      $string1 = " CUSTOM FORM SECTION: $customSectionNameShort ";
      $string2 = str_repeat(' ', 98 - strlen($string1));

      $str  = '/' . str_repeat('*', 98) . "*\n";
      $str .= '*' . str_repeat(' ', 98) . "*\n";
      $str .= '*' . "$string1$string2"  . "*\n";
      $str .= '*' . str_repeat(' ', 98) . "*\n";
      $str .= '*' . str_repeat('*', 98) . "*/\n\n";

      return $str;
   }

   /*
    * Convert an all-lowercase-dash-separated name to a
    * camelCased name prefixed with 'CustomFormSection'.
    *
    * Eg. "contact-details" becomes "CustomFormSectionContactDetails".
    */
   private static function _getClassNameFromCustomFormSectionNameShort($customSectionNameShort)
   {
      $className = '';

      for ($i = 0, $len = strlen($customSectionNameShort); $i < $len; ++$i)
      {
         $char       = $customSectionNameShort[$i];
         $className .= ($char == '-')? strtoupper($customSectionNameShort[++$i]): $char;
      }

      return 'CustomFormSection' . ucfirst($className);
   }

   /*
    * Convert an all-lowercase-dash-separated name to an all_lowercase_underscore_separated_name.
    *
    * Eg. "contact-details" becomes "contact_details".
    */
   private static function _getDirectoryNameFromNameShort($customSectionNameShort)
   {
      return str_replace('-', '_', $customSectionNameShort);
   }

   /*
    *
    */
   private static function _addInitialInputValuesFromPostArrayToHtml
   (
      $customSectionNameShort, $postArray, $html
   )
   {
      $customSectionNameShortStrlen = strlen($customSectionNameShort);

      foreach ($postArray as $key => $value)
      {
         if (substr($key, 0, $customSectionNameShortStrlen) != $customSectionNameShort)
         {
            continue;
         }

         try
         {
            $idAttributeStrlen = strlen("id='$key'");
            $idAttributeStrpos = self::_getIdAttributeStringPos($html, $key);

            if ($idAttributeStrpos === false)
            {
               throw new Exception("No element identification string \"id='$key'\" found.");
            }

            $inputTagName = self::_getLastTagNameInHtmlBeforeIndex($html, $idAttributeStrpos);

            switch ($inputTagName)
            {
             case 'select':
               $html = self::_modifyHtmlToSetDefaultSelectedOptionForSelector
               (
                  $html, $idAttributeStrpos, $value
               );
               break;

             case 'input':
               $html = self::_modifyHtmlToSetDefaultValueForInput
               (
                  $html, $idAttributeStrpos, $value
               );
               break;

             case 'textarea':
               $html = self::_modifyHtmlToSetDefaultValueForTextarea
               (
                  $html, $idAttributeStrpos, $value
               );
               break;

             default:
               throw new Exception("Unknown input tag name '$inputTagName'.");
            }

            $tagNameAngledBracketStrpos = strrpos($html, '<', $idAttributeStrpos);
         }
         catch (Exception $e)
         {
            throw new Exception
            (
               'Exception caught while adding default value to input' .
               " with id '$key' in custom section '$customSectionNameShort'.__|__" .
               $e->getMessage()
            );
         }
      }

      return $html;
   }

   /*
    *
    */
   private static function _getIdAttributeStringPos($html, $idAttribute)
   {
      $singleQuotePos = strpos($html, "id='$idAttribute'"  );
      $doubleQuotePos = strpos($html, "id=\"$idAttribute\"");

      if ($singleQuotePos !== false) {return $singleQuotePos;}
      if ($doubleQuotePos !== false) {return $singleQuotePos;}

      throw new Exception("Could not find \"id='$idAttribute'\" substring.");
   }

   /*
    *
    */
   private static function _getLastTagNameInHtmlBeforeIndex($html, $index)
   {
      $reverseIndex        = strlen($html) - $index;
      $angledBracketStrpos = strrpos($html, '<', -$reverseIndex);

      if ($angledBracketStrpos === false)
      {
         throw new Exception("No angled bracket '<' found before index $index.");
      }

      $testStringByTagName = array
      (
         'input'    => substr($html, $angledBracketStrpos + 1, strlen('input'   )),
         'select'   => substr($html, $angledBracketStrpos + 1, strlen('select'  )),
         'textarea' => substr($html, $angledBracketStrpos + 1, strlen('textarea'))
      );

      foreach ($testStringByTagName as $tagName => $testString)
      {
         if (strtolower($testString) == $tagName)
         {
            return $tagName;
         }
      }

      throw new Exception('Could not parse input element tag name.');
   }

   /*
    *
    */
   private static function _modifyHtmlToSetDefaultSelectedOptionForSelector
   (
      $html, $idAttributeStrpos, $value
   )
   {
      $closingSelectTagStrpos  = strpos($html, '</select>'              , $idAttributeStrpos);
      $matchingOptionStringPos = strpos($html, "<option>$value</option>", $idAttributeStrpos);

      if ($closingSelectTagStrpos === false)
      {
         throw new Exception('Could not find closing select tag "</select>".');
      }

      if ($matchingOptionStringPos === false)
      {
         throw new Exception("Could not find option '<option>$value</option>'.");
      }

      $strposToInsertSelectedString = $matchingOptionStringPos + strlen('<option');

      return
      (
         substr($html, 0, $strposToInsertSelectedString) . " selected='selected'" .
         substr($html,    $strposToInsertSelectedString)
      );
   }

   /*
    * Insert a "value='$value'" string into a given html string at the appropriate position.
    */
   private static function _modifyHtmlToSetDefaultValueForInput
   (
      $html, $idAttributeStrpos, $value
   )
   {
      return
      (
         substr($html, 0, $idAttributeStrpos)               .
         "value='" . Utils_html::escapeSingleQuotes($value) . "' " .
         substr($html,    $idAttributeStrpos)
      );
   }

   /*
    *
    */
   private static function _modifyHtmlToSetDefaultValueForTextarea
   (
      $html, $idAttributeStrpos, $value
   )
   {
      $angledBracketStrpos = strpos($html, '>', $idAttributeStrpos);

      if ($angledBracketStrpos === false)
      {
         throw new Exception('Could not find close of textarea tag.');
      }

      return
      (
         substr($html, 0, $angledBracketStrpos + 1) . htmlentities($value) .
         substr($html,    $angledBracketStrpos + 1)
      );
   }
}
?>
