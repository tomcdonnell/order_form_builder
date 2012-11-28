<?php
/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
class Config
{
   // NOTE
   // ----
   // The two PATH constants below must be updated in each working copy of the project that is
   // checked out from the subversion repository if the paths in the local copy differ from the
   // paths in the live version.  Modifications to this value to suit the local environment should
   // not be committed back to the repository however, so that local values in other working
   // copies are not overwritten when an svn update is performed.
   //
   // The PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT is to be used for locating files in code executed at
   // the client (where the root directory is the web root directory).  For example, paths in css
   // files generated using php should use the PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT.
   //
   // The PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT is to be used for locating files in code executed
   // at the server.  For example, paths in php scripts should use the PATH_TO_PROJECT_ROOT_FROM-
   // _SERVER_ROOT.
   //
   // Both paths specified below must begin with a '/'.

   // Production.
   //const DOMAIN_NAME                           = 'intra';
   //const PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT    = '/order_form_builder';
   //const PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT = '/Apps/Public_Html/order_form_builder';

   // Testing.
   //const DOMAIN_NAME                           = 'localhost';
   //const PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT    = '/order_form_builder';
   //const PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT = '/data/xampp/htdocs/order_form_builder';

   // Home..
   const DOMAIN_NAME                           = 'localhost';
   const PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT    = '/tom/order_form_builder';
   const PATH_TO_PROJECT_ROOT_FROM_SERVER_ROOT = '/home/tom/htdocs/order_form_builder';

   const FAILURE_MESSAGE_TIMEOUT_MS = 3000;
}
