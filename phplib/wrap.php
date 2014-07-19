<?php
//
// This file applies a standard HTML wrapper around content pages.
//
// Content pages are read up to the <body> line, extracting any <title> text
// from the <head> section.  A standard HTML "header" is then written using
// the page title.  The default title is the filename without the trailing
// extension.
//
// The <body> content is copied to the browser, followed by the standard HTML
// "footer".
//

global $_SERVER;

date_default_timezone_set("America/New_York");

if (array_key_exists("PATH_TRANSLATED", $_SERVER))
  $translated = $_SERVER["PATH_TRANSLATED"];
else
  $translated = "";

if (array_key_exists("PATH_INFO", $_SERVER))
  $info = $_SERVER["PATH_INFO"];
else
  $info = "";

if (array_key_exists("PHP_SELF", $_SERVER))
{
  $path = dirname(dirname(substr($_SERVER["PHP_SELF"], 0, -strlen($info)))) . "/";
  if ($path == "//")
    $path = "/";
}
else
  $path = "";

$title    = htmlspecialchars(basename($info, ".html"), ENT_QUOTES);
$content  = "";
$subtitle = "";
$css      = "";

if (file_exists($translated))
{
  $contents = file_get_contents($translated);

  if (($start = strpos($contents, "<style type=\"text/css\">")) !== FALSE)
  {
    $end = strpos($contents, "</style>", $start);
    $css = substr($contents, $start, $end - $start + 8);
  }
  else if (($start = strpos($contents, "<style>")) !== FALSE)
  {
    $end = strpos($contents, "</style>", $start);
    $css = substr($contents, $start, $end - $start + 8);
  }

  if (($start = strpos($contents, "<title>")) !== FALSE)
  {
    $end   = strpos($contents, "</title>", $start);
    $title = trim(substr($contents, $start + 7, $end - $start - 7));
  }

  if (($start = strpos($contents, "<!--subtitle ")) !== FALSE)
  {
    $end      = strpos($contents, " -->", $start);
    $subtitle = trim(substr($contents, $start + 13, $end - $start - 13));
  }

  if (($start = strpos($contents, "<div id=\"PWGContentBody\">")) !== FALSE)
  {
    $end = strpos($contents, "<div id=\"PWGFooter\">", $start);
    $end = strpos($contents, "</div>", $end - 45);
    $contents = substr($contents, $start + 25, $end - $start - 25);
  }
  else if (($start = strpos($contents, "<body>")) !== FALSE)
  {
    $end = strpos($contents, "</body>", $start);
    $contents = substr($contents, $start + 6, $end - $start - 6);
  }
}
else
{
  $title    = "Not Found";
  $contents = "The file you requested cannot be found.";
}

if ($subtitle != "")
  $htitle = "$title<br><small>$subtitle</small>";
else
  $htitle = $title;

if (strpos($title, "Printer Working Group") === FALSE)
  $ptitle = "$title - Printer Working Group";
else
  $ptitle = $title;

print("<!DOCTYPE html>\n"
     ."<html>\n"
     ."  <head>\n"
     ."    <!-- path=\"$path\" -->\n"
     ."    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=9\">\n"
     ."    <title>$ptitle</title>\n"
     ."    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n"
     ."    <meta name=\"viewport\" content=\"width=device-width\">\n"
     ."    <link rel=\"stylesheet\" href=\"http://www.google.com/cse/style/look/default.css\" type=\"text/css\">\n"
     ."    <link rel=\"stylesheet\" type=\"text/css\" href=\"${path}pwg.css\">\n"
     ."    $css\n"
     ."    <link rel=\"shortcut icon\" href=\"${path}pwg.png\" type=\"image/png\">\n"
     ."    <script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>\n"
     ."    <script type=\"text/javascript\" src=\"${path}pwg.js\"></script>\n"
     ."  </head>\n"
     ."  <body onload=\"load_sidebar('$path');\">\n"
     ."    <div id=\"PWGPage\">\n"
     ."      <div id=\"PWGHeader\">\n"
     ."        <div id=\"PWGHeaderBody\">\n"
     ."          <div id=\"PWGLogo\"><img src=\"${path}pwg.png\" alt=\"PWG Logo\" height=\"78\" width=\"75\"></div>\n"
     ."          <div id=\"PWGSearchForm\">Google Custom Search</div>\n"
     ."          <div id=\"PWGTitle\">$htitle</div>\n"
     ."        </div>\n"
     ."      </div>\n"
     ."      <div id=\"PWGBody\">\n"
     ."        <div id=\"PWGSearchResults\"></div>\n"
     ."        <div id=\"PWGSideBar\">\n"
     ."          <div id=\"PWGSideBody\">Loading...</div>\n"
     ."        </div>\n"
     ."        <div id=\"PWGContent\">\n"
     ."          <div id=\"PWGContentBody\">\n"
     ."$contents\n"
     ."          </div>\n"
     ."        </div>\n"
     ."      </div>\n"
     ."      <div id=\"PWGFooter\">\n"
     ."        <div id=\"PWGFooterBody\">Comments are owned by the poster. All other material is Copyright &copy; 2001-" . date("Y") . ". The Printer Working Group. All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the PWG logo are trademarks of the IEEE-ISTO. Please contact the <a href=\"mailto:webmaster@pwg.org\">PWG Webmaster</a> to report problems with this site.</div>\n"
     ."      </div>\n"
     ."    </div>\n"
     ."  </body>\n"
     ."</html>\n");
?>
