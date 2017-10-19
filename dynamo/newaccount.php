<?php
//
// New account form...
//


//
// Include necessary headers...
//

include_once "phplib/site.php";

$usererror = "";

if (html_form_validate())
{
  if (array_key_exists("name", $_POST))
    $name = trim($_POST["name"]);
  else
    $name = "";

  if (array_key_exists("organization_id", $_POST))
    $organization_id = (int)$_POST["organization_id"];
  else
    $organization_id = 0;

  if (array_key_exists("other_organization", $_POST))
    $other_organization = trim($_POST["other_organization"]);
  else
    $other_organization = "";

  if ($organization_id < 0 && $other_organization != "")
  {
    if ($org_id = organization_lookup($other_organization))
      $organization_id = $org_id;
    else
    {
      $org = new organization();
      $org->name = $other_organization;
      if ($org->save())
	$organization_id = $org->id;
    }
  }

  if (array_key_exists("email", $_POST))
    $email = trim($_POST["email"]);
  else
    $email = "";

  if (array_key_exists("email2", $_POST))
    $email2 = trim($_POST["email2"]);
  else
    $email2 = "";

  if ($name == "" || $email == "" || $email2 == "")
    $usererror = "Please provide all of the requested information.";
  else if (!validate_email($email))
    $usererror = "Bad email address.";
  else if ($email != $email2)
    $usererror = "Email addresses do not match.";
  else
  {
    // Good new account request so far; see if account already exists...
    $result = db_query("SELECT * FROM user WHERE email LIKE ?", array($email));
    if (db_count($result) == 0)
    {
      // Nope, add unpublished user account and send registration email.
      $user                  = new user();
      $user->name            = $name;
      $user->organization_id = $organization_id;
      $user->email           = $email;
      $user->password();

      // Check whether the user's email address has the same domain as the
      // organization...
      $organization = new organization($organization_id);
      if ($organization->id == $organization_id && $organization->domain != "" && preg_match("/[@.]" . preg_quote($organization->domain) . "\$/i", $email))
      {
        $user->is_member = $organization->status == ORGANIZATION_STATUS_NON_VOTING ||
                           $organization->status == ORGANIZATION_STATUS_SMALL_VOTING ||
                           $organization->status == ORGANIZATION_STATUS_LARGE_VOTING;
      }

      if ($user->save())
      {
	$register = substr(hash("sha256", "$user->id:$user->modify_date:$user->hash"), 0, 8);

	$url = "https://$_SERVER[SERVER_NAME]/dynamo/enable.php?email=" .
	       urlencode($email) . "&register=$register";
	$msg = wordwrap("Thank you for requesting an account on the "
		       ."$SITE_NAME web site.  To complete your "
		       ."registration, go to the following URL:\n\n"
		       ."    $url\n\n"
		       ."and provide a password for your account.\n");
	mail($email, "$SITE_NAME User Registration", $msg,
	     "From: $SITE_EMAIL\r\n");

	site_header("New Account");

	print("<p>Thank you for requesting an account. You should receive an "
	     ."email from $SITE_EMAIL shortly with instructions on "
	     ."completing your registration.</p>\n");

	site_footer();
	exit();
      }
      else
        $usererror = "Unable to create account. Please contact $SITE_EMAIL "
                    ."for assistance.";
    }
    else
    {
      // Account or email already exists...
      $row = db_next($result);

      $usererror = "Email address already in use for an account.";
    }
  }
}
else
{
  $name               = "";
  $organization_id    = 0;
  $other_organization = "";
  $email              = "";
  $email2             = "";

  if ($REQUEST_METHOD == "POST")
    $usererror = "Bad form submission.";
}

// New user...
site_header("New Account");

if ($usererror != "")
  html_show_error($usererror);

print("<p>Please fill in the form below to register. An email will be sent "
     ."to the address you supply to confirm the registration:</p>\n");
html_form_start($PHP_SELF);
html_form_field_start("name", "Real Name");
html_form_text("name", "John Doe", $name);
html_form_field_end();
html_form_field_start("organization_id", "Organization Name");
organization_select("organization_id", $organization_id, "None", "", "Other...", "other_organization");
html_form_field_end();
html_form_field_start("email", "EMail");
html_form_email("email", "name@example.com", $email);
html_form_field_end();
html_form_field_start("email2", "EMail Again");
html_form_email("email2", "name@example.com", $email);
html_form_field_end();
html_form_end(array("SUBMIT" => "+Request Account"));

site_footer();
?>
