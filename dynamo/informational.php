<?php
//
// PWG home page.
//

include_once "phplib/site.php";
include_once "phplib/db-document.php";

site_header("Informational Documents", "", TRUE);

$matches = document_search("", "number", -1, DOCUMENT_STATUS_INFORMATIONAL, DOCUMENT_STATUS_INFORMATIONAL);

foreach ($matches as $id)
{
  $doc = new document($id);

  $doc->view("", 2, FALSE);
}

site_footer();

?>
