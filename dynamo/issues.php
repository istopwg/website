<?php
//
// Issue tracking page for errata and document updates...
//

//
// Include necessary headers...
//

include_once "phplib/db-issue.php";

// Get command-line options...
//
// Usage: issues.php [operation] [options]
//
// Operations:
//
// B         = Batch update selected Issues
// L         = List all bugs
// U         = Post new bug
// U#        = Modify/view bug #
//
// Options:
//
// I#        = Set first Issue
// P#        = Set priority filter
// S#        = Set status filter
// E#        = Set user filter
// M#        = Set maximum Issues per page
// Qtext     = Set search text
// Z#        = Set project ID

$femail     = 0;
$index      = 0;
$priority   = 0;
$search     = "";
$status     = -2;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U')
  {
    issue_header("Issues Error");
    print("<p>Bad command '$op'.</p>\n");
    issue_footer();
    exit();
  }

  if ($op == 'B' && !$LOGIN_IS_ADMIN)
  {
    issue_header("Issues Error");
    print("<p>The '$op' command is not available to you.</p>\n");
    issue_footer();
    exit();
  }

  if ($op == 'L' && $id > 0)
    $op = 'U';

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'E' : // Show only problem reports matching the current user
	  $femail = (int)$option;
	  break;

      case 'I' : // Set first Issue
	  $index = (int)$option;
	  if ($index < 0)
	    $index = 0;
	  break;

      case 'P' : // Set priority filter
	  $priority = (int)$option;
	  break;

      case 'Q' : // Set search text
	  $search = urldecode($option);
	  $i ++;
	  while ($i < $argc)
	  {
	    $search .= urldecode(" $argv[$i]");
	    $i ++;
	  }
	  break;

      case 'S' : // Set status filter
	  $status = (int)$option;
	  break;

      default :
	  issue_header("Issues Error");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  issue_footer();
	  exit();
	  break;
    }
  }
}
else
{
  $op = 'L';
  $id = 0;
}

if (html_form_validate())
{
  if (array_key_exists("FPRIORITY", $_POST))
    $priority = (int)$_POST["FPRIORITY"];
  if (array_key_exists("FSTATUS", $_POST))
    $status = (int)$_POST["FSTATUS"];
  if (array_key_exists("FEMAIL", $_POST))
    $femail = (int)$_POST["FEMAIL"];
  if (array_key_exists("SEARCH", $_POST))
    $search = $_POST["SEARCH"];
}

function issue_header($title)
{
  site_header($title);
  html_title($title);
}

function issue_footer()
{
  print("</div></div>\n");
  site_footer();
}


$options = "+P$priority+S$status+I$index+E$femail+Q" . urlencode($search);

if ($op == "L" || ($op == "U" && $id != 0))
{
  // Set $issuelinks to point to the previous and next bugs in the
  // current search, respectively...
  $matches = issue_search($search, "-status -priority id",
			$project_id, $priority, $status, $femail);
  $count   = sizeof($matches);

  if ($id != 0 && $count > 1 && $search != "")
  {
    if (($me = array_search($id, $matches)) === FALSE)
      $me = -1;

    $issuelinks = "<span style='float: right;'>";

    if ($me > 0)
    {
      $previd   = $matches[$me - 1];
      $issuelinks .= "<a href='$PHP_SELF?$op$previd$options'>Prev</a>";
    }
    else
      $issuelinks .= "<span style='color: #cccccc;'>Prev</span>";

    $issuelinks .= " &middot; ";

    if (($me + 1) < $count)
    {
      $nextid   = $matches[$me + 1];
      $issuelinks .= "<a href='$PHP_SELF?$op$nextid$options'>Next</a>";
    }
    else
      $issuelinks .= "<span style='color: #cccccc;'>Next</span>";

    $issuelinks .= "</span>";
  }
  else
    $issuelinks = "";
}
else
  $issuelinks = "";

