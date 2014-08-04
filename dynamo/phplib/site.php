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

$html_login_url = "https://$SERVER_NAME${html_path}dynamo/login.php";

// Include necessary headers...
include_once "auth.php";
include_once "html.php";
include_once "validate.php";

// Set the timezone...
date_default_timezone_set($LOGIN_TIMEZONE);


//
// 'site_header()' - Show the standard page header and navbar.
//

function				// O - User information
site_header($title = "",		// I - Additional document title
	    $subtitle = "&nbsp;",	// I - Subtitle
	    $sidebar = TRUE)		// I - Show sidebar?
{
  global $argc, $argv, $html_path, $_GET, $LOGIN_EMAIL;
  global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_MEMBER, $LOGIN_IS_OFFICER, $LOGIN_IS_REVIEWER, $LOGIN_IS_SUBMITTER, $LOGIN_NAME, $PHP_SELF, $_SERVER, $SERVER_NAME;
  global $html_is_phone, $html_is_tablet, $html_login_url;


  $title = str_replace(array("(tm)", "(r)"), array("<sup>TM</sup>", "&reg;"), htmlspecialchars($title));

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

  if ($subtitle == "")
    $subtitle = "&nbsp;";

  if (array_key_exists("Q", $_GET))
    $q = htmlspecialchars($_GET["Q"], ENT_QUOTES);
  else
    $q = "";

  if ($LOGIN_ID != 0)
  {
    $hname     = htmlspecialchars($LOGIN_NAME);
    $userlogin = "<li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><span class=\"glyphicon glyphicon-user\"></span> $hname <span class=\"caret\"></span></a>\n"
		."          <ul class=\"dropdown-menu\" role=\"menu\">\n";

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/articles.php\">Manage Articles</a></li>\n";

    if ($LOGIN_IS_ADMIN)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/organizations.php\">Manage Organizations</a></li>\n"
                   ."            <li><a href=\"${html_path}dynamo/accounts.php\">Manage Users</a></li>\n"
                   ."            <li><a href=\"${html_path}dynamo/workgroups.php\">Manage Workgroups</a></li>\n";

    if ($LOGIN_IS_EDITOR)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/issues.php\">Review Issues</a></li>\n";

    if ($LOGIN_IS_REVIEWER)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/evereview.php\">Review Self-Certifications</a></li>\n";

    if ($LOGIN_IS_SUBMITTER)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/evesubmit.php\">Submit Self-Certification</a></li>\n";

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_SUBMITTER || $LOGIN_IS_REVIEWER)
      $userlogin .= "            <li class=\"divider\"></li>\n";

    $userlogin .= "            <li><a href=\"${html_path}dynamo/account.php\">Profile</a></li>\n"
		 ."            <li><a href=\"${html_path}dynamo/logout.php\">Logout</a></li>\n"
		 ."          </ul>\n"
		 ."        </li>";
  }
  else
  {
    // Show login/logout link which redirects back to the current page...
    $url    = urlencode($PHP_SELF);
    $prefix = "?";
    for ($i = 0; $i < $argc; $i ++)
    {
      $url    .= $prefix . urlencode($argv[$i]);
      $prefix = "+";
    }

    $userlogin = "<li><a href=\"$html_login_url?PAGE=$url\"><span class=\"glyphicon glyphicon-user\"></span> Login</a></li>";
  }

  print("<title>$html_title Printer Working Group</title>\n"
       ."<link rel=\"stylesheet\" href=\"//www.google.com/cse/style/look/default.css\" type=\"text/css\">\n"
       ."<link rel=\"stylesheet\" href=\"${html_path}dynamo/resources/bootstrap-3.2.0.min.css\">\n"
       ."<link rel=\"stylesheet\" href=\"${html_path}dynamo/resources/bootstrap-theme-3.2.0.min.css\">\n"
       ."<link rel=\"stylesheet\" type=\"text/css\" href=\"${html_path}dynamo/resources/pwg.css\">\n"
       ."<link rel=\"alternate\" title=\"Printer Working Group RSS\" "
       ."type=\"application/rss+xml\" href=\"${html_path}rss/index.rss\">\n"
       ."<link rel=\"shortcut icon\" href=\"${html_path}dynamo/resources/pwg.png\" "
       ."type=\"image/png\">\n"
       ."</head>\n"
       ."<body onLoad=\"load_toc('$html_path');\">\n"
       ."<nav class=\"navbar navbar-inverse pwg-navbar\" role=\"navigation\">\n"
       ."  <div class=\"container-fluid\">\n"
       ."    <div class=\"navbar-header\">\n"
       ."      <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#pwg-nav-collapsible\"><span class=\"sr-only\">Toggle navigation</span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span></button>\n"
       ."      <a class=\"navbar-brand\" href=\"{$html_path}\"><img src=\"${html_path}dynamo/resources/pwg.png\" alt=\"PWG Logo\" "
       ."height=\"78\" width=\"75\"></a>\n"
       ."    </div>\n");
  if (!$html_is_phone)
    print("    <div id=\"pwg-search-form\">Google Custom Search</div>\n");
  print("    <div class=\"pwg-navbar-title\">$title<br><p>$subtitle</p></div>\n"
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
       ."      </ul>\n"
       ."    </div>\n"
       ."  </div>\n"
       ."</nav>\n"
       ."<div id=\"pwg-body\">\n"
       ."  <div class=\"dropdown\" id=\"pwg-toc-button\" style=\"display: none;\">\n"
       ."    <button type=\"button\" class=\"btn btn-default btn-sm dropdown-toggle\" data-toggle=\"dropdown\">Jump To... <span class=\"caret\"></span></button>\n"
       ."    <ul class=\"dropdown-menu\" role=\"menu\" id=\"pwg-toc-menu\">\n"
       ."    </ul>\n"
       ."  </div>\n");
  if (!$html_is_phone)
    print("  <div id=\"pwg-search-results\"></div>\n");
  print("  <div id=\"pwg-content\">\n");
}


//
// 'site_footer()' - Show the standard footer for a page.
//

function
site_footer($javascript = "")
{
  global $html_is_phone, $html_path, $SITE_EMAIL;


  $year = date("Y");

  print("  </div>\n"
       ."</div>\n"
       ."<div id=\"pwg-footer\">\n"
       ."  <div id=\"pwg-footer-body\">Comments are owned by the poster. All other "
       ."material is Copyright &copy; 2001-$year The Printer Working Group. "
       ."All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the "
       ."PWG logo are trademarks of the IEEE-ISTO. Please contact the "
       ."<a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> to report problems "
       ."with this site.</div>\n"
       ."</div>\n"
       ."<script src=\"${html_path}dynamo/resources/jquery-1.11.1.min.js\"></script>\n"
       ."<script src=\"${html_path}dynamo/resources/bootstrap-3.2.0.min.js\"></script>\n");
  if (!$html_is_phone)
    print("<script type=\"text/javascript\" src=\"//www.google.com/jsapi\"></script>\n"
         ."<script type=\"text/javascript\" src=\"${html_path}dynamo/resources/pwg-search.js\"></script>\n");
  print("<script type=\"text/javascript\" src=\"${html_path}dynamo/resources/pwg.js\">"
       ."</script>\n");
  if ($javascript != "")
    print("<script>$javascript</script>\n");
  print("</body>\n"
       ."</html>\n");
}


//
// End of "$Id: site.php 142 2014-04-11 01:18:16Z msweet $".
//
?>
