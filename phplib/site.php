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

include_once "site.cfg";


//
// PHP transition stuff...
//

global $_COOKIE, $_FILES, $_GET, $_POST, $_SERVER;

foreach (array("argc", "argv", "PATH_INFO", "REQUEST_METHOD", "SERVER_NAME",
               "SERVER_PORT", "REMOTE_ADDR") as $var)
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
  $i = -1;
  while (($i = strpos($_SERVER["PATH_INFO"], "/", $i + 1)) !== FALSE)
    $html_path = dirname($html_path);
}

if ($html_path != "/")
  $html_path = "$html_path/";

$html_login_url = "https://$_SERVER[SERVER_NAME]$html_path/login.php";

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
// 'html_header()' - Show the standard page header and navbar.
//

function				// O - User information
html_header($title = "",		// I - Additional document title
	    $subtitle = "",		// I - Subtitle
	    $sidebar = TRUE)		// I - Show sidebar?
{
  global $argc, $argv, $html_path, $_GET, $LOGIN_EMAIL;
  global $LOGIN_IS_ADMIN, $LOGIN_NAME, $PHP_SELF, $_SERVER;
  global $html_is_phone, $html_is_tablet, $html_login_url;


  $title = htmlspecialchars($title);

  if ($LOGIN_EMAIL != "")
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

  print("<title>$html_title Printer Working Group</title>\n"
       ."<link rel=\"stylesheet\" "
       ."href=\"http://www.google.com/cse/style/look/default.css\" "
       ."type=\"text/css\">\n"
       ."<link rel=\"stylesheet\" type=\"text/css\" "
       ."href=\"${html_path}pwg.css\">\n"
       ."<link rel=\"alternate\" title=\"Printer Working Group RSS\" "
       ."type=\"application/rss+xml\" href=\"${html_path}rss/index.rss\">\n"
       ."<link rel=\"shortcut icon\" href=\"${html_path}pwg.png\" "
       ."type=\"image/png\">\n"
       ."<script type=\"text/javascript\" "
       ."src=\"http://www.google.com/jsapi\"></script>\n"
       ."<script type=\"text/javascript\" src=\"${html_path}pwg.js\">"
       ."</script>\n"
       ."</head>\n"
       ."<body onload=\"load_sidebar('$html_path');\">\n"
       ."<div id=\"PWGPage\">\n"
       ."<div id=\"PWGHeader\">\n"
       ."<div id=\"PWGHeaderBody\">\n"
       ."<div id=\"PWGLogo\"><img src=\"${html_path}pwg.png\" alt=\"PWG Logo\" "
       ."height=\"78\" width=\"75\"></div>\n"
       ."<div id=\"PWGSearchForm\">Google Custom Search</div>\n"
       ."<div id=\"PWGTitle\">$title$html_subtitle</div>\n"
       ."</div>\n"
       ."</div>\n"
       ."<div id=\"PWGBody\">\n"
       ."<div id=\"PWGSearchResults\"></div>\n"
       ."<div id=\"PWGSideBar\">\n"
       ."<div id=\"PWGSideBody\">Loading...</div>\n"
       ."</div>\n"
       ."<div id=\"PWGContent\">\n"
       ."<div id=\"PWGContentBody\">\n");

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
// 'html_footer()' - Show the standard footer for a page.
//

function
html_footer()
{
  global $html_path, $SITE_EMAIL;


  $year = date("Y");

  print("</div>\n"
       ."</div>\n"
       ."</div>\n"
       ."<div id=\"PWGFooter\">\n"
       ."<div id=\"PWGFooterBody\">Comments are owned by the poster. All other "
       ."material is Copyright &copy; 2001-$year The Printer Working Group. "
       ."All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the "
       ."PWG logo are trademarks of the IEEE-ISTO. Please contact the "
       ."<a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> to report problems "
       ."with this site.</div>\n"
       ."</div>\n"
       ."</div>\n"
       ."</body>\n"
       ."</html>\n");
}


//
// End of "$Id: site.php 142 2014-04-11 01:18:16Z msweet $".
//
?>
