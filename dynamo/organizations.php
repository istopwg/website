<?php
//
// "$Id$"
//
// Account management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-user.php";


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
// 'organizations_header()' - Show standard organization page header...
//

function
organizations_header($title, $id = 0)
{
  if ($id)
    site_header($title, organization_name($id));
  else
    site_header($title);
}


//
// 'organizations_footer()' - Show standard account page footer...
//

function
organizations_footer()
{
  site_footer();
}


// Get command-line options...
//
// Usage: organizations.php [operation] [options]
//
// Operations:
//
// B         = Batch update selected users
// L         = List
// U#        = Modify user #
// X         = Purge disabled users
//
// Options:
//
// I#        = Set first user
// Qtext     = Set search text

$search = "";
$index  = 0;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U' && $op != 'X')
  {
    site_header("Manage Organizations");
    print("<p>Bad command '$op'.</p>\n");
    organizations_footer();
    exit();
  }

  if ($op == 'U' && $id)
  {
    $organization = new user($id);

    if ($organization->id != $id)
    {
      site_header("Manage Organizations");
      print("<p>Account #$id does not exist.</p>\n");
      organizations_footer();
      exit();
    }
  }

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'I' : // Set first user
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
	  site_header("Manage Organizations");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  organizations_footer();
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
      // Disable/enable/expire/etc. organizations...
      if (html_form_validate() && array_key_exists("OP", $_POST))
      {
	$op = $_POST["OP"];

        db_query("BEGIN TRANSACTION");

        reset($_POST);
        while (list($key, $val) = each($_POST))
          if (substr($key, 0, 3) == "ID_")
	  {
	    $id = (int)substr($key, 3);

            if ($op == "ban")
              db_query("UPDATE user SET status = 0 WHERE id = $id");
            else if ($op == "enable")
              db_query("UPDATE user SET status = 2 WHERE id = $id");
            else if ($op == "delete")
              db_query("UPDATE user SET status = 3 WHERE id = $id");
	  }

        db_query("COMMIT TRANSACTION");
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'X' : // Purge dead organizations...
      db_query("DELETE FROM user WHERE status = 1");
      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // View/list
      // List organizations...
      organizations_header("Manage Organizations");

      html_form_start("$PHP_SELF?L", TRUE);
      html_form_search("search", "Search Organizations", $search);
      html_form_end(array("SUBMIT" => "-Search"));

      $organization    = new user();
      $matches = organization_search($search, 0, "name");
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No organizations found.</p>\n");

	organizations_footer();
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
	print("<p>1 user found:</p>\n");
      else if ($count <= $LOGIN_PAGEMAX)
	print("<p>$count users found:</p>\n");
      else
	print("<p>$count users found, showing $start to $end:</p>\n");

      html_form_start("$PHP_SELF?B$options", TRUE);

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      html_start_table(array("Name", "Organization", "EMail", "Roles", "Status"));

      for ($i = $start - 1; $i < $end; $i ++)
      {
	$organization->load($matches[$i]);

	if ($organization->id != $matches[$i])
	  continue;

	$name   = htmlspecialchars($organization->name, ENT_QUOTES);
	$org    = htmlspecialchars(organization_name($organization->organization_id), ENT_QUOTES);
	$email  = htmlspecialchars($organization->email, ENT_QUOTES);
	$roles = "";
	if ($organization->is_admin)
	  $roles .= ", Admin";
	if ($organization->is_editor)
	  $roles .= ", Editor";
	if ($organization->is_member)
	  $roles .= ", Member";
	if ($organization->is_reviewer)
	  $roles .= ", Reviewer";
	if ($organization->is_submitter)
	  $roles .= ", Submitter";
	if ($roles == "")
	  $roles = "None";
	else
	  $roles = substr($roles, 2);
	$status = $USER_STATUSES[$organization->status];

	print("<tr><td nowrap>");
	html_form_checkbox("ID_$organization->id");
	print("<a href=\"$PHP_SELF?U$organization->id$options\">$name</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$org</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$email</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$roles</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$status</a></td>"
	     ."</tr>\n");
      }

      html_end_table();

      print("<div class=\"form-group\">");
      html_form_select("OP", array("ban" => "Ban", "delete" => "Delete",
                                   "enable" => "Enable"), "-- Choose --");
      html_form_end(array("SUBMIT" => "-Checked Organizations"));
      print("</div>\n");

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      organizations_footer();
      break;

  case 'U' : // Update/create
      $organization = new user($id);

      if ($organization->id != $id)
      {
	site_header("Manage Organizations");
	print("<p>Account #$id does not exist.\n");
	organizations_footer();
	exit();
      }

      if ($organization->loadform())
      {
        $organization->save();
        header("Location: $PHP_SELF?L$options");
      }
      else
      {
        organizations_header("Modify User", $id);

        print("<p><a class=\"btn btn-default btn-xs\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

	$organization->form($options);

        organizations_footer();
      }
      break;
}

//
// End of "$Id$".
//
?>
