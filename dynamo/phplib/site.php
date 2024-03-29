<?php
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

if ($SERVER_NAME == "pwg.org")
  $html_login_url = "https://www.pwg.org${html_path}dynamo/login.php";
else
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
  global $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_EDITOR, $LOGIN_IS_MEMBER, $LOGIN_IS_OFFICER, $LOGIN_NAME, $PHP_SELF, $_SERVER, $SERVER_NAME, $SITE_SHOW_BETA;
  global $html_login_url;


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
  {
    if ($title[0] == "-")
      $html_title = str_replace(array("(tm)", "(r)"), array("&trade;", "&reg;"), htmlspecialchars(substr($title, 1) . " -"));
    else
      $html_title = str_replace(array("(tm)", "(r)"), array("&trade;", "&reg;"), htmlspecialchars("$title -"));
  }
  else
    $html_title = "";

  $title = str_replace(array("(tm)", "(r)"), array("&trade;", "&reg;"), htmlspecialchars($title));

  if ($subtitle != "")
    $subtitle = "<br><small>$subtitle</small>";

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

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $LOGIN_IS_EDITOR)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/documents.php\">Manage Documents</a></li>\n";

    if ($LOGIN_IS_ADMIN)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/organizations.php\">Manage Organizations</a></li>\n"
                   ."            <li><a href=\"${html_path}dynamo/accounts.php\">Manage Users</a></li>\n"
                   ."            <li><a href=\"${html_path}dynamo/workgroups.php\">Manage Workgroups</a></li>\n";

    if ($LOGIN_IS_EDITOR)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/issues.php\">Review Issues</a></li>\n";

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_MEMBER)
      $userlogin .= "            <li><a href=\"${html_path}dynamo/evesubmit.php\">Submit Self-Certification</a></li>\n";

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER)
    {
      $userlogin .= "            <li><a href=\"https://www.pwg.org/awstats/awstats.pl\">View HTTP Usage</a></li>\n";
//      $userlogin .= "            <li><a href=\"${html_path}ftp-usage\">View FTP Usage</a></li>\n";
    }

    if ($LOGIN_IS_ADMIN || $LOGIN_IS_EDITOR || $LOGIN_IS_OFFICER || $LOGIN_IS_MEMBER)
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
       ."<link rel=\"stylesheet\" type=\"text/css\" href=\"https://www.google.com/cse/style/look/default.css\" type=\"text/css\">\n"
       ."<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\" integrity=\"sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u\" crossorigin=\"anonymous\">\n"
       ."<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css\" integrity=\"sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp\" crossorigin=\"anonymous\">\n"
       ."<link rel=\"stylesheet\" type=\"text/css\" href=\"${html_path}dynamo/resources/pwg.css\">\n"
       ."<link rel=\"shortcut icon\" href=\"${html_path}dynamo/resources/pwg@2x.png\" "
       ."type=\"image/png\">\n"
       ."</head>\n");

  if ($sidebar)
    print("<body data-spy=\"scroll\" data-target=\"#pwg-toc\">\n");
  else
    print("<body>\n");

  print("<nav class=\"navbar navbar-inverse navbar-fixed-top pwg-navbar\" role=\"navigation\">\n"
       ."  <div class=\"container-fluid\">\n"
       ."    <div class=\"navbar-header\">\n"
       ."      <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#pwg-nav-collapsible\"><span class=\"sr-only\">Toggle navigation</span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span><span class=\"icon-bar\"></span></button>\n"
       ."      <a class=\"navbar-brand\" href=\"{$html_path}\"><img src=\"${html_path}dynamo/resources/pwg-4dark.png\" alt=\"PWG Logo\" "
       ."height=\"27\" width=\"28\"></a>\n"
       ."    </div>\n"
       ."    <div class=\"collapse navbar-collapse\" id=\"pwg-nav-collapsible\">\n"
       ."      <ul class=\"nav navbar-nav\">\n"
       ."        $userlogin\n"
       ."        <li><a href=\"${html_path}index.html\">Home</a></li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">About <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li><a href=\"${html_path}about.html\">About the PWG</a></li>\n"
       ."            <li><a href=\"${html_path}members.html#JOINING\">Joining</a></li>\n"
       ."            <li><a href=\"${html_path}members.html\">Members</a></li>\n"
       ."            <li><a href=\"${html_path}chair/index.html\">Officers</a></li>\n"
       ."            <li class=\"divider\"></li>\n"
       ."            <li><a href=\"${html_path}bofs.html\">BOF Sessions</a></li>\n"
       ."            <li><a href=\"${html_path}mailhelp.html\">Mailing Lists</a></li>\n"
       ."            <li><a href=\"${html_path}chair/meeting-info/meetings.html\">Meetings</a></li>\n"
       ."            <li><a href=\"${html_path}chair/participating.html\">Participating</a></li>\n"
       ."            <li><a href=\"https://ieee-isto.org/privacy-policy/\">Privacy Policy</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Our Work <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li class=\"dropdown-header\" role=\"presentation\">Publications</li>\n"
       ."            <li><a href=\"${html_path}informational.html\">Informational Documents</a></li>\n"
       ."            <li><a href=\"${html_path}namespaces.html\">Namespaces</a></li>\n"
       ."            <li><a href=\"${html_path}standards.html\">Standards</a></li>\n"
       ."            <li class=\"divider\"></li>\n"
       ."            <li class=\"dropdown-header\" role=\"presentation\">Technologies</li>\n"
       ."            <li><a href=\"${html_path}3d/index.html\">3D Printing</a></li>\n"
       ."            <li><a href=\"${html_path}ipp/everywhere.html\">IPP Everywhere&trade;</a></li>\n"
       ."            <li><a href=\"${html_path}sm/model.html\">PWG Semantic Model</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">Workgroups <span class=\"caret\"></span></a>\n"
       ."          <ul class=\"dropdown-menu\" role=\"menu\">\n"
       ."            <li class=\"dropdown-header\" role=\"presentation\">Active Workgroups</li>\n"
       ."            <li><a href=\"${html_path}ids/\">Imaging Device Security</a></li>\n"
       ."            <li><a href=\"${html_path}ipp/\">Internet Printing Protocol</a></li>\n"
       ."            <li class=\"divider\"></li>\n"
       ."            <li class=\"dropdown-header\" role=\"presentation\">Inactive Workgroups</li>\n"
       ."            <li><a href=\"${html_path}cloud/\">Cloud Imaging Model</a></li>\n"
       ."            <li><a href=\"${html_path}sm/\">Semantic Model</a></li>\n"
       ."            <li><a href=\"${html_path}wims/\">Workgroup for Imaging Management Solutions</a></li>\n"
       ."          </ul>\n"
       ."        </li>\n"
       ."        <li><a href=\"#modalSearch\" data-toggle=\"modal\" data-target=\"#modalSearch\">Search</a></li>\n"
       ."      </ul>\n"
       ."    </div>\n"
       ."  </div>\n"
       ."</nav>\n"
       ."<!-- Search Modal -->\n"
       ."<div id=\"modalSearch\" class=\"modal fade\" role=\"dialog\">\n"
       ."  <div class=\"modal-dialog\" role=\"document\">\n"
       ."    <!-- Modal content-->\n"
       ."    <div class=\"modal-content\">\n"
       ."       <div class=\"modal-header\">\n"
       ."         <h4 class=\"modal-title\">Search PWG</h4>\n"
       ."         <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">\n"
       ."           <span aria-hidden=\"true\">&times;</span>\n"
       ."         </button>\n"
       ."       </div>\n"
       ."       <div class=\"modal-body\">\n"
       ."         <script>\n"
       ."           (function() {\n"
       ."    	 var cx = '018021367961685880654:mdt584m83r4';\n"
       ."    	 var gcse = document.createElement('script');\n"
       ."    	 gcse.type = 'text/javascript';\n"
       ."    	 gcse.async = true;\n"
       ."    	 gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;\n"
       ."    	 var s = document.getElementsByTagName('script')[0];\n"
       ."    	 s.parentNode.insertBefore(gcse, s);\n"
       ."           })();\n"
       ."         </script>\n"
       ."         <gcse:search></gcse:search>\n"
       ."       </div>\n"
       ."       <div class=\"modal-footer\">\n"
       ."         <button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\">Close</button>\n"
       ."       </div>\n"
       ."    </div>\n"
       ."  </div>\n"
       ."</div>\n"
       ."<div id=\"pwg-body\">\n"
       ."  <div id=\"pwg-content\">\n");

