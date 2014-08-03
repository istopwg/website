<?php
//
// Class for the article table.
//

include_once "site.php";
include_once "db-workgroup.php";


class article
{
  //
  // Instance variables...
  //

  var $id;
  var $workgroup_id, $workgroup_id_valid;
  var $title, $title_valid;
  var $contents, $contents_valid;
  var $url, $url_valid;
  var $create_date;
  var $create_id;
  var $modify_date;
  var $modify_id;


  //
  // 'article::article()' - Create an article object.
  //

  function				// O - New article object
  article($id = 0)				// I - ID, if any
  {
    if ($id > 0)
      $this->load($id);
    else
      $this->clear();
  }


  //
  // 'article::clear()' - Initialize a new an article object.
  //

  function
  clear()
  {
    global $LOGIN_ID;

    $this->id           = 0;
    $this->workgroup_id = 0;
    $this->title        = "";
    $this->contents     = "";
    $this->url          = "";
    $this->create_date  = "";
    $this->create_id    = $LOGIN_ID;
    $this->modify_date  = "";
    $this->modify_id    = $LOGIN_ID;
  }


  //
  // 'article::delete()' - Delete an article object.
  //

  function
  delete()
  {
    db_query("DELETE FROM article WHERE id=$this->id");
    $this->clear();
  }


  //
  // 'article::form()' - Display a form for an article object.
  //

  function
  form($options = "")			// I - Page options
  {
    global $PHP_SELF;


    if ($this->id <= 0)
      $action = "Create Article";
    else
      $action = "Save Changes";

    html_form_start("$PHP_SELF?U$this->id$options");

    html_form_field_start("title", "Title", $this->title_valid);
    html_form_text("title", "The title/summary of the article.", $this->title);
    html_form_field_end();

    html_form_field_start("contents", "Contents", $this->contents_valid);
    html_form_text("contents", "An abstract or short article to show on the home page.", $this->contents,
                   "Formatting/markup rules:\n\n"
                  ."! Header\n"
                  ."!! Sub-header\n"
                  ."- Unordered list\n"
                  ."* Unordered list\n"
                  ."1. Numbered list\n"
                  ."\" Blockquote\n"
                  ."SPACE preformatted text\n"
                  ."[[link||text label]]\n", 10);
    html_form_field_end();

    html_form_field_start("url", "Related URL", $this->url_valid);
    html_form_url("url", "http://www.example.com/article.html", $this->url);
    html_form_field_end();

    html_form_field_start("workgroup_id", "Workgroup");
    workgroup_select("workgroup_id", $this->workgroup_id, "None");
    html_form_field_end();

    // Submit
    html_form_end(array("SUBMIT" => "+$action", "PREVIEW" => "Preview"));
  }


  //
  // 'article::load()' - Load an article object.
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
    $this->workgroup_id = $row["workgroup_id"];
    $this->title        = $row["title"];
    $this->contents     = $row["contents"];
    $this->url          = $row["url"];
    $this->create_date  = $row["create_date"];
    $this->create_id    = $row["create_id"];
    $this->modify_date  = $row["modify_date"];
    $this->modify_id    = $row["modify_id"];

    db_free($result);

    return (TRUE);
  }


  //
  // 'article::loadform()' - Load an article object from form data.
  //

  function				// O - TRUE if OK, FALSE otherwise
  loadform()
  {
    global $_POST;


    if (!html_form_validate())
      return (FALSE);

    if (array_key_exists("title", $_POST))
      $this->title = trim($_POST["title"]);

    if (array_key_exists("contents", $_POST))
      $this->contents = trim($_POST["contents"]);

    if (array_key_exists("url", $_POST))
      $this->url = trim($_POST["url"]);

    if (array_key_exists("workgroup_id", $_POST))
      $this->workgroup_id = (int)$_POST["workgroup_id"];

    return ($this->validate());
  }


  //
  // 'article::save()' - Save an article object.
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
                      ." SET workgroup_id = $this->workgroup_id"
                      .", title = '" . db_escape($this->title) . "'"
                      .", contents = '" . db_escape($this->contents) . "'"
                      .", url = '" . db_escape($this->url) . "'"
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
                  .", $this->workgroup_id"
                  .", '" . db_escape($this->title) . "'"
                  .", '" . db_escape($this->contents) . "'"
                  .", '" . db_escape($this->url) . "'"
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
  // 'article::validate()' - Validate the current article object values.
  //

  function				// O - TRUE if OK, FALSE otherwise
  validate()
  {
    $valid = TRUE;


    if ($this->title == "")
    {
      $this->title_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->title_valid = TRUE;

    if ($this->contents == "")
    {
      $this->contents_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->contents_valid = TRUE;

    $this->url_valid = validate_url($this->url);
    if (!$this->url_valid && preg_match("/^[a-z0-9]+\\/.*\\.(html|php)\$/i", $this->url) && strpos($this->url, "../") === FALSE)
      $this->url_valid = TRUE;
    if (!$this->url_valid)
      $valid = FALSE;

    return ($valid);
  }

  //
  // 'article::view()' - View an article.
  //

  function
  view($options = "", $level = 2, $links = TRUE)
  {
    global $html_path, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_OFFICER;


    $title    = htmlspecialchars($this->title, ENT_QUOTES);
    $contents = html_format($this->contents, FALSE, $level + 1);
    $date     = html_date($this->modify_date);

    if (validate_url($this->url))
      $url = htmlspecialchars($this->url, ENT_QUOTES);
    else
      $url = $html_path . htmlspecialchars($this->url, ENT_QUOTES);

    print("<h2>$title <small>$date</small></h2>\n"
	 ."$contents\n");
    if ($url != "" || ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)))
    {
      print("<p>");
      if ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID))
      {
	print("<a class=\"btn btn-default btn-xs\" href=\"${html_path}dynamo/articles.php?U$this->id$options\">Edit</a>\n");
	print("<a class=\"btn btn-default btn-xs\" href=\"${html_path}dynamo/articles.php?D$this->id$options\">Delete</a>\n");
      }
      if ($url != "")
	print("<a class=\"btn btn-default btn-xs\" href=\"$url\">View</a>\n");
      print("</p>\n");
    }
  }
}


//
// 'article_search()' - Return an array of article IDs for the given reference.
//

function				// O - Array of article objects
article_search($search = "",		// I - Search text
               $workgroup_id = -1,	// I - Which workgroup to limit to
               $order = "-modify_date")	// I - Order of objects
{
  if ($search != "")
  {
    // Convert the search string to an array of words...
    $words = html_search_words($search);

    // Loop through the array of words, adding them to the query...
    if ($workgroup_id >= 0)
      $query = " WHERE workgroup_id = $workgroup_id AND (";
    else
      $query  = " WHERE (";

    $prefix = "";
    $next   = " OR";
    $logic  = "";

    reset($words);
    foreach ($words as $word)
    {
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
	$query .= "${subpre}contents LIKE \"%$word%\"";
	$query .= "${subpre}url LIKE \"%$word%\"";

	$query .= ")";
	$prefix = $next;
	$logic  = '';
      }
    }

    $query .= ")";
  }
  else if ($workgroup_id >= 0)
    $query = " WHERE workgroup_id = $workgroup_id";
  else
    $query = "";

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

  // Do the query and convert the result to an array of objects...
  $result  = db_query("SELECT id FROM article$query");
  $matches = array();

  while ($row = db_next($result))
    $matches[sizeof($matches)] = $row["id"];

  // Free the query result and return the array...
  db_free($result);

  return ($matches);
}
?>
