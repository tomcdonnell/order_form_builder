<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/PostArrayValidator.php';
require_once dirname(__FILE__) . '/FormUtils.php';
require_once dirname(__FILE__) . '/UserUtils.php';
require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_array.php';

/*
 *
 */
class FormWriter
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    * Note Regarding MySQL Transactions and Responsibility
    * ----------------------------------------------------
    * It is the calling function's responsibility to enclose this operation in a MySQL transaction.
    */
   public static function saveValidatedPostArrayAsFormEdit
   (
      PdoExtended $pdoEx, $idForm, Array $postArray
   )
   {
      if ($idForm === null)
      {
         $idForm = $pdoEx->insert
         (
            'form', array
            (
               'idFormType' => FormUtils::getIdFormTypeFromNameShort
               (
                  $pdoEx, $postArray['formTypeNameShort']
               )
            )
         );
      }

      foreach (PostArrayValidator::getExtraPostArrayKeysNotToBeSaved() as $extraKey)
      {
         unset($postArray[$extraKey]);
      }

      $previousValueByFieldIdAttribute = FormUtils::getValueByFieldIdAttribute($pdoEx, $idForm);

      if (Utils_array::arraysAreEqual($postArray, $previousValueByFieldIdAttribute))
      {
         return array(null, 0);
      }

      $mostRecentEditNumber      = FormUtils::getMostRecentEditNumberOrNull($pdoEx, $idForm);
      $nFormEditDataRowsInserted = 0;
      $idFormEdit                = $pdoEx->insert
      (
         'form_edit', array
         (
            // Note Regarding Exceptions and SOE ids
            // -------------------------------------
            // Client-side validation would have prevented the form from being submitted if the SOE
            // id was invalid.  Therefore the SOE id should be valid unless an error has occurred.
            // If an error has occurred it is appropriate to throw an exception.  Therefore the
            // fact that function UserUtils::getIdUserFromSoeid() will throw an exception if the
            // SOE id is invalid is appropriate.
            'editNumber' => (($mostRecentEditNumber === null)? 1: $mostRecentEditNumber + 1),
            'created'    => date('Y-m-d H:i:s')                                             ,
            'idForm'     => $idForm                                                         ,
            'idUser'     => UserUtils::getIdUserFromSoeid
            (
               $pdoEx, $postArray['contact-details|soeid']
            )
         )
      );

      foreach ($postArray as $fieldIdAttribute => $value)
      {
         if
         (
            array_key_exists($fieldIdAttribute, $previousValueByFieldIdAttribute) &&
            $value == $previousValueByFieldIdAttribute[$fieldIdAttribute]
         )
         {
            continue;
         }

         $idFormData = $pdoEx->insert
         (
            'form_edit_data', array
            (
               'idFormEdit'       => $idFormEdit      ,
               'fieldIdAttribute' => $fieldIdAttribute,
               'fieldValue'       => $value
            )
         );

         ++$nFormEditDataRowsInserted;
      }

      return array($idFormEdit, $nFormEditDataRowsInserted);
   }
}
?>
