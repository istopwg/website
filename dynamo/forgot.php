<?php
//
// Login/registration form...
//


//
// Include necessary headers...
//

include_once "phplib/site.php";

$usererror = "";

if (html_form_validate())
{
  if (array_key_exists("email", $_POST))
    $email = trim($_POST["email"]);
  else
    $email = "";

  if ($email != "" && !validate_email($email))
    $usererror = $EMAIL_REQUIREMENTS;
  else if ($email == "")
    $usererror = "Please provide an email address.";
  else
  {
    // Good "forgot account/password" request so far; see if account already
    // exists...
    $result = db_query("SELECT * FROM user WHERE email LIKE ?", array($email));

    if (db_count($result) == 1)
    {
      // Found the account, send an email...
      $row      = db_next($result);
      $date     = db_datetime();
      $email    = $row["email"];
      $register = substr(hash("sha256", "$row[id]:$date:$row[hash]"), 0, 8);

      site_header("Forgot Password");

      if ($row["status"] == USER_STATUS_BANNED)
	print("<p>Your account is locked. Please contact $SITE_EMAIL for "
	     ."more information.</p>\n");
      else
      {
	db_query("UPDATE user SET modify_date = ? WHERE id = ?", array($date, (int)$row["id"]));

	$url = "https://$_SERVER[SERVER_NAME]$html_path/dynamo/enable.php?email=" .
	       urlencode($email) . "&register=$register";
        $msg = wordwrap("Someone, possibly you, requested that your password "
		       ."be reset on the $SITE_NAME web site.  To enter "
		       ."a new password, go to the following URL:\n\n"
		       ."    $url\n\n"
		       ."and enter your email ($email) and the "
		       ."following registration code:\n\n"
		       ."    $register\n\n"
		       ."You will then be able to access your account.\n");

	mail($email, "$SITE_NAME Password Reset Request", $msg,
	     "From: $SITE_EMAIL\r\n");

	print("<p>You should receive an email from $SITE_EMAIL shortly "
	     ."with instructions on resetting your password.</p>\n");

//        print("<p>code = $register</p>\n");
      }

      site_footer();
      exit();
    }

    // Account and email not found...
    $usererror = "No matching email address was found.";
  }
}
else
{
  $email = "";
}

// Forgot username or password form...
site_header("Forgot Password");

if ($usererror != "")
  html_show_error($usererror);

print("<p>If have forgotten your password, please fill in "
     ."the form below to reset your password. An email will be sent to the "
     ."address you supply with instructions:</p>\n");

html_form_start($PHP_SELF);
html_form_field_start("email", "EMail");
html_form_email("+email", "name@example.com", $email);
html_form_field_end();
html_form_end(array("SUBMIT" => "+Send Password Reset Instructions"));

site_footer();
?>
