<?php
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
  $page = "${html_path}index.html";

if (!preg_match("/\\/dynamo\\/[a-z]+\\.php(|\\?.*)\$/", $page) && !preg_match("/\\.html\$/", $page))
  $page = "{$html_path}index.html";

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
html_form_password("+password");
html_form_field_end();
html_form_end(array("SUBMIT" => "+Login"));

print("<p><a class=\"btn btn-default btn-xs\" href=\"newaccount.php\">New Account</a> "
     ."<a <a class=\"btn btn-default btn-xs\" href=\"forgot.php\">Forgot Password</a></p>\n");

site_footer();
?>
