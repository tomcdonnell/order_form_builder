<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../config/database.php';

// Note Regarding Expected $_GET Parameters
// ----------------------------------------
// A random integer may be appended to the $_GET string to force browsers not to use cached
// versions of this page.  Therefore the $_GET count is expected to be one or two.
if (!in_array(count($_GET), array(1, 2)) || !array_key_exists('formTypeNameShort', $_GET))
{
   die('This page has been used incorrectly.');
}

$formTypeNameShort = $_GET['formTypeNameShort'];
$formClassName     = 'Form' . ucfirst($formTypeNameShort);

require_once dirname(__FILE__) . "/../form_definitions/$formClassName.php";

eval("\$form = new $formClassName(\$pdoEx);");

header('content-type: text/javascript');

if (!array_key_exists('formTypeNameShort', $_GET))
{
   die('This page has been used incorrectly.  Append eg "?formTypeNameShort=phone" to the url.');
}

echo "/*\n";
echo " * This file has been automatically generated.\n";
echo " */\n";
echo "window.FORM_DEFINITION =\n";
echo $form->getAsJsonString(), ';';
?>
