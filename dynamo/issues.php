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
// L         = List all issues
// U         = Post new issue
// U#        = Modify/view issue #
//
// Options:
//
// I#        = Set first Issue
// P#        = Set priority filter
// S#        = Set status filter
// E#        = Set user filter
// M#        = Set maximum Issues per page
// Qtext     = Set search text
// Z#        = Set document ID

$document_id = 0;
$femail      = 0;
$index       = 0;
$priority    = ISSUE_PRIORITY_ANY_WILDCARD;
$search      = "";
$status      = ISSUE_STATUS_OPEN_WILDCARD;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U')
  {
    site_header("Issues Error");
    print("<p>Bad command '$op'.</p>\n");
    site_footer();
    exit();
  }

  if ($op == 'B' && !$LOGIN_IS_ADMIN)
  {
    site_header("Issues Error");
    print("<p>The '$op' command is not available to you.</p>\n");
    site_footer();
    exit();
  }

  if ($op == 'L' && $id > 0)
    $op = 'U';

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'E' : // Show only issues matching the current user
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

      case 'Z' : // Set document filter
	  $document_id = (int)$option;
	  break;

      default :
	  site_header("Issues Error");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  site_footer();
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
  if (array_key_exists("FDOCUMENTID", $_POST))
    $document_id = (int)$_POST["FDOCUMENTID"];
  if (array_key_exists("SEARCH", $_POST))
    $search = $_POST["SEARCH"];
}

$options = "+P$priority+S$status+I$index+E$femail+Z$document_id+Q" . urlencode($search);

