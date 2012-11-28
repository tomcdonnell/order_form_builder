<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

require_once dirname(__FILE__) . '/../../lib/tom/php/utils/Utils_html.php';
require_once dirname(__FILE__) . '/UserUtils.php';

/*
 *
 */
class AuditDivHtmlGenerator
{
   // Public functions. /////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   public function __construct ()
   {
      throw new Exception('This class is not intended to be instantiated.');
   }

   /*
    *
    */
   public static function echoAuditDivHtml(PdoExtended $pdoEx, $idForm, $idFormEdit, $indent)
   {
      echo self::_getAuditDivHtml($pdoEx, $idForm, $idFormEdit, $indent);
   }

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   private static function _getAuditDivHtml(PdoExtended $pdoEx, $idForm, $idFormEdit, $indent)
   {
      if ($idFormEdit !== null)
      {
         self::_assertFormAndFormEditIdsMatch($pdoEx, $idForm, $idFormEdit);
      }

      $formEditInfoByIdFormEdit = FormUtils::getFormEditInfoByIdFormEditForForm($pdoEx, $idForm);

      $i     = &$indent;
      $html  = "$i<div class='audit-div'>\n";
      $html .= "$i <h2>Audit Mode</h2>\n";
      $html .= "$i <div class='left-div'>\n";
      $html .= "$i  <ol>\n";

      $boolFoundFormEdit = false;
      $idFormEditPrev    = null;
      $idFormEditNext    = null;

      $urlPrefix =
      (
         'http://' . Config::DOMAIN_NAME . Config::PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT .
         "/index.php?auditMode=1&idForm=$idForm"
      );

      foreach ($formEditInfoByIdFormEdit as $idFormEditLoop => $formEditInfo)
      {
         $created  = $formEditInfo['created'];
         $idUser   = $formEditInfo['idUser' ];
         $nameFull = UserUtils::getNameFullFromId($pdoEx, $idUser);
         $soeid    = UserUtils::getSoeidFromId($pdoEx, $idUser);

         switch ($boolFoundFormEdit)
         {
          case true : if ($idFormEditNext === null       ) {$idFormEditNext=$idFormEditLoop;} break;
          case false: if ($idFormEditLoop !== $idFormEdit) {$idFormEditPrev=$idFormEditLoop;} break;
         }

         switch ($idFormEditLoop == $idFormEdit)
         {
          case true : $classStr = "class='highlighted' "; $boolFoundFormEdit = true; break;
          case false: $classStr = '';                                                break;
         }

         $url   = "$urlPrefix&idFormEdit=$idFormEditLoop";
         $html .= "$i   <li {$classStr}title='" . Utils_html::escapeSingleQuotes($nameFull) . "'>";
         $html .=        htmlentities("$created - Saved by $soeid.") . ' ';
         $html .=       "<a href='" . Utils_html::escapeSingleQuotes($url) . "'>(show)</a>";
         $html .=      "</li>\n";
      }

      $html .= "$i  </ol>\n";
      $html .= "$i </div>\n";
      $html .= "$i <div class='right-div'>\n";

      if ($idFormEditPrev !== null)
      {
         $escapedUrl = Utils_html::escapeSingleQuotes("$urlPrefix&idFormEdit=$idFormEditPrev");
         $html      .= "$i  <div><a href='$escapedUrl'>show previous save</a></div>\n";
      }

      if ($idFormEditNext !== null)
      {
         $escapedUrl = Utils_html::escapeSingleQuotes("$urlPrefix&idFormEdit=$idFormEditNext");
         $html      .= "$i  <div><a href='$escapedUrl'>show next save</a></div>\n";
      }

      $html .= "$i </div>\n";
      $html .= "$i <div class='clear-floats'></div>\n";
      $html .= "$i <p class='explanation'>";
      $html .=     'NOTE: All input fields below are disabled because';
      $html .=          ' the form is displayed in Audit Mode.';
      $html .=    "</p>\n";
      $html .= "$i</div>\n";

      return $html;
   }

   /*
    *
    */
   private static function _assertFormAndFormEditIdsMatch(PdoExtended $pdoEx, $idForm, $idFormEdit)
   {
      if (!$pdoEx->rowExistsInTable('form_edit', array('idForm' => $idForm, 'id' => $idFormEdit)))
      {
         throw new Exception("Form/FormEdit mismatch: {idForm: $idForm, idFormEdit: $idFormEdit}.");
      }
   }
}
?>
