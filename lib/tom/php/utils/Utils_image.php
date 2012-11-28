<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "Utils_date.php"
*
* Project: Utilities.
*
* Purpose: Utilities relating to dates.
*
* Author: Tom McDonnell 2008-05-25.
*
\**************************************************************************************************/

// Class definition. ///////////////////////////////////////////////////////////////////////////////

/*
 *
 */
class Utils_image
{
   // Public functions. -----------------------------------------------------------------------//

   /*
    *
    */
   public function __construct()
   {
      throw new Exception('This class is not intended to be instatiated.');
   }

   /*
    *
    */
   public static function printXcenteredHorizTextString
   (
      $image, $fontSize, $minX, $maxX, $y, $textStr, $color
   )
   {
      // NOTE: The '+ 1' in the $x calculation below was found to be
      //       necessary to achieve proper centering by experiment.
      $x = ($minX + $maxX - strlen($textStr) * imagefontwidth($fontSize)) / 2 + 1;
      imagestring($image, $fontSize, $x, $y, $textStr, $color);
   }

   /*
    *
    */
   public static function printYcenteredVertTextString
   (
      $image, $fontSize, $x, $minY, $maxY, $textStr, $color
   )
   {
      $y = ($minY + $maxY + strlen($textStr) * imagefontwidth($fontSize)) / 2;
      imagestringup($image, $fontSize, $x, $y, $textStr, $color);
   }

   /*
    * NOTE: THis function has never been observed to work as intended.
    */
   public static function printExceptionAsImage($str, $image)
   {
      // Define and create .PNG image.
      header('Content-type: image/png');

      imagestring($image, 10, 0, 0, $str, imagecolorallocate($image, 0xff, 0x00, 0x00));

      // Output graph and clear image from memory.
      imagepng($image);
      imagedestroy($image);
   }
}

/*******************************************END*OF*FILE********************************************/
?>
