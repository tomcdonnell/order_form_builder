<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../config/database.php';

header('content-type: text/javascript');

if (!array_key_exists('formTypeNameShort', $_GET))
{
   die('This page has been used incorrectly.  Append eg "?formTypeNameShort=phone" to the url.');
}

$rows = $pdoEx->selectRows
(
   'SELECT
    item.nameShort             AS itemNameShort       ,
    item.nameLong              AS itemNameLong        ,
    item.chargeDollarsAus      AS itemChargeDollarsAus,
    item.chargeCentsAus        AS itemChargeCentsAus  ,
    item.imageFilename         AS itemImageFilename   ,
    charge_frequency.nameShort AS chargeFrequencyNameShort
    FROM item
    JOIN link_device_item ON (link_device_item.idItem=item.id)
    JOIN device ON (device.id=link_device_item.idDevice)
    JOIN form_type ON (form_type.id=device.idFormType)
    JOIN charge_frequency ON (charge_frequency.id=item.idChargeFrequency)
    WHERE form_type.nameShort=?
    ORDER BY itemNameShort ASC',
   array($_GET['formTypeNameShort'])
);

$itemInfoById = array();
foreach ($rows as $row)
{
   $itemNameShort = $row['itemNameShort'];
   unset($row['itemNameShort']);
   $row['itemChargeDollarsAus']  = (int)$row['itemChargeDollarsAus'];
   $row['itemChargeCentsAus'  ]  = (int)$row['itemChargeCentsAus'  ];
   $itemInfoById[$itemNameShort] = $row;
}

$jsonString = json_encode($itemInfoById);

if ($jsonString == '[]')
{
   $jsonString = '{}';
}

echo "/*\n";
echo " * This file has been automatically generated.\n";
echo " */\n";
echo "window.ITEM_INFO_BY_NAME_SHORT=$jsonString;\n";
?>
