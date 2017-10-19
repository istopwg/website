<?php
//
// Account enable form...
//


//
// Include necessary headers...
//

include_once "phplib/site.php";

$usererror = "";

if (array_key_exists("email", $_GET))
  $email = trim(strtolower($_GET["email"]));
else if (array_key_exists("email", $_POST))
  $email = trim(strtolower($_POST["email"]));
else
  $email = "";

if (array_key_exists("password", $_POST))
  $password = trim($_POST["password"]);
else
  $password = "";

if (array_key_exists("password2", $_POST))
  $password2 = trim($_POST["password2"]);
else
  $password2 = "";

if (array_key_exists("register", $_GET))
  $register = trim(strtolower($_GET["register"]));
else if (array_key_exists("register", $_POST))
  $register = trim(strtolower($_POST["register"]));
else
  $register = "";

if (!validate_email($email))
  $usererror = $EMAIL_REQUIREMENTS;
else if (!preg_match("/^[0-9a-f]{8}\$/", $register))
  $usererror = "Bad registration code.";
else if (html_form_validate())
{
  if (!validate_password($password))
    $usererror = $PASSWORD_REQUIREMENTS;
  else if ($password != $password2)
    $usererror = "Passwords do not match.";
  else
  {
    // Check that we have an existing user account...
    $result = db_query("SELECT * FROM user WHERE email LIKE ?", array($email));

    if (db_count($result) == 1)
    {
      // Yes, now check the registration code...
      $row     = db_next($result);
      $userid  = (int)$row["id"];
      $hash    = substr(hash("sha256", "$userid:$row[modify_date]:$row[hash]"), 0, 8);
      $expdate = time() - 86400;
      $moddate = db_seconds($row["modify_date"]);

      if ($hash == $register && $moddate >= $expdate)
      {
        // Good code, enable the account and login...
        $user = new user($userid);
        $user->password($password);
        $user->status = USER_STATUS_ENABLED;
        if ($user->save())
        {
	  if (auth_login($email, $password) == "")
	  {
            $user->status = USER_STATUS_PENDING;
            $user->save();

	    $usererror = "Login failed - please contact $SITE_EMAIL for "
			."assistance.";
	  }
	}
	else
	  $usererror = "Unable to enable account. Please contact "
	              ."$SITE_EMAIL for assistance.";
      }
      else if ($hash == $register)
        $usererror = "Registration code has expired. Please request a new code.";
      else
        $usererror = "Incorrect registration code.";
    }
    else
      $usererror = "Email not found.";
  }
}
else if ($REQUEST_METHOD == "POST")
  $usererror = "Bad form submission.";

if ($LOGIN_ID != 0)
{
  header("Location: account.php");
  exit();
}

site_header("Enable Account");

if ($usererror != "")
  html_show_error($usererror);

print("<p>Please enter the registration code that was emailed to you "
     ."with your email and password to enable your account and login:</p>\n");
html_form_start($PHP_SELF);
html_form_field_start("email", "EMail");
html_form_email("+email", "name@example.com", $email);
html_form_field_end();
html_form_field_start("register", "Registration Code");
html_form_text("+register", "xxxxxxxx", $register, "", 1, "", 8);
print(" <a href=\"forgot.php\" class=\"btn btn-sm\">Request New Code</a>");
html_form_field_end();
html_form_field_start("password", "Password", "", FALSE);
html_form_password("+password");
html_form_field_end();
html_form_field_start("password2", "Password Again", "", FALSE);
html_form_password("+password2");
html_form_field_end();
html_form_end(array("SUBMIT" => "+Enable Account"));

site_footer();
?>
