<?php
//
// "$Id: bugs.php 143 2014-04-14 02:16:36Z msweet $"
//
// Bugs page...
//

//
// Include necessary headers...
//

include_once "phplib/db-bug.php";


// See if we were called as ".../bugs.php/Name" and do a redirect as needed
if ($PATH_INFO != "")
{
  $dname = db_escape(substr($PATH_INFO, 1));
  $result = db_query("SELECT id FROM project WHERE name LIKE '$dname'");

  if ($row = db_next($result))
    header("Location: $html_path/bugs.php?L+Z$row[id]");
  else
    header("Location: $html_path/bugs.php");

  exit(0);
}


// Get command-line options...
//
// Usage: bugs.php [operation] [options]
//
// Operations:
//
// B         = Batch update selected Bugs
// L         = List all bugs
// U         = Post new bug
// U#        = Modify/view bug #
//
// Options:
//
// I#        = Set first Bug
// P#        = Set priority filter
// S#        = Set status filter
// E#        = Set user filter
// M#        = Set maximum Bugs per page
// Qtext     = Set search text
// Z#        = Set project ID

$femail     = 0;
$index      = 0;
$project_id = -1;
$priority   = 0;
$search     = "";
$status     = -2;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U')
  {
    bug_header("Bugs Error");
    print("<p>Bad command '$op'.</p>\n");
    bug_footer();
    exit();
  }

  if ($op == 'B' && !$LOGIN_IS_ADMIN)
  {
    bug_header("Bugs Error");
    print("<p>The '$op' command is not available to you.</p>\n");
    bug_footer();
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

      case 'I' : // Set first Bug
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

      case 'Z' : // Set project filter
          $project_id = (int)$option;
          break;

      default :
	  bug_header("Bugs Error");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  bug_footer();
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
  if (array_key_exists("FPROJECTID", $_POST) &&
      preg_match("/^p[0-9]+\$/", $_POST["FPROJECTID"]))
    $project_id = (int)substr($_POST["FPROJECTID"], 1);
  if (array_key_exists("SEARCH", $_POST))
    $search = $_POST["SEARCH"];
}

if ($project_id < 0)
{
  $project_id = -1;
  $project    = FALSE;
}
else
{
  $project = new project($project_id);
  if ($project->id != $project_id ||
      (!$LOGIN_IS_ADMIN && !$project->is_published))
  {
    $project_id = -1;
    $project    = FALSE;
  }
}

function bug_header($title)
{
  global $html_path, $project, $LOGIN_IS_ADMIN;

  site_header($title);

  if ($project && $project->id > 0)
    html_title($project->name, $title);
  else
    html_title("msweet.org", $title);

  print("<div class=\"row-fluid\"><div class=\"span2 hidden-print\">\n");
  project_links($project);
  print("</div><div class=\"span10\">\n");
}

function bug_footer()
{
  print("</div></div>\n");
  site_footer();
}


$options = "+P$priority+S$status+I$index+E$femail+Z$project_id+Q" .
	   urlencode($search);

if ($op == "L" || ($op == "U" && $id != 0))
{
  // Set $buglinks to point to the previous and next bugs in the
  // current search, respectively...
  $matches = bug_search($search, "-status -priority id",
			$project_id, $priority, $status, $femail);
  $count   = sizeof($matches);

  if ($id != 0 && $count > 1 && $search != "")
  {
    if (($me = array_search($id, $matches)) === FALSE)
      $me = -1;

    $buglinks = "<span style='float: right;'>";

    if ($me > 0)
    {
      $previd   = $matches[$me - 1];
      $buglinks .= "<a href='$PHP_SELF?$op$previd$options'>Prev</a>";
    }
    else
      $buglinks .= "<span style='color: #cccccc;'>Prev</span>";

    $buglinks .= " &middot; ";

    if (($me + 1) < $count)
    {
      $nextid   = $matches[$me + 1];
      $buglinks .= "<a href='$PHP_SELF?$op$nextid$options'>Next</a>";
    }
    else
      $buglinks .= "<span style='color: #cccccc;'>Next</span>";

    $buglinks .= "</span>";
  }
  else
    $buglinks = "";
}
else
  $buglinks = "";

