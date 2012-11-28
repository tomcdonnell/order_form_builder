<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../config/Config.php';
require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/../lib/tom/php/utils/Utils_error.php';
require_once dirname(__FILE__) . '/../lib/tom/php/utils/Utils_html.php';
require_once dirname(__FILE__) . '/classes/FormUtils.php';
require_once dirname(__FILE__) . '/classes/FormUtils.php';
require_once dirname(__FILE__) . '/classes/FormWriter.php';
require_once dirname(__FILE__) . '/classes/GeneralHtmlGenerator.php';
require_once dirname(__FILE__) . '/classes/PostArrayValidator.php';

Utils_error::initErrorAndExceptionHandler('../error_log.txt', '../error_page.html');

if (!array_key_exists('formTypeNameShort', $_POST))
{
   throw new Exception('This page has been used incorrectly.');
}

$formTypeNameShort = $_POST['formTypeNameShort'];
$formClassName     = 'Form' . ucfirst($formTypeNameShort);
$idFormOrNull      = (array_key_exists('idForm', $_GET))? $_GET['idForm']: null;

if
(
   $idFormOrNull !== null &&
   FormUtils::getFormTypeNameShortForForm($pdoEx, $idFormOrNull) != $_POST['formTypeNameShort']
)
{
   throw new Exception
   (
      "Mismatch on idForm/formTypeNameShort. {idForm: $idFormOrNull, formName: $formName}"
   );
}

require_once dirname(__FILE__) . "/../form_definitions/$formClassName.php";

eval("\$form = new $formClassName(\$pdoEx);");

assertPostArrayIsAsExpected($pdoEx, $form);

$soeid = $_POST['contact-details|soeid'];

if (!$pdoEx->rowExistsInTable('user', array('soeid' => $soeid)))
{
   die("No user found with SOE id '$soeid'.");
}

$pdoEx->query('START TRANSACTION');

try
{
   list($idFormEdit, $nFormEditDataRowsInserted) =
   (
      FormWriter::saveValidatedPostArrayAsFormEdit($pdoEx, $idFormOrNull, $_POST)
   );
}
catch (Exception $e)
{
   $pdoEx->query('ROLLBACK');
   throw new Exception
   (
      "Transaction rolled back.\n" .
      $e->getMessage() .      "\n" .
      $e->getTraceAsString()
   );
}

$pdoEx->query('COMMIT');

// Code to output HTML. ////////////////////////////////////////////////////////////////////////////

$indent = GeneralHtmlGenerator::echoHtmlHeaderIncludingOpenBodyTag();
$i      = &$indent; // Abbreviation.

if ($idFormEdit === null)
{
   echo "$i<p>No changes were detected, so nothing was saved.</p>\n";
}
else
{
   echo "$i<p>";
   echo "Success!  Saved $nFormEditDataRowsInserted field values to the ";
   echo FormUtils::getFormEditNumberFromId($pdoEx, $idFormEdit, true), ' edit of form ';
   echo FormUtils::getIdFormFromIdFormEdit($pdoEx, $idFormEdit), " (idFormEdit $idFormEdit).\n";
   echo "</p>\n";
}

$idForm = FormUtils::getIdFormFromIdFormEdit($pdoEx, $idFormEdit);
$url    =
(
   'http://' .
   Config::DOMAIN_NAME . Config::PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT .
   "/index.php?idForm=$idForm"
);

echo "$i<a href='", Utils_html::escapeSingleQuotes($url), "'>Back to saved form</a>\n";

GeneralHtmlGenerator::echoHtmlFooterIncludingCloseBodyTag();

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function assertPostArrayIsAsExpected(PdoExtended $pdoEx, FormBase $form)
{
   $postArrayValidator = new PostArrayValidator($form);
   $postArrayValidator->assertPostArrayKeysAndValuesAreAsExpected($pdoEx, $_POST);
}
?>
