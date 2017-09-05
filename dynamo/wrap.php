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

if (strpos($PATH_INFO, "..") !== FALSE || !file_exists($PATH_TRANSLATED))
{
  header("Status: 404");
  print("<h1>File Not Found</h1>\n");
  print("<p>The file \"" . htmlspecialchars($PATH_INFO) . "\" was not found.</p>\n");
  exit();
}

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
else
{
  // Tag image files appropriately...
  if (preg_match("/\\.gif\$/", $PATH_TRANSLATED))
  {
    header("Content-Type: image/gif");
    readfile($PATH_TRANSLATED);
    exit(0);
  }
  else if (preg_match("/\\.(jpg,jpeg)\$/", $PATH_TRANSLATED))
  {
    header("Content-Type: image/jpeg");
    readfile($PATH_TRANSLATED);
    exit(0);
  }
  else if (preg_match("/\\.pdf\$/", $PATH_TRANSLATED))
  {
    header("Content-Type: application/pdf");
    readfile($PATH_TRANSLATED);
    exit(0);
  }
  else if (preg_match("/\\.png\$/", $PATH_TRANSLATED))
  {
    header("Content-Type: image/png");
    readfile($PATH_TRANSLATED);
    exit(0);
  }
  else if (preg_match("/\\/schemas\\/.*\\.html\$/", $PATH_TRANSLATED))
  {
    header("Content-Type: text/html");
    readfile($PATH_TRANSLATED);
    exit(0);
  }

  // Otherwise assume HTML...
  $contents = file_get_contents($PATH_TRANSLATED);

  if (($start = stripos($contents, "<style type=\"text/css\">")) !== FALSE)
  {
    $end = stripos($contents, "</style>", $start);
    $css = substr($contents, $start, $end - $start + 8);
  }
  else if (($start = stripos($contents, "<style>")) !== FALSE)
  {
    $end = stripos($contents, "</style>", $start);
    $css = substr($contents, $start, $end - $start + 8);
  }

  if (($start = stripos($contents, "<title>")) !== FALSE)
  {
    $end   = stripos($contents, "</title>", $start);
    $title = trim(str_replace("- Printer Working Group", "", substr($contents, $start + 7, $end - $start - 7)));
  }

  if (($start = strpos($contents, "<!--subtitle ")) !== FALSE)
  {
    $end      = strpos($contents, " -->", $start);
    $subtitle = trim(substr($contents, $start + 13, $end - $start - 13));
  }

  if (($start = stripos($contents, "<div id=\"PWGContentBody\">")) !== FALSE)
  {
    $end = stripos($contents, "<div id=\"PWGFooter\">", $start);
    $end = stripos($contents, "</div>", $end - 45);
    $contents = substr($contents, $start + 25, $end - $start - 25);
  }
  else if (($start = stripos($contents, "<body>")) !== FALSE)
  {
    $end = stripos($contents, "</body>", $start);
    $contents = substr($contents, $start + 6, $end - $start - 6);
  }

  // Wrap the contents of the HTML file with the standard header/footer for the site.
  site_header($title, $subtitle);
  print("$contents\n");
  site_footer();
}

?>
