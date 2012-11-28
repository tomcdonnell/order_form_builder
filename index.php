<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once 'config/database.php';
require_once 'lib/tom/php/utils/Utils_error.php';
require_once 'lib/tom/php/utils/Utils_html.php';
require_once 'lib/tom/php/utils/Utils_validator.php';
require_once 'php/classes/AuditDivHtmlGenerator.php';
require_once 'php/classes/FormHtmlGenerator.php';
require_once 'php/classes/FormUtils.php';
require_once 'php/classes/GeneralHtmlGenerator.php';

Utils_error::initErrorAndExceptionHandler('error_log.txt', 'error_page.html');

try
{
   $params = getParametersFromGetString($pdoEx);
}
catch (Exception $e)
{
$str = <<<STR
<html>
 <head><title>Error</title></head>
 <body>
  Invalid url.  Try
  <a href='{$_SERVER['PHP_SELF']}?formTypeNameShort=phone'>this</a>
  one to view a blank phone order form, or
  <a href='{$_SERVER['PHP_SELF']}?idForm=1&auditMode=1'>this</a>
  one to view the first form that was saved (assuming it exists), in audit mode.
 </body>
</html>
STR;
   die($str);
}

Utils_validator::checkArray
(
   $params, array
   (
      'boolAuditMode'               => 'boolean'          ,
      'formClassName'               => 'string'           ,
      'formTypeNameShort'           => 'string'           ,
      'idForm'                      => 'nullOrPositiveInt',
      'idFormEdit'                  => 'nullOrPositiveInt',
      'valueByFieldIdAttribute'     => 'array'            ,
      'valueByFieldIdAttributePrev' => 'nullOrArray'
   )
);
extract($params);

$formClassFilename = "form_definitions/$formClassName.php";

if (!file_exists($formClassFilename))
{
   die("Form class file '$formClassFilename' not found.");
}

eval("require_once '$formClassFilename';");
eval("\$form = new $formClassName(\$pdoEx);");

$customSectionNames                = $form->getAllCustomSectionNames();
$customSectionFilenamesByExtension =
(
   CustomFormSectionUtils::getJsAndCssFilenames($customSectionNames)
);

// Code to output HTML. ////////////////////////////////////////////////////////////////////////////

$indent = GeneralHtmlGenerator::echoHtmlHeaderIncludingOpenBodyTag
(
   array_merge
   (
      array
      (
         'lib/tom/js/contrib/jQuery/jQuery_minified.js', // Must include 1st.
         "js/form_definition.js.php?formTypeNameShort=$formTypeNameShort"        ,
         "js/item_info_by_name_short.js.php?formTypeNameShort=$formTypeNameShort",
         'config/Config.js.php'                                                  ,
         'js/FormUpdater.js'                                                     ,
         'js/FormUtils.js'                                                       ,
         'js/MiscUtils.js'                                                       ,
         'js/main.js'                                                            ,
         'lib/tom/js/contrib/utils/DomBuilder.js'                                ,
         'lib/tom/js/utils/utils.js'                                             ,
         'lib/tom/js/utils/utilsArray.js'                                        ,
         'lib/tom/js/utils/utilsAjax.js'                                         ,
         'lib/tom/js/utils/utilsDOM.js'                                          ,
         'lib/tom/js/utils/utilsObject.js'                                       ,
         'lib/tom/js/utils/utilsValidator.js'
      ),
      $customSectionFilenamesByExtension['js']
   ),
   array_merge
   (
      array
      (
         'css/style.css',
         'lib/tom/css/general_styles.css'
      ),
      $customSectionFilenamesByExtension['css']
   )
);

if ($boolAuditMode)
{
   AuditDivHtmlGenerator::echoAuditDivHtml($pdoEx, $idForm, $idFormEdit, $indent);
}

FormHtmlGenerator::echoFormHtml
(
   $formTypeNameShort, $idForm, $form->getAsPhpArray(),
   $valueByFieldIdAttribute,
   $valueByFieldIdAttributePrev,
   $indent
);

GeneralHtmlGenerator::echoHtmlFooterIncludingCloseBodyTag();

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function getParametersFromGetString(PdoExtended $pdoEx)
{
   if (array_key_exists('idForm', $_GET))
   {
      Utils_validator::checkArrayAndSetDefaults
      (
         $_GET, array
         (
            'idForm' => 'ctype_digit'
         ), array
         (
            'auditMode'  => array('ctype_digit', '0' ),
            'idFormEdit' => array('ctype_digit', null)
         )
      );
      extract($_GET);

      switch ($auditMode)
      {
       case '0':
         if ($idFormEdit !== null)
         {
            die("Invalid url parameter.  idFormEdit should only be supplied if 'auditMode=1'.");
         }
         break;
       case '1':
         // Do nothing.
         break;
       default:
         die("Invalid url parameter.  Expected values for 'auditMode' are '0' or '1'.");
      }

      $idForm                  = (int)$idForm;
      $idFormEdit              = ($idFormEdit === null)? null: (int)$idFormEdit;
      $boolAuditMode           = ($auditMode == '1');
      $formTypeNameShort       = FormUtils::getFormTypeNameShortForForm($pdoEx, $idForm);
      $valueByFieldIdAttribute = FormUtils::getValueByFieldIdAttribute($pdoEx, $idForm,$idFormEdit);

      if (!$pdoEx->rowExistsInTable('form', array('id' => $idForm)))
      {
         die("Invalid url parameter.  No form with id '$idForm' exists.");
      }

      if
      (
         $idFormEdit !== null && !$pdoEx->rowExistsInTable('form_edit', array('id' => $idFormEdit))
      )
      {
         die("Invalid url parameter.  No form edit with id '$idFormEdit' exists.");
      }
   }
   else
   {
      // Case for creating a new form.
      Utils_validator::checkArray($_GET, array('formTypeNameShort' => 'string'));
      $idForm                  = null;
      $idFormEdit              = null;
      $boolAuditMode           = false;
      $formTypeNameShort       = $_GET['formTypeNameShort'];
      $valueByFieldIdAttribute = array();
   }

   if ($boolAuditMode && $idFormEdit !== null)
   {
      $idFormEditPrev              = FormUtils::getPreviousIdFormEditOrNull($pdoEx, $idFormEdit);
      $valueByFieldIdAttributePrev =
      (
         ($idFormEditPrev === null)? null:
         FormUtils::getValueByFieldIdAttribute($pdoEx, $idForm, $idFormEditPrev)
      );
   }
   else
   {
      $valueByFieldIdAttributePrev = null;
   }

   return array
   (
      'boolAuditMode'               => $boolAuditMode                      ,
      'formClassName'               => 'Form' . ucfirst($formTypeNameShort),
      'formTypeNameShort'           => $formTypeNameShort                  ,
      'idForm'                      => $idForm                             ,
      'idFormEdit'                  => $idFormEdit                         ,
      'valueByFieldIdAttribute'     => $valueByFieldIdAttribute            ,
      'valueByFieldIdAttributePrev' => $valueByFieldIdAttributePrev
   );
}
?>
