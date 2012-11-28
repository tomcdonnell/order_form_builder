<?php
/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "CliHelperOutputHelper.php"
*
* Project: Utilities.
*
* Purpose: Helper class for use with command-line scripts.
*
* Author: Tom McDonnell 2011-11-11.
*
\**************************************************************************************************/

/*
 * This class helps when a problem described in CliHelper->_constructor() is encountered.
 * Using this class allows the extending class to be used with or without the CliHelper->out()
 * function depending on whether the CliHelper->out() function is passed to the extending class's
 * constructor.
 *
 * Usage
 * -----
 *
 * 1. Extend the CliHelperOutputHelper class.
 *    Inside the class, use $this->_out() wherever 'echo' would otherwise be used.
 *
 *    class StuffDoer extends CliHelperOutputHelper
 *    {
 *       function doStuff()
 *       {
 *          $this->_out('Doing stuff...');
 *          // Do stuff.
 *          $this->_out("Done.\n");
 *       }
 *    }
 *
 * 2. Create an instance of the CliHelper class as (see example in CliHelper.php).
 *    Create an instance of the StuffDoer class also.
 *
 *    $cliHelper = new CliHelper(__FILE__, '// Type script description here.');
 *    $stuffDoer = new StuffDoer(array($cliHelper, 'out'));
 *
 * 3. Call the $stuffDoer->doStuff() method from inside the try...catch block defined for the
 *    CliHelper (see example in CliHelper.php).
 */
class CliHelperOutputHelper
{
   /*
    *
    */
   public function __construct($outputFunction = null)
   {
      $this->_outputFunction = $outputFunction;
   }

   /*
    *
    */
   protected function _out($string)
   {
      if ($this->_outputFunction === null)
      {
          echo $string;
      }
      else
      {
          call_user_func($this->_outputFunction, $string);
      }
   }

   private $_outputFunction = null;
}

/*******************************************END*OF*FILE********************************************/
?>
