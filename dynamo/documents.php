<?php
//
// Document management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-document.php";


if ($LOGIN_ID == 0)
{
  header("Location: $html_login_url");
  exit(0);
}


//
// 'documents_header()' - Show standard document page header...
//

function
documents_header($title, $id = 0)
{
  if ($id)
  {
    $document = new document($id);
    site_header($title, $document->display_name());
  }
  else
    site_header($title);
}


// Get command-line options...
//
// Usage: documents.php [operation] [options]
//
// Operations:
//
// B         = Batch update selected documents
// L         = List
// U#        = Modify document #
//
// Options:
//
// I#        = Set first document
// Qtext     = Set search text

$search = "";
$index  = 0;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'B' && $op != 'L' && $op != 'U')
  {
    site_header("Manage Documents");
    html_show_error("Bad command '$op'.");
    site_footer();
    exit();
  }

  if ($op == 'U' && $id)
  {
    $document = new document($id);

    if ($document->id != $id)
    {
      site_header("Manage Documents");
      html_show_error("Document #$id does not exist.");
      site_footer();
      exit();
    }
  }

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'I' : // Set first document
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
	  site_header("Manage Documents");
	  html_show_error("Bad option '$argv[$i]'.");
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

if ($REQUEST_METHOD == "POST")
{
  if (array_key_exists("SEARCH", $_POST))
    $search = $_POST["SEARCH"];
}

$options = "+I$index+Q" . urlencode($search);

switch ($op)
{
  case 'B' : // Batch update
      // Batch update status of documents...
      if (html_form_validate() && array_key_exists("STATUS", $_POST))
      {
	$status = (int)$_POST["STATUS"];

        db_query("BEGIN TRANSACTION");

        reset($_POST);
        while (list($key, $val) = each($_POST))
          if (substr($key, 0, 3) == "ID_")
	  {
	    $id = (int)substr($key, 3);
	    $document = new document($id);
	    if ($document->id == $id)
	    {
	      $document->status = $status;
	      $document->save();
	    }
	  }

        db_query("COMMIT TRANSACTION");
      }

      header("Location: $PHP_SELF?L$options");
      break;

  case 'L' : // View/list
      // List documents...
      documents_header("Manage Documents");

      print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$PHP_SELF?U$options\">Create Document</a></p>\n");

      html_form_start("$PHP_SELF?L", TRUE, FALSE, TRUE);
      html_form_search("search", "Search Documents", $search);
      html_form_end(array("SUBMIT" => "-Search"));

      $matches = document_search($search, "title");
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No documents found.</p>\n");

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

      if ($count == 1)
	print("<p>1 document found:</p>\n");
      else if ($count <= $LOGIN_PAGEMAX)
	print("<p>$count documents found:</p>\n");
      else
	print("<p>$count documents found, showing $start to $end:</p>\n");

      html_form_start("$PHP_SELF?B$options", TRUE);

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      html_start_table(array("Name", "Status"));

      for ($i = $start - 1; $i < $end; $i ++)
      {
	$document = new document($matches[$i]);

	if ($document->id != $matches[$i])
	  continue;

	$name    = $document->display_name();
	$status  = $DOCUMENT_STATUSES[$document->status];

	print("<tr><td>");
	html_form_checkbox("ID_$document->id");
	print("<a href=\"$PHP_SELF?U$document->id$options\">$name</a></td>"
	     ."<td><a href=\"$PHP_SELF?U$document->id$options\">$status</a></td>"
	     ."</tr>\n");
      }

      html_end_table();

      print("<p align=\"center\">");
      html_form_select("STATUS", $DOCUMENT_STATUSES, "-- Choose --");
      html_form_end(array("SUBMIT" => "--Set Status of Checked Documents"));
      print("</p>\n");

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      site_footer();
      break;

  case 'U' : // Update/create
      $document = new document($id);

      if ($document->id != $id)
      {
	site_header("Manage Documents");
	html_show_error("Document #$id does not exist.");
	site_footer();
	exit();
      }

      if ($document->create_id != $LOGIN_ID && !$LOGIN_IS_ADMIN && !$LOGIN_IS_OFFICER && !$LOGIN_IS_EDITOR)
      {
	site_header("Manage Documents");
	html_show_error("You do not have permission to edit document #$id.");
	site_footer();
	exit();
      }

      if ($document->loadform($error))
      {
        $document->save();
        header("Location: $PHP_SELF?L$options");
      }
      else
      {
        if ($id)
	  documents_header("Modify Document", $id);
	else
	  site_header("Create Document");

        print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

        if ($REQUEST_METHOD == "POST")
          html_show_error($error);

	$document->form($options);

        site_footer();
      }
      break;
}
?>
