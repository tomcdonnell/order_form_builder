<?php
/*
 * vim: ts=3 sw=3 et nowrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../php/classes/FormBase.php';

class FormPhone extends FormBase
{
   public function getAsPhpArray()
   {
      return array
      (
         'sections' => array
         (
            array
            (
               'type'      => 'custom'         ,
               'nameShort' => 'contact-details',
               'nameLong'  => 'Contact Details'
            ),
            array
            (
               'type'      => 'normal'             ,
               'nameShort' => 'request-information',
               'nameLong'  => 'Request Information',
               'fields'    => array
               (
                  array
                  (
                     'name'                      => 'new-phone-will-replace-existing-phone'    ,
                     'type'                      => 'select'                                   ,
                     'questionHtml'              => 'Is this replacing an existing DPI mobile?',
                     'defaultSelectedOptionText' => 'Select...'                                ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'No'                                               ),
                        array('text' => 'Yes'                                              )
                     )
                  ),
                  array
                  (
                     'name'              => 'existing-phone-number'            ,
                     'type'              => 'text'                             ,
                     'questionHtml'      => 'Existing mobile telephone number:',
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'existing-phone-fate'      ,
                     'type'                      => 'select'                   ,
                     'questionHtml'              => 'The phone being replaced:',
                     'defaultSelectedOptionText' => 'Select...'                                                     ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'Was lost/stolen'                                  ),
                        array('text' => 'it is not functioning and will be returned'       ),
                        array('text' => 'Is being upgraded and will be returned'           ),
                        array('text' => 'Is being transferred'                             )
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'existing-phone-type',
                     'type'                      => 'select'             ,
                     'questionHtml'              => 'The old phone is:'  ,
                     'defaultSelectedOptionText' => 'Select...'          ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'An iPhone 4s'                                     ),
                        array('text' => 'An iPhone 5'                                      ),
                        array('text' => 'A Blackberry'                                     ),
                        array('text' => 'A Nokia'                                          ),
                        array('text' => 'A Motorola'                                       )
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'application-for-mobile-telephone-transfer-form-completed'                                                                                                                                                                                                                                                                                                                       ,
                     'type'                      => 'select'                                                                                                                                                                                                                                                                                                                                                                         ,
                     'questionHtml'              => 'Has the <a target="_blank" href="http://filenet/workplace/WcmSignIn.jsp?targetBase=http%3A%2F%2Ffilenet%2Fworkplace&amp;originPort=&amp;originIp=10.61.94.9&amp;targetUrl=getContent%3Fid%3Drelease%26vsId%3D%257B7B1A0E4C-E234-4975-A9CA-E7443A7C86AB%257D%26objectStoreName%3DDPI%26objectType%3Ddocument">application for mobile telephone transfer form</a> been completed?',
                     'defaultSelectedOptionText' => 'Select...'                                                                                                                                                                                                                                                                                                                                                                      ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'Yes'                                              ),
                        array('text' => 'No'                                               )
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        ),
                        array
                        (
                           'sectionNameShort' => 'request-information',
                           'fieldName'        => 'existing-phone-fate',
                           'value'            => 'Is being transferred'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'property-stores-and-money-loss-report-form-completed',
                     'type'                      => 'select',
                     'questionHtml'              => 'Has the <a target="_blank" href="http://filenet/workplace/getContent?id=%7B572B3723-AB20-4D3F-A29D-C786B5B7CEA5%7D&amp;vsId=%7B5F88AE96-6003-43E6-B7FF-2C2C1BB78B86%7D&amp;objectStoreName=DPI&amp;objectType=document">property, stores, and money loss report form</a> been completed?',
                     'defaultSelectedOptionText' => 'Select...',
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'Yes'                                              ),
                        array('text' => 'No'                                               )
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        ),
                        array
                        (
                           'sectionNameShort' => 'request-information',
                           'fieldName'        => 'existing-phone-fate',
                           'value'            => 'Was lost/stolen'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'existing-phone-has-car-kit'           ,
                     'type'                      => 'select'                               ,
                     'questionHtml'              => 'Do you have a car kit for this phone?',
                     'defaultSelectedOptionText' => 'Select...'                            ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'Yes'                                              ),
                        array('text' => 'No'                                               )
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                  ,
                           'fieldName'        => 'new-phone-will-replace-existing-phone',
                           'value'            => 'Yes'
                        )
                     )
                  ),
                  array
                  (
                     'name'         => 'business-justification',
                     'type'         => 'textarea'              ,
                     'questionHtml' => 'Business justification:'
                  ),
                  array
                  (
                     'name'                      => 'exempt-from-having-mobile-phone-number-on-people-connect'            ,
                     'type'                      => 'select'                                                              ,
                     'questionHtml'              => 'Exemption from publication of mobile phone number on People Connect?',
                     'defaultSelectedOptionText' => 'No'                                                                  ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'),
                        array('text' => 'Yes'                                              ),
                        array('text' => 'No'                                               )
                     )
                  ),
                  array
                  (
                     'name'              => 'exempt-from-having-mobile-phone-number-on-people-connect-message'                                                                                                                                                                                      ,
                     'type'              => 'paragraph'                                                                                                                                                                                                                                             ,
                     'html'              => '<strong>Work mobile phone numbers will be published in People Connect, however, staff involved in activities of a confidential nature (eg. enforcement and investigation) may be exempt from this requirement subject to Supervisor approval.</strong>',
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'request-information'                                     ,
                           'fieldName'        => 'exempt-from-having-mobile-phone-number-on-people-connect',
                           'value'            => 'Yes'
                        )
                     )
                  )
               )
            ),
            array
            (
               'type'      => 'normal'       ,
               'nameShort' => 'phone-options',
               'nameLong'  => 'Phone Options',
               'fields'    => array
               (
                  array
                  (
                     'name'                      => 'phone-type' ,
                     'type'                      => 'select'     ,
                     'questionHtml'              => 'Phone type:',
                     'defaultSelectedOptionText' => 'Select...'  ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString'                   => 'not-valid-selection'               ),
                        array('text' => 'Nokia'    , 'itemNameShortsToAddIfSelected' => array('nokia_handset', 'phone_plan')),
                        array('text' => 'iPhone 5'                                                                          )
                     )
                  ),
                  array
                  (
                     'name'              => 'nokia-phone-type-message'                                                                            ,
                     'type'              => 'paragraph'                                                                                           ,
                     'html'              => 'Only the following standard phone services will be provided: voice calls; text messaging; voicemail.',
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'Nokia'
                        )
                     )
                  ),
                  array
                  (
                     'name'              => 'iphone-travel-recommendation'                                                                                                                ,
                     'type'              => 'paragraph'                                                                                                                                   ,
                     'html'              => 'It is recommended that staff who frequently travel to remote areas where iPhone coverage might be inadequate also have a Nokia mobile phone.',
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     )
                  ),
                  array
                  (
                     'name'              => 'iphone-standard-service-warning'                                                                                                                                               ,
                     'type'              => 'paragraph'                                                                                                                                                                     ,
                     'html'              => '<strong>Only the following standard phone services will be provided: voice calls; text messaging; voicemail; mSuite (email, calendar and contacts), Internet and GPS.</strong>',
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     )
                  ),
                  array
                  (
                     'name'                      => 'colour'     ,
                     'type'                      => 'select'     ,
                     'questionHtml'              => 'Colour:'    ,
                     'defaultSelectedOptionText' => 'Surprise Me',
                     'options'                   => array
                     (
                        array('text' => 'Select...'  , 'classString'                   => 'not-valid-selection'                             ),
                        array('text' => 'Surprise Me', 'itemNameShortsToAddIfSelected' => array('iphone_handset_color_random', 'phone_plan')),
                        array('text' => 'Black'      , 'itemNameShortsToAddIfSelected' => array('iphone_handset_color_black' , 'phone_plan')),
                        array('text' => 'White'      , 'itemNameShortsToAddIfSelected' => array('iphone_handset_color_white' , 'phone_plan'))
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     )
                  ),
/*
Comment out from here..
*/
                  array
                  (
                     'name'                      => 'case'         ,
                     'type'                      => 'select'       ,
                     'questionHtml'              => 'Case:'        ,
                     'defaultSelectedOptionText' => 'Standard Case',
                     'displayConditions'         => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     ),
                     'options' => array
                     (
                        array('text' => 'Select...'                , 'classString'                   => 'not-valid-selection'          ),
                        array('text' => 'Standard Case'            , 'itemNameShortsToAddIfSelected' => array('iphone_case_bumper'    )),
                        array('text' => 'Rugged Case'              , 'itemNameShortsToAddIfSelected' => array('iphone_case_otterbox'  )),
                        array('text' => 'Water and Dust Proof Case', 'itemNameShortsToAddIfSelected' => array('iphone_case_waterproof'))
                     )
                  ),
