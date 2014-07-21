<?php
//
// "$Id: login.php 121 2013-10-07 14:26:37Z msweet $"
//
// Login form...
//


//
// Include necessary headers...
//

include_once "phplib/site.php";

$usererror = "";

if (array_key_exists("PAGE", $_GET))
  $page = $_GET["PAGE"];
else if (array_key_exists("PAGE", $_POST))
  $page = $_POST["PAGE"];
else
  $page = "index.php";

if (!preg_match("/^(\\/[a-z]+|[a-z]+)\\.php(|\\?.*)\$/", $page))
  $page = "index.php";

if (html_form_validate())
{
  if (array_key_exists("email", $_POST))
    $email = trim($_POST["email"]);
  else
    $email = "";

  if (array_key_exists("password", $_POST))
    $password = trim($_POST["password"]);
  else
    $password = "";

  if ($email == "" || $password == "")
    $usererror = "Please enter your email address and password.";
  else if (!validate_email($email))
    $usererror = $EMAIL_REQUIREMENTS;
  else if (auth_login($email, $password) == "")
    $usererror = "Unable to login with that email address and password.";
}
else
{
  $email    = "";
  $password = "";
}

if ($LOGIN_ID > 0)
{
  header("Location: $page");
  exit(0);
}

// Header + start of table...
site_header("Login");

if ($usererror != "")
  html_show_error($usererror);

html_form_start($PHP_SELF);
html_form_hidden("PAGE", $page);
html_form_field_start("email", "EMail");
html_form_email("+email", "name@example.com", $email);
html_form_field_end();
html_form_field_start("password", "Password");
html_form_password("+password", "", "", TRUE);
html_form_field_end();
html_form_end(array("SUBMIT" => "+Login"));

print("<p><a href=\"newaccount.php\">New Account</a><br>\n"
     ."<a href=\"forgot.php\">Forgot Password</a></p>\n");

site_footer();


//
// End of "$Id: login.php 121 2013-10-07 14:26:37Z msweet $".
//
?>
