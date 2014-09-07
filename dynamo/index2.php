<?php
//
// PWG home page.
//

include_once "phplib/site.php";
include_once "phplib/db-article.php";

site_header("");

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
       ."<div class=\"carousel-inner\">\n"
       ."<div class=\"item active\">");
}

print("<h1><img class=\"pwg-logo pwg-logo-right hidden-xs\" src=\"${html_path}dynamo/resources/pwg-medium@2x.png\">The Printer Working Group</h1>\n"
     ."<p>Our members include printer and multi-function device manufacturers, print server developers, operating system providers, print management application developers, and industry experts. We make printers, multi-function devices, and the applications and operating systems supporting them work together better.</p>\n"
     ."<p><a class=\"btn btn-primary btn-lg\" href=\"${html_path}about.html\">More Info</a></p>\n");

if (sizeof($carousel) > 0)
{
  print("</div>\n");

  for ($i = 0; $i < sizeof($carousel); $i ++)
  {
    print("<div class=\"item\">");
    $article = $carousel[$i];
    $article->view("", 1, FALSE, "btn-lg");
    print("</div>\n");
  }

  print("</div></div>\n");
}

print("</div>\n"
     ."<div class=\"row\">\n"
     ."<div class=\"col-md-3 col-sm-6\">\n"
     ."<div class=\"panel panel-default\"><div class=\"panel-heading\">Standards</div>\n"
     ."<div class=\"panel-body\"><p>PWG Standards define all of the common network protocols used by your printer.</p>\n"
     ."<p><a class=\"btn btn-default btn-sm\" href=\"${html_path}standards.html\">More Info</a></p></div></div>\n"
     ."</div>\n"
     ."<div class=\"col-md-3 col-sm-6\">\n"
     ."<div class=\"panel panel-default\"><div class=\"panel-heading\">IPP Everywhere</div>\n"
     ."<div class=\"panel-body\"><p>Print to any network or USB printer without using special software from the manufacturer.</p>\n"
     ."<p><a class=\"btn btn-default btn-sm\" href=\"${html_path}ipp/everywhere.html\">More Info</a>");
if ($SITE_SHOW_BETA)
  print(" <a class=\"btn btn-default btn-sm\" href=\"${html_path}dynamo/eveprinters.php\">Find Printers</a>");
print("</p></div></div>\n"
     ."</div>\n"
     ."<div class=\"col-md-3 col-sm-6\">\n"
     ."<div class=\"panel panel-default\"><div class=\"panel-heading\">PWG Semantic Model</div>\n"
     ."<div class=\"panel-body\"><p>Support multiple protocols and job ticket formats using our abstract model.</p>\n"
     ."<p><a class=\"btn btn-default btn-sm\" href=\"${html_path}sm/index.html\">More Info</a></p></div></div>\n"
     ."</div>\n"
     ."<div class=\"col-md-3 col-sm-6\">\n"
     ."<div class=\"panel panel-default\"><div class=\"panel-heading\">SNMP MIBs</div>\n"
     ."<div class=\"panel-body\"><p>Monitor jobs, status, and supplies, and manage your printers remotely.</p>\n"
     ."<p><a class=\"btn btn-default btn-sm\" href=\"${html_path}wims/index.html\">More Info</a></p></div>\n"
     ."</div>\n"
     ."</div>\n"
     ."</div>\n"
     ."<div class=\"row\">\n"
     ."<div class=\"col-md-12\">\n"
     ."<div class=\"panel panel-default\"><div class=\"panel-heading\">PWG News</div>\n"
     ."<div class=\"panel-body\">\n");

for ($i = 0, $count = 0; $i < sizeof($matches) && $count < 5; $i ++)
{
  $article = new article($matches[$i]);

  if ($article->display_until_date != "")
    continue;

  if ($article->id == $matches[$i])
  {
    $count ++;
    $article->view("", 3, FALSE);
  }
}

print("<p><a class=\"btn btn-default btn-xs\" href=\"${html_path}/dynamo/articles.php\">View Older Articles</a></p>\n"
     ."</div></div>\n"
     ."</div></div>\n");

if (sizeof($carousel) > 0)
  site_footer("pwg-rolling.js");
else
  site_footer();

?>
