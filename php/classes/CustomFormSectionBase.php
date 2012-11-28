<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

abstract class CustomFormSectionBase
{
   /*
    * @return {array}
    *    A list of the id attributes of all the input, select, and textarea
    *    HTML elements that are returned by the $this->getHtml() function.
    *    The list is used to validate submitted form values at the server.
    */
   abstract public function getInputIdAttributes();

   /*
    * @return {string}
    *    An HTML string.  Eg. "<fieldset><legend>Title</legend></fieldset>".
    */
   abstract public function getHtmlFilename();

   /*
    * @return {string or array}
    *    A string of Javascript code OR
    *    an array of names of files (including paths) that contain Javascript code.
    *
    * Define small snippets of Javascript inside the PHP function, but for large amounts of
    * Javascript, it is more convenient to have the Javascript in its own file or files.  Benefits
    * of the separate file approach are better syntax highlighting in text editors, and easier
    * debugging since the line numbers reported in error messages in the browser will correspond to
    * actual line numbers in the file or files.
    */
   abstract public function getJsFilenames();

   /*
    * @return {string or array}
    *    A string of CSS code OR
    *    an array of names of files (including paths) that contain CSS code.
    *
    * The same comments as are given for the getJsFilenamesIncludingPaths() function apply here.
    */
   abstract public function getCssFilenames();
}
?>
