<?php
//
// PWG home page.
//

include_once "phplib/site.php";
include_once "phplib/db-document.php";

site_header("Published Standards", "", TRUE);

$groups = array(
  5100 => "Internet Printing Protocol",
  5101 => "Registrations",
  5102 => "File Formats",
  5104 => "Print System Interface",
  5105 => "Semantic Model v1",
  5106 => "Workgroup for Imaging Management Services (WIMS)",
  5107 => "MIB Workgroup",
  5108 => "Semantic Model v2",
  5109 => "Cloud Imaging Model",
  5110 => "Imaging Device Security"
);

$matches = document_search("", "series,number", -1, DOCUMENT_STATUS_CANDIDATE_STANDARD);

print("<div id=\"pwg-standards\">\n");

print("<ul id=\"pwg-series\" class=\"nav nav-tabs\" data-tabs=\"pwg-series\" role=\"complementary\">\n");
$series = 0;
foreach ($matches as $id)
{
  $doc = new document($id);

  if ($series != $doc->series)
  {
    if ($series == 0)
      $active = " class=\"active\"";
    else
      $active = "";

    $series = $doc->series;

    print("<li$active><a data-toggle=\"tab\" href=\"#s$series\">$series.x</a></li>\n");
  }
}
print("</ul>\n");

print("<div class=\"tab-content\">\n");

$series = 0;
foreach ($matches as $id)
{
  $doc = new document($id);

  if ($series != $doc->series)
  {
    if ($series == 0)
      $active = " in active";
    else
      $active = "";

    if ($series != 0)
      print("</div>\n");

    $series = $doc->series;

    if (array_key_exists($doc->series, $groups))
      $group = $groups[$doc->series];
    else
      $group = "Other";

    print("<div class=\"tab-pane fade$active\" id=\"s$series\"><h1>PWG $series.x: $group</h1>\n");
  }

  $doc->view("", 2, FALSE);
}
if ($series != 0)
  print("</div>\n");

print("</div></div>\n");

site_footer();

?>
