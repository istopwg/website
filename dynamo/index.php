<?php
//
// PWG home page.
//

include_once "phplib/site.php";
include_once "phplib/db-article.php";

site_header("Printer Working Group", "Making printers, multi-function devices, and software better with public standards.");

$matches  = article_search();
$carousel = array();

for ($i = 0, $count = 0; $i < sizeof($matches); $i ++)
{
  $article = new article($matches[$i]);

  if ($article->id !== $matches[$i] || $article->display_until_date == "" || $article->display_until_date < date("Y-m-d"))
    continue;

  $count ++;
  $carousel["D$article->display_until_date+A$article->id"] = $article;
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

  if ($article->display_until_date != "")
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

?>
