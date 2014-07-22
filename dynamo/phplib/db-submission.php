<?php
//
// "$Id: db-article.php 101 2013-08-22 14:03:34Z msweet $"
//
// Class for the article table.
//
// Contents:
//
//   article::article()      - Create an Article object.
//   article::clear()	     - Initialize a new an Article object.
//   article::delete()	     - Delete an Article object.
//   article::form()	     - Display a form for an Article object.
//   article::load()	     - Load an Article object.
//   article::loadform()     - Load an Article object from form data.
//   article::save()	     - Save an Article object.
//   article::validate()     - Validate the current Article object values./
//   article::view()         - View an article.
//   article::view_summary() - View a summary of the article.
//   article_search()	     - Get a list of Article IDs.
//

include_once "site.php";
include_once "db.php";


class article
{
  //
  // Instance variables...
  //

  var $id;
  var $project_id, $project_id_valid;
  var $is_published;
  var $title, $title_valid;
  var $summary, $summary_valid;
  var $contents, $contents_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'article::article()' - Create an Article object.
  //

  function				// O - New Article object
  article($id = 0)			// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'article::clear()' - Initialize a new an Article object.
  //

  function
  clear()
  {
    $this->id           = 0;
    $this->project_id   = -1;
    $this->is_published = 0;
    $this->title        = "";
    $this->summary      = "";
    $this->contents     = "";
    $this->create_date  = "";
    $this->create_id    = 0;
    $this->modify_date  = "";
    $this->modify_id    = 0;
  }


  //
  // 'article::delete()' - Delete an Article object.
  //

