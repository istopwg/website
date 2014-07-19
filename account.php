<?php
//
// "$Id: account.php 350 2012-09-10 05:33:20Z mike $"
//
// Account management page...
//

//
// Include necessary headers...
//

include_once "phplib/site.php";
include_once "phplib/db-article.php";
include_once "phplib/db-bug.php";
include_once "phplib/db-user.php";


if ($LOGIN_ID == 0)
{
  header("Location: $html_login_url");
  exit(0);
}

html_header("$LOGIN_NAME");
print("<div class=\"page-header\"><h1>Settings <small>$LOGIN_NAME</small></h1>"
     ."</div>\n");

$user = new user($LOGIN_ID);
if ($REQUEST_METHOD == "POST")
{
  if ($user->loadform())
  {
    $user->save();
    html_show_info("Changes saved.");
  }
}
$user->form();

html_footer();

//
// End of "$Id: account.php 350 2012-09-10 05:33:20Z mike $".
//
?>