/*
to here.
*/
                  array
                  (
                     'name'                      => 'screen-protector' ,
                     'type'                      => 'select'           ,
                     'questionHtml'              => 'Screen Protector:',
                     'defaultSelectedOptionText' => 'No'               ,
                     'displayConditions'         => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     ),
                     'options' => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'                             ),
                        array('text' => 'No'                                                                            ),
                        array('text' => 'Yes'      , 'itemNameShortsToAddIfSelected' => array('iphone_screen_protector'))
                     )
                  ),
/*
Comment out from here..
*/
                  array
                  (
                     'name'                      => 'car-charger' ,
                     'type'                      => 'select'      ,
                     'questionHtml'              => 'Car Charger:',
                     'defaultSelectedOptionText' => 'No'          ,
                     'displayConditions'         => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     ),
                     'options' => array
                     (
                        array('text' => 'Select...', 'classString' => 'not-valid-selection'                             ),
                        array('text' => 'No'                                                                            ),
                        array('text' => 'Yes'      , 'itemNameShortsToAddIfSelected' => array('iphone_ipad_car_charger'))
                     )
                  ),
/*
to here.
*/
                  array
                  (
                     'name'                      => 'bluetooth-headset' ,
                     'type'                      => 'select'            ,
                     'questionHtml'              => 'Bluetooth Headset:',
                     'defaultSelectedOptionText' => 'No'                ,
                     'displayConditions'         => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     ),
                     'options' => array
                     (
                        array('text' => 'Select...', 'classString'                   => 'not-valid-selection'                 ),
                        array('text' => 'No'                                                                                  ),
                        array('text' => 'Yes'      , 'itemNameShortsToAddIfSelected' => array('iphone_ipad_headset_bluetooth'))
                     )
                  ),
