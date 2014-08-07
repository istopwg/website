<?php
//
// "$Id: account.php 350 2012-09-10 05:33:20Z mike $"
//
// Account management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-user.php";


if ($LOGIN_ID == 0)
{
  header("Location: $html_login_url");
  exit(0);
}

site_header("Profile", $LOGIN_NAME);

$user = new user($LOGIN_ID);
if ($REQUEST_METHOD == "POST")
{
  if ($user->loadform())
  {
    $user->save();
    html_show_info("Changes saved.");
    site_footer();
    exit();
  }

  html_show_error("Please correct the highlighted fields.");
}
$user->form();

site_footer();

//
// End of "$Id: account.php 350 2012-09-10 05:33:20Z mike $".
//
?>
