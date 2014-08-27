<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/db-submission.php";

// Get command-line options...
//
// Usage: evesubmit.php

if (!$LOGIN_ID)
{
  header("Location: $html_login_url?PAGE=" . urlencode("${html_path}dynamo/evesubmit.php"));
  exit(0);
}

site_header("IPP Everywhere", "Submit Self-Certification");

if (!$LOGIN_IS_SUBMITTER)
{
  html_show_error("You do not have access to the IPP Everywhere Printer self-certification submission page.");
  print("<p>Please contact the <a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> to request access. Organizations that have not yet submitted a signed IPP Everywhere logo license agreeemnt to the IEEE-ISTO are not eligible for access.</p>\n");
  site_footer();
  exit(0);
}

$submission = new submission();
if ($REQUEST_METHOD == "POST" && $submission->loadform())
{
  if ($submission->save())
  {
    if ($submission->add_files())
    {
      $submission->notify_users("");

      print("<p>Thank you for submitting your self-certification results. Your submission number is $submission->id. You should receive a response within 25 working days.</p>\n");
      site_footer();
      exit(0);
    }
  }
  else
  {
    html_show_error("There was an error saving your submission.");
    print("<p>Please try again later or contact the <a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> for assistance.");
    site_footer();
    exit(0);
  }
}

if ($REQUEST_METHOD == "POST")
  html_show_error("There was a problem with your submission. Please correct the highlighted fields below.");

$submission->form();

site_footer();

?>
