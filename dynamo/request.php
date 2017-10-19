<?php
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

site_header("Request Access Roles", $LOGIN_NAME);

$user = new user($LOGIN_ID);

if (html_form_validate() && (array_key_exists("editor", $_POST) || array_key_exists("member", $_POST)))
{
  $message = "$LOGIN_EMAIL would like to have the following additional access roles:";
  if (array_key_exists("editor", $_POST))
    $message .= " Editor";
  if (array_key_exists("member", $_POST))
    $message .= " Member";

  $message .= "\n\nLink: ${SITE_URL}/dynamo/accounts.php?U$user->id\n";

  mail($SITE_EMAIL, "PWG.org Access Request", wordwrap($message), "From: $LOGIN_EMAIL\n");

  print("<p>A message has been sent to the PWG.org webmaster requesting the additional access roles.</p>");
  site_footer();
  exit(0);
}

print("<p>This page allows you to request additional access roles for your PWG.org account. Your current roles are:");

if ($user->is_editor)
  print(" Editor");
if ($user->is_member)
  print(" Member");
if (!$user->is_editor && !$user->is_member && !$user->is_reviewer && !$user->is_submitter)
  print(" None");

print(".</p>\n"
     ."<p>Additional roles:</p>\n");

html_form_start($PHP_SELF, TRUE);

if (!$user->is_editor)
{
  print("&nbsp;&nbsp;&nbsp;&nbsp;");
  html_form_checkbox("editor", "Request Editor Access", 0, "Editors can post new documents to PWG.org.");
  print("<br>\n");
}

if (!$user->is_member)
{
  print("&nbsp;&nbsp;&nbsp;&nbsp;");
  html_form_checkbox("member", "Request Member Access", 0, "Request if you are a PWG member.");
  print("<br>\n");
}

html_form_end(array("SUBMIT" => "+Request Access Roles"));

site_footer();
?>
