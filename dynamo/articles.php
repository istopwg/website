<?php
//
// "$Id$"
//
// Account management page...
//

//
// Include necessary headers...
//

include_once "phplib/db-article.php";


//
// 'article_header()' - Show standard article page header...
//

function
article_header($title, $id = 0)
{
  if ($id)
  {
    $article = new article($id);

    site_header($title, $article->title);
  }
  else
    site_header($title);
}


//
// 'article_footer()' - Show standard article page footer...
//

function
article_footer()
{
  site_footer();
}


// Get command-line options...
//
// Usage: article.php [operation] [options]
//
// Operations:
//
// L         = List
// U#        = Modify article #
//
// Options:
//
// I#        = Set first article item
// Qtext     = Set search text

$search = "";
$index  = 0;

if ($argc)
{
  $op = $argv[0][0];
  $id = (int)substr($argv[0], 1);

  if ($op != 'D' && $op != 'L' && $op != 'U')
  {
    site_header("Articles");
    print("<p>Bad command '$op'.</p>\n");
    article_footer();
    exit();
  }

  if (($op == 'D' || $op == 'U') && $id)
  {
    $article = new article($id);

    if ($article->id != $id)
    {
      site_header("Articles");
      print("<p>Articles article #$id does not exist.</p>\n");
      article_footer();
      exit();
    }
  }

  for ($i = 1; $i < $argc; $i ++)
  {
    $option = substr($argv[$i], 1);

    switch ($argv[$i][0])
    {
      case 'I' : // Set first article item
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
	  site_header("Articles");
	  print("<p>Bad option '$argv[$i]'.</p>\n");
	  article_footer();
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
  case 'D' : // Delete
      if (!$LOGIN_IS_ADMIN && !$LOGIN_IS_OFFICER)
      {
	site_header("Articles");
	print("<p>You do not have permission to access this page.</p>\n");
	article_footer();
	exit(0);
      }

      $article = new article($id);

      if ($article->id != $id)
      {
	site_header("Articles");
	print("<p>Article #$id does not exist.</p>\n");
	article_footer();
	exit();
      }

      if (!$id)
      {
        header("Location: $PHP_SELF?L$options");
        exit();
      }

      if ($article->loadform() && array_key_exists("SUBMIT", $_POST))
      {
        $article->delete();
        header("Location: $PHP_SELF?L$options");
      }
      else
      {
        article_header("Delete Article", $id);

        print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

        html_form_start("$PHP_SELF?U$id$options");

        $article->view("", 2, FALSE);

        // Submit
        html_form_end(array("SUBMIT" => "+Confirm Delete"));

        article_footer();
      }
      break;

  case 'L' : // View/list
      // List article...
      article_header("Articles");

      if ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER)
        print("<p align=\"right\"><a class=\"btn btn-primary\" href=\"$PHP_SELF?U$options\">Create Article</a></p>\n");

      html_form_start("$PHP_SELF?L", TRUE, FALSE, TRUE);
      html_form_search("search", "Search Articles", $search);
      html_form_end(array("SUBMIT" => "-Search"));

      $matches = article_search($search);
      $count   = sizeof($matches);

      if ($count == 0)
      {
	print("<p>No article found.</p>\n");

	article_footer();
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
	print("<p>1 article article found:</p>\n");
      else if ($count <= $LOGIN_PAGEMAX)
	print("<p>$count article articles found:</p>\n");
      else
	print("<p>$count article articles found, showing $start to $end:</p>\n");

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      for ($i = $start - 1; $i < $end; $i ++)
      {
	$article = new article($matches[$i]);

	if ($article->id != $matches[$i])
	  continue;

        $article->view($options);
      }

      html_end_table();

      html_paginate($index, $count, $LOGIN_PAGEMAX, "$PHP_SELF?L+I",
                    "+Q" . urlencode($search));

      article_footer();
      break;

  case 'U' : // Update/create
      if (!$LOGIN_IS_ADMIN && !$LOGIN_IS_OFFICER)
      {
	if ($LOGIN_ID == 0)
	{
	  header("Location: $html_login_url");
	  exit(0);
	}

	site_header("Articles");
	print("<p>You do not have permission to access this page.</p>\n");
	article_footer();
	exit(0);
      }

      $article = new article($id);

      if ($article->id != $id)
      {
	site_header("Articles");
	print("<p>Article #$id does not exist.</p>\n");
	article_footer();
	exit();
      }

      if ($article->loadform() && array_key_exists("SUBMIT", $_POST))
      {
        $article->save();
        header("Location: $PHP_SELF?L$options");
      }
      else
      {
        if ($id == 0)
          article_header("Create Article");
        else
          article_header("Modify Article", $id);

        print("<p><a class=\"btn btn-default\" href=\"$PHP_SELF?L$options\"><span class=\"glyphicon glyphicon-arrow-left\"></span> Back to List</a></p>\n");

        if ($REQUEST_METHOD == "POST" && array_key_exists("SUBMIT", $_POST))
          html_show_error("Please correct the highlighted fields.");

	$article->form($options);

        if (array_key_exists("contents", $_POST))
          $article->view("", 2, FALSE);
        article_footer();
      }
      break;
}

//
// End of "$Id$".
//
?>
