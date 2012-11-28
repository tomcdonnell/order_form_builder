<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap go-=b
*
* Filename: "Util_2dArrayConverter.php"
*
* Project: Utilities.
*
* Purpose: Utilities for converting two dimensional arrays containing text to CSV or HTML table
*          format.
*
* Author: Tom McDonnell 2010-06-15.
*
\**************************************************************************************************/

// Includes. ///////////////////////////////////////////////////////////////////////////////////////

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Util_2dArrayConverter
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct(DatabaseConnection $dbc, $idCase)
   {
      throw new Exception('This function is not intended to be instantiated.');
   }

   /*
    * TODO
    * ----
    * Less repetition of code if get as CSV then convert from CSV to HTML.
    * Need to deal with commas in fields somehow though.
    */
   public function getAsHtml($indent = '  ')
   {
      $i = &$indent;

      $emptyRow = "$i  <tr>" . str_repeat('<td></td>', $this->maxCols) . "</tr>\n";

      $html  = "$i<table>\n";
      $html .= "$i <tbody>\n";
      $html .= "$i  <tr><th>Earth Resources Case Report</th>";
      $html .= str_repeat('<td></td>', $this->maxCols - 1) . "</tr>\n";
      $html .= $emptyRow;

      foreach ($this->reportInfo as $key => $value)
      {
         switch ($key)
         {
          case 'tableRow':
            $html .= $this->getRowsAsHtml(array($value), "$i  ");
            $html .= $emptyRow;
            $html .= $emptyRow;
            break;

          case 'rowDirectlyLinkedFromTableByTableLinkCol':
            foreach ($value as $tableLinkCol => $row)
            {
               $html .= "$i  <tr><th>$tableLinkCol</th>";
               $html .= str_repeat('<th></th>', $this->maxCols - 1) . "</tr>\n";
               $html .= $this->getRowsAsHtml(array($row), "$i  ");
               $html .= $emptyRow;
            }
            $html .= $emptyRow;
            break;

          case 'rowsDirectlyLinkedToTableByLinkedTableName':
            foreach ($value as $linkedTableName => $rows)
            {
               $html .= "$i  <tr><th>$linkedTableName</th>";
               foreach ($rows as $row)
               {
                  $html .= str_repeat('<th></th>', $this->maxCols - 1) . "</tr>\n";
                  $html .= $this->getRowsAsHtml(array($row), "$i  ");
               }
               $html .= $emptyRow;
            }
            $html .= $emptyRow;
            break;

          case 'rowsLinkedViaLinkTableByLinkTableName':
            foreach ($value as $linkTableName => $rows)
            {
               $html .= "$i  <tr><th>$linkTableName</th>";
               foreach ($rows as $row)
               {
                  $html .= str_repeat('<th></th>', $this->maxCols - 1) . "</tr>\n";
                  $html .= $this->getRowsAsHtml(array($row), "$i  ");
               }
               $html .= $emptyRow;
            }
            $html .= $emptyRow;
            break;
          default:
            throw new Exception("Unexpected array key '$key'.");
         }
      }

      $html .= "$i </tbody>\n";
      $html .= "$i</table>\n";

      return $html;
   }

   /*
    *
    */
   public function getAsCsv()
   {
      $emptyLine = str_repeat(',', $this->maxCols) . "\n";

      $csv  = '"Earth Resources Case Report"' . str_repeat(',', $this->maxCols - 1) . "\n";
      $csv .= $emptyLine;

      foreach ($this->reportInfo as $key => $value)
      {
         switch ($key)
         {
          case 'tableRow':
            $csv .= $this->getRowsAsCsv(array($value));
            $csv .= $emptyLine;
            $csv .= $emptyLine;
            break;

          case 'rowDirectlyLinkedFromTableByTableLinkCol':
            foreach ($value as $tableLinkCol => $row)
            {
               $csv .= '"' . str_replace('"', '\"', $tableLinkCol) . '"';
               $csv .= str_repeat(',', $this->maxCols - 1) . "\n";
               $csv .= $this->getRowsAsCsv(array($row));
               $csv .= $emptyLine;
            }
            $csv .= $emptyLine;
            break;

          case 'rowsDirectlyLinkedToTableByLinkedTableName':
            foreach ($value as $linkedTableName => $rows)
            {
               $csv .= '"' . str_replace('"', '\"', $linkedTableName) . '"';
               foreach ($rows as $row)
               {
                  $csv .= str_repeat(',', $this->maxCols - 1) . "\n";
                  $csv .= $this->getRowsAsCsv(array($row));
               }
               $csv .= $emptyLine;
            }
            $csv .= $emptyLine;
            break;

          case 'rowsLinkedViaLinkTableByLinkTableName':
            foreach ($value as $linkTableName => $rows)
            {
               $csv .= '"' . str_replace('"', '\"', $linkTableName) . '"';
               foreach ($rows as $row)
               {
                  $csv .= str_repeat(',', $this->maxCols - 1) . "\n";
                  $csv .= $this->getRowsAsCsv(array($row));
               }
               $csv .= $emptyLine;
            }
            $csv .= $emptyLine;
            break;
          default:
            throw new Exception("Unexpected array key '$key'.");
         }
      }

      return $csv;
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private function getMaxColsRequiredFromReportInfo()
   {
      $maxCols = 0;

      foreach ($this->reportInfo as $key => $rowOrRows)
      {
         switch ($key)
         {
          case 'tableRow':
            $rowsCombined = array($rowOrRows);
            break;

          case 'rowDirectlyLinkedFromTableByTableLinkCol':
            $rowsCombined = array_values($rowOrRows);
            break;

          case 'rowsDirectlyLinkedToTableByLinkedTableName':
            $rowsCombined = array();
            foreach ($rowOrRows as $linkedTableName => $rows)
            {
               $rowsCombined = array_merge($rowsCombined, $rows);
            }
            break;

          case 'rowsLinkedViaLinkTableByLinkTableName':
            $rowsCombined = array();
            foreach ($rowOrRows as $linkTableName => $rows)
            {
               $rowsCombined = array_merge($rowsCombined, $rows);
            }
            break;

          default:
            throw new Exception("Unexpected array key '$key'.");
         }

         foreach ($rowsCombined as $row)
         {
            $nCols = count($row);

            if ($nCols > $maxCols)
            {
               $maxCols = $nCols;
            }
         }
      }

      return $maxCols;
   }

   /*
    *
    */
   private function getRowsAsCsv($rows)
   {
      if (count($rows) == 0)
      {
         return '';
      }

      $csv        = '';
      $rowsKeys   = array_keys($rows);
      $firstRow   = $rows[$rowsKeys[0]];
      $nColsToPad = $this->maxCols - count($firstRow);

      // Headings row.
      $row         = $rows[0];
      $colHeadings = array_map
      (
         array('Utils_string', 'escapeAndEnclose'), array_keys($row)
      );
      $csv .= implode(',', $colHeadings) . str_repeat(',', $nColsToPad) . "\n";

      // Data rows.
      foreach ($rows as $row)
      {
         $colValues = array_map
         (
            array('Utils_string', 'escapeAndEnclose'), array_values($row)
         );
         $csv .= implode(',', $colValues) . str_repeat(',', $nColsToPad) . "\n";
      }

      return $csv;
   }

   /*
    *
    */
   private function getRowsAsHtml($rows, $indent = '   ')
   {
      if (count($rows) == 0)
      {
         return '';
      }

      $i          = &$indent;
      $html       = '';
      $rowsKeys   = array_keys($rows);
      $firstRow   = $rows[$rowsKeys[0]];
      $nColsToPad = $this->maxCols - count($firstRow);

      // Headings row.
      $row         = $rows[0];
      $colHeadings = array_map
      (
         array('Utils_string', 'encloseInThTags'), array_keys($row)
      );
      $html .= "$i<tr>" . implode('', $colHeadings);
      $html .= str_repeat('<th></th>', $nColsToPad) . "</tr>\n";

      // Data rows.
      foreach ($rows as $row)
      {
         $colValues = array_map
         (
            array('Utils_string', 'encloseInTdTags'), array_values($row)
         );
         $html .= "$i<tr>" . implode('', $colValues);
         $html .= str_repeat('<td></td>', $nColsToPad) . "</tr>\n";
      }

      return $html;
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////
}

/*******************************************END*OF*FILE********************************************/
?>