  function
  delete()
  {
    db_query("DELETE FROM article WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'article::form()' - Display a form for an Article object.
  //

  function
  form($options = "")			// I - Search/etc. options
  {
    global $LOGIN_ID, $LOGIN_IS_ADMIN, $PHP_SELF;


    if ($this->id <= 0)
      $action = "Create Article";
    else
      $action = "Modify Article #$this->id";

    print("<h2>$action</h2>\n");
    html_form_start("$PHP_SELF?U$this->id$options");

    // project_id
    html_form_field_start("project_id", "Project", $this->project_id_valid);
    project_select("project_id", $this->project_id,
                   $this->id < 0 ? "Select Project" : "");
    html_form_field_end();

    if ($LOGIN_IS_ADMIN)
    {
      // is_published
      html_form_field_start("is_published", "Visibility");
      html_select_is_published($this->is_published);
      html_form_field_end();
    }

    // title
    html_form_field_start("title", "Title", $this->title_valid);
    html_form_text("title", "Title of Article", $this->title);
    html_form_field_end();

    // summary
    html_form_field_start("summary", "Summary", $this->summary_valid);
    html_form_text("summary", "Summary of Article", $this->summary);
    html_form_field_end();

    // contents
    html_form_field_start("contents", "Contents", $this->contents_valid);
    html_form_text("contents", "Contents of article.", $this->contents,
                   "The contents may contain:\n"
		  ."! Heading\n"
		  ."!! Sub-Heading\n"
		  ."- Unordered list\n"
		  ."1. Ordered list\n"
		  ."\" Blockquote\n"
		  ."SPACE Preformatted text\n"
		  ."[[URL|text of link]]", 20);
    html_form_field_end();

    // Submit
    html_form_end(array("SUBMIT" => "+$action", "PREVIEW" => "Preview Article"));
  }


  //
  // 'article::load()' - Load an Article object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  load($id)				// I - Object ID
  {
    $this->clear();

    $result = db_query("SELECT * FROM article WHERE id = $id");
    if (db_count($result) != 1)
      return (FALSE);

    $row = db_next($result);
    $this->id           = $row["id"];
    $this->project_id   = $row["project_id"];
    $this->is_published = $row["is_published"];
    $this->title        = $row["title"];
    $this->summary      = $row["summary"];
    $this->contents     = $row["contents"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return ($this->validate());
  }


  //
  // 'article::loadform()' - Load an Article object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST, $LOGIN_IS_ADMIN;


    if (!html_form_validate())
      return (FALSE);

    if (!$LOGIN_IS_ADMIN && $this->id == 0)
      $this->is_published = 0;
    else if (array_key_exists("is_published", $_POST))
      $this->is_published = (int)$_POST["is_published"];

    if (array_key_exists("project_id", $_POST) &&
        preg_match("/^p[0-9]+\$/", $_POST["project_id"]))
      $this->project_id = (int)substr($_POST["project_id"], 1);

    if (array_key_exists("title", $_POST))
      $this->title = trim($_POST["title"]);

    if (array_key_exists("summary", $_POST))
      $this->summary = trim($_POST["summary"]);

    if (array_key_exists("contents", $_POST))
      $this->contents = trim($_POST["contents"]);

    return ($this->validate());
  }


  //
  // 'article::save()' - Save an Article object.
  //

  function				// O - TRUE if OK, FALSE otherwise
  save()
  {
    global $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
    {
      return (db_query("UPDATE article "
                      ." SET is_published = $this->is_published"
                      .", project_id = $this->project_id"
                      .", title = '" . db_escape($this->title) . "'"
                      .", summary = '" . db_escape($this->summary) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", modify_date = '" . db_escape($this->modify_date) . "'"
                      .", modify_id = $this->modify_id"
                      ." WHERE id = $this->id") !== FALSE);
    }
    else
    {
      $this->create_date = $this->modify_date;
      $this->create_id   = $this->modify_id;

      if (db_query("INSERT INTO article VALUES"
                  ."(NULL"
		  .", $this->project_id"
                  .", $this->is_published"
                  .", '" . db_escape($this->title) . "'"
                  .", '" . db_escape($this->summary) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", '" . db_escape($this->create_date) . "'"
                  .", $this->create_id"
                  .", '" . db_escape($this->modify_date) . "'"
                  .", $this->modify_id"
                  .")") === FALSE)
        return (FALSE);

      $this->id = db_insert_id();
    }

    return (TRUE);
  }


  //
  // 'article::validate()' - Validate the current Article object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    global $REQUEST_METHOD;


    $valid = TRUE;

    if ($this->project_id < 0 && $REQUEST_METHOD == "POST")
    {
      $this->project_id_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->project_id_valid = TRUE;

    if ($this->title == "" && $REQUEST_METHOD == "POST")
    {
      $this->title_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->title_valid = TRUE;

    if ($this->summary == "" && $REQUEST_METHOD == "POST")
    {
      $this->summary_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->summary_valid = TRUE;

    if ($this->contents == "" && $REQUEST_METHOD == "POST")
    {
      $this->contents_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contents_valid = TRUE;

    return ($valid);
  }


  //
  // 'article::view()' - View an article.
  //

  function
  view($show_edit = FALSE, $options = "", $show_link = TRUE)
  {
    global $html_path, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_NAME, $html_textarea_width;


    if ($options == "")
      $options = "+Z$this->project_id";

    if ($show_edit)
      $edit = " <a class=\"btn\" href=\"${html_path}/blog.php?"
             ."U$this->id$options\">Edit</a>";
    else
      $edit = "";

    $temp     = db_query("SELECT id FROM comment WHERE "
			."ref_id = 'article$this->id' AND is_published = 1 "
			."ORDER BY create_date");
    $count    = db_count($temp);

    if ($show_link)
    {
      if ($row = db_next($temp))
        $cid = "C$row[id]";
      else
        $cid = "POST_COMMENT";

      $link    = "<a href=\"$html_path/blog.php?L$this->id$options\">";
      $btnlink = "<a class=\"btn\" "
                ."href=\"$html_path/blog.php?L$this->id$options#$cid\">";
      $endlink = "</a>";
    }
    else
    {
      $link    = "";
      $btnlink = "";
      $endlink = "";
    }

    $title    = htmlspecialchars($this->title);
    $contents = html_format($this->contents);
    $date     = html_date($this->modify_date);

    if (!$this->is_published)
      $title .= "&nbsp;<i class=\"icon-ban-circle\"></i>";

    if ($count == 0)
    {
      if ($show_link)
        $comments = "${btnlink}Post&nbsp;comment$endlink";
      else
        $comments = "No&nbsp;comments";
    }
    else if ($count == 1)
      $comments = "${btnlink}1&nbsp;comment$endlink";
    else
      $comments = "${btnlink}$count&nbsp;comments$endlink";

    print("<h2>$title <small>$date</small>$edit</h2>\n"
         ."$contents\n<p>$comments</p>\n");

    if (!$show_link)
    {
      if ($LOGIN_IS_ADMIN)
        html_form_start("$html_path/blog.php?B$this->id$options", TRUE);

      $results = db_query("SELECT * FROM comment WHERE "
                         ."ref_id = 'article$this->id' "
                         ."ORDER BY create_date;");
      while ($row = db_next($results))
      {
        if (!$row["is_published"] && !$LOGIN_IS_ADMIN)
          continue;

        $name     = user_name($row["create_id"]);
        $contents = html_text($row["contents"]);
        $date     = html_date($row["create_date"]);

	if ($LOGIN_IS_ADMIN)
	{
	  if ($row["is_published"])
	    $button = "<input type=\"submit\" "
		     ."name=\"HIDE_COMMENT_ID_$row[id]\" value=\"Hide\"> ";
	  else
	    $button = "<input type=\"submit\" "
		     ."name=\"SHOW_COMMENT_ID_$row[id]\" value=\"Show\"> ";
        }
        else
          $button = "";

        if (!$row["is_published"])
          $contents = "<del>$contents</del>";

        print("<h3><a name=\"C$row[id]\">$button $name "
             ."<small>$date</small></a></h3>\n"
             ."<p>$contents</p>\n");
      }
      db_free($results);

      if ($LOGIN_IS_ADMIN)
        html_form_end();

      if ($LOGIN_ID)
      {
	print("<h3><a name=\"POST_COMMENT\">$LOGIN_NAME "
	     ."<small>Today</small></a></h3>\n");
	html_form_start("$html_path/blog.php?C$this->id$options", TRUE);
	html_form_text("comment", "Your comment here.", "", "", 4);
	print("<br>\n");
	html_form_end(array("SUBMIT" => "-Post Comment"));
      }
    }
  }


  //
  // 'article::view_summary()' - View a summary of the article.
  //

  function
  view_summary($show_checkbox = FALSE,
               $options = "",
               $show_link = TRUE)
  {
    global $html_path;


    if ($options == "")
      $options = "+Z$this->project_id";

    if ($show_checkbox)
      $edit = "<input type=\"checkbox\" name=\"ID_$this->id\">&nbsp;";
    else
      $edit = "";

    $temp     = db_query("SELECT id FROM comment WHERE "
			."ref_id = 'article$this->id' AND is_published = 1 "
			."ORDER BY create_date");
    $count    = db_count($temp);

    if ($show_link)
    {
      if ($row = db_next($temp))
        $cid = "C$row[id]";
      else
        $cid = "POST_COMMENT";

      $link    = "<a href=\"$html_path/blog.php?L$this->id$options\">";
      $btnlink = "<a class=\"btn\" "
                ."href=\"$html_path/blog.php?L$this->id$options#$cid\">";
      $endlink = "</a>";
    }
    else
    {
      $link    = "";
      $btnlink = "";
      $endlink = "";
    }

    $title   = htmlspecialchars($this->title);
    $summary = htmlspecialchars($this->summary);
    $date    = html_date($this->modify_date);
    $temp    = db_query("SELECT id FROM comment WHERE "
		       ."ref_id = 'article$this->id' AND is_published = 1;");
    $count   = db_count($temp);

    if (!$this->is_published)
      $title .= "&nbsp;<i class=\"icon-ban-circle\"></i>";

    if ($count == 0)
    {
      if ($show_link)
        $comments = "${btnlink}Post&nbsp;comment$endlink";
      else
        $comments = "No&nbsp;comments";
    }
    else if ($count == 1)
      $comments = "${btnlink}1&nbsp;comment$endlink";
    else
      $comments = "${btnlink}$count&nbsp;comments$endlink";

    print("<h2>$edit$title <small>$date</small></h2>\n"
         ."<p>$summary</p>\n<p>$comments</p>\n");
  }
}


//
// 'article_search()' - Get a list of Article IDs.
//

function				// O - Array of Article IDs
article_search($search = "",		// I - Search string
	       $order = "",		// I - Order fields
	       $project_id = 0,		// I - Project, if any
	       $is_published = 0)	// I - Only return published articles
{
  global $LOGIN_ID, $LOGIN_IS_ADMIN;


  $query  = "";
  $prefix = " WHERE ";

  if ($is_published)
  {
    $query .= "${prefix}is_published = 1";
    $prefix = " AND ";
  }
  else if (!$LOGIN_IS_ADMIN)
  {
    $query .= "${prefix}(is_published = 1 OR create_id = '"
	     . db_escape($LOGIN_ID) . "')";
    $prefix = " AND ";
  }

  if ($project_id > 0)
  {
    $query .= "${prefix}project_id = $project_id";
    $prefix = " AND ";
  }

  if ($search != "")
  {
    // Convert the search string to an array of words...
    $words = html_search_words($search);

    // Loop through the array of words, adding them to the query...
    $query .= "${prefix}(";
    $prefix = "";
    $next   = " OR";
    $logic  = "";

    reset($words);
    foreach ($words as $word)
    {
      $word = db_escape($word);

      if ($word == "or")
      {
	$next = ' OR';
	if ($prefix != '')
	  $prefix = ' OR';
      }
      else if ($word == "and")
      {
	$next = ' AND';
	if ($prefix != '')
	  $prefix = ' AND';
      }
      else if ($word == "not")
	$logic = ' NOT';
      else if (substr($word, 0, 8) == "creator:")
      {
	$word   = substr($word, 8);
	$query .= "$prefix$logic create_id LIKE \"$word\"";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 7) == "number:")
      {
	$number = (int)substr($word, 7);
	$query  .= "$prefix$logic id = $number";
	$prefix = $next;
	$logic  = '';
      }
      else if (substr($word, 0, 6) == "title:")
      {
	$word   = substr($word, 6);
	$query  .= "$prefix$logic title LIKE \"%$word%\"";
	$prefix = $next;
	$logic  = '';
      }
      else
      {
	$query .= "$prefix$logic (";
	$subpre = "";

	if (preg_match("/^[0-9]+\$/", $word))
	{
	  $query .= "${subpre}id = $word";
	  $subpre = " OR ";
	}

	$query .= "${subpre}title LIKE \"%$word%\"";
	$subpre = " OR ";
	$query .= "${subpre}summary LIKE \"%$word%\"";
	$query .= "${subpre}contents LIKE \"%$word%\"";

	$query .= ")";
	$prefix = $next;
	$logic  = '';
      }
    }

    $query .= ")";
  }

  if ($order != "")
  {
    // Separate order into array...
    $fields = explode(" ", $order);
    $prefix = " ORDER BY ";

    // Add ORDER BY stuff...
    foreach ($fields as $field)
    {
      if ($field[0] == '+')
	$query .= "${prefix}" . substr($field, 1);
      else if ($field[0] == '-')
	$query .= "${prefix}" . substr($field, 1) . " DESC";
      else
	$query .= "${prefix}$field";

      $prefix = ", ";
    }
  }

//  print("<p>$query</p>\n");

  // Do the query and convert the result to an array of objects...
  $result  = db_query("SELECT id FROM article$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}


//
// End of "$Id: db-article.php 101 2013-08-22 14:03:34Z msweet $".
//
?>
