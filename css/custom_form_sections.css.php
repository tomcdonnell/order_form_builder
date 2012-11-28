<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../php/classes/CustomFormSectionUtils.php';

header('content-type: text/css');

if (!array_key_exists('formName', $_GET))
{
   die('This page has been used incorrectly.  Append eg "?formName=phone" to the url.');
}

$formName = $_GET['formName'];
$js       = CustomFormSectionUtils::getCombinedCssForAllCustomSections($formName);

echo "/*\n";
echo " * This file has been automatically generated.\n";
echo " */\n\n";
echo
(
   ($js != '')? $js:
   "/* This file is empty because no css code was found for custom sections of form '$formName'. */"
);
?>
