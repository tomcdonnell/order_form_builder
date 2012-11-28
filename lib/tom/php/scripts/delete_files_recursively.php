<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "delete_files_recursively.php"
*
* Project: Scripts.
*
* Purpose: Delete all files matching a given regex in the current directory and all subdirectories.
*
* Author: Tom McDonnell 2012-06-07.
*
\**************************************************************************************************/

require_once dirname(__FILE__) . '/../utils/Utils_error.php';

// Global variables. ///////////////////////////////////////////////////////////////////////////////

$usageString =  <<<STR

Delete Files Recursively
------------------------

Usage:
   php delete_files_recursively.php <directory_name> <regex> <option>

   Where 'option' is one of those listed below (including the hyphens).

Options:
   --hypothetical
   List all files and directories that would have been deleted had --actual
   been used.

   --actual
   Delete all files (or directories including all content) whose names match
   regular expression <regex> (using php function preg_match()) that exist in
   directory <directory_name> or in any subdirectory thereof.  Output the names
   of files and directories deleted as in --hypothetical.

Example:
   php delete_files_recursively.php project /.svn$/ --hypothetical

   List all files in directory 'project' recursively.
   If --actual is used instead of --hypothetical, delete those files.


STR;

// Globally executed code. /////////////////////////////////////////////////////////////////////////

Utils_error::initErrorAndExceptionHandler('log.txt');

$filename = __FILE__;

if ($argc != 4) 
{
   echo "\nIncorrect number of parameters.\n";
   die($usageString);
}

switch ($argv[3])
{
 case '--hypothetical': $boolActuallyDeleteFiles = false; break;
 case '--actual'      : $boolActuallyDeleteFiles = true ; break;
 default              : die($usageString);
}

if (!$boolActuallyDeleteFiles)
{
   echo "\nNo files will be deleted since --hypothetical was used.\n";
   echo "The following files would have been deleted if --actual had been used.\n\n";
}

list($nFilesDeleted, $nDirectoriesDeleted) = deleteFilesAndDirectoriesMatchingRegexRecursively
(
   $argv[1], $argv[2], $boolActuallyDeleteFiles
);

echo "\n$nFilesDeleted files and $nDirectoriesDeleted directories ",
(
   ($boolActuallyDeleteFiles)?
   "were deleted.": "would have been deleted if --actual was used."
);
echo "\n\n";

// Functions. //////////////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function deleteFilesAndDirectoriesMatchingRegexRecursively
(
   $dirname, $regEx, $boolActuallyDeleteFiles
)
{
   if (!is_dir($dirname))
   {
      throw new Exception("'$dirname' is not a directory.");
   }

   // NOTE: Function glob() will not return hidden files unless they are explicity requested.
   $filenames               = array_merge(glob($dirname . '/*'), glob($dirname . '/.*'));
   $totalDeletedFiles       = 0;
   $totalDeletedDirectories = 0;

   foreach ($filenames as $filename)
   {
      if (is_dir($filename))
      {
         if
         (
            substr($filename, strlen($filename) - 3) == '/..' ||
            substr($filename, strlen($filename) - 2) == '/.'
         )
         {
            continue;
         }

         if (preg_match($regEx, $filename) != 0)
         {
            list($nFilesDeleted, $nDirectoriesDeleted) =
            (
               deleteDirectoryAndContentsRecursively($filename, $boolActuallyDeleteFiles)
            );

            echo "$filename (directory containing $nFilesDeleted files";
            echo " and $nDirectoriesDeleted subdirectories)\n";

            $totalDeletedFiles       += $nFilesDeleted;
            $totalDeletedDirectories += $nDirectoriesDeleted;

            continue;
         }

         list($nFilesDeleted, $nDirectoriesDeleted) =
         (
            deleteFilesAndDirectoriesMatchingRegexRecursively
            (
               $filename, $regEx, $boolActuallyDeleteFiles
            )
         );

         $totalDeletedFiles       += $nFilesDeleted;
         $totalDeletedDirectories += $nDirectoriesDeleted;

         continue;
      }

      if (preg_match($regEx, $filename) == 0)
      {
         continue;
      }

      echo "$filename\n";
      ++$totalDeletedFiles;

      if ($boolActuallyDeleteFiles)
      {
         unlink($filename);
      }
   }

   return array($totalDeletedFiles, $totalDeletedDirectories);
}

/*
 *
 */
function deleteDirectoryAndContentsRecursively($dirname, $boolActuallyDeleteFiles)
{
   if (!is_dir($dirname))
   {
      throw new Exception("'$dirname' is not a directory.");
   }

   // NOTE: Function glob() will not return hidden files unless they are explicity requested.
   $filenames               = array_merge(glob($dirname . '/*'), glob($dirname . '/.*'));
   $totalDeletedFiles       = 0;
   $totalDeletedDirectories = 0;

   foreach ($filenames as $filename)
   {
      if (is_dir($filename))
      {
         if
         (
            substr($filename, strlen($filename) - 3) == '/..' ||
            substr($filename, strlen($filename) - 2) == '/.'
         )
         {
            continue;
         }

         list($nFilesDeleted, $nDirectoriesDeleted) =
         (
            deleteDirectoryAndContentsRecursively($filename, $boolActuallyDeleteFiles)
         );

         $totalDeletedFiles       += $nFilesDeleted;
         $totalDeletedDirectories += $nDirectoriesDeleted;

         continue;
      }

      ++$totalDeletedFiles;

      if ($boolActuallyDeleteFiles)
      {
         unlink($filename);
      }
   }

   ++$totalDeletedDirectories;

   if ($boolActuallyDeleteFiles)
   {
      rmdir($dirname);
   }

   return array($totalDeletedFiles, $totalDeletedDirectories);
}

/*******************************************END*OF*FILE********************************************/
?>
