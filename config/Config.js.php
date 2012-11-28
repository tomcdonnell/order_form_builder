<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/Config.php';

header('content-type: text/javascript');
?>
/*
 * Automatically generated object containing all the class constants defined in Config.php.
 * The purpose of this object is to allow those constants to be used in Javascript files.
 */
window.Config =
{
<?php
$reflectionClass     = new ReflectionClass('Config');
$constantValueByName = $reflectionClass->getConstants();
$jsPropertyLines     = array();

foreach ($constantValueByName as $name => $value)
{
   $jsPropertyLine = "$name: ";

   switch (getType($value))
   {
    case 'integer': $jsPropertyLine .=   $value                  ; break;
    case 'double' : $jsPropertyLine .=   $value                  ; break;
    case 'string' : $jsPropertyLine .= "'$value'"                ; break;
    case 'boolean': $jsPropertyLine .=  ($value)? 'true': 'false'; break;
    default: throw new Exception("Unknown type '$type' for constant TaasAdminConfig::$name.");
   }

   $jsPropertyLines[] = $jsPropertyLine;
}

echo '    ', implode(",\n    ", $jsPropertyLines), "\n";
?>
};
