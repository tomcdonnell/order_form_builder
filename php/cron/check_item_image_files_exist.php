<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/database.php';
require_once dirname(__FILE__) . '/../../config/Config.php';

echo "\nGetting all image filenames from the `item` table...";
$rows = $pdoEx->selectRows
(
   'SELECT id, nameShort, imageFilename
    FROM item
    ORDER BY id ASC'
);
$nFilenames = count($rows);
echo "done.\nGot $nFilenames filenames.\n\n";

echo 'Checking that the image files exist...';
$rowsForWhichNoImageFound = array();
foreach ($rows as $row)
{
   if (!file_exists(Config::PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT . "/{$row['imageFilename']}"))
   {
      $rowsForWhichNoImageFound[] = $row;
   }
}
echo "done.\n\n";

if (count($rowsForWhichNoImageFound) > 0)
{
   $columnWidths = array(6, 40);
   echo "Failure!  The following image files were not found:\n\n";
   printf
   (
      "| %{$columnWidths[0]}s | %-{$columnWidths[1]}s | %s\n",
      'itemId', 'itemNameShort', 'itemImageFilename'
   );

   foreach ($rowsForWhichNoImageFound as $row)
   {
      printf
      (
         "| %{$columnWidths[0]}s | %-{$columnWidths[1]}s | %s\n",
         $row['id'], $row['nameShort'], $row['imageFilename']
      );
   }
}
else
{
   echo "Success!  All $nFilenames image files exist.\n";
}

echo "\n";
?>
