<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
class TableCellsMerger
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
    * Take an array defining data values for an HTML table, and convert that array into an array
    * having a different format.  The different format allows attribute information to be stored
    * for each td element in the HTML table.
    *
    * Convert an array of the form:
    *    array
    *    (
    *       array('row1_value1', 'row1_value2', 'row1_value3', ...),
    *       array('row2_value1', 'row2_value2', 'row2_value3', ...),
    *       ...
    *    )
    *
    * Into an array of the form:
    *    array
    *    (
    *       array
    *       (
    *          array('value' => 'row1_value1'),
    *          array('value' => 'row1_value2'),
    *          array('value' => 'row1_value3'),
    *          ...
    *       ),
    *       array
    *       (
    *          array('value' => 'row2_value1'),
    *          array('value' => 'row2_value2'),
    *          array('value' => 'row2_value3'),
    *          ...
    *       ),
    *       ...
    *    )
    *
    * @param $boolMergeVerticallyAdjacentInfosByRowNo
    *    If the value of this parameter is true, the effect will be that td values that are
    *    equal and vertically adjacent will be merged into a single td element.
    *    This is accomplished by adding an 'attributeValueByKey' element specifying a rowspan value
    *    to arrays that define merged cells.  See example below.
    *
    *    Supplied array for the two examples to follow:
    *       array
    *       (
    *          array('One', 'Two'  ),
    *          array('One', 'Three')
    *       )
    *
    *    Returned array with $boolMergeVerticallyAdjacentInfosByRowNo = false:
    *       array
    *       (
    *          array(array('value' => 'One'), array('value' => 'Two'  )),
    *          array(array('value' => 'One'), array('value' => 'Three'))
    *       )
    *
    *    Returned array with $boolMergeVerticallyAdjacentInfosByRowNo = true:
    *       array
    *       (
    *          array
    *          (
    *             array('value' => 'One', 'attributeValueByKey' => array('rowspan' => 2)),
    *             array('value' => 'Two')
    *          ),
    *          array(array('value' => 'Three'))
    *       )
    *
    * Note that the 'attributeValueByKey' key is optional, and will be included only if the rowspan
    * of a td element is not the default value of 1.
    */
   public static function convertRowsIntoTdInfosByRowNo
   (
      $rows, $boolMergeEqualVerticallyAdjacentCells = true
   )
   {
      if (count($rows) == 0)
      {
         return array();
      }

      $cols                                  = self::_convertRowsToCols($rows);
      $tdInfosByColNo                        = self::_convertColsToTdInfosByColNo($cols);
      $rowIndexPairsNotToBeMergedAsArrayKeys = array();

      if ($boolMergeEqualVerticallyAdjacentCells)
      {
         foreach ($tdInfosByColNo as $colNo => $tdInfos)
         {
            list($tdInfosByColNo[$colNo], $rowIndexPairsNotToBeMergedAsArrayKeys) =
            (
               self::_mergeVerticallyAdjacentTdsInColumnAddingNullsAsPlaceholders
               (
                  $tdInfos, $rowIndexPairsNotToBeMergedAsArrayKeys
               )
            );
         }
      }

      $tdInfosByRowNo = self::_convertTdInfosByColNoToTdInfosByRowNo($tdInfosByColNo);

      if ($boolMergeEqualVerticallyAdjacentCells)
      {
         foreach ($tdInfosByRowNo as $rowNo => $tdInfos)
         {
            $tdInfosByRowNo[$rowNo] = self::_removeNullPlaceholdersFromRow($tdInfos);
         }
      }

      return $tdInfosByRowNo;
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _convertRowsToCols($valueByColNoByRowNo)
   {
      $nRows = count($valueByColNoByRowNo);

      if ($nRows == 0)
      {
         return array();
      }

      $nCols               = count($valueByColNoByRowNo[0]);
      $valueByRowNoByColNo = array();

      for ($colNo = 0; $colNo < $nCols; ++$colNo)
      {
         $valueByRowNoByColNo[$colNo] = array_fill(0, $nRows, array());
      }

      for ($rowNo = 0; $rowNo < $nRows; ++$rowNo)
      {
         for ($colNo = 0; $colNo < $nCols; ++$colNo)
         {
            if (!array_key_exists($rowNo, $valueByColNoByRowNo))
            {
               throw new Exception
               (
                  "Numeric key $rowNo does not exist in array." .
                  'Did you use PDO::FETCH_ASSOC instead of PDO::FETCH_NUM?'
               );
            }

            $value = $valueByColNoByRowNo[$rowNo][$colNo];

            if (!is_string($value))
            {
               throw new Exception("Non-string value found at row $rowNo, col $colNo.");
            }

            $valueByRowNoByColNo[$colNo][$rowNo] = $value;
         }
      }

      return $valueByRowNoByColNo;
   }

   /*
    *
    */
   private static function _convertTdInfosByColNoToTdInfosByRowNo($tdInfosByColNo)
   {
      $nCols = count($tdInfosByColNo);

      if ($nCols == 0)
      {
         return array();
      }

      $nRows          = count($tdInfosByColNo[0]);
      $tdInfosByRowNo = array();

      for ($rowNo = 0; $rowNo < $nRows; ++$rowNo)
      {
         $tdInfosByRowNo[$rowNo] = array_fill(0, $nCols, array());
      }

      for ($colNo = 0; $colNo < $nCols; ++$colNo)
      {
         for ($rowNo = 0; $rowNo < $nRows; ++$rowNo)
         {
            $tdInfosByRowNo[$rowNo][$colNo] = $tdInfosByColNo[$colNo][$rowNo];
         }
      }

      return $tdInfosByRowNo;
   }

   /*
    *
    */
   private static function _convertColsToTdInfosByColNo($cols)
   {
      $nCols = count($cols);

      if ($nCols == 0)
      {
         return array();
      }

      $tdInfosByColNo = array();

      for ($colNo = 0; $colNo < $nCols; ++$colNo)
      {
         $tdInfosByColNo[$colNo] = array();

         foreach ($cols[$colNo] as $value)
         {
            $tdInfosByColNo[$colNo][] = array('value' => $value, 'attributeValueByKey' => array());
         }
      }

      return $tdInfosByColNo;
   }

   /*
    *
    */
   private static function _mergeVerticallyAdjacentTdsInColumnAddingNullsAsPlaceholders
   (
      $tdInfos, $rowIndexPairsNotToBeMergedAsArrayKeys
   )
   {
      $nRows            = count($tdInfos);
      $prevTdInfo       = null;

      for ($rowNo = 0; $rowNo < $nRows; ++$rowNo)
      {
         if ($prevTdInfo !== null)
         {
            $rowIndexPairKey = ($rowNo - 1) . "-$rowNo";

            if
            (
               $prevTdInfo['value'] == $tdInfos[$rowNo]['value'] &&
               !array_key_exists($rowIndexPairKey, $rowIndexPairsNotToBeMergedAsArrayKeys)
            )
            {
               if (!array_key_exists('rowspan', $prevTdInfo['attributeValueByKey']))
               {
                  $prevTdInfo['attributeValueByKey']['rowspan'] = 1;
               }

               ++$prevTdInfo['attributeValueByKey']['rowspan'];
               $tdInfos[$rowNo]  = null;
               continue;
            }

            $rowIndexPairsNotToBeMergedAsArrayKeys[$rowIndexPairKey] = null;
         }

         $prevTdInfo = &$tdInfos[$rowNo];
      }

      return array($tdInfos, $rowIndexPairsNotToBeMergedAsArrayKeys);
   }

   /*
    *
    */
   private static function _removeNullPlaceholdersFromRow($tdInfos)
   {
      foreach ($tdInfos as $key => $tdInfo)
      {
         if ($tdInfo === null)
         {
            unset($tdInfos[$key]);
         }
      }

      return $tdInfos;
   }
}
?>