switch ($op)
{
  case 'B' : // Batch update selected Issues
      if (!html_form_validate())
      {
        header("Location: $PHP_SELF?L$options");
        break;
      }

      if (array_key_exists("status", $_POST) && ($_POST["status"] != "" || $_POST["priority"] != "" || $_POST["assigned_id"] != ""))
      {
        foreach ($_POST as $key => $val)
	{
          if (preg_match("/^ID_[0-9]+\$/", $key))
	  {
	    $id  = (int)substr($key, 3);
	    $issue = new issue($id);

	    if ($issue->id != $id)
	      continue;

            $contents = "";

	    if (array_key_exists("status", $_POST) && (int)$_POST["status"] > 0 && (int)$_POST["status"] != $issue->status)
	    {
	      $issue->status = (int)$_POST["status"];
	      $contents .= "Status changed to '" . $ISSUE_STATUS_SHORT[$issue->status] . "'.\n";
	    }
	    if (array_key_exists("priority", $_POST) && (int)$_POST["priority"] > 0 && (int)$_POST["priority"] != $issue->priority)
	    {
	      $issue->priority = (int)$_POST["priority"];
	      $contents .= "Priority changed to '" . $ISSUE_PRIORITY_LONG[$issue->priority] . "'.\n";
	    }
	    if (array_key_exists("assigned_id", $_POST) && (int)$_POST["assigned_id"] > 0 && (int)$_POST["assigned_id"] != $issue->assigned_id)
	    {
	      $issue->assigned_id = (int)$_POST["assigned_id"];
	      $contents .= "Assigned to '" . user_name($issue->assigned_id) . "'.\n";
            }

            if ($issue->validate())
	    {
              $issue->save();
	      $issue->notify_users($contents);
	    }
	  }
        }
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // List issue(s)
      site_header("Issues");

      if ($LOGIN_ID != 0)
        print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$PHP_SELF?U$options\">Create Issue</a></p>\n");
      else
	print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$html_login_url?PAGE=" .
	      urlencode("$PHP_SELF?U$options") . "\">Login to Create Issue</a></p>\n");

      html_form_start("$PHP_SELF?L", TRUE, FALSE, TRUE);
      html_form_search("SEARCH", "Search Issues", $search);
      html_form_button("SUBMIT", "-Search Issues");
      print("<br>\n");
      html_form_select("FPRIORITY", $ISSUE_PRIORITY_LIST, "", $priority);
      html_form_select("FSTATUS", $ISSUE_STATUS_LIST, "", $status);

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

      print("&nbsp;in&nbsp;");
      document_select("FDOCUMENTID", $document_id, "All Documents");

      html_form_end();

      $matches = issue_search($search, "-status -priority id", $priority, $status, $document_id, $femail);
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No issues found.</p>\n");

	if (($priority || $status) && $search != "")
	{
	  $htmlsearch = htmlspecialchars($search, ENT_QUOTES);
	  print("<p><a href='$PHP_SELF?L+S0+Q" . urlencode($search)
	       ."'>Search for \"<i>$htmlsearch</i>\" in all issues...</a></p>\n");
	}

	site_footer();
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
                    "+Z$document_id+Q" . urlencode($search));

      $columns  = array("Id", "Priority", "Status", "Summary", "Last Updated");
      $colcount = sizeof($columns) - 2;

      html_start_table($columns);

      for ($i = $index; $i < $end; $i ++)
      {
	$issue  = new issue($matches[$i]);
	$date   = html_date($issue->modify_date);
	$title  = htmlspecialchars($issue->title, ENT_QUOTES);
	$tabbr  = html_abbreviate($issue->title, 80);
	$prtext = $ISSUE_PRIORITY_SHORT[$issue->priority];
	$sttext = $ISSUE_STATUS_SHORT[$issue->status];
	$link   = "<a href='$PHP_SELF?U$issue->id$options' title='Issue #$issue->id: $title'>";

	print("<tr><td nowrap>");
	if ($LOGIN_IS_ADMIN)
	  html_form_checkbox("ID_$issue->id");
	print("$link$issue->id</a></td>"
	     ."<td align=\"center\">$link$prtext</a></td>"
	     ."<td align=\"center\">$link$sttext</a></td>"
	     ."<td width=\"66%\">$link$tabbr</a></td>"
	     ."<td align=\"center\" nowrap>$link$date</a></td>"
	     ."</tr>\n");

	if ($issue->status <= ISSUE_STATUS_ACTIVE)
	{
	  $textresult = comment_search("issue_$issue->id");
	  if (($count = sizeof($textresult)) > 0)
	  {
	    $comment  = new comment($textresult[$count - 1]);
	    $name     = user_name($comment->create_id);
	    $contents = html_text($comment->contents, TRUE);

	    print("<tr><td colspan=\"2\">&nbsp;</td><td colspan=\"$colcount\">$name: <tt>$contents</tt></td></tr>\n");
	  }
	}
      }

      html_end_table();

      if ($LOGIN_IS_ADMIN)
      {
	print("<p align=\"center\">");
        html_form_select("status",
                         array(ISSUE_STATUS_PENDING => "Status: Pending",
                               ISSUE_STATUS_ACTIVE => "Status: Active",
                               ISSUE_STATUS_RESOLVED => "Status: Resolved",
                               ISSUE_STATUS_UNRESOLVED => "Status: Unresolved"),
                         "Status: No Change", 0);
        html_form_select("priority",
                         array(ISSUE_PRIORITY_CRITICAL => "Priority: Critical",
                               ISSUE_PRIORITY_HIGH => "Priority: High",
                               ISSUE_PRIORITY_MODERATE => "Priority: Moderate",
                               ISSUE_PRIORITY_LOW => "Priority: Low",
                               ISSUE_PRIORITY_RFE => "Priority: Enhancement"),
                         "Priority: No Change", 0);
	user_select("assigned_id", 0, USER_SELECT_MEMBER | USER_SELECT_EDITOR, "No Change", "Assigned To: ");
        html_form_end(array("SUBMIT" => "--Modify Selected Issues"));
        print("</p>");
      }

      html_paginate($index, $count, $LOGIN_PAGEMAX,
                    "$PHP_SELF?L+P$priority+S$status+E$femail+I",
                    "+Q" . urlencode($search));

      site_footer();
      break;

  case 'U' : // Post new/modify existing Issue
      if ($LOGIN_ID == 0)
      {
        header("Location: $html_login_url?PAGE=" . urlencode("${html_path}issues.php?U$id$options"));
	return;
      }

      $issue    = new issue($id);
      $oldissue = new issue($id); // TODO: Way to get real copy?

      if ($id <= 0)
      {
	$action = "Create Issue";
	$title  = "Create Issue";

	$issue->document_id = $document_id;
      }
      else
      {
	$action = "Save Issue #$id";
	$title  = "Issue #$id: $issue->title";
      }

      if ($issue->id != $id)
      {
        site_header($action);

	print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a></p>\n");

	html_show_error("Issue #$id was not found.");

	site_footer();
	exit();
      }

      if (html_form_validate())
      {
        $havedata = $issue->loadform();

	if ($id == 0 && (!array_key_exists("contents", $_POST) || trim($_POST["contents"]) == ""))
	  $havedata = 0;
      }
      else
      {
        $issue->validate();

        $havedata = 0;
      }

      site_header($title);

      if ($havedata)
      {
        if (!$issue->save())
	{
	  print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a></p>\n");

	  html_show_error("Unable to save issue.");

	  site_footer();
	  exit();
	}

	if (array_key_exists("contents", $_POST))
	{
	  // Add text...
	  if (($contents = $issue->add_comment()) === FALSE)
	  {
	    print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a></p>\n");

	    html_show_error("Unable to save comment to issue.");

	    site_footer();
	    exit();
	  }
	}
	else
	  $contents = "Updated by '" . user_name($issue->modify_id) . "'.";

	$contents .= "\n";

	if (array_key_exists("status", $_POST) && (int)$_POST["status"] > 0 && (int)$_POST["status"] != $oldissue->status)
	  $contents .= "\nStatus changed to '" . $ISSUE_STATUS_SHORT[$issue->status] . "'.";
	if (array_key_exists("priority", $_POST) && (int)$_POST["priority"] > 0 && (int)$_POST["priority"] != $oldissue->priority)
	  $contents .= "\nPriority changed to '" . $ISSUE_PRIORITY_LONG[$issue->priority] . "'.";
	if (array_key_exists("assigned_id", $_POST) && (int)$_POST["assigned_id"] > 0 && (int)$_POST["assigned_id"] != $oldissue->assigned_id)
	  $contents .= "\nAssigned to '" . user_name($issue->assigned_id) . "'.";

	if ($id <= 0)
	  $issue->notify_users($contents, "");
	else
	  $issue->notify_users($contents);

	print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a> <a class=\"btn btn-default\" href=\"$PHP_SELF?U$options\">Create Another</a></p>\n");

	html_show_info("Issue saved.");

	$action = "Save Issue #$issue->id";
      }
      else
      {
	print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Return to List</a></p>\n");

	if ($REQUEST_METHOD == "POST")
	  html_show_error("Please correct the highlighted fields.");
      }

      $issue->form($action, $options);

      site_footer();
      break;
}

?>
