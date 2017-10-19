<?php
//
// Workgroup management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-workgroup.php";


if ($LOGIN_ID == 0)
{
  header("Location: $html_login_url");
  exit(0);
}

if (!$LOGIN_IS_ADMIN)
{
  header("Location: ${html_path}index.html");
  exit(0);
}


//
// 'workgroups_header()' - Show standard workgroup page header...
//

function
workgroups_header($title, $id = 0)
{
  if ($id)
    site_header($title, workgroup_name($id));
  else
    site_header($title);
}


//
// 'workgroups_footer()' - Show standard workgroup page footer...
//

function
workgroups_footer()
{
  site_footer();
}


// Get command-line options...
//
// Usage: workgroups.php [operation] [options]
//
// Operations:
//
// B         = Batch update selected workgroups
// L         = List
// U#        = Modify workgroup #
//
// Options:
//
// I#        = Set first workgroup
// Qtext     = Set search text

$search = "";
$index  = 0;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U')
  {
    site_header("Manage Workgroups");
    print("<p>Bad command '$op'.</p>\n");
    workgroups_footer();
    exit();
  }

  if ($op == 'U' && $id)
  {
    $workgroup = new workgroup($id);

    if ($workgroup->id != $id)
    {
      site_header("Manage Workgroups");
      print("<p>Workgroup #$id does not exist.</p>\n");
      workgroups_footer();
      exit();
    }
  }

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'I' : // Set first workgroup
          $index = (int)$option;
	  if ($index < 0)
	    $index = 0;
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
      default :
	  site_header("Manage Workgroups");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  workgroups_footer();
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

if ($REQUEST_METHOD == "POST")
{
  if (array_key_exists("SEARCH", $_POST))
    $search = $_POST["SEARCH"];
}

$options = "+I$index+Q" . urlencode($search);

switch ($op)
{
  case 'B' : // Batch update
      // Batch update status of workgroups...
      if (html_form_validate() && array_key_exists("STATUS", $_POST))
      {
	$status = (int)$_POST["STATUS"];

        db_query("BEGIN TRANSACTION");

        reset($_POST);
        while (list($key, $val) = each($_POST))
          if (substr($key, 0, 3) == "ID_")
	  {
	    $id = (int)substr($key, 3);
	    $workgroup = new workgroup($id);
	    if ($workgroup->id == $id)
	    {
	      $workgroup->status = $status;
	      $workgroup->save();
	    }
	  }

        db_query("COMMIT TRANSACTION");
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // View/list
      // List workgroups...
      workgroups_header("Manage Workgroups");

      print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$PHP_SELF?U$options\">Create Workgroup</a></p>\n");

      html_form_start("$PHP_SELF?L", TRUE, FALSE, TRUE);
      html_form_search("search", "Search Workgroups", $search);
      html_form_end(array("SUBMIT" => "-Search"));

      $matches = workgroup_search($search, "name");
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No workgroups found.</p>\n");

	workgroups_footer();
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

      if ($count == 1)
	print("<p>1 workgroup found:</p>\n");
      else if ($count <= $LOGIN_PAGEMAX)
	print("<p>$count workgroups found:</p>\n");
      else
	print("<p>$count workgroups found, showing $start to $end:</p>\n");

      html_form_start("$PHP_SELF?B$options", TRUE);

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      html_start_table(array("Name", "Home Page", "Status"));

      for ($i = $start - 1; $i < $end; $i ++)
      {
	$workgroup = new workgroup($matches[$i]);

	if ($workgroup->id != $matches[$i])
	  continue;

	$name   = htmlspecialchars($workgroup->name, ENT_QUOTES);
	$wwwdir = htmlspecialchars($workgroup->wwwdir, ENT_QUOTES);
	$status = $WORKGROUP_STATUSES[$workgroup->status];

	print("<tr><td nowrap>");
	html_form_checkbox("ID_$workgroup->id");
	print("<a href=\"$PHP_SELF?U$workgroup->id$options\">$name</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$workgroup->id$options\">$SITE_URL/$wwwdir</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$workgroup->id$options\">$status</a></td>"
	     ."</tr>\n");
      }

      html_end_table();

      print("<p align=\"center\">");
      html_form_select("STATUS", $WORKGROUP_STATUSES, "-- Choose --");
      html_form_end(array("SUBMIT" => "--Set Status of Checked Workgroups"));
      print("</p>\n");

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      workgroups_footer();
      break;

  case 'U' : // Update/create
      $workgroup = new workgroup($id);

      if ($workgroup->id != $id)
      {
	site_header("Manage Workgroups");
	print("<p>Workgroup #$id does not exist.\n");
	workgroups_footer();
	exit();
      }

      if ($workgroup->loadform())
      {
        $workgroup->save();
        header("Location: $PHP_SELF?L$options");
      }
      else
      {
        workgroups_header("Modify Workgroup", $id);

        print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

        if ($REQUEST_METHOD == "POST")
          html_show_error("Please correct the highlighted fields.");

	$workgroup->form($options);

        workgroups_footer();
      }
      break;
}
?>
