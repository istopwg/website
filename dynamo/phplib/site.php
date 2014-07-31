<?php
//
// "$Id: site.php 142 2014-04-11 01:18:16Z msweet $"
//
// Main site include file...
//
// This file should be included using "include_once"...
//


//
// Include site configuration...
//

include_once "config/site.cfg";


//
// PHP transition stuff...
//

global $_COOKIE, $_FILES, $_GET, $_POST, $_SERVER;

foreach (array("argc", "argv", "PATH_INFO", "PATH_TRANSLATED", "REQUEST_METHOD", "SERVER_NAME", "SERVER_PORT", "REMOTE_ADDR") as $var)
{
  if (array_key_exists($var, $_SERVER))
    $$var = $_SERVER[$var];
  else
    $$var = "";
}

// Handle PHP_SELF differently - we need to quote it properly...
if (array_key_exists("PHP_SELF", $_SERVER))
  $PHP_SELF = htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES);
else
  $PHP_SELF = "";

if (array_key_exists("ISHTTPS", $_SERVER))
  $PHP_URL = "https://$SERVER_NAME:$SERVER_PORT$PHP_SELF";
else
  $PHP_URL = "http://$SERVER_NAME:$SERVER_PORT$PHP_SELF";

// Figure out the base path...
$html_path = dirname($PHP_SELF);

if (array_key_exists("PATH_INFO", $_SERVER))
{
  // PHP script is prefixed on path...
  $html_path = dirname(dirname(substr($PHP_SELF, 0, -strlen($PATH_INFO))));
}
else
{
  // Determine from the URL...
  $html_path = dirname(dirname($PHP_SELF));
}

if ($html_path != "/")
  $html_path = "$html_path/";

$html_login_url = "https://$_SERVER[SERVER_NAME]$html_path/dynamo/login.php";

// Include necessary headers...
include_once "auth.php";
include_once "html.php";
include_once "validate.php";

// Set the timezone...
date_default_timezone_set($LOGIN_TIMEZONE);

// Load projects...
$PROJECT_NAMES = array("p0" => "Web Site");
$results       = db_query("SELECT id, name FROM project WHERE is_published = 1 "
                         ."ORDER BY name");
while ($row = db_next($results))
{
  $pid  = $row["id"];
  $name = htmlspecialchars($row["name"]);

  $PROJECT_NAMES["p$pid"] = $name;
}
db_free($results);


//
// 'site_header()' - Show the standard page header and navbar.
//

