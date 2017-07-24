<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/site.php";
include_once "phplib/db-printer.php";

// Handle updates...
if ($LOGIN_ID > 0 && $argc && $argv[0][0] == "U" && (int)substr($argv[0], 1) > 0)
{
  $id = (int)substr($argv[0], 1);
  $printer = new printer($id);
  if ($printer->id == $id && $id > 0 && ($LOGIN_IS_ADMIN || $printer->create_id == $LOGIN_ID))
  {
    if ($printer->loadform())
    {
      // Save changes...
      $printer->save();
      header("Location: $PHP_SELF");
    }
    else
    {
      // Show form...
      site_header("Update IPP Everywhere(tm) Self-Certified Printer");

      print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

      if ($REQUEST_METHOD == "POST")
        html_show_error("Please correct the highlighted fields.");

      $printer->form();
      site_footer();
    }

    exit(0);
  }
}

// Otherwise show the list...
site_header("IPP Everywhere(tm) Self-Certified Printers");

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

if (array_key_exists("t", $_GET))
  $ipps = (int)$_GET["t"];
else
  $ipps = -1;

if (array_key_exists("s", $_GET))
  $search = trim($_GET["s"]);
else
  $search = "";

// Filter form...
print("<div style=\"display: block-inline; text-align: center;\">"
     ."<img src=\"/ipp/ipp-everywhere.png\" width=\"55\" height=\"64\" align=\"left\" alt=\"IPP Everywhere&trade;\">"
     ."<form action=\"$PHP_SELF\" method=\"GET\" class=\"form-inline\">\n");
html_form_search("s", "Name, etc.", $search, "");
html_form_buttons(array("" => "Filter Results"));
print("<br>\n");
html_form_select("c", array("-1" => "Color and B&W", "0" => "B&W Only", "1" => "Color Only"), "", $color);
print(" ");
html_form_select("d", array("-1" => "1 and 2-Sided", "0" => "1-Sided Only", "1" => "2-Sided Capable"), "", $duplex);
print(" ");
html_form_select("f", array("-1" => "Optional Staple, Punch, ...", "0" => "No Staple, Punch, ...", "1" => "Staple, Punch, ..."), "", $finishings);
print(" ");
html_form_select("t", array("-1" => "Optional IPPS", "0" => "No IPPS", "1" => "IPPS Only"), "", $ipps);
print(" ");
html_form_end();
print("<hr>\n");

// Printer list...
$matches = printer_search($search, $color, $duplex, $finishings, $ipps, "model");
$count   = sizeof($matches);

if ($count == 0)
  print("<p>No printers found.</p>\n");
else if ($count == 1)
  print("<p>1 printer found:</p>\n");
else
  print("<p>$count printers found:</p>\n");

if ($count > 0)
{
  html_start_table(array("Model", "Color?", "2-Sided?", "Finishers?", "IPPS?"));

  foreach ($matches as $id)
  {
    $printer     = new printer($id);
    $pmodel      = htmlspecialchars($printer->model);
    $purl        = htmlspecialchars($printer->url, ENT_QUOTES);
    $pcolor      = $printer->color_supported ? "YES" : "NO";
    $pduplex     = $printer->duplex_supported ? "YES" : "NO";
    $pfinishings = $printer->finishings_supported ? "YES" : "NO";
    $pipps       = $printer->ipps_supported ? "YES" : "NO";

    if ($LOGIN_IS_ADMIN || $LOGIN_ID == $printer->create_id)
      $pedit = " <a class=\"btn btn-default btn-xs\" href=\"$PHP_SELF?U$id\"><span class=\"glyphicon glyphicon-pencil\"></span></a>";
    else
      $pedit = "";

    if ($purl != "")
      print("<tr><td><a href=\"$purl\" target=\"_blank\">$pmodel</a>$pedit</td><td>$pcolor</td><td>$pduplex</td><td>$pfinishings</td><td>$pipps</td></tr>\n");
    else
      print("<tr><td>$pmodel$pedit<td>$pcolor</td><td>$pduplex</td><td>$pfinishings</td><td>$pipps</td></tr>\n");
  }

  html_end_table();

  print("<hr>\n"
       ."<p>Note: Printers listed on this web page have been tested and submitted by the vendor. The IEEE-ISTO Printer Working Group provides vendors with software that tests printers for general conformance to the IPP Everywhere&trade; standard, but we cannot guarantee that any printer will function perfectly and/or produce the correct output under all circumstances. There is currently no software to certify the conformance of client printing software to the IPP Everywhere&trade; standard.</p>\n");
}

site_footer();

?>
