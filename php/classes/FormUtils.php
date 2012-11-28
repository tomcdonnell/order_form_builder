<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
class FormUtils
{
   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function getFieldIdAttributeFromSectionNameFieldName($sectionName, $fieldName)
   {
      return str_replace(' ', '_', "$sectionName|$fieldName");
   }

   /*
    *
    */
   public static function getFieldsetCssClassFromSectionName($sectionName)
   {
      return 'form-section form-section-' . strtolower(str_replace(' ', '-', $sectionName));
   }

   /*
    *
    */
   public static function getOptionValuesFromSelectField(Array $field, $boolValidOptionsOnly)
   {
      if ($field['type'] != 'select')
      {
         throw new Exception("Unexpected field type is not 'select'.");
      }

      $validOptionValues = array();

      foreach ($field['options'] as $option)
      {
         if
         (
            $boolValidOptionsOnly                    &&
            array_key_exists('classString', $option) &&
            strpos($option['classString'], 'not-valid-selection') !== false
         )
         {
            continue;
         }

         $validOptionValues[] = $option['text'];
      }

      return $validOptionValues;
   }

   /*
    *
    */
   public static function getFormTypeNameShortForForm(PdoExtended $pdoEx, $idForm)
   {
      return $pdoEx->selectField
      (
         'SELECT nameShort
          FROM form_type
          JOIN form ON (form.idFormType=form_type.id)
          WHERE form.id=?',
         array($idForm)
      );
   }

   /*
    *
    */
   public static function getIdFormTypeFromNameShort(PdoExtended $pdoEx, $nameShort)
   {
      return (int)$pdoEx->selectField
      (
         'SELECT id
          FROM form_type
          WHERE nameShort=?',
         array($nameShort)
      );
   }

   /*
    *
    */
   public static function getIdFormFromIdFormEdit(PdoExtended $pdoEx, $idFormEdit)
   {
      return (int)$pdoEx->selectField
      (
         'SELECT idForm
          FROM form_edit
          WHERE id=?',
         array($idFormEdit)
      );
   }

   /*
    *
    */
   public static function getValueByFieldIdAttribute
   (
      PdoExtended $pdoEx, $idForm, $idFormEditAtWhichToStop = null
   )
   {
      $formEditInfoByIdFormEdit = self::getFormEditInfoByIdFormEditForForm($pdoEx, $idForm);
      $valueByFieldIdAttribute  = array();

      // Build $valueByFieldIdAttribute by starting with values from the earliest form edit, and
      // overwriting with new values from each subsequent form edit until the last is reached.
      foreach ($formEditInfoByIdFormEdit as $idFormEdit => $info)
      {
         foreach ($info['valueByFieldIdAttribute'] as $fieldIdAttribute => $value)
         {
            $valueByFieldIdAttribute[$fieldIdAttribute] = $value;
         }

         if ($idFormEdit == $idFormEditAtWhichToStop)
         {
            break;
         }
      }

      return $valueByFieldIdAttribute;
   }

   /*
    *
    */
   public static function getFormEditNumberFromId
   (
      PdoExtended $pdoEx, $idFormEdit, $boolReturnAsNumberWithStringSuffix
   )
   {
      $formEditNumber = (int)$pdoEx->selectField
      (
         'SELECT editNumber
          FROM form_edit
          WHERE id=?',
         array($idFormEdit)
      );
/*
The query below should be equivalent to the one above, except for cases where the system clock has been incorrectly set.  TODO: Write a cron script to check this.
         'SELECT COUNT(*) + 1 AS formEditNo
          FROM form_edit
          WHERE idForm=
          (
             SELECT idForm
             FROM form_edit
             WHERE id=?
          )
          AND form_edit.created<
          (
             SELECT created
             FROM form_edit
             WHERE id=?
          )',
         array($idFormEdit, $idFormEdit)
*/

      if ($boolReturnAsNumberWithStringSuffix)
      {
         switch ($formEditNumber % 10)
         {
          case 1 : return "{$formEditNumber}st";
          case 2 : return "{$formEditNumber}nd";
          case 3 : return "{$formEditNumber}rd";
          default: return "{$formEditNumber}th";
         }
      }

      return $formEditNumber;
   }

   /*
    *
    */
   public static function getMostRecentEditNumberOrNull(PdoExtended $pdoEx, $idForm)
   {
      $rows = $pdoEx->selectRows
      (
         'SELECT id AS idFormEdit
          FROM form_edit
          WHERE idForm=?
          ORDER BY editNumber DESC
          LIMIT 1',
         array($idForm)
      );

      switch (count($rows))
      {
       case 0: return null;
       case 1: return (int)$rows[0]['idFormEdit'];
       default: throw new Exception('Unexpected number of rows returned by SQL query.');
      }
   }

   /*
    *
    */
   public static function getPreviousIdFormEditOrNull(PdoExtended $pdoEx, $idFormEdit)
   {
      $rows = $pdoEx->selectRows
      (
         'SELECT id
          FROM form_edit
          WHERE idForm=
          (
             SELECT idForm
             FROM form_edit
             WHERE id=?
          )
          AND editNumber=
          (
             SELECT editNumber
             FROM form_edit
             WHERE id=?
          )-1',
         array($idFormEdit, $idFormEdit)
      );

      switch (count($rows))
      {
       case 0 : return null;
       case 1 : return (int)$rows[0]['id'];
       default: throw new Exception('Unexpected number of rows returned by SQL query.');
      }
   }

   /*
    * Return an array matching the format of the following example:
    *
    * array
    * (
    *    <idFormEdit1> => array
    *    (
    *       'editNumber'              => 1                    ,
    *       'created'                 => '2012-03-14 12:43:04',
    *       'idUser'                  => 5                    ,
    *       'valueByFieldIdAttribute' => array
    *       (
    *          'section-one-name|field-one-name' => 'value1',
    *          ...
    *       )
    *    ),
    *    ...
    * )
    *
    * The returned array will be sorted in ascending order of creation time.
    */
   public static function getFormEditInfoByIdFormEditForForm(PdoExtended $pdoEx, $idForm)
   {
      $rows = $pdoEx->selectRows
      (
         'SELECT id AS idFormEdit, editNumber, created, idUser
          FROM form_edit
          WHERE idForm=?
          ORDER BY editNumber ASC',
         array($idForm)
      );

      $formEditInfoById = array();

      foreach ($rows as $row)
      {
         extract($row);

         $formEditInfoById[$idFormEdit] = array
         (
            'created'                 => $created   ,
            'editNumber'              => $editNumber,
            'idUser'                  => $idUser    ,
            'valueByFieldIdAttribute' => $pdoEx->selectIndexedColumn
            (
               'SELECT fieldIdAttribute, fieldValue
                FROM form_edit_data
                WHERE idFormEdit=?',
               array($idFormEdit)
            )
         );
      }

      return $formEditInfoById;
   }
}
?>
