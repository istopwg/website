<?php
//
// PWG home page.
//

include_once "phplib/site.php";
include_once "phplib/db-article.php";

site_header("");

$matches = article_search();


?>

<div class="jumbotron">
  <h1><img class="pwg-logo pwg-right hidden-xs" src="<?print($html_path);?>dynamo/resources/pwg-medium@2x.png">The Printer Working Group</h1>
  <p>Our members include printer and multi-function device manufacturers, print server developers, operating system providers, print management application developers, and industry experts. We make printers, multi-function devices, and the applications and operating systems supporting them work together better.</p>
  <p><a class="btn btn-primary btn-lg" href="<?print($html_path);?>about.html">More Info</a>
  &nbsp;<a class="btn btn-default btn-lg" href="#NEWS">Recent News</a></p>
</div>

<?

$firsttime = TRUE;
$today     = date("Y-m-d");

for ($i = 0; $i < sizeof($matches); $i ++)
{
  $article = new article($matches[$i]);

  if ($article->display_until_date == "" || $article->display_until_date < $today || $article->id != $matches[$i])
    continue;

  if ($firsttime)
  {
    print("<div class=\"panel-group\" id=\"pwg-display-until\">\n");
    $firsttime = FALSE;
  }

  $title    = htmlspecialchars($article->title);
  $contents = html_format($article->contents);
  $url      = htmlspecialchars($article->url, ENT_QUOTES);

  print("<div class=\"panel panel-default pwg-alert-panel\">\n"
       ."  <div class=\"panel-heading\"><a class=\"pwg-right\" href=\"#a$article->id\" data-toggle=\"collapse\" data-parent=\"#pwg-display-until\"><span class=\"glyphicon glyphicon-chevron-down\"></span></a><a href=\"#a$article->id\" data-toggle=\"collapse\" data-parent=\"#pwg-display-until\">$title</a></div>\n"
       ."  <div id=\"a$article->id\" class=\"panel-collapse collapse\">\n"
       ."    <div class=\"panel-body\">$contents\n"
       ."      <p><a class=\"btn btn-default btn-sm\" href=\"$url\">More Info</a></p>\n"
       ."    </div>\n"
       ."  </div>\n"
       ."</div>\n");
}

if (!$firsttime)
  print("</div>\n");

?>
<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">3D Printing</div>
      <div class="panel-body">
        <p>The PWG is defining an IPP extension for 3D printing using standard file formats.</p>
        <p><a class="btn btn-default btn-sm" href="<?print($html_path);?>3d">More Info</a></p>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">IPP Everywhere</div>
      <div class="panel-body">
	<p>Print to any network or USB printer without using special software from the manufacturer.</p>
	<p><a class="btn btn-default btn-sm" href="<?print($html_path);?>ipp/everywhere.html">More Info</a>
	&nbsp;<a class="btn btn-default btn-sm" href="<?print($html_path);?>dynamo/eveprinters.php">Find Printers</a></p>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">PWG Semantic Model</div>
      <div class="panel-body">
        <p>Support multiple network protocols and job ticket formats using our abstract model.</p>
        <p><a class="btn btn-default btn-sm" href="<?print($html_path);?>sm/index.html">More Info</a></p>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">Standards</div>
      <div class="panel-body">
        <p>PWG Standards define all of the common network protocols used by your printer.</p>
        <p><a class="btn btn-default btn-sm" href="<?print($html_path);?>standards.html">More Info</a></p>
      </div>
    </div>
  </div>
<!--  <div class="col-md-3 col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading">SNMP MIBs</div>
      <div class="panel-body">
        <p>Monitor jobs, status, and supplies, and manage your printers remotely using SNMP.</p>
        <p><a class="btn btn-default btn-sm" href="<?print($html_path);?>wims/index.html">More Info</a></p>
      </div>
    </div>
  </div>-->
</div>
<div class="row">
<div class="col-md-12">
  <div class="panel panel-default"><div class="panel-heading"><a name="NEWS">PWG News</a></div>
  <div class="panel-body">
<?

for ($i = 0, $count = 0; $i < sizeof($matches) && $count < 5; $i ++)
{
  $article = new article($matches[$i]);

  if (($article->display_until_date != "" && $article->display_until_date >= $today) || $article->id != $matches[$i])
    continue;

  $count ++;
  $article->view("", 3, FALSE);
}

print("<p><a class=\"btn btn-default btn-xs\" href=\"${html_path}dynamo/articles.php\">View Older Articles</a></p>\n"
     ."</div></div>\n"
     ."</div></div>\n");

site_footer();

?>
