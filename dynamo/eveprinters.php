<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/site.php";
include_once "phplib/db-printer.php";

site_header("IPP Everywhere Self-Certified Printers");

// Collect form input...
if (array_key_exists("c", $_GET))
  $color = (int)$_GET["c"];
else
  $color = -1;

if (array_key_exists("d", $_GET))
  $duplex = (int)$_GET["d"];
else
  $duplex = -1;

if (array_key_exists("f", $_GET))
  $finishings = (int)$_GET["f"];
else
  $finishings = -1;

if (array_key_exists("s", $_GET))
  $search = trim($_GET["s"]);
else
  $search = "";

// Filter form...
print("<div style=\"display: block-inline; text-align: center;\">"
     ."<form action=\"$PHP_SELF\" method=\"GET\" class=\"form-inline\">\n");
html_form_text("s", "Name, etc.", $search, "", 1, "", 15);
print(" ");
html_form_select("c", array("-1" => "Color and B&W", "0" => "B&W Only", "1" => "Color Only"), "", $color);
print(" ");
html_form_select("d", array("-1" => "1 and 2-Sided", "0" => "1-Sided Only", "1" => "2-Sided Capable"), "", $duplex);
print(" ");
html_form_select("f", array("-1" => "Optional Staple, Punch, ...", "0" => "No Staple, Punch, ...", "1" => "Staple, Punch, ..."), "", $finishings);
print(" ");
html_form_end(array("" => "Filter Results"));
print("<hr>\n");

// Printer list...
$matches = printer_search($search, $color, $duplex, $finishings, "model");
$count   = sizeof($matches);

if ($count == 0)
  print("<p>No printers found.</p>\n");
else if ($count == 1)
  print("<p>1 printer found:</p>\n");
else
  print("<p>$count printers found:</p>\n");

if ($count > 0)
{
  html_start_table(array("Model", "Color?", "2-Sided?", "Staple, Punch, ...?"));

  foreach ($matches as $id)
  {
    $printer     = new printer($id);
    $pmodel      = htmlspecialchars($printer->model);
    $purl        = htmlspecialchars($printer->url, ENT_QUOTES);
    $pcolor      = $printer->color_supported ? "YES" : "NO";
    $pduplex     = $printer->duplex_supported ? "YES" : "NO";
    $pfinishings = $printer->finishings_supported ? "YES" : "NO";

    if ($purl != "")
      print("<tr><td><a href=\"$purl\" target=\"_blank\">$pmodel</td><td>$pcolor</td><td>$pduplex</td><td>$pfinishings</td></tr>\n");
    else
      print("<tr><td>$pmodel<td>$pcolor</td><td>$pduplex</td><td>$pfinishings</td></tr>\n");
  }

  html_end_table();
}

site_footer();

?>
