<?php
//
// "$Id: rss.php 38 2013-04-08 04:03:27Z msweet $"
//
// This file exports the 10 most recent articles to an RDF file
// that sites and news ticker apps can grab to show the latest news.
//
// Contents:
//
//   make_rss_file() - Create an RSS file from the current articles table.
//

include_once "db-article.php";


//
// 'make_rss_file()' - Create an RSS file from the current articles table.
//

function
make_rss_file($file,			// I - File to create
              $baseurl,			// I - Base URL
	      $title,			// I - Title of site
	      $description)		// I - Description of site
{
  // Create the RDF file...
  $fp = fopen($file, "w");
  if (!$fp) return;

  // Get a list of articles that are not FAQ's...
  $matches = article_search("", "-modify_date", 0, 1);
  $count   = sizeof($matches);
  if ($count > 20)
    $count = 20;

  // XML header...
  fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
             ."<rdf:RDF\n"
	     ." xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n"
	     ." xmlns=\"http://purl.org/rss/1.0/\"\n"
	     ." xmlns:dc=\"http://purl.org/dc/elements/1.1/\"\n"
	     ." xmlns:syn=\"http://purl.org/rss/1.0/modules/syndication/\"\n"
	     .">\n");

  // Description of channel...
  $secs = time();
  $date = gmdate("Y-m-d", $secs);
  $time = gmdate("H:i:s", $secs);
  fwrite($fp, "<channel rdf:about=\"${baseurl}index.rss\">\n"
	     ."  <title>$title</title>\n"
	     ."  <link>$baseurl</link>\n"
	     ."  <description>$description</description>\n"
	     ."  <dc:language>en-us</dc:language>\n"
	     ."  <dc:date>${date}T$time+00:00</dc:date>\n"
	     ."  <dc:publisher>msweet.org</dc:publisher>\n"
	     ."  <dc:creator>webmaster@msweet.org</dc:creator>\n"
	     ."  <dc:subject>Technology</dc:subject>\n"
	     ."  <syn:updatePeriod>hourly</syn:updatePeriod>\n"
	     ."  <syn:updateFrequency>1</syn:updateFrequency>\n"
	     ."  <syn:updateBase>1970-01-01T00:00+00:00</syn:updateBase>\n"
	     ."  <items>\n"
	     ."    <rdf:Seq>\n");

  // Item index...
  for ($i = 0; $i < $count; $i ++)
    fwrite($fp, "      <rdf:li rdf:resource=\"${baseurl}blog.php?L$matches[$i]\" />\n");

  fwrite($fp, "    </rdf:Seq>\n"
             ."  </items>\n"
	     ."  <image rdf:resource=\"${baseurl}images/mike.png\" />\n"
	     ."  <textinput rdf:resource=\"${baseurl}search.php\" />\n"
	     ."</channel>\n"
	     ."<image rdf:about=\"${baseurl}images/mike.png\">\n"
	     ."  <title>$title</title>\n"
	     ."  <url>${baseurl}images/mike.png</url>\n"
	     ."  <link>$baseurl</link>\n"
	     ."</image>\n");

  // Now the news items...
  for ($i = 0; $i < $count; $i ++)
  {
    $article = new article($matches[$i]);

    $headline    = htmlspecialchars($article->title, ENT_QUOTES, "UTF-8");
    $description = htmlspecialchars($article->summary, ENT_QUOTES, "UTF-8");
    $seconds     = db_seconds($article->modify_date);
    $date        = gmdate("Y-m-d", $seconds);
    $time        = gmdate("H:i:s", $seconds);
    $creator     = user_name($article->create_id);

    fwrite($fp, "<item rdf:about=\"${baseurl}blog.php?L$article->id\">\n"
	       ."  <title>$headline</title>\n"
	       ."  <link>${baseurl}blog.php?L$article->id</link>\n"
	       ."  <description>$description</description>\n"
	       ."  <dc:creator>$creator</dc:creator>\n"
	       ."  <dc:subject>$headline</dc:subject>\n"
	       ."  <dc:date>${date}T$time+00:00</dc:date>\n"
	       ."</item>\n");
  }

  // Finally a search link and close the file...
  fwrite($fp, "<textinput rdf:about=\"${baseurl}search.php\">\n"
	     ."<title>Search</title>\n"
	     ."<description>Search Site</description>\n"
	     ."<name>Q</name>\n"
	     ."<link>${baseurl}search.php</link>\n"
	     ."</textinput>\n");

  fwrite($fp, "</rdf:RDF>\n");
  fclose($fp);
}

?>