/*
Comment out from here..
*/
                  array
                  (
                     'name'                      => 'desktop-dock' ,
                     'type'                      => 'select'       ,
                     'questionHtml'              => 'Desktop Dock:',
                     'defaultSelectedOptionText' => 'No'           ,
                     'displayConditions'         => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        )
                     ),
                     'options' => array
                     (
                        array('text' => 'Select...'      , 'classString'                   => 'not-valid-selection'    ),
                        array('text' => 'No'                                                                           ),
                        array('text' => 'iPhone only'    , 'itemNameShortsToAddIfSelected' => array('iphone_dock'     )),
                        array('text' => 'iPhone and iPad', 'itemNameShortsToAddIfSelected' => array('iphone_ipad_dock'))
                     )
                  ),
                  array
                  (
                     'name'                      => 'car-kit-install'                         ,
                     'type'                      => 'select'                                  ,
                     'questionHtml'              => 'Do you require a car kit for this phone?',
                     'defaultSelectedOptionText' => 'No'                                      ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString'                   => 'not-valid-selection'   ),
                        array('text' => 'No'                                                                    ),
                        array('text' => 'Yes'      , 'itemNameShortsToAddIfSelected' => array('car_kit_install'))
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        ),
                        array
                        (
                           'sectionNameShort' => 'request-information'       ,
                           'fieldName'        => 'existing-phone-has-car-kit',
                           'value'            => 'No'
                        )
                     )
                  ),
/*
to here.
*/
                  array
                  (
                     'name'                      => 'car-kit-upgrade'                         ,
                     'type'                      => 'select'                                  ,
                     'questionHtml'              => 'Do you require a car kit for this phone?',
                     'defaultSelectedOptionText' => 'No'                                      ,
                     'options'                   => array
                     (
                        array('text' => 'Select...', 'classString'                   => 'not-valid-selection'   ),
                        array('text' => 'No'                                                                    ),
                        array('text' => 'Yes'      , 'itemNameShortsToAddIfSelected' => array('car_kit_upgrade'))
                     ),
                     'displayConditions' => array
                     (
                        array
                        (
                           'sectionNameShort' => 'phone-options',
                           'fieldName'        => 'phone-type'   ,
                           'value'            => 'iPhone 5'
                        ),
                        array
                        (
                           'sectionNameShort' => 'request-information'       ,
                           'fieldName'        => 'existing-phone-has-car-kit',
                           'value'            => 'Yes'
                        )
                     )
                  )
               )
            ),
            array
            (
               'type'      => 'custom'         ,
               'nameShort' => 'purchase-bundle',
               'nameLong'  => 'Purchase Bundle'
            ),
            array
            (
               'type'      => 'custom'      ,
               'nameShort' => 'charge-codes',
               'nameLong'  => 'Charge Codes'
            ),
            array
            (
               'type'      => 'custom'         ,
               'nameShort' => 'phone-approvers',
               'nameLong'  => 'Approvers'
            ),
            array
            (
               'type'      => 'custom'   ,
               'nameShort' => 'agreement',
               'nameLong'  => 'Agreement'
            )
         )
      );
   }
}
?>