function				// O - User information
site_header($title = "",		// I - Additional document title
	    $subtitle = "",		// I - Subtitle
	    $sidebar = TRUE)		// I - Show sidebar?
{
  global $argc, $argv, $html_path, $_GET, $LOGIN_EMAIL;
  global $LOGIN_IS_ADMIN, $LOGIN_ID, $LOGIN_NAME, $PHP_SELF, $_SERVER;
  global $html_is_phone, $html_is_tablet, $html_login_url;


  $title = htmlspecialchars($title);

  if ($LOGIN_ID != "")
    header("Cache-Control: no-cache");

  header("X-UA-Compatible: IE=9");

  print("<!DOCTYPE html>\n"
       ."<html lang=\"en\">\n"
       ."<head>\n"
       ."<meta charset=\"utf-8\">\n"
       ."<meta name=\"viewport\" content=\"width=device-width, "
       ."initial-scale=1.0\">\n");

  // Title...
  if ($title != "" && $title != "Printer Working Group")
    $html_title = "$title -";
  else
    $html_title = "";

  if ($subtitle != "")
    $html_subtitle = "<br><small>$subtitle</small>";
  else
    $html_subtitle = "";

  if (array_key_exists("Q", $_GET))
    $q = htmlspecialchars($_GET["Q"], ENT_QUOTES);
  else
    $q = "";

  if ($LOGIN_ID != 0)
  {
    $hname     = htmlspecialchars($LOGIN_NAME);
    $userlogin = "<li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-user\"></span> $hname <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}dynamo/account.php\">Profile</a></li>\n"
       ."            <li><a href=\"${html_path}dynamo/logout.php\">Logout</a></li>\n"
       ."          </ul>\n"
       ."        </li>";
  }
  else
    $userlogin = "<li><a href=\"${html_path}dynamo/login.php\"><span class=\"glyphicon glyphicon-user\"></span> Login</a></li>";

  print("<title>$html_title Printer Working Group</title>\n"
       ."<link rel=\"stylesheet\" href=\"https://www.google.com/cse/style/look/default.css\" type=\"text/css\">\n"
       ."<link rel=\"stylesheet\" href=\"${html_path}dynamo/resources/bootstrap-3.2.0.min.css\">\n"
       ."<link rel=\"stylesheet\" href=\"${html_path}dynamo/resources/bootstrap-theme-3.2.0.min.css\">\n"
       ."<link rel=\"stylesheet\" type=\"text/css\" href=\"${html_path}dynamo/resources/pwg.css\">\n"
       ."<link rel=\"alternate\" title=\"Printer Working Group RSS\" "
       ."type=\"application/rss+xml\" href=\"${html_path}rss/index.rss\">\n"
       ."<link rel=\"shortcut icon\" href=\"${html_path}dynamo/resources/pwg.png\" "
       ."type=\"image/png\">\n"
       ."</head>\n"
       ."<body>\n"
       ."<nav class=\"navbar navbar-default\" role=\"navigation\">\n"
       ."  <div class=\"container-fluid\">\n"
       ."    <div class=\"navbar-header\">\n"
       ."      <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#pwg-nav-collapsible\"><span class=\"sr-only\">Toggle navigation</span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span></button>\n"
       ."      <a class=\"navbar-brand\" href=\"{$html_path}\"><img src=\"${html_path}dynamo/resources/pwg.png\" alt=\"PWG Logo\" "
       ."height=\"26\" width=\"25\"></a>\n"
       ."    </div>\n"
       ."    <div class=\"collapse navbar-collapse\" id=\"pwg-nav-collapsible\">\n"
       ."      <ul class=\"nav navbar-nav\">\n"
       ."        $userlogin\n"
       ."        <li><a href=\"${html_path}index.html\">Home</a></li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">About <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}about.html\">About the PWG</a></li>\n"
       ."            <li><a href=\"${html_path}pwg-logos/members.html#JOINING\">Joining</a></li>\n"
       ."            <li><a href=\"${html_path}pwg-logos/members.html\">Members</a></li>\n"
       ."            <li><a href=\"${html_path}chair/index.html\">Officers</a></li>\n"
       ."            <li class=\"divider\"></li>\n"
       ."            <li><a href=\"http://www.google.com/calendar/embed?src=istopwg%40gmail.com\">Calendar</a></li>\n"
       ."            <li><a href=\"${html_path}chair/meeting-info/meetings.html\">Meetings</a></li>\n"
       ."            <li><a href=\"${html_path}chair/participating.html\">Participating</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Publications <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}informational.html\">Informational</a></li>\n"
       ."            <li><a href=\"${html_path}namespaces.html\">Namespaces</a></li>\n"
       ."            <li><a href=\"${html_path}standards.html\">Standards</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Technologies <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}ipp/everywhere.html\">IPP Everywhere<sup>TM</sup></a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Workgroups <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}cloud/\">Cloud Imaging Model</a></li>\n"
       ."            <li><a href=\"${html_path}ids/\">Imaging Device Security</a></li>\n"
       ."            <li><a href=\"${html_path}ipp/\">Internet Printing Protocol</a></li>\n"
       ."            <li><a href=\"${html_path}sm/.html\">Semantic Model</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\" id=\"pwg-toc-button\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">This Page <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\" id=\"pwg-toc-menu\">\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."      </ul>\n"
       ."      <ul class=\"nav navbar-nav navbar-right\">\n"
       ."        <li><div id=\"pwg-search-form\">Google Custom Search</div></li>\n"
       ."      </ul>\n"
       ."    </div>\n"
       ."  </div>\n"
       ."</nav>\n"
       ."<div id=\"pwg-search-results\"></div>\n"
       ."<h1>$title$html_subtitle</h1>\n");

