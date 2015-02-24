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

site_header("Submit IPP Everywhere Self-Certification");

if (!$LOGIN_ID)
{
  print("<p>Access to the IPP Everywhere Printer self-certification submission page requires a login on this web site and submission of a signed <a href=\"${html_path}ipp/everywhere.html\">IPP Everywhere logo license agreement</a> to the IEEE-ISTO.</p>\n"
       ."<p><a class=\"btn btn-default\" href=\"$html_login_url?PAGE=" . urlencode("${html_path}dynamo/evesubmit.php") . "\">Login or Request Account</a></p>\n");
  site_footer();
  exit(0);
}

if (!$LOGIN_IS_SUBMITTER)
{
  print("<p>Access to the IPP Everywhere Printer self-certification submission page requires submission of a signed <a href=\"${html_path}ipp/everywhere.html\">IPP Everywhere logo license agreement</a> to the IEEE-ISTO.</p>\n"
       ."<p><a class=\"btn btn-default\" href=\"${html_path}dynamo/request.php\">Request Access</a></p>\n");
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

      print("<p><a class=\"btn btn-default\" href=\"${html_path}dynamo/evereview.php\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Review Self-Certifications</a></p>\n");

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