switch ($op)
{
  case 'B' : // Batch update selected Issues
      if (!html_form_validate())
      {
        header("Location: $PHP_SELF?L$options");
        break;
      }

      if (array_key_exists("status", $_POST) &&
          ($_POST["status"] != "" ||
	   $_POST["issue_version"] != "" ||
	   $_POST["fix_version"] != "" ||
	   $_POST["priority"] != "" ||
	   $_POST["assigned_id"] != "" ||
	   $_POST["message"] != ""))
      {
        foreach ($_POST as $key => $val)
	{
          if (preg_match("/^ID_[0-9]+\$/", $key))
	  {
	    $id  = (int)substr($key, 3);
	    $issue = new issue($id);

	    if ($issue->id != $id)
	      continue;

	    if (array_key_exists("status", $_POST) && (int)$_POST["status"] > 0)
	      $issue->status = (int)$_POST["status"];
	    if (array_key_exists("issue_version", $_POST))
	      $issue->issue_version = trim($_POST["issue_version"]);
	    if (array_key_exists("fix_version", $_POST))
	      $issue->fix_version = trim($_POST["fix_version"]);
	    if (array_key_exists("priority", $_POST) &&
	        (int)$_POST["priority"] > 0)
	      $issue->priority = (int)$_POST["priority"];
	    if (array_key_exists("assigned_id", $_POST) &&
		(int)$_POST["assigned_id"] > 0)
	      $issue->assigned_id = (int)$_POST["assigned_id"];

            if ($issue->validate())
	    {
              $issue->save();

              if ($_POST["message"] != "")
		$contents = $issue->add_text();
	      else
	        $contents = "";

	      if ($contents !== FALSE)
		$issue->notify_users($contents);
	    }
	  }
        }
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // List issue(s)
      issue_header("Issues");

      html_form_start("$PHP_SELF?L$options");
      print("<p align=\"center\">");
      html_form_search("SEARCH", "Search Issues", $search);
      html_form_button("SUBMIT", "-Search Issues");
      print("<br>\n");
      html_form_select("FPRIORITY", $BUG_PRIORITY_LIST, "", $priority);
      html_form_select("FSTATUS", $BUG_STATUS_LIST, "", $status);

      if ($LOGIN_ID != 0)
      {
	print("<select name='FEMAIL'>");
	print("<option value='0'>Show: All Issues</option>");
	print("<option value='1'");
	if ($femail)
	  print(" selected");
	print(">Show: My Issues</option>");
	print("</select>");
      }
      else
        print("Show: All&nbsp;Issues");

      if ($project_id < 0)
      {
	print("&nbsp;in&nbsp;");
        project_select("FPROJECTID", $project_id, "All Projects");
      }

      print("<br>\n"
           ."<small>Search supports 'and', 'or', 'not', and parenthesis. "
	   ."<a href='search-help.php'>More info...</a></small></p>\n");
      html_form_end();

      $matches = issue_search($search, "-status -priority id", $priority, $status, $femail);
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No bugs found.</p>\n");

	if (($priority || $status) && $search != "")
	{
	  $htmlsearch = htmlspecialchars($search, ENT_QUOTES);
	  print("<p><a href='$PHP_SELF?L+S0+Q" . urlencode($search)
	       ."'>Search for \"<i>$htmlsearch</i>\" in all issues...</a></p>\n");
	}

	issue_footer();
	exit();
      }

      if ($index >= $count)
	$index = $count - ($count % $LOGIN_PAGEMAX);
      if ($index < 0)
	$index = 0;

      $start = $index + 1;
      $end   = $index + $LOGIN_PAGEMAX;
      if ($end > $count)
	$end = $count;

      $prev = $index - $LOGIN_PAGEMAX;
      if ($prev < 0)
	$prev = 0;
      $next = $index + $LOGIN_PAGEMAX;

      if ($LOGIN_IS_ADMIN)
	html_form_start("$PHP_SELF?B$options", TRUE);

      html_paginate($index, $count, $LOGIN_PAGEMAX,
                    "$PHP_SELF?L+P$priority+S$status+E$femail+I",
                    "+Q" . urlencode($search));

      $columns  = array("Id", "Priority", "Status", "Summary", "Version",
		        "Last Updated");
      $colcount = sizeof($columns);

      html_start_table($columns);

      for ($i = $index; $i < $end; $i ++)
      {
	$issue  = new issue($matches[$i]);
	$date   = html_date($issue->modify_date);
	$title  = htmlspecialchars($issue->title, ENT_QUOTES);
	$tabbr  = html_abbreviate($issue->title, 80);
	$prtext = $BUG_PRIORITY_SHORT[$issue->priority];
	$sttext = $BUG_STATUS_SHORT[$issue->status];
	$link   = "<a href='$PHP_SELF?U$issue->id$options' title='Issue #$issue->id: $summary'>";

	print("<tr><td nowrap>");
	if ($LOGIN_IS_ADMIN)
	  html_form_checkbox("ID_$issue->id");
	print("$link$issue->id</a></td>"
	     ."<td align=\"center\">$link$prtext</a></td>"
	     ."<td align=\"center\">$link$sttext</a></td>"
	     ."<td>$link$tabbr</a></td>"
	     ."<td align=\"center\">$link$issue->issue_version</a></td>"
	     ."<td align=\"center\" nowrap>$link$date</a></td>"
	     ."</tr>\n");

	if ($issue->status >= BUG_STATUS_PENDING)
	{
	  $textresult = comment_search("issue_$issue->id");
	  if (($count = sizeof($textresult)) > 0)
	  {
	    $comment  = new comment($textresult[$count - 1]);
	    $name     = user_name($comment->create_id);
	    $contents = html_abbreviate($comment->contents, 128);

	    print("<tr><td colspan=\"3\">&nbsp;</td>"
	         ."<td colspan=\"$textcount\">$name: <tt>$contents</tt>"
	         ."</td></tr>\n");
	  }
	}
      }

      html_end_table();

      if ($LOGIN_IS_ADMIN)
      {
	print("<div class=\"form-actions\">");
        html_form_select("status",
                         array("Status: Resolved", "Status: Unresolved",
                               "Status: Active", "Status: Pending"),
                         "Status: No Change", 0, "", "1");
        html_form_select("priority",
                         array("Priority: RFE", "Priority: Low",
                               "Priority: Mod", "Priority: High",
                               "Priority: Crit"),
                         "Priority: No Change", 0, "", "1");
	user_select("assigned_id", 0, TRUE, "No Change", "Assigned To: ");
        html_form_end(array("SUBMIT" => "-Modify Selected Issues"));
        print("</div>");
      }

      html_paginate($index, $count, $LOGIN_PAGEMAX,
                    "$PHP_SELF?L+P$priority+S$status+E$femail+I",
                    "+Q" . urlencode($search));

      issue_footer();
      break;

  case 'U' : // Post new/modify existing Issue
      if ($LOGIN_ID == 0)
      {
        header("Location: $html_login_url?PAGE=" . urlencode("issues.php?U$id$options"));
	return;
      }

      $issue = new issue($id);

      if ($id <= 0)
      {
	$action = "Create Issue";
	$title  = "Create Issue";
      }
      else
      {
	$action = "Save Issue #$id";
	$title  = "Issue #$id: $issue->summary";
      }

      if ($issue->id != $id)
      {
        issue_header($action);
	print("<p><b>Error:</b> Issue #$id was not found.</p>\n");
	issue_footer();
	exit();
      }

      if (html_form_validate())
      {
        $havedata = $issue->loadform();

	if ($id == 0 && !array_key_exists("contents", $_POST))
	  $havedata = 0;
      }
      else
      {
        $issue->validate();

        $havedata = 0;
      }

      if ($havedata)
      {
        if (!$issue->save())
	{
	  issue_header($title);
	  print("<p>Unable to save issue.</p>\n");
	  issue_footer();
	  exit();
	}

	if (array_key_exists("contents", $_POST))
	{
	  // Add text...
	  if (($contents = $issue->add_comment()) === FALSE)
	  {
	    issue_header($title);
	    print("<p>Unable to save text to issue.</p>\n");
	    issue_footer();
	    exit();
	  }
	}
	else
	  $contents = "";

	if ($id <= 0)
	  $issue->notify_users($contents, "");
	else
	  $issue->notify_users($contents);

	header("Location: $PHP_SELF?U$issue->id$options");
      }
      else
      {
        issue_header($title);
	print($issuelinks);

	if ($REQUEST_METHOD == "POST")
	{
	  print("<p><b>Error:</b> Please fill in the fields as "
	       ."<span class=\"invalid\">marked</span> and resubmit.</p>\n"
	       ."<hr noshade>\n");
	}

        $issue->form($action, $options);

	issue_footer();
      }
      break;
}

//
// End of "$Id: issues.php 143 2014-04-14 02:16:36Z msweet $".
//
?>
