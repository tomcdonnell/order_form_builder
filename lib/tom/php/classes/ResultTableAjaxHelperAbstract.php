<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "ResultTableAjaxHelperAbstract.php"
*
* Project: General.
*
* Purpose: Helper class for generating AJAX responses for the Javascript ResultTable object.
*
* See also: /lib/tom/js/gui_elements/other/ResultTable.js
*           /lib/tom/php/ajax/result_table_ajax.php
*
* Author: Tom McDonnell 2010-12-16.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/../../../Zend/Db/Adapter/Pdo/Mysql.php';
require_once dirname(__FILE__) . '/../utils/Utils_validator.php';

/*
 * Abstract class for use with the Javascript ResultTable object.
 */
abstract class ResultTableAjaxHelperAbstract
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function getData($db, $params)
   {
      $this->_validateParamsAndSaveToPrivateVariables($db, $params);

      $sqlQueryPartOne = $this->_getSqlFromJoinWhereGroupBy();

      if ($sqlQueryPartOne === false)
      {
         // Variable $sqlQueryPartOne should be false if no SQL query can be run.
         // A table with no rows and an alternate heading will be displayed at the client.
         return $this->_getEmptyDataArray();
      }

      $colInfoByColIndex = $this->_getColumnInfoByColumnIndex();

      if ($sqlQueryPartOne === null)
      {
         // Variable $sqlQueryPartOne should be null if it is clear that no rows
         // can be returned and so there is no need to prepare and run the SQL query.
         // A table with no rows but with a normal heading will be displayed at the client.

         // Remove from $colInfoByColumnIndex details that are irrelevant to the client.
         foreach ($colInfoByColIndex as $colIndex => &$colInfo) {unset($colInfo['sqlExpression']);}

         return $this->_getFilledDataArray($colInfoByColIndex, array(), 0);
      }

      // Get variables from descendent class before setting fetch mode.  This is
      // necessary because the descendent class may use the same $db connection,
      // and will expect the fetch mode to be FETCH_ASSOC as normal.
      $rowIdSqlExpression         = $this->_getRowIdSqlExpression();
      $sqlExpressionByColumnIndex = $this->_getSqlExpressionByColumnIndex();
      $sqlQueryPartTwo            = $this->_getSqlOrderByLimitOffset();

      $db->setFetchMode(Zend_Db::FETCH_NUM);

      $rows = $db->fetchAll
      (
         "SELECT SQL_CALC_FOUND_ROWS
                 $rowIdSqlExpression,
                 " . implode(', ', $sqlExpressionByColumnIndex) . "
          $sqlQueryPartOne
          $sqlQueryPartTwo"
      );

      self::_replaceSpecialStringsInRowData($rows);

      $db->setFetchMode(Zend_Db::FETCH_ASSOC);

      $nRowsTotal        = (int)($db->fetchOne('SELECT FOUND_ROWS()'));
      $rowInfoByRowIndex = $this->_getRowInfoByRowIndex($rows);

      // Remove from $colInfoByColumnIndex details that are irrelevant to the client.
      foreach ($colInfoByColIndex as $colIndex => &$colInfo) {unset($colInfo['sqlExpression']);}

      return $this->_getFilledDataArray($colInfoByColIndex, $rowInfoByRowIndex, $nRowsTotal);
   }

   /*
    *
    */
   public function callCustomAjaxResponderFunction($db, $functionName, $params)
   {
      Utils_validator::checkArray
      (
         $params, array
         (
            'classClientParams' => 'array' ,
            'resultTableParams' => 'array' ,
            'rowId'             => 'string', // NOTE: May be other than int (eg. GUID).
            'valueByColIndex'   => 'array'
         )
      );
      extract($params);

      // Save the parameters to private variables so that they are available
      // to the descendent class via the protected _get...() functions.
      $this->_validateParamsAndSaveToPrivateVariables
      (
         $db, array
         (
            'classClientParams' => $classClientParams,
            'resultTableParams' => $resultTableParams
         )
      );

      $this->$functionName($rowId, $valueByColIndex);

      return $this->getData
      (
         $db, array
         (
            'classClientParams' => $classClientParams,
            'resultTableParams' => $resultTableParams
         )
      );
   }

   // Protected functions. //////////////////////////////////////////////////////////////////////

   protected function _getDb()                {return $this->_db               ;}
   protected function _getClassClientParams() {return $this->_classClientParams;}
   protected function _getResultTableParams() {return $this->_resultTableParams;}

   /*
    * NOTE
    * ----
    * This function is designed to be called by the descendent
    * class' _getSqlQueryMinusOrderByLimitAndOffset() function.
    */
   protected function _getSqlExpressionByColumnIndex()
   {
      $colInfoByColIndex       = $this->_getColumnInfoByColumnIndex();
      $sqlExpressionByColIndex = array();

      foreach ($colInfoByColIndex as $colIndex => $colInfo)
      {
         $sqlExpressionByColIndex[$colIndex] = $colInfo['sqlExpression'];
      }

      return $sqlExpressionByColIndex;
   }

   /*
    * Return a string to be used as the heading for the ResultTable.
    */
   abstract protected function _getHeading();

   /*
    * Return a string to be used as the footer for the ResultTable.
    */
   abstract protected function _getFooter();

   /*
    * Return an SQL expression to be used in the SELECT clause that will be prepended to the SQL
    * query returned by the function $this->_getSqlFromJoinWhereGroupBy().  The SQL expression
    * returned should select a unique id that can be used to refer to a given row uniquely.
    *
    * Eg. return 'activity.id'.
    */
   abstract protected function _getRowIdSqlExpression();

   /*
    * Return the SQL query that returns the data to be displayed in the ResultTable.
    *
    * The column count should be one more than the number of columns returned by the
    * _getSqlQueryMinusOrderByLimitAndOffset() SQL query.  The extra column in the SQL query is
    * for the id of the row, which is included for editing purposes only and is not displayed at
    * the client.
    *
    * The id should be a number that allows the server to determine what rows of what tables need
    * to be updated when it receives a 'updateRow' message from the client via AJAX.  The id will
    * be included in the 'updateRow' message.
    *
    * Eg. return
    *     (
    *        'SELECT activity.id, // id must be included, but will not be displayed at the client.
    *         activity_category.name, activity.activityNo, activity.name
    *         FROM activity
    *         JOIN activity_category ON (activity.idActivityCategory=activity_category.id)
    *         WHERE activity.deleted="0"'
    *     );
    */
   abstract protected function _getSqlFromJoinWhereGroupBy();

   /*
    * Return an array in the format described below.
    *
    * The array count should be one less than the number of columns returned by the
    * _getSqlQueryMinusOrderByLimitAndOffset() SQL query.  The extra column in the SQL query is for
    * the id of the row, which is included for editing purposes only and is not displayed at the
    * client.
    *
    * array
    * (
    *    array
    *    (
    *       'cssClassesStr' => <string css class names separated by spaces>,
    *       'heading'       => <string column heading>
    *    ),
    *    ...
    * );
    */
   abstract protected function _getColumnInfoByColumnIndex();

   /*
    * Return an array in the format described below, or null.
    *
    * If null is returned, then the client will interpret the null value as
    * being equivalent to an empty array being returned for each row index.
    *
    * The array count should be equal to the number returned by $this->_getMaxRowsPerPage().
    *
    * array
    * (
    *    // One of these arrays for each row index.
    *    array
    *    (
    *       // One of these arrays for each button to be displayed in the 'Actions' column.
    *       array
    *       (
    *          // The text to appear when the mouse hovers over the button.
    *          // (The DOM 'title' attribute for the INPUT element).
    *          'titleStr' => <string>,
    *
    *          // CSS classes to add to the INPUT element that will be the button.
    *          'cssClassesStr' => <string>,
    *
    *          // Optional.  The name of a Javascript function to be run when the button is clicked.
    *          'jsOnClickFunctionName' => <string>,
    *
    *          // Optional.  The name of a php function defined in class extending this one to be
    *          // run just as the $this->_updateRow() function is called following a click on the
    *          // 'save' button that appears for editable rows when the edit button is clicked.
    *          // The function named here should have no return value, but should throw an
    *          // exception if something goes wrong (similarly to the $this->_updateRow() function.
    *          'phpFunctionName' => <string>
    *       )
    *    ),
    *    ...
    * );
    */
   abstract protected function _getRowButtonsInfoByRowIndexOrNull();

   /*
    * Return an integer that will determine whether a rank column is
    * displayed at the far left of the Javascript ResultTable table.
    *
    * The value returned should be either:
    *  * -1, for no ranking column OR
    *  * a positive integer, being the rank of the first row of the table.
    *
    * For example, the first row of the first page will always have rank 1, but for subsequent
    * pages, the rank will not generally be equal to the row number because some rows may be of
    * equal rank.
    *
    * The ResultTable object will assign a rank to all rows other than the first row.  The rank
    * will be determined by comparing as strings the value in the far right column of each row.
    * See the Javascript ResultTable object for details.
    */
   abstract protected function _getFirstRowRank();

   /*
    * Expected to return a string to be used as the table heading by the Javascript ResultTable
    * object when no data has yet been returned from the server (before an AJAX call is made).
    */
   abstract protected function _getEmptyTableHeading();

   /*
    * Expected to return a string to be used as the table footer by the Javascript ResultTable
    * object when no data has yet been returned from the server (before an AJAX call is made).
    * Return a blank string to direct that no footer should be displayed.
    */
   abstract protected function _getEmptyTableFooter();

   /*
    * Expected to return an integer to be used as a constant.
    * Defines the number of rows to be displayed per page.
    */
   abstract protected function _getMaxRowsPerPage();

   /*
    * This function can be left completely empty if all columns of the table are non-editable.
    *
    * If any columns of the table are editable, this function should run the appropriate SQL
    * queries to save the updated row values.  A return value is not expected from this function,
    * but an exception should be thrown if something goes wrong.  The exception message will be
    * sent to the client.
    */
   abstract protected function _updateRow($rowId, $valuesByColIndex);

   /*
    * This function can be left completely empty if the insertion of new rows has been disabled.
    *
    * If the insertion of new rows is allowed, this function should run the appropriate SQL queries
    * to insert a new row, given a single value for each of the editable columns.  A return value
    * is not expected from this function, but an exception should be thrown if something goes
    * wrong.  The exception message will be sent to the client.
    */
   abstract protected function _insertRow($rowId, $valuesByColIndex);

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function _getRowInfoByRowIndex($rows)
   {
      $rowInfoByRowIndex        = array();
      $rowButtonsInfoByRowIndex = $this->_getRowButtonsInfoByRowIndexOrNull();

      if
      (
         $rowButtonsInfoByRowIndex        !== null &&
         count($rowButtonsInfoByRowIndex) !=  $this->_getMaxRowsPerPage()
      )
      {
         throw new Exception
         (
            'Function $this->_getRowButtonsInfoByRowIndexOrNull() must return null or' .
            ' an array whose count matches that returned by $this->_getMaxRowsPerPage().'
         );
      }

      foreach ($rows as $rowIndex => $row)
      {
         $rowId       = array_shift($row);
         $buttonsInfo =
         (
            ($rowButtonsInfoByRowIndex === null)? array(): $rowButtonsInfoByRowIndex[$rowIndex]
         );

         foreach ($buttonsInfo as &$buttonInfo)
         {
            Utils_validator::checkArray
            (
               $buttonInfo, array
               (
                  'cssClassesStr' => 'string',
                  'titleStr'      => 'string',
                  'valueStr'      => 'string'
               ),
               array
               (
                  'anchorHref'            => 'string',
                  'confirmString'         => 'string',
                  'jsOnClickFunctionName' => 'string',
                  'phpFunctionName'       => 'string',
                  'successMsg'            => 'string'
               )
            );

            // Substitute rowId into anchorHref if necessary.
            if (array_key_exists('anchorHref', $buttonInfo))
            {
               $buttonInfo['anchorHref'] = str_replace
               (
                  '__RowId__', $rowId, $buttonInfo['anchorHref']
               );
            }
         }

         // Return the rowId separate from the row, as expected by the client.
         $rowInfoByRowIndex[] = array
         (
            'data'        => array('id' => $rowId, 'valueByColIndex' => $row),
            'buttonsInfo' => $buttonsInfo
         );
      }

      return $rowInfoByRowIndex;
   }

   /*
    * Validate the given parameters and save them to private variables so that they
    * are available to descendent functions via the protected _get...() functions.
    */
   private function _validateParamsAndSaveToPrivateVariables($db, $params)
   {
      Utils_validator::checkArray
      (
         $params, array
         (
            'classClientParams' => 'array',
            'resultTableParams' => 'array'
         )
      );

      Utils_validator::checkArray
      (
         $params['resultTableParams'], array
         (
            'offset'      => 'nonNegativeInt',
            'orderByInfo' => 'array'
         )
      );

      $this->_db                = $db;
      $this->_classClientParams = $params['classClientParams'];
      $this->_resultTableParams = $params['resultTableParams'];
   }

   /*
    *
    */
   private function _getSqlOrderByLimitOffset()
   {
      $columnHeadingSqlExpressions = $this->_getSqlExpressionByColumnIndex();
      $orderByExpressions          = array();
      $resultTableParams           = $this->_resultTableParams;

      foreach ($resultTableParams['orderByInfo'] as $colNoAndDirection)
      {
         if (count($colNoAndDirection) != 2)
         {
            throw new Exception('Unexpected count for colNoAndDirection.');
         }

         $colNo     = $colNoAndDirection[0];
         $ascORdesc = $colNoAndDirection[1];

         if ($ascORdesc != 'asc' && $ascORdesc != 'desc')
         {
            throw new Exception("Unexpected value '$ascORdesc' for ascORdesc.");
         }

         // NOTE
         // ----
         // If the number of columns selected in the SQL query depends on the classClientParams,
         // then it is possible that the resultTableParams['orderByInfo'] will refer to a column
         // outside the range of the current SQL expression.  The reason is that resultTableParams
         // from the previous AJAX call are passed unchanged back to the server for the next AJAX
         // call.  If the resultTableParams['orderByInfo'] is out of range, it is simply ignored.
         if ($colNo < count($columnHeadingSqlExpressions))
         {
            $orderByExpressions[] =
            (
               "{$columnHeadingSqlExpressions[$colNo]} " . strtoupper($ascORdesc)
            );
         }
      }

      $sqlOrderByLimitOffset =
      (
         (count($orderByExpressions) == 0)? '':
         'ORDER BY ' . implode(', ', $orderByExpressions) . "\n"
      ) . 'LIMIT ' . $this->_getMaxRowsPerPage() . "\nOFFSET {$resultTableParams['offset']}";

      return $sqlOrderByLimitOffset;
   }

   /*
    *
    */
   private function _getEmptyDataArray()
   {
      return array
      (
         'colInfoByColIndex' => array()                       ,
         'firstRowRank'      => -1                            ,
         'footer'            => $this->_getEmptyTableFooter() ,
         'heading'           => $this->_getEmptyTableHeading(),
         'maxRowsPerPage'    => $this->_getMaxRowsPerPage()   ,
         'nRowsTotal'        => 0                             ,
         'offset'            => 0                             ,
         'rowInfoByRowIndex' => array()                       ,
         'subheading'        => ''
      );
   }

   /*
    *
    */
   private function _getFilledDataArray($colInfoByColIndex, $rowInfoByRowIndex, $nRowsTotal)
   {
      return array
      (
         'colInfoByColIndex' => $colInfoByColIndex                 ,
         'firstRowRank'      => $this->_getFirstRowRank()          ,
         'footer'            => $this->_getFooter()                ,
         'heading'           => $this->_getHeading()               ,
         'maxRowsPerPage'    => $this->_getMaxRowsPerPage()        ,
         'nRowsTotal'        => (int)$nRowsTotal                   ,
         'offset'            => $this->_resultTableParams['offset'],
         'rowInfoByRowIndex' => $rowInfoByRowIndex                 ,
         'subheading'        => $this->_getSubheading()
      );
   }

   /*
    *
    */
   private function _replaceSpecialStringsInRowData(&$rows)
   {
      foreach ($rows as &$colValueByIndex)
      {
         foreach ($colValueByIndex as $index => &$colValue)
         {
            // ASSUMPTION
            // ----------
            // The value at row index zero is the rowId.
            // This is ensured in function self::getData().
            $colValue = str_replace('__RowId__', $colValueByIndex[0], $colValue);
         }
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private $_db                = null;
   private $_classClientParams = null;
   private $_resultTableParams = null;
}

/*******************************************END*OF*FILE********************************************/
?>
