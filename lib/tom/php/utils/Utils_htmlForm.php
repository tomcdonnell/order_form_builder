<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_htmlForm.php"
*
* Project: Utilities.
*
* Purpose: Miscellaneous utilities.
*
* Author: Tom McDonnell 2010-02-28.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/Utils_html.php';
require_once dirname(__FILE__) . '/Utils_validator.php';

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_htmlForm
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   /*
    * @param $options {Array}
    *    array
    *    (
    *       <string optionValue> =>
    *          <string optionText OR array('text' => <string>, 'title' => <string>)>
    *       ...
    *    )
    */
   public static function getSelectorHtml
   (
      $nameAndId, $options, $instructionOptionArrayOrNull,
      $indent, $selectedOptionValue = null, $class = null, $boolDisabled = false
   )
   {
      // Selected option value must be null (if no option is to be selected by this function), or
      // a string.  This is to avoid confusion due to bizarre behaviour of PHP loose comparisons.
      // ('' == 0) === true.  ('0' == 0) === true.  ('php' == 0) === true.  ('1' == 0) === false.
      // So if there are options with values '0' and 'blank', and the $selectedOptionValue is given
      // as 0, both would be selected if loose comparisons were used.
      // See http://www.php.net/manual/en/types.comparisons.php.
      Utils_validator::checkType($selectedOptionValue, 'nullOrString');

      $i                       = &$indent;
      $classStr                = ($class === null)? ''                      : " class='$class'";
      $disabledStr             = ($boolDisabled  )? ' disabled=\'disabled\'': ''               ;
      $boolFoundSelectedOption = false;

      $html = "$i<select name='$nameAndId' id='$nameAndId'$disabledStr$classStr>\n";

      if ($instructionOptionArrayOrNull !== null)
      {
         $value = $instructionOptionArrayOrNull[0];
         $text  = $instructionOptionArrayOrNull[1];

         if (array_key_exists($value, $options))
         {
            throw new Exception("Value supplied for instruction option '$value' already exists.");
         }

         // NOTE: This is not the same as array_merge(array($value => $text), $options)).
         $options = array_combine
         (
            array_merge(array($value), array_keys($options)  ),
            array_merge(array($text ), array_values($options))
         );
      }

      if (array_key_exists('', $options))
      {
         throw new Exception
         (
            'Option with empty string key found.  Use of this key is not allowed because the' .
            ' Firefox browser confuses options having zero key with options having blank key.'
         );
      }

      foreach ($options as $value => $text)
      {
         if (is_array($text))
         {
            Utils_validator::checkArray($text, array('text' => 'string', 'title' => 'string'));
            $title = $text['title'];
            $text  = $text['text' ];
         }
         else
         {
            $title = '';
         }

         // Convert $value to string to ensure that all comparisons are string::string and so
         // avoid loose comparison confusion.  Eg. ('' == 0) === true.  ('blank' == 0) === true.
         $value = (string)$value;

         if ($selectedOptionValue !== null && $selectedOptionValue == $value)
         {
            $boolFoundSelectedOption = true;
            $selectedStr             = ' selected="selected"';
         }
         else
         {
            $selectedStr = '';
         }

         $html .=
         (
            "$i <option value='" . Utils_html::escapeSingleQuotes($value) . "'$selectedStr" .
            (($title == '')? '': " title='$title'") . ">" . htmlentities($text) . "</option>\n"
         );
      }

      $html .= "$i</select>\n";

      if ($selectedOptionValue !== null && !$boolFoundSelectedOption)
      {
         throw new Exception("Could not find option with value '$selectedOptionValue'.");
      }

      return $html;
   }

   /*
    *
    */
   public static function echoSelectorHtml
   (
      $nameAndId, $options, $instructionOptionArray = null,
      $indent, $selectedOptionValue = null, $class = null, $boolDisabled = false
   )
   {
      echo self::getSelectorHtml
      (
         $nameAndId, $options, $instructionOptionArray,
         $indent, $selectedOptionValue, $class, $boolDisabled
      );
   }

   /*
    *
    */
   public static function getRadioButtonsHtml
   (
      $name, $buttonInfoByValue, $indent,
      $checkedValue = null, $inputClass = null, $labelClass = null
   )
   {
      $htmlStrings = array();
      $i           = &$indent; // Abbreviation.
      $n           = 0;

      foreach ($buttonInfoByValue as $value => $info)
      {
         Utils_validator::checkArrayAndSetDefaults
         (
            $info, array('text' => 'string'), array('title' => array('nullOrString', null))
         );
      
         $escapedClass = Utils_html::escapeSingleQuotes($inputClass);
         $escapedName  = Utils_html::escapeSingleQuotes($name      );
         $escapedValue = Utils_html::escapeSingleQuotes($value     );
         $escapedId    = $escapedName . '_option' . ++$n;

         $attributeStringsForInput = array
         (
            "id='$escapedId'"    ,
            "name='$escapedName'",
            "type='radio'"       ,
            "value='$escapedValue'"
         );

         $attributeStringsForLabel = array
         (
            "for='$escapedId'"
         );

         if ($inputClass !== null) {$attributeStringsForInput[] = "class='$inputClass'";}
         if ($labelClass !== null) {$attributeStringsForLabel[] = "class='$labelClass'";}

         if ($checkedValue !== null && $checkedValue == $value)
         {
            $attributeStringsForInput[] = "checked='checked'";
         }

         if ($info['title'] !== null)
         {
            $escapedTitle               = Utils_html::escapeSingleQuotes($info['title']);
            $attributeStringsForInput[] = "title='$escapedTitle'";
            $attributeStringsForLabel[] = "title='$escapedTitle'";
         }

         $htmlStrings[] =
         (
            "$i<input " . implode(' ', $attributeStringsForInput) . '/>' .
              "<label " . implode(' ', $attributeStringsForLabel) . '>'  .
                htmlentities($info['text']) .
              '</label>'
         );
      }

      return implode("<br/>\n", $htmlStrings) . "\n";
   }

   /*
    *
    */
   public static function echoRadioButtonsHtml
   (
      $name, $buttonInfoByValue, $indent,
      $checkedValue = null, $inputClass = null, $labelClass = null
   )
   {
      echo self::getRadioButtonsHtml
      (
         $name, $buttonInfoByValue, $indent, $checkedValue, $inputClass, $labelClass
      );
   }

   /*
    *
    */
   public static function echoTextareaHtml
   (
      $name, $indent,
      $value = '', $class = null, $nRows = null, $nCols = null, $boolDisabled = false
   )
   {
      echo "$indent<textarea name='", Utils_html::escapeSingleQuotes($name), "'";

      if ($nRows !== null) {echo " rows='" , Utils_html::escapeSingleQuotes($nRows), "'";}
      if ($nCols !== null) {echo " cols='" , Utils_html::escapeSingleQuotes($nCols), "'";}
      if ($class !== null) {echo " class='", Utils_html::escapeSingleQuotes($class), "'";}
      if ($boolDisabled  ) {echo " disabled='disabled'"                                 ;}

      echo '>', htmlentities($value), "</textarea>\n";
   }

   /*
    *
    */
   public static function echoCheckboxHtml($name, $indent, $boolChecked = false, $class = null)
   {
      echo "$indent<input type='checkbox' name='", Utils_html::escapeSingleQuotes($name), "'";

      if ($class !== null) {echo " class='", Utils_html::escapeSingleQuotes($class), "'";}
      if ($boolChecked   ) {echo " checked='checked'"                                   ;}

      echo "/>\n";
   }

   /*
    * Particularly useful for passing on $_GET parameters.
    *
    * TODO
    * ----
    * See php native function http_build_query().  That function
    * appears to do almost exactly what this one does.
    */
   public static function createGetStringFromArray($array, $questionMarkOrAmpersand = '?')
   {
      if ($questionMarkOrAmpersand != '?' && $questionMarkOrAmpersand != '&')
      {
         throw new Exception("Expected '?' or '&'.  Received '$questionMarkOrAmpersand'.");
      }

      if (count($array) == 0)
      {
         return '';
      }

      $strs = array();

      foreach ($array as $key => $value)
      {
         $strs[] = "$key=" . urlencode($value);
      }

      return $questionMarkOrAmpersand . implode('&', $strs);
   }

   /*
    * Particularly useful for passing on $_POST parameters.
    */
   public static function echoArrayAsHiddenInputs($array, $indent)
   {
      foreach ($array as $key => $value)
      {
         if (is_array($value))
         {
            $key .= '[]';

            foreach ($value as $arrayValue)
            {
               echo "$indent<input type='hidden'";
               echo " name='" , Utils_html::escapeSingleQuotes($key       ), "'";
               echo " value='", Utils_html::escapeSingleQuotes($arrayValue), "'/>\n";
            }
         }
         else
         {
            echo "$indent<input type='hidden'";
            echo " name='" , Utils_html::escapeSingleQuotes($key  ), "'";
            echo " value='", Utils_html::escapeSingleQuotes($value), "'/>\n";
         }
      }
   }

   /*
    * TODO
    * ----
    * Consider using cURL to accomplish this task.  See the following url for details.
    *  * http://stackoverflow.com/questions/2865289/php-redirection-with-post-parameters
    */
   public static function redirectToUrlIncludingGetAndPostParams
   (
      $redirectUrl, $alternateGet = null, $alternatePost = null
   )
   {
      $getArray  = ($alternateGet  === null)? $_GET : $alternateGet ;
      $postArray = ($alternatePost === null)? $_POST: $alternatePost;
      $postUrl   = $redirectUrl . self::createGetStringFromArray($getArray, '&');

      if (count($postArray) == 0)
      {
         // Nothing in $postArray, so a redirect using the header() function will suffice.
         header("Location: $postUrl");
         exit(0);
      };
?>
<!DOCTYPE html PUBLIC
 "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns='http://www.w3.org/1999/xhtml'>
 <head>
  <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
  <title>Redirecting...</title>
 </head>
 <body onload='document.redirectForm.submit()'>
  <h1>Redirecting...</h1>
  <p>This page is intended to redirect to another page immediately upon loading.</p>
  <p>
   If no redirection has occurred after a few seconds, then javascript may be disabled in your
   browser.  In that case you should click the 'Continue' button below.
  </p>
  <form name='redirectForm' method='post'
   action='<?php echo Utils_html::escapeSingleQuotes($postUrl); ?>'>
<?php
      self::echoArrayAsHiddenInputs($postArray, '   ');
?>
   <input type='submit' value='Continue'/>
  </form>
  <p>
   If the submit button has been clicked and still no redirection appears to have occurred, the
   reason could be that the page to which the redirection was intended does not display HTML
   content.  This situation can occur for example if a file download has been attempted.
  </p>
  <p>In any case do not be alarmed!  No doubt the universe is unfolding as it should.</p>
 </body>
</html>
<?php
      exit(0);
   }
}

/*******************************************END*OF*FILE********************************************/
?>
