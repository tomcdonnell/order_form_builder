<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../lib/tom/php/database/mysql_pdo/PdoExtended.php';

$pdoEx = new PdoExtended
(
    'mysql:host=localhost;dbname=generic_forms', 'admin_forms', 'demopassword'
);
?>
