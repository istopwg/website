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
  include_once "phplib/db-article.php";

  site_header("Printer Working Group", "Making printers, multi-function devices, and software better with public standards.");

  $matches  = article_search();
  $carousel = array();

  for ($i = 0, $count = 0; $i < sizeof($matches); $i ++)
  {
    $article = new article($matches[$i]);

    if ($article->id !== $matches[$i] || $article->display_until == "" || $article->display_until < date("Y-m-d"))
      continue;

    $count ++;
    $carousel["D$article->display_until+A$article->id"] = $article;
  }

  ksort($carousel);
  $carousel = array_values($carousel);

  print("<div class=\"jumbotron\">\n");
  if (sizeof($carousel) > 0)
  {
    print("<div id=\"pwg-rolling\" class=\"carousel slide\" data-ride=\"carousel\">\n"
	 ."<ol class=\"carousel-indicators\">\n"
	 ."<li class=\"active\" data-target=\"#pwg-rolling\" data-slide-to=\"$i\"></li>\n");
    for ($i = 1; $i <= sizeof($carousel); $i ++)
      print("<li data-target=\"#pwg-rolling\" data-slide-to=\"$i\"></li>\n");
    print("</ol>\n"
	 ."<div class=\"carousel-inner\">\n");
    for ($i = 0; $i < sizeof($carousel); $i ++)
    {
      if ($i == 0)
	print("<div class=\"item active\">");
      else
	print("<div class=\"item\">");

      $article = $carousel[$i];
      $article->view("", 1, FALSE, "btn-lg");
      print("</div>\n");
    }

    print("<div class=\"item\">");
  }

  print("<h1>The Printer Working Group</h1>\n"
       ."<p>Our members include printer and multi-function device manufacturers, print server developers, operating system providers, print management application developers, and industry experts. We make printers, multi-function devices, and the applications and operating systems supporting them work together better.</p>\n"
       ."<p><a class=\"btn btn-primary btn-lg\" href=\"${html_path}about.html\">More Info</a></p>\n");

  if (sizeof($carousel) > 0)
    print("</div>\n"
         ."</div>\n"
	 ."</div>\n"
	 ."</div>\n");
  else
    print("</div>\n");

  print("<h1>PWG News</h1>\n");

  for ($i = 0, $count = 0; $i < sizeof($matches) && $count < 5; $i ++)
  {
    $article = new article($matches[$i]);

    if ($article->display_until != "")
      continue;

    if ($article->id == $matches[$i])
    {
      $count ++;
      $article->view("", 2, FALSE);
    }
  }

  print("<p><a class=\"btn btn-default btn-xs\" href=\"${html_path}/dynamo/articles.php\">View Older Articles</a></p>\n");

  if (sizeof($carousel) > 0)
    site_footer("pwg-rolling.js");
  else
    site_footer();

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