switch ($op)
{
  case 'B' : // Batch update selected Bugs
      if (!html_form_validate())
      {
        header("Location: $PHP_SELF?L$options");
        break;
      }

      if (array_key_exists("status", $_POST) &&
          ($_POST["status"] != "" ||
	   $_POST["bug_version"] != "" ||
	   $_POST["fix_version"] != "" ||
	   $_POST["priority"] != "" ||
	   $_POST["developer_id"] != "" ||
	   $_POST["message"] != ""))
      {
        foreach ($_POST as $key => $val)
	{
          if (preg_match("/^ID_[0-9]+\$/", $key))
	  {
	    $id  = (int)substr($key, 3);
	    $bug = new bug($id);

	    if ($bug->id != $id)
	      continue;

	    if (array_key_exists("status", $_POST) && (int)$_POST["status"] > 0)
	      $bug->status = (int)$_POST["status"];
	    if (array_key_exists("bug_version", $_POST))
	      $bug->bug_version = trim($_POST["bug_version"]);
	    if (array_key_exists("fix_version", $_POST))
	      $bug->fix_version = trim($_POST["fix_version"]);
	    if (array_key_exists("priority", $_POST) &&
	        (int)$_POST["priority"] > 0)
	      $bug->priority = (int)$_POST["priority"];
	    if (array_key_exists("developer_id", $_POST) &&
		(int)$_POST["developer_id"] > 0)
	      $bug->developer_id = (int)$_POST["developer_id"];

            if ($bug->validate())
	    {
              $bug->save();

              if ($_POST["message"] != "")
		$contents = $bug->add_text();
	      else
	        $contents = "";

	      if ($contents !== FALSE)
		$bug->notify_users($contents);
	    }
	  }
        }
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // List bug(s)
      bug_header("Bugs");

      html_form_start("$PHP_SELF?L$options");
      print("<p align=\"center\">");
      html_form_search("SEARCH", "Search Bugs", $search);
      html_form_button("SUBMIT", "-Search Bugs");
      print("<br>\n");
      html_form_select("FPRIORITY", $BUG_PRIORITY_LIST, "", $priority);
      html_form_select("FSTATUS", $BUG_STATUS_LIST, "", $status);

      if ($LOGIN_ID != 0)
      {
	print("<select name='FEMAIL'>");
	print("<option value='0'>Show: All Bugs</option>");
	print("<option value='1'");
	if ($femail)
	  print(" selected");
	print(">Show: My Bugs</option>");
	print("</select>");
      }
      else
        print("Show: All&nbsp;Bugs");

      if ($project_id < 0)
      {
	print("&nbsp;in&nbsp;");
        project_select("FPROJECTID", $project_id, "All Projects");
      }

      print("<br>\n"
           ."<small>Search supports 'and', 'or', 'not', and parenthesis. "
	   ."<a href='search-help.php'>More info...</a></small></p>\n");
      html_form_end();

      $matches = bug_search($search, "-status -priority id",
			    $project_id, $priority, $status, $femail);
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No bugs found.</p>\n");

	if (($priority || $status) && $search != "")
	{
	  $htmlsearch = htmlspecialchars($search, ENT_QUOTES);
	  print("<p><a href='$PHP_SELF?L+S0+Q" . urlencode($search)
	       ."'>Search for \"<i>$htmlsearch</i>\" in all bugs...</a></p>\n");
	}

	bug_footer();
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
                    "$PHP_SELF?L+Z$project_id+P$priority+S$status+E$femail+I",
                    "+Q" . urlencode($search));

      $columns = array("Id", "Priority", "Status", "Summary", "Version",
		       "Last Updated");
      if ($project_id < 0)
        $columns[sizeof($columns)] = "Project";

      $colcount = sizeof($columns);

      html_start_table($columns);

      for ($i = $index; $i < $end; $i ++)
      {
	$bug = new bug($matches[$i]);

	$date     = html_date($bug->modify_date);
	$summary  = htmlspecialchars($bug->summary, ENT_QUOTES);
	$summabbr = html_abbreviate($bug->summary, 80);
	$prtext   = $BUG_PRIORITY_SHORT[$bug->priority];
	$sttext   = $BUG_STATUS_SHORT[$bug->status];
	$link     = "<a href='$PHP_SELF?U$bug->id$options' "
		   ."title='Bug #$bug->id: $summary'>";

        if (file_exists("files/bug$bug->id"))
	  $summabbr .= " <img src=\"images/attachment.gif\" width=\"16\" "
	              ."height=\"16\" border=\"0\" align=\"middle\" "
	              ."alt=\"Has Attachments\">";

	if ($bug->is_published == 0)
	  $summabbr .= " <img src=\"images/private.gif\" width=\"16\" "
	              ."height=\"16\" border=\"0\" align=\"middle\" "
	              ."alt=\"Private\">";

	print("<tr><td nowrap>");
	if ($LOGIN_IS_ADMIN)
	  html_form_checkbox("ID_$bug->id");
	print("$link$bug->id</a></td>"
	     ."<td align=\"center\">$link$prtext</a></td>"
	     ."<td align=\"center\">$link$sttext</a></td>"
	     ."<td>$link$summabbr</a></td>"
	     ."<td align=\"center\">$link$bug->bug_version</a></td>"
	     ."<td align=\"center\" nowrap>$link$date</a></td>");
        if ($project_id < 0)
        {
	  $name = project_name($bug->project_id);
	  print("<td align=\"center\">$link$name</a></td>");
	}
	print("</tr>\n");

	if ($bug->status >= BUG_STATUS_PENDING)
	{
	  $textresult = db_query("SELECT * FROM comment "
				."WHERE ref_id = 'bug$bug->id' AND "
				."is_published = 1 "
				."ORDER BY id DESC LIMIT 1");
	  if ($textrow = db_next($textresult))
	  {
	    $textcount = $colcount - 3;
	    $name      = user_name($textrow['create_id']);
	    $contents  = html_abbreviate($textrow['contents'], 128);

	    print("<tr><td colspan=\"3\">&nbsp;</td>"
	         ."<td colspan=\"$textcount\">$name: <tt>$contents</tt>"
	         ."</td></tr>\n");

	    db_free($textresult);
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
	projversion_select($project_id, "bug_version", "", "No Change",
	                   "Version: ");
	projversion_select($project_id, "fix_version", "", "No Change",
	                   "Fixed In: ");
	user_select("developer_id", 0, TRUE, "No Change", "Assigned To: ");
        html_form_select("message", $BUG_MESSAGES, "No Message");
        html_form_end(array("SUBMIT" => "-Modify Selected Bugs"));
        print("</div>");
      }

      html_paginate($index, $count, $LOGIN_PAGEMAX,
                    "$PHP_SELF?L+P$priority+S$status+E$femail+I",
                    "+Q" . urlencode($search));

      bug_footer();
      break;

  case 'U' : // Post new/modify existing Bug
      if ($LOGIN_ID == 0)
      {
        header("Location: $html_login_url?PAGE=" . urlencode("bugs.php?U$id$options"));
	return;
      }
      else if ($id == 0 && $project_id < 0)
      {
        // TODO: Add a project picker here instead of redirecting to the projects page
        header("Location: projects.php");
	return;
      }

      $bug = new bug($id);

      if ($id <= 0)
      {
	$action = "Create Bug";
	$title  = "Create Bug";
      }
      else
      {
	$action = "Save Bug #$id";
	$title  = "Bug #$id: $bug->summary";
      }

      if ($bug->id != $id)
      {
        bug_header($action);
	print("<p><b>Error:</b> Bug #$id was not found.</p>\n");
	bug_footer();
	exit();
      }

      if (!$LOGIN_IS_ADMIN && $LOGIN_ID != $bug->create_id &&
          !$bug->is_published)
      {
        bug_header($title);
	print("<p><b>Error:</b> You do not have permission to modify or view "
	     ."bug #$id.</p>\n");
	bug_footer();
	exit();
      }

      if ($id == 0 && $project_id >= 0)
      {
	// "project" is created above when we validate project_id...
	$bug->project_id   = $project->id;
	$bug->developer_id = $project->developer_id;
      }
      else if ($id > 0)
      {
        $project_id = $bug->project_id;
        $project    = new project($project_id);
      }

      if (html_form_validate())
      {
        // TODO: Fix file/text show/hide stuff
/*
	if (array_key_exists("FILE_ID", $_POST) &&
	    (int)$_POST["FILE_ID"] > 0 &&
	    array_key_exists("is_published", $_POST))
	{
	  $file_id = (int)$_POST["FILE_ID"];
	  $bugfile = new bugfile($file_id);

	  if ($bugfile->id == $file_id)
	  {
	    $bugfile->is_published = (int)$_POST["is_published"];
	    $bugfile->save();
	  }

	  header("Location: $PHP_SELF?L$bug->id$options");
	  exit();
	}

	if (array_key_exists("TEXT_ID", $_POST) &&
	    (int)$_POST["TEXT_ID"] > 0 &&
	    array_key_exists("is_published", $_POST))
	{
	  $text_id = (int)$_POST["TEXT_ID"];
	  $bugtext = new bugtext($text_id);

	  if ($bugtext->id == $text_id)
	  {
	    $bugtext->is_published = (int)$_POST["is_published"];
	    $bugtext->save();
	  }

	  header("Location: $PHP_SELF?L$bug->id$options");
	  exit();
	}
*/

        $havedata = $bug->loadform();

	if ($id == 0 && !array_key_exists("contents", $_POST))
	  $havedata = 0;
      }
      else
      {
        $bug->validate();

        $havedata = 0;
      }

      if ($havedata)
      {
        if (!$bug->save())
	{
	  bug_header($title);
	  print("<p>Unable to save bug.</p>\n");
	  bug_footer();
	  exit();
	}

	if (array_key_exists("text", $_POST) ||
	    array_key_exists("message", $_POST))
	{
	  // Add text...
	  if (($contents = $bug->add_text()) === FALSE)
	  {
	    bug_header($title);
	    print("<p>Unable to save text to bug.</p>\n");
	    bug_footer();
	    exit();
	  }
	}
	else
	  $contents = "";

        if (array_key_exists("file", $_FILES))
	{
	  // Add file...
	  if (($file = $bug->add_file()) === FALSE)
	  {
	    bug_header($title);
	    print("<p>Unable to save file to bug.</p>\n");
	    bug_footer();
	    exit();
	  }
	}
	else
	  $file = "";

	if ($id <= 0)
	  $bug->notify_users($contents, $file, "");
	else
	  $bug->notify_users($contents, $file);

	header("Location: $PHP_SELF?U$bug->id$options");
      }
      else
      {
        bug_header($title);
	print($buglinks);

	if ($REQUEST_METHOD == "POST")
	{
	  print("<p><b>Error:</b> Please fill in the fields as "
	       ."<span class=\"invalid\">marked</span> and resubmit.</p>\n"
	       ."<hr noshade>\n");
	}
	else if ($id <= 0)
	  print("<p>Please use this form to report all bugs and request "
	       ."features in the software hosted on this site. General usage "
	       ."and compilation questions should be directed to the project "
	       ."mailing lists. When reporting bugs, please be sure to include "
	       ."the operating system, compiler, sample programs and/or files, "
	       ."and any other information you can about your problem. "
	       ."<i>Thank you</i> for helping us to improve our software!</p>\n"
	       ."<hr noshade>\n");

        $bug->form($action, $options);

	bug_footer();
      }
      break;
}

//
// End of "$Id: bugs.php 143 2014-04-14 02:16:36Z msweet $".
//
?>
