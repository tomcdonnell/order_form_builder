DROP DATABASE generic_forms;
CREATE DATABASE generic_forms;
USE generic_forms;

SELECT 'creating form_type...';

CREATE TABLE form_type
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   nameShort varchar(16) COLLATE utf8_unicode_ci NOT NULL,
   nameLong varchar(64) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (nameShort),
   UNIQUE KEY (nameLong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO form_type (created, nameShort, nameLong)
VALUES
('0000-00-00 00:00:00', 'phone', 'Phone'),
('0000-00-00 00:00:00', 'tablet', 'Tablet'),
('0000-00-00 00:00:00', 'computer', 'Computer');

SELECT 'creating charge_frequency...';

CREATE TABLE charge_frequency
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   nameShort varchar(16) COLLATE utf8_unicode_ci NOT NULL,
   nameLong varchar(64) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (nameShort),
   UNIQUE KEY (nameLong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO charge_frequency (created, nameShort, nameLong)
VALUES
('0000-00-00 00:00:00', 'once', 'Once'),
('0000-00-00 00:00:00', 'monthly', 'Monthly'),
('0000-00-00 00:00:00', 'yearly', 'Yearly');

SELECT 'creating device...';

CREATE TABLE device
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   idFormType int(11) unsigned NOT NULL,
   nameShort varchar(16) COLLATE utf8_unicode_ci NOT NULL,
   nameLong varchar(64) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (nameShort),
   UNIQUE KEY (nameLong),
   CONSTRAINT FOREIGN KEY (idFormType) REFERENCES form_type (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO device (created, idFormType, nameShort, nameLong)
VALUES
('0000-00-00 00:00:00', 1, 'nokia', 'Nokia'),
('0000-00-00 00:00:00', 1, 'iphone', 'iPhone'),
('0000-00-00 00:00:00', 2, 'ipad', 'iPad');

SELECT 'creating item...';

CREATE TABLE item
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   idChargeFrequency int(11) unsigned NOT NULL,
   nameShort varchar(128) COLLATE utf8_unicode_ci NOT NULL,
   nameLong varchar(128) COLLATE utf8_unicode_ci NOT NULL,
   chargeDollarsAus int(11) unsigned NOT NULL,
   chargeCentsAus int(11) unsigned NOT NULL,
   orderName varchar(128) COLLATE utf8_unicode_ci NOT NULL,
   supplier varchar(64) COLLATE utf8_unicode_ci NOT NULL,
   imageFilename varchar(128) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (nameShort),
   UNIQUE KEY (nameLong),
   CONSTRAINT FOREIGN KEY (idChargeFrequency) REFERENCES charge_frequency (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
 * TODO: Move chargeDollarsAus to separate table item_charge since prices may change.
 */
INSERT INTO item
(created, idChargeFrequency, nameShort, nameLong, chargeDollarsAus, chargeCentsAus, orderName, supplier, imageFilename)
VALUES
('0000-00-00 00:00:00', 1, 'car_kit_install'                , 'Bury System 9 car kit install'          , 595, 0, 'Bury System 9 car kit install'    , 'Local Installer'   , 'images/mobile/iPhoneCradle.png'        ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'car_kit_upgrade'                , 'Bury System 9 car kit upgrade'          , 275, 0, 'Bury System 9 car kit upgrade'    , 'Local Installer'   , 'images/mobile/iPhoneCradle.png'        ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'headset_corded'                 , 'Corded headset'                         ,  35, 0, 'MB770G/B'                         , 'Apple'             , 'images/mobile/iPhoneHeadphones.png'    ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_case_keyboard'             , 'Keyboard case'                          , 130, 0, '920-003398'                       , 'Logitech'          , 'images/mobile/iPadKeyboard.png'        ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_case_magnetic'             , 'Magnetic Smart cover'                   ,  45, 0, 'iPad Smart Cover – Polyurethane', 'Apple'             , 'images/mobile/iPadCaseMagnetic.png'    ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_case_otterbox'             , 'Rugged case for iPad'                   ,  94, 0, 'OTDE-New-iPad'                    , 'Smartphone Store'  , 'images/mobile/iPadRugged.png'          ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_case_waterproof'           , 'Waterproof case'                        , 169, 0, 'ZZG112'                           , 'C H Smith Marine'  , 'images/mobile/iPadWaterproof.png'      ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_dock'                      , 'iPad dock'                              ,  35, 0, 'iPad Dock'                        , 'Apple'             , 'images/mobile/iPadDock.png'            ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_screen_protector'          , 'Screen protector for iPad'              ,   7, 0, 'XBELGLSKNWIPAD'                   , 'Telstra'           , 'images/mobile/iPadProtector.png'       ), /* iPad                */
('0000-00-00 00:00:00', 1, 'ipad_tablet'                    , 'iPad 2012 16Gb'                         , 679, 0, 'XAPP16GIPADBLK'                   , 'Telstra'           , 'images/mobile/iPad.png'                ), /* iPad                */
('0000-00-00 00:00:00', 1, 'iphone_case_waterproof'         , 'Waterproof case & floating lanyard'     ,  60, 0, 'Case & Floating Lanyard'          , 'InDepth Cases'     , 'images/mobile/iPhoneWaterproofCase.png'), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_case_bumper'             , 'Bumper case'                            ,  39, 0, 'Apple iPhone 4 Bumper Black'      , 'Apple'             , 'images/mobile/iPhoneStandard.png'      ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_case_otterbox'           , 'Rugged case for iPhone'                 ,  42, 0, 'OTDE4SGREYWHI'                    , 'Smartphone Store'  , 'images/mobile/iPhoneRugged.png'        ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_dock'                    , 'iPhone dock'                            ,  35, 0, 'XIPHONE4DOCK'                     , 'Telstra'           , 'images/mobile/iPhoneDock.png'          ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_handset_color_black'     , 'iPhone 4s (Black) 16Gb'                 , 792, 0, 'XAPPIP4S16BLK'                    , 'Telstra'           , 'images/mobile/iPhone-black.png'        ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_handset_color_random'    , 'iPhone 4s (random colour) 16Gb'         , 792, 0, ''                                 , ''                  , 'images/mobile/iPhone-surprise.png'     ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_handset_color_white'     , 'iPhone 4s (White) 16Gb'                 , 792, 0, 'XAPPIP4S16WHT'                    , 'Telstra'           , 'images/mobile/iPhone-white.png'        ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'iphone_ipad_car_charger'        , 'USB car charger'                        ,  19, 0, 'XBELKUSBCARCHG'                   , 'Telstra'           , 'images/mobile/iPhoneCharger.png'       ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 1, 'iphone_ipad_dock'               , 'iPhone & iPad dock'                     ,  70, 0, 'H2060ZM/A'                        , 'Apple Store'       , 'images/mobile/iPhoneDockiPad.png'      ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 1, 'iphone_ipad_headset_bluetooth'  , 'Bluetooth headset'                      ,  58, 0, 'XPLAM155BTHSWT'                   , 'Telstra'           , 'images/mobile/iPhoneHeadset.png'       ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 1, 'iphone_ipad_mobile_suite_setup' , 'Email, Calendar, Contacts etc'          , 360, 0, 'mSuite'                           , 'CenITex'           , 'images/mobile/mSuite.png'              ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 1, 'iphone_screen_protector'        , 'Screen protector for iPhone'            ,   7, 0, 'XTELSCPROTIP4S'                   , 'Telstra'           , 'images/mobile/iPhoneProtector.png'     ), /* iPhone              */
('0000-00-00 00:00:00', 1, 'nokia_handset'                  , 'Nokia C5'                               , 190, 0, 'XNOKC5003GREY and XNOKC5LP'       , 'Telstra'           , 'images/mobile/nokia.png'               ), /* Nokia               */
('0000-00-00 00:00:00', 2, 'iphone_ipad_airwatch'           , 'Mobile device management'               ,  10, 0, 'AirWatch'                         , 'App & Web Dev Team', ''                                      ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 2, 'iphone_ipad_datapack'           , 'Data Pack'                              ,  15, 0, '1Gb plan'                         , 'Telstra'           , 'images/mobile/datapack.png'            ), /* iPhone, iPad        */
('0000-00-00 00:00:00', 2, 'phone_plan'                     , 'Phone Plan'                             ,   0, 0, ''                                 , ''                  , 'images/mobile/nokiaSim.png'            ), /* Nokia, iPhone, iPad */
('0000-00-00 00:00:00', 3, 'iphone_ipad_mobile_suite_yearly', 'Email, Calendar, Contacts etc. (yearly)', 670, 0, 'mSuite'                           , 'CenITex'           , ''                                      ); /* iPhone, iPad        */

SELECT 'creating link_device_item...';

CREATE TABLE link_device_item
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idDevice int(11) unsigned NOT NULL,
   idItem int(11) unsigned NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (idDevice, idItem),
   CONSTRAINT FOREIGN KEY (idDevice) REFERENCES device (id),
   CONSTRAINT FOREIGN KEY (idItem) REFERENCES item (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO link_device_item
(created, idDevice, idItem)
VALUES
('0000-00-00 00:00:00', 2,  1),
('0000-00-00 00:00:00', 2,  2),
('0000-00-00 00:00:00', 3,  3),
('0000-00-00 00:00:00', 3,  4),
('0000-00-00 00:00:00', 3,  5),
('0000-00-00 00:00:00', 3,  6),
('0000-00-00 00:00:00', 3,  7),
('0000-00-00 00:00:00', 3,  8),
('0000-00-00 00:00:00', 3,  9),
('0000-00-00 00:00:00', 3, 10),
('0000-00-00 00:00:00', 2, 11),
('0000-00-00 00:00:00', 2, 12),
('0000-00-00 00:00:00', 2, 13),
('0000-00-00 00:00:00', 2, 14),
('0000-00-00 00:00:00', 2, 15),
('0000-00-00 00:00:00', 2, 16),
('0000-00-00 00:00:00', 2, 17),
('0000-00-00 00:00:00', 2, 18),
('0000-00-00 00:00:00', 3, 18),
('0000-00-00 00:00:00', 2, 19),
('0000-00-00 00:00:00', 3, 19),
('0000-00-00 00:00:00', 2, 20),
('0000-00-00 00:00:00', 3, 20),
('0000-00-00 00:00:00', 2, 21),
('0000-00-00 00:00:00', 2, 22),
('0000-00-00 00:00:00', 1, 23),
('0000-00-00 00:00:00', 2, 24),
('0000-00-00 00:00:00', 3, 24),
('0000-00-00 00:00:00', 2, 25),
('0000-00-00 00:00:00', 3, 25),
('0000-00-00 00:00:00', 1, 26),
('0000-00-00 00:00:00', 2, 26),
('0000-00-00 00:00:00', 3, 26),
('0000-00-00 00:00:00', 2, 27),
('0000-00-00 00:00:00', 3, 27);

SELECT 'creating user...';

CREATE TABLE `user`
(
   `soeid` varchar(7) NOT NULL,
   `identifier` int(11) unsigned NOT NULL,
   `status` varchar(2) NOT NULL,
   `nameTitle` varchar(10) NOT NULL,
   `lastName` varchar(30) NOT NULL,
   `preferredName` varchar(30) NOT NULL,
   `firstName` varchar(30) NOT NULL,
   `middleNames` varchar(30) NOT NULL,
   `fte` decimal(10,2) NOT NULL,
   `employmentCategory` varchar(30) NOT NULL,
   `employmentSubgroup` varchar(30) NOT NULL,
   `gradeClassification` varchar(30) NOT NULL,
   `defaultExpenseAccount` varchar(24) NOT NULL,
   `positionNumber` varchar(6) NOT NULL,
   `positionName` varchar(60) NOT NULL,
   `managerSoeid` varchar(7) NOT NULL,
   `supervisorEmployeeNumber` int(11) unsigned NULL,
   `supervisorName` varchar(50) NOT NULL,
   `department` varchar(30) NOT NULL,
   `division` varchar(40) NOT NULL,
   `branch` varchar(40) NOT NULL,
   `section` varchar(40) NOT NULL,
   `subSection` varchar(40) NOT NULL,
   `mainGroup` varchar(40) NOT NULL,
   `subGroup` varchar(40) NOT NULL,
   `addressLine1` varchar(40) NOT NULL,
   `addressLine2` varchar(40) NOT NULL,
   `addressLine3` varchar(40) NOT NULL,
   `townOrCity` varchar(30) NOT NULL,
   `state` varchar(5) NOT NULL,
   `postCode` varchar(5) NOT NULL,
   `siteType` varchar(30) NOT NULL,
   `emailAddress` varchar(60) NOT NULL,
   `workPhone` varchar(20) NOT NULL,
   `workMobile` varchar(20) NOT NULL,
   `workFax` varchar(20) NOT NULL,
   `accountManager` varchar(30) NOT NULL,
   `hrOperative` varchar(30) NOT NULL,
   `hrOperativeSoeid` varchar(6) NOT NULL,
   `paypointid` varchar(6) NOT NULL,
   `visible` varchar(1) NOT NULL DEFAULT 'y',
   `webAuthor` varchar(1) NOT NULL DEFAULT 'n',
   `delegate` varchar(1) NOT NULL DEFAULT 'n',
   `profilePic` varchar(1) NOT NULL DEFAULT 'n',
   `firstAid` varchar(1) NOT NULL DEFAULT 'n',
   `fireWarden` varchar(1) NOT NULL DEFAULT 'n',
   `dateAdded` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `dateUpdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `dateDeleted` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
   `assignmentStatus` varchar(50) NOT NULL,
   `accountManagerSoeid` varchar(4) NOT NULL,
   PRIMARY KEY `Employee Number` (`identifier`),
   INDEX (`soeid`),
   INDEX (`supervisorEmployeeNumber`),
   INDEX (`managerSoeid`),
   INDEX (`hrOperativeSoeid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SELECT 'creating form...';

CREATE TABLE `form`
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   idFormType int(11) unsigned NOT NULL,
   PRIMARY KEY (id),
   CONSTRAINT FOREIGN KEY (idFormType) REFERENCES form_type (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SELECT 'creating form_edit...';

CREATE TABLE form_edit
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idForm int(11) unsigned NOT NULL,
   idUser int(11) unsigned NOT NULL,
   editNumber int(11) unsigned NOT NULL, /* Determine the edit order using editNumber (not created) to guard against the possibility of an incorrectly set system clock. */
   PRIMARY KEY (id),
   UNIQUE KEY (idForm, editNumber), /* To enforce no ambiguity in ordering of form edits. */
   CONSTRAINT FOREIGN KEY (idForm) REFERENCES `form` (id),
   CONSTRAINT FOREIGN KEY (idUser) REFERENCES `user` (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SELECT 'creating form_edit_data...';

CREATE TABLE form_edit_data
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   idFormEdit int(11) unsigned NOT NULL,
   fieldIdAttribute varchar(256) NOT NULL,
   fieldValue varchar(2048) NOT NULL,
   PRIMARY KEY (id),
   CONSTRAINT FOREIGN KEY (idFormEdit) REFERENCES `form_edit` (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
SELECT 'creating submitted_form_item...';

CREATE TABLE submitted_form_item
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idForm int(11) unsigned NOT NULL,
   idItem int(11) unsigned NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (idForm, idItem),
   CONSTRAINT FOREIGN KEY (idForm) REFERENCES `form` (id),
   CONSTRAINT FOREIGN KEY (idItem) REFERENCES item (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SELECT 'creating charge_code...';

CREATE TABLE form_charge_code
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idForm int(11) unsigned NOT NULL,
   code varchar(18) COLLATE utf8_unicode_ci NOT NULL,
   percentageOfOrder int(3) unsigned NOT NULL,
   PRIMARY KEY (id),
   CONSTRAINT FOREIGN KEY (idForm) REFERENCES `form` (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
SELECT 'creating approval_decision...';

CREATE TABLE approval_decision
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   nameShort varchar(16) COLLATE utf8_unicode_ci NOT NULL,
   nameLong varchar(64) COLLATE utf8_unicode_ci NOT NULL,
   PRIMARY KEY (id),
   UNIQUE KEY (nameShort),
   UNIQUE KEY (nameLong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO approval_decision (created, nameShort, nameLong)
VALUES
('0000-00-00 00:00:00', 'approved', 'Approved'),
('0000-00-00 00:00:00', 'not_approved', 'Not Approved');

SELECT 'creating link_form_approver...';

/*
 * The set of user_approvers who are tasked with approving a form is determined at the time the
 * form is submitted.
 *
 * Eg. A form may require approval by a line manager, then a higher level manager, then an
 * executive.  In that case, a row for each would be added to the link_form_approver table when the
 * form is submitted.  The line manager would be assigned rank 1, the higher level manager rank 2,
 * and the executive rank 3.
 *
 * When any of the designated approvers makes an approval decision pertaining to the form, a row is
 * inserted into the form_approval_decision table.  Order approval decisions may only be made by
 * designated approvers (those who appear in the link_form_approver table).
 *
 * The form in which approval decisions were made is recorded in the created column.  The approval
 * state of the form can be determined by inspecting the form_approval_decision rows in the form
 * they were created.
 *
CREATE TABLE link_form_approver
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idForm int(11) unsigned NOT NULL,
   idUserApprover int(11) unsigned NOT NULL,
   approverRank tinyint unsigned NOT NULL,
   PRIMARY KEY (id),                       
   UNIQUE KEY (idForm, idUserApprover),
   UNIQUE KEY (idForm, approverRank),
   CONSTRAINT FOREIGN KEY (idForm) REFERENCES `form` (id),
   CONSTRAINT FOREIGN KEY (idUserApprover) REFERENCES user (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SELECT 'creating submitted_form_action...';

CREATE TABLE submitted_form_action
(
   id int(11) unsigned NOT NULL AUTO_INCREMENT,
   created datetime NOT NULL,
   idForm int(11) unsigned NOT NULL,
   idUserApprover int(11) unsigned NOT NULL,
   idApprovalDecision int(11) unsigned NOT NULL,
   PRIMARY KEY (id),
   CONSTRAINT FOREIGN KEY (idUserApprover) REFERENCES user (identifier),
   CONSTRAINT FOREIGN KEY (idForm) REFERENCES `form` (id),
   CONSTRAINT FOREIGN KEY (idApprovalDecision) REFERENCES `approval_decision` (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
*/

SELECT 'filling table user';

INSERT INTO `user`
SELECT *
FROM emerg.staff;

SELECT 'Correcting warnings pertaining to supervisor_employee_number.';

UPDATE `user`
SET supervisorEmployeeNumber=NULL
WHERE supervisorEmployeeNumber=0;

SELECT 'creating view_item_info...';

CREATE VIEW view_item_info AS
SELECT
form_type.nameShort AS formTypeNameShort,
device.nameShort AS deviceNameShort,
item.id AS itemId,
item.nameShort AS itemNameShort,
item.imageFilename AS itemImageFilename,
item.chargeDollarsAus AS itemChargeDollarsAus,
charge_frequency.nameShort AS chargeFrequencyNameShort
FROM `item`
JOIN link_device_item ON (link_device_item.idItem=item.id)
JOIN device ON (device.id=link_device_item.idDevice)
JOIN form_type ON (form_type.id=device.idFormType)
JOIN charge_frequency ON (charge_frequency.id=item.idChargeFrequency)
WHERE 1
ORDER BY formTypeNameShort ASC, deviceNameShort ASC, itemNameShort ASC;
