<?php
//
// Organization management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-organization.php";


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
// 'organizations_footer()' - Show standard organization page footer...
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
// B         = Batch update selected organizations
// L         = List
// U#        = Modify organization #
// X         = Purge disabled organizations
//
// Options:
//
// I#        = Set first organization
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
    $organization = new organization($id);

    if ($organization->id != $id)
    {
      site_header("Manage Organizations");
      print("<p>Organization #$id does not exist.</p>\n");
      organizations_footer();
      exit();
    }
  }

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'I' : // Set first organization
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
      // Batch update status of organizations...
      if (html_form_validate() && array_key_exists("STATUS", $_POST))
      {
	$status = (int)$_POST["STATUS"];

        db_query("BEGIN TRANSACTION");

        reset($_POST);
        while (list($key, $val) = each($_POST))
          if (substr($key, 0, 3) == "ID_")
	  {
	    $id = (int)substr($key, 3);
	    $organization = new organization($id);
	    if ($organization->id == $id)
	    {
	      $organization->status = $status;
	      $organization->save();
	    }
	  }

        db_query("COMMIT TRANSACTION");
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // View/list
      // List organizations...
      organizations_header("Manage Organizations");

      print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$PHP_SELF?U$options\">Create Organization</a></p>\n");

      html_form_start("$PHP_SELF?L", TRUE, FALSE, TRUE);
      html_form_search("search", "Search Organizations", $search);
      html_form_end(array("SUBMIT" => "-Search"));

      $matches = organization_search($search, "name");
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
	print("<p>1 organization found:</p>\n");
      else if ($count <= $LOGIN_PAGEMAX)
	print("<p>$count organizations found:</p>\n");
      else
	print("<p>$count organizations found, showing $start to $end:</p>\n");

      html_form_start("$PHP_SELF?B$options", TRUE);

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      html_start_table(array("Name", "Domain", "Status"));

      for ($i = $start - 1; $i < $end; $i ++)
      {
	$organization = new organization($matches[$i]);

	if ($organization->id != $matches[$i])
	  continue;

	$name   = htmlspecialchars($organization->name, ENT_QUOTES);
	$domain = htmlspecialchars($organization->domain, ENT_QUOTES);
	$status = $ORGANIZATION_STATUSES[$organization->status];

	print("<tr><td nowrap>");
	html_form_checkbox("ID_$organization->id");
	print("<a href=\"$PHP_SELF?U$organization->id$options\">$name</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$domain</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$organization->id$options\">$status</a></td>"
	     ."</tr>\n");
      }

      html_end_table();

      print("<p align=\"center\">");
      html_form_select("STATUS", $ORGANIZATION_STATUSES, "-- Choose --");
      html_form_end(array("SUBMIT" => "--Set Status of Checked Organizations"));
      print("</p>\n");

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      organizations_footer();
      break;

  case 'U' : // Update/create
      $organization = new organization($id);

      if ($organization->id != $id)
      {
	site_header("Manage Organizations");
	print("<p>Organization #$id does not exist.\n");
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
        organizations_header("Modify Organization", $id);

        print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

        if ($REQUEST_METHOD == "POST")
          html_show_error("Please correct the highlighted fields.");

	$organization->form($options);

        organizations_footer();
      }
      break;
}

?>
