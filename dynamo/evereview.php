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
  header("Location: $html_login_url?PAGE=" . urlencode("${html_path}dynamo/evereview.php"));
  exit(0);
}

site_header("IPP Everywhere", "Review Self-Certifications");

// Get command-line options...
//
// Usage: evereview.php [operation]
//
// Operations:
//
// L         = List all submissions
// U#        = Modify/view submission #

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'L' && $op != 'U')
  {
    print("<p>Bad command '$op'.</p>\n");
    site_footer();
    exit();
  }
}
else
{
  $op = 'L';
  $id = 0;
}

if (!$LOGIN_IS_SUBMITTER && !$LOGIN_IS_REVIEWER && !$LOGIN_IS_ADMIN)
{
  html_show_error("You do not have access to the IPP Everywhere Printer self-certification review page.");
  print("<p>Please contact the <a href=\"mailto:$SITE_EMAIL\">PWG Webmaster</a> to request access. Organizations that have not yet submitted a signed IPP Everywhere logo license agreeemnt to the IEEE-ISTO are not eligible for access.</p>\n");
  site_footer();
  exit(0);
}

switch ($op)
{
  case "L" :
      // List
      if ($LOGIN_IS_ADMIN)
	$results = db_query("SELECT id FROM submission ORDER BY status,modify_date DESC");
      else
	$results = db_query("SELECT id FROM submission WHERE create_id=$LOGIN_ID OR reviewer1_id=$LOGIN_ID OR reviewer2_id=$LOGIN_ID ORDER BY status,modify_date DESC");

      if (($count = db_count($results)) == 0)
      {
	print("<p>No submissions found.</p>\n");
      }
      else
      {
	if ($count == 1)
	  print("<p>1 submission found:</p>\n");
	else
	  print("<p>$count submissions found:</p>\n");

	html_start_table(array("#","Product Family","Status","Last Updated"));
	$last_status = -1;
	while ($row = db_next($results))
	{
	  $submission = new submission($row["id"]);
	  if ($submission->id != $row["id"])
	    continue;

	  if ($submission->status != $last_status)
	  {
	    if ($last_status != -1)
	      print("<tr><td colspan=\"4\"></td></tr>\n");

	    $last_status = $submission->status;
	  }

	  $pf = htmlspecialchars($submission->product_family);
	  $st = $SUBMISSION_STATUSES[$submission->status];
	  $lu = html_date($submission->modify_date);
	  $l  = "<a href=\"$PHP_SELF?U$submission->id\">";

	  print("<tr><td>$l$submission->id</a></td>"
	       ."<td>$l$pf</a></td><td>$l$st</a></td>"
	       ."<td>$l$lu</a></td></tr>\n");
	}
	html_end_table();
      }

      db_free($results);
      break;

  case "U" : // Update
      print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a></p>\n");

      $submission = new submission($id);
      if ($submission->id != $id || $id == 0)
      {
        html_show_error("No such submission #$id.");
        site_footer();
        exit();
      }

      if (!$LOGIN_IS_ADMIN &&
	  $LOGIN_ID != $submission->create_id &&
	  $LOGIN_ID != $submission->reviewer1_id &&
	  $LOGIN_ID != $submission->reviewer2_id)
      {
        html_show_error("You do not have permission to access submission #$id.");
        site_footer();
        exit();
      }

      if ($REQUEST_METHOD == "POST" && $submission->loadform())
      {
        if ($submission->save())
        {
          $submission->notify_users();

          html_show_info("Changes saved.");
          $_POST["contents"] = "";
        }
        else
          html_show_error("Unable to save changes.");
      }
      else if ($REQUEST_METHOD == "POST")
	html_show_error("Please correct the highlighted fields.");

      $submission->form();
      break;
}

site_footer();

?>
