<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../config/database.php';

/*
 *
 */
class UserUtils
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
   public static function getIdUserFromSoeid(PdoExtended $pdoEx, $soeid)
   {
      return (int)$pdoEx->selectField
      (
         'SELECT identifier
          FROM user
          WHERE soeid=?',
         array($soeid)
      );
   }

   /*
    *
    */
   public static function getNameFullFromId(PdoExtended $pdoEx, $idUser)
   {
      return $pdoEx->selectField
      (
         'SELECT CONCAT(IF(preferredName="",firstName,preferredName)," ",lastName)
          FROM user
          WHERE identifier=?',
         array($idUser)
      );
   }

   /*
    *
    */
   public static function getSoeidFromId(PdoExtended $pdoEx, $idUser)
   {
      return $pdoEx->selectField
      (
         'SELECT soeid
          FROM user
          WHERE identifier=?',
         array($idUser)
      );
   }
}
?>
