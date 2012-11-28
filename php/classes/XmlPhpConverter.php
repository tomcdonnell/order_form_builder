<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
class XmlPhpConverter
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
   public static function simpleXmlElementToArray(SimpleXMLElement $xmlObj)
   {
      $xmlObjAsArray = get_object_vars($xmlObj);
      $returnArray   = array();

      foreach ($xmlObjAsArray as $key => $value)
      {
         if (is_a($value, 'SimpleXMLElement'))
         {
            $returnArray[$key] = self::simpleXmlElementToArray($value);
            continue;
         }

         if (is_string($value))
         {
            $returnArray[$key] = $value;
            continue;
         }

         if (is_array($value))
         {
            $returnArray[$key] = array();

            foreach ($value as $childValue)
            {
               if (is_a($childValue, 'SimpleXMLElement'))
               {
                  $returnArray[$key][] = self::simpleXmlElementToArray($childValue);
                  continue;
               }

               throw new Exception('Unexpected value (1).' . var_dump($childValue, true));
            }

            continue;
         }

         throw new Exception('Unexpected value (2). ' . var_dump($value, true));
      }

      return $returnArray;
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   private static $_recursionDepth = 0;
}
?>
