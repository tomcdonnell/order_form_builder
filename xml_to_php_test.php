<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/php/classes/XmlPhpConverter.php';

error_reporting(-1);

$xmlString         = file_get_contents('form_definitions/phone.xml');
$xmlObj            = new SimpleXmlElement($xmlString);
$phoneFormPhpArray = XmlPhpConverter::simpleXmlElementToArray($xmlObj);

echo "Returned array:\n";
var_export($phoneFormPhpArray);
?>
