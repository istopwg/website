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

include_once "phplib/site.php";

// Get the contents and metadata from the base HTML file...
$title    = htmlspecialchars(basename($PATH_INFO, ".html"), ENT_QUOTES);
$content  = "";
$subtitle = "";
$css      = "";

if ($PATH_INFO == "${SITE_DOCROOT}index.html")
{
  include_once "index.php";
  exit(0);
}
else if ($PATH_INFO == "${SITE_DOCROOT}standards.html")
{
  include_once "standards.php";
  exit(0);
}
else if (file_exists($PATH_TRANSLATED))
{
  $contents = file_get_contents($PATH_TRANSLATED);

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
    $title = trim(str_replace("- Printer Working Group", "", substr($contents, $start + 7, $end - $start - 7)));
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
  // File does not exist, show a standard error page.
  $title    = "Not Found";
  $contents = "The file you requested cannot be found.";
}

// Wrap the contents of the HTML file with the standard header/footer for the site.
site_header($title, $subtitle);
print("$contents\n");
site_footer();

?>