//  if ($title != "" && $title[0] != "-")
//    print("    <h1 class=\"pwg-title\">$title$subtitle</h1>\n");
}


//
// 'site_footer()' - Show the standard footer for a page.
//

function
site_footer($javascript = "")
{
  global $html_path, $SITE_EMAIL;


  $year = date("Y");

  print("  </div>\n"
       ."</div>\n"
       ."<div id=\"pwg-footer\">\n"
       ."  <div id=\"pwg-footer-body\">Comments are owned by the poster. All other "
       ."material is Copyright &copy; 2001-$year The Printer Working Group. "
       ."All rights reserved. IPP Everywhere, the IPP Everywhere logo, and the "
       ."PWG logo are trademarks of the IEEE-ISTO.<br>\n"
       ."<a href=\"${html_path}about.html\">About the PWG</a> "
       ."&middot; "
       ."<a href=\"https://ieee-isto.org/privacy-policy/\">Privacy Policy</a> "
       ."&middot; "
       ."<a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a></div>\n"
       ."</div>\n"
       ."<script src=\"https://code.jquery.com/jquery-3.2.1.min.js\"   integrity=\"sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=\" crossorigin=\"anonymous\"></script>\n"
       ."<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\" integrity=\"sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa\" crossorigin=\"anonymous\"></script>\n");
  print("<script type=\"text/javascript\" src=\"//www.google.com/jsapi\"></script>\n"
       ."<script type=\"text/javascript\" src=\"${html_path}dynamo/resources/pwg.js\"></script>\n"
       ."<script type=\"text/javascript\" src=\"${html_path}dynamo/resources/pwg-cookie-notice.js\"></script>\n");
  if ($javascript != "")
    print("<script type=\"text/javascript\" src=\"${html_path}dynamo/resources/$javascript\"></script>\n");
  print("</body>\n"
       ."</html>\n");
}
?>