/*

  // Show login/logout link which redirects back to the current page...
  $url    = urlencode($PHP_SELF);
  $prefix = "?";
  for ($i = 0; $i < $argc; $i ++)
  {
    $url    .= $prefix . urlencode($argv[$i]);
    $prefix = "+";
  }

  if (preg_match("/(index|account|accounts|enable|forgot|login|logout|"
                ."newaccount)\\.php\$/", $PHP_SELF))
    $active = " active";
  else
    $active = "";

  if ($LOGIN_NAME)
  {
    $html = str_replace(" ", "&nbsp;", $LOGIN_NAME);
    print("<li class=\"dropdown\">"
         ."<a href=\"#\" class=\"dropdown-toggle$active\" "
         ."data-toggle=\"dropdown\">"
         ."<i class=\"icon-user icon-white\"></i> $html "
         ."<b class=\"caret\"></b></a>"
         ."<ul class=\"dropdown-menu\">\n");
    if ($LOGIN_IS_ADMIN)
      print("<li><a href=\"$html_path/accounts.php\">Manage Accounts</a></li>\n"
           ."<li><a href=\"$html_path/mailman/admin\">Manage Lists</a></li>\n"
           ."<li><a href=\"$html_path/usage\">Server Usage</a></li>\n"
           ."<li class=\"divider\"></li>\n");
    print("<li><a href=\"$html_path/account.php\">Settings</a></li>\n"
         ."<li><a href=\"$html_path/logout.php\">Logout</a></li>\n"
         ."</ul></li>\n");
  }
  else if ($active != "")
  {
    print("<li><a href=\"$html_login_url?PAGE=$url\" class=\"active\">"
         ."<i class=\"icon-user icon-white\"></i> Login</a></li>\n");
  }
  else
  {
    print("<li><a href=\"$html_login_url?PAGE=$url\">"
         ."<i class=\"icon-user icon-white\"></i> Login</a></li>\n");
  }

  if (preg_match("/blog\\.php\$/", $PHP_SELF))
    $blog = " class=\"active\"";
  else
    $blog = "";

  if (preg_match("/photos\\.php\$/", $PHP_SELF))
    $photos = " class=\"active\"";
  else
    $photos = "";

  if (preg_match("/(bugs|documentation|projects|software)\\.php\$/", $PHP_SELF))
    $active = " active";
  else
    $active = "";

  print("<li><a$blog href=\"$html_path/blog.php\">Blog</a></li>\n"
       ."<li><a$photos href=\"photos.php\">Photos</a></li>\n"
       ."<li class=\"dropdown\">"
       ."<a href=\"#\" class=\"dropdown-toggle$active\" "
       ."data-toggle=\"dropdown\">Projects <b class=\"caret\"></b></a>"
       ."<ul class=\"dropdown-menu\">\n");
  if ($LOGIN_IS_ADMIN)
    print("<li><a href=\"$html_path/projects.php?U0\">New Project</a></li>\n"
         ."<li class=\"divider\"></li>\n");
  foreach ($PROJECT_NAMES as $pid => $name)
  {
    $pid = (int)substr($pid, 1);
    if ($pid > 0)
      print("<li><a href=\"$html_path/projects.php?Z$pid\">$name</a></li>\n");
  }
  print("</ul></li>\n"
       ."<li><form class=\"navbar-search visible-phone\" "
       ."method=\"GET\" action=\"$html_path/search.php\">"
       ."<input type=\"search\" name=\"Q\" value=\"$q\" "
       ."placeholder=\"Search Site\" autosave=\"org.msweet.search\" "
       ."results=\"5\"></form></li>\n"
       ."</ul>\n"
       ."<form class=\"navbar-search pull-right hidden-phone\" method=\"GET\" "
       ."action=\"$html_path/search.php\">"
       ."<input class=\"span4\" type=\"search\" name=\"Q\" value=\"$q\" "
       ."placeholder=\"Search Site\" autosave=\"org.msweet.search\" "
       ."results=\"5\"></form>\n"
       ."</div>\n"
       ."</div>\n"
       ."</div>\n"
       ."<div class=\"container-fluid container-top\">\n");
*/
}


//
// 'site_footer()' - Show the standard footer for a page.
//

function
site_footer()
{
  global $html_path, $SITE_EMAIL;


  $year = date("Y");

  print("<div id=\"PWGFooter\">\n"
       ."<div id=\"PWGFooterBody\">Comments are owned by the poster. All other "
       ."material is Copyright &copy; 2001-$year The Printer Working Group. "
       ."All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the "
       ."PWG logo are trademarks of the IEEE-ISTO. Please contact the "
       ."<a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> to report problems "
       ."with this site.</div>\n"
       ."</div>\n"
       ."</div>\n"
       ."<script src=\"${html_path}dynamo/resources/jquery-1.11.1.min.js\"></script>\n"
       ."<script src=\"${html_path}dynamo/resources/bootstrap-3.2.0.min.js\"></script>\n"
       ."<script type=\"text/javascript\" "
       ."src=\"https://www.google.com/jsapi\"></script>\n"
       ."<script type=\"text/javascript\" src=\"${html_path}pwg.js\">"
       ."load_toc('$html_path');</script>\n"
       ."</body>\n"
       ."</html>\n");
}


//
// End of "$Id: site.php 142 2014-04-11 01:18:16Z msweet $".
//
?>
