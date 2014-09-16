<?php
//
// Class for the article table.
//

include_once "site.php";
include_once "db-workgroup.php";

$ARTICLE_COLUMNS = array(
  "workgroup_id" => PDO::PARAM_INT,
  "title" => PDO::PARAM_STR,
  "contents" => PDO::PARAM_STR,
  "url" => PDO::PARAM_STR,
  "display_until_date" => PDO::PARAM_STR,
  "create_date" => PDO::PARAM_STR,
  "create_id" => PDO::PARAM_INT,
  "modify_date" => PDO::PARAM_STR,
  "modify_id" => PDO::PARAM_INT
);


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
  var $display_until_date, $display_until_date_valid;
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

    $this->id            = 0;
    $this->workgroup_id  = 0;
    $this->title         = "";
    $this->contents      = "";
    $this->url           = "";
    $this->display_until_date = "";
    $this->create_date   = "";
    $this->create_id     = $LOGIN_ID;
    $this->modify_date   = "";
    $this->modify_id     = $LOGIN_ID;
  }


  //
  // 'article::delete()' - Delete an article object.
  //

  function
  delete()
  {
    db_delete("article", $this->id);
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

    html_form_field_start("display_until_date", "Display Until", $this->display_until_date_valid);
    html_form_text("display_until_date", "YYYY-MM-DD", $this->display_until_date);
    html_form_field_end();

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
    global $ARTICLE_COLUMNS;

    $this->clear();

    if (db_load($this, "article", $id, $ARTICLE_COLUMNS))
    {
      $this->id = $id;
      return (TRUE);
    }
    else
      return (FALSE);
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

    if (array_key_exists("display_until_date", $_POST))
      $this->display_until_date = trim($_POST["display_until_date"]);

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
    global $ARTICLE_COLUMNS, $LOGIN_ID, $PHP_SELF;


    $this->modify_date = db_datetime();
    $this->modify_id   = $LOGIN_ID;

    if ($this->id > 0)
      return (db_save($this, "article", $this->id, $ARTICLE_COLUMNS));

    $this->create_date = $this->modify_date;
    $this->create_id   = $this->modify_id;

    if (($id = db_create($this, "article", $ARTICLE_COLUMNS)) === FALSE)
      return (FALSE);

    $this->id = $id;

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

    if ($this->display_until_date != "" && !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[01])\$/", $this->display_until_date))
    {
      $this->display_until_date_valid = FALSE;
      $valid = FALSE;
    }
    else
      $this->display_until_date_valid = TRUE;

    return ($valid);
  }

  //
  // 'article::view()' - View an article.
  //

  function
  view($options = "", $level = 2, $links = TRUE, $btnclass = "btn-xs")
  {
    global $html_path, $LOGIN_ID, $LOGIN_IS_ADMIN, $LOGIN_IS_OFFICER;


    $title    = htmlspecialchars($this->title, ENT_QUOTES);
    $contents = html_format($this->contents, FALSE, $level + 1);
    $date     = html_date($this->create_date);

    if ($this->display_until_date != "" && $this->display_until_date < date("Y-m-d"))
      $title .= " (concluded)";

    if (validate_url($this->url))
      $url = htmlspecialchars($this->url, ENT_QUOTES);
    else
      $url = $html_path . htmlspecialchars($this->url, ENT_QUOTES);

    print("<h$level>$title <small>$date</small></h$level>\n"
	 ."$contents\n");
    if ($url != "" || ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID)))
    {
      print("<p>");
      if ($links && ($LOGIN_IS_ADMIN || $LOGIN_IS_OFFICER || $this->create_id == $LOGIN_ID))
      {
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/articles.php?U$this->id$options\">Edit</a>\n");
	print("<a class=\"btn btn-default $btnclass\" href=\"${html_path}dynamo/articles.php?D$this->id$options\">Delete</a>\n");
      }
      if ($url != "")
	print("<a class=\"btn btn-primary $btnclass\" href=\"$url\">View</a>\n");
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
               $order = "-create_date")	// I - Order of objects
{
  global $ARTICLE_COLUMNS;

  if ($workgroup_id >= 0)
    $keyvals = array("workgroup_id" => $workgroup_id);
  else
    $keyvals = null;

  return (db_search("article", $ARTICLE_COLUMNS, $keyvals, $search, $order));
}
?>
